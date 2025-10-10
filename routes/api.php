<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FarmerController;
use App\Http\Controllers\Admin\settings\LocationController;
use App\Http\Controllers\Api\V1\CropdataController;
use App\Http\Controllers\Api\V1\FarmerBenefitController;
use App\Http\Controllers\Admin\settings\RelationshipownerController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\FarmerViewController;
use App\Http\Controllers\Api\V1\PipeIntallationController;
use App\Http\Controllers\Api\V1\PolygonController;
use App\Http\Controllers\Admin\settings\SettingController;
use App\Http\Controllers\Api\V1\FarmerEditController;
use App\Http\Controllers\Api\V1\TestController;
use App\Http\Controllers\Api\V1\AerationController;
use App\Http\Controllers\Api\V1\APISettingController;
use App\Http\Controllers\Admin\settings\FertilizerController;
use App\Http\Controllers\Admin\settings\GenderController;
use App\Http\Controllers\Admin\settings\OrganizationController;
use App\Http\Controllers\Admin\Account\CompanyController;
use App\Http\Controllers\Api\V1\CallerlistController;
use App\Http\Controllers\Api\V1\FarmerUpdateController;
use App\Http\Controllers\Api\V1\BaselineFormController;
use App\Http\Controllers\Api\V1\UserTargetController;
use App\Http\Controllers\Api\V1\NearbyPolygonController;
use App\Http\Controllers\Api\V1\PipeController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    return response()->json('done');
   // return what you want
  });

    Route::get('update_location_data',[\App\Http\Controllers\Api\V1\TestController2::class,'update_location_data']);
    Route::post('upload_excel_data',[\App\Http\Controllers\Api\V1\TestController2::class,'upload_excel_data']);


// isolate  testing purpose
// Route::any('assignrole',[TestController::class,'assignrole']);
// Route::get('changell/pacssword',[TestController::class,'changes_password']);
// Route::any('add/role/exit',[TestController::class,'add_role']);
// Route::any('remove/email/users',[TestController::class,'remove_email_users']);
// Route::get('farmers/generate/updateunique/plot',[TestController::class,'updte_unique_plot']);
// Route::get('generate/unique/plot/id',[TestController::class,'add_newupqueplotid']);
// isolate
      // Route::get('move/plot/status/record',[TestController::class,'move_status_to_new_table']);
// Route::get('move/plot/status/record',[UserController::class,'move_status_to_new_table']);
// Route::get('test/change/to/zero',[TestController::class,'change_to_zero']);

// // Route::get('test/change/to/zero',[TestController::class,'change_to_zero']);
// Route::get('change/data/westbengal',[TestController::class,'change_data_westbengal']);
// Route::post('check/plot2',[TestController::class,'check_plot2']);



// Route::any('upload/deleted',[TestController::class,'upload_delete']);

// Route::get('update/location/id',[TestController::class,'uploadlocation']);

// Route::get('update/col/westbengal',[TestController::class,'update_col_westbengal']);
// Route::get('update/col/assam',[TestController::class,'update_col_assam']);

// // Route::get('move/plotcqc/to/plot',[TestController::class,'move_plotcqc_to_plot']);
// // Route::get('move/apprv/to/pend',[TestController::class,'move_data_appr_pending']);
// Route::post('upload/villages',[TestController::class,'upload_village']);
// Route::post('upload/survey/assam',[TestController::class,'newupload_survey_assam']);
// Route::post('upload/survey/bengal',[TestController::class,'upload_surveybengal']);
// Route::post('upload/changes/value',[TestController::class,'changes_value']);
// Route::get('/delete-duplicate-plot',[TestController::class,'delete_data']);
Route::get('current/datetime', function() {
    $now = now();
    return response()->json([
        'date' => $now->format('d/m/Y'), // dd/MM/yyyy
        'time' => $now->format('H:i:s'), // H:i:s
    ]);
});
Route::post('/upload-kml-polygons', [\App\Http\Controllers\Api\V1\TestController2::class, 'upload_kml_polygons']);

Route::get('onboarding/screen',[APISettingController::class,'onboarding_screen']);

Route::any('generateuniqueId',[AuthController::class,'generate_uniqueId']);
Route::any('check/version',[AuthController::class,'check_version']);
Route::post('generate/user/otp',[FarmerController::class,'generate_otp']);
Route::post('validate/user/otp',[FarmerController::class,'validate_otp']);
Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'authenticate']);
Route::post('validate',[AuthController::class,'checkUserValidation']);
Route::get('State',[LocationController::class,'getState']);
Route::get('districts/{id}',[LocationController::class,'getDistricts']);
Route::get('taluka/{id}',[LocationController::class,'getTaluka']);
Route::get('villagepanchayat/{id}',[LocationController::class,'village_panchayat']);
Route::get('village/{id}',[LocationController::class,'getVillage']);
Route::get('relationshipowner',[RelationshipownerController::class,'relationshipowner']);
Route::get('cropvariety',[CropdataController::class, 'cropvariety']);
Route::get('get/seasons',[FarmerBenefitController::class, 'getSeasons']);
Route::get('get/gender',[GenderController::class, 'get_gender']);
Route::get('get/fertilizer',[FertilizerController::class, 'get_fertilizer']);
Route::get('get/benefits',[FarmerBenefitController::class, 'getBenefits']);
Route::get('termandconditions',[FarmerController::class, 'getnc']);
Route::post('dashboard/setting',[SettingController::class, 'app_dashboard_api']);
Route::get('privacy/policy',[UserController::class, 'getPrivacy']);
Route::get('app/termcondition',[UserController::class, 'getTermcondition']);
Route::post('organization',[CompanyController::class, 'get_organization']);
Route::post('districtsid',[LocationController::class,'districts']);

Route::get('oragnization-based-state_list',[CompanyController::class, 'get_organizatios_state']);

Route::get('base/value',[SettingController::class, 'getminimumvalue']);


Route::post('fetch/season',[SettingController::class,'fetch_season']);

//All season
Route::post('fetch/all-season',[SettingController::class,'fetch_season_all']);

//Call verification API
Route::post('caller_list',[CallerlistController::class,'store']);
Route::get('verify_number',[CallerlistController::class,'index']);


//Excel Data Insertion API
// Route::post('upload/pnp-data',[TestController::class,'upload_pnp_data']);
Route::post('upload/pnp-data',[TestController::class,'update_excel_data_using_api']);

//Update an Existing Data which is stored via excel
Route::post('/update/existing/data',[TestController::class,'update_excel_data_using_api']);

Route::get('genrate-onboarding-sheet',[\App\Http\Controllers\Api\V1\TestController2::class,'onboarding_sheet']);


//Report Download from the API use this
Route::get('genrate-aeration-sheet',[\App\Http\Controllers\Api\V1\TestController2::class,'aeration_sheet']);
Route::get('genrate-polygon-sheet',[\App\Http\Controllers\Api\V1\TestController2::class,'polygon_sheet']);
Route::get('genrate-pipe-sheet',[\App\Http\Controllers\Api\V1\TestController2::class,'pipe_sheet']);
Route::get('genrate-onboarding-sheet',[\App\Http\Controllers\Api\V1\TestController2::class,'onboarding_sheet']);
Route::get('genrate-excel',[\App\Http\Controllers\Api\V1\TestController2::class,'genrate_excel']);
Route::post('genrate/geojson',[\App\Http\Controllers\Api\V1\TestController2::class,'genrate_geojson']);
Route::get('store/geojson',[\App\Http\Controllers\Api\V1\TestController2::class,'filegenerateGeoJSON']);

Route::post('upload_farmer',[\App\Http\Controllers\Api\V1\TestController2::class,'upload_farmer']);
Route::post('upload_polygon',[\App\Http\Controllers\Api\V1\TestController2::class,'upload_polygon']);


    //Used for convertion
    Route::get('V1/convertion/values/statewise',[PolygonController::class,'state_wise_data']);


Route::middleware(['auth:sanctum', 'check.user.status'])->group( function () {
  Route::prefix("V1")->group(function(){
    Route::resource('pipe',PipeController::class);

    Route::get('nearby-polygon',[NearbyPolygonController::class,'nearby']);
    
    Route::get('get-farmer-details',[FarmerController::class,'get_farmer_details']);
    Route::post('submit-pipe-data',[PipeIntallationController::class,'submit_pipe_data']);

    Route::get('check-mobile-no',[FarmerController::class,'Check_mobile_no']);
    Route::post('logout',[AuthController::class,'logout']);
    Route::any('generateuniqueId',[AuthController::class,'generate_uniqueId']);
    Route::any('check/version',[AuthController::class,'check_version']);
    Route::post('validate',[AuthController::class,'checkUserValidation']);
    Route::get('districts/{id}',[LocationController::class,'getDistricts']);
    Route::get('taluka/{id}',[LocationController::class,'getTaluka']);
    Route::get('villagepanchayat/{id}',[LocationController::class,'village_panchayat']);
    Route::get('village/{id}',[LocationController::class,'getVillage']);
    Route::get('relationshipowner',[RelationshipownerController::class,'relationshipowner']);
    Route::get('cropvariety',[CropdataController::class, 'cropvariety']);
    Route::get('get/seasons',[FarmerBenefitController::class, 'getSeasons']);
    Route::get('get/benefits',[FarmerBenefitController::class, 'getBenefits']);
    Route::get('termandconditions',[FarmerController::class, 'getnc']);
    Route::post('dashboard/setting',[SettingController::class, 'app_dashboard_api']);
    Route::get('base/value',[SettingController::class, 'getminimumvalue']);
    Route::get('search-type',[FarmerController::class, 'search_type']);
    //Genearate new plot id for polygon.
    Route::get('generate/unique/plot',[FarmerController::class,'generate_plot_id']);
    //farmer onbaording
    Route::resource('farmer',FarmerController::class);
    Route::post('farmer/location/info',[FarmerController::class,'storeLocation']);
    Route::post('farmer/plot',[FarmerController::class,'storePlot']);
    Route::post('storeimage',[FarmerController::class, 'StoreImage']);
    Route::post('farmer/plot/images',[FarmerController::class, 'FarmerPlotImage']);
    Route::get('mobileno',[CropdataController::class, 'mobileno']);
    Route::post('farmer/images',[FarmerController::class,'store_image__last_screen']);
    //Farmer Details
    Route::post('/farmer_details',[FarmerController::class,'farmer_details']);
    //farmer Farm details
    Route::post('/farmer-farm-details',[FarmerController::class,'store_farm_details']);
    Route::get('/farmer-questions',[FarmerController::class,'get_questions']);
    Route::get('/farmer-document',[FarmerController::class,'documents']);
    Route::get('/farmer-number-of-season',[FarmerController::class,'number_of_season']);
    Route::get('/farmer-status',[FarmerController::class,'farmer_status']);
    //Farmer Update
    Route::get('/get_farmer_data',[FarmerController::class,'get_farmer_data']);   
    Route::post('/farmer-info-update',[FarmerUpdateController::class,'update_farmer_onboarding']);
    Route::post('/update-farmer-doc',[FarmerUpdateController::class,'update_image__last_screen']);
    Route::post('/update-farmer-location',[FarmerUpdateController::class,'farmerLocation_update']);
    Route::post('/update-farmer-plot',[FarmerUpdateController::class,'farmerplot_update']);
    Route::post('farmer/plot/images-update',[FarmerUpdateController::class, 'farmer_plot_image_update']);
    Route::post('update-farmer-area', [FarmerUpdateController::class,'update_area']);
    //cropdata
    Route::get('cropdata/setting',[CropdataController::class,'cropdata_settings']);
    Route::get('plotuniqueid/list',[CropdataController::class,'plotuniqueid_list']);
    Route::get('search-uniqueid/list',[CropdataController::class,'plotuniqueid_list_new']);
    Route::get('subplots',[CropdataController::class,'subplots_list']);
    Route::get('crop/subplots',[CropdataController::class,'crop_subplots_list']);
    Route::get('cropdata/plot/detail',[CropdataController::class,'fetch_plot_detail']);
    Route::post('farmer/cropdata',[CropdataController::class, 'farmercropdata']);
    //benefits
    Route::get('farmer/benefits/detail',[FarmerBenefitController::class,'fetch_benefit_detail']);
    Route::post('farmer/benefits/check',[FarmerBenefitController::class,'fetch_benefit_check']);
    Route::resource('farmer/benefits',FarmerBenefitController::class);
    Route::post('farmer/benefits/images',[FarmerBenefitController::class, 'benefit_image']);
    //user
    Route::get('user/profile',[UserController::class,'profile']);
    Route::post('user/profile/update',[UserController::class,'UpdateProfile']);
    //show farmer
    Route::get('user/farmer/count',[FarmerViewController::class,'user_form_count']);
    Route::get('user/farmer/registration',[FarmerViewController::class,'farmer_registration_list']);
    Route::get('user/farmer/registration/search',[FarmerViewController::class,'farmer_registration_list_search']);
    Route::post('user/farmer/registration/detail',[FarmerViewController::class,'farmer_registration_detail']);
    Route::post('user/status/onboarding/list',[FarmerViewController::class,'getOnboardingList']);

     //farmer Rejected list
    Route::get('user/farmer/aeration/search',[FarmerViewController::class,'farmer_aeration_list_search']);
    Route::get('user/farmer/cropdata',[FarmerViewController::class,'farmer_cropdata_list']);
    Route::get('user/farmer/cropdata/search',[FarmerViewController::class,'farmer_cropdata_list_search']);
    Route::post('user/farmer/cropdata/detail',[FarmerViewController::class,'farmer_cropdata_detail']);
    Route::post('user/status/cropdata/list',[FarmerViewController::class,'getCropdataList']);
    Route::get('user/farmer/benefit',[FarmerViewController::class,'farmer_benefit_list']);
    Route::get('user/farmer/benefit/search',[FarmerViewController::class,'farmer_benefit_list_search']);
    Route::post('user/farmer/benefit/detail',[FarmerViewController::class,'farmer_benefit_detail']);
    Route::post('user/status/benefit/list',[FarmerViewController::class,'getBenefitList']);
    //edit data
    Route::get('user/farmer/registration/edit/{uniqueid}/{plotno}',[FarmerEditController::class,'farmer_registration_edit']);
    Route::post('user/farmer/registration/update',[FarmerEditController::class,'farmer_registration_update']);
    Route::post('user/farmer/registration/update/img',[FarmerEditController::class,'farmer_registration_updateimg']);
    Route::post('user/farmer/onboarding/image/update',[FarmerEditController::class,'farmer_onboarding_image']);


    //pipe installation
    Route::get('get/pipe/threshold',[PipeIntallationController::class,'get_threshold']);
    Route::post('check/polyon/short_by',[PipeIntallationController::class,'nearby']);
    //update pipe installation
    Route::post('update-pipe-location',[PipeIntallationController::class,'update_location']);

    

    //baseline Form
    Route::post('baseline-farmer-details',[BaselineFormController::class,'farmer_detail']);
    Route::post('baseline-crop-details',[BaselineFormController::class,'farmer_crop_detail']);
    Route::post('baseline-fertilizer-details',[BaselineFormController::class,'fertilizer_detail']);
    Route::post('baseline-additional-questions',[BaselineFormController::class,'additional_quiz']);
    Route::any('check/polyon/area',[PipeIntallationController::class,'check_polygon_area']);

   //Polygon
    Route::post('polygon-store',[PolygonController::class,'store']);
    Route::any('check/polyon/nearby',[PolygonController::class,'check_polygon_nearby']);
    Route::post('check/coordinates',[PolygonController::class,'check_lat_lng_inside_polygon']);

    Route::post('farmer/pipe/plot/detail',[PipeIntallationController::class,'detail_pipe_plot_id']);
    Route::get('check/pipe/data',[PipeIntallationController::class,'check_pipe_data']);
    Route::get('check/pipe/image/data',[PipeIntallationController::class,'check_pipe_location_image']);
    Route::resource('pipe/installation',PipeIntallationController::class);
    Route::post('pipe/location',[PipeIntallationController::class,'pipes_location']);
    Route::post('new/pipe/location',[PipeIntallationController::class,'pipes_location_new']);
    Route::post('number/pipe/check',[PipeIntallationController::class,'no_of_pipe']);


    //Aeration
    Route::get('check/aeration/data', [AerationController::class,'check_aeration_data']);
    Route::get('pipe/unique/plot', [AerationController::class,'get_plot_uniqueid']);
    Route::get('pipe/unique/plot/pipewise', [AerationController::class,'get_plot_uniqueid_pipewise']);
    Route::get('pipe/plot/pipeno', [AerationController::class,'get_plot_pipe']);
    Route::get('check/aeration/ploygon', [AerationController::class,'get_polygon']);
    Route::post('store/aeration', [AerationController::class,'store']);
    Route::post('store/aeration/image', [AerationController::class,'aeration_image']);
    //aeration for edit
    Route::get('aeration/list', [FarmerViewController::class,'aeration_list']);
    Route::post('aeration/reject/detail', [FarmerViewController::class,'aeration_reject_detail']);
    Route::post('update/aeration/image', [FarmerEditController::class,'aeration_img_update']);

    //pipe for edit
    Route::get('pipe/installtion/list', [FarmerViewController::class,'pipe_installtion_list']);
    Route::get('polygon/reject/list', [FarmerViewController::class,'polygon_list']);    
    Route::post('update/pipe/image', [FarmerEditController::class,'pipe_installtion_img_update']);

    //pendiong polygon
    Route::get('polygon/pending/list',[FarmerViewController::class,'pending_polygon_list']);

    Route::post('update/pipeinstallation',[FarmerEditController::class,'updatePipeInstallation']);
    Route::post('pipeinstallation/polygon',[FarmerViewController::class,'pipe_installtion_polygon']);
    Route::get('search/surveyid/list',[FarmerController::class,'search_survey_list']);
    Route::post('onboarding/data',[FarmerController::class,'onboard_existing']);
    Route::post('onboarding/store/location',[FarmerController::class,'storeLocation_update']);
    Route::post('onboarding/store/plot',[FarmerController::class,'storePlot_update']);    
    
    //For uploading Geo Json File.   
    Route::post('geojson',[\App\Http\Controllers\Api\V1\TestController2::class,'geojson'])->withoutMiddleware('auth:sanctum');
    Route::post('stakeholder-confirmation-form/store',[BaselineFormController::class,'baseline_survey']);

    //Use this to generate Dynamically Generate the Aeration Number 
    Route::get('plotwise/aeration/no',[\App\Http\Controllers\Api\V1\AerationController::class,'plotwise_aeration_no'])->withoutMiddleware('auth:sanctum');

     //Daily Target 
     Route::get('daily/target/view',[UserTargetController::class,'daily_target']);
     Route::post('daily/user-target', [UserTargetController::class, 'user_target']);


         //Module Wise Count
    Route::post('module-wise-count',[UserTargetController::class,'modulewise_count']);
    //BAseline Form Farmer Signatuer

    Route::post('/baseline-form-image', [BaselineFormController::class, 'baseline_form_image']);   
    
    Route::post('/pipe-settings/increment', [PipeIntallationController::class, 'incrementPipeSettings']);
    //Update Concern form
    Route::post('update/consernt_form',[FarmerUpdateController::class,'update_consent_form']);
  });



  Route::prefix("v2")->group(function(){

    Route::resource('pipe',PipeController::class);

    Route::get('check/pipe/data',[App\Http\Controllers\Api\v2\PipeIntallationController::class,'check_pipe_data']);
    Route::get('pipe/plot/pipeno', [App\Http\Controllers\Api\v2\AerationController::class,'get_plot_pipe']);
    Route::get('check/aeration/ploygon', [App\Http\Controllers\Api\v2\AerationController::class,'get_polygon']);
    Route::post('farmer/cropdata',[App\Http\Controllers\Api\v2\CropDataController::class, 'farmercropdata']);
    Route::post('farmer/images',[App\Http\Controllers\Api\v2\CropDataController::class,'store_image__last_screen']);
    Route::get('get/pipe/plotwise/pipeno', [App\Http\Controllers\Api\v2\AerationController::class,'get_plot_pipe_plotwise']);
    Route::get('subplots',[App\Http\Controllers\Api\v2\CropDataController::class,'subplots_list_new']);
    Route::post('number/pipe/check',[PipeIntallationController::class,'pipeinstallation_no']);
    Route::get('check/aeration/data', [AerationController::class,'check_aeration_data_new']);
    Route::get('check/aeration/data/new', [AerationController::class,'check_aeration_data_latest']); // just validate above endpoint with season & financial_year
    Route::post('/update-farmer-doc',[FarmerUpdateController::class,'update_image_last_screen_v2']); // created new endpoint for updating image in last screen on 07/01/2024

  });

  Route::prefix("v3")->group(function(){
    Route::get('check/pipe/data',[PipeIntallationController::class,'check_pipe_data_new']);

  });
  
});
