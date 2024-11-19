<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiProductService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchProductsFromApi(): array
    {
        $response = $this->client->request('GET', 'https://dummyjson.com/products');
        if($response->getStatusCode() == 200) {
            return $response->toArray()['products'];
        }
        return [];
    }
}
