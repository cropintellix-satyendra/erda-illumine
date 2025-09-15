<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
//new
use App\Http\Controllers\Admin\l1validator\L1ValidatorController;
use App\Http\Controllers\Admin\l2validator\L2ValidatorController;
use App\Http\Controllers\Admin\settings\TermsandconditionController;
use App\Http\Controllers\Admin\l1validator\PipeValidationController; 
use App\Http\Controllers\Admin\l1validator\AerationValidationController; 
use App\Http\Controllers\Admin\l2validator\L2PipeValidationController; 
use App\Http\Controllers\Admin\l2validator\L2AerationValidationController; 
use App\Http\Controllers\Admin\l1validator\CropDataValidationController; 
use App\Http\Controllers\Admin\l2validator\L2CropDataValidationController; 
use App\Http\Controllers\Admin\l1validator\BenefitValidationController; 
use App\Http\Controllers\Admin\l2validator\L2BenefitValidationController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/term-and-condition', [TermsandconditionController::class, 'web_tnc']);
Route::get('/privacy/policy', [TermsandconditionController::class, 'web_privacy_policy']);

Route::middleware(['web','auth'])->group( function(){
  Route::delete('delete/excel/download',[\App\Http\Controllers\Admin\DownloadManager::class, 'destroy']);

  Route::prefix("l1")->group(function(){
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    //route for all record of any status
    Route::get('all-plot',[L1ValidatorController::class, 'all_plots']);
    Route::get('all-farmer/show/{id}/{uniqueid}',[L1ValidatorController::class, 'all_show_plot']);
    Route::get('all-farmer/plot/{id}',[L1ValidatorController::class, 'all_plot_detail']);
    Route::get('fetch/counting', [L1ValidatorController::class, 'counting']);
    //approved
    Route::get('approved/plots',[L1ValidatorController::class, 'approved_lists']);
    Route::get('approved/plot/detail/{id}',[L1ValidatorController::class, 'approved_detail']);
    Route::get('approved/show/{id}/{uniqueid}',[L1ValidatorController::class, 'approved_show']);

    // pending
    Route::get('pendings/plots',[L1ValidatorController::class, 'pending_lists']);
    Route::get('pending/plot/detail/{id}',[L1ValidatorController::class, 'pending_detail']);
    Route::get('pending/show/{id}/{uniqueid}',[L1ValidatorController::class, 'pending_show']);
    Route::get('show/edit/{id}/{uniqueid}',[L1ValidatorController::class, 'show_edit']);
    Route::post('update/{id}',[L1ValidatorController::class, 'update']);
    Route::post('status/{type}/{uniqueid}',[L1ValidatorController::class, 'farmer_status']);
    Route::get('plot/edit/{id}/{uniqueid}',[L1ValidatorController::class, 'plotEdit']);
    Route::post('plot/update/{id}',[L1ValidatorController::class, 'update']);

    // reject
    Route::get('reject/plots',[L1ValidatorController::class, 'reject_lists']);
    Route::get('reject/plot/detail/{id}',[L1ValidatorController::class, 'reject_detail']);
    Route::get('reject/show/{id}/{uniqueid}',[L1ValidatorController::class, 'reject_show']);

    //search
    Route::get('plot/search/{status}',[L1ValidatorController::class, 'search']);
    //download consent
    Route::get('plot/download/{unique}/{type}/{plotno}',[L1ValidatorController::class, 'download']);
    //download excel
    Route::get('download/file',[L1ValidatorController::class,'downloadFile'])->name('admin.download.file');
    Route::get('download/file/{type}/{unique}/{plotno}/{status}',[L1ValidatorController::class,'excel_download']);
    //download manager
    Route::resource('download',\App\Http\Controllers\Admin\DownloadManager::class)->names('admin.download.manager');

    // pending pipeinstallation
    Route::get('pipeinstallation/search/{status}',[PipeValidationController::class, 'search']);
    Route::get('pendings/pipe-installations',[PipeValidationController::class, 'pipe_pending_lists']);
    Route::get('pending/pipeinstallation/plot/{plotunique}',[PipeValidationController::class, 'pipe_pending_detail']);
    Route::post('pipeinstallation/status/{type}/{uniqueid}',[PipeValidationController::class, 'pipeinstallation_validation']);
    // Route::post('pipe/polygon/calculate',[PipeValidationController::class, 'polygon_area']);
    //update polygon
    Route::post('pipe/polygon/update/{id}',[PipeValidationController::class, 'update_polygon']);

    //reject pipeinstallation
    Route::get('rejected/pipe-installations',[PipeValidationController::class, 'pipe_reject_lists']);    
    Route::get('rejected/pipeinstallation/plot/{plotunique}',[PipeValidationController::class, 'pipe_reject_detail']);
    

    //approved pipe pipeinstallation
    Route::get('approved/pipe-installations',[PipeValidationController::class, 'pipe_approved_lists']); 
    Route::get('approved/pipeinstallation/plot/{plotunique}',[PipeValidationController::class, 'pipe_approved_detail']);
    //download excel  pipeinstallation
    Route::get('pipeinstallation/download',[PipeValidationController::class,'downloadFile']);
    Route::get('download/pipeinstallation/{type}/{unique}/{plotno}/{status}',[PipeValidationController::class,'excel_download']);


    // pending aeration
    Route::get('aeration/search/{status}',[AerationValidationController::class, 'search']);
    Route::get('pendings/aeration',[AerationValidationController::class, 'awd_pending_lists']);
    Route::get('pending/aeration/plot/{plotunique}/{aerationno}/{pipeno}',[AerationValidationController::class, 'awd_pending_detail']);
    Route::post('aeration/status/{type}/{uniqueid}',[AerationValidationController::class, 'aeration_validation']);
    //approved pipe pipeinstallation
    Route::get('approved/aeration',[AerationValidationController::class, 'awd_approved_lists']); 
    Route::get('approved/aeration/plot/{plotunique}/{aerationno}/{pipeno}',[AerationValidationController::class, 'awd_approved_detail']);
    //reject pipeinstallation
    Route::get('rejected/aeration',[AerationValidationController::class, 'awd_reject_lists']);    
    Route::get('rejected/aeration/plot/{plotunique}/{aerationno}/{pipeno}',[AerationValidationController::class, 'awd_reject_detail']);
    //download excel  aeration
    Route::get('aeration/download',[AerationValidationController::class,'downloadFile']);
    Route::get('download/aeration/{type}/{unique}/{plotno}/{status}/{aeration}',[AerationValidationController::class,'excel_download']);

    // Route::get('pending/show/{id}/{uniqueid}',[L1ValidatorController::class, 'pending_show']);
    // Route::get('show/edit/{id}/{uniqueid}',[L1ValidatorController::class, 'show_edit']);
    // Route::post('update/{id}',[L1ValidatorController::class, 'update']);
    // Route::get('plot/edit/{id}/{uniqueid}',[L1ValidatorController::class, 'plotEdit']);
    // Route::post('plot/update/{id}',[L1ValidatorController::class, 'update']);
    //update polygon
    //cropdata
    Route::get('pendings/cropdata',[CropDataValidationController::class, 'cropdata_pending_lists']);
    Route::get('cropdata/search/{status}',[CropDataValidationController::class, 'search']); 
    Route::get('pending/cropdata/plot/{plotunique}/{plotno}',[CropDataValidationController::class, 'cropdata_pending_detail']);
    Route::post('pending/cropdata/update/{uniqueid}',[CropDataValidationController::class, 'cropdata_pending_update']); 
    Route::post('cropdata/status/{uniqueid}/{plotno}',[CropDataValidationController::class, 'cropdata_validation']);
    //approved pipe cropdata
    Route::get('approved/cropdata',[CropDataValidationController::class, 'cropdata_approved_lists']); 
    Route::get('approved/cropdata/plot/{plotunique}/{plotno}',[CropDataValidationController::class, 'cropdata_approved_detail']);
    //download excel  cropdata
    Route::get('cropdata/download',[CropDataValidationController::class,'downloadFile']);
    Route::get('download/cropdata/{type}/{unique}/{plotno}/{status}',[CropDataValidationController::class,'excel_download']);
    //benefit
    Route::get('pendings/benefit',[BenefitValidationController::class, 'benefit_pending_lists']);
    Route::get('pending/benefit/plot/{plotunique}',[BenefitValidationController::class, 'benefit_pending_detail']);
    Route::post('benefit/status/{uniqueid}',[BenefitValidationController::class, 'benefit_validation']);
    Route::get('benefit/search/{status}',[BenefitValidationController::class, 'search']); 

    //benefit approve
    Route::get('approved/benefit',[BenefitValidationController::class, 'benefit_approved_lists']);
    Route::get('approved/benefit/plot/{plotunique}',[BenefitValidationController::class, 'benefit_approved_detail']);
    //download excel  benefitdata
    Route::get('benefit/download',[BenefitValidationController::class,'downloadFile']);
    Route::get('download/benefit/{type}/{unique}/{status}',[BenefitValidationController::class,'excel_download']);

  });//end of prefix for l1 validator

  Route::prefix("l2")->group(function(){
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    //route for all record of any status
    Route::get('all-plot',[L2ValidatorController::class, 'all_plots']);
    Route::get('all-farmer/show/{id}/{uniqueid}',[L2ValidatorController::class, 'all_show_plot']);
    Route::get('all-farmer/plot/{id}',[L2ValidatorController::class, 'all_plot_detail']);
    Route::get('fetch/counting', [L2ValidatorController::class, 'counting']);

    //approved
    Route::get('approved/search',[L2ValidatorController::class, 'approved_search']);
    Route::get('approved/plots',[L2ValidatorController::class, 'approved_lists']);
    Route::get('approved/plot/detail/{id}',[L2ValidatorController::class, 'approved_detail']);
    Route::get('approved/show/{uniqueid}',[L2ValidatorController::class, 'approved_show']);

    //pending
    Route::get('pending/search/{status}',[L2ValidatorController::class, 'pending_search']);
    Route::get('pendings/plots',[L2ValidatorController::class, 'pending_lists']);
    Route::get('pending/plot/detail/{id}',[L2ValidatorController::class, 'pending_detail']);

    Route::post('final/status/{type}/{uniqueid}',[L2ValidatorController::class, 'final_farmer_status']);


    Route::get('pending/show/{id}/{uniqueid}',[L2ValidatorController::class, 'pending_show']);
    Route::get('show/edit/{id}/{uniqueid}',[L2ValidatorController::class, 'show_edit']);
    Route::post('update/{id}',[L2ValidatorController::class, 'update']);
    Route::post('status/{type}/{uniqueid}',[L2ValidatorController::class, 'farmer_status']);
    Route::get('plot/edit/{id}/{uniqueid}',[L2ValidatorController::class, 'plotEdit']);


    // reject
    Route::get('reject/search/{status}',[L2ValidatorController::class, 'reject_search']);
    Route::get('reject/plots',[L2ValidatorController::class, 'reject_lists']);
    Route::get('reject/plot/detail/{id}',[L2ValidatorController::class, 'reject_detail']);
    Route::get('reject/show/{id}/{uniqueid}',[L2ValidatorController::class, 'reject_show']);

    Route::get('fetch/appoved/counting', [L2ValidatorController::class, 'counting']);
    //download excel
    Route::get('download/file',[L2ValidatorController::class,'downloadFile'])->name('admin.download.file');
    Route::get('download/file/{type}/{unique}/{plotno}/{status}',[L2ValidatorController::class,'excel_download']);
    //download manager
    Route::resource('download',\App\Http\Controllers\Admin\DownloadManager::class)->names('admin.download.manager');

    //pipeinstallation
    Route::get('pipeinstallation/plot/{plotunique}',[L2ValidatorController::class, 'pipeinstalltion_plot']);
    Route::get('plot/pipe/polygon/{uniqueplotid}',[L2ValidatorController::class, 'getPolygon']);

    //aeration
    Route::get('awd-captured/plot/{uniqueplotid}',[L2ValidatorController::class, 'awd_captured']);

    // pending pipeinstallation
    Route::get('pipeinstallation/search/{status}',[L2PipeValidationController::class, 'search']);
    Route::get('pendings/pipe-installations',[L2PipeValidationController::class, 'pipe_pending_lists']);
    Route::get('pending/pipeinstallation/plot/{plotunique}',[L2PipeValidationController::class, 'pipe_pending_detail']);
    Route::post('pipeinstallation/status/{type}/{uniqueid}',[L2PipeValidationController::class, 'pipeinstallation_validation']);
    // Route::post('pipe/polygon/calculate',[L2PipeValidationController::class, 'polygon_area']);
    //update polygon
    Route::post('pipe/polygon/update/{id}',[L2PipeValidationController::class, 'update_polygon']);

    //approved pipe pipeinstallation
    Route::get('approved/pipe-installations',[L2PipeValidationController::class, 'pipe_approved_lists']); 
    Route::get('approved/pipeinstallation/plot/{plotunique}',[L2PipeValidationController::class, 'pipe_approved_detail']);
    
    //reject pipeinstallation
    Route::get('rejected/pipe-installations',[L2PipeValidationController::class, 'pipe_reject_lists']);    
    Route::get('rejected/pipeinstallation/plot/{plotunique}',[L2PipeValidationController::class, 'pipe_reject_detail']);

    // pending aeration
    Route::get('aeration/search/{status}',[L2AerationValidationController::class, 'search']);
    Route::get('pendings/aeration',[L2AerationValidationController::class, 'awd_pending_lists']);
    Route::get('pending/aeration/plot/{plotunique}/{aerationno}/{pipeno}',[L2AerationValidationController::class, 'awd_pending_detail']);
    Route::post('aeration/status/{type}/{uniqueid}',[L2AerationValidationController::class, 'aeration_validation']);
    //approved pipe pipeinstallation
    Route::get('approved/aeration',[L2AerationValidationController::class, 'awd_approved_lists']); 
    Route::get('approved/aeration/plot/{plotunique}/{aerationno}/{pipeno}',[L2AerationValidationController::class, 'awd_approved_detail']);
    //reject pipeinstallation
    Route::get('rejected/aeration',[L2AerationValidationController::class, 'awd_reject_lists']);    
    Route::get('rejected/aeration/plot/{plotunique}/{aerationno}/{pipeno}',[L2AerationValidationController::class, 'awd_reject_detail']);
    //download excel  aeration
    Route::get('aeration/download',[L2AerationValidationController::class,'downloadFile']);
    Route::get('download/aeration/{type}/{unique}/{plotno}/{status}/{aeration}',[L2AerationValidationController::class,'excel_download']);

    //cropdata
    Route::get('pendings/cropdata',[L2CropDataValidationController::class, 'cropdata_pending_lists']);
    Route::get('cropdata/search/{status}',[L2CropDataValidationController::class, 'search']); 
    Route::get('pending/cropdata/plot/{plotunique}/{plotno}',[L2CropDataValidationController::class, 'cropdata_pending_detail']);
    Route::post('pending/cropdata/update/{uniqueid}',[L2CropDataValidationController::class, 'cropdata_pending_update']); 
    Route::post('cropdata/status/{uniqueid}/{plotno}',[L2CropDataValidationController::class, 'cropdata_validation']);
    //approved pipe pipeinstallation
    Route::get('approved/cropdata',[L2CropDataValidationController::class, 'cropdata_approved_lists']); 
    Route::get('approved/cropdata/plot/{plotunique}/{plotno}',[L2CropDataValidationController::class, 'cropdata_approved_detail']);
    //download excel  cropdata
    Route::get('cropdata/download',[L2CropDataValidationController::class,'downloadFile']);
    Route::get('download/cropdata/{type}/{unique}/{plotno}/{status}',[L2CropDataValidationController::class,'excel_download']);

    //l2 benefit
    Route::get('pendings/benefit',[L2BenefitValidationController::class, 'benefit_pending_lists']);
    Route::get('pending/benefit/plot/{plotunique}',[L2BenefitValidationController::class, 'benefit_pending_detail']);
    Route::post('benefit/status/{uniqueid}',[L2BenefitValidationController::class, 'benefit_validation']);
    Route::get('benefit/search/{status}',[L2BenefitValidationController::class, 'search']); 

    //benefit approve
    Route::get('approved/benefit',[L2BenefitValidationController::class, 'benefit_approved_lists']);
    Route::get('approved/benefit/plot/{plotunique}',[L2BenefitValidationController::class, 'benefit_approved_detail']);
    //download excel  benefitdata
    Route::get('benefit/download',[L2BenefitValidationController::class,'downloadFile']);
    Route::get('download/benefit/{type}/{unique}/{status}',[L2BenefitValidationController::class,'excel_download']);
  });//end of prefix for l2 validator


});



 Route::get('/check-storage', function() {
   return response()->json([config('storagesystems.store'), config('storagesystems.final_store'), config('storagesystems.appfilename')   ]);
    // return what you want
});

Route::get('download/{imgtype}/{id}/{uniqueid}/{plotno}',function($type,$id,$uniqueid,$plotno){//download zip for plotimage
    if($type =='PlotImg'){

        $plotImg =\App\Models\FarmerPlotImage::select('id','plot_no','path')->where('farmer_id',$id)->where('farmer_unique_id',$uniqueid)->where('plot_no',$plotno)->get();
        // $zip = new \ZipArchive();
        $zip = new ZipArchive;
        // $fileName = base_path('public/ImageFile.zip');
        $fileName = $uniqueid.'_Plot_Images_'.$plotno.'.zip';
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($plotImg as $key => $value){
                $imgdata =Storage::disk('s3')->url($value->path);//base_path('public/storage/'.$value->path); //has image path
                $relativeName =  'plot_no_'.$value->plot_no.'_'.basename($imgdata);   //filename
                // $zip->addFile($imgdata, $relativeName);
                // dd($imgdata);
                $zip->addFromString($relativeName, file_get_contents($imgdata));
                // $zip->addFileFromStream($relativeName, $imgdata); //
                // $zip->put($relativeName, $imgdata);
            }
            $zip->close();
        }
        return response()->download($fileName)->deleteFileAfterSend(true);// this line of code download zip and then delete zip file which was created in public folder
    }elseif($type =='BenefitImg'){
        $benefitImg =\App\Models\FarmerBenefitImage::select('id','path')->where('farmer_id',$id)->where('farmer_uniqueId',$uniqueid)->get();
        $zip = new \ZipArchive();
        // $fileName = base_path('public/resources.zip');
         $fileName = 'ImageFile.zip';
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($benefitImg as $key => $value){
                // $value=Storage::disk('s3')->url($value->path);//base_path('public/storage/'.$value->path); //has image path
                $relativeName = basename($value->path);//filename
                $zip->addFromString($relativeName, file_get_contents($value->path));
                // $zip->addFile($value, $relativeName);
            }
            $zip->close();
        }
         return response()->download($fileName);
    }

});

// route for download approve image for plot image
Route::get('download/{apprvplot}/{imgtype}/{id}/{uniqueid}/{plotno}',function($apprvplot,$type,$id,$uniqueid,$plotno){//download zip for plotimage
    if($type =='PlotImg'){
        $plotImg =\App\Models\FinalFarmerPlotImage::select('id','plot_no','path')->where('farmer_unique_id',$uniqueid)->where('plot_no',$plotno)->get();

        // $zip = new \ZipArchive();
        $zip = new ZipArchive;
        // $fileName = base_path('public/ImageFile.zip');
        $fileName = $uniqueid.'_Plot_Images_'.$plotno.'.zip';
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($plotImg as $key => $value){
                $imgdata =Storage::disk('s3')->url($value->path);//base_path('public/storage/'.$value->path); //has image path
                $relativeName =  'plot_no_'.$value->plot_no.'_'.basename($imgdata);   //filename
                // $zip->addFile($imgdata, $relativeName);
                // dd($imgdata);
                $zip->addFromString($relativeName, file_get_contents($imgdata));
                // $zip->addFileFromStream($relativeName, $imgdata); //
                // $zip->put($relativeName, $imgdata);
            }
            $zip->close();
        }
        return response()->download($fileName)->deleteFileAfterSend(true);// this line of code download zip and then delete zip file which was created in public folder
    }elseif($type =='BenefitImg'){
        $benefitImg =\App\Models\FarmerBenefitImage::select('id','path')->where('farmer_id',$id)->where('farmer_uniqueId',$uniqueid)->get();
        $zip = new \ZipArchive();
        // $fileName = base_path('public/resources.zip');
         $fileName = 'ImageFile.zip';
        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($benefitImg as $key => $value){
                // $value=Storage::disk('s3')->url($value->path);//base_path('public/storage/'.$value->path); //has image path
                $relativeName = basename($value->path);//filename
                $zip->addFromString($relativeName, file_get_contents($value->path));
                // $zip->addFile($value, $relativeName);
            }
            $zip->close();
        }
         return response()->download($fileName);
    }

});
// end for approved plotimage download

Route::get('download old/{imgtype}/{id}/{uniqueid}',function($type,$id,$uniqueid){//download zip for plotimage
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('config:cache');

        $benefitImg =\App\Models\FarmerBenefitImage::select('id','path')->where('farmer_id',$id)->where('farmer_uniqueId',$uniqueid)->get();
        $zip = new \ZipArchive();
        // $fileName = base_path('public/resources.zip');
         $fileName = 'ImageFile.zip';

         $aa = [];

        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE){
            foreach ($benefitImg as $key => $value){
                // $value=Storage::disk('s3')->url($value->path);//base_path('public/storage/'.$value->path); //has image path
                $relativeName = basename($value->path);//filename
                // $zip->addFromString($relativeName, file_get_contents($value->path));
                // $zip->addFile($value, $relativeName);
                $aa[] =$value->path;
            }

            // dd($aa);

            $zip->close();
        }
         return response()->download($fileName);
 });

 Route::get('/clear-cache', function() {
     Artisan::call('cache:clear');
     Artisan::call('view:clear');
     Artisan::call('config:cache');
    dd('dddsss');
    // return what you want
});

Route::get('/', [LoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/signin', [LoginController::class, 'login']);
