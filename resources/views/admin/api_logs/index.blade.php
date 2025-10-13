@extends('layout.default')

@section('styles')
<style>
/* Force hide preloader immediately */
#preloader {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
}

/* Ensure main content is visible */
body {
    visibility: visible !important;
    opacity: 1 !important;
}

#main-wrapper {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.content-body {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Loading indicator -->
    <div id="loading-indicator" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: rgba(255,255,255,0.9); padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="mt-2">Loading API Logs...</div>
        </div>
    </div>

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>API Request Logs</h4>
                <p class="mb-0">View and filter API request logs</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">API Logs</a></li>
            </ol>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            <p class="mb-0 text-muted">Total Requests</p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <h4 class="mb-0 text-success">{{ number_format($stats['today']) }}</h4>
                            <p class="mb-0 text-muted">Today's Requests</p>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <h4 class="mb-0 text-success">{{ number_format($stats['success']) }}</h4>
                            <p class="mb-0 text-muted">Successful (2xx)</p>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <h4 class="mb-0 text-danger">{{ number_format($stats['errors']) }}</h4>
                            <p class="mb-0 text-muted">Errors (4xx/5xx)</p>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Filter Logs</h4>
                    <button class="btn btn-sm btn-primary" type="button" data-toggle="collapse" data-target="#filterCollapse">
                        <i class="fas fa-filter"></i> Toggle Filters
                    </button>
                </div>
                <div class="card-body">
                    <div class="collapse show" id="filterCollapse">
                        <form method="GET" action="{{ route('admin.api-logs.index') }}">
                            <div class="row">
                                <!-- Row 1: Basic Filters -->
                                <div class="col-md-3 mb-3">
                                    <label>Search URL</label>
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Enter URL...">
                                </div>
                                
                                <div class="col-md-2 mb-3">
                                    <label>Method</label>
                                    <select class="form-control" name="method">
                                        <option value="">All Methods</option>
                                        @foreach($methods as $method)
                                            <option value="{{ $method }}" {{ request('method') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label>Status Category</label>
                                    <select class="form-control" name="status_category">
                                        <option value="">All Categories</option>
                                        <option value="success" {{ request('status_category') == 'success' ? 'selected' : '' }}>Success (2xx)</option>
                                        <option value="client_error" {{ request('status_category') == 'client_error' ? 'selected' : '' }}>Client Error (4xx)</option>
                                        <option value="server_error" {{ request('status_category') == 'server_error' ? 'selected' : '' }}>Server Error (5xx)</option>
                                        <option value="redirect" {{ request('status_category') == 'redirect' ? 'selected' : '' }}>Redirect (3xx)</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label>Status Code</label>
                                    <select class="form-control" name="status">
                                        <option value="">All Status</option>
                                        @foreach($statusCodes as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Time Range</label>
                                    <select class="form-control" name="time_range">
                                        <option value="">All Time</option>
                                        <option value="last_hour" {{ request('time_range') == 'last_hour' ? 'selected' : '' }}>Last Hour</option>
                                        <option value="today" {{ request('time_range') == 'today' ? 'selected' : '' }}>Today</option>
                                        <option value="yesterday" {{ request('time_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                        <option value="last_7_days" {{ request('time_range') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                        <option value="last_30_days" {{ request('time_range') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                    </select>
                                </div>

                                <!-- Row 2: Advanced Filters -->
                                <div class="col-md-3 mb-3">
                                    <label>API Version</label>
                                    <select class="form-control" name="api_version">
                                        <option value="">All Versions</option>
                                        <option value="V1" {{ request('api_version') == 'V1' ? 'selected' : '' }}>V1</option>
                                        <option value="v2" {{ request('api_version') == 'v2' ? 'selected' : '' }}>v2</option>
                                        <option value="v3" {{ request('api_version') == 'v3' ? 'selected' : '' }}>v3</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Endpoint Search</label>
                                    <input type="text" class="form-control" name="extracted_string" value="{{ request('extracted_string') }}" placeholder="Search endpoint...">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Endpoint Type</label>
                                    <select class="form-control" name="endpoint_type">
                                        <option value="">All Endpoints</option>
                                        <option value="auth" {{ request('endpoint_type') == 'auth' ? 'selected' : '' }}>Authentication</option>
                                        <option value="cropdata" {{ request('endpoint_type') == 'cropdata' ? 'selected' : '' }}>Crop Data</option>
                                        <option value="pipe" {{ request('endpoint_type') == 'pipe' ? 'selected' : '' }}>Pipe</option>
                                        <option value="polygon" {{ request('endpoint_type') == 'polygon' ? 'selected' : '' }}>Polygon</option>
                                        <option value="farmer" {{ request('endpoint_type') == 'farmer' ? 'selected' : '' }}>Farmer</option>
                                        <option value="settings" {{ request('endpoint_type') == 'settings' ? 'selected' : '' }}>Settings</option>
                                        <option value="aeration" {{ request('endpoint_type') == 'aeration' ? 'selected' : '' }}>Aeration</option>
                                        <option value="aeration_images" {{ request('endpoint_type') == 'aeration_images' ? 'selected' : '' }}>Aeration Images</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label>Response Time Min (s)</label>
                                    <input type="number" step="0.001" class="form-control" name="response_time_min" value="{{ request('response_time_min') }}" placeholder="0.000">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label>Response Time Max (s)</label>
                                    <input type="number" step="0.001" class="form-control" name="response_time_max" value="{{ request('response_time_max') }}" placeholder="10.000">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label>IP Address</label>
                                    <input type="text" class="form-control" name="ip_address" value="{{ request('ip_address') }}" placeholder="IP Address">
                                </div>

                                <!-- Row 3: Date Range & Pagination -->
                                <div class="col-md-2 mb-3">
                                    <label>Date From</label>
                                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label>Date To</label>
                                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label>Per Page</label>
                                    <select class="form-control" name="per_page">
                                        <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.api-logs.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt"></i> Reset
                                        </a>
                                        <button type="button" class="btn btn-info" onclick="showQuickFilters()">
                                            <i class="fas fa-bolt"></i> Quick Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Quick Filter Buttons -->
                        <div id="quick-filters" style="display: none;" class="mt-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Quick Filters:</h6>
                                    <a href="{{ route('admin.api-logs.index', ['status_category' => 'client_error', 'time_range' => 'today']) }}" class="btn btn-sm btn-warning mr-2">Today's Errors</a>
                                    <a href="{{ route('admin.api-logs.index', ['status_category' => 'success', 'time_range' => 'today']) }}" class="btn btn-sm btn-success mr-2">Today's Success</a>
                                    <a href="{{ route('admin.api-logs.index', ['endpoint_type' => 'cropdata', 'time_range' => 'last_7_days']) }}" class="btn btn-sm btn-info mr-2">Crop Data (7 days)</a>
                                    <a href="{{ route('admin.api-logs.index', ['endpoint_type' => 'pipe', 'time_range' => 'last_7_days']) }}" class="btn btn-sm btn-primary mr-2">Pipe Data (7 days)</a>
                                    <a href="{{ route('admin.api-logs.index', ['endpoint_type' => 'aeration', 'time_range' => 'last_7_days']) }}" class="btn btn-sm btn-warning mr-2">Aeration Data (7 days)</a>
                                    <a href="{{ route('admin.api-logs.index', ['endpoint_type' => 'aeration_images', 'time_range' => 'last_7_days']) }}" class="btn btn-sm btn-dark mr-2">Aeration Images (7 days)</a>
                                    <a href="{{ route('admin.api-logs.index', ['response_time_min' => '1', 'time_range' => 'today']) }}" class="btn btn-sm btn-danger mr-2">Slow Requests Today</a>
                                    <a href="{{ route('admin.api-logs.index', ['method' => 'POST', 'time_range' => 'today']) }}" class="btn btn-sm btn-secondary mr-2">POST Requests Today</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">API Request Logs (Total: {{ $logs->total() }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-responsive-md table-hover">
                            <thead>
                                <tr>
                                    <th><strong>ID</strong></th>
                                    <th><strong>Method</strong></th>
                                    <th><strong>Endpoint</strong></th>
                                    <th><strong>User</strong></th>
                                    <th><strong>IP Address</strong></th>
                                    <th><strong>Status</strong></th>
                                    <th><strong>Response Time</strong></th>
                                    <th><strong>Date</strong></th>
                                    <th><strong>Action</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <span class="badge badge-{{ $log->method == 'GET' ? 'info' : ($log->method == 'POST' ? 'success' : ($log->method == 'PUT' || $log->method == 'PATCH' ? 'warning' : 'danger')) }}">
                                            {{ $log->method }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $extractedString = \App\Http\Controllers\Admin\ApiLogController::extractStringFromUrl($log->url);
                                        @endphp
                                        
                                        @if($extractedString)
                                            <span class="badge badge-info" title="Full URL: {{ $log->url }}">
                                                {{ $extractedString }}
                                            </span>
                                        @else
                                            <span class="text-muted" title="Full URL: {{ $log->url }}">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->user)
                                            {{ $log->user->name }}
                                        @else
                                            <span class="text-muted">Guest</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>
                                        @if($log->response_status)
                                            <span class="badge badge-{{ $log->response_status >= 200 && $log->response_status < 300 ? 'success' : ($log->response_status >= 400 ? 'danger' : 'warning') }}">
                                                {{ $log->response_status }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->response_time)
                                            {{ number_format($log->response_time, 3) }}s
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('admin.api-logs.show', $log->id) }}" class="btn btn-primary shadow btn-xs sharp mr-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.api-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger shadow btn-xs sharp">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No logs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                        </div>
                        <div>
                            {{ $logs->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Immediate preloader hide - multiple methods
    (function() {
        // Method 1: Immediate
        var preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.style.display = 'none';
        }
        
        // Method 2: After slight delay
        setTimeout(function() {
            var preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
                preloader.style.visibility = 'hidden';
                preloader.style.opacity = '0';
            }
        }, 100);
        
        // Method 3: On DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            var preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
                preloader.style.visibility = 'hidden';
                preloader.style.opacity = '0';
            }
        });
        
        // Method 4: On window load
        window.addEventListener('load', function() {
            var preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
                preloader.style.visibility = 'hidden';
                preloader.style.opacity = '0';
            }
        });
    })();
</script>
@endpush

@section('scripts')
<script>
    // Show loading indicator on form submit
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                document.getElementById('loading-indicator').style.display = 'block';
            });
        }
        
        // Hide loading indicator after page load
        setTimeout(function() {
            document.getElementById('loading-indicator').style.display = 'none';
        }, 1000);
    });

    // Quick filters toggle function
    function showQuickFilters() {
        const quickFilters = document.getElementById('quick-filters');
        if (quickFilters.style.display === 'none') {
            quickFilters.style.display = 'block';
        } else {
            quickFilters.style.display = 'none';
        }
    }

    if (typeof $ !== 'undefined') {
        $(document).ready(function() {
            // Hide preloader with jQuery
            $('#preloader').fadeOut();
            
            // Auto-submit on select change (optional)
            // $('select[name="method"], select[name="status"], select[name="per_page"]').on('change', function() {
            //     $(this).closest('form').submit();
            // });
        });
    }
</script>
@endsection

