<?php

namespace Ipregistry\Sdk\Endpoint;

use Ipregistry\Sdk\ClientInterface;
use Ipregistry\Sdk\Response;

/**
 *  Handles Autonomous System (ASN) lookup endpoints.
 * 
 * @see https://ipregistry.co/docs/hostname#contentsingle-as for details on single ASN lookup.
 * @see https://ipregistry.co/docs/hostname#contentbatch-as for details on batch ASN lookup.
 * @see https://ipregistry.co/docs/hostname#contentorigin-as for details on origin ASN lookup. 
 */
class ASNLookup extends AbstractEndpoint
{
    /**
     * Look up information for a single ASN.
     * 
     * @param int $asn The ASN to look up.
     * 
     * @return Response The Ipregistry API response.
     * 
     * @see https://ipregistry.co/docs/hostname#contentsingle-as for details. 
     */
    public function lookup(int $asn): Response
    {
        $endpoint = '/AS' . $asn; // The endpoint requires 'AS' prefix

        return $this->request('GET', $endpoint);
    }

    /**
     *  Look up information for multiple ASNs in a batch request.
     * 
     *  @param array $asns An array of ASNs to look up (e.g., [12345, 67890]).
     * 
     *  @return Response The Ipregistry API response.
     * 
     *  @see https://ipregistry.co/docs/hostname#contentbatch-as for details. 
     */
    public function batchLookup(array $asns): Response
    {
        $endpoint = '/';
        $options = [
            'json' => $asns, // Send ASNs as JSON array in request body (POST)
        ];

        return $this->request('POST', $endpoint, $options);
    }

    /**
     *  Look up information for the originating ASN.
     * 
     *  @return Response The Ipregistry API response.
     * 
     *  @see https://ipregistry.co/docs/hostname#contentorigin-as for details.
     */
    public function originLookup(): Response
    {
        $endpoint = '/AS'; // Note: No trailing slash here (see documentation)

        return $this->request('GET', $endpoint);
    }
}