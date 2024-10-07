
[![Latest Stable Version](https://poser.pugx.org/skpassegna/ipregistry-php-sdk/v/stable)](https://packagist.org/packages/skpassegna/ipregistry-php-sdk)
[![Total Downloads](https://poser.pugx.org/skpassegna/ipregistry-php-sdk/downloads)](https://packagist.org/packages/skpassegna/ipregistry-php-sdk)
[![License](https://poser.pugx.org/skpassegna/ipregistry-php-sdk/license)](https://packagist.org/packages/skpassegna/ipregistry-php-sdk)

# 1. Introduction

The Ipregistry PHP SDK is an unofficial library that simplifies interaction with the Ipregistry API, enabling developers to easily access geolocation and threat data for IP addresses and Autonomous Systems (ASNs) within their PHP applications. 

**Key Features:**

* **Complete API Coverage:** Supports all Ipregistry API endpoints, including single and batch lookups for IPs and ASNs, origin lookups, and User-Agent parsing.
* **JSON and XML Support:** Handles responses in both JSON (default) and XML formats.
* **Robust Error Handling:** Throws specific exceptions for Ipregistry API error codes and network errors, aiding in debugging.
* **Convenience Methods:** Provides easy-to-use methods for accessing data fields from responses.
* **Composer Integration:** Installable and managed through Composer for easy dependency management.
* **PSR-4 Autoloading:** Adheres to PSR-4 autoloading standards for seamless integration into PHP projects.

# 2. Installation

The SDK is distributed as a Composer package. To install it, run the following command in your project's root directory:

```bash
composer require skpassegna/ipregistry-php-sdk
```

# 3. Getting Started

## 3.1. Obtain an API Key:

Sign up for a free Ipregistry account at [https://ipregistry.co](https://ipregistry.co) to obtain your API key.

## 3.2. Configure the SDK:

```php
use Ipregistry\Sdk\IpRegistryClient;
use Ipregistry\Sdk\Config;

// Replace 'YOUR_API_KEY' with your actual Ipregistry API key.
$config = new Config('YOUR_API_KEY');

// (Optional) Customize base URL, timeout, hostname lookup, and output format:
// $config = new Config(
//     'YOUR_API_KEY', 
//     Ipregistry\Sdk\Constants::EU_BASE_URL, // Use EU base URL
//     10,                                   // Set timeout to 10 seconds
//     true,                                  // Enable hostname lookup
//     'xml'                                 // Set output format to XML
// );

$client = new IpRegistryClient($config);
```

# 4. Usage: Endpoints and Methods

The SDK provides separate classes for each API endpoint group:

* `Ipregistry\Sdk\Endpoint\IpLookup`:  Handles IP address lookups.
* `Ipregistry\Sdk\Endpoint\ASNLookup`:  Handles ASN lookups.
* `Ipregistry\Sdk\Endpoint\UserAgentParsing`:  Handles User-Agent parsing.

## 4.1. IP Address Lookup (`IpLookup`)

*   **Single IP Lookup:**

    ```php
    $ipLookup = new IpLookup($client);
    $response = $ipLookup->lookup('8.8.8.8');

    echo "IP Address: " . $response->getIp() . PHP_EOL;
    echo "Country: " . $response->getCountryName() . PHP_EOL;
    echo "City: " . $response->getCity() . PHP_EOL;
    // ... Access other IP-related data fields
    ```

*   **Batch IP Lookup:**

    ```php
    $ips = ['8.8.8.8', '1.1.1.1', '2001:4860:4860::8888'];
    $batchResponse = $ipLookup->batchLookup($ips);

    foreach ($batchResponse->getData()['results'] as $result) {
        echo "IP: " . $result['ip'] . ", Country: " . $result['location']['country']['name'] . PHP_EOL;
    }
    ```

*   **Origin IP Lookup:**

    ```php
    $originResponse = $ipLookup->originLookup();

    echo "Originating IP: " . $originResponse->getIp() . PHP_EOL;
    ```

## 4.2. ASN Lookup (`ASNLookup`)

*   **Single ASN Lookup:**

    ```php
    $asnLookup = new ASNLookup($client);
    $response = $asnLookup->lookup(15169);

    echo "ASN Name: " . $response->getAsnName() . PHP_EOL;
    // ... Access other ASN-related data fields
    ```

*   **Batch ASN Lookup:**

    ```php
    $asns = [15169, 20940];
    $batchResponse = $asnLookup->batchLookup($asns);

    foreach ($batchResponse->getData()['results'] as $result) {
        echo "ASN: " . $result['asn'] . ", Name: " . $result['name'] . PHP_EOL;
    }
    ```

*   **Origin ASN Lookup:**

    ```php
    $originResponse = $asnLookup->originLookup();

    echo "Originating ASN: " . $originResponse->getAsn() . PHP_EOL;
    ```

## 4.3. User-Agent Parsing (`UserAgentParsing`)

*   **Single User-Agent Parsing:**

    ```php
    $userAgentParsing = new UserAgentParsing($client);
    $response = $userAgentParsing->parse('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

    echo "Browser: " . $response->getUserAgentName() . PHP_EOL;
    echo "Operating System: " . $response->getOsName() . PHP_EOL;
    // ... Access other User-Agent related data fields
    ```

*   **Batch User-Agent Parsing:**

    ```php
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1'
    ];
    $batchResponse = $userAgentParsing->batchParse($userAgents);

    foreach ($batchResponse->getData()['results'] as $result) {
        echo "User-Agent: " . $result['user_agent']['header'] . ", Browser: " . $result['user_agent']['name'] . PHP_EOL;
    }
    ```

*   **Origin User-Agent Parsing:**

    ```php
    $originResponse = $userAgentParsing->originParse();

    echo "Originating User-Agent: " . $originResponse->getUserAgentHeader() . PHP_EOL;
    ```

# 5. Accessing Response Data

The `Ipregistry\Sdk\Response` class handles API responses and provides methods for accessing data:

*   **`getData()`:** Returns the parsed response data as an array (for JSON) or an object (for XML).
*   **`getRawResponse()`:** Returns the raw Guzzle `ResponseInterface` object.
*   **Convenience Methods (Getters):**  The `Response` class provides a comprehensive set of convenience methods (getters) for accessing specific data fields from the API responses. These methods follow a consistent naming convention:

    *   **`get[FieldName]()`:** Used for most fields, where `[FieldName]` is the CamelCase representation of the field name in the API response.
        *   *Examples:* `getIp()`, `getCountryCode()`, `getTimeZoneId()`, `getUserAgentName()`.
    *   **`is[FieldName]()`:** Used for boolean fields, where `[FieldName]` is the CamelCase representation of the field name.
        *   *Examples:* `isInEu()`, `isVpn()`, `isTor()`.
    *   **`raw()`:** Returns the raw Guzzle `ResponseInterface` object.

**Example:**

```php
$response = $ipLookup->lookup('8.8.8.8');

// Access data using getData()
$data = $response->getData();
echo "Country Code: " . $data['location']['country']['code'] . PHP_EOL; // Assuming JSON format

// Access data using convenience methods
echo "Country Name: " . $response->getCountryName() . PHP_EOL;
echo "Is in EU: " . ($response->isInEu() ? 'Yes' : 'No') . PHP_EOL;
```

Refer to the `Ipregistry\Sdk\Response` class documentation for a complete list of available convenience methods.

# 6. Error Handling

The SDK uses a structured exception system to handle errors:

*   **`Ipregistry\Sdk\Exception\IpregistryException`:** The base exception class for all SDK-specific errors.
*   **Specific Exception Classes:**  Subclasses of `IpregistryException` are thrown for specific Ipregistry API error codes. These include:
    *   `InvalidApiKeyException`
    *   `InsufficientCreditsException`
    *   `BadRequestException`
    *   `ForbiddenIpException`
    *   `ForbiddenOriginException`
    *   `ForbiddenIpOriginException`
    *   `InvalidAsnException`
    *   `InvalidIpAddressException`
    *   `MissingApiKeyException`
    *   `ReservedAsnException`
    *   `ReservedIpAddressException`
    *   `TooManyAsnsException`
    *   `TooManyIpsException`
    *   `TooManyRequestsException`
    *   `TooManyUserAgentsException`
    *   `UnknownAsnException`
*   **Guzzle Exceptions:** Network-related exceptions from Guzzle (e.g., `ConnectException`, `RequestException`) are thrown for connectivity issues or request failures.

**Exception Properties:**

*   `$errorCode`: The Ipregistry API error code (if available).
*   `$resolution`: A suggestion from the Ipregistry API on how to resolve the error (if available).

**Example Error Handling:**

```php
try {
    $response = $ipLookup->lookup('invalid-ip');
} catch (Ipregistry\Sdk\Exception\InvalidIpAddressException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Error Code: " . $e->getErrorCode() . PHP_EOL;
    echo "Resolution: " . $e->getResolution() . PHP_EOL; 
} catch (Exception $e) {
    // Handle other exceptions.
}
```

# 7. Configuration Options

The SDK's behavior can be customized using the `Ipregistry\Sdk\Config` class:

*   **`apiKey` (required):** Your Ipregistry API key.
*   **`baseUrl` (optional):** The Ipregistry API base URL. Defaults to `https://api.ipregistry.co`. You can use constants from `Ipregistry\Sdk\Constants` to set regional base URLs (e.g., `Constants::EU_BASE_URL`).
*   **`timeout` (optional):** Request timeout in seconds. Defaults to 5.
*   **`hostname` (optional):** Enable hostname lookup for IP address lookups. Defaults to `false`.
*   **`format` (optional):** The desired output format. Accepts `'json'` (default) or `'xml'`.

**Example Configuration:**

```php
$config = new Config(
    'YOUR_API_KEY', 
    Ipregistry\Sdk\Constants::EU_BASE_URL, // Use EU base URL
    10,                                   // Set timeout to 10 seconds
    true,                                  // Enable hostname lookup
    'xml'                                 // Set output format to XML
);
```

# 8. Testing (not completed)

The SDK includes a comprehensive suite of unit tests using PHPUnit to ensure its reliability and correctness. The tests cover all endpoints, methods, and error scenarios.

**Running Tests:**

From the project's root directory, run:

```bash
./vendor/bin/phpunit
```

# 9. Contributing

Contributions to the Ipregistry PHP SDK are welcome! Please open an issue or submit a pull request on GitHub.

# 10. License

This SDK is licensed under the GPL-3.0-or-later License. 
