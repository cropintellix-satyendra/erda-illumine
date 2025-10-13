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
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>API Log Details</h4>
                <p class="mb-0">Log ID: {{ $log->id }}</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.api-logs.index') }}">API Logs</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Request Information</h4>
                    <a href="{{ route('admin.api-logs.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Method:</label>
                            <p>
                                <span class="badge badge-{{ $log->method == 'GET' ? 'info' : ($log->method == 'POST' ? 'success' : ($log->method == 'PUT' || $log->method == 'PATCH' ? 'warning' : 'danger')) }}">
                                    {{ $log->method }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Status Code:</label>
                            <p>
                                @if($log->response_status)
                                    <span class="badge badge-{{ $log->response_status >= 200 && $log->response_status < 300 ? 'success' : ($log->response_status >= 400 ? 'danger' : 'warning') }}">
                                        {{ $log->response_status }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold">URL:</label>
                            <p class="text-break">{{ $log->url }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">IP Address:</label>
                            <p>{{ $log->ip_address ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">User:</label>
                            <p>
                                @if($log->user)
                                    {{ $log->user->name }} (ID: {{ $log->user->id }})
                                @else
                                    <span class="text-muted">Guest/Unauthenticated</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Response Time:</label>
                            <p>{{ $log->response_time ? number_format($log->response_time, 3) . 's' : 'N/A' }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Created At:</label>
                            <p>{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Updated At:</label>
                            <p>{{ $log->updated_at->format('d/m/Y H:i:s') }}</p>
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
                    <h4 class="card-title">User Agent</h4>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded">{{ $log->user_agent ?? 'N/A' }}</pre>
                </div>
            </div>
        </div>
    </div>

    @if($log->headers)
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Headers</h4>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ json_encode($log->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($log->request_data)
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Request Data</h4>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary" onclick="toggleView('request-json')">JSON View</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleView('request-table')">Table View</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- JSON View -->
                    <div id="request-json" class="view-content">
                        <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                    
                    <!-- Table View -->
                    <div id="request-table" class="view-content" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="30%">Key</th>
                                        <th width="70%">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $requestData = $log->request_data;
                                        if (is_array($requestData) || is_object($requestData)) {
                                            $requestData = json_decode(json_encode($requestData), true);
                                        }
                                    @endphp
                                    @if(is_array($requestData))
                                        @foreach($requestData as $key => $value)
                                            <tr>
                                                <td><strong>{{ $key }}</strong></td>
                                                <td>
                                                    @if(is_array($value) || is_object($value))
                                                        <div class="nested-data">
                                                            @include('admin.api_logs.partials.nested_data', ['data' => $value, 'level' => 1])
                                                        </div>
                                                    @elseif(is_bool($value))
                                                        <span class="badge badge-{{ $value ? 'success' : 'danger' }}">{{ $value ? 'true' : 'false' }}</span>
                                                    @elseif(is_numeric($value))
                                                        <code>{{ $value }}</code>
                                                    @elseif(is_string($value) && strlen($value) > 100)
                                                        <div class="long-text" data-toggle="tooltip" title="{{ $value }}">
                                                            {{ Str::limit($value, 100) }}
                                                            <button type="button" class="btn btn-sm btn-link" onclick="showFullText(this, '{{ addslashes($value) }}')">Show More</button>
                                                        </div>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No data available</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($log->response_data)
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Response Data</h4>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary" onclick="toggleView('response-json')">JSON View</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleView('response-table')">Table View</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- JSON View -->
                    <div id="response-json" class="view-content">
                        <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                    
                    <!-- Table View -->
                    <div id="response-table" class="view-content" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="30%">Key</th>
                                        <th width="70%">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $responseData = $log->response_data;
                                        if (is_array($responseData) || is_object($responseData)) {
                                            $responseData = json_decode(json_encode($responseData), true);
                                        }
                                    @endphp
                                    @if(is_array($responseData))
                                        @foreach($responseData as $key => $value)
                                            <tr>
                                                <td><strong>{{ $key }}</strong></td>
                                                <td>
                                                    @if(is_array($value) || is_object($value))
                                                        <div class="nested-data">
                                                            @include('admin.api_logs.partials.nested_data', ['data' => $value, 'level' => 1])
                                                        </div>
                                                    @elseif(is_bool($value))
                                                        <span class="badge badge-{{ $value ? 'success' : 'danger' }}">{{ $value ? 'true' : 'false' }}</span>
                                                    @elseif(is_numeric($value))
                                                        <code>{{ $value }}</code>
                                                    @elseif(is_string($value) && strlen($value) > 100)
                                                        <div class="long-text" data-toggle="tooltip" title="{{ $value }}">
                                                            {{ Str::limit($value, 100) }}
                                                            <button type="button" class="btn btn-sm btn-link" onclick="showFullText(this, '{{ addslashes($value) }}')">Show More</button>
                                                        </div>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No data available</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Force hide preloader immediately
    document.addEventListener('DOMContentLoaded', function() {
        var preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.style.display = 'none';
        }
    });

    // Toggle between JSON and Table view
    function toggleView(viewId) {
        // Hide all view contents
        document.querySelectorAll('.view-content').forEach(function(element) {
            element.style.display = 'none';
        });
        
        // Show selected view
        document.getElementById(viewId).style.display = 'block';
        
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(function(btn) {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
        });
        
        event.target.classList.remove('btn-secondary');
        event.target.classList.add('btn-primary');
    }

    // Toggle nested data
    function toggleNested(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.className = 'fas fa-minus';
        } else {
            content.style.display = 'none';
            icon.className = 'fas fa-plus';
        }
    }

    // Show full text in modal
    function showFullText(button, text) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Full Text</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <pre style="max-height: 400px; overflow-y: auto;">${text}</pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        $(modal).modal('show');
        
        // Remove modal from DOM when closed
        $(modal).on('hidden.bs.modal', function() {
            document.body.removeChild(modal);
        });
    }

    // Initialize tooltips
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection

