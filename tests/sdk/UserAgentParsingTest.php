<?php

namespace Ipregistry\Sdk\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Ipregistry\Sdk\Config;
use Ipregistry\Sdk\Endpoint\UserAgentParsing;
use Ipregistry\Sdk\Exception\BadRequestException;
use Ipregistry\Sdk\Exception\InsufficientCreditsException;
use Ipregistry\Sdk\Exception\InvalidApiKeyException;
use Ipregistry\Sdk\Exception\MissingApiKeyException;
use Ipregistry\Sdk\Exception\TooManyRequestsException;
use Ipregistry\Sdk\Exception\TooManyUserAgentsException;
use Ipregistry\Sdk\IpRegistryClient;
use PHPUnit\Framework\TestCase;

class UserAgentParsingTest extends TestCase
{
    /** @var UserAgentParsing */
    private $userAgentParsing;

    /** @var MockHandler */
    private $mockHandler;

    public function setUp(): void
    {
        parent::setUp();

        $apiKey = getenv('IPREGISTRY_API_KEY');
        if ($apiKey === false) {
            $this->markTestSkipped('IPREGISTRY_API_KEY environment variable is not set.');
        }

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack        ]);

        $config = new Config($apiKey);
        $client = new IpRegistryClient($config, $httpClient);

        $this->userAgentParsing = new UserAgentParsing($client);
    }

    public function testParseSuccess(): void
    {
        $mockResponse = new Response(200, [], '{
            "user_agent": {
                "header": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
                "name": "Chrome",
                "type": "browser",
                "version": "117.0.0.0",
                "version_major": "117",
                "device": {
                    "name": "PC",
                    "type": "desktop",
                    "brand": null
                },
                "engine": {
                    "name": "Blink",
                    "type": "browser",
                    "version": null
                },
                "os": {
                    "name": "Windows",
                    "type": "desktop",
                    "version": "10"
                }
            }
        }');
        $this->mockHandler->append($mockResponse);

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36';
        $response = $this->userAgentParsing->parse($userAgent);

        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertEquals($userAgent, $response->getUserAgentHeader());
        $this->assertEquals('Chrome', $response->getUserAgentName());
        $this->assertEquals('browser', $response->getUserAgentType());
        $this->assertEquals('117.0.0.0', $response->getUserAgentVersion());
        $this->assertEquals('117', $response->getUserAgentVersionMajor());
        $this->assertEquals('PC', $response->getDeviceName());
        $this->assertEquals('desktop', $response->getDeviceType());
        $this->assertEquals(null, $response->getDeviceBrand());
        $this->assertEquals('Blink', $response->getEngineName());
        $this->assertEquals('browser', $response->getEngineType());
        $this->assertEquals(null, $response->getEngineVersion());
        $this->assertEquals('Windows', $response->getOsName());
        $this->assertEquals('desktop', $response->getOsType());
        $this->assertEquals('10', $response->getOsVersion());
    }

    public function testBatchParseSuccess(): void
    {
        $mockResponse = new Response(200, [], '{
            "results": [
                {
                    "user_agent": {
                        "header": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
                        "name": "Chrome",
                        "type": "browser"
                    }
                },
                {
                    "user_agent": {
                        "header": "Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1",
                        "name": "Safari",
                        "type": "browser"
                    }
                }
            ]
        }');
        $this->mockHandler->append($mockResponse);

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1'
        ];
        $response = $this->userAgentParsing->batchParse($userAgents);

        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertCount(2, $response->getData()['results']);

        // Assertions for the first User-Agent in the results array
        $this->assertEquals($userAgents[0], $response->getData()['results'][0]['user_agent']['header']);
        $this->assertEquals('Chrome', $response->getData()['results'][0]['user_agent']['name']);
        $this->assertEquals('browser', $response->getData()['results'][0]['user_agent']['type']);

        // Assertions for the second User-Agent in the results array
        $this->assertEquals($userAgents[1], $response->getData()['results'][1]['user_agent']['header']);
        $this->assertEquals('Safari', $response->getData()['results'][1]['user_agent']['name']);
        $this->assertEquals('browser', $response->getData()['results'][1]['user_agent']['type']);
    }

    public function testOriginParseSuccess(): void
    {
        $mockResponse = new Response(200, [], '{
            "user_agent": {
                "header": "curl/7.88.1",
                "name": "cURL",
                "type": "hacker"
            }
        }');
        $this->mockHandler->append($mockResponse);

        $response = $this->userAgentParsing->originParse();

        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertEquals('curl/7.88.1', $response->getUserAgentHeader());
        $this->assertEquals('cURL', $response->getUserAgentName());
        $this->assertEquals('hacker', $response->getUserAgentType());
    }

    public function testInvalidApiKeyException(): void
    {
        $mockResponse = new Response(403, [], '{
            "code": "INVALID_API_KEY",
            "message": "Your API key is invalid.",
            "resolution": "Check your API key and try again."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(InvalidApiKeyException::class);
        $this->expectExceptionMessage("The provided API key is invalid or disabled: Your API key is invalid.");
        $this->expectExceptionCode(403);

        $this->userAgentParsing->parse('Mozilla/5.0');
    }

    public function testInsufficientCreditsException(): void
    {
        $mockResponse = new Response(402, [], '{
            "code": "INSUFFICIENT_CREDITS",
            "message": "You do not have enough credits.",
            "resolution": "Purchase more credits."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(InsufficientCreditsException::class);
        $this->expectExceptionMessage("The API request failed due to insufficient credits: You do not have enough credits.");
        $this->expectExceptionCode(402);

        $this->userAgentParsing->parse('Mozilla/5.0');
    }

    public function testBadRequestException(): void
    {
        $mockResponse = new Response(400, [], '{
            "code": "BAD_REQUEST",
            "message": "The request was malformed.",
            "resolution": "Review the request format."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("The request was malformed or contained invalid parameters: The request was malformed.");
        $this->expectExceptionCode(400);

        $this->userAgentParsing->parse('Mozilla/5.0');
    }

    public function testMissingApiKeyException(): void
    {
        $mockResponse = new Response(401, [], '{
            "code": "MISSING_API_KEY",
            "message": "You have not supplied an API key.",
            "resolution": "Add your API key as a query parameter: https://api.ipregistry.co/?key=YOUR_API_KEY"
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(MissingApiKeyException::class);
        $this->expectExceptionMessage("An API key is required to make requests: You have not supplied an API key.");
        $this->expectExceptionCode(401);

        $this->userAgentParsing->parse('Mozilla/5.0');
    }

    public function testTooManyRequestsException(): void
    {
        $mockResponse = new Response(429, [], '{
            "code": "TOO_MANY_REQUESTS",
            "message": "You have exceeded the rate limit.",
            "resolution": "Please wait and try again later."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(TooManyRequestsException::class);
        $this->expectExceptionMessage("The API rate limit has been exceeded: You have exceeded the rate limit.");
        $this->expectExceptionCode(429);

        $this->userAgentParsing->parse('Mozilla/5.0');
    }

    public function testTooManyUserAgentsException(): void
    {
        $mockResponse = new Response(400, [], '{
            "code": "TOO_MANY_USER_AGENTS",
            "message": "You have specified too many user agents with the batch endpoint.",
            "resolution": "Batch requests must not contain more than 256 user agents."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(TooManyUserAgentsException::class);
        $this->expectExceptionMessage("Too many User-Agents were provided in the batch request: You have specified too many user agents with the batch endpoint.");
        $this->expectExceptionCode(400);

        $userAgents = array_fill(0, 257, 'Mozilla/5.0'); // Create an array with 257 User-Agents (exceeding the limit)
        $this->userAgentParsing->batchParse($userAgents);
    }
}