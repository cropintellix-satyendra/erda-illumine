@extends('layout.default')

@section('title', 'Analyze Polygon')

@section('styles')
<style>
    /* Ensure main content is visible */
    body, #main-wrapper, .content-body {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .content-body {
        padding: 10px !important;
    }
    
    .container-fluid {
        padding: 0 10px !important;
    }
    
    .page-titles {
        margin-bottom: 15px !important;
        padding: 15px 20px !important;
        background: linear-gradient(135deg, #0cb3c2 0%, #0891a0 100%);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(12,179,194,0.3);
    }
    
    .page-titles h4, .page-titles p {
        color: white !important;
        margin: 0;
    }
    
    .page-titles .breadcrumb {
        background: transparent;
        margin: 0;
        padding: 0;
    }
    
    .page-titles .breadcrumb-item,
    .page-titles .breadcrumb-item a {
        color: rgba(255,255,255,0.9) !important;
    }
    
    .page-titles .breadcrumb-item.active {
        color: white !important;
    }
    
    .row {
        margin-left: -5px !important;
        margin-right: -5px !important;
    }
    
    .col, [class*="col-"] {
        padding-left: 5px !important;
        padding-right: 5px !important;
    }
    
    .card {
        margin-bottom: 10px !important;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .card-header {
        background: linear-gradient(135deg, #0cb3c2 0%, #0891a0 100%);
        border-bottom: none;
        padding: 12px 20px;
    }
    
    .card-header h4 {
        color: white !important;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }
    
    .kml-selector {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .kml-file-item {
        padding: 12px 15px;
        margin: 8px 0;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #ffffff;
    }
    
    .kml-file-item:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }
    
    .kml-file-item.active {
        background: linear-gradient(135deg, #0cb3c2 0%, #0891a0 100%);
        color: white;
        border-color: #0cb3c2;
        box-shadow: 0 4px 12px rgba(12,179,194,0.4);
    }
    
    .kml-file-item.active small {
        color: rgba(255,255,255,0.9) !important;
    }
    
    .kml-file-item h6 {
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .kml-file-item small {
        font-size: 11px;
        color: #6c757d;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #0cb3c2 0%, #0891a0 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 4px 12px rgba(12,179,194,0.3);
        text-align: center;
    }
    
    .stat-card h2 {
        font-size: 48px;
        font-weight: bold;
        margin: 0;
    }
    
    .stat-card p {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 14px;
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .stat-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    }
    
    .stat-card.info {
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
    }
    
    .results-panel {
        display: none;
    }
    
    .loading {
        text-align: center;
        padding: 40px;
    }
    
    .loading i {
        font-size: 48px;
        color: #0cb3c2;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .polygon-table {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .polygon-table table {
        font-size: 13px;
    }
    
    .polygon-table th {
        background: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .badge-custom {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Analyze Polygon - Range Comparison</h4>
                <p class="mb-0">KML और Database ranges की तुलना करें</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">KML Reader</a></li>
                <li class="breadcrumb-item active">Analyze Polygon</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <!-- Left Panel: KML File Selector -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Select KML File to Compare</h4>
                </div>
                <div class="card-body">
                    <div class="kml-selector">
                        @if(count($kmlFiles) > 0)
                            @foreach($kmlFiles as $kmlFile)
                                <div class="kml-file-item" 
                                     data-filename="{{ $kmlFile['name'] }}"
                                     onclick="compareKml('{{ $kmlFile['name'] }}')">
                                    <h6><i class="fa fa-file-o mr-2"></i>{{ $kmlFile['name'] }}</h6>
                                    <small>
                                        Size: {{ number_format($kmlFile['size'] / 1024, 2) }} KB | 
                                        Modified: {{ date('d M Y', $kmlFile['modified']) }}
                                    </small>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="flaticon-381-folder" style="font-size: 64px; color: #ddd;"></i>
                                <p class="text-muted mt-3">No KML files found</p>
                                <a href="{{ url('admin/kml/upload') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fa fa-upload mr-2"></i>Upload KML
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Comparison Results -->
        <div class="col-xl-8">
            <!-- Loading State -->
            <div id="loadingState" class="loading" style="display: none;">
                <i class="fa fa-spinner"></i>
                <p class="mt-3">Analyzing data structure and comparing ranges...</p>
            </div>

            <!-- Results Panel -->
            <div id="resultsPanel" class="results-panel">
                <!-- Summary Stats -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card info">
                            <h2 id="totalKml">0</h2>
                            <p>KML Polygons</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card info">
                            <h2 id="totalDb">0</h2>
                            <p>DB Polygons</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <h2 id="matchedCount">0</h2>
                            <p>✓ Matched</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card danger">
                            <h2 id="unmatchedCount">0</h2>
                            <p>✗ Unmatched</p>
                        </div>
                    </div>
                </div>

                <!-- Data Structure Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">📊 Data Structure Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Database Structure:</strong></h6>
                                <pre class="bg-light p-3 rounded" style="font-size: 12px;">Table: polygons
Column: ranges (JSON)
Format: [
  {"lat": 23.123, "lng": 85.456},
  {"lat": 23.124, "lng": 85.457},
  ...
]</pre>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>KML Structure:</strong></h6>
                                <pre class="bg-light p-3 rounded" style="font-size: 12px;">&lt;Placemark&gt;
  &lt;Polygon&gt;
    &lt;coordinates&gt;
      lng,lat,alt lng,lat,alt ...
    &lt;/coordinates&gt;
  &lt;/Polygon&gt;
&lt;/Placemark&gt;</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Matched Polygons -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">✓ Matched Polygons</h4>
                        <span class="badge badge-success" id="matchedBadge">0</span>
                    </div>
                    <div class="card-body polygon-table">
                        <table class="table table-striped" id="matchedTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>KML Polygon</th>
                                    <th>DB Plot No</th>
                                    <th>Plot Unique ID</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Unmatched Polygons -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">✗ Unmatched Polygons (Only in KML)</h4>
                        <span class="badge badge-danger" id="unmatchedBadge">0</span>
                    </div>
                    <div class="card-body polygon-table">
                        <table class="table table-striped" id="unmatchedTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Polygon Name</th>
                                    <th>Points Count</th>
                                    <th>First Point</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Export Button -->
                <div class="text-center mt-3">
                    <button class="btn btn-primary btn-lg" onclick="exportResults()">
                        <i class="fa fa-download mr-2"></i>Export Comparison Results
                    </button>
                </div>
            </div>

            <!-- Initial State -->
            <div id="initialState" class="text-center" style="padding: 100px 20px;">
                <i class="flaticon-381-file-1" style="font-size: 128px; color: #ddd;"></i>
                <h4 class="mt-4 text-muted">Select a KML file to start comparison</h4>
                <p class="text-muted">KML file के ranges को database polygons के साथ compare किया जाएगा</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Force hide preloader
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
            }
        }, 1000);
    });
    
    // Fix menu active state
    window.addEventListener('load', function() {
        setTimeout(function() {
            setActiveMenu();
        }, 500);
    });
    
    function setActiveMenu() {
        const allMenuItems = document.querySelectorAll('#menu li');
        allMenuItems.forEach(item => item.classList.remove('mm-active'));
        
        const allLinks = document.querySelectorAll('#menu a');
        allLinks.forEach(link => {
            link.classList.remove('mm-active');
            link.setAttribute('aria-expanded', 'false');
        });
        
        const allSubMenus = document.querySelectorAll('#menu ul[aria-expanded]');
        allSubMenus.forEach(ul => {
            ul.setAttribute('aria-expanded', 'false');
            ul.classList.remove('mm-show');
            ul.classList.add('mm-collapse');
        });
        
        const currentUrl = window.location.pathname;
        allLinks.forEach(link => {
            if (link.getAttribute('href') && link.getAttribute('href').includes(currentUrl)) {
                link.classList.add('mm-active');
                let parentLi = link.closest('li');
                if (parentLi) {
                    parentLi.classList.add('mm-active');
                    let parentUl = parentLi.closest('ul');
                    if (parentUl && parentUl.id !== 'menu') {
                        parentUl.classList.remove('mm-collapse');
                        parentUl.classList.add('mm-show');
                        parentUl.setAttribute('aria-expanded', 'true');
                        let parentToggle = parentUl.previousElementSibling;
                        if (parentToggle && parentToggle.classList.contains('has-arrow')) {
                            parentToggle.classList.add('mm-active');
                            parentToggle.setAttribute('aria-expanded', 'true');
                            parentToggle.closest('li').classList.add('mm-active');
                        }
                    }
                }
            }
        });
    }

    let currentResults = null;

    function compareKml(filename) {
        // Update UI
        document.querySelectorAll('.kml-file-item').forEach(i => i.classList.remove('active'));
        event.target.closest('.kml-file-item').classList.add('active');
        
        // Show loading
        document.getElementById('initialState').style.display = 'none';
        document.getElementById('resultsPanel').style.display = 'none';
        document.getElementById('loadingState').style.display = 'block';
        
        // Fetch comparison results
        fetch(`{{ url('admin/kml/compare') }}/${filename}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentResults = data;
                    displayResults(data);
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
                document.getElementById('loadingState').style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to compare KML file');
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('initialState').style.display = 'block';
            });
    }

    function displayResults(data) {
        // Update stats
        document.getElementById('totalKml').textContent = data.total_kml_polygons;
        document.getElementById('totalDb').textContent = data.total_db_polygons;
        document.getElementById('matchedCount').textContent = data.matched_count;
        document.getElementById('unmatchedCount').textContent = data.unmatched_count;
        document.getElementById('matchedBadge').textContent = data.matched_count;
        document.getElementById('unmatchedBadge').textContent = data.unmatched_count;
        
        // Display matched polygons
        const matchedTable = document.getElementById('matchedTable').querySelector('tbody');
        matchedTable.innerHTML = '';
        
        data.matched.forEach((match, index) => {
            const row = `<tr>
                <td>${index + 1}</td>
                <td>${match.kml_polygon.name}</td>
                <td>${match.db_polygon.plot_no || 'N/A'}</td>
                <td><small>${match.db_polygon.plot_uniqueid}</small></td>
                <td><span class="badge badge-info">${match.kml_polygon.ranges.length}</span></td>
            </tr>`;
            matchedTable.innerHTML += row;
        });
        
        if (data.matched_count === 0) {
            matchedTable.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No matched polygons found</td></tr>';
        }
        
        // Display unmatched polygons
        const unmatchedTable = document.getElementById('unmatchedTable').querySelector('tbody');
        unmatchedTable.innerHTML = '';
        
        data.unmatched.forEach((polygon, index) => {
            const firstPoint = polygon.ranges[0];
            const row = `<tr>
                <td>${index + 1}</td>
                <td>${polygon.name}</td>
                <td><span class="badge badge-warning">${polygon.ranges.length}</span></td>
                <td><small>Lat: ${firstPoint.lat.toFixed(5)}, Lng: ${firstPoint.lng.toFixed(5)}</small></td>
            </tr>`;
            unmatchedTable.innerHTML += row;
        });
        
        if (data.unmatched_count === 0) {
            unmatchedTable.innerHTML = '<tr><td colspan="4" class="text-center text-muted">All KML polygons matched!</td></tr>';
        }
        
        // Show results
        document.getElementById('resultsPanel').style.display = 'block';
    }

    function exportResults() {
        if (!currentResults) {
            alert('कोई results नहीं हैं export करने के लिए!');
            return;
        }
        
        const exportData = {
            timestamp: new Date().toISOString(),
            summary: {
                total_kml_polygons: currentResults.total_kml_polygons,
                total_db_polygons: currentResults.total_db_polygons,
                matched_count: currentResults.matched_count,
                unmatched_count: currentResults.unmatched_count,
                match_percentage: ((currentResults.matched_count / currentResults.total_kml_polygons) * 100).toFixed(2) + '%'
            },
            data_structure: {
                database: "Table: polygons, Column: ranges (JSON), Format: [{lat, lng}, ...]",
                kml: "XML format with <coordinates> tag containing lng,lat,alt points"
            },
            matched: currentResults.matched,
            unmatched: currentResults.unmatched
        };
        
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportData, null, 2));
        const downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("download", `polygon_comparison_${new Date().getTime()}.json`);
        document.body.appendChild(downloadAnchorNode);
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
    }
</script>
@endsection
