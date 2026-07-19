<?php

namespace App\Http\Middleware;

use App\Models\SystemConfiguration;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAttendanceConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! SystemConfiguration::query()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance configuration has not been completed.',
            ], Response::HTTP_CONFLICT);
        }

        return $next($request);
    }
}
