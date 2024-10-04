<?php

namespace Ipregistry\Sdk\Endpoint;

use Ipregistry\Sdk\ClientInterface;
use Ipregistry\Sdk\Response;

/**
 *  Handles User-Agent parsing endpoints.
 * 
 *  @see https://ipregistry.co/docs/hostname#contentbatch-user-agent for details on User-Agent parsing.
 *  @see https://ipregistry.co/docs/hostname#contentorigin-user-agent for details on Origin User-Agent parsing.
 */
class UserAgentParsing extends AbstractEndpoint
{
    /**
     * Parse a single User-Agent string.
     * 
     * @param string $userAgent The User-Agent string to parse. 
     * 
     * @return Response The Ipregistry API response.
     * 
     * @see https://ipregistry.co/docs/hostname#contentbatch-user-agent for details (single User-Agent parsing is done via batch endpoint). 
     */
    public function parse(string $userAgent): Response
    {
        return $this->batchParse([$userAgent]); 
    }

    /**
     *  Parse multiple User-Agent strings in a batch request.
     * 
     *  @param array $userAgents An array of User-Agent strings.
     * 
     *  @return Response The Ipregistry API response.
     *  
     *  @see https://ipregistry.co/docs/hostname#contentbatch-user-agent for details.
     */
    public function batchParse(array $userAgents): Response
    {
        $endpoint = '/user_agent';
        $options = [
            'json' => $userAgents, // Send User-Agents as JSON array in request body (POST)
        ];

        return $this->request('POST', $endpoint, $options);
    }

    /**
     *  Parse the User-Agent string for the originating request.
     * 
     *  @return Response The Ipregistry API response.
     *  
     *  @see https://ipregistry.co/docs/hostname#contentorigin-user-agent for details. 
     */
    public function originParse(): Response
    {
        $endpoint = '/user_agent'; 

        return $this->request('GET', $endpoint); 
    }
}