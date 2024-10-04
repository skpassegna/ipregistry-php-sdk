<?php

namespace Ipregistry\Sdk;

use Psr\Http\Message\ResponseInterface;

interface ClientInterface 
{
    /**
     * Send an HTTP request to the Ipregistry API.
     * 
     * @param string $method  HTTP method ('GET', 'POST')
     * @param string $endpoint API endpoint path
     * @param array $options  Request options (query parameters, body, etc.)
     * 
     * @return ResponseInterface The PSR-7 response object from Guzzle.
     * @throws \GuzzleHttp\Exception\GuzzleException  For Guzzle errors.
     * 
     * @see https://ipregistry.co/docs/hostname#contentendpoints#content for the list of available endpoints
     */
    public function send(string $method, string $endpoint, array $options = []): ResponseInterface;

    /**
     * Get the Ipregistry API key.
     *
     * @return string The API key.
     */
    public function getApiKey(): string;

    /**
     * Get the Ipregistry API base URL.
     *
     * @return string The base URL.
     */
    public function getBaseUrl(): string;

    /**
     * Get the request timeout in seconds.
     *
     * @return int The timeout in seconds.
     */
    public function getTimeout(): int;

    /**
     * Check if hostname lookup is enabled.
     *
     * @return bool True if hostname lookup is enabled, false otherwise.
     */
    public function getHostname(): bool;

    /**
     * Get the output format ('json' or 'xml').
     *
     * @return string The output format.
     */
    public function getFormat(): string;
}