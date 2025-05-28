<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

function getMongoDB() {
    $client = new Client("mongodb://localhost:27017");
    return $client->eduregistrar;
}
?>