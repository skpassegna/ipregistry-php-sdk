<?php

namespace Ipregistry\Sdk;

/**
 *  Configuration class for the Ipregistry SDK.
 * 
 * @see https://ipregistry.co/docs/authentication#content for information on API keys
 * @see https://ipregistry.co/docs/eu-base-url#content for details about base URLs per region
 * @see https://ipregistry.co/docs/format#content for details about output formats
 * @see https://ipregistry.co/docs/hostname#content for information about enabling hostname lookups 
 */
class Config
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    /** @var int Request timeout in seconds */
    private $timeout; 

    /** @var bool  */
    private $hostname;

    /** @var string Output format: 'json' or 'xml'  */
    private $format;

    /**
     *  Config constructor.
     * 
     * @param string $apiKey  Your Ipregistry API key.
     * @param string $baseUrl (Optional) Override the default Ipregistry API base URL (for regions). Defaults to 'https://api.ipregistry.co'.
     * @param int $timeout (Optional) Request timeout in seconds. Defaults to 5 seconds.
     * @param bool $hostname  (Optional) Enable hostname lookup. Defaults to false.
     * @param string $format  (Optional) Output format. Accepts 'json' (default) or 'xml'.
     * 
     * @see https://ipregistry.co/docs/authentication#content for information on API keys
     * @see https://ipregistry.co/docs/eu-base-url#content for details about base URLs per region
     * @see https://ipregistry.co/docs/format#content for details about output formats
     * @see https://ipregistry.co/docs/hostname#content for information about enabling hostname lookups
     */
    public function __construct(
        string $apiKey, 
        string $baseUrl = 'https://api.ipregistry.co', 
        int $timeout = 5, 
        bool $hostname = false,
        string $format = 'json' 
    )
    {
        if (!in_array($format, ['json', 'xml'])) {
            throw new \InvalidArgumentException("Invalid output format. Must be 'json' or 'xml'.");
        }

        $this->apiKey = $apiKey; 
        $this->baseUrl = $baseUrl;
        $this->timeout = $timeout; 
        $this->hostname = $hostname;
        $this->format = $format; 
    }

    /**
     * Get the Ipregistry API key.
     *
     * @return string The API key.
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the Ipregistry API base URL.
     *
     * @return string The base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the request timeout in seconds.
     *
     * @return int The timeout in seconds.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Check if hostname lookup is enabled.
     *
     * @return bool True if hostname lookup is enabled, false otherwise.
     */
    public function getHostname(): bool
    {
        return $this->hostname;
    }

    /**
     * Get the output format ('json' or 'xml').
     *
     * @return string The output format.
     */
    public function getFormat(): string
    {
        return $this->format;
    }
} 