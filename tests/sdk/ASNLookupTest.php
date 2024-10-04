<?php

namespace Ipregistry\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Ipregistry\Sdk\Config;
use Ipregistry\Sdk\Endpoint\ASNLookup;
use Ipregistry\Sdk\Exception\BadRequestException;
use Ipregistry\Sdk\Exception\InsufficientCreditsException;
use Ipregistry\Sdk\Exception\InvalidApiKeyException;
use Ipregistry\Sdk\Exception\InvalidAsnException;
use Ipregistry\Sdk\Exception\MissingApiKeyException;
use Ipregistry\Sdk\Exception\ReservedAsnException;
use Ipregistry\Sdk\Exception\TooManyAsnsException;
use Ipregistry\Sdk\Exception\TooManyRequestsException;
use Ipregistry\Sdk\Exception\UnknownAsnException;
use Ipregistry\Sdk\IpRegistryClient;
use PHPUnit\Framework\TestCase;

class ASNLookupTest extends TestCase
{
    /** @var ASNLookup */
    private $asnLookup;

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
        $httpClient = new Client(['handler' => $handlerStack]);

        $config = new Config($apiKey);
        $client = new IpRegistryClient($config, $httpClient);

        $this->asnLookup = new ASNLookup($client);
    }

    public function testLookupSuccess(): void
    {
        $mockResponse = new Response(200, [], '{
            "asn": 15169,
            "name": "Google LLC",
            "country_code": "US",
            "allocated": "1988-12-01T00:00:00Z",
            "registry": "ARIN",
            "domain": "google.com",
            "type": "business",
            "prefixes": {
                "ipv4_count": 1090,
                "ipv6_count": 122,
                "ipv4": [
                    {
                        "cidr": "172.253.64.0/19",
                        "country_code": "US",
                        "network_name": "GOOGLE",
                        "organization": "Google LLC",
                        "registry": "ARIN",
                        "status": "allocated",
                        "size": 8192
                    }
                ],
                "ipv6": [
                    {
                        "cidr": "2607:f8b0:4009:800::/39",
                        "country_code": "US",
                        "network_name": "GOOGLE - Google LLC",
                        "organization": "Google LLC",
                        "registry": "ARIN",
                        "status": "allocated",
                        "size": 549755813888
                    }
                ]
            },
            "relationships": {
                "upstreams": [
                    174,
                    3356,
                    701,
                    1299
                ],
                "downstreams": [
                    132605,
                    209106,
                    396541,
                    45543
                ],
                "peers": [
                    12956,
                    32934,
                    3491,
                    5511
                ]
            },
            "updated": "2023-10-26T12:15:48Z"
        }');
        $this->mockHandler->append($mockResponse);

        $response = $this->asnLookup->lookup(15169);

        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertEquals(15169, $response->getAsn());
        $this->assertEquals('Google LLC', $response->getAsnName());
        $this->assertEquals('US', $response->getAsnCountryCode());
        $this->assertEquals('1988-12-01T00:00:00Z', $response->getAsnAllocated());
        $this->assertEquals('ARIN', $response->getAsnRegistry());
        $this->assertEquals('google.com', $response->getAsnDomain());
        $this->assertEquals('business', $response->getAsnType());
        $this->assertEquals(1090, $response->getAsnIpv4Count());
        $this->assertEquals(122, $response->getAsnIpv6Count());
        $this->assertIsArray($response->getAsnIpv4Prefixes());
        $this->assertIsArray($response->getAsnIpv6Prefixes());
        $this->assertIsArray($response->getAsnUpstreams());
        $this->assertIsArray($response->getAsnDownstreams());
        $this->assertIsArray($response->getAsnPeers());
        $this->assertEquals('2023-10-26T12:15:48Z', $response->getAsnUpdated());
    }

    public function testBatchLookupSuccess(): void
    {
        $mockResponse = new Response(200, [], '{
            "results": [
                {
                    "asn": 15169,
                    "name": "Google LLC",
                    "country_code": "US",
                    "allocated": "1988-12-01T00:00:00Z",
                    "registry": "ARIN",
                    "domain": "google.com",
                    "type": "business"
                },
                {
                    "asn": 20940,
                    "name": "Akamai Technologies, Inc.",
                    "country_code": "US",
                    "allocated": "1997-11-12T00:00:00Z",
                    "registry": "ARIN",
                    "domain": "akamai.com",
                    "type": "business"
                }
            ]
        }');
        $this->mockHandler->append($mockResponse);

        $asns = [15169, 20940];
        $response = $this->asnLookup->batchLookup($asns);

        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertCount(2, $response->getData()['results']);

        // Assertions for the first ASN in the results array
        $this->assertEquals(15169, $response->getData()['results'][0]['asn']);
        $this->assertEquals('Google LLC', $response->getData()['results'][0]['name']);
        $this->assertEquals('US', $response->getData()['results'][0]['country_code']);

        // Assertions for the second ASN in the results array
        $this->assertEquals(20940, $response->getData()['results'][1]['asn']);
        $this->assertEquals('Akamai Technologies, Inc.', $response->getData()['results'][1]['name']);
        $this->assertEquals('US', $response->getData()['results'][1]['country_code']);
    }

    public function testOriginLookupSuccess(): void
    {
        $mockResponse = new Response(200, [], '{
            "asn": 47886,
            "name": "Hetzner Online GmbH",
            "country_code": "DE",
            "allocated": "2009-07-02T00:00:00Z",
            "registry": "RIPE NCC",
            "domain": "hetzner.com",
            "type": "hosting"
        }');
        $this->mockHandler->append($mockResponse);

        $response = $this->asnLookup->originLookup();

        $this->assertEquals(200, $response->getRawResponse()->getStatusCode());
        $this->assertEquals(47886, $response->getAsn());
        $this->assertEquals('Hetzner Online GmbH', $response->getAsnName());
        $this->assertEquals('DE', $response->getAsnCountryCode());
        $this->assertEquals('2009-07-02T00:00:00Z', $response->getAsnAllocated());
        $this->assertEquals('RIPE NCC', $response->getAsnRegistry());
        $this->assertEquals('hetzner.com', $response->getAsnDomain());
        $this->assertEquals('hosting', $response->getAsnType());
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

        $this->asnLookup->lookup(15169);
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

        $this->asnLookup->lookup(15169);
    }

    public function testInvalidAsnException(): void
    {
        $mockResponse = new Response(400, [], '{
            "code": "INVALID_ASN",
            "message": "The ASN you entered is in the wrong format.",
            "resolution": "It should begin with AS followed by a number, like AS173."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(InvalidAsnException::class);
        $this->expectExceptionMessage("The provided ASN is invalid: The ASN you entered is in the wrong format.");
        $this->expectExceptionCode(400);

        $this->asnLookup->lookup('invalid-asn');
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

        $this->asnLookup->lookup(15169);
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

        $this->asnLookup->lookup(15169);
    }

    public function testReservedAsnException(): void
    {
        $mockResponse = new Response(400, [], '{
            "code": "RESERVED_ASN",
            "message": "You attempted to search for a private ASN.",
            "resolution": "Please try with a public ASN instead."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(ReservedAsnException::class);
        $this->expectExceptionMessage("The provided ASN is reserved and cannot be looked up: You attempted to search for a private ASN.");
        $this->expectExceptionCode(400);

        $this->asnLookup->lookup(64512); // Example private ASN
    }

    public function testTooManyAsnsException(): void
    {
        $mockResponse = new Response(400, [], json_encode([
            "code" => "TOO_MANY_ASNS",
            "message" => "You've submitted a batch request with too many ASNs.",
            "resolution" => "Ensure your batch request includes no more than 16 ASNs."
        ]));
        $this->mockHandler->append($mockResponse);

        $this->expectException(TooManyAsnsException::class);
        $this->expectExceptionMessage("Too many ASNs were provided in the batch request: You've submitted a batch request with too many ASNs.");
        $this->expectExceptionCode(400);

        $asns = range(1, 17); // Create an array with 17 ASNs (exceeding the limit)
        $this->asnLookup->batchLookup($asns);
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

        $this->asnLookup->lookup(15169);
    }

    public function testUnknownAsnException(): void
    {
        $mockResponse = new Response(404, [], '{
            "code": "UNKNOWN_ASN",
            "message": "The ASN you requested was not found.",
            "resolution": "Check the ASN and try again."
        }');
        $this->mockHandler->append($mockResponse);

        $this->expectException(UnknownAsnException::class);
        $this->expectExceptionMessage("The requested ASN was not found: The ASN you requested was not found.");
        $this->expectExceptionCode(404);

        $this->asnLookup->lookup(999999); // Example of an unknown ASN
    }
}
