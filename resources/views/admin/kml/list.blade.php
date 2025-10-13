@extends('layout.default')

@section('title', 'KML Files List')

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
    
    .file-card {
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
    }
    
    .file-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .file-icon {
        font-size: 48px;
        color: #007bff;
    }
    
    .action-btn {
        margin: 0 2px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
        <!-- Page Title -->
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>KML Files</h4>
                    <p class="mb-0">सभी uploaded KML files की list</p>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">KML Files</li>
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

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ url('admin/kml/upload') }}" class="btn btn-primary">
                    <i class="fa fa-upload mr-2"></i>Upload New KML
                </a>
                <a href="{{ url('admin/kml/viewer') }}" class="btn btn-success ml-2">
                    <i class="fa fa-eye mr-2"></i>Open Viewer
                </a>
            </div>
        </div>

        @if(count($kmlFiles) > 0)
            <!-- Table View -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">All KML Files ({{ count($kmlFiles) }})</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-responsive-md">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>File Name</th>
                                            <th>Size</th>
                                            <th>Modified</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($kmlFiles as $index => $file)
                                            <tr>
                                                <td><strong>{{ $index + 1 }}</strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="flaticon-381-file-1 file-icon mr-2"></i>
                                                        <span>{{ $file['name'] }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $file['size'] }}</td>
                                                <td>{{ $file['modified'] }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{ url('admin/kml/viewer') }}" class="btn btn-primary shadow btn-xs sharp action-btn" title="View on Map">
                                                            <i class="fa fa-map-marker"></i>
                                                        </a>
                                                        <a href="{{ $file['url'] }}" download class="btn btn-success shadow btn-xs sharp action-btn" title="Download">
                                                            <i class="fa fa-download"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger shadow btn-xs sharp action-btn" 
                                                                onclick="deleteFile('{{ $file['name'] }}')" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid View (Alternative) -->
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="mb-3">Grid View</h4>
                </div>
                @foreach($kmlFiles as $file)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                        <div class="card file-card">
                            <div class="card-body text-center">
                                <i class="flaticon-381-file-1 file-icon"></i>
                                <h6 class="mt-3 mb-2">{{ Str::limit($file['name'], 25) }}</h6>
                                <p class="text-muted mb-1"><small>{{ $file['size'] }}</small></p>
                                <p class="text-muted mb-3"><small>{{ $file['modified'] }}</small></p>
                                
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ url('admin/kml/viewer') }}" class="btn btn-primary" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ $file['url'] }}" download class="btn btn-success" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deleteFile('{{ $file['name'] }}')" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="flaticon-381-file-1 display-1 text-muted"></i>
                            <h4 class="mt-4">कोई KML file उपलब्ध नहीं है</h4>
                            <p class="text-muted mb-4">पहले KML file upload करें</p>
                            <a href="{{ url('admin/kml/upload') }}" class="btn btn-primary">
                                <i class="fa fa-upload mr-2"></i>Upload KML File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
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

    function deleteFile(filename) {
        if (confirm('क्या आप सच में इस file को delete करना चाहते हैं?\n\nFile: ' + filename)) {
            const form = document.getElementById('deleteForm');
            form.action = "{{ url('admin/kml/delete') }}/" + filename;
            form.submit();
        }
    }
</script>
@endsection

