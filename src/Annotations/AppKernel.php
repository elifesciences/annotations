<?php

namespace eLife\Annotations;

use Aws\Sqs\SqsClient;
use eLife\Annotations\Provider\QueueCommandsProvider;
use eLife\ApiClient\HttpClient\BatchingHttpClient;
use eLife\ApiClient\HttpClient\Guzzle6HttpClient;
use eLife\ApiClient\HttpClient\NotifyingHttpClient;
use eLife\ApiProblem\Silex\ApiProblemProvider;
use eLife\ApiSdk\ApiSdk;
use eLife\Bus\Limit\CompositeLimit;
use eLife\Bus\Limit\LoggingLimit;
use eLife\Bus\Limit\MemoryLimit;
use eLife\Bus\Limit\SignalsLimit;
use eLife\Bus\Queue\SqsMessageTransformer;
use eLife\Bus\Queue\SqsWatchableQueue;
use eLife\HypothesisClient\ApiSdk as HypothesisApiSdk;
use eLife\HypothesisClient\Credentials\Credentials;
use eLife\HypothesisClient\HttpClient\BatchingHttpClient as HypothesisBatchingHttpClient;
use eLife\HypothesisClient\HttpClient\Guzzle6HttpClient as HypothesisGuzzle6HttpClient;
use eLife\HypothesisClient\HttpClient\NotifyingHttpClient as HypothesisNotifyingHttpClient;
use eLife\Logging\LoggingFactory;
use eLife\Logging\Monitoring;
use eLife\Ping\Silex\PingControllerProvider;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Knp\Provider\ConsoleServiceProvider;
use Monolog\Logger;
use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\ContainerInterface;
use Silex\Application;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\VarDumperServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use function GuzzleHttp\Psr7\str;

final class AppKernel implements ContainerInterface, HttpKernelInterface, TerminableInterface
{
    private $app;

    public function __construct(string $environment = 'dev')
    {
        $configFile = __DIR__.'/../../config.php';
        $config = array_merge(file_exists($configFile) ? require $configFile : [], require __DIR__."/../../config/{$environment}.php");

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
            ],
            'hypothesis' => ($config['hypothesis'] ?? []) + [
                'api_url' => 'https://hypothes.is/api/',
                'client_id' => '',
                'secret_key' => '',
                'authority' => '',
            ],
        ]);

        $this->app->register(new ApiProblemProvider());
        $this->app->register(new PingControllerProvider());

        if ($this->app['debug']) {
            $this->app->register(new VarDumperServiceProvider());
            $this->app->register(new HttpFragmentServiceProvider());
            $this->app->register(new ServiceControllerServiceProvider());
            $this->app->register(new TwigServiceProvider());
            $this->app->register(new WebProfilerServiceProvider(), [
                'profiler.cache_dir' => __DIR__.'/../../var/cache/profiler',
                'profiler.mount_prefix' => '/_profiler',
            ]);
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

        $this->app['hypothesis.guzzle'] = function (Application $app) {
            // Create default HandlerStack
            $stack = HandlerStack::create();
            $logger = $app['logger'];
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

            return new Client([
                'base_uri' => $app['hypothesis']['api_url'],
                'handler' => $stack,
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

            $credentials = new Credentials(
                $app['hypothesis']['client_id'],
                $app['hypothesis']['secret_key'],
                $app['hypothesis']['authority']
            );

            return new HypothesisApiSdk($notifyingHttpClient, $credentials);
        };

        $this->app['guzzle'] = function (Application $app) {
            // Create default HandlerStack
            $stack = HandlerStack::create();
            $logger = $app['logger'];
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

            return new Client([
                'base_uri' => $app['api.url'],
                'handler' => $stack,
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
            return new SqsWatchableQueue($app['aws.sqs'], $app['aws']['queue_name']);
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

    /**
     * Use only in integration tests.
     *
     * @internal
     */
    public function override($id, callable $factory)
    {
        $this->app[$id] = $factory;
    }
}
