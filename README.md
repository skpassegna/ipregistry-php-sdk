# Ipregistry PHP SDK (Unofficial)

[![Latest Stable Version](https://poser.pugx.org/skpassegna/ipregistry-php-sdk/v/stable)](https://packagist.org/packages/skpassegna/ipregistry-php-sdk)
[![Total Downloads](https://poser.pugx.org/skpassegna/ipregistry-php-sdk/downloads)](https://packagist.org/packages/skpassegna/ipregistry-php-sdk)
[![License](https://poser.pugx.org/skpassegna/ipregistry-php-sdk/license)](https://packagist.org/packages/skpassegna/ipregistry-php-sdk)

This is an unofficial PHP SDK for interacting with the [Ipregistry API](https://ipregistry.co), providing a convenient and developer-friendly way to access geolocation and threat data for IP addresses and Autonomous Systems (ASNs).

## Features

* **Complete API Coverage:** Supports all Ipregistry API endpoints, including:
    * Single IP Lookup
    * Batch IP Lookup (up to 1024 IPs per request)
    * Origin IP Lookup
    * Single ASN Lookup
    * Batch ASN Lookup (up to 16 ASNs per request)
    * Origin ASN Lookup
    * User-Agent Parsing 
    * Origin User-Agent Parsing
* **JSON and XML Support:**  Handles responses in both JSON (default) and XML formats.
* **Robust Error Handling:**  Throws specific exceptions for all Ipregistry API error codes, network errors, and other potential issues, making debugging easier.
* **Convenience Methods:**  Provides easy-to-use methods for accessing data fields from responses.
* **Composer Integration:**  Easily installable and manageable through Composer. 
* **PSR-4 Autoloading:**  Follows PSR-4 autoloading standards. 

## Installation

Install the SDK using Composer:

```bash
composer require skpassegna/ipregistry-php-sdk
```

## Getting Started

1. **Get Your API Key:** Sign up for a free account at [https://ipregistry.co](https://ipregistry.co) to obtain your API key.

2. **Configure the SDK:**

   ```php
   use Ipregistry\Sdk\IpRegistryClient;
   use Ipregistry\Sdk\Config;

   // Replace 'YOUR_API_KEY' with your actual Ipregistry API key.
   $config = new Config('YOUR_API_KEY'); 

   // You can customize the base URL, timeout, hostname lookup, and output format:
   // $config = new Config('YOUR_API_KEY', Ipregistry\Sdk\Constants::EU_BASE_URL, 10, true, 'xml'); 

   $client = new IpRegistryClient($config);
   ```

## Using the SDK: Endpoints and Methods

### IP Address Lookup

```php
use Ipregistry\Sdk\Endpoint\IpLookup;

$ipLookup = new IpLookup($client);

// Single IP Lookup
$response = $ipLookup->lookup('8.8.8.8'); 

echo "IP: " . $response->getIp() . PHP_EOL;
echo "Country: " . $response->getCountryName() . PHP_EOL;
echo "City: " . $response->getCity() . PHP_EOL;
echo "Time Zone: " . $response->getTimeZoneId() . PHP_EOL;
// ... (Access other data fields using convenience methods)

// Batch IP Lookup
$ips = ['8.8.8.8', '1.1.1.1'];
$batchResponse = $ipLookup->batchLookup($ips);

foreach ($batchResponse->getData()['results'] as $result) {
    echo "IP: " . $result['ip'] . ", Country: " . $result['location']['country']['name'] . PHP_EOL;
}

// Origin IP Lookup 
$originResponse = $ipLookup->originLookup();

echo "Originating IP: " . $originResponse->getIp() . PHP_EOL;
```

### ASN Lookup

```php
use Ipregistry\Sdk\Endpoint\ASNLookup;

$asnLookup = new ASNLookup($client);

// Single ASN Lookup
$asnResponse = $asnLookup->lookup(15169);

echo "ASN Name: " . $asnResponse->getAsnName() . PHP_EOL;
echo "ASN Type: " . $asnResponse->getAsnType() . PHP_EOL;
// ... (Access other ASN data fields)

// Batch ASN Lookup
$asns = [15169, 20940];
$batchAsnResponse = $asnLookup->batchLookup($asns);

foreach ($batchAsnResponse->getData()['results'] as $result) {
    echo "ASN: " . $result['asn'] . ", Name: " . $result['name'] . PHP_EOL;
}

// Origin ASN Lookup
$originAsnResponse = $asnLookup->originLookup();

echo "Originating ASN: " . $originAsnResponse->getAsn() . PHP_EOL;
```

### User-Agent Parsing

```php
use Ipregistry\Sdk\Endpoint\UserAgentParsing;

$userAgentParsing = new UserAgentParsing($client);

// Single User-Agent Parsing
$userAgentResponse = $userAgentParsing->parse('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

echo "Browser: " . $userAgentResponse->getUserAgentName() . PHP_EOL;
echo "OS: " . $userAgentResponse->getOsName() . PHP_EOL;
echo "Device Type: " . $userAgentResponse->getDeviceType() . PHP_EOL;
// ... (Access other User-Agent data fields)

// Batch User-Agent Parsing
$userAgents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1'
];

$batchUserAgentResponse = $userAgentParsing->batchParse($userAgents);

foreach ($batchUserAgentResponse->getData()['results'] as $result) {
    echo "User-Agent: " . $result['user_agent']['header'] . ", Browser: " . $result['user_agent']['name'] . PHP_EOL;
}

// Origin User-Agent Parsing
$originUserAgentResponse = $userAgentParsing->originParse();

echo "Originating User-Agent: " . $originUserAgentResponse->getUserAgentHeader() . PHP_EOL;
```

## Accessing Response Data: Convenience Methods

The `Ipregistry\Sdk\Response` class provides a set of convenience methods (getters) for accessing specific data fields from the API responses. These methods follow a consistent naming convention:

* **`get[FieldName]()`:**  Used for most fields, where `[FieldName]` is the CamelCase representation of the field name in the API response.
    * *Examples:* `getIp()`, `getCountryCode()`, `getTimeZoneId()`, `getUserAgentName()`.
* **`is[FieldName]()`:** Used for boolean fields, where `[FieldName]` is the CamelCase representation of the field name.
    * *Examples:* `isInEu()`, `isVpn()`, `isTor()`. 

**Example:**

To get the country name from an IP lookup response:

```php
$response = $ipLookup->lookup('8.8.8.8');
$countryName = $response->getCountryName();
```

Refer to the `Ipregistry\Sdk\Response` class documentation for a complete list of available convenience methods and the data fields they return.

## Error Handling

The SDK throws specific exceptions for various error conditions, providing informative messages and resolution suggestions (when available from the API):

* **`Ipregistry\Sdk\Exception\InvalidApiKeyException`:** Thrown when an invalid or disabled API key is used.
    * *Example Message:* "The provided API key is invalid or disabled: INVALID_API_KEY."
* **`Ipregistry\Sdk\Exception\InsufficientCreditsException`:**  Thrown when your account has insufficient credits. 
    * *Example Message:* "The API request failed due to insufficient credits: INSUFFICIENT_CREDITS."
* **`Ipregistry\Sdk\Exception\BadRequestException`:** Thrown for malformed requests or invalid input parameters.
    * *Example Message:* "The request was malformed or contained invalid parameters: INVALID_IP_ADDRESS."
* **`Ipregistry\Sdk\Exception\ForbiddenIpException`:** Thrown when the request is blocked due to IP filtering. 
    * *Example Message:* "The request was forbidden because your IP address is not allowed: FORBIDDEN_IP."
* **`Ipregistry\Sdk\Exception\ForbiddenOriginException`:** Thrown when the request is blocked due to origin filtering. 
    * *Example Message:* "The request was forbidden because your origin is not allowed: FORBIDDEN_ORIGIN."
* **`Ipregistry\Sdk\Exception\ForbiddenIpOriginException`:** Thrown when both IP and origin filtering block the request. 
    * *Example Message:* "The request was forbidden because your IP address and origin are not allowed: FORBIDDEN_IP_ORIGIN." 
* **`Ipregistry\Sdk\Exception\InvalidAsnException`:** Thrown when an invalid ASN is provided.
    * *Example Message:* "The provided ASN is invalid: INVALID_ASN." 
* **`Ipregistry\Sdk\Exception\InvalidIpAddressException`:** Thrown when an invalid IP address is provided.
    * *Example Message:* "The provided IP address is invalid: INVALID_IP_ADDRESS."
* **`Ipregistry\Sdk\Exception\MissingApiKeyException`:** Thrown when an API key is required but not provided.
    * *Example Message:* "An API key is required to make requests: MISSING_API_KEY." 
* **`Ipregistry\Sdk\Exception\ReservedAsnException`:** Thrown when attempting to look up a reserved ASN.
    * *Example Message:* "The provided ASN is reserved and cannot be looked up: RESERVED_ASN."
* **`Ipregistry\Sdk\Exception\ReservedIpAddressException`:** Thrown when attempting to look up a reserved IP address.
    * *Example Message:* "The provided IP address is reserved and cannot be looked up: RESERVED_IP_ADDRESS."
* **`Ipregistry\Sdk\Exception\TooManyAsnsException`:** Thrown when exceeding the ASN limit in a batch request. 
    * *Example Message:* "Too many ASNs were provided in the batch request: TOO_MANY_ASNS."
* **`Ipregistry\Sdk\Exception\TooManyIpsException`:** Thrown when exceeding the IP address limit in a batch request.
    * *Example Message:* "Too many IP addresses were provided in the batch request: TOO_MANY_IPS."
* **`Ipregistry\Sdk\Exception\TooManyRequestsException`:** Thrown when exceeding the API rate limit.
    * *Example Message:* "The API rate limit has been exceeded: TOO_MANY_REQUESTS." 
* **`Ipregistry\Sdk\Exception\TooManyUserAgentsException`:** Thrown when exceeding the User-Agent limit in a batch request.
    * *Example Message:* "Too many User-Agents were provided in the batch request: TOO_MANY_USER_AGENTS."
* **`Ipregistry\Sdk\Exception\UnknownAsnException`:** Thrown when the requested ASN is not found. 
    * *Example Message:* "The requested ASN was not found: UNKNOWN_ASN."
* **Network-related exceptions from Guzzle:**  (e.g., `ConnectException`, `RequestException`). These are thrown for network connectivity issues or general request errors.

**Example Error Handling:**

```php
try {
    $response = $ipLookup->lookup('invalid-ip');
} catch (Ipregistry\Sdk\Exception\InvalidIpAddressException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Resolution: " . $e->getResolution() . PHP_EOL; 
} catch (Exception $e) {
    // Handle other exceptions.
}
```

## Additional Configuration Options

### Base URL

You can configure the SDK to use a different Ipregistry API base URL, such as the EU base URL:

```php
$config = new Config('YOUR_API_KEY', Ipregistry\Sdk\Constants::EU_BASE_URL); 
$client = new IpRegistryClient($config);
```

See `Ipregistry\Sdk\Constants` for available base URL constants.

### Timeout

Set a custom request timeout (in seconds):

```php
$config = new Config('YOUR_API_KEY', Ipregistry\Sdk\Constants::DEFAULT_BASE_URL, 10); // Timeout of 10 seconds
$client = new IpRegistryClient($config);
```

### Hostname Lookup

Enable hostname lookup for IP address lookups:

```php
$config = new Config('YOUR_API_KEY', Ipregistry\Sdk\Constants::DEFAULT_BASE_URL, 5, true); // Enable hostname lookup
$client = new IpRegistryClient($config);
```

### Output Format

Set the desired output format (JSON or XML):

```php
$config = new Config('YOUR_API_KEY', Ipregistry\Sdk\Constants::DEFAULT_BASE_URL, 5, false, 'xml'); // Set output to XML
$client = new IpRegistryClient($config);
```

## Documentation

[Full API reference documentation](https://ipregistry.co/docs/).

## Contributing

Contributions are welcome! Please open an issue or submit a pull request on GitHub.

## License

This SDK is licensed under the [GNU General Public License v3.0](LICENSE) License.