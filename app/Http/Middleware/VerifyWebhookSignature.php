<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Signature');

        if (!$signature) {
            return response()->json([
                'error' => 'Missing Webhook Signature'
            ], 401);
        }

        $payload = $request->getContent();
        $key = config('services.fastapi.webhook_secret');
        $computed = hash_hmac('sha3-512', $payload, $key);

        if (!hash_equals($computed, $signature)) {
            return response()->json([
                'error' => 'Invalid Webhook Signature'
                ], 401);
        }

        return $next($request);
    }
}
