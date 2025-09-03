<?php
namespace App\Api;
use GuzzleHttp\Client;

class FlightApi {
    private $client;
    private $apiKey;

    public function __construct() {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->client = new Client();
        $this->apiKey = $_ENV['API_KEY'];
    }

    public function fetchFlights($limit = 10) {
        $url = "http://api.aviationstack.com/v1/flights";

        $response = $this->client->get($url, [
            'query' => [
                'access_key' => $this->apiKey,
                'limit' => $limit
            ]
        ]);

        return json_decode($response->getBody(), true)['data'];
    }
}
