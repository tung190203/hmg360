<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    use \App\Traits\DetectsBots;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $today = now()->toDateString();

        try {
            // Chỉ log nếu IP này chưa được ghi nhận trong ngày hôm nay
            $exists = \App\Models\VisitLog::where('ip_address', $ip)
                ->whereDate('created_at', $today)
                ->exists();

            if (!$exists) {
                $userAgent = $request->header('User-Agent', '');
                
                \App\Models\VisitLog::create([
                    'ip_address' => $ip,
                    'user_agent' => substr($userAgent, 0, 1000),
                    'is_bot'     => $this->isBot($userAgent),
                    // Bỏ log path và visitor_id theo yêu cầu
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to log visit: ' . $e->getMessage());
        }

        return $next($request);
    }

    // Removed local isBot method as it's now in DetectsBots trait
}

