<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\Credentials\Credentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\HttpClient\UserAgentPrependingHttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;

trait ApiClientTrait
{
    private $httpClient;
    private $headers;
    private $credentials;

    public function __construct(HttpClient $httpClient, $credentials = null, array $headers = [])
    {
        $this->httpClient = new UserAgentPrependingHttpClient($httpClient, 'HypothesisClient');
        $this->headers = $headers;
        if (!is_null($credentials)) {
            $this->setCredentials($credentials);
        }
    }

    private function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;
        $this->headers['Authorization'] = 'Basic '.base64_encode($credentials->getClientId().':'.$credentials->getSecretKey());
    }

    final public function getCredentials() : Credentials
    {
        return $this->credentials;
    }

    final protected function deleteRequest(UriInterface $uri, array $headers) : PromiseInterface
    {
        $request = new Request('DELETE', $uri, array_merge($this->headers, $headers));

        return $this->httpClient->send($request);
    }

    final protected function getRequest(UriInterface $uri, array $headers) : PromiseInterface
    {
        $request = new Request('GET', $uri, array_merge($this->headers, $headers));

        return $this->httpClient->send($request);
    }

    final protected function postRequest(
        UriInterface $uri,
        array $headers,
        string $content
    ) : PromiseInterface {
        $headers = array_merge($this->headers, $headers);

        $request = new Request('POST', $uri, $headers, $content);

        return $this->httpClient->send($request);
    }

    final protected function putRequest(
        UriInterface $uri,
        array $headers,
        string $content
    ) : PromiseInterface {
        $headers = array_merge($this->headers, $headers);

        $request = new Request('PUT', $uri, $headers, $content);

        return $this->httpClient->send($request);
    }

    final protected function patchRequest(
        UriInterface $uri,
        array $headers,
        string $content
    ) : PromiseInterface {
        $headers = array_merge($this->headers, $headers);

        $request = new Request('PATCH', $uri, $headers, $content);

        return $this->httpClient->send($request);
    }
}
