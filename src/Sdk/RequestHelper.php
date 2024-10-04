<?php

namespace Ipregistry\Sdk;

/**
 * Utility class for common request-related tasks. 
 */
class RequestHelper
{
    /**
     *  Build a complete API request URL.
     * 
     * @param string $baseUrl The API base URL.
     * @param string $endpoint The API endpoint path.
     * @param array $queryParams  An associative array of query parameters.
     * 
     * @return string The fully constructed request URL.
     */
    public static function buildUrl(string $baseUrl, string $endpoint, array $queryParams = []): string
    {
        $url = $baseUrl . $endpoint;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }
}