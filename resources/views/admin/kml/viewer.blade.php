@extends('layout.default')

@section('title', 'KML Viewer')

@section('styles')
<style>
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
        padding: 10px !important; /* Minimal padding */
    }
    
    .container-fluid {
        padding: 0 10px !important; /* Minimal padding */
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
    
    .col, .col-xl-4, .col-xl-8, [class*="col-"] {
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
    
    .card-body {
        padding: 15px;
    }
    
    #map {
        height: 70vh; /* Increased height using viewport height */
        width: 100%;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .kml-selector {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 15px;
        max-height: 65vh;
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
    
    .upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 25px 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fafbfc;
    }
    
    .upload-zone:hover {
        border-color: #007bff;
        background-color: #e7f3ff;
        transform: scale(1.02);
    }
    
    .upload-zone i {
        font-size: 32px;
        color: #007bff;
    }
    
    .map-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 5;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>KML Viewer</h4>
                    <p class="mb-0">View और analyze करें KML files को Interactive Map पर</p>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                    <li class="breadcrumb-item active">KML Viewer</li>
                </ol>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span></button>
            </div>
        @endif

        <div class="row">
            <!-- KML File Selector -->
            <div class="col-xl-5 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">KML Files</h4>
                    </div>
                    <div class="card-body">
                        <div class="kml-selector">
                            @if(count($kmlFiles) > 0)
                                <div id="kml-files-list">
                                    @foreach($kmlFiles as $file)
                                        <div class="kml-file-item" data-file="{{ $file['name'] }}" data-url="{{ asset('storage/' . $file['path']) }}">
                                            <h6 class="mb-1">{{ $file['name'] }}</h6>
                                            <small class="text-muted">
                                                Size: {{ number_format($file['size'] / 1024, 2) }} KB | 
                                                Modified: {{ date('d M Y', $file['modified']) }}
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="flaticon-381-file-1 display-4 text-muted"></i>
                                    <p class="mt-3 text-muted">कोई KML file उपलब्ध नहीं है</p>
                                    <a href="{{ url('admin/kml/upload') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="fa fa-upload mr-2"></i>Upload KML
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Quick Upload Section -->
                        <div class="mt-4">
                            <h5 class="mb-3">Quick Upload</h5>
                            <form action="{{ route('admin.kml.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="upload-zone" onclick="document.getElementById('kmlFileInput').click()">
                                    <i class="flaticon-381-download display-4 text-primary"></i>
                                    <p class="mt-2 mb-0">Click to upload KML file</p>
                                    <small class="text-muted">Maximum file size: 10MB</small>
                                </div>
                                <input type="file" id="kmlFileInput" name="kml_file" accept=".kml" style="display: none;" onchange="this.form.submit()">
                            </form>
                        </div>

                        <!-- Map Controls Info -->
                        <div class="mt-4">
                            <h6>Map Controls:</h6>
                            <ul class="list-unstyled mt-2">
                                <li><i class="fa fa-hand-pointer text-primary mr-2"></i>Click on file to view</li>
                                <li><i class="fa fa-search-plus text-primary mr-2"></i>Scroll to zoom</li>
                                <li><i class="fa fa-arrows text-primary mr-2"></i>Drag to pan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Map -->
            <div class="col-xl-7 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Map View</h4>
                        <button id="clearMapBtn" class="btn btn-danger btn-sm" style="display: none;">
                            <i class="fa fa-times mr-1"></i>Clear Map
                        </button>
                    </div>
                    <div class="card-body position-relative">
                        <div id="map"></div>
                        <div id="kml-info" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <h6 id="current-kml-name"></h6>
                                <div id="kml-stats"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Leaflet Omnivore for KML support -->
<script src='https://unpkg.com/@mapbox/leaflet-omnivore@0.3.4/leaflet-omnivore.min.js'></script>

<script>
    // Force hide preloader after page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
            }
        }, 1000);
        
        // Initialize map after DOM is ready
        initMap();
    });
    
    // Fix menu active state - Run after MetisMenu initializes
    window.addEventListener('load', function() {
        setTimeout(function() {
            setActiveMenu();
        }, 500);
    });
    
    function setActiveMenu() {
        // 1. Remove all active classes and collapse all menus
        const allMenuItems = document.querySelectorAll('#menu li');
        allMenuItems.forEach(item => {
            item.classList.remove('mm-active');
        });
        
        const allLinks = document.querySelectorAll('#menu a');
        allLinks.forEach(link => {
            link.classList.remove('mm-active');
            link.setAttribute('aria-expanded', 'false');
        });
        
        // 2. Collapse all submenus
        const allSubMenus = document.querySelectorAll('#menu ul[aria-expanded]');
        allSubMenus.forEach(ul => {
            ul.setAttribute('aria-expanded', 'false');
            ul.classList.remove('mm-show');
            ul.classList.add('mm-collapse');
        });
        
        // 3. Set active for current page
        const currentUrl = window.location.pathname;
        allLinks.forEach(link => {
            if (link.getAttribute('href') && link.getAttribute('href').includes(currentUrl)) {
                link.classList.add('mm-active');
                
                // Find parent li and add active class
                let parentLi = link.closest('li');
                if (parentLi) {
                    parentLi.classList.add('mm-active');
                    
                    // If it has a parent menu, expand ONLY that
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

    let map;
    let currentKmlLayer = null;
    let currentKmlFile = null;

    // Initialize Leaflet Map
    function initMap() {
        try {
            // Create map centered on India
            map = L.map('map', {
                center: [20.5937, 78.9629],
                zoom: 5,
                zoomControl: true
            });

            // Add OpenStreetMap tiles (Satellite view)
            const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles © Esri',
                maxZoom: 18
            });

            // Add OpenStreetMap standard tiles
            const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            });

            // Add satellite by default
            satellite.addTo(map);

            // Layer control for switching between views
            const baseMaps = {
                "Satellite": satellite,
                "Streets": streets
            };
            L.control.layers(baseMaps).addTo(map);

            console.log('Map initialized successfully with Leaflet.js');
            
            // Initialize event listeners
            initEventListeners();
        } catch (error) {
            console.error('Error initializing map:', error);
            document.getElementById('map').innerHTML = '<div class="alert alert-danger m-4"><strong>Map Error:</strong> ' + error.message + '</div>';
        }
    }

    // Load KML file on map
    function loadKmlFile(filename, url) {
        try {
            // Clear existing KML layer
            if (currentKmlLayer) {
                map.removeLayer(currentKmlLayer);
                currentKmlLayer = null;
            }

            // Load KML using omnivore
            currentKmlLayer = omnivore.kml(url)
                .on('ready', function() {
                    console.log('KML loaded successfully');
                    currentKmlFile = filename;
                    
                    // Fit map to KML bounds
                    map.fitBounds(currentKmlLayer.getBounds());
                    
                    // Update UI
                    document.getElementById('current-kml-name').textContent = 'Loaded: ' + filename;
                    document.getElementById('kml-info').style.display = 'block';
                    document.getElementById('clearMapBtn').style.display = 'inline-block';
                    
                    // Show success message
                    console.log('KML file displayed on map');
                })
                .on('error', function(e) {
                    console.error('KML loading error:', e);
                    alert('KML file को load करने में समस्या आई। कृपया file check करें।');
                })
                .addTo(map);

            // Style the KML features
            currentKmlLayer.eachLayer(function(layer) {
                if (layer instanceof L.Polyline) {
                    layer.setStyle({
                        color: '#0cb3c2',
                        weight: 3,
                        opacity: 0.8
                    });
                }
                if (layer instanceof L.Polygon) {
                    layer.setStyle({
                        color: '#0cb3c2',
                        fillColor: '#0cb3c2',
                        fillOpacity: 0.3,
                        weight: 2
                    });
                }
                
                // Add popup if there's a name or description
                if (layer.feature && layer.feature.properties) {
                    const props = layer.feature.properties;
                    let popupContent = '';
                    if (props.name) popupContent += '<strong>' + props.name + '</strong><br>';
                    if (props.description) popupContent += props.description;
                    if (popupContent) layer.bindPopup(popupContent);
                }
            });

        } catch (error) {
            console.error('Error loading KML:', error);
            alert('KML file load करने में error: ' + error.message);
        }
    }

    // Clear map
    function clearMap() {
        if (currentKmlLayer) {
            map.removeLayer(currentKmlLayer);
            currentKmlLayer = null;
        }
        currentKmlFile = null;
        document.getElementById('kml-info').style.display = 'none';
        document.getElementById('clearMapBtn').style.display = 'none';
        
        // Remove active class from all items
        document.querySelectorAll('.kml-file-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Reset map to default position
        map.setView([20.5937, 78.9629], 5);
    }

    // Initialize event listeners
    function initEventListeners() {
        // Add click event to KML file items
        document.querySelectorAll('.kml-file-item').forEach(item => {
            item.addEventListener('click', function() {
                const filename = this.dataset.file;
                const url = this.dataset.url;
                
                // Remove active class from all items
                document.querySelectorAll('.kml-file-item').forEach(i => i.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Load KML file
                loadKmlFile(filename, url);
            });
        });

        // Clear map button
        const clearBtn = document.getElementById('clearMapBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearMap);
        }

        // File input change handler
        const fileInput = document.getElementById('kmlFileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const fileName = this.files[0].name;
                    const fileExt = fileName.split('.').pop().toLowerCase();
                    
                    if (fileExt !== 'kml') {
                        alert('कृपया केवल KML file upload करें!');
                        this.value = '';
                        return false;
                    }
                }
            });
        }
    }

</script>
@endsection

