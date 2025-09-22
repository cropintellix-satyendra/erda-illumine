<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  
        
class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Insert initial log entry and get the ID
        $logId = DB::table('api_request_logs')->insertGetId([
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => json_encode($request->headers->all()),
            'request_data' => json_encode($request->all()),
            'user_id' => Auth::user()->id ?? null,
            'response_status' => null,
            'response_time' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->status == 0) {
                return response()->json(['error' => 'User is disabled'], 501);
            }
        }
        $response = $next($request);

                
        // Calculate response time
        $responseTime = round((microtime(true) - $startTime) * 1000, 3);
        
        // Get response content
        $responseContent = $response->getContent();
        $responseData = null;
        
        // Try to decode JSON response
        if ($responseContent) {
            $decodedResponse = json_decode($responseContent, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $responseData = $decodedResponse;
            } else {
                $responseData = $responseContent;
            }
        }
        
        // Update the same row with response data
        try {
            DB::table('api_request_logs')
                ->where('id', $logId)
                ->update([
                    'response_status' => $response->getStatusCode(),
                    'response_time' => $responseTime,
                    'response_data' => json_encode($responseData),
                    'updated_at' => now()
                ]);
        } catch (\Exception $e) {
            Log::error('Failed to update API request log: ' . $e->getMessage());
        }
        
        return $response;
    }
}
