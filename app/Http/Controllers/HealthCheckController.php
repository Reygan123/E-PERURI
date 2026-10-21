<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends Controller
{
    /**
     * Check the application health status.
     */
    public function __invoke(): JsonResponse
    {
        $dbStatus = 'ok';
        $dbError = null;

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $dbStatus = 'error';
            $dbError = $e->getMessage();
        }

        $status = $dbStatus === 'ok' ? 'ok' : 'degraded';

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'app' => config('app.name'),
            'database' => [
                'status' => $dbStatus,
                'connection' => config('database.default'),
                'error' => $dbError,
            ],
        ], $status === 'ok' ? 200 : 503);
    }
}
