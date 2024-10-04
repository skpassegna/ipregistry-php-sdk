<?php

namespace Ipregistry\Sdk\Endpoint;

use Ipregistry\Sdk\ClientInterface;
use Ipregistry\Sdk\Exception\IpregistryException;
use Ipregistry\Sdk\Exception\InvalidApiKeyException;
use Ipregistry\Sdk\Exception\InsufficientCreditsException;
use Ipregistry\Sdk\Exception\BadRequestException;
use Ipregistry\Sdk\Exception\ForbiddenIpException;
use Ipregistry\Sdk\Exception\ForbiddenOriginException;
use Ipregistry\Sdk\Exception\ForbiddenIpOriginException;
use Ipregistry\Sdk\Exception\InvalidAsnException;
use Ipregistry\Sdk\Exception\InvalidIpAddressException;
use Ipregistry\Sdk\Exception\MissingApiKeyException;
use Ipregistry\Sdk\Exception\ReservedAsnException;
use Ipregistry\Sdk\Exception\ReservedIpAddressException;
use Ipregistry\Sdk\Exception\TooManyAsnsException;
use Ipregistry\Sdk\Exception\TooManyIpsException;
use Ipregistry\Sdk\Exception\TooManyRequestsException;
use Ipregistry\Sdk\Exception\TooManyUserAgentsException;
use Ipregistry\Sdk\Exception\UnknownAsnException;
use Ipregistry\Sdk\Response;

/**
 * Abstract class providing shared functionality for endpoint classes.
 */
abstract class AbstractEndpoint
{
    /** @var ClientInterface */
    protected $client;

    /**
     * AbstractEndpoint constructor.
     *
     * @param ClientInterface $client The API client instance.
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     *  Send an API request and handle the response.
     * 
     * @param string $method The HTTP method ('GET', 'POST').
     * @param string $endpoint The API endpoint path.
     * @param array $options Request options (query parameters, body, etc.).
     * 
     * @return Response The Ipregistry API response object.
     * @throws IpregistryException  If an API error occurs.
     */
    /**
     *  Send an API request and handle the response.
     * 
     * @param string $method The HTTP method ('GET', 'POST').
     * @param string $endpoint The API endpoint path.
     * @param array $options Request options (query parameters, body, etc.).
     * 
     * @return Response The Ipregistry API response object.
     * @throws IpregistryException  If an API error occurs.
     */
    protected function request(string $method, string $endpoint, array $options = []): Response
    {
        try {
            $response = $this->client->send($method, $endpoint, $options);
            return new Response($response, $this->client->getFormat());

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->handleClientException($e);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new IpregistryException("Connection error: " . $e->getMessage(), null, null, 0, $e);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new IpregistryException("Request error: " . $e->getMessage(), null, null, 0, $e);
        } catch (\Exception $e) {
            throw new IpregistryException("An error occurred during the API request.", null, null, 0, $e);
        }
    }

    /**
     * Handle Guzzle client exceptions, mapping them to Ipregistry SDK exceptions.
     *
     * @param \GuzzleHttp\Exception\ClientException $e The Guzzle exception.
     * 
     * @throws IpregistryException A specific SDK exception or a generic IpregistryException.
     */
    private function handleClientException(\GuzzleHttp\Exception\ClientException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);

        $errorCode = $responseBody['code'] ?? null;
        $message = $responseBody['message'] ?? $e->getMessage();
        $resolution = $responseBody['resolution'] ?? null;

        switch ($statusCode) {
            case 400: // Bad Request
                switch ($errorCode) { 
                    case 'BAD_REQUEST':
                        throw new BadRequestException($message, $errorCode, $resolution);
                    case 'INVALID_ASN':
                        throw new InvalidAsnException($message, $errorCode, $resolution);
                    case 'INVALID_FILTER_SYNTAX':
                        throw new BadRequestException($message, $errorCode, $resolution); // Using BadRequestException for now
                    case 'INVALID_IP_ADDRESS':
                        throw new InvalidIpAddressException($message, $errorCode, $resolution);
                    case 'RESERVED_ASN':
                        throw new ReservedAsnException($message, $errorCode, $resolution);
                    case 'RESERVED_IP_ADDRESS':
                        throw new ReservedIpAddressException($message, $errorCode, $resolution);
                    case 'TOO_MANY_ASNS':
                        throw new TooManyAsnsException($message, $errorCode, $resolution);
                    case 'TOO_MANY_IPS':
                        throw new TooManyIpsException($message, $errorCode, $resolution);
                    case 'TOO_MANY_USER_AGENTS':
                        throw new TooManyUserAgentsException($message, $errorCode, $resolution);
                    default:
                        throw new BadRequestException($message, $errorCode, $resolution);
                } 
            case 401: // Unauthorized (Missing API Key)
                throw new MissingApiKeyException($message, $errorCode, $resolution);
            case 402: // Payment Required (Insufficient Credits)
                throw new InsufficientCreditsException($message, $errorCode, $resolution);
            case 403: // Forbidden
                switch ($errorCode) {
                    case 'DISABLED_API_KEY':
                        throw new InvalidApiKeyException($message, $errorCode, $resolution); // Reusing InvalidApiKeyException
                    case 'FORBIDDEN_IP':
                        throw new ForbiddenIpException($message, $errorCode, $resolution);
                    case 'FORBIDDEN_ORIGIN':
                        throw new ForbiddenOriginException($message, $errorCode, $resolution);
                    case 'FORBIDDEN_IP_ORIGIN':
                        throw new ForbiddenIpOriginException($message, $errorCode, $resolution);
                    case 'INVALID_API_KEY':
                        throw new InvalidApiKeyException($message, $errorCode, $resolution);
                    default:
                        throw new IpregistryException($message, $errorCode, $resolution, $statusCode); // Generic Forbidden
                }
            case 404: // Not Found (Unknown ASN)
                throw new UnknownAsnException($message, $errorCode, $resolution);
            case 429: // Too Many Requests
                throw new TooManyRequestsException($message, $errorCode, $resolution);
            case 451: // Unavailable For Legal Reasons (Disabled API Key)
                throw new InvalidApiKeyException($message, $errorCode, $resolution); // Reusing InvalidApiKeyException
            case 500: // Internal Server Error
                throw new IpregistryException($message, $errorCode, $resolution, $statusCode);
            default:
                throw new IpregistryException($message, $errorCode, $resolution, $statusCode); // Catch-all for other HTTP errors
        }
    }
}