<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiUserService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchUsersFromApi(): array
    {
        $response = $this->client->request('GET', 'https://dummyjson.com/users');
        if($response->getStatusCode() == 200) {
            return $response->toArray()['users'];
        }
        return [];
    }
}
