<?php

namespace Ipregistry\Sdk\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Ipregistry\Sdk\Config;
use Ipregistry\Sdk\Endpoint\IpLookup;
use Ipregistry\Sdk\Exception\BadRequestException;
use Ipregistry\Sdk\Exception\ForbiddenIpException;
use Ipregistry\Sdk\Exception\ForbiddenIpOriginException;
use Ipregistry\Sdk\Exception\ForbiddenOriginException;
use Ipregistry\Sdk\Exception\InsufficientCreditsException;
use Ipregistry\Sdk\Exception\InvalidApiKeyException;
use Ipregistry\Sdk\Exception\InvalidIpAddressException;
use Ipregistry\Sdk\Exception\MissingApiKeyException;
use Ipregistry\Sdk\Exception\ReservedIpAddressException;
use Ipregistry\Sdk\Exception\TooManyIpsException;
use Ipregistry\Sdk\Exception\TooManyRequestsException;
use Ipregistry\Sdk\IpRegistryClient;
use PHPUnit\Framework\TestCase;

class IpLookupTest extends TestCase
{
    /** @var IpLookup */
    private $ipLookup;

    /** @var MockHandler */
    private $mockHandler;

    public function setUp(): void
    {
        parent::setUp();

        $apiKey = getenv('IPREGISTRY_API_KEY');
        if ($apiKey === false) {
            $this->markTestSkipped('IPREGISTRY_API_KEY environment variable is not set.');
        }

        // Create a mock handler for Guzzle
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        // Create a Config object with a test API key
        $config = new Config($apiKey);

        // Create an IpRegistryClient with the mock HTTP client
        $client = new IpRegistryClient($config, $httpClient);

        // Create the IpLookup endpoint
        $this->ipLookup = new IpLookup($client);
    }

    public function testLookupSuccess(): void
    {
        // Mock a successful API response
        $mockResponse = new Response(200, [], '{
            "ip": "8.8.8.8",
            "type": "IPv4",
            "location": {
                "continent": {
                    "code": "NA",
                    "name": "North America"
                },
                "country": {
                    "name": "United States",
                    "code": "US",
                    "capital": "Washington D.C.",
                    "tld": ".us"
                },
                "city": "Mountain View",
                "postal": "94043",
                "latitude": 37.4223,
                "longitude": -122.085,
                "in_eu": false
            },
            "security": {
                "is_vpn": false
            },
            "time_zone": {
                "id": "America/Los_Angeles",
                "current_time": "2023-10-26T12:08:16-07:00",
                "offset": -25200
            }
        }');
        $this->mockHandler->append($mockResponse);

        // Call the lookup() method
        $response = $this->ipLookup->lookup('8.8.8.8');

        // Assertions
        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertEquals('8.8.8.8', $response->getIp());
        $this->assertEquals('IPv4', $response->getType());
        $this->assertEquals('NA', $response->getContinentCode());
        $this->assertEquals('North America', $response->getContinentName());
        $this->assertEquals('United States', $response->getCountryName());
        $this->assertEquals('US', $response->getCountryCode());
        $this->assertEquals('Washington D.C.', $response->getCountryCapital());
        $this->assertEquals('.us', $response->getCountryTld());
        $this->assertEquals('Mountain View', $response->getCity());
        $this->assertEquals('94043', $response->getPostalCode());
        $this->assertEquals(37.4223, $response->getLatitude());
        $this->assertEquals(-122.085, $response->getLongitude());
        $this->assertEquals(false, $response->isInEu());
        $this->assertEquals(false, $response->isVpn());
        $this->assertEquals('America/Los_Angeles', $response->getTimeZoneId());
        $this->assertEquals('2023-10-26T12:08:16-07:00', $response->getTimeZoneCurrentTime());
        $this->assertEquals(-25200, $response->getTimeZoneOffset());
    }

    public function testBatchLookupSuccess(): void
    {
        // Mock a successful batch API response
        $mockResponse = new Response(200, [], '{
            "results": [
                {
                    "ip": "8.8.8.8",
                    "type": "IPv4",
                    "location": {
                        "country": {
                            "name": "United States",
                            "code": "US"
                        }
                    }
                },
                {
                    "ip": "1.1.1.1",
                    "type": "IPv4",
                    "location": {
                        "country": {
                            "name": "United States",
                            "code": "US"
                        }
                    }
                }
            ]
        }');
        $this->mockHandler->append($mockResponse);

        // Call the batchLookup() method
        $ips = ['8.8.8.8', '1.1.1.1'];
        $response = $this->ipLookup->batchLookup($ips);

        // Assertions
        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertCount(2, $response->getData()['results']);

        // Assertions for the first IP in the results array
        $this->assertEquals('8.8.8.8', $response->getData()['results'][0]['ip']);
        $this->assertEquals('IPv4', $response->getData()['results'][0]['type']);
        $this->assertEquals('United States', $response->getData()['results'][0]['location']['country']['name']);
        $this->assertEquals('US', $response->getData()['results'][0]['location']['country']['code']);

        // Assertions for the second IP in the results array
        $this->assertEquals('1.1.1.1', $response->getData()['results'][1]['ip']);
        $this->assertEquals('IPv4', $response->getData()['results'][1]['type']);
        $this->assertEquals('United States', $response->getData()['results'][1]['location']['country']['name']);
        $this->assertEquals('US', $response->getData()['results'][1]['location']['country']['code']);
    }

    public function testOriginLookupSuccess(): void
    {
        // Mock a successful origin lookup response
        $mockResponse = new Response(200, [], '{
            "ip": "192.168.1.1",
            "type": "IPv4",
            "location": {
                "continent": {
                    "code": "EU",
                    "name": "Europe"
                },
                "country": {
                    "name": "Germany",
                    "code": "DE",
                    "capital": "Berlin",
                    "tld": ".de"
                },
                "city": "Munich",
                "postal": "80331",
                "latitude": 48.1374,
                "longitude": 11.5755,
                "in_eu": true
            },
            "security": {
                "is_vpn": false
            },
            "time_zone": {
                "id": "Europe/Berlin",
                "current_time": "2023-10-26T20:09:40+02:00",
                "offset": 7200
            }
        }');
        $this->mockHandler->append($mockResponse);

        // Call the originLookup() method
        $response = $this->ipLookup->originLookup();

        // Assertions
        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertEquals('192.168.1.1', $response->getIp());
        $this->assertEquals('IPv4', $response->getType());
        $this->assertEquals('EU', $response->getContinentCode());
        $this->assertEquals('Europe', $response->getContinentName());
        $this->assertEquals('Germany', $response->getCountryName());
        $this->assertEquals('DE', $response->getCountryCode());
        $this->assertEquals('Berlin', $response->getCountryCapital());
        $this->assertEquals('.de', $response->getCountryTld());
        $this->assertEquals('Munich', $response->getCity());
        $this->assertEquals('80331', $response->getPostalCode());
        $this->assertEquals(48.1374, $response->getLatitude());
        $this->assertEquals(11.5755, $response->getLongitude());
        $this->assertEquals(true, $response->isInEu());
        $this->assertEquals(false, $response->isVpn());
        $this->assertEquals('Europe/Berlin', $response->getTimeZoneId());
        $this->assertEquals('2023-10-26T20:09:40+02:00', $response->getTimeZoneCurrentTime());
        $this->assertEquals(7200, $response->getTimeZoneOffset());
    }

    public function testInvalidApiKeyException(): void
    {
        // Mock an Invalid API Key error response
        $mockResponse = new Response(403, [], '{
            "code": "INVALID_API_KEY",
            "message": "Your API key is invalid.",
            "resolution": "Check your API key and try again."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(InvalidApiKeyException::class);
        $this->expectExceptionMessage("The provided API key is invalid or disabled: Your API key is invalid.");
        $this->expectExceptionCode(403);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }

    public function testInsufficientCreditsException(): void
    {
        // Mock an Insufficient Credits error response
        $mockResponse = new Response(402, [], '{
            "code": "INSUFFICIENT_CREDITS",
            "message": "You do not have enough credits.",
            "resolution": "Purchase more credits."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(InsufficientCreditsException::class);
        $this->expectExceptionMessage("The API request failed due to insufficient credits: You do not have enough credits.");
        $this->expectExceptionCode(402);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }

    public function testInvalidIpAddressException(): void
    {
        // Mock an Invalid IP Address error response
        $mockResponse = new Response(400, [], '{
            "code": "INVALID_IP_ADDRESS",
            "message": "The IP address is invalid.",
            "resolution": "Provide a valid IP address."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(InvalidIpAddressException::class);
        $this->expectExceptionMessage("The provided IP address is invalid: The IP address is invalid.");
        $this->expectExceptionCode(400);

        // Call the lookup() method with an invalid IP
        $this->ipLookup->lookup('invalid-ip');
    }

    public function testBadRequestException(): void
    {
        // Mock a Bad Request error response
        $mockResponse = new Response(400, [], '{
            "code": "BAD_REQUEST",
            "message": "The request was malformed.",
            "resolution": "Review the request format."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("The request was malformed or contained invalid parameters: The request was malformed.");
        $this->expectExceptionCode(400);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }

    public function testForbiddenIpException(): void
    {
        // Mock a Forbidden IP error response
        $mockResponse = new Response(403, [], '{
            "code": "FORBIDDEN_IP",
            "message": "Your IP address is not allowed.",
            "resolution": "Contact support for assistance."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(ForbiddenIpException::class);
        $this->expectExceptionMessage("The request was forbidden because your IP address is not allowed: Your IP address is not allowed.");
        $this->expectExceptionCode(403);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }

    public function testForbiddenOriginException(): void
    {
        // Mock a Forbidden Origin error response
        $mockResponse = new Response(403, [], '{
            "code": "FORBIDDEN_ORIGIN",
            "message": "Your origin is not allowed.",
            "resolution": "Check your origin settings."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(ForbiddenOriginException::class);
        $this->expectExceptionMessage("The request was forbidden because your origin is not allowed: Your origin is not allowed.");
        $this->expectExceptionCode(403);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }

    public function testForbiddenIpOriginException(): void
    {
        // Mock a Forbidden IP and Origin error response
        $mockResponse = new Response(403, [], '{
            "code": "FORBIDDEN_IP_ORIGIN",
            "message": "Your IP address and origin are not allowed.",
            "resolution": "Contact support for assistance."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(ForbiddenIpOriginException::class);
        $this->expectExceptionMessage("The request was forbidden because your IP address and origin are not allowed: Your IP address and origin are not allowed.");
        $this->expectExceptionCode(403);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }

    public function testMissingApiKeyException(): void
    {
        // Mock a Missing API Key error response
        $mockResponse = new Response(401, [], '{
            "code": "MISSING_API_KEY",
            "message": "You have not supplied an API key.",
            "resolution": "Add your API key as a query parameter: https://api.ipregistry.co/?key=YOUR_API_KEY"
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(MissingApiKeyException::class);
        $this->expectExceptionMessage("An API key is required to make requests: You have not supplied an API key.");
        $this->expectExceptionCode(401);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }

    public function testReservedIpAddressException(): void
    {
        // Mock a Reserved IP Address error response
        $mockResponse = new Response(400, [], '{
            "code": "RESERVED_IP_ADDRESS",
            "message": "The IP address is reserved.",
            "resolution": "Provide a non-reserved IP address."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(ReservedIpAddressException::class);
        $this->expectExceptionMessage("The provided IP address is reserved and cannot be looked up: The IP address is reserved.");
        $this->expectExceptionCode(400);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('127.0.0.1');
    }

    public function testTooManyIpsException(): void
    {
        // Mock a Too Many IPs error response
        $mockResponse = new Response(400, [], '{
            "code": "TOO_MANY_IPS",
            "message": "Too many IP addresses provided.",
            "resolution": "Reduce the number of IP addresses in your request."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(TooManyIpsException::class);
        $this->expectExceptionMessage("Too many IP addresses were provided in the batch request: Too many IP addresses provided.");
        $this->expectExceptionCode(400);

        // Call the batchLookup() method with too many IPs (you'll need to define the limit)
        $ips = array_fill(0, 1025, '8.8.8.8'); // Example: More than 1024 IPs
        $this->ipLookup->batchLookup($ips);
    }

    public function testTooManyRequestsException(): void
    {
        // Mock a Too Many Requests error response
        $mockResponse = new Response(429, [], '{
            "code": "TOO_MANY_REQUESTS",
            "message": "You have exceeded the rate limit.",
            "resolution": "Please wait and try again later."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(TooManyRequestsException::class);
        $this->expectExceptionMessage("The API rate limit has been exceeded: You have exceeded the rate limit.");
        $this->expectExceptionCode(429);

        // Call the lookup() method, which should throw the exception
        $this->ipLookup->lookup('8.8.8.8');
    }
}
