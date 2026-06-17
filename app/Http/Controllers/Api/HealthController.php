<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $health = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => [],
        ];

        try {
            DB::connection()->getPdo();
            $health['services']['database'] = 'ok';
        } catch (\Throwable $e) {
            $health['services']['database'] = 'error: '.$e->getMessage();
            $health['status'] = 'degraded';
        }

        try {
            Redis::ping();
            $health['services']['redis'] = 'ok';
        } catch (\Throwable $e) {
            $health['services']['redis'] = 'error: '.$e->getMessage();
            $health['status'] = 'degraded';
        }

        try {
            Cache::store()->put('health_check', true, 10);
            Cache::store()->get('health_check');
            $health['services']['cache'] = 'ok';
        } catch (\Throwable $e) {
            $health['services']['cache'] = 'error: '.$e->getMessage();
            $health['status'] = 'degraded';
        }

        $statusCode = $health['status'] === 'ok' ? 200 : 503;

        return response()->json($health, $statusCode);
    }
}
