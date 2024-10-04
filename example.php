<?php


require_once 'vendor/autoload.php';


use Ipregistry\Sdk\Config;
use Ipregistry\Sdk\IpRegistryClient;
use Ipregistry\Sdk\Endpoint\IpLookup;

echo "<title>Hello Debug!</title>";

// Replace 'YOUR_API_KEY' with your actual Ipregistry API key.
$config = new Config('ira_C8zPReRfc1g1WzGiePjZGDqK4prkMJ2J97TD'); 
$client = new IpRegistryClient($config);
$ipLookup = new IpLookup($client);

$response = $ipLookup->lookup('8.8.8.8');

dd($response);