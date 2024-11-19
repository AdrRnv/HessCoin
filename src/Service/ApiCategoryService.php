<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiCategoryService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchCategoriesFromApi(): array
    {
        $response = $this->client->request('GET', 'https://dummyjson.com/products/categories');
        if($response->getStatusCode() == 200) {
            return $response->toArray();
        }
        return [];
    }
}
