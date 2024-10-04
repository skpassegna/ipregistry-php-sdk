<?php
// tests/bootstrap.php
require __DIR__ . '/../vendor/autoload.php'; // Autoload Composer dependencies

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); 
$dotenv->load();