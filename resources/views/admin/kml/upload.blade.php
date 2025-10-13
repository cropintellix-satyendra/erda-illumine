@extends('layout.default')

@section('title', 'Upload KML File')

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
    }
    
    .drop-zone {
        border: 3px dashed #ddd;
        border-radius: 10px;
        padding: 60px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }
    
    .drop-zone:hover,
    .drop-zone.dragover {
        border-color: #007bff;
        background-color: #e7f3ff;
    }
    
    .drop-zone.dragover {
        transform: scale(1.02);
    }
    
    .file-preview {
        display: none;
        margin-top: 20px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .upload-icon {
        font-size: 64px;
        color: #007bff;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
        <!-- Page Title -->
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Upload KML File</h4>
                    <p class="mb-0">अपनी KML files upload करें</p>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/kml/viewer') }}">KML Viewer</a></li>
                    <li class="breadcrumb-item active">Upload</li>
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8 offset-xl-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Upload KML File</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.kml.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                            @csrf
                            
                            <div class="drop-zone" id="dropZone">
                                <i class="flaticon-381-download upload-icon"></i>
                                <h4>Drag & Drop KML file here</h4>
                                <p class="text-muted">या</p>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('kmlFileInput').click()">
                                    <i class="fa fa-folder-open mr-2"></i>Browse Files
                                </button>
                                <input type="file" id="kmlFileInput" name="kml_file" accept=".kml" style="display: none;">
                                <p class="text-muted mt-3 mb-0">
                                    <small>Supported: KML files only | Maximum size: 10MB</small>
                                </p>
                            </div>

                            <div class="file-preview" id="filePreview">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 id="fileName" class="mb-1"></h5>
                                        <p id="fileSize" class="text-muted mb-0"></p>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="clearFile()">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-success btn-block" id="uploadBtn" disabled>
                                    <i class="fa fa-upload mr-2"></i>Upload KML File
                                </button>
                            </div>
                        </form>

                        <div class="mt-4">
                            <h5>Instructions:</h5>
                            <ul>
                                <li>केवल .kml extension वाली files ही upload करें</li>
                                <li>Maximum file size 10MB तक होनी चाहिए</li>
                                <li>File upload होने के बाद आप इसे Viewer में देख सकते हैं</li>
                            </ul>
                        </div>

                        <div class="mt-3">
                            <a href="{{ url('admin/kml/viewer') }}" class="btn btn-outline-primary">
                                <i class="fa fa-eye mr-2"></i>Go to Viewer
                            </a>
                            <a href="{{ url('admin/kml/list') }}" class="btn btn-outline-secondary ml-2">
                                <i class="fa fa-list mr-2"></i>View All Files
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@section('scripts')
<script>
    // Force hide preloader after page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
            }
        }, 1000);
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

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('kmlFileInput');
    const filePreview = document.getElementById('filePreview');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('dragover');
    }

    function unhighlight(e) {
        dropZone.classList.remove('dragover');
    }

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            handleFiles(files);
        }
    }

    // Handle file input change
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            
            // Check file extension
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (fileExt !== 'kml') {
                alert('कृपया केवल KML file upload करें!');
                clearFile();
                return;
            }

            // Check file size (10MB = 10485760 bytes)
            if (file.size > 10485760) {
                alert('File size 10MB से ज्यादा नहीं होनी चाहिए!');
                clearFile();
                return;
            }

            // Show file preview
            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);
            filePreview.style.display = 'block';
            uploadBtn.disabled = false;
        }
    }

    function clearFile() {
        fileInput.value = '';
        filePreview.style.display = 'none';
        uploadBtn.disabled = true;
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
</script>
@endsection

