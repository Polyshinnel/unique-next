<?php

namespace Tests\Feature;

use App\Http\Middleware\ProtectHorizonWithBasicAuth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HorizonBasicAuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(ProtectHorizonWithBasicAuth::class)
            ->get('/_test/horizon-basic-auth', fn () => 'ok');
    }

    public function test_it_returns_unauthorized_without_credentials(): void
    {
        config([
            'horizon.basic_auth.username' => 'horizon',
            'horizon.basic_auth.password' => 'secret',
        ]);

        $response = $this->get('/_test/horizon-basic-auth');

        $response->assertUnauthorized();
        $response->assertHeader('WWW-Authenticate', 'Basic realm="Horizon"');
    }

    public function test_it_allows_access_with_valid_credentials(): void
    {
        config([
            'horizon.basic_auth.username' => 'horizon',
            'horizon.basic_auth.password' => 'secret',
        ]);

        $response = $this->get('/_test/horizon-basic-auth', [
            'PHP_AUTH_USER' => 'horizon',
            'PHP_AUTH_PW' => 'secret',
        ]);

        $response->assertOk();
        $response->assertSeeText('ok');
    }

    public function test_it_returns_forbidden_when_credentials_are_not_configured(): void
    {
        config([
            'horizon.basic_auth.username' => '',
            'horizon.basic_auth.password' => '',
        ]);

        $response = $this->get('/_test/horizon-basic-auth');

        $response->assertForbidden();
    }
}
