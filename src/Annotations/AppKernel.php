<?php

namespace eLife\Annotations;

use Aws\Sqs\SqsClient;
use ComposerLocator;
use Csa\GuzzleHttp\Middleware\Cache\MockMiddleware;
use eLife\Annotations\Controller\AnnotationsController;
use eLife\Annotations\Provider\QueueCommandsProvider;
use eLife\Annotations\Serializer\CommonMark;
use eLife\Annotations\Serializer\CommonMark\HtmlPurifierRenderer;
use eLife\Annotations\Serializer\CommonMark\MathEscapeRenderer;
use eLife\Annotations\Serializer\HypothesisClientAnnotationNormalizer;
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
use eLife\HypothesisClient\Clock\FixedClock;
use eLife\HypothesisClient\Clock\SystemClock;
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
use HTMLPurifier;
use JsonSchema\Validator;
use Knp\Provider\ConsoleServiceProvider;
use League\CommonMark\Block as CommonMarkBlock;
use League\CommonMark\DocParser;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\Inline as CommonMarkInline;
use Monolog\Logger;
use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\ContainerInterface;
use Silex\Application;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
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

        $cache_path = $config['cache']['path'] ?? __DIR__.'/../../var/cache';
        $this->app = new Application([
            'debug' => $config['debug'] ?? false,
            'cache.path' => $cache_path,
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
            'common_mark.environment' => ($config['common_mark']['environment'] ?? []) + [
                'allow_unsafe_links' => false,
            ],
            'html_purifier' => ($config['html_purifier'] ?? []) + [
                'Cache.SerializerPath' => $cache_path.'/html_purifier',
            ],
            'mock' => $config['mock'] ?? false,
        ]);

        $this->app->register(new ApiProblemProvider());
        $this->app->register(new ContentNegotiationProvider());
        $this->app->register(new PingControllerProvider());
        $this->app->register(new ServiceControllerServiceProvider());

        if ($this->app['debug']) {
            $this->app->register(new HttpFragmentServiceProvider());
            $this->app->register(new TwigServiceProvider());
        }

        $this->app['logger'] = function () {
            $factory = new LoggingFactory($this->app['logging.path'], 'annotations', $this->app['logging.level']);

            return $factory->logger();
        };

        $this->app['monitoring'] = function () {
            return new Monitoring();
        };

        /*
         * @internal
         */
        $this->app['limit._memory'] = function () {
            return MemoryLimit::mb($this->app['process_memory_limit']);
        };
        /*
         * @internal
         */
        $this->app['limit._signals'] = function () {
            return SignalsLimit::stopOn(['SIGINT', 'SIGTERM', 'SIGHUP']);
        };

        $this->app['limit.long_running'] = function () {
            return new LoggingLimit(
                new CompositeLimit(
                    $this->app['limit._memory'],
                    $this->app['limit._signals']
                ),
                $this->app['logger']
            );
        };

        $this->app['limit.interactive'] = function () {
            return new LoggingLimit(
                $this->app['limit._signals'],
                $this->app['logger']
            );
        };

        $this->app['hypothesis.guzzle.handler'] = function () {
            return HandlerStack::create();
        };

        $this->app['api.guzzle.handler'] = function () {
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

            $this->app['api.guzzle.mock.validating_storage'] = function () {
                return new ValidatingStorageAdapter($this->app['guzzle.mock.in_memory_storage'], $this->app['elife.json_message_validator']);
            };

            $this->app['api.guzzle.mock'] = function () {
                return new MockMiddleware($this->app['api.guzzle.mock.validating_storage'], 'replay');
            };

            $this->app['hypothesis.guzzle.mock'] = function () {
                return new MockMiddleware($this->app['guzzle.mock.in_memory_storage'], 'replay');
            };

            $this->app->extend('api.guzzle.handler', function (HandlerStack $stack) {
                $stack->push($this->app['api.guzzle.mock']);

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

        $this->app['hypothesis.sdk.jwt_signing'] = function () {
            return new JWTSigningCredentials(
                $this->app['hypothesis']['jwt_signing']['client_id'],
                $this->app['hypothesis']['jwt_signing']['client_secret'],
                $this->app['hypothesis']['authority'],
                (!$this->app['mock']) ? new SystemClock() : new FixedClock()
            );
        };

        $this->app['hypothesis.sdk'] = function () {
            $notifyingHttpClient = new HypothesisNotifyingHttpClient(
                new HypothesisBatchingHttpClient(
                    new HypothesisGuzzle6HttpClient(
                        $this->app['hypothesis.guzzle']
                    ),
                    $this->app['api.requests_batch']
                )
            );
            if ($this->app['debug']) {
                $logger = $this->app['logger'];
                $notifyingHttpClient->addRequestListener(function ($request) use ($logger) {
                    $logger->debug("Request performed in NotifyingHttpClient: {$request->getUri()}");
                });
            }

            $userManagement = new UserManagementCredentials(
                $this->app['hypothesis']['user_management']['client_id'],
                $this->app['hypothesis']['user_management']['client_secret'],
                $this->app['hypothesis']['authority']
            );

            return new HypothesisSdk($notifyingHttpClient, $userManagement, $this->app['hypothesis.sdk.jwt_signing'], $this->app['hypothesis']['group']);
        };

        $this->app['api.guzzle'] = function () {
            $logger = $this->app['logger'];
            $this->app->extend('api.guzzle.handler', function (HandlerStack $stack) use ($logger) {
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
                'handler' => $this->app['api.guzzle.handler'],
            ]);
        };

        $this->app['api.sdk'] = function () {
            $notifyingHttpClient = new NotifyingHttpClient(
                new BatchingHttpClient(
                    new Guzzle6HttpClient(
                        $this->app['api.guzzle']
                    ),
                    $this->app['api.requests_batch']
                )
            );
            if ($this->app['debug']) {
                $logger = $this->app['logger'];
                $notifyingHttpClient->addRequestListener(function ($request) use ($logger) {
                    $logger->debug("Request performed in NotifyingHttpClient: {$request->getUri()}");
                });
            }

            return new ApiSdk($notifyingHttpClient);
        };

        $this->app['aws.sqs'] = function () {
            $config = [
                'version' => '2012-11-05',
                'region' => $this->app['aws']['region'],
            ];
            if (isset($this->app['aws']['endpoint'])) {
                $config['endpoint'] = $this->app['aws']['endpoint'];
            }
            if (!isset($this->app['aws']['credential_file']) || $this->app['aws']['credential_file'] === false) {
                $config['credentials'] = [
                    'key' => $this->app['aws']['key'],
                    'secret' => $this->app['aws']['secret'],
                ];
            }

            return new SqsClient($config);
        };

        $this->app['aws.queue'] = function () {
            if ($this->app['aws']['stub']) {
                return new WatchableQueueMock();
            } else {
                return new SqsWatchableQueue($this->app['aws.sqs'], $this->app['aws']['queue_name']);
            }
        };

        $this->app['aws.queue_transformer'] = function () {
            return new SqsMessageTransformer($this->app['api.sdk']);
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

        $this->app['annotation.serializer.common_mark.environment'] = function () {
            $environment = Environment::createCommonMarkEnvironment();
            $environment->setConfig($this->app['common_mark.environment']);

            $environment->addBlockRenderer(CommonMarkBlock\Element\BlockQuote::class, new CommonMark\Block\Renderer\BlockQuoteRenderer());
            $environment->addBlockRenderer(CommonMarkBlock\Element\FencedCode::class, new CommonMark\Block\Renderer\CodeRenderer());
            $environment->addBlockRenderer(CommonMarkBlock\Element\HtmlBlock::class, new CommonMark\Block\Renderer\HtmlBlockRenderer());
            $environment->addBlockRenderer(CommonMarkBlock\Element\IndentedCode::class, new CommonMark\Block\Renderer\CodeRenderer());
            $environment->addBlockRenderer(CommonMarkBlock\Element\ListItem::class, new CommonMark\Block\Renderer\ListItemRenderer());
            $environment->addBlockRenderer(CommonMarkBlock\Element\Paragraph::class, new CommonMark\Block\Renderer\ParagraphRenderer());

            $environment->addInlineRenderer(CommonMarkInline\Element\HtmlInline::class, new CommonMark\Inline\Renderer\HtmlInlineRenderer());
            $environment->addInlineRenderer(CommonMarkInline\Element\Image::class, new CommonMark\Inline\Renderer\ImageRenderer());

            return $environment;
        };

        $this->app['annotation.serializer.common_mark.doc_parser'] = function () {
            return new DocParser($this->app['annotation.serializer.common_mark.environment']);
        };

        $this->app['annotation.serializer.common_mark.element_renderer'] = function () {
            return new HtmlRenderer($this->app['annotation.serializer.common_mark.environment']);
        };

        $this->app['annotation.serializer.html_purifier'] = function () {
            return new HTMLPurifier($this->app['html_purifier']);
        };

        $this->app->extend('annotation.serializer.common_mark.element_renderer', function (ElementRendererInterface $elementRenderer) {
            return new MathEscapeRenderer($elementRenderer);
        });

        $this->app->extend('annotation.serializer.common_mark.element_renderer', function (ElementRendererInterface $elementRenderer) {
            return new HtmlPurifierRenderer($elementRenderer, $this->app['annotation.serializer.html_purifier']);
        });

        $this->app['annotation.serializer'] = function () {
            return new HypothesisClientAnnotationNormalizer($this->app['annotation.serializer.common_mark.doc_parser'], $this->app['annotation.serializer.common_mark.element_renderer'], $this->app['logger']);
        };

        $this->app['controllers.annotations'] = function () {
            return new AnnotationsController($this->app['hypothesis.sdk'], $this->app['api.sdk'], $this->app['annotation.serializer']);
        };

        $this->app->get('/annotations', 'controllers.annotations:annotationsAction')
            ->before($this->app['negotiate.accept'](
                'application/vnd.elife.annotation-list+json; version=1'
            ));

        $this->app->after(function (Request $request, Response $response) {
            if ($response->isCacheable()) {
                $response->headers->set('ETag', md5($response->getContent()));
                $response->isNotModified($request);
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
