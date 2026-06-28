<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectHorizonWithBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUsername = (string) config('horizon.basic_auth.username');
        $expectedPassword = (string) config('horizon.basic_auth.password');

        if ($expectedUsername === '' || $expectedPassword === '') {
            abort(403, 'Horizon basic auth credentials are not configured.');
        }

        $providedUsername = (string) $request->getUser();
        $providedPassword = (string) $request->getPassword();

        if (
            ! hash_equals($expectedUsername, $providedUsername)
            || ! hash_equals($expectedPassword, $providedPassword)
        ) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Horizon"',
            ]);
        }

        return $next($request);
    }
}
