<?php

namespace Ipregistry\Sdk\Endpoint;

use Ipregistry\Sdk\ClientInterface;
use Ipregistry\Sdk\RequestHelper;
use Ipregistry\Sdk\Response;

/**
 * Handles IP address lookup endpoints.
 * 
 * @see https://ipregistry.co/docs/hostname#contentsingle-ip for details on single IP lookup.
 * @see https://ipregistry.co/docs/hostname#contentbatch-ip for details on batch IP lookup.
 * @see https://ipregistry.co/docs/hostname#contentorigin-ip for details on origin IP lookup.
 */
class IpLookup extends AbstractEndpoint
{
    /**
     * Look up information for a single IP address.
     * 
     * @param string $ip The IP address to look up.
     * 
     * @return Response The Ipregistry API response.
     * 
     * @see https://ipregistry.co/docs/hostname#contentsingle-ip for details.
     */
    public function lookup(string $ip): Response
    {
        $endpoint = '/' . $ip;
        $options = [
            'query' => [
                'hostname' => $this->client->getHostname() ? 'true' : 'false',
            ],
        ];

        return $this->request('GET', $endpoint, $options);
    }

    /**
     * Look up information for multiple IP addresses in a batch request.
     * 
     * @param array $ips An array of IP addresses to look up.
     * 
     * @return Response The Ipregistry API response.
     * 
     * @see https://ipregistry.co/docs/hostname#contentbatch-ip for details.
     */
    public function batchLookup(array $ips): Response
    {
        $endpoint = '/';
        $options = [
            'json' => $ips, // Send IPs as JSON array in request body (POST)
            'query' => [
                'hostname' => $this->client->getHostname() ? 'true' : 'false',
            ],
        ];

        return $this->request('POST', $endpoint, $options);
    }

    /**
     * Look up information for the originating IP address.
     * 
     * @return Response The Ipregistry API response.
     * 
     * @see https://ipregistry.co/docs/hostname#contentorigin-ip for details.
     */
    public function originLookup(): Response
    {
        $endpoint = '/'; 
        $options = [
            'query' => [
                'hostname' => $this->client->getHostname() ? 'true' : 'false',
            ],
        ];

        return $this->request('GET', $endpoint, $options);
    }
}