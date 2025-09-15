<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\MediquadminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Account\UserController;
use App\Http\Controllers\Admin\Account\ValidatorL1Controller;
use App\Http\Controllers\Admin\Account\ViewerController;
use App\Http\Controllers\Admin\Account\ValidatorL2Controller;
use App\Http\Controllers\Admin\Account\CompanyController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Account\FarmerController;
use App\Http\Controllers\Admin\settings\YearController;
use App\Http\Controllers\Admin\settings\LandownershipController;
use App\Http\Controllers\Admin\settings\LocationController;
use App\Http\Controllers\Admin\plot\PlotController;
use App\Http\Controllers\Admin\settings\RelationshipownerController;
use App\Http\Controllers\Admin\settings\CropvarietyController;
use App\Http\Controllers\Admin\settings\TermsandconditionController;
use App\Http\Controllers\Admin\settings\SeasonController;
use App\Http\Controllers\Admin\settings\GenderController;
use App\Http\Controllers\Admin\settings\FertilizerController;
use App\Http\Controllers\Admin\settings\BenefitController;
use App\Http\Controllers\Admin\settings\SettingController;
use App\Http\Controllers\Admin\Apprvfarmer\FarmerApprovController;
use App\Http\Controllers\Admin\Account\AdminUserController;
use App\Http\Controllers\Admin\settings\OrganizationController;
use App\Http\Controllers\Admin\CordinateController;
use App\Http\Controllers\Admin\Account\CallerlistController;
use App\Http\Controllers\Admin\settings\FarmerQuestionController;
use App\Http\Controllers\Admin\settings\DocumentTypeController;
use App\Http\Controllers\Admin\settings\DailyTargetController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/
Route::middleware(['web','auth'])->group( function(){
  //admin report
  //Route::get('report',[App\Http\Controllers\Admin\AllReportController::class,'index']);
  //Route::get('report/download',[App\Http\Controllers\Admin\AllReportController::class,'download']);
  Route::get('report',[App\Http\Controllers\Admin\AllReportController::class,'index']);
  Route::get('report/download',[App\Http\Controllers\Admin\AllReportController::class,'download']);
  Route::get('/search_surveyor', [App\Http\Controllers\Admin\AllReportController::class, 'searchSurveyor'])->name('search_surveyor');
  Route::get('reportcount',[App\Http\Controllers\Admin\AllReportController::class,'report'])->name('reportcount');

   Route::get('plot/search/{status}',[FarmerController::class, 'search']);
   Route::get('plot/search',[FarmerController::class, 'search_all']);
   
   //For Polygon ranges
   Route::get('/poly-ranges',[CordinateController:: class,'index'])->name('admin.polyrange');


   Route::get('farmer/trash/record',[FarmerController::class, 'list_trash']);
   Route::get('farmers/trashshow/{id}/{uniqueid}',[FarmerController::class, 'showtrash']);
   Route::delete('farmers/trashshow/delete/{id}',[FarmerController::class,'destroy_trash']);
   Route::delete('farmers/trashshowall/delete',[FarmerController::class,'destroy_trashall']);
   Route::get('farmers/updateunique/plot',[FarmerController::class,'updte_unique_plot']);

  Route::get('assign/role',[ViewerController::class,'assignrole']);

  Route::get('/dashboard', [DashboardController::class, 'index']);
  Route::get('fetch/dashboard/counting', [DashboardController::class, 'counting']);
  Route::get('fetch/all-farmer/counting', [DashboardController::class, 'all_farmers_counting']);
  Route::get('fetch/polygon/counting', [FarmerApprovController::class, 'polygon_counting']);
  Route::get('fetch/pipe/counting', [FarmerApprovController::class, 'pipe_counting']);
  Route::get('fetch/counting', [FarmerController::class, 'counting']);
        //Onboarding Counting
        Route::get('fetch/onboard/counting', [FarmerApprovController::class, 'onboarding_counting']);

  Route::get('daily/data/entry/records', [DashboardController::class, 'daily_data_entry_records']);
  Route::get('daily/data/entry/records/6months', [DashboardController::class, 'monthly_data_entry_records_in_6month']);
  Route::get('daily/data/entry/records/6months', [DashboardController::class, 'monthly_data_entry_records_in_6month']);
  Route::Post('filter/pie/chart',[DashboardController::class,'filter_pie_chart'])->name('filter.pie.chart');
  Route::get('crop/data/count',[DashboardController::class,'crop_data_count'])->name('crop.data.count');
  Route::get('polygon/data/count',[DashboardController::class,'polygon_data_count'])->name('polygon.data.count');
  Route::get('pipeinstallation/data/count',[DashboardController::class,'pipeinstallation_data_count'])->name('pipeinstallation.data.count');
  Route::get('aeration/data/count',[DashboardController::class,'areation_data_count'])->name('aeration.data.count');
  Route::get('aeration2/data/count',[DashboardController::class,'areation2_data_count'])->name('aeration2.data.count');
  Route::get('aeration3/data/count',[DashboardController::class,'areation3_data_count'])->name('aeration3.data.count');
  Route::get('aeration4/data/count',[DashboardController::class,'areation4_data_count'])->name('aeration4.data.count');
  Route::post('fetch/organization',[DashboardController::class,'fetch_organization'])->name('fetch.organization');
  Route::post('fetch/filter/graph',[DashboardController::class,'filter_graph'])->name('fetch.filter.graph');
  Route::post('fetch/filter/organization/graph',[DashboardController::class,'organization_filter_graph'])->name('organization.fetch.filter.graph');
  Route::post('fetch/filter/district/graph',[DashboardController::class,'district_filter'])->name('district.fetch.filter.graph');
  Route::post('fetch/filter/taluka/graph',[DashboardController::class,'taluka_filter'])->name('taluka.fetch.filter.graph');

  

  Route::get('/', [DashboardController::class, 'index']);
  Route::get('/logout', [LoginController::class, 'logout']);
  Route::get('/profile',[AdminController::class, 'admin_profile']);
  Route::post('/profile-update/{id}',[AdminController::class, 'admin_profile_update']);
  Route::get('/dashboard',[DashboardController::class, 'index']);

  
  //This route used when the filter wise chart section's Submit button hit
  Route::post('/clear-cache', [DashboardController::class,'clear'])->name('cache.clear');


  //route for approved record and level 2 validator
  Route::get('farmers/all-plot',[FarmerController::class, 'all_plot']);
  Route::get('farmers/all-farmer/show/{id}/{uniqueid}',[FarmerController::class, 'all_show_plot']);
  Route::get('farmer/all-farmer/plot/{id}',[FarmerController::class, 'all_plot_detail']);
  Route::get('approved/farmer',[FarmerApprovController::class, 'index']);
  Route::get('l2/plot',[FarmerApprovController::class, 'l2_pending_plot_list']);
  Route::get('fetch/appoved/counting', [FarmerApprovController::class, 'counting']);
  Route::get('approved/farmers/show/{uniqueid}',[FarmerApprovController::class, 'show']);
  Route::get('farmer/approved/plot/{id}',[FarmerApprovController::class, 'plot']);
  Route::get('farmers/approved/plot/edit/{id}/{uniqueid}',[FarmerApprovController::class, 'plotEdit']);
  Route::get('download/approved/file',[FarmerApprovController::class,'downloadFile'])->name('admin.download.file');
  Route::get('farmers/approved/download/{unique}/{type}/{plotno}',[FarmerApprovController::class, 'download']);
  Route::get('farmer/approved/search',[FarmerApprovController::class, 'searchlist']);
  Route::post('farmers/approved/update/{id}',[FarmerApprovController::class, 'update']);
  Route::get('approved/pipeinstallation/plot/{plotunique}',[FarmerApprovController::class, 'pipeinstalltion_plot']);
  Route::get('approved/plot/pipe/polygon/{uniqueplotid}',[FarmerApprovController::class, 'getPolygon']);

  Route::get('approved/awd-captured/plot/{uniqueplotid}',[FarmerApprovController::class, 'awd_captured']);
  Route::get('farmers/download/{unique}/{type}/{plotno}',[FarmerController::class, 'download']);
  Route::get('farmers/show/{id}/{uniqueid}',[FarmerController::class, 'show']);
  Route::get('farmers/edit/{id}/{uniqueid}',[FarmerController::class, 'edit']);
  Route::get('farmers/plot/edit/{id}/{uniqueid}',[FarmerController::class, 'plotEdit']);
  Route::post('farmers/update/{id}',[FarmerController::class, 'update']);
  Route::post('farmers/status/{type}/{uniqueid}',[FarmerController::class, 'farmer_status']);
  //final approval status
  Route::post('farmers/final/status/{type}/{uniqueid}',[FarmerController::class, 'final_farmer_status']);
  Route::get('farmer/plot/{id}',[FarmerController::class, 'plot']);
  Route::get('farmer/final/plot/{id}',[FarmerApprovController::class, 'plot_pending_reject']);
  Route::get('plot/final/search/{status}/{finalstatus}',[FarmerApprovController::class, 'search_pending_reject']);
  Route::resource('farmer',FarmerController::class)->names('admin.farmers');
  Route::post('fetch/PlotDetail',[FarmerController::class,'fetch_detail']);

  Route::get('list/admin',[AdminUserController::class, 'index'])->name('admin.adminlist.index');
  Route::get('create-admin',[AdminUserController::class, 'create']);
  Route::post('store-admin',[AdminUserController::class, 'store']);
  Route::get('edit-admin/{id}',[AdminUserController::class, 'edit']);
  Route::put('update-admin/{id}',[AdminUserController::class, 'update']);

  Route::resource('users',UserController::class)->names('admin.users');

  Route::get('user/disable/{id}',[UserController::class,'disable']);
  Route::get('user/enable/{id}',[UserController::class,'enable']);
  Route::get('users/all/device',[UserController::class,'all_remove_device_id']);
  Route::get('user/remove/deviceid/{id}',[UserController::class,'remove_device_id']);
  Route::put('user/change/password/{id}',[UserController::class,'change_password']);
  
  Route::resource('validator',ValidatorL1Controller::class)->names('admin.validator');
 //Caller List Controller
  Route::resource('caller-lists',CallerlistController::class)->names('admin.callerlist');
  

  Route::resource('viewer',ViewerController::class)->names('admin.viewer');
  Route::resource('verifier',ValidatorL2Controller::class)->names('admin.verifier');
  Route::put('validator/2/change/password/{userid}',[ValidatorL2Controller::class,'change_password']);

  Route::resource('company',CompanyController::class)->names('admin.company');
  Route::delete('company/destroy/{id}',[CompanyController::class,'destroy']);
  Route::resource('roles',RoleController::class)->names('admin.roles');
  Route::resource('permissions',PermissionController::class)->names('admin.permissions');
  Route::resource('landownership',LandownershipController::class)->names('admin.landownership');
  Route::resource('relationshipowner',RelationshipownerController::class)->names('admin.relationshipowner');
  Route::resource('cropvariety',CropvarietyController::class)->names('admin.cropvariety');
  Route::resource('terms-and-conditions',TermsandconditionController::class)->names('admin.terms-and-conditions');
  Route::get('web/privacy/policy',[TermsandconditionController::class,'web_privacypolicy']);
  Route::post('store/web/privacy/policy',[TermsandconditionController::class,'store_privacypolicy']);

  Route::post('store/web/termsandcondition',[TermsandconditionController::class,'store_term_condition']);
  
  //notification
  Route::resource('notification',NotificationController::class)->names('admin.notification');

  Route::get('company/terms-and-conditions',[TermsandconditionController::class,'tnc_cquest']);
  Route::post('kosher/terms-and-conditions/store',[TermsandconditionController::class,'tnc_cquest_store']);
  Route::post('kosher/privacy/policy/store',[TermsandconditionController::class,'privacy_policy_cquest_store']);
  Route::post('carboncredit',[TermsandconditionController::class,'store_carbon_credit']);
  Route::resource('season',SeasonController::class)->names('admin.season');

   // Questions 
   Route::resource('questions',FarmerQuestionController::class)->names('admin.questions');

  Route::resource('year',YearController::class)->names('admin.year');
  Route::resource('gender',GenderController::class)->names('admin.gender');
  Route::resource('fertilizer',FertilizerController::class)->names('admin.fertilizer');
  Route::resource('benefit',BenefitController::class)->names('admin.benefit');
  Route::resource('organization',OrganizationController::class)->names('admin.organization');

  //location
  Route::post('fetch/district/{id}',[LocationController::class,'getDistricts']);
  Route::post('fetch/block/{id}',[LocationController::class,'getTaluka']);
  Route::post('fetch/panchayat/{id}',[LocationController::class,'village_panchayat']);
  Route::post('fetch/village/{id}',[LocationController::class,'getVillage']);
  

  Route::get('location',[LocationController::class,'location_list'])->name('admin.location');
  Route::get('state/create',[LocationController::class,'create_state']);
  Route::post('state/store',[LocationController::class,'store_state']);
  Route::get('state/edit/{id}',[LocationController::class,'edit_state']);
  Route::post('state/update/{id}',[LocationController::class,'update_state']);
  Route::delete('state/delete/{id}',[LocationController::class,'delete_state']);

  Route::get('district/create',[LocationController::class,'create_district']);
  Route::post('district/districtstore',[LocationController::class,'store_district']);
  Route::get('district/edit/{id}',[LocationController::class,'edit_district']);
  Route::post('district/edit/{id}',[LocationController::class,'update_district']);
  Route::delete('district/delete/{id}',[LocationController::class,'destroy_district']);

  Route::get('taluka/create',[LocationController::class,'create_taluka']);
  Route::post('taluka/talukastore',[LocationController::class,'store_taluka']);
  Route::get('taluka/edit/{id}',[LocationController::class,'edit_taluka']);
  Route::post('taluka/edit/{id}',[LocationController::class,'update_taluka']);
  Route::delete('taluka/delete/{id}',[LocationController::class,'destroy_taluka']);
  // Route::get('location',[LocationController::class,'village_list'])->name('admin.location');
  Route::get('panchayat/create',[LocationController::class,'create_panchayat']);
  Route::post('panchayat/panchayatstore',[LocationController::class,'store_panchayat']);
  Route::get('panchayat/edit/{id}',[LocationController::class,'edit_panchayat']);
  Route::post('panchayat/edit/{id}',[LocationController::class,'update_panchayat']);
  Route::delete('panchayat/delete/{id}',[LocationController::class,'destroy_panchayat']);
  Route::get('village/create',[LocationController::class,'create_village']);
  Route::post('village/villagestore',[LocationController::class,'store_village']);
  Route::get('village/edit/{id}',[LocationController::class,'edit_village']);
  Route::post('village/edit/{id}',[LocationController::class,'update_village']);
  Route::delete('village/delete/{id}',[LocationController::class,'destroy_village']);
  Route::get('location',[LocationController::class,'location_list'])->name('admin.location');
  Route::get('district',[LocationController::class,'district_list'])->name('admin.settings.Location.district_list');
  
  Route::get('villages',[LocationController::class,'villages_list'])->name('admin.villages');
  Route::get('search/village',[LocationController::class,'villages_list']);

  Route::get('state/assign/{id}',[LocationController::class,'edit_assign']);
  Route::post('state/assign/update/{id}',[LocationController::class,'update_assign'])->name('assign_update');


  Route::get('panchayat',[LocationController::class,'panchayat_list'])->name('admin.panchayat');
  Route::get('taluka',[LocationController::class,'taluka_list'])->name('admin.taluka');


  //minimum
  Route::get('minimum/value',[SettingController::class,'index']);
  Route::get('minimum/create',[SettingController::class,'create']);
  Route::post('minimum/store',[SettingController::class,'store']);
  Route::get('minimum/edit/{id}',[SettingController::class,'edit']);
  Route::post('minimum/update/{id}',[SettingController::class,'update']);
  Route::delete('minimum/delete/{id}',[SettingController::class,'destroy']);
    Route::get('areation/date',[SettingController::class,'areation_date'])->name('admin.areation.date');
  Route::get('areation/date/create',[SettingController::class,'areation_create'])->name('admin.areationdate.create');
  Route::post('areation/date/store',[SettingController::class,'areation_store'])->name('admin.areationdate.store');
  Route::get('areation/date/edit/{id}',[SettingController::class,'areation_edit'])->name('admin.areationdate.edit');
  Route::post('areation/date/update/{id}',[SettingController::class,'areation_update'])->name('admin.areationdate.update');

  //app settings
  Route::get('app/settings',[SettingController::class,'app_setting']);
  Route::get('app/keys',[SettingController::class,'keys_update_list']);
  Route::post('app/keys/update',[SettingController::class,'keys_update']);

  Route::resource('document_types',DocumentTypeController::class)->names('admin.document_type');
  

//   Route::get('app/settings/create',[SettingController::class,'create']);
//   Route::post('app/settings/store',[SettingController::class,'store']);
  Route::get('app/settings/edit/{id}',[SettingController::class,'app_setting_edit']);
  Route::post('app/settings/update/{id}',[SettingController::class,'app_setting_update']);
  Route::delete('app/settings/delete/{id}',[SettingController::class,'destroy']);

  Route::get('cropdata/settings',[SettingController::class,'cropdata_settings']);
  Route::post('cropdata/settings/update/{id}',[SettingController::class,'cropdata_setting_update']);

  Route::get('pipe/threshold/settings',[SettingController::class,'pipe_threshold_settings']);
  Route::post('pipe/threshold/settings/update/{id}',[SettingController::class,'pipe_threshold_setting_update']);


  //pipe settimngs
  Route::get('pipe/setting',[SettingController::class,'main_settings']);
  Route::get('pipe/setting/edit/{id}',[SettingController::class,'pipe_setting_edit']);
  Route::post('pipe/setting/update/{id}',[SettingController::class,'pipe_setting_update']);
  Route::delete('pipe/setting/delete',[SettingController::class,'pipe_setting_delete']);

  //app dashboard settings
  Route::get('app/dashboard',[SettingController::class,'app_dashboard']);
  Route::post('app/dashboard/status',[SettingController::class,'app_dashboard_update']);
  Route::get('app/dashboard/settings/{id}',[SettingController::class,'check_state_dashboard']);

  //plots
  Route::resource('plot',PlotController::class)->names('admin.farmer.plot');
  //download excel
  Route::get('download/file',[FarmerController::class,'downloadFile'])->name('admin.download.file');
  Route::get('download/file/{type}/{unique}/{plotno}/{status}',[FarmerController::class,'excel_download']);
  Route::get('l2/download/file/{type}/{unique}/{plotno}/{status}',[FarmerApprovController::class,'excel_download']);


  Route::get('download/file/{id}/{unique}',[FarmerController::class,'downloadFileindividual']);
  Route::group([
      // 'namespace'  => 'Backpack\BackupManager\app\Http\Controllers',
      // 'prefix'     => config('backpack.base.route_prefix', 'admin'),
      // 'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
  ], function () {
      Route::get('backup', '\App\Http\Controllers\Admin\BackupController@index')->name('backup.index');
      Route::put('backup/create', '\App\Http\Controllers\Admin\BackupController@create')->name('backup.store');
      Route::get('backup/download/', '\App\Http\Controllers\Admin\BackupController@download')->name('backup.download');
      Route::delete('backup/delete/', '\App\Http\Controllers\Admin\BackupController@delete')->name('backup.destroy');
  });

  //download manager
  Route::resource('download',\App\Http\Controllers\Admin\DownloadManager::class)->names('admin.download.manager');

    //Baseline Suervey Form
    Route::get('baseline/survey',[\App\Http\Controllers\Admin\BaselineformController::class,'index'])->name('admin.basline');
    Route::get('baseline/show/{id}/{form_number}',[\App\Http\Controllers\Admin\BaselineformController::class,'show']);
    
    //Stake Holder Form
    Route::get('stake-holder/survey',[\App\Http\Controllers\Admin\BaselineformController::class,'stake_holder_index'])->name('admin.stake_holder');
    Route::get('stake-holder/show/{id}/{form_number}',[\App\Http\Controllers\Admin\BaselineformController::class,'stake_holder_show']);
  
  
    //Daily Target
    Route::resource('daily_target',DailyTargetController::class)->names('admin.daily_target');
});
