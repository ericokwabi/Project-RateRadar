<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiLog;
use Symfony\Component\HttpFoundation\Response;

class LogApiResults
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $start) * 1000; // ms

        ApiLog::create([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'request_payload' => json_encode($this->filterSensitive($request->all())),
            'response_status' => $response->getStatusCode(),
            'response_payload' => $this->safeResponseBody($response),
            'user_id' => optional($request->user())->id,
            'organisation_id' => optional(optional($request->user())->organisation)->id,
            'ip_address' => $request->ip(),
            'duration_ms' => round($duration, 2),
        ]);

        return $response;
    }


    private function filterSensitive(array $data): array
    {
        $blocked = ['password', 'password_confirmation', 'token', 'hash'];

        foreach ($blocked as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '***hidden***';
            }
        }

        return $data;
    }

    private function safeResponseBody($response): ?string
    {
        $content = $response->getContent();

        // avoid storing huge blobs
        return strlen($content) > 5000
            ? substr($content, 0, 5000) . '...[truncated]'
            : $content;
    }
}
