<?php

include_once __DIR__ . '/../vendor/autoload.php';

use AmoCRM\Client\AmoCRMApiClient;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

function getClient(): AmoCRMApiClient
{
    return (new AmoCRMApiClient(
        $_ENV['CLIENT_ID'],
        $_ENV['CLIENT_SECRET'],
        $_ENV['REDIRECT_URL']
    ))->setAccountBaseDomain('egorkovalev.amocrm.ru');
}