<?php

namespace eLife\Annotations;

use Aws\Sqs\SqsClient;
use ComposerLocator;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use eLife\Annotations\Controller\AnnotationsController;
use eLife\Annotations\Provider\QueueCommandsProvider;
use eLife\Annotations\Serializer\AnnotationNormalizer;
use eLife\ApiClient\HttpClient\BatchingHttpClient;
use eLife\ApiClient\HttpClient\Guzzle6HttpClient;
use eLife\ApiClient\HttpClient\NotifyingHttpClient;
use eLife\ApiProblem\Silex\ApiProblemProvider;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiValidator\MessageValidator\JsonMessageValidator;
use eLife\ApiValidator\SchemaFinder\PathBasedSchemaFinder;
use eLife\Bus\Limit\CompositeLimit;
use eLife\Bus\Limit\LoggingLimit;
use eLife\Bus\Limit\MemoryLimit;
use eLife\Bus\Limit\SignalsLimit;
use eLife\Bus\Queue\Mock\WatchableQueueMock;
use eLife\Bus\Queue\SqsMessageTransformer;
use eLife\Bus\Queue\SqsWatchableQueue;
use eLife\ContentNegotiator\Silex\ContentNegotiationProvider;
use eLife\HypothesisClient\ApiSdk as HypothesisSdk;
use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\BatchingHttpClient as HypothesisBatchingHttpClient;
use eLife\HypothesisClient\HttpClient\Guzzle6HttpClient as HypothesisGuzzle6HttpClient;
use eLife\HypothesisClient\HttpClient\NotifyingHttpClient as HypothesisNotifyingHttpClient;
use eLife\Logging\LoggingFactory;
use eLife\Logging\Monitoring;
use eLife\Ping\Silex\PingControllerProvider;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use JsonSchema\Validator;
use Knp\Provider\ConsoleServiceProvider;
use Monolog\Logger;
use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\ContainerInterface;
use Silex\Application;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use tests\eLife\Annotations\InMemoryStorageAdapter;
use tests\eLife\Annotations\ValidatingStorageAdapter;
use function GuzzleHttp\Psr7\str;

final class AppKernel implements ContainerInterface, HttpKernelInterface, TerminableInterface
{
    private $app;

    public function __construct(string $environment = 'dev')
    {
        $configFile = __DIR__.'/../../config.php';
        $config = array_merge(
            $environment !== 'test' && file_exists($configFile) ? require $configFile : [],
            require __DIR__."/../../config/{$environment}.php"
        );

        $this->app = new Application([
            'debug' => $config['debug'] ?? false,
            'logging.path' => $config['logging']['path'] ?? __DIR__.'/../../var/logs',
            'logging.level' => $config['logging']['level'] ?? Logger::INFO,
            'api.url' => $config['api_url'] ?? 'https://api.elifesciences.org/',
            'api.requests_batch' => $config['api_requests_batch'] ?? 10,
            'process_memory_limit' => $config['process_memory_limit'] ?? 256,
            'aws' => ($config['aws'] ?? []) + [
                'queue_name' => 'annotations--prod',
                'queue_message_default_type' => 'profile',
                'credential_file' => true,
                'region' => 'us-east-1',
                'endpoint' => 'http://localhost:4100',
                'stub' => false,
            ],
            'hypothesis' => ($config['hypothesis'] ?? []) + [
                'api_url' => 'https://hypothes.is/api/',
                'user_management' => [
                    'client_id' => '',
                    'client_secret' => '',
                ],
                'jwt_signing' => [
                    'client_id' => '',
                    'client_secret' => '',
                    'expire' => 600,
                ],
                'authority' => '',
                'group' => '',
            ],
            'mock' => $config['mock'] ?? false,
        ]);

        $this->app->register(new ApiProblemProvider());
        $this->app->register(new ContentNegotiationProvider());
        $this->app->register(new PingControllerProvider());

        if ($this->app['debug']) {
            $this->app->register(new HttpFragmentServiceProvider());
            $this->app->register(new ServiceControllerServiceProvider());
            $this->app->register(new TwigServiceProvider());
        }

        $this->app['logger'] = function (Application $app) {
            $factory = new LoggingFactory($app['logging.path'], 'annotations', $app['logging.level']);

            return $factory->logger();
        };

        $this->app['monitoring'] = function () {
            return new Monitoring();
        };

        /*
         * @internal
         */
        $this->app['limit._memory'] = function (Application $app) {
            return MemoryLimit::mb($app['process_memory_limit']);
        };
        /*
         * @internal
         */
        $this->app['limit._signals'] = function () {
            return SignalsLimit::stopOn(['SIGINT', 'SIGTERM', 'SIGHUP']);
        };

        $this->app['limit.long_running'] = function (Application $app) {
            return new LoggingLimit(
                new CompositeLimit(
                    $app['limit._memory'],
                    $app['limit._signals']
                ),
                $app['logger']
            );
        };

        $this->app['limit.interactive'] = function (Application $app) {
            return new LoggingLimit(
                $app['limit._signals'],
                $app['logger']
            );
        };

        $this->app['hypothesis.guzzle.handler'] = function () {
            return HandlerStack::create();
        };

        $this->app['guzzle.handler'] = function () {
            return HandlerStack::create();
        };

        if ($this->app['mock']) {
            $this->app['elife.json_message_validator'] = function () {
                return new JsonMessageValidator(
                    new PathBasedSchemaFinder(ComposerLocator::getPath('elife/api').'/dist/model'),
                    new Validator()
                );
            };

            $this->app['guzzle.mock.in_memory_storage'] = function () {
                return new InMemoryStorageAdapter();
            };

            $this->app['guzzle.mock.validating_storage'] = function () {
                return new ValidatingStorageAdapter($this->app['guzzle.mock.in_memory_storage'], $this->app['elife.json_message_validator']);
            };

            $this->app['hypothesis.guzzle.mock'] = function () {
                return new MockMiddleware($this->app['guzzle.mock.in_memory_storage'], 'replay');
            };

            $this->app['guzzle.mock'] = function () {
                return new MockMiddleware($this->app['guzzle.mock.validating_storage'], 'replay');
            };

            $this->app->extend('guzzle.handler', function (HandlerStack $stack) {
                $stack->push($this->app['guzzle.mock']);

                return $stack;
            });

            $this->app->extend('hypothesis.guzzle.handler', function (HandlerStack $stack) {
                $stack->push($this->app['hypothesis.guzzle.mock']);

                return $stack;
            });
        }

        $this->app['hypothesis.guzzle'] = function () {
            $logger = $this->app['logger'];
            $this->app->extend('hypothesis.guzzle.handler', function (HandlerStack $stack) use ($logger) {
                $stack->push(
                    Middleware::mapRequest(function ($request) use ($logger) {
                        $logger->debug("Request performed in Guzzle Middleware: {$request->getUri()}.", ['request' => str($request)]);

                        return $request;
                    })
                );
                $stack->push(
                    Middleware::mapResponse(function ($response) use ($logger) {
                        $logger->debug('Response received in Guzzle Middleware.', ['response' => str($response)]);

                        return $response;
                    })
                );

                return $stack;
            });

            return new Client([
                'base_uri' => $this->app['hypothesis']['api_url'],
                'handler' => $this->app['hypothesis.guzzle.handler'],
            ]);
        };

        $this->app['hypothesis.sdk'] = function (Application $app) {
            $notifyingHttpClient = new HypothesisNotifyingHttpClient(
                new HypothesisBatchingHttpClient(
                    new HypothesisGuzzle6HttpClient(
                        $app['hypothesis.guzzle']
                    ),
                    $app['api.requests_batch']
                )
            );
            if ($app['debug']) {
                $logger = $app['logger'];
                $notifyingHttpClient->addRequestListener(function ($request) use ($logger) {
                    $logger->debug("Request performed in NotifyingHttpClient: {$request->getUri()}");
                });
            }

            $userManagement = new UserManagementCredentials(
                $app['hypothesis']['user_management']['client_id'],
                $app['hypothesis']['user_management']['client_secret'],
                $app['hypothesis']['authority']
            );

            $jwtSigning = new JWTSigningCredentials(
                $app['hypothesis']['jwt_signing']['client_id'],
                $app['hypothesis']['jwt_signing']['client_secret'],
                $app['hypothesis']['authority'],
                new Clock()
            );

            return new HypothesisSdk($notifyingHttpClient, $userManagement, $jwtSigning, $app['hypothesis']['group']);
        };

        $this->app['guzzle'] = function () {
            $logger = $this->app['logger'];
            $this->app->extend('guzzle.handler', function (HandlerStack $stack) use ($logger) {
                $stack->push(
                    Middleware::mapRequest(function ($request) use ($logger) {
                        $logger->debug("Request performed in Guzzle Middleware: {$request->getUri()}.", ['request' => str($request)]);

                        return $request;
                    })
                );
                $stack->push(
                    Middleware::mapResponse(function ($response) use ($logger) {
                        $logger->debug('Response received in Guzzle Middleware.', ['response' => str($response)]);

                        return $response;
                    })
                );

                return $stack;
            });

            return new Client([
                'base_uri' => $this->app['api.url'],
                'handler' => $this->app['guzzle.handler'],
            ]);
        };

        $this->app['api.sdk'] = function (Application $app) {
            $notifyingHttpClient = new NotifyingHttpClient(
                new BatchingHttpClient(
                    new Guzzle6HttpClient(
                        $app['guzzle']
                    ),
                    $app['api.requests_batch']
                )
            );
            if ($app['debug']) {
                $logger = $app['logger'];
                $notifyingHttpClient->addRequestListener(function ($request) use ($logger) {
                    $logger->debug("Request performed in NotifyingHttpClient: {$request->getUri()}");
                });
            }

            return new ApiSdk($notifyingHttpClient);
        };

        $this->app['aws.sqs'] = function (Application $app) {
            $config = [
                'version' => '2012-11-05',
                'region' => $app['aws']['region'],
            ];
            if (isset($app['aws']['endpoint'])) {
                $config['endpoint'] = $app['aws']['endpoint'];
            }
            if (!isset($app['aws']['credential_file']) || $app['aws']['credential_file'] === false) {
                $config['credentials'] = [
                    'key' => $app['aws']['key'],
                    'secret' => $app['aws']['secret'],
                ];
            }

            return new SqsClient($config);
        };

        $this->app['aws.queue'] = function (Application $app) {
            if ($app['aws']['stub']) {
                return new WatchableQueueMock();
            } else {
                return new SqsWatchableQueue($app['aws.sqs'], $app['aws']['queue_name']);
            }
        };

        $this->app['aws.queue_transformer'] = function (Application $app) {
            return new SqsMessageTransformer($app['api.sdk']);
        };

        $this->app->register(new ConsoleServiceProvider(), [
            'console.name' => 'Annotations console',
            'console.version' => '0.1.0',
            'console.project_directory' => __DIR__.'/../..',
        ]);

        $this->app->register(new QueueCommandsProvider(), [
            'sqs.queue_message_type' => $this->app['aws']['queue_message_default_type'],
            'sqs.queue_name' => $this->app['aws']['queue_name'],
            'sqs.region' => $this->app['aws']['region'],
        ]);

        $this->app['annotation.serializer'] = function (Application $app) {
            return new AnnotationNormalizer();
        };

        $this->app['controllers.annotations'] = function () {
            return new AnnotationsController($this->app['hypothesis.sdk'], $this->app['api.sdk'], $this->app['annotation.serializer']);
        };

        $this->app->get('/annotations', 'controllers.annotations:annotationsAction')
            ->before($this->app['negotiate.accept'](
                'application/vnd.elife.annotation-list+json; version=1'
            ));

        $this->app->after(function (Request $request, Response $response, Application $app) {
            if ($response->isCacheable()) {
                $response->headers->set('ETag', md5($response->getContent()));
                $response->isNotModified($request);
            }

            if (!$this->app['mock'] && $this->app['debug']) {
                (new JsonMessageValidator(
                    new PathBasedSchemaFinder(ComposerLocator::getPath('elife/api').'/dist/model'),
                    new Validator()
                ))->validate((new DiactorosFactory())->createResponse($response));
            }
        });
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true) : Response
    {
        return $this->app->handle($request, $type, $catch);
    }

    public function terminate(Request $request, Response $response)
    {
        $this->app->terminate($request, $response);
    }

    public function get($id)
    {
        if (!isset($this->app[$id])) {
            throw new UnknownIdentifierException($id);
        }

        return $this->app[$id];
    }

    public function has($id) : bool
    {
        return isset($this->app[$id]);
    }
}
