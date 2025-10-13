<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;

class ApiLogController extends Controller
{
    /**
     * Display a listing of API logs
     */
    public function index(Request $request)
    {
        // Start with basic query - no eager loading initially
        $query = ApiRequestLog::query();

        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by status code
        if ($request->filled('status')) {
            $query->where('response_status', $request->status);
        }

        // Filter by status category
        if ($request->filled('status_category')) {
            switch($request->status_category) {
                case 'success':
                    $query->whereBetween('response_status', [200, 299]);
                    break;
                case 'client_error':
                    $query->whereBetween('response_status', [400, 499]);
                    break;
                case 'server_error':
                    $query->whereBetween('response_status', [500, 599]);
                    break;
                case 'redirect':
                    $query->whereBetween('response_status', [300, 399]);
                    break;
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Filter by time range (today, yesterday, last 7 days, etc.)
        if ($request->filled('time_range')) {
            switch($request->time_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'last_7_days':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
                case 'last_30_days':
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
                case 'last_hour':
                    $query->where('created_at', '>=', now()->subHour());
                    break;
            }
        }

        // Search by URL
        if ($request->filled('search')) {
            $query->where('url', 'like', '%' . $request->search . '%');
        }

        // Filter by API version
        if ($request->filled('api_version')) {
            $query->where('url', 'like', '%/api/' . $request->api_version . '/%');
        }

        // Filter by extracted string
        if ($request->filled('extracted_string')) {
            $query->where(function($q) use ($request) {
                $searchTerm = $request->extracted_string;
                $q->where('url', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by endpoint type
        if ($request->filled('endpoint_type')) {
            switch($request->endpoint_type) {
                case 'auth':
                    $query->where('url', 'like', '%login%')->orWhere('url', 'like', '%auth%');
                    break;
                case 'cropdata':
                    $query->where('url', 'like', '%cropdata%');
                    break;
                case 'pipe':
                    $query->where('url', 'like', '%pipe%');
                    break;
                case 'polygon':
                    $query->where('url', 'like', '%polygon%')->orWhere('url', 'like', '%polyon%');
                    break;
                case 'farmer':
                    $query->where('url', 'like', '%farmer%');
                    break;
                case 'settings':
                    $query->where('url', 'like', '%setting%');
                    break;
                case 'aeration':
                    $query->where('url', 'like', '%aeration%');
                    break;
                case 'aeration_images':
                    $query->where('url', 'like', '%aeration%')->where('url', 'like', '%image%');
                    break;
            }
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        // Filter by response time
        if ($request->filled('response_time_min')) {
            $query->where('response_time', '>=', $request->response_time_min);
        }
        if ($request->filled('response_time_max')) {
            $query->where('response_time', '<=', $request->response_time_max);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate with smaller default
        $perPage = $request->get('per_page', 25);
        $logs = $query->paginate($perPage);

        // Only load user relationships for displayed logs
        $logs->load('user');

        // Get unique methods and status codes for filters (cached)
        $methods = cache()->remember('api_logs_methods', 300, function() {
            return ApiRequestLog::distinct()->pluck('method');
        });
        
        $statusCodes = cache()->remember('api_logs_status_codes', 300, function() {
            return ApiRequestLog::distinct()->whereNotNull('response_status')->pluck('response_status')->sort();
        });

        // Get statistics
        $stats = cache()->remember('api_logs_stats', 300, function() {
            return [
                'total' => ApiRequestLog::count(),
                'today' => ApiRequestLog::whereDate('created_at', today())->count(),
                'errors' => ApiRequestLog::where('response_status', '>=', 400)->count(),
                'success' => ApiRequestLog::whereBetween('response_status', [200, 299])->count(),
            ];
        });

        $action = 'api_logs_index';

        return view('admin.api_logs.index', compact('logs', 'methods', 'statusCodes', 'stats', 'action'));
    }

    /**
     * Display the specified API log
     */
    public function show($id)
    {
        $log = ApiRequestLog::with('user')->findOrFail($id);
        $action = 'api_logs_show';
        
        return view('admin.api_logs.show', compact('log', 'action'));
    }

    /**
     * Delete old API logs
     */
    public function deleteOld(Request $request)
    {
        $days = $request->get('days', 30);
        $deleted = ApiRequestLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->back()->with('success', "$deleted logs deleted successfully.");
    }

    /**
     * Delete a specific API log
     */
    public function destroy($id)
    {
        $log = ApiRequestLog::findOrFail($id);
        $log->delete();

        return redirect()->route('admin.api-logs.index')->with('success', 'Log deleted successfully.');
    }

    /**
     * Extract meaningful string from URL
     */
    public static function extractStringFromUrl($url)
    {
        $extractedString = '';
        
        // Extract different patterns from URL
        if (preg_match('/\/api\/v1\/([^\/\?]+)(?:\/([^\/\?]+))?(?:\/([^\/\?]+))?/i', $url, $matches)) {
            $parts = array_filter(array_slice($matches, 1)); // Remove full match, keep only groups
            $extractedString = implode(' / ', $parts);
        } elseif (preg_match('/\/api\/V1\/([^\/\?]+)(?:\/([^\/\?]+))?(?:\/([^\/\?]+))?/i', $url, $matches)) {
            $parts = array_filter(array_slice($matches, 1)); // Remove full match, keep only groups
            $extractedString = implode(' / ', $parts);
        } elseif (preg_match('/\/admin\/([^\/\?]+)(?:\/([^\/\?]+))?(?:\/([^\/\?]+))?/', $url, $matches)) {
            $parts = array_filter(array_slice($matches, 1)); // Remove full match, keep only groups
            $extractedString = implode(' / ', $parts);
        } elseif (preg_match('/\/carbonintellix\/public\/([^\/\?]+)(?:\/([^\/\?]+))?(?:\/([^\/\?]+))?/', $url, $matches)) {
            $parts = array_filter(array_slice($matches, 1)); // Remove full match, keep only groups
            $extractedString = implode(' / ', $parts);
        } elseif (preg_match('/\/public\/([^\/\?]+)(?:\/([^\/\?]+))?(?:\/([^\/\?]+))?/', $url, $matches)) {
            $parts = array_filter(array_slice($matches, 1)); // Remove full match, keep only groups
            $extractedString = implode(' / ', $parts);
        } elseif (preg_match('/\/([^\/\?]+)\/([^\/\?]+)/', $url, $matches)) {
            $extractedString = $matches[1] . ' / ' . $matches[2];
        } elseif (preg_match('/\/([^\/\?]+)/', $url, $matches)) {
            $extractedString = $matches[1];
        }
        
        // Clean up the extracted string
        $extractedString = str_replace(['-', '_'], ' ', $extractedString);
        $extractedString = ucwords($extractedString);
        
        return $extractedString;
    }
}

