<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\Credentials\Credentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\HttpClient\UserAgentPrependingHttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;

trait ApiClient
{
    private $httpClient;
    private $headers;
    private $credentials = null;

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
    }

    /**
     * @return Credentials|null
     */
    private function getCredentials()
    {
        return $this->credentials;
    }

    private function getAuthorizationBasic() : array
    {
        return ($this->getCredentials() instanceof Credentials) ? ['Authorization' => $this->getCredentials()->getAuthorizationBasic()] : [];
    }

    final protected function deleteRequest(
        UriInterface $uri,
        array $headers
    ) : PromiseInterface {
        return $this->createRequest('DELETE', $uri, $headers);
    }

    final protected function getRequest(
        UriInterface $uri,
        array $headers
    ) : PromiseInterface {
        return $this->createRequest('GET', $uri, $headers);
    }

    final protected function postRequest(
        UriInterface $uri,
        array $headers,
        string $content
    ) : PromiseInterface {
        return $this->createRequest('POST', $uri, $headers, $content);
    }

    final protected function putRequest(
        UriInterface $uri,
        array $headers,
        string $content
    ) : PromiseInterface {
        return $this->createRequest('PUT', $uri, $headers, $content);
    }

    final protected function patchRequest(
        UriInterface $uri,
        array $headers,
        string $content
    ) : PromiseInterface {
        return $this->createRequest('PATCH', $uri, $headers, $content);
    }

    private function createRequest(
        string $method,
        UriInterface $uri,
        array $headers,
        $content = null
    ) : PromiseInterface {
        $headers = array_merge($this->headers, $headers);

        $request = new Request($method, $uri, $headers, $content);

        return $this->httpClient->send($request);
    }
}
