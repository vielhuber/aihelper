<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use vielhuber\aihelper\aihelper;
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}
if (!isset($_GET['service']) || !isset($_GET['model']) || !isset($_GET['api_key']) || !isset($_GET['log'])) {
    die();
}

@unlink($_GET['log']);
$ai = aihelper::create(
    service: $_GET['service'],
    model: $_GET['model'],
    temperature: 1.0,
    api_key: $_SERVER[$_GET['api_key']],
    log: $_GET['log'],
    stream: true
);

$response = $ai->ask($_GET['prompt']);
