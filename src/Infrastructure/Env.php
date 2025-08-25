<?php

namespace Itsmng\Infrastructure;

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$envPath = __DIR__ . '/../../.env';

if (file_exists($envPath)) {
    $dotenv->load($envPath);
}
