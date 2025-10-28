<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = base_path('auth_token.json');

        if (!file_exists($path)) {
            throw new \RuntimeException('RUN SETUP COMMAND');
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        $token = $data['token'] ?? null;

        if ($token == $request->header('token')) {
            return $next($request);
        }

        return response()->json(['error' => 'not auth'], Response::HTTP_UNAUTHORIZED);
    }
}
