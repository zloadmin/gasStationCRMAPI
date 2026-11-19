<?php

namespace App\Services\Usrbb;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * HTTP client for the usrbb.ru API.
 *
 * Every request carries the session cookie configured in services.usrbb.cookie.
 */
class UsrbbClient
{
    private ?string $cookie;
    private ?string $username;
    private ?string $passoword;

    public function __construct(
        private readonly string $baseUrl,
        ?string                 $cookie,
        private readonly int    $timeout = 30,
        ?string                 $username,
        ?string                 $password,
    )
    {
        $this->cookie = $cookie;
        $this->username = $username;
        $this->passoword = $password;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    private function login(): void
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson()
            ->post('/api/login', [
                'username' => $this->username,
                'password' => $this->passoword,
            ]);

        $response->throw();

        $cookie = $this->extractCookie($response);

        if (!$cookie) {
            throw new \RuntimeException(
                'USRBB login succeeded, but Set-Cookie header was not received.'
            );
        }

        $this->setCookie($cookie);

        $this->updateEnvCookie($cookie);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function loginIsNeeded(): void
    {

        if(!$this->check()) {
            $this->login();
        }
    }
    /**
     * Extract session cookie from response.
     */
    private function extractCookie(Response $response): ?string
    {
        $cookies = $response->header('Set-Cookie');

        if (!$cookies) {
            return null;
        }

        // Extract only the cookie name and value, without attributes.
        if (preg_match('/(?:^|,\s*)([^=;,]+=[^;]*)/', $cookies, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Update cookie in current client instance.
     */
    private function setCookie(string $cookie): void
    {
        $this->cookie = $cookie;
    }

    /**
     * Update USRBB_COOKIE in .env file.
     */
    private function updateEnvCookie(string $cookie): void
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            throw new \RuntimeException('.env file not found.');
        }

        $env = file_get_contents($path);

        if ($env === false) {
            throw new \RuntimeException('Unable to read .env file.');
        }

        $value = 'USRBB_COOKIE=' . json_encode($cookie, JSON_UNESCAPED_SLASHES);

        if (preg_match('/^USRBB_COOKIE=.*$/m', $env)) {
            $env = preg_replace(
                '/^USRBB_COOKIE=.*$/m',
                $value,
                $env
            );
        } else {
            $env .= PHP_EOL . $value . PHP_EOL;
        }

        if (file_put_contents($path, $env, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to update USRBB_COOKIE in .env.');
        }
    }


    /**
     * @param array<string, mixed> $query
     */
    private function get(string $uri, array $query = []): Response
    {
        return $this->request()->get($uri, $query)->throw();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function post(string $uri, array $data = []): Response
    {
        return $this->request()->post($uri, $data)->throw();
    }
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
            $request->withHeaders([ 'Cookie' => $this->cookie ]);
        }

        return $request;
    }

    public function getServices(int $page = 0, int $size = 100, $org_id = 57): array
    {
        return $this->get($this->getListPathByName('services'),
            compact('page', 'size', 'org_id'))->json();
    }

    public function getTerminals(int $page = 0, int $size = 100): array
    {
        return $this->get($this->getListPathByName('terminals'),
            compact('page', 'size'))->json();
    }

    public function getTransactions(int $page = 0, int $size = 100): array
    {
        return $this->get($this->getListPathByName('operations'),
            compact('page', 'size'))->json();
    }

    public function getTransaction(int $id): array
    {
        return $this->get($this->getPath('operations/list/' . $id))->json();
    }

    private function check(): bool
    {
        try {
            $result = $this->post($this->getPath('check'))->json();
        } catch (\Exception $e) {
            return false;
        }
        return boolval($result['ok'] ?? false);
    }

    private function getListPathByName(string $name): string
    {
        return sprintf('%s/api/%s/list', $this->baseUrl, $name);
    }

    private function getPath(string $name): string
    {
        return sprintf('%s/api/%s', $this->baseUrl, $name);
    }
}
