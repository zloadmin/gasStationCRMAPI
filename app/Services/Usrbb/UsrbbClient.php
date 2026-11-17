<?php

namespace App\Services\Usrbb;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * HTTP client for the usrbb.ru API.
 *
 * Every request carries the session cookie configured in services.usrbb.cookie.
 */
class UsrbbClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $cookie,
        private readonly int $timeout = 30,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     */
    private function get(string $uri, array $query = []): Response
    {
        return $this->request()->get($uri, $query)->throw();
    }
//
//    /**
//     * @param  array<string, mixed>  $data
//     */
//    public function post(string $uri, array $data = []): Response
//    {
//        return $this->request()->post($uri, $data)->throw();
//    }
//
//    /**
//     * @param  array<string, mixed>  $data
//     */
//    public function put(string $uri, array $data = []): Response
//    {
//        return $this->request()->put($uri, $data)->throw();
//    }
//
//    /**
//     * @param  array<string, mixed>  $data
//     */
//    public function delete(string $uri, array $data = []): Response
//    {
//        return $this->request()->delete($uri, $data)->throw();
//    }

    /**
     * Base request with URL, timeout, JSON accept header and the session cookie.
     */
    private function request(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson();

        if (filled($this->cookie)) {
            $request->withHeaders(['Cookie' => $this->cookie]);
        }

        return $request;
    }
    public function getServices(int $page = 0, int $size = 100, $org_id = 57) : array
    {
        return $this->get($this->getListPathByName('services'),
            compact('page', 'size', 'org_id'))->json();
    }
    public function getTerminals(int $page = 0, int $size = 100) : array
    {
        return $this->get($this->getListPathByName('terminals'),
            compact('page', 'size'))->json();
    }
    public function getTransactions(int $page = 0, int $size = 100) : array
    {
        return $this->get($this->getListPathByName('operations'),
            compact('page', 'size'))->json();
    }
    private function getListPathByName(string $name): string
    {
        return sprintf('%s/api/%s/list', $this->baseUrl, $name);
    }
}
