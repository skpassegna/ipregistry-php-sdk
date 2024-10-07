<?php

namespace Ipregistry\Sdk;

use Psr\Http\Message\ResponseInterface;

/**
 *  Handles Ipregistry API responses.
 * 
 *  @see https://ipregistry.co/docs/hostname#contentresponses/#content for details about the response fields.
 */
class Response
{
    /** @var ResponseInterface The PSR-7 response object from Guzzle */
    private $response;

    /** @var array|object The parsed response data */
    private $data;

    /** @var string The response format ('json' or 'xml') */
    private $format;

    /**
     * Response constructor.
     * 
     * @param ResponseInterface $response The PSR-7 response object.
     * @param string $format  The expected response format ('json' or 'xml').
     * 
     * @throws \RuntimeException If an error occurs during response parsing.
     */
    public function __construct(ResponseInterface $response, string $format)
    {
        $this->response = $response;
        $this->format = $format;

        if ($format === 'json') {
            $this->data = $this->parseJson();
        } elseif ($format === 'xml') {
            $this->data = $this->parseXml();
        } else {
            throw new \InvalidArgumentException("Invalid format specified: {$format}");
        }
    }

    /**
     *  Parse the response as JSON.
     * 
     * @return array The parsed JSON data.
     * @throws \RuntimeException If JSON parsing fails.
     */
    private function parseJson(): array
    {
        $data = json_decode($this->response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse JSON response: ' . json_last_error_msg());
        }
        return $data;
    }

    /**
     *  Parse the response as XML.
     * 
     * @return object The parsed XML data.
     * @throws \RuntimeException If XML parsing fails.
     */
    private function parseXml(): object
    {
        $data = simplexml_load_string($this->response->getBody());

        if ($data === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \RuntimeException('Failed to parse XML response: ' . implode(', ', $errors));
        }
        return $data;
    }

    /**
     *  Get the raw response object from Guzzle.
     * 
     * @return ResponseInterface The Guzzle response object.
     */
    public function getRawResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     *  Get the parsed response data.
     * 
     * @return array|object The parsed data (array for JSON, object for XML).
     */
    public function getData()
    {
        return $this->data;
    }

    // ------------------------------------------------------------------------
    // IP Address Response Field Convenience Methods:
    // ------------------------------------------------------------------------

    /**
     *  Get the IP address.
     * 
     * @return string|null  The IP address, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields 
     */
    public function getIp(): ?string
    {
        return $this->getField('ip');
    }

    /**
     * Get the IP address type.
     *
     * @return string|null  The IP address type ('IPv4' or 'IPv6'), or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getType(): ?string
    {
        return $this->getField('type');
    }

    /**
     * Get the mobile carrier name.
     *
     * @return string|null The carrier name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCarrierName(): ?string
    {
        return $this->getField('carrier.name');
    }

    /**
     * Get the mobile carrier MCC (Mobile Country Code).
     *
     * @return string|null The MCC, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCarrierMcc(): ?string
    {
        return $this->getField('carrier.mcc');
    }

    /**
     * Get the mobile carrier MNC (Mobile Network Code).
     *
     * @return string|null The MNC, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCarrierMnc(): ?string
    {
        return $this->getField('carrier.mnc');
    }

    /**
     * Get the company domain.
     *
     * @return string|null The company domain, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCompanyDomain(): ?string
    {
        return $this->getField('company.domain');
    }

    /**
     * Get the company name.
     *
     * @return string|null The company name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCompanyName(): ?string
    {
        return $this->getField('company.name');
    }

    /**
     * Get the company type.
     *
     * @return string|null The company type, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCompanyType(): ?string
    {
        return $this->getField('company.type');
    }

    /**
     * Get the connection ASN (Autonomous System Number).
     *
     * @return int|null The ASN, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getConnectionAsn(): ?int
    {
        return $this->getField('connection.asn');
    }

    /**
     * Get the connection domain.
     *
     * @return string|null The connection domain, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getConnectionDomain(): ?string
    {
        return $this->getField('connection.domain');
    }

    /**
     * Get the connection organization.
     *
     * @return string|null The connection organization, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getConnectionOrganization(): ?string
    {
        return $this->getField('connection.organization');
    }

    /**
     * Get the connection route.
     *
     * @return string|null The connection route, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getConnectionRoute(): ?string
    {
        return $this->getField('connection.route');
    }

    /**
     * Get the connection type.
     *
     * @return string|null The connection type, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getConnectionType(): ?string
    {
        return $this->getField('connection.type');
    }

    /**
     * Get the currency code.
     *
     * @return string|null The currency code (ISO 4217), or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyCode(): ?string
    {
        return $this->getField('currency.code');
    }

    /**
     * Get the currency name (US locale).
     *
     * @return string|null The currency name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyName(): ?string
    {
        return $this->getField('currency.name');
    }

    /**
     * Get the currency name (native locale).
     *
     * @return string|null The native currency name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyNameNative(): ?string
    {
        return $this->getField('currency.name_native');
    }

    /**
     * Get the currency plural name (US locale).
     *
     * @return string|null The currency plural name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyPlural(): ?string
    {
        return $this->getField('currency.plural');
    }

    /**
     * Get the currency plural name (native locale).
     *
     * @return string|null The native currency plural name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyPluralNative(): ?string
    {
        return $this->getField('currency.plural_native');
    }

    /**
     * Get the currency symbol.
     *
     * @return string|null The currency symbol, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencySymbol(): ?string
    {
        return $this->getField('currency.symbol');
    }

    /**
     * Get the currency symbol (native locale).
     *
     * @return string|null The native currency symbol, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencySymbolNative(): ?string
    {
        return $this->getField('currency.symbol_native');
    }

    /**
     * Get the prefix for negative currency amounts.
     *
     * @return string|null The negative currency prefix, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyNegativePrefix(): ?string
    {
        return $this->getField('currency.format.negative.prefix');
    }

    /**
     * Get the suffix for negative currency amounts.
     *
     * @return string|null The negative currency suffix, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyNegativeSuffix(): ?string
    {
        return $this->getField('currency.format.negative.suffix');
    }

    /**
     * Get the prefix for positive currency amounts.
     *
     * @return string|null The positive currency prefix, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyPositivePrefix(): ?string
    {
        return $this->getField('currency.format.positive.prefix');
    }

    /**
     * Get the suffix for positive currency amounts.
     *
     * @return string|null The positive currency suffix, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCurrencyPositiveSuffix(): ?string
    {
        return $this->getField('currency.format.positive.suffix');
    }

    /**
     * Get the continent code.
     *
     * @return string|null The continent code (ISO 3166-1 alpha-2), or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getContinentCode(): ?string
    {
        return $this->getField('location.continent.code');
    }

    /**
     * Get the continent name.
     *
     * @return string|null The continent name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getContinentName(): ?string
    {
        return $this->getField('location.continent.name');
    }

    /**
     * Get the country area (in km²).
     *
     * @return float|null The country area, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryArea(): ?float
    {
        return $this->getField('location.country.area');
    }

    /**
     * Get the country borders (array of ISO 3166-1 alpha-2 codes).
     *
     * @return array The country borders, or an empty array if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryBorders(): array
    {
        return $this->getField('location.country.borders') ?? [];
    }

    /**
     * Get the country calling code.
     *
     * @return string|null The calling code, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryCallingCode(): ?string
    {
        return $this->getField('location.country.calling_code');
    }

    /**
     * Get the country capital.
     *
     * @return string|null The capital city, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryCapital(): ?string
    {
        return $this->getField('location.country.capital');
    }

    /**
     * Get the country code.
     *
     * @return string|null The country code (ISO 3166-1 alpha-2), or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryCode(): ?string
    {
        return $this->getField('location.country.code');
    }

    /**
     * Get the country name.
     *
     * @return string|null The country name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryName(): ?string
    {
        return $this->getField('location.country.name');
    }

    /**
     * Get the country population.
     *
     * @return int|null The population, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryPopulation(): ?int
    {
        return $this->getField('location.country.population');
    }

    /**
     * Get the country population density.
     *
     * @return float|null The population density (residents per km²), or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryPopulationDensity(): ?float
    {
        return $this->getField('location.country.population_density');
    }

    /**
     * Get the country flag emoji.
     *
     * @return string|null The flag emoji, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryFlagEmoji(): ?string
    {
        return $this->getField('location.country.flag.emoji');
    }

    /**
     * Get the country flag emoji Unicode value.
     *
     * @return string|null The emoji Unicode value, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryFlagEmojiUnicode(): ?string
    {
        return $this->getField('location.country.flag.emoji_unicode');
    }

    /**
     * Get the country flag EmojiTwo SVG URL.
     *
     * @return string|null The EmojiTwo SVG URL, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryFlagEmojiTwo(): ?string
    {
        return $this->getField('location.country.flag.emojitwo');
    }

    /**
     * Get the country flag Noto PNG URL.
     *
     * @return string|null The Noto PNG URL, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryFlagNoto(): ?string
    {
        return $this->getField('location.country.flag.noto');
    }

    /**
     * Get the country flag Twemoji SVG URL.
     *
     * @return string|null The Twemoji SVG URL, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryFlagTwemoji(): ?string
    {
        return $this->getField('location.country.flag.twemoji');
    }

    /**
     * Get the country flag Wikimedia SVG URL.
     *
     * @return string|null The Wikimedia SVG URL, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryFlagWikimedia(): ?string
    {
        return $this->getField('location.country.flag.wikimedia');
    }

    /**
     * Get the country languages (array of language objects).
     *
     * @return array The languages, or an empty array if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryLanguages(): array
    {
        return $this->getField('location.country.languages') ?? [];
    }

    /**
     * Get the country TLD (top-level domain).
     *
     * @return string|null The TLD, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCountryTld(): ?string
    {
        return $this->getField('location.country.tld');
    }

    /**
     * Get the region code (ISO 3166-2).
     *
     * @return string|null The region code, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getRegionCode(): ?string
    {
        return $this->getField('location.region.code');
    }

    /**
     * Get the region name.
     *
     * @return string|null The region name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getRegionName(): ?string
    {
        return $this->getField('location.region.name');
    }

    /**
     * Get the city name.
     *
     * @return string|null The city name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getCity(): ?string
    {
        return $this->getField('location.city');
    }

    /**
     * Get the postal code.
     *
     * @return string|null The postal code, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getPostalCode(): ?string
    {
        return $this->getField('location.postal');
    }

    /**
     * Get the latitude.
     *
     * @return float|null The latitude, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getLatitude(): ?float
    {
        return $this->getField('location.latitude');
    }

    /**
     * Get the longitude.
     *
     * @return float|null The longitude, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getLongitude(): ?float
    {
        return $this->getField('location.longitude');
    }

    /**
     * Get the location language code (ISO 639-1).
     *
     * @return string|null The language code, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getLocationLanguageCode(): ?string
    {
        return $this->getField('location.language.code');
    }

    /**
     * Get the location language name.
     *
     * @return string|null The language name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getLocationLanguageName(): ?string
    {
        return $this->getField('location.language.name');
    }

    /**
     * Get the location language native name.
     *
     * @return string|null The native language name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getLocationLanguageNative(): ?string
    {
        return $this->getField('location.language.native');
    }

    /**
     * Check if the location is within the European Union.
     *
     * @return bool True if the location is in the EU, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isInEu(): bool
    {
        return $this->getField('location.in_eu') ?? false;
    }

    /**
     * Check if the IP address is anonymous.
     *
     * @return bool True if anonymous, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isAnonymous(): bool
    {
        return $this->getField('security.is_anonymous') ?? false;
    }

    /**
     * Check if the IP address is a known abuser.
     *
     * @return bool True if abuser, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isAbuser(): bool
    {
        return $this->getField('security.is_abuser') ?? false;
    }

    /**
     * Check if the IP address is a known attacker.
     *
     * @return bool True if attacker, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isAttacker(): bool
    {
        return $this->getField('security.is_attacker') ?? false;
    }

    /**
     * Check if the IP address is a bogon.
     *
     * @return bool True if bogon, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isBogon(): bool
    {
        return $this->getField('security.is_bogon') ?? false;
    }

    /**
     * Check if the IP address is from a cloud provider.
     *
     * @return bool True if cloud provider, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isCloudProvider(): bool
    {
        return $this->getField('security.is_cloud_provider') ?? false;
    }

    /**
     * Check if the IP address is a proxy.
     *
     * @return bool True if proxy, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isProxy(): bool
    {
        return $this->getField('security.is_proxy') ?? false;
    }

    /**
     * Check if the IP address is a relay.
     *
     * @return bool True if relay, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isRelay(): bool
    {
        return $this->getField('security.is_relay') ?? false;
    }

    /**
     * Check if the IP address is a threat.
     *
     * @return bool True if threat, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isThreat(): bool
    {
        return $this->getField('security.is_threat') ?? false;
    }

    /**
     * Check if the IP address is a Tor node.
     *
     * @return bool True if Tor node, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isTor(): bool
    {
        return $this->getField('security.is_tor') ?? false;
    }

    /**
     * Check if the IP address is a Tor exit node.
     *
     * @return bool True if Tor exit node, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isTorExit(): bool
    {
        return $this->getField('security.is_tor_exit') ?? false;
    }

    /**
     * Check if the IP address is a VPN.
     *
     * @return bool True if VPN, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isVpn(): bool
    {
        return $this->getField('security.is_vpn') ?? false;
    }

    /**
     * Get the time zone ID (IANA Time Zone Database).
     *
     * @return string|null The time zone ID, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getTimeZoneId(): ?string
    {
        return $this->getField('time_zone.id');
    }

    /**
     * Get the time zone abbreviation.
     *
     * @return string|null The time zone abbreviation, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getTimeZoneAbbreviation(): ?string
    {
        return $this->getField('time_zone.abbreviation');
    }

    /**
     * Get the current time in the time zone.
     *
     * @return string|null The current time (ISO 8601 format), or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getTimeZoneCurrentTime(): ?string
    {
        return $this->getField('time_zone.current_time');
    }

    /**
     * Get the time zone name.
     *
     * @return string|null The time zone name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getTimeZoneName(): ?string
    {
        return $this->getField('time_zone.name');
    }

    /**
     * Get the time zone offset (GMT offset in seconds).
     *
     * @return int|null The time zone offset, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function getTimeZoneOffset(): ?int
    {
        return $this->getField('time_zone.offset');
    }

    /**
     * Check if the time zone is in daylight saving time.
     *
     * @return bool True if in daylight saving, false otherwise.
     * @see https://ipregistry.co/docs/hostname#contentresponses/ip-address-fields
     */
    public function isTimeZoneInDaylightSaving(): bool
    {
        return $this->getField('time_zone.in_daylight_saving') ?? false;
    }

    // ------------------------------------------------------------------------
    // User-Agent Response Field Convenience Methods:
    // ------------------------------------------------------------------------

    /**
     * Get the raw User-Agent header.
     *
     * @return string|null The User-Agent header, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getUserAgentHeader(): ?string
    {
        return $this->getField('user_agent.header');
    }

    /**
     * Get the User-Agent name (browser name).
     *
     * @return string|null The User-Agent name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getUserAgentName(): ?string
    {
        return $this->getField('user_agent.name');
    }

    /**
     * Get the User-Agent type.
     *
     * @return string|null The User-Agent type, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getUserAgentType(): ?string
    {
        return $this->getField('user_agent.type');
    }

    /**
     * Get the User-Agent version.
     *
     * @return string|null The User-Agent version, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getUserAgentVersion(): ?string
    {
        return $this->getField('user_agent.version');
    }

    /**
     * Get the User-Agent major version.
     *
     * @return string|null The User-Agent major version, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getUserAgentVersionMajor(): ?string
    {
        return $this->getField('user_agent.version_major');
    }

    /**
     * Get the device brand.
     *
     * @return string|null The device brand, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getDeviceBrand(): ?string
    {
        return $this->getField('user_agent.device.brand');
    }

    /**
     * Get the device name.
     *
     * @return string|null The device name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getDeviceName(): ?string
    {
        return $this->getField('user_agent.device.name');
    }

    /**
     * Get the device type.
     *
     * @return string|null The device type, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getDeviceType(): ?string
    {
        return $this->getField('user_agent.device.type');
    }

    /**
     * Get the engine name.
     *
     * @return string|null The engine name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getEngineName(): ?string
    {
        return $this->getField('user_agent.engine.name');
    }

    /**
     * Get the engine type.
     *
     * @return string|null The engine type, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getEngineType(): ?string
    {
        return $this->getField('user_agent.engine.type');
    }

    /**
     * Get the engine version.
     *
     * @return string|null The engine version, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getEngineVersion(): ?string
    {
        return $this->getField('user_agent.engine.version');
    }

    /**
     * Get the OS name.
     *
     * @return string|null The OS name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getOsName(): ?string
    {
        return $this->getField('user_agent.os.name');
    }

    /**
     * Get the OS type.
     *
     * @return string|null The OS type, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getOsType(): ?string
    {
        return $this->getField('user_agent.os.type');
    }

    /**
     * Get the OS version.
     *
     * @return string|null The OS version, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/user-agent-fields
     */
    public function getOsVersion(): ?string
    {
        return $this->getField('user_agent.os.version');
    }

    // ------------------------------------------------------------------------
    // Autonomous System Response Field Convenience Methods:
    // ------------------------------------------------------------------------

    /**
     * Get the ASN allocation date and time (ISO 8601 format).
     *
     * @return string|null The allocation date and time, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnAllocated(): ?string
    {
        return $this->getField('allocated');
    }

    /**
     * Get the Autonomous System Number (ASN).
     *
     * @return int|null The ASN, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsn(): ?int
    {
        return $this->getField('asn');
    }

    /**
     * Get the ASN country code (ISO 3166-1 alpha-2).
     *
     * @return string|null The country code, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnCountryCode(): ?string
    {
        return $this->getField('country_code');
    }

    /**
     * Get the ASN domain.
     *
     * @return string|null The domain, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnDomain(): ?string
    {
        return $this->getField('domain');
    }

    /**
     * Get the ASN name (organization name).
     *
     * @return string|null The ASN name, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnName(): ?string
    {
        return $this->getField('name');
    }

        /**
     * Get the announced IPv4 prefixes.
     *
     * @return array The IPv4 prefixes, or an empty array if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnIpv4Prefixes(): array
    {
        return $this->getField('prefixes.ipv4') ?? [];
    }

    /**
     * Get the announced IPv6 prefixes.
     *
     * @return array The IPv6 prefixes, or an empty array if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnIpv6Prefixes(): array
    {
        return $this->getField('prefixes.ipv6') ?? [];
    }

    /**
     * Get the downstream ASNs.
     *
     * @return array The downstream ASNs, or an empty array if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnDownstreams(): array
    {
        return $this->getField('relationships.downstreams') ?? [];
    }

    /**
     * Get the peer ASNs.
     *
     * @return array The peer ASNs, or an empty array if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnPeers(): array
    {
        return $this->getField('relationships.peers') ?? [];
    }

    /**
     * Get the upstream ASNs.
     *
     * @return array The upstream ASNs, or an empty array if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnUpstreams(): array
    {
        return $this->getField('relationships.upstreams') ?? [];
    }

    /**
     * Get the ASN registry (RIR).
     *
     * @return string|null The registry, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnRegistry(): ?string
    {
        return $this->getField('registry');
    }

    /**
     * Get the ASN type.
     *
     * @return string|null The ASN type, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnType(): ?string
    {
        return $this->getField('type');
    }

    /**
     * Get the ASN last update date and time (ISO 8601 format).
     *
     * @return string|null The update date and time, or null if not available.
     * @see https://ipregistry.co/docs/hostname#contentresponses/autonomous-system-fields
     */
    public function getAsnUpdated(): ?string
    {
        return $this->getField('updated');
    }

    // ------------------------------------------------------------------------
    // Helper Method for Field Access (JSON and XML)
    // ------------------------------------------------------------------------

    /**
     *  Get a field value from the parsed response data.
     * 
     *  @param string $field  The field path using dot notation (e.g., 'location.country.code').
     * 
     *  @return mixed|null The field value, or null if not found.
     */
    private function getField(string $field)
    {
        if ($this->format === 'json') {
            return $this->getJsonField($field);
        } elseif ($this->format === 'xml') {
            return $this->getXmlField($field);
        }

        return null; // Or throw an exception for an invalid format
    }

    /**
     *  Get a field value from a JSON response.
     * 
     * @param string $field The field path using dot notation.
     * 
     * @return mixed|null The field value, or null if not found.
     */
    private function getJsonField(string $field)
    {
        $parts = explode('.', $field);
        $data = $this->data;

        foreach ($parts as $part) {
            if (is_array($data) && array_key_exists($part, $data)) {
                $data = $data[$part];
            } else {
                return null; 
            }
        }
        return $data; 
    }

    /**
     *  Get a field value from an XML response.
     * 
     * @param string $field  The field path using dot notation.
     * 
     * @return mixed|null The field value, or null if not found.
     */
    private function getXmlField(string $field)
    {
        $parts = explode('.', $field);
        $data = $this->data;

        foreach ($parts as $part) {
            if (isset($data->$part)) {
                $data = $data->$part; 
            } else {
                return null;
            }
        }
        return (string) $data; // Cast SimpleXMLElement to string
    }

    /**
     * Get the raw Guzzle response object.
     *
     * @return ResponseInterface|null The Guzzle response object, or null if not available.
     */
    public function raw(): ?ResponseInterface
    {
        return $this->response;
    }
}