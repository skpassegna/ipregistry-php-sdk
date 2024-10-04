<?php

namespace Ipregistry\Sdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Core API client for interacting with the Ipregistry API.
 *
 * @see https://ipregistry.co/docs/hostname#contentendpoints#content for available API endpoints and usage details.
 */
class IpRegistryClient implements ClientInterface
{
    /** @var Config  */
    private $config;

    /** @var Client Guzzle HTTP client */
    private $httpClient;

    /**
     * IpRegistryClient constructor.
     * 
     * @param Config $config Configuration object.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->httpClient = new Client([
            'base_uri' => $this->config->getBaseUrl(),
            'timeout'  => $this->config->getTimeout(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function send(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        // Add API key to request options (as query parameter)
        $options['query']['key'] = $this->config->getApiKey(); 

        try {
            return $this->httpClient->request($method, $endpoint, $options);
        } catch (GuzzleException $e) {
            // Handle Guzzle exceptions here (logging, re-throwing, etc.) 
            throw $e; // Re-throw for now
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getApiKey(): string
    {
        return $this->config->getApiKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseUrl(): string
    {
        return $this->config->getBaseUrl();
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeout(): int
    {
        return $this->config->getTimeout();
    }

    /**
     * {@inheritDoc}
     */
    public function getHostname(): bool
    {
        return $this->config->getHostname();
    }

    /**
     * {@inheritDoc}
     */
    public function getFormat(): string
    {
        return $this->config->getFormat();
    }
}