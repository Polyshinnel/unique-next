<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    public function test_health_endpoint_returns_ok_when_all_services_are_available(): void
    {
        DB::shouldReceive('connection')->once()->andReturnSelf();
        DB::shouldReceive('getPdo')->once()->andReturn(new \stdClass());
        Redis::shouldReceive('ping')->once()->andReturn('PONG');
        Cache::shouldReceive('store')->twice()->andReturnSelf();
        Cache::shouldReceive('put')->once()->with('health_check', true, 10);
        Cache::shouldReceive('get')->once()->with('health_check')->andReturn(true);

        $response = $this->getJson('/api/health');

        $response
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('services.database', 'ok')
            ->assertJsonPath('services.redis', 'ok')
            ->assertJsonPath('services.cache', 'ok');
    }

    public function test_health_endpoint_returns_degraded_when_a_service_fails(): void
    {
        DB::shouldReceive('connection')->once()->andReturnSelf();
        DB::shouldReceive('getPdo')->once()->andReturn(new \stdClass());
        Redis::shouldReceive('ping')->once()->andThrow(new \RuntimeException('Redis unavailable'));
        Cache::shouldReceive('store')->twice()->andReturnSelf();
        Cache::shouldReceive('put')->once()->with('health_check', true, 10);
        Cache::shouldReceive('get')->once()->with('health_check')->andReturn(true);

        $response = $this->getJson('/api/health');

        $response
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('services.database', 'ok')
            ->assertJsonPath('services.cache', 'ok')
            ->assertJsonPath('services.redis', 'error: Redis unavailable');
    }
}
