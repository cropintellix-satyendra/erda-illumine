# Erda Illumine - Web Routes Documentation

## 📋 Overview
This document provides comprehensive documentation for the `web.php` routes file in the Erda Illumine project. This file contains all web routes for the application, including authentication, admin panels, L1/L2 validator routes, and utility functions.

## 🏗️ Project Structure
- **Framework**: Laravel 8/9
- **Authentication**: Laravel Sanctum
- **File Location**: `routes/web.php`
- **Total Routes**: 200+ routes
- **Main Controllers**: 15+ controllers

## 🔐 Authentication & Public Routes

### Public Routes (No Authentication Required)
```php
// Terms and Privacy Policy
Route::get('/terms-and-condition', [TermsandconditionController::class, 'web_tnc']);
Route::get('/privacy/policy', [TermsandconditionController::class, 'web_privacy_policy']);
Route::get('/privacy/webpolicy', [TermsandconditionController::class, 'web_privacy_policy']);
Route::get('/privacy-policy', [TermsandconditionController::class, 'web_privacy_policy_terms']);

// Account Deletion Request
Route::get('/req_delete_account', function(){
    return view('req_deleted_account');
});
Route::post('/store_req_delete_account', function(Request $request){
    return redirect()->back()->with('success', 'Delete account request submitted successfully');
})->name('store_req_delete_account');

// Login Routes
Route::get('/', [LoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/signin', [LoginController::class, 'login']);

// Utility Routes
Route::get('genrate/geojson', [\App\Http\Controllers\Api\V1\TestController2::class, 'genrate_geojson']);
Route::get('/check-storage', function() {
    return response()->json([config('storagesystems.store'), config('storagesystems.final_store'), config('storagesystems.appfilename')]);
});
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
});
```

## 🔒 Authenticated Routes

### Middleware Group: `['web','auth']`
All routes below require authentication and are grouped under this middleware.

## 🎛️ Admin Routes

### KML Management Routes
```php
Route::prefix("admin")->group(function(){
    // KML Viewer and Management
    Route::get('kml/viewer', [KmlController::class, 'viewer'])->name('admin.kml.viewer');
    Route::get('kml/upload', [KmlController::class, 'upload'])->name('admin.kml.upload');
    Route::post('kml/store', [KmlController::class, 'store'])->name('admin.kml.store');
    Route::get('kml/list', [KmlController::class, 'list'])->name('admin.kml.list');
    Route::get('kml/analyze', [KmlController::class, 'analyze'])->name('admin.kml.analyze');
    Route::get('kml/compare/{filename}', [KmlController::class, 'compareKml'])->name('admin.kml.compare');
    Route::delete('kml/delete/{filename}', [KmlController::class, 'delete'])->name('admin.kml.delete');
    Route::get('kml/content/{filename}', [KmlController::class, 'getKmlContent'])->name('admin.kml.content');
    
    // API Logs Management
    Route::get('api-logs', [\App\Http\Controllers\Admin\ApiLogController::class, 'index'])->name('admin.api-logs.index');
    Route::get('api-logs/{id}', [\App\Http\Controllers\Admin\ApiLogController::class, 'show'])->name('admin.api-logs.show');
    Route::delete('api-logs/{id}', [\App\Http\Controllers\Admin\ApiLogController::class, 'destroy'])->name('admin.api-logs.destroy');
    Route::post('api-logs/delete-old', [\App\Http\Controllers\Admin\ApiLogController::class, 'deleteOld'])->name('admin.api-logs.delete-old');
});
```

## 👥 Role-Based Access Routes

### Universal Access Routes (Admin & Viewer)
```php
Route::prefix("{accessrole}/view")->group(function(){
    // Accessible by both admin and viewer roles
    // Pattern: /{accessrole}/view/...
});
```

#### L1 Validator Routes (Universal Access)

**Benefit Validation:**
```php
// Counting and Statistics
Route::get('fetch/counting', [\App\Http\Controllers\Admin\Account\l1validator\L1ValidatorController::class, 'counting']);

// Pending Benefits
Route::get('pendings/benefit/l1', [\App\Http\Controllers\Admin\Account\l1validator\BenefitValidationController::class, 'benefit_pending_lists']);
Route::get('pending/benefit/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l1validator\BenefitValidationController::class, 'benefit_pending_detail']);
Route::get('benefit/search/{status}', [\App\Http\Controllers\Admin\Account\l1validator\BenefitValidationController::class, 'search']);

// Approved Benefits
Route::get('approved/benefit/l1', [\App\Http\Controllers\Admin\Account\l1validator\BenefitValidationController::class, 'benefit_approved_lists']);
Route::get('approved/benefit/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l1validator\BenefitValidationController::class, 'benefit_approved_detail']);
```

**Crop Data Validation:**
```php
// Pending Crop Data
Route::get('pendings/cropdata/l1', [\App\Http\Controllers\Admin\Account\l1validator\CropDataValidationController::class, 'cropdata_pending_lists']);
Route::get('pending/cropdata/plot/{plotunique}/{plotno}', [\App\Http\Controllers\Admin\Account\l1validator\CropDataValidationController::class, 'cropdata_pending_detail']);
Route::get('cropdata/search/{status}', [\App\Http\Controllers\Admin\Account\l1validator\CropDataValidationController::class, 'search']);

// Approved Crop Data
Route::get('approved/cropdata/l1', [\App\Http\Controllers\Admin\Account\l1validator\CropDataValidationController::class, 'cropdata_approved_lists']);
Route::get('approved/cropdata/plot/{plotunique}/{plotno}', [\App\Http\Controllers\Admin\Account\l1validator\CropDataValidationController::class, 'cropdata_approved_detail']);
```

**Aeration Validation:**
```php
// Pending Aeration
Route::get('aeration/search/{status}', [\App\Http\Controllers\Admin\Account\l1validator\AerationValidationController::class, 'search']);
Route::get('pendings/aeration/l1', [\App\Http\Controllers\Admin\Account\l1validator\AerationValidationController::class, 'awd_pending_lists']);
Route::get('pending/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [\App\Http\Controllers\Admin\Account\l1validator\AerationValidationController::class, 'awd_pending_detail']);

// Approved Aeration
Route::get('approved/aeration/l1', [\App\Http\Controllers\Admin\Account\l1validator\AerationValidationController::class, 'awd_approved_lists']);
Route::get('approved/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [\App\Http\Controllers\Admin\Account\l1validator\AerationValidationController::class, 'awd_approved_detail']);

// Rejected Aeration
Route::get('rejected/aeration/l1', [\App\Http\Controllers\Admin\Account\l1validator\AerationValidationController::class, 'awd_reject_lists']);
Route::get('rejected/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [\App\Http\Controllers\Admin\Account\l1validator\AerationValidationController::class, 'awd_reject_detail']);
```

**Pipe Installation Validation:**
```php
// Pending Pipe Installation
Route::get('pipeinstallation/search/{status}', [\App\Http\Controllers\Admin\Account\l1validator\PipeValidationController::class, 'search']);
Route::get('pendings/pipeinstalltion/l1', [\App\Http\Controllers\Admin\Account\l1validator\PipeValidationController::class, 'pipe_pending_lists']);
Route::get('pending/pipeinstallation/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l1validator\PipeValidationController::class, 'pipe_pending_detail']);

// Rejected Pipe Installation
Route::get('rejected/pipeinstalltion/l1', [\App\Http\Controllers\Admin\Account\l1validator\PipeValidationController::class, 'pipe_reject_lists']);
Route::get('rejected/pipeinstallation/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l1validator\PipeValidationController::class, 'pipe_reject_detail']);

// Approved Pipe Installation
Route::get('approved/pipeinstalltion/l1', [\App\Http\Controllers\Admin\Account\l1validator\PipeValidationController::class, 'pipe_approved_lists']);
Route::get('approved/pipeinstallation/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l1validator\PipeValidationController::class, 'pipe_approved_detail']);
```

#### L2 Validator Routes (Universal Access)

**Benefit Validation:**
```php
// Counting and Statistics
Route::get('l2/fetch/counting', [\App\Http\Controllers\Admin\Account\l2validator\L2ValidatorController::class, 'counting']);

// Pending Benefits
Route::get('pendings/benefit/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2BenefitValidationController::class, 'benefit_pending_lists']);
Route::get('l2/pending/benefit/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2BenefitValidationController::class, 'benefit_pending_detail']);
Route::get('l2/benefit/search/{status}', [\App\Http\Controllers\Admin\Account\l2validator\L2BenefitValidationController::class, 'search']);

// Approved Benefits
Route::get('approved/benefit/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2BenefitValidationController::class, 'benefit_approved_lists']);
Route::get('l2/approved/benefit/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2BenefitValidationController::class, 'benefit_approved_detail']);
```

**Crop Data Validation:**
```php
// Pending Crop Data
Route::get('pendings/cropdata/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2CropDataValidationController::class, 'cropdata_pending_lists']);
Route::get('l2/pending/cropdata/plot/{plotunique}/{plotno}', [\App\Http\Controllers\Admin\Account\l2validator\L2CropDataValidationController::class, 'cropdata_pending_detail']);
Route::get('l2/cropdata/search/{status}', [\App\Http\Controllers\Admin\Account\l2validator\L2CropDataValidationController::class, 'search']);

// Approved Crop Data
Route::get('approved/cropdata/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2CropDataValidationController::class, 'cropdata_approved_lists']);
Route::get('l2/approved/cropdata/plot/{plotunique}/{plotno}', [\App\Http\Controllers\Admin\Account\l2validator\L2CropDataValidationController::class, 'cropdata_approved_detail']);
```

**Aeration Validation:**
```php
// Pending Aeration
Route::get('l2/aeration/search/{status}', [\App\Http\Controllers\Admin\Account\l2validator\L2AerationValidationController::class, 'search']);
Route::get('pendings/aeration/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2AerationValidationController::class, 'awd_pending_lists']);
Route::get('l2/pending/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [\App\Http\Controllers\Admin\Account\l2validator\L2AerationValidationController::class, 'awd_pending_detail']);

// Approved Aeration
Route::get('approved/aeration/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2AerationValidationController::class, 'awd_approved_lists']);
Route::get('l2/approved/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [\App\Http\Controllers\Admin\Account\l2validator\L2AerationValidationController::class, 'awd_approved_detail']);

// Rejected Aeration
Route::get('rejected/aeration/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2AerationValidationController::class, 'awd_reject_lists']);
Route::get('l2/rejected/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [\App\Http\Controllers\Admin\Account\l2validator\L2AerationValidationController::class, 'awd_reject_detail']);
```

**Pipe Installation & Polygon Management:**
```php
// Pending Pipe Installation
Route::get('l2/pipeinstallation/search/{status}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'search']);
Route::get('pendings/pipeinstalltion/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'pipe_pending_lists']);
Route::get('l2/pending/pipeinstallation/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'pipe_pending_detail']);

// Polygon Map View (NEW - with pagination)
Route::get('map/polygon', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'polygon_map_view']);

// Polygon Management
Route::get('all/polygon', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'all_polygon_list']);
Route::get('l2/all/polygon/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'polygon_all_detail']);

// Pending Polygons
Route::get('pendings/pipe/polygon/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'pending_polygon_list']);
Route::get('l2/pending/pipe/polygon/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'polygon_pending_detail']);

// Approved Polygons
Route::get('approved/pipe/polygon/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'polygon_approved_lists']);
Route::get('l2/approved/pipe/polygon/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'polygon_approved_detail']);

// Move Polygon to Pending (NEW FEATURE)
Route::post('l2/polygon/move-to-pending/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'movePolygonToPending']);

// Rejected Polygons
Route::get('rejected/pipe/polygon/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'polygon_reject_lists']);
Route::get('l2/rejected/pipe/polygon/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'polygon_rejected_detail']);

// Rejected Pipe Installation
Route::get('rejected/pipeinstalltion/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'pipe_reject_lists']);
Route::get('l2/rejected/pipeinstallation/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'pipe_reject_detail']);

// Approved Pipe Installation
Route::get('approved/pipeinstalltion/l2', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'pipe_approved_lists']);
Route::get('l2/approved/pipeinstallation/plot/{plotunique}', [\App\Http\Controllers\Admin\Account\l2validator\L2PipeValidationController::class, 'pipe_approved_detail']);
```

## 🎯 L2 Validator Specific Routes

### L2 Dashboard and Core Functions
```php
Route::prefix("l2")->group(function(){
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // All Plots Management
    Route::get('all-plot', [L2ValidatorController::class, 'all_plots']);
    Route::get('all-farmer/show/{id}/{uniqueid}', [L2ValidatorController::class, 'all_show_plot']);
    Route::get('all-farmer/plot/{id}', [L2ValidatorController::class, 'all_plot_detail']);
    Route::get('fetch/counting', [L2ValidatorController::class, 'counting']);

    // Approved Plots
    Route::get('approved/search', [L2ValidatorController::class, 'approved_search']);
    Route::get('approved/plots', [L2ValidatorController::class, 'approved_lists']);
    Route::get('approved/plot/detail/{id}', [L2ValidatorController::class, 'approved_detail']);
    Route::get('approved/show/{uniqueid}', [L2ValidatorController::class, 'approved_show']);

    // Pending Plots
    Route::get('pending/search/{status}', [L2ValidatorController::class, 'pending_search']);
    Route::get('pendings/plots', [L2ValidatorController::class, 'pending_lists']);
    Route::get('pending/plot/detail/{id}/{uniqueid}', [L2ValidatorController::class, 'pending_detail']);
    Route::post('final/status/{type}/{uniqueid}', [L2ValidatorController::class, 'final_farmer_status']);
    Route::get('pending/show/{id}/{uniqueid}', [L2ValidatorController::class, 'pending_show']);
    Route::get('show/edit/{id}/{uniqueid}', [L2ValidatorController::class, 'show_edit']);
    Route::post('update/{id}', [L2ValidatorController::class, 'update']);
    Route::post('status/{type}/{uniqueid}', [L2ValidatorController::class, 'farmer_status']);
    Route::get('plot/edit/{id}/{uniqueid}', [L2ValidatorController::class, 'plotEdit']);

    // Rejected Plots
    Route::get('reject/search/{status}', [L2ValidatorController::class, 'reject_search']);
    Route::get('reject/plots', [L2ValidatorController::class, 'reject_lists']);
    Route::get('farmer-awd-rejected', [L2ValidatorController::class, 'not_eligible_farmer']);
    Route::get('reject/plot/detail/{id}', [L2ValidatorController::class, 'reject_detail']);
    Route::get('reject/show/{id}/{uniqueid}', [L2ValidatorController::class, 'reject_show']);

    // Download Management
    Route::get('fetch/appoved/counting', [L2ValidatorController::class, 'counting']);
    Route::get('download/file', [L2ValidatorController::class, 'downloadFile'])->name('admin.download.file');
    Route::get('download/file/{type}/{unique}/{plotno}/{status}', [L2ValidatorController::class, 'excel_download']);
    Route::resource('download', \App\Http\Controllers\Admin\DownloadManager::class)->names('admin.download.manager');

    // Pipe Installation Management
    Route::get('pipeinstallation/plot/{plotunique}', [L2ValidatorController::class, 'pipeinstalltion_plot']);
    Route::get('plot/pipe/polygon/{uniqueplotid}', [L2ValidatorController::class, 'getPolygon']);

    // Aeration Management
    Route::get('awd-captured/plot/{uniqueplotid}', [L2ValidatorController::class, 'awd_captured']);
    Route::get('fetch/appoved/aeration/counting', [\App\Http\Controllers\Admin\l2validator\L2ValidatorController::class, 'aeration_counting']);

    // Farmer Benefit Counting
    Route::get('fetch/appoved/farner_benfit/counting', [\App\Http\Controllers\Admin\l2validator\L2ValidatorController::class, 'farmer_benefit']);

    // Polygon Counting
    Route::get('fetch/polygon/counting', [\App\Http\Controllers\Admin\l2validator\L2ValidatorController::class, 'polygon_counting']);
});
```

### L2 Pipe Installation & Polygon Routes
```php
// Pipe Installation Management
Route::get('pipeinstallation/search/{status}', [L2PipeValidationController::class, 'search']);
Route::get('pendings/pipe-installations', [L2PipeValidationController::class, 'pipe_pending_lists']);
Route::get('pending/pipeinstallation/plot/{plotunique}', [L2PipeValidationController::class, 'pipe_pending_detail']);
Route::post('pipeinstallation/status/{type}/{uniqueid}', [L2PipeValidationController::class, 'pipeinstallation_validation']);
Route::post('polygon/validation/status/{type}/{uniqueid}', [L2PipeValidationController::class, 'polygon_validation']);

// Polygon Map View (with pagination)
Route::get('map/polygon', [L2PipeValidationController::class, 'polygon_map_view']);

// Polygon Filter with Server-side Pagination
Route::match(['GET','POST'], 'polygon/filter', [L2PipeValidationController::class, 'polygon_filter_list'])->name('l2.polygon.filter');

// Move Polygon to Pending
Route::post('polygon/move-to-pending/{plotunique}', [L2PipeValidationController::class, 'movePolygonToPending']);

// All Polygons
Route::get('all/polygon', [L2PipeValidationController::class, 'all_polygon_lists']);
Route::get('all/polygon/plot/{plotunique}', [L2PipeValidationController::class, 'polygon_all_detail']);

// Pending Polygons
Route::get('pendings/pipe/polygon', [L2PipeValidationController::class, 'polygon_pending_lists']);
Route::get('pending/pipe/polygon/plot/{plotunique}', [L2PipeValidationController::class, 'polygon_pending_detail']);

// Approved Polygons
Route::get('approved/pipe/polygon', [L2PipeValidationController::class, 'polygon_approved_lists']);
Route::get('approved/pipe/polygon/plot/{plotunique}', [L2PipeValidationController::class, 'polygon_approved_detail']);

// Rejected Polygons
Route::get('rejected/pipe/polygon', [L2PipeValidationController::class, 'polygon_reject_lists']);
Route::get('rejected/pipe/polygon/plot/{plotunique}', [L2PipeValidationController::class, 'polygon_reject_detail']);

// Polygon Update
Route::post('pipe/polygon/update/{id}', [L2PipeValidationController::class, 'update_new_polygon']);

// Approved Pipe Installations
Route::get('approved/pipe-installations', [L2PipeValidationController::class, 'pipe_approved_lists']);
Route::get('approved/pipeinstallation/plot/{plotunique}', [L2PipeValidationController::class, 'pipe_approved_detail']);

// Rejected Pipe Installations
Route::get('rejected/pipe-installations', [L2PipeValidationController::class, 'pipe_reject_lists']);
Route::get('rejected/pipeinstallation/plot/{plotunique}', [L2PipeValidationController::class, 'pipe_reject_detail']);
```

### L2 Aeration Routes
```php
// Aeration Management
Route::get('aeration/search/{status}', [L2AerationValidationController::class, 'search']);
Route::get('pendings/aeration', [L2AerationValidationController::class, 'awd_pending_lists']);
Route::get('pending/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [L2AerationValidationController::class, 'awd_pending_detail']);
Route::post('aeration/status/{type}/{uniqueid}', [L2AerationValidationController::class, 'aeration_validation']);

// Approved Aeration
Route::get('approved/aeration', [L2AerationValidationController::class, 'awd_approved_lists']);
Route::get('approved/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [L2AerationValidationController::class, 'awd_approved_detail']);

// Rejected Aeration
Route::get('rejected/aeration', [L2AerationValidationController::class, 'awd_reject_lists']);
Route::get('rejected/aeration/plot/{plotunique}/{aerationno}/{pipeno}', [L2AerationValidationController::class, 'awd_reject_detail']);

// Download Aeration Data
Route::get('aeration/download', [L2AerationValidationController::class, 'downloadFile']);
Route::get('download/aeration/{type}/{unique}/{plotno}/{status}/{aeration}', [L2AerationValidationController::class, 'excel_download']);
```

### L2 Crop Data Routes
```php
// Crop Data Management
Route::get('pendings/cropdata', [L2CropDataValidationController::class, 'cropdata_pending_lists']);
Route::get('cropdata/search/{status}', [L2CropDataValidationController::class, 'search']);
Route::get('pending/cropdata/plot/{plotunique}/{plotno}', [L2CropDataValidationController::class, 'cropdata_pending_detail']);
Route::post('pending/cropdata/update/{uniqueid}', [L2CropDataValidationController::class, 'cropdata_pending_update']);
Route::post('cropdata/status/{uniqueid}/{plotno}', [L2CropDataValidationController::class, 'cropdata_validation']);
Route::post('cropdata/bulk/approval', [L2CropDataValidationController::class, 'bulk_approval']);

// Approved Crop Data
Route::get('approved/cropdata', [L2CropDataValidationController::class, 'cropdata_approved_lists']);
Route::get('approved/cropdata/plot/{plotunique}/{plotno}', [L2CropDataValidationController::class, 'cropdata_approved_detail']);

// Download Crop Data
Route::get('cropdata/download', [L2CropDataValidationController::class, 'downloadFile']);
Route::get('download/cropdata/{type}/{unique}/{plotno}/{status}', [L2CropDataValidationController::class, 'excel_download']);
```

### L2 Benefit Routes
```php
// Benefit Management
Route::get('pendings/benefit', [L2BenefitValidationController::class, 'benefit_pending_lists']);
Route::get('pending/benefit/plot/{plotunique}', [L2BenefitValidationController::class, 'benefit_pending_detail']);
Route::post('benefit/status/{uniqueid}', [L2BenefitValidationController::class, 'benefit_validation']);
Route::get('benefit/search/{status}', [L2BenefitValidationController::class, 'search']);

// Approved Benefits
Route::get('approved/benefit', [L2BenefitValidationController::class, 'benefit_approved_lists']);
Route::get('approved/benefit/plot/{plotunique}', [L2BenefitValidationController::class, 'benefit_approved_detail']);

// Download Benefit Data
Route::get('benefit/download', [L2BenefitValidationController::class, 'downloadFile']);
Route::get('download/benefit/{type}/{unique}/{status}', [L2BenefitValidationController::class, 'excel_download']);
```

### L2 Reports
```php
// L2 Reports
Route::get('report', [App\Http\Controllers\Admin\l2validator\ReportController::class, 'index']);
Route::get('report/download', [App\Http\Controllers\Admin\l2validator\ReportController::class, 'download']);
```

## 📁 File Download Routes

### Download Management
```php
Route::delete('delete/excel/download', [\App\Http\Controllers\Admin\DownloadManager::class, 'destroy']);
Route::delete('delete/geojson/download', [\App\Http\Controllers\Admin\DownloadManager::class, 'destroy_geojson']);
Route::get('geojson/download/{id}', [\App\Http\Controllers\Admin\DownloadManager::class, 'download_geojson']);
```

### Image Download Routes
```php
// Plot Images Download
Route::get('download/{imgtype}/{id}/{uniqueid}/{plotno}', function($type, $id, $uniqueid, $plotno){
    if($type == 'PlotImg'){
        $plotImg = \App\Models\FarmerPlotImage::select('id','plot_no','path')
            ->where('farmer_id', $id)
            ->where('farmer_unique_id', $uniqueid)
            ->where('plot_no', $plotno)
            ->get();
        
        $zip = new ZipArchive;
        $fileName = $uniqueid.'_Plot_Images_'.$plotno.'.zip';
        
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($plotImg as $key => $value){
                $imgdata = Storage::disk('s3')->url($value->path);
                $relativeName = 'plot_no_'.$value->plot_no.'_'.basename($imgdata);
                $zip->addFromString($relativeName, file_get_contents($imgdata));
            }
            $zip->close();
        }
        return response()->download($fileName)->deleteFileAfterSend(true);
    }
    elseif($type == 'BenefitImg'){
        $benefitImg = \App\Models\FarmerBenefitImage::select('id','path')
            ->where('farmer_id', $id)
            ->where('farmer_uniqueId', $uniqueid)
            ->get();
        
        $zip = new \ZipArchive();
        $fileName = 'ImageFile.zip';
        
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($benefitImg as $key => $value){
                $relativeName = basename($value->path);
                $zip->addFromString($relativeName, file_get_contents($value->path));
            }
            $zip->close();
        }
        return response()->download($fileName);
    }
});

// Approved Plot Images Download
Route::get('download/{apprvplot}/{imgtype}/{id}/{uniqueid}/{plotno}', function($apprvplot, $type, $id, $uniqueid, $plotno){
    if($type == 'PlotImg'){
        $plotImg = \App\Models\FinalFarmerPlotImage::select('id','plot_no','path')
            ->where('farmer_unique_id', $uniqueid)
            ->where('plot_no', $plotno)
            ->get();
        
        $zip = new ZipArchive;
        $fileName = $uniqueid.'_Plot_Images_'.$plotno.'.zip';
        
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($plotImg as $key => $value){
                $imgdata = Storage::disk('s3')->url($value->path);
                $relativeName = 'plot_no_'.$value->plot_no.'_'.basename($imgdata);
                $zip->addFromString($relativeName, file_get_contents($imgdata));
            }
            $zip->close();
        }
        return response()->download($fileName)->deleteFileAfterSend(true);
    }
    elseif($type == 'BenefitImg'){
        $benefitImg = \App\Models\FarmerBenefitImage::select('id','path')
            ->where('farmer_id', $id)
            ->where('farmer_uniqueId', $uniqueid)
            ->get();
        
        $zip = new \ZipArchive();
        $fileName = 'ImageFile.zip';
        
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($benefitImg as $key => $value){
                $relativeName = basename($value->path);
                $zip->addFromString($relativeName, file_get_contents($value->path));
            }
            $zip->close();
        }
        return response()->download($fileName);
    }
});

// Old Download Route
Route::get('download old/{imgtype}/{id}/{uniqueid}', function($type, $id, $uniqueid){
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:cache');
    
    $benefitImg = \App\Models\FarmerBenefitImage::select('id','path')
        ->where('farmer_id', $id)
        ->where('farmer_uniqueId', $uniqueid)
        ->get();
    
    $zip = new \ZipArchive();
    $fileName = 'ImageFile.zip';
    $aa = [];
    
    if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
        foreach ($benefitImg as $key => $value){
            $relativeName = basename($value->path);
            $aa[] = $value->path;
        }
        $zip->close();
    }
    return response()->download($fileName);
});
```

## 🔧 Utility Routes

### Cache Management
```php
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
});
```

### Storage Configuration Check
```php
Route::get('/check-storage', function() {
    return response()->json([
        config('storagesystems.store'), 
        config('storagesystems.final_store'), 
        config('storagesystems.appfilename')
    ]);
});
```

## 📊 Route Statistics

### Route Categories
- **Public Routes**: 8 routes
- **Admin Routes**: 12 routes
- **L1 Validator Routes**: 25 routes
- **L2 Validator Routes**: 80+ routes
- **Download Routes**: 6 routes
- **Utility Routes**: 3 routes
- **Total Routes**: 130+ routes

### Controller Distribution
- **L2PipeValidationController**: 25+ routes
- **L2ValidatorController**: 20+ routes
- **L2AerationValidationController**: 8 routes
- **L2CropDataValidationController**: 8 routes
- **L2BenefitValidationController**: 8 routes
- **L1 Controllers**: 25+ routes
- **Admin Controllers**: 12 routes

## 🎯 Key Features

### 1. Role-Based Access Control
- **Admin**: Full access to all features
- **L1 Validator**: First-level validation
- **L2 Validator**: Second-level validation and approval
- **Viewer**: Read-only access

### 2. Validation Workflow
- **Pending**: Items awaiting validation
- **Approved**: Items approved by validators
- **Rejected**: Items rejected with reasons

### 3. Polygon Management
- **Map View**: Interactive polygon visualization
- **Pagination**: Server-side pagination for performance
- **Filter**: Advanced filtering capabilities
- **Move to Pending**: Ability to move approved polygons back to pending

### 4. File Management
- **Image Downloads**: ZIP downloads for plot images
- **Excel Exports**: Data export functionality
- **GeoJSON**: Geographic data downloads

### 5. Search & Filter
- **Status-based Search**: Search by approval status
- **Plot-specific**: Individual plot details
- **Bulk Operations**: Bulk approval capabilities

## 🚀 Recent Updates (October 2025)

### New Features Added:
1. **Polygon Map Pagination**: Server-side pagination for better performance
2. **Move to Pending**: Ability to move approved polygons back to pending
3. **Enhanced Search**: Improved search functionality
4. **Bulk Operations**: Bulk approval for crop data
5. **API Logs**: Admin panel for API log management
6. **KML Management**: Enhanced KML file handling

### Performance Improvements:
- **Caching**: Route-specific caching for better performance
- **Pagination**: Reduced memory usage with pagination
- **Optimized Queries**: Better database query optimization

## 📝 Usage Examples

### Accessing L2 Polygon Map:
```
GET /l2/map/polygon?page=1&per_page=50
```

### Moving Polygon to Pending:
```
POST /l2/polygon/move-to-pending/{plotunique}
```

### Downloading Plot Images:
```
GET /download/PlotImg/{id}/{uniqueid}/{plotno}
```

### Accessing Admin KML Viewer:
```
GET /admin/kml/viewer
```

## 🔒 Security Considerations

### Middleware Protection:
- All routes except public ones are protected by `auth` middleware
- Role-based access control implemented
- CSRF protection enabled

### File Download Security:
- File validation before download
- Temporary file cleanup after download
- S3 integration for secure file storage

---

**Last Updated**: October 2025
**Version**: 2.0
**Status**: ACTIVE
**Maintainer**: Development Team
