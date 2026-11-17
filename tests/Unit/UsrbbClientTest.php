<?php

namespace Tests\Unit;

use App\Services\Usrbb\UsrbbClient;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UsrbbClientTest extends TestCase
{
    public function test_it_sends_the_configured_cookie_to_the_base_url(): void
    {
        config([
            'services.usrbb.base_url' => 'https://usrbb.test',
            'services.usrbb.cookie' => 'session=abc123',
        ]);
        Http::fake(['usrbb.test/*' => Http::response(['ok' => true])]);

        $response = $this->app->make(UsrbbClient::class)->get('stations', ['page' => 2]);

        $this->assertTrue($response->json('ok'));
        Http::assertSent(fn (Request $request) => $request->url() === 'https://usrbb.test/stations?page=2'
            && $request->header('Cookie') === ['session=abc123']
            && $request->header('Accept') === ['application/json']);
    }

    public function test_it_omits_the_cookie_header_when_not_configured(): void
    {
        config(['services.usrbb.cookie' => null]);
        Http::fake(['*' => Http::response()]);

        $this->app->make(UsrbbClient::class)->post('stations', ['name' => 'A']);

        Http::assertSent(fn (Request $request) => ! $request->hasHeader('Cookie'));
    }

    public function test_it_throws_on_error_responses(): void
    {
        Http::fake(['*' => Http::response(status: 401)]);

        $this->expectException(RequestException::class);

        $this->app->make(UsrbbClient::class)->get('stations');
    }
}
