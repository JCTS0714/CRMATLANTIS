<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestMetricsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('observability.request_metrics_enabled', false)) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');
        $ignoredPaths = config('observability.request_metrics_ignored_paths', []);

        foreach ($ignoredPaths as $pattern) {
            if (Str::is((string) $pattern, $path)) {
                return $next($request);
            }
        }

        $start = hrtime(true);
        $response = $next($request);
        $durationMs = (hrtime(true) - $start) / 1_000_000;

        $slowThreshold = (int) config('observability.request_metrics_slow_ms', 800);
        $sampleRate = (int) config('observability.request_metrics_sample_rate', 20);
        $sampleRate = max(1, min($sampleRate, 100));
        $isSlow = $durationMs >= $slowThreshold;
        $isSampled = $sampleRate === 100 || random_int(1, 100) <= $sampleRate;

        if (!$isSlow && !$isSampled) {
            return $response;
        }

        $route = $request->route();
        $routeName = $route?->getName();

        $context = [
            'method' => $request->getMethod(),
            'path' => '/' . $path,
            'route_name' => $routeName,
            'status' => $response->getStatusCode(),
            'duration_ms' => round($durationMs, 2),
            'is_slow' => $isSlow,
            'user_id' => $request->user()?->id,
            'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];

        $channel = (string) config('observability.request_metrics_channel', 'stack');

        if ($isSlow) {
            Log::channel($channel)->warning('request.metrics', $context);
        } else {
            Log::channel($channel)->info('request.metrics', $context);
        }

        return $response;
    }
}
