<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Exports\AllPolygonExport;
use App\Models\VendorLocation;
use App\Models\Farmer;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\L1PendingExport;
use App\Exports\L1ApprovedExport;
use App\Exports\L1RejectedExport;
use App\Exports\CropdataExport;
use App\Exports\PipeInstallationExport;
use App\Exports\AerationExport;
use App\Exports\AerationNoExport;
use App\Exports\L1BenefitExport;
use App\Exports\L2PendingExport;
use App\Exports\ApprovedPlotExport;
use App\Exports\L2RejectExport;
use App\Exports\L2CropdataExport;
use App\Exports\L2PipeInstallationExport;
use App\Exports\L2AerationExport;
use App\Exports\L2BenefitExport;
use App\Exports\AllOnboardingExport;
use Artisan;
// use Log;
use Illuminate\Support\Facades\Log;
use App\Exports\AllCropDataExport;
use App\Models\PipeInstallationPipeImg;
use App\Exports\AllPipeInstallationExport;
use App\Exports\AllAerationExport;
use App\Exports\AllBenefitExport;
use App\Exports\L2PolygonExport;
use App\Exports\PlotOnboardingExport;
use App\Exports\PlotPipeinstallationExport;
use App\Exports\TotalAerationExport;
use App\Exports\TotalPipeExport;
use App\Jobs\ExportExcelJob;
use App\Models\FarmerPlot;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallation;
use App\Models\Aeration;
use App\Models\FarmerBenefit;
use App\Models\Polygon;
use App\Models\ViewerLocation;

class AllReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // dd(phpinfo());
        $page_title = 'Report';
        $action = 'table_farmer';
        $page_description = 'Report';
        $organizations = DB::table('companies')->get();
        // dd($organizations);
        $states = DB::table('states')->where('status',1)->get();
        $districts = DB::table('districts')->where('status',1)->get();
        $talukas = DB::table('talukas')->where('status',1)->get();
        $panchayats = DB::table('panchayats')->get();
        $villages = DB::table('villages')->get();
        $onboarding_executive = User::whereHas('roles', function($q){
                            $q->where('name', 'AppUser');//fetch user from users table hasrole User
                        }
                        )->select('id','name')->orderBy('created_at','desc')->get();
        // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
        $l1_validators =   User::whereHas('roles', function($q){
            $q->whereIn('name',['L-1-Validator']);//fetch user from users table hasrole SuperValidator  L-1-Validator
             })->where('status',1)->orderBy('created_at','desc')->get();
        $l2_validators =   User::whereHas('roles', function($q){
            $q->whereIn('name',['L-2-Validator']);//fetch user from users table hasrole SuperValidator  L-1-Validator
             })->where('status',1)->orderBy('created_at','desc')->get();
        return view('report',compact('page_title','action','page_title','page_description','states','districts','talukas','panchayats','villages','onboarding_executive','l1_validators','l2_validators','organizations'));
    }



    public function report(){
        $page_title = 'Report';
        $action = 'table_farmer';
        $page_description = 'Report';
        $organizations = DB::table('companies')->get();
        $years = DB::table('years')->get();
        $seasons = DB::table('seasons')->get();
        return view('reportcount',compact('page_title','action','page_title','page_description','organizations','years','seasons'));
    }


    public function searchSurveyor(Request $request)
    {
        $search = $request->get('term'); // Get the search term
        $results = User::where('name', 'LIKE', '%' . $search . '%')
                        ->where('status', 1) // Filter users with status 1
                        ->where('role', 'User')
                        ->get();

        $formattedResults = $results->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        });

        return response()->json($formattedResults);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Report download for admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        //  dd(request()->all());

        $job="";
        //start of onboarding
        if(request()->modules == 'Onboarding'){

            if (request()->level == 'All' && request()->report_type == 'Farmer_wise') {

                try {
                    Log::info('Request level is All');

                    $userRole = auth()->user()->roles->first()->name;
                    Log::info('User role: ' . $userRole);

                    $Farmers = FarmerPlot::whereHas('final_farmers', function($q) use ($userRole) {
                        if ($userRole == 'Viewer') {
                            $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                            Log::info('Viewer location: ' . json_encode($viewerlocation));

                            if ($viewerlocation) {
                                $q->whereIn('state_id', explode(',', $viewerlocation->state));
                            } else {
                                throw new Exception("Viewer location not found.");
                            }
                        }

                        if (request()->has('state') && request()->state) {
                            Log::info('State filter: ' . request()->state);
                            $q->where('state_id', 'like', request()->state);
                        }

                        if (request()->has('district') && request()->district) {
                            Log::info('District filter: ' . request()->district);
                            $q->where('district_id', 'like', request()->district);
                        }

                        if (request()->has('taluka') && request()->taluka) {
                            Log::info('Taluka filter: ' . request()->taluka);
                            $q->where('taluka_id', 'like', request()->taluka);
                        }
                    })
                    ->when(request()->has('filter'), function($w) {
                        if (request()->has('start_date') && request()->start_date) {
                            Log::info('Start date filter: ' . request()->start_date);
                            $w->whereDate('created_at', '>=', request()->start_date);
                        }

                        if (request()->has('end_date') && request()->end_date) {
                            Log::info('End date filter: ' . request()->end_date);
                            $w->whereDate('created_at', '<=', request()->end_date);
                        }
                    })
                    ->get();

                    Log::info('Farmers retrieved: ' . $Farmers->count());
                                        if ($Farmers->isEmpty()) {
                        return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                    }

                    $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString().'.xlsx' ;
                    Log::info('Filename: ' . $filename);

                    // dd("in");

                    // ExportExcelJob::dispatch($filename, request()->all());

                    // return Excel::download(new AllOnboardingExport('All', json_encode(request()->all())), $filename);

                    $payload = [
                        'uuid' => \Str::uuid(),
                        'data' => [
                            'command' => '\App\Exports\AllOnboardingExport',
                            'parameters' => ['All', request()->all()],
                            'filename' => $filename,
                            'drive' => 'excel'
                        ]
                    ];
            //dd($payload);
                    $job = \DB::table('temp_jobs')->insert([
                        'queue' => 'excel',
                        'user_id' => auth()->user()->id,
                        'payload' => json_encode($payload),
                        'available_at' => \Carbon\Carbon::now()->timestamp,
                        'created_at' => \Carbon\Carbon::now()->timestamp,
                        'date' => \Carbon\Carbon::today()->toDateString()
                    ]);

                    if (!$job) {
                        return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                    }

                    return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section']);
                } catch (Exception $e) {
                    Log::error('Exception caught: ' . $e->getMessage());
                    return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
                }
            }

            //end of level all

            if (request()->level == 'All' && request()->report_type == 'Plot_wise') {

                try {
                    Log::info('Request level is All');

                    $userRole = auth()->user()->roles->first()->name;
                    Log::info('User role: ' . $userRole);

                    $Farmers = FarmerPlot::whereHas('final_farmers', function($q) use ($userRole) {
                        if ($userRole == 'Viewer') {
                            $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                            Log::info('Viewer location: ' . json_encode($viewerlocation));

                            if ($viewerlocation) {
                                $q->whereIn('state_id', explode(',', $viewerlocation->state));
                            } else {
                                throw new Exception("Viewer location not found.");
                            }
                        }

                        if (request()->has('state') && request()->state) {
                            Log::info('State filter: ' . request()->state);
                            $q->where('state_id', 'like', request()->state);
                        }

                        if (request()->has('district') && request()->district) {
                            Log::info('District filter: ' . request()->district);
                            $q->where('district_id', 'like', request()->district);
                        }

                        if (request()->has('taluka') && request()->taluka) {
                            Log::info('Taluka filter: ' . request()->taluka);
                            $q->where('taluka_id', 'like', request()->taluka);
                        }
                    })
                    ->when(request()->has('filter'), function($w) {
                        if (request()->has('start_date') && request()->start_date) {
                            Log::info('Start date filter: ' . request()->start_date);
                            $w->whereDate('created_at', '>=', request()->start_date);
                        }

                        if (request()->has('end_date') && request()->end_date) {
                            Log::info('End date filter: ' . request()->end_date);
                            $w->whereDate('created_at', '<=', request()->end_date);
                        }
                    })
                    ->get();

                    // Log::info('Farmers retrieved: ' . $Farmers->count());

                    if ($Farmers->isEmpty()) {
                        return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                    }

                    $filename = request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString().'.xlsx';
                    // Log::info('Filename: ' . $filename);

                    // return Excel::download(new PlotOnboardingExport('All', json_encode(request()->all())), $filename);

                    $payload = [
                        'uuid' => \Str::uuid(),
                        'data' => [
                            'command' => '\App\Exports\PlotOnboardingExport',
                            'parameters' => ['All', request()->all()],
                            'filename' => $filename,
                            'drive' => 'excel'
                        ]
                    ];

                    $job = \DB::table('temp_jobs')->insert([
                        'queue' => 'excel',
                        'user_id' => auth()->user()->id,
                        'payload' => json_encode($payload),
                        'available_at' => \Carbon\Carbon::now()->timestamp,
                        'created_at' => \Carbon\Carbon::now()->timestamp,
                        'date' => \Carbon\Carbon::today()->toDateString()
                    ]);

                    if (!$job) {
                        return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                    }

                    return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section']);
                } catch (Exception $e) {
                    Log::error('Exception caught: ' . $e->getMessage());
                    return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
                }
            }


            if(request()->level == 'L1-Validator'){
                    $Farmers = FarmerPlot::whereHas('farmer', function($q){
                        if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                        if(isset(request()->state)  && request()->state){
                            $q->where('state_id','like',request()->state);
                        }
                        if(isset(request()->district)  && request()->district){
                             $q->where('district_id','like',request()->district);
                        }
                        if(isset(request()->taluka)  && request()->taluka){
                             $q->where('taluka_id','like',request()->taluka);
                        }
                    })
                    ->when('fliter',function($w){
                        if(isset(request()->start_date) && request()->start_date){
                            $w->whereDate('created_at','>=',request()->start_date);
                        }
                        if(isset(request()->end_date) && request()->end_date){
                            $w->whereDate('created_at','<=',request()->end_date);
                        }
                        if(request()->status){
                            $w->where('status',request()->status);
                        }
                        if(request()->status != 'Pending' && isset(request()->l1_validator)){
                            $w->where('aprv_recj_userid',request()->l1_validator);
                        }
                    })
                    ->get();
                    if($Farmers->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                // $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                // return Excel::download(new L1RejectedExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->onboarding_l1(request());
                // L1ApprovedExport  L1RejectedExport L1PendingExport
            }

            if(request()->level == 'L2-Validator'){
                $Farmers = FarmerPlot::whereHas('final_farmers', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                        if(isset(request()->state)  && request()->state){
                            $q->where('state_id','like',request()->state);
                        }
                        if(isset(request()->district)  && request()->district){
                             $q->where('district_id','like',request()->district);
                        }
                        if(isset(request()->taluka)  && request()->taluka){
                             $q->where('taluka_id','like',request()->taluka);
                        }
                    })
                    ->when('fliter',function($w){
                        if(isset(request()->start_date) && request()->start_date){
                            $w->whereDate('created_at','>=',request()->start_date);
                        }
                        if(isset(request()->end_date) && request()->end_date){
                            $w->whereDate('created_at','<=',request()->end_date);
                        }
                        if(request()->status){
                            $w->where('final_status',request()->status);
                        }
                        if(request()->status == 'Approved' && isset(request()->l2_validator)){
                            $w->where('status','Approved');
                            $w->where('finalappr_userid',request()->l2_validator);
                        }
                        if(request()->status == 'Pending' && isset(request()->l2_validator)){

                            $w->where('status','Pending');
                            $w->where('finalappr_userid',request()->l2_validator);
                        }

                        if(request()->status == 'Rejected' && isset(request()->l2_validator)){
                            $w->where('status','Rejected');
                            $w->where('finalreject_userid',request()->l2_validator);
                        }
                    })
                    ->get();
                    if($Farmers->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }
                //  $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                 $filename = request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString().'.xlsx';
                //  return Excel::download(new L2RejectExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->onboarding_l2(request());
            }
            // L2PendingExport  ApprovedPlotExport  L2RejectExport
            if(!$data){
                return response()->json([
                    'error'=>true,
                    'message'=>'Unknown error or check your selection'
                ],500);
            }
            return response()->json([
                'success'=>true,
                'message'=>'Export request submitted. Please check download section'
            ]);
        //end of onboarding

        }elseif(request()->modules == 'CropData'){
            if(request()->level == 'All'){
                $crop = FarmerCropdata::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                })
                ->limit(1)->get();
                if($crop->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString().'.xlsx';
            //    return Excel::download(new CropdataExport('All' ,json_encode(request()->all())), $filename);
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\CropdataExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'excel',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->addMinutes(1)->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
                if(!$job){
                    return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                }
                return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section']);
            }


            if(request()->level == 'L1-Validator'){
                $crop = FarmerCropdata::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(request()->status){
                        $w->where('status',request()->status);
                    }
                    if(request()->status != 'Pending' && isset(request()->l1_validator)){
                        $w->where('apprv_reject_user_id',request()->l1_validator);
                    }
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($crop->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                // $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                // return Excel::download(new CropdataExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->cropdata_l1(request());
            }

            if(request()->level == 'L2-Validator'){
                $crop = FarmerCropdata::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(request()->status){
                        $w->where('l2_status',request()->status);
                    }
                    if(request()->status != 'Pending' && isset(request()->l2_validator)){
                        $w->where('l2_apprv_reject_user_id',request()->l2_validator);
                    }
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($crop->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                //  $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                 $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString(). '.xlsx';
                //  return Excel::download(new L2CropdataExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->cropdata_l2(request());
            }
            if(!$data){
                return response()->json([
                    'error'=>true,
                    'message'=>'Unknown error or check your selection'
                ],500);
            }
            return response()->json([
                'success'=>true,
                'message'=>'Export request submitted. Please check download section'
            ]);
        //end of cropdata
        }elseif(request()->modules == 'Polygon'){

            // dd(request()->all());

            if (request()->level == 'All') {
                $pipe = Polygon::whereHas('farmerapproved', function($q) {
                    if (auth()->user()->roles->first()->name == 'Viewer') {
                        $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                        $q->whereIn('state_id', explode(',', $viewerlocation->state));
                    }
                    $q->where('onboarding_form', 1);
                    if (isset(request()->state) && request()->state) {
                        $q->where('state_id', 'like', request()->state);
                    }
                    if (isset(request()->district) && request()->district) {
                        $q->where('district_id', 'like', request()->district);
                    }
                    if (isset(request()->taluka) && request()->taluka) {
                        $q->where('taluka_id', 'like', request()->taluka);
                    }

                    // Ensure you return the builder instance
                    return $q;
                })
                ->when(request()->filter, function($w) {
                    if (isset(request()->start_date) && request()->start_date) {
                        $w->whereDate('created_at', '>=', request()->start_date);
                    }
                    if (isset(request()->end_date) && request()->end_date) {
                        $w->whereDate('created_at', '<=', request()->end_date);
                    }

                    return $w;
                })
                ->get();

                // Check if records are empty
                if ($pipe->isEmpty()) {
                    return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                }

                // Prepare filename based on request parameters
                // $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . Carbon::now()->toDateTimeString();
                $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString();

                // Determine file extension and return the appropriate download response
                if (request()->type_download == 'Geojson') {
                    $filename .= '.geojson';
                    // return Excel::download(new AllPolygonExport('All', json_encode(request()->all())), $filename);
                } else {
                    $filename .= '.xlsx';
                    // return Excel::download(new AllPolygonExport('All', json_encode(request()->all())), $filename);
                }

                // Prepare payload for job storage
                $payload = [
                    'uuid' => \Str::uuid(),
                    'data' => [
                        'command' => '\App\Exports\AllPolygonExport',
                        'parameters' => ['All', request()->all()],
                        'filename' => $filename,
                        'drive' => request()->type_download == 'Geojson' ? 'geojson' : 'excel'
                    ]
                ];

                // Insert job into temp_jobs table
                $job = \DB::table('temp_jobs')->insert([
                    'queue' => request()->type_download == 'Geojson' ? 'geojson' : 'excel',
                    'user_id' => auth()->user()->id,
                    'payload' => json_encode($payload),
                    'available_at' => \Carbon\Carbon::now()->timestamp,
                    'created_at' => \Carbon\Carbon::now()->timestamp,
                    'date' => \Carbon\Carbon::today()->toDateString(),
                    'type' => request()->type_download == 'Geojson' ? 'Geojson' : 'Excel'
                ]);
                Log::info('Job Inserted:', [
                    'queue' => request()->type_download == 'Geojson' ? 'geojson' : 'excel',
                    'user_id' => auth()->user()->id,
                    'payload' => $payload,
                    'available_at' => \Carbon\Carbon::now()->timestamp,
                    'created_at' => \Carbon\Carbon::now()->timestamp,
                    'date' => \Carbon\Carbon::today()->toDateString()
                ]);

                // Check if job insertion was successful
                if (!$job) {
                    return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                }

                // Return success response
                return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section']);
            }


            //start of pipeinstallation
            if(request()->level == 'L1-Validator'){
                $pipe = PipeInstallation::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){

                        if(isset(request()->status)  && request()->status == 'Pending'){
                            $c->where('status','Pending');
                        }
                        if(isset(request()->status)  && request()->status == 'Rejected'){
                            $c->where('status','Rejected');
                            $c->where('trash',0);
                        }
                        if(isset(request()->status)  && request()->status == 'Approved'){
                            $c->where('status','Approved');
                        }
                        return $c;
                    });
                    return $im;
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    if(isset(request()->l1_validator) && request()->l1_validator){
                        $w->where('apprv_reject_user_id', request()->l1_validator);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }


                // $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                // return Excel::download(new PipeInstallationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->pipedata_l1(request());
            }

            if(request()->level == 'L2-Validator'){
                $pipe = Polygon::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                // ->whereHas('pipe_image',function($im){
                //     $im->when('filter',function($c){
                //         if(isset(request()->status)  && request()->status == 'Pending'){
                //             $c->where('status','Approved');
                //             $c->where('l2status','Pending');
                //         }
                //         if(isset(request()->status)  && request()->status == 'Rejected'){
                //             $c->where('l2status','Rejected');
                //             $c->where('l2trash',0);
                //         }
                //         if(isset(request()->status)  && request()->status == 'Approved'){
                //             $c->where('status','Approved');
                //             $c->where('l2status','Approved');
                //         }
                //         return $c;
                //     });
                //     return $im;
                // })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    if(isset(request()->l2_validator) && request()->l2_validator){
                        $w->where('l2_apprv_reject_user_id', request()->l2_validator);
                    }
                    return $w;
                })
                ->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                //  $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString() ;
                //  return Excel::download(new L2PolygonExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->polygondata_l2(request());
            }
            if(!$data){
                return response()->json([
                    'error'=>true,
                    'message'=>'Unknown error or check your selection'
                ],500);
            }
            return response()->json([
                'success'=>true,
                'message'=>'Export request submitted. Please check download section'
            ]);
        }elseif(request()->modules == 'PipeInstallation'){
            if (request()->level == 'All' && request()->type_report == 'Farmer_wise'){

                $pipe = PipeInstallationPipeImg::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }


                    if(request()->type_download == 'Geojson'){
                        //for geojson download
                        $filename = request()->modules.'_'.request()->level.'_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                        // return Excel::download(new AllPipeInstallationExport('All', json_encode(request()->all())), $filename);

                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'   =>'\App\Exports\AllPipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'filename'  =>$filename,
                                'drive'     =>'geojson'
                            ]
                        ];

                        $job=\DB::table('temp_jobs')->insert([
                            'queue'     =>'geojson',
                            'user_id'   => auth()->user()->id,
                            'payload'   =>json_encode($payload),
                            'available_at'=>\Carbon\Carbon::now()->timestamp,
                            'created_at'    =>\Carbon\Carbon::now()->timestamp,
                            'date'      => \Carbon\Carbon::today()->toDateString(),
                            'type'  => 'Geojson'
                        ]);
                        if(!$job){
                            return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                        }
                        return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section']);
                     }



                    // $filename = request()->modules.'_'.request()->level.'_'.request()->type_report.'-'.Carbon::now()->toDateTimeString().'.xlsx';
                    $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . request()->report_type . '_' . Carbon::now()->toDateTimeString() . '.xlsx';
                    
                     //return Excel::download(new AllPipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                    //this will be for excel download
                    $payload=[
                        'uuid'=>\Str::uuid(),
                        'data'=>[
                            'command'   =>'\App\Exports\AllPipeInstallationExport',
                            'parameters'=>['All' ,request()->all()],
                            'filename'  =>$filename,
                            'drive'     =>'excel'
                        ]
                    ];
                    $job=\DB::table('temp_jobs')->insert([
                        'queue'     =>'excel',
                        'user_id'   => auth()->user()->id,
                        'payload'   =>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'    =>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                    ]);


                if(!$job){
                    return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                }
                return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section']);
            }


            if (request()->level == 'All' && request()->type_report == 'Total_Date_Wise'){

                $pipe = PipeInstallationPipeImg::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                    $filename = request()->modules.'_'.request()->level.'_'.request()->type_report.'-'.Carbon::now()->toDateTimeString().'.xlsx';
                    // return Excel::download(new TotalPipeExport('All' ,json_encode(request()->all())), $filename);
                    //this will be for excel download
                    $payload=[
                        'uuid'=>\Str::uuid(),
                        'data'=>[
                            'command'   =>'\App\Exports\TotalPipeExport',
                            'parameters'=>['All' ,request()->all()],
                            'filename'  =>$filename,
                            'drive'     =>'excel'
                        ]
                    ];
                    $job=\DB::table('temp_jobs')->insert([
                        'queue'     =>'excel',
                        'user_id'   => auth()->user()->id,
                        'payload'   =>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'    =>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                    ]);


                if(!$job){
                    return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                }
                return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section']);
            }



            if (request()->level == 'All' && request()->type_report == 'Plot_wise') {
                $pipe = PipeInstallationPipeImg::whereHas('farmerapproved', function($q) {
                    if (auth()->user()->roles->first()->name == 'Viewer') {
                        $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                        $q->whereIn('state_id', explode(',', $viewerlocation->state));
                    }
                    $q->where('onboarding_form', 1);

                    if (isset(request()->state) && request()->state) {
                        $q->where('state_id', 'like', request()->state);
                    }
                    if (isset(request()->district) && request()->district) {
                        $q->where('district_id', 'like', request()->district);
                    }
                    if (isset(request()->taluka) && request()->taluka) {
                        $q->where('taluka_id', 'like', request()->taluka);
                    }
                    if (isset(request()->organization) && request()->organization) {
                        $q->where('organization_id',request()->organization);
                    }
                });

                $pipe->when(request()->has('start_date') && request()->start_date, function($w) {
                    return $w->whereDate('created_at', '>=', request()->start_date);
                })->when(request()->has('end_date') && request()->end_date, function($w) {
                    return $w->whereDate('created_at', '<=', request()->end_date);
                });

                $pipeResult = $pipe->get();

                if ($pipeResult->isEmpty()) {
                    return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                }

                if (request()->type_download == 'Geojson') {
                    // for geojson download
                    $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_geojson_' . Carbon::now()->toDateTimeString() . '.geojson';
                    //return Excel::download(new PlotPipeinstallationExport('All', json_encode(request()->all())), $filename);
                }

                $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' . request()->type_report . '_' . Carbon::now()->toDateTimeString() . '.xlsx';

                //return Excel::download(new PlotPipeinstallationExport('All', json_encode(request()->all())), $filename);

                // Insert job into temp_jobs table
                $payload = [
                    'uuid' => \Str::uuid(),
                    'data' => [
                        'command' => '\App\Exports\PlotPipeinstallationExport',
                        'parameters' => ['All', request()->all()],
                        'filename' => $filename,
                        'drive' => 'excel'
                    ]
                ];

                $job = \DB::table('temp_jobs')->insert([
                    'queue' => 'excel',
                    'user_id' => auth()->user()->id,
                    'payload' => json_encode($payload),
                    'available_at' => \Carbon\Carbon::now()->timestamp,
                    'created_at' => \Carbon\Carbon::now()->timestamp,
                    'date' => \Carbon\Carbon::today()->toDateString()
                ]);

                if (!$job) {
                    return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                }

                return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section']);
            }

            //start of pipeinstallation
            if(request()->level == 'L1-Validator'){
                $pipe = PipeInstallationPipeImg::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$viewerlocation->state));
                        }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){

                        if(isset(request()->status)  && request()->status == 'Pending'){
                            $c->where('status','Pending');
                        }
                        if(isset(request()->status)  && request()->status == 'Rejected'){
                            $c->where('status','Rejected');
                            $c->where('trash',0);
                        }
                        if(isset(request()->status)  && request()->status == 'Approved'){
                            $c->where('status','Approved');
                        }
                        return $c;
                    });
                    return $im;
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    if(isset(request()->l1_validator) && request()->l1_validator){
                        $w->where('apprv_reject_user_id', request()->l1_validator);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }


                // $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                // return Excel::download(new PipeInstallationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->pipedata_l1(request());
            }

            if (request()->level == 'L2-Validator') {
                $pipe = PipeInstallationPipeImg::whereHas('farmerapproved', function($q) {
                    if (auth()->user()->roles->first()->name == 'Viewer') {
                        $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                        $q->whereIn('state_id', explode(',', $viewerlocation->state));
                    }
                    $q->where('onboarding_form', 1);
                    if (request()->has('state') && request()->state) {
                        $q->where('state_id', 'like', request()->state);
                    }
                    if (request()->has('district') && request()->district) {
                        $q->where('district_id', 'like', request()->district);
                    }
                    if (request()->has('taluka') && request()->taluka) {
                        $q->where('taluka_id', 'like', request()->taluka);
                    }
                })
                ->whereHas('pipe_image', function($im) {
                    $im->when(request()->has('status') && request()->status == 'Pending', function($c) {
                        $c->where('status', 'Approved');
                        $c->where('l2status', 'Pending');
                    })
                    ->when(request()->has('status') && request()->status == 'Rejected', function($c) {
                        $c->where('l2status', 'Rejected');
                        $c->where('l2trash', 0);
                    })
                    ->when(request()->has('status') && request()->status == 'Approved', function($c) {
                        $c->where('status', 'Approved');
                        $c->where('l2status', 'Approved');
                    })
                    ->when(request()->has('l2_validator') && request()->l2_validator, function($c) {
                        $c->where('user_id', request()->l2_validator);
                    });
                })
                ->when(request()->has('start_date') && request()->start_date, function($w) {
                    $w->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(request()->has('end_date') && request()->end_date, function($w) {
                    $w->whereDate('created_at', '<=', request()->end_date);
                })
                ->limit(1)
                ->get();

                if ($pipe->isEmpty()) {
                    return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                }

                $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_All_' . Carbon::now()->toDateTimeString() . '.xlsx';
                //return Excel::download(new L2PipeInstallationExport('All', json_encode(request()->all())), $filename);

                $data = $this->pipedata_l2(request());
            }

            if (!$data) {
                return response()->json([
                    'error' => true,
                    'message' => 'Unknown error or check your selection'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Export request submitted. Please check download section'
            ]);

        }elseif(request()->modules == 'Aeration'){
            \Log::info('Request Parameters:', request()->all());

          // dd(request()->all());
            if (request()->level == 'All' && request()->type_report == 'Plot_wise') {
                $aeration = Aeration::whereHas('farmerapproved', function ($q) {
                    if (auth()->user()->roles->first()->name == 'Viewer') {
                        $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                        $q->whereIn('state_id', explode(',', $viewerlocation->state));
                    }
                    $q->where('onboarding_form', 1);
                    if (request()->has('state') && request()->state) {
                        $q->where('state_id', 'like', request()->state);
                    }

                    if (request()->has('district') && request()->district) {
                        $q->where('district_id', 'like', request()->district);
                    }
                    if (request()->has('taluka') && request()->taluka) {
                        $q->where('taluka_id', 'like', request()->taluka);
                    }
                })
                ->when(request()->has('start_date') && request()->start_date, function ($w){
                    $w->whereDate('created_at', '<=', request()->start_date);
                })
                ->when(request()->has('end_date') && request()->end_date, function ($w){
                    $w->whereDate('created_at', '<=', request()->end_date);
                })
                ->when(request()->has('aeration_no') && request()->aeration_no, function ($w){
                    $w->where('aeration_no', request()->aeration_no);
                })
                ->limit(1)->get();
            //dd($aeration);
                if ($aeration->isEmpty()) {
                    return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                }

           $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' .  request()->type_report . '_' . request()->aeration_no . '_' . Carbon::now()->toDateTimeString() . '.xlsx';

             //return Excel::download(new AerationNoExport('All', json_encode(request()->all())), $filename);


                $payload = [
                    'uuid' => \Str::uuid(),
                    'data' => [
                        'command'   => '\App\Exports\AerationNoExport',
                        'parameters' => ['All', request()->all()],
                        'filename'  => $filename,
                        'drive'     => 'excel'
                    ]
                ];

                $job = \DB::table('temp_jobs')->insert([
                    'queue'         => 'excel',
                    'user_id'       => auth()->user()->id,
                    'payload'       => json_encode($payload),
                    'available_at'  => \Carbon\Carbon::now()->timestamp,
                    'created_at'    => \Carbon\Carbon::now()->timestamp,
                    'date'          => \Carbon\Carbon::today()->toDateString()
                ]);

                if (!$job) {
                    return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                }

                return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section']);
            }

            if (request()->level == 'All' && request()->type_report == 'Farmer_wise') {
                $aeration = Aeration::whereHas('farmerapproved', function ($q) {
                    if (auth()->user()->roles->first()->name == 'Viewer') {
                        $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                        $q->whereIn('state_id', explode(',', $viewerlocation->state));
                    }
                    $q->where('onboarding_form', 1);
                    if (isset(request()->state) && request()->state) {
                        $q->where('state_id', 'like', request()->state);
                    }
                    if (isset(request()->district) && request()->district) {
                        $q->where('district_id', 'like', request()->district);
                    }
                    if (isset(request()->taluka) && request()->taluka) {
                        $q->where('taluka_id', 'like', request()->taluka);
                    }
                })
                ->when(isset(request()->start_date) && request()->start_date, function ($w) {
                    $w->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(isset(request()->end_date) && request()->end_date, function ($w) {
                    $w->whereDate('created_at', '<=', request()->end_date);
                })
                ->limit(1)->get();

                if ($aeration->isEmpty()) {
                    return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                }

                $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' .  request()->type_report . '_' . Carbon::now()->toDateTimeString() . '.xlsx';

                //return Excel::download(new AllAerationExport('All', json_encode(request()->all())), $filename);

                $payload = [
                    'uuid' => \Str::uuid(),
                    'data' => [
                        'command'   => '\App\Exports\AllAerationExport',
                        'parameters' => ['All', request()->all()],
                        'filename'  => $filename,
                        'drive'     => 'excel'
                    ]
                ];

                $job = \DB::table('temp_jobs')->insert([
                    'queue'         => 'excel',
                    'user_id'       => auth()->user()->id,
                    'payload'       => json_encode($payload),
                    'available_at'  => \Carbon\Carbon::now()->timestamp,
                    'created_at'    => \Carbon\Carbon::now()->timestamp,
                    'date'          => \Carbon\Carbon::today()->toDateString()
                ]);

                if (!$job) {
                    return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                }

                return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section']);
            }

            if (request()->level == 'All' && request()->type_report == 'Total_Date_Wise') {
                $aeration = Aeration::whereHas('farmerapproved', function ($q) {
                    if (auth()->user()->roles->first()->name == 'Viewer') {
                        $viewerlocation = ViewerLocation::where('user_id', auth()->user()->id)->first();
                        $q->whereIn('state_id', explode(',', $viewerlocation->state));
                    }
                    $q->where('onboarding_form', 1);
                    if (isset(request()->state) && request()->state) {
                        $q->where('state_id', 'like', request()->state);
                    }
                    if (isset(request()->district) && request()->district) {
                        $q->where('district_id', 'like', request()->district);
                    }
                    if (isset(request()->taluka) && request()->taluka) {
                        $q->where('taluka_id', 'like', request()->taluka);
                    }
                })
                ->when(isset(request()->start_date) && request()->start_date, function ($w) {
                    $w->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(isset(request()->end_date) && request()->end_date, function ($w) {
                    $w->whereDate('created_at', '<=', request()->end_date);
                })
                ->limit(1)->get();

                if ($aeration->isEmpty()) {
                    return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
                }

                $filename = request()->organizationName. '_' . request()->modules . '_' . request()->level . '_' .  request()->type_report . '_' . Carbon::now()->toDateTimeString() . '.xlsx';

                //return Excel::download(new TotalAerationExport('All', json_encode(request()->all())), $filename);

                $payload = [
                    'uuid' => \Str::uuid(),
                    'data' => [
                        'command'   => '\App\Exports\TotalAerationExport',
                        'parameters' => ['All', request()->all()],
                        'filename'  => $filename,
                        'drive'     => 'excel'
                    ]
                ];

                $job = \DB::table('temp_jobs')->insert([
                    'queue'         => 'excel',
                    'user_id'       => auth()->user()->id,
                    'payload'       => json_encode($payload),
                    'available_at'  => \Carbon\Carbon::now()->timestamp,
                    'created_at'    => \Carbon\Carbon::now()->timestamp,
                    'date'          => \Carbon\Carbon::today()->toDateString()
                ]);

                if (!$job) {
                    return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                }

                return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section']);
            }





            if(request()->level == 'L1-Validator'){

                $aeration = Aeration::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                        $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$viewerlocation->state));
                    }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(request()->status){
                        $w->where('status',request()->status);
                    }
                    if(isset(request()->l1_validator) && request()->status != 'Pending'){
                        $w->where('apprv_reject_user_id', request()->l1_validator);
                    }
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    if(isset(request()->aeration_no)  && request()->aeration_no){
                        $w->where('aeration_no','=',request()->aeration_no);
                   }
                    return $w;
                })
                ->limit(1)->get();
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                 $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                 //return Excel::download(new AerationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->aeration_l1(request());
            }

            if(request()->level == 'L2-Validator'){
                $aeration = Aeration::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                        $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$viewerlocation->state));
                    }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(request()->status){
                        $w->where('l2_status',request()->status);
                    }
                    if(isset(request()->l2_validator) && request()->status != 'Pending'){
                        $w->where('l2_apprv_reject_user_id', request()->l2_validator);
                    }
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                 $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                 //return Excel::download(new L2AerationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->aeration_l2(request());
            }
            if(!$data){
                return response()->json([
                    'error'=>true,
                    'message'=>'Unknown error or check your selection'
                ],500);
            }
            return response()->json([
                'success'=>true,
                'message'=>'Export request submitted. Please check download section'
            ]);
        }elseif(request()->modules == 'Benefit'){
            if(request()->level == 'All'){
                $benefit = FarmerBenefit::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                        $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$viewerlocation->state));
                    }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($benefit->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                $filename = request()->modules.'_'.request()->level.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                // return Excel::download(new AllBenefitExport('All' ,json_encode(request()->all())), $filename);
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\AllBenefitExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'excel',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
                if(!$job){
                    return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                }
                return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section'],200);
            }
            //start of benefit
            if(request()->level == 'L1-Validator'){
                $benefit = FarmerBenefit::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                        $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$viewerlocation->state));
                    }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(request()->status){
                        $w->where('status',request()->status);
                    }
                    if(isset(request()->l1_validator) && request()->status != 'Pending'){
                        $w->where('apprv_reject_user_id', request()->l1_validator);
                    }
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($benefit->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                // $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                // return Excel::download(new L1BenefitExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->benefit_l1(request());
            }

            if(request()->level == 'L2-Validator'){
                $benefit = FarmerBenefit::whereHas('farmerapproved', function($q){
                    if(auth()->user()->roles->first()->name == 'Viewer'){
                        $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$viewerlocation->state));
                    }//end of viewer
                    $q->where('onboarding_form',1);
                    if(isset(request()->state)  && request()->state){
                        $q->where('state_id','like',request()->state);
                    }
                    if(isset(request()->district)  && request()->district){
                         $q->where('district_id','like',request()->district);
                    }
                    if(isset(request()->taluka)  && request()->taluka){
                         $q->where('taluka_id','like',request()->taluka);
                    }
                    return $q;
                })
                ->when('fliter',function($w){
                    if(request()->status){
                        $w->where('l2_status',request()->status);
                    }
                    if(isset(request()->l2_validator) && request()->status != 'Pending'){
                        $w->where('l2_apprv_reject_user_id', request()->l2_validator);
                    }
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->limit(1)->get();
                if($benefit->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                 $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                // return Excel::download(new L2BenefitExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->benefit_l2(request());
            }
            if(!$data){
                return response()->json([
                    'error'=>true,
                    'message'=>'Unknown error or check your selection'
                ],500);
            }
            return response()->json([
                'success'=>true,
                'message'=>'Export request submitted. Please check download section'
            ]);
        }
             elseif(request()->report2 == 'organization_wise'){
           // dd('in');
            if(request()->modules2 == 'Onboarding'){
              // dd(request()->start_date);
               $farmers = FarmerPlot::whereHas('final_farmers', function ($q) {
                $q->where('onboarding_form', 1);
            })
            ->when(request()->start_date, function($query, $start_date) {
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(request()->end_date, function($query, $end_date) {
                return $query->whereDate('created_at', '<=', $end_date);
            })
            ->when(isset(request()->organization2) && request()->organization2, function ($query){
                $query->whereHas('final_farmers', function ($q){
                    $q->where('organization_id', request()->organization2);
                });
            })
            ->when(isset(request()->seasons) && request()->seasons, function ($query){
                $query->whereHas('final_farmers', function ($q){
                    $q->where('season', request()->seasons);
                });
            })
            ->when(isset(request()->years) && request()->years, function ($query){
                $query->whereHas('final_farmers', function ($q){
                    $q->where('financial_year', request()->years);
                });
            })
            ->latest()
            ->limit(1)
            ->get();
               //dd($farmers);
                if($farmers->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                $filename = request()->report2.'_'.request()->modules2.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                //dd($filename);
                 //return Excel::download(new OnboardingCountExport('All' ,json_encode(request()->all())), $filename);
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\OnboardingCountExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'excel',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
                if(!$job){
                    return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                }
                return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section'],200);
            }

            if (request()->modules2 == 'Polygon') {
             //dd(request()->end_date);
                $farmers = PipeInstallation::whereHas('farmerapproved', function ($q) {
                    $q->where('onboarding_form', 1);
                })
                ->when(request()->start_date, function($query, $start_date) {
                    return $query->whereDate('created_at', '>=', $start_date);
                })
                ->when(request()->end_date, function($query, $end_date) {
                    return $query->whereDate('created_at', '<=', $end_date);
                })
                ->when(isset(request()->organization2) && request()->organization2, function ($query){
                    $query->whereHas('farmerapproved', function ($q){
                        $q->where('organization_id', request()->organization2);
                    });
                })
                ->when(request()->seasons, function($query, $seasons) {
                    return $query->where('season', $seasons);
                })
                ->when(request()->years, function($query, $years) {
                    return $query->where('financial_year', $years);
                })
                ->latest()
                ->limit(1)
                ->count();
           // dd($farmers);
           if ($farmers == 0) {
            return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
        }

                $filename = request()->report2 . '_' . request()->modules2 . '_' . \Carbon\Carbon::now()->toDateTimeString() . '.xlsx';

                //return Excel::download(new PolygonCountExport('All' ,json_encode(request()->all())), $filename);
                $payload = [
                    'uuid' => \Str::uuid(),
                    'data' => [
                        'command' => '\App\Exports\PolygonCountExport',
                        'parameters' => ['All', json_encode(request()->all())],
                        'filename' => $filename,
                        'drive' => 'excel'
                    ]
                ];

                $job = \DB::table('temp_jobs')->insert([
                    'queue' => 'excel',
                    'user_id' => auth()->user()->id,
                    'payload' => json_encode($payload),
                    'available_at' => \Carbon\Carbon::now()->timestamp,
                    'created_at' => \Carbon\Carbon::now()->timestamp,
                    'date' => \Carbon\Carbon::today()->toDateString()
                ]);

                if (!$job) {
                    return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                }

                return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section'], 200);
            }


            if (request()->modules2 == 'PipeInstallation') {
                //dd(request()->end_date);
                   $farmers = PipeInstallationPipeImg::whereHas('farmerapproved', function ($q) {
                       $q->where('onboarding_form', 1);
                   })
                   ->when(request()->start_date, function($query, $start_date) {
                       return $query->whereDate('created_at', '>=', $start_date);
                   })
                   ->when(request()->end_date, function($query, $end_date) {
                       return $query->whereDate('created_at', '<=', $end_date);
                   })
                   ->when(isset(request()->organization2) && request()->organization2, function ($query){
                       $query->whereHas('farmerapproved', function ($q){
                           $q->where('organization_id', request()->organization2);
                       });
                   })
                   ->when(request()->seasons, function($query, $seasons) {
                       return $query->where('season', $seasons);
                   })
                   ->when(request()->years, function($query, $years) {
                       return $query->where('financial_year', $years);
                   })
                   ->latest()
                   ->limit(1)
                   ->count();
              // dd($farmers);
              if ($farmers == 0) {
               return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
           }

                   $filename = request()->report2 . '_' . request()->modules2 . '_' . \Carbon\Carbon::now()->toDateTimeString() . '.xlsx';

                  // return Excel::download(new PipeCountExport('All' ,json_encode(request()->all())), $filename);
                   $payload = [
                       'uuid' => \Str::uuid(),
                       'data' => [
                           'command' => '\App\Exports\PipeCountExport',
                           'parameters' => ['All', json_encode(request()->all())],
                           'filename' => $filename,
                           'drive' => 'excel'
                       ]
                   ];

                   $job = \DB::table('temp_jobs')->insert([
                       'queue' => 'excel',
                       'user_id' => auth()->user()->id,
                       'payload' => json_encode($payload),
                       'available_at' => \Carbon\Carbon::now()->timestamp,
                       'created_at' => \Carbon\Carbon::now()->timestamp,
                       'date' => \Carbon\Carbon::today()->toDateString()
                   ]);

                   if (!$job) {
                       return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                   }

                   return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section'], 200);
               }

               if (request()->modules2 == 'Aeration') {
                   //dd(request()->end_date);
                      $farmers = Aeration::whereHas('final_farmer', function ($q) {
                          $q->where('onboarding_form', 1);
                      })
                      ->when(request()->start_date, function($query, $start_date) {
                          return $query->whereDate('created_at', '>=', $start_date);
                      })
                      ->when(request()->end_date, function($query, $end_date) {
                          return $query->whereDate('created_at', '<=', $end_date);
                      })
                      ->when(isset(request()->organization2) && request()->organization2, function ($query){
                          $query->whereHas('final_farmer', function ($q){
                              $q->where('organization_id', request()->organization2);
                          });
                      })
                      ->when(request()->seasons, function($query, $seasons) {
                          return $query->where('season', $seasons);
                      })
                      ->when(request()->years, function($query, $years) {
                          return $query->where('financial_year', $years);
                      })
                      ->latest()
                      ->limit(1)
                      ->count();
                 // dd($farmers);
                 if ($farmers == 0) {
                  return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
              }

                      $filename = request()->report2 . '_' . request()->modules2 . '_' . \Carbon\Carbon::now()->toDateTimeString() . '.xlsx';
                 // return Excel::download(new AerationCountExport('All' ,json_encode(request()->all())), $filename);
                      $payload = [
                          'uuid' => \Str::uuid(),
                          'data' => [
                              'command' => '\App\Exports\AerationCountExport',
                              'parameters' => ['All', json_encode(request()->all())],
                              'filename' => $filename,
                              'drive' => 'excel'
                          ]
                      ];

                      $job = \DB::table('temp_jobs')->insert([
                          'queue' => 'excel',
                          'user_id' => auth()->user()->id,
                          'payload' => json_encode($payload),
                          'available_at' => \Carbon\Carbon::now()->timestamp,
                          'created_at' => \Carbon\Carbon::now()->timestamp,
                          'date' => \Carbon\Carbon::today()->toDateString()
                      ]);

                      if (!$job) {
                          return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                      }

                      return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section'], 200);
                  }


        }elseif(request()->report2 == 'surveyor_wise'){
             //dd('in');
             if(request()->modules2 == 'Onboarding'){
               //dd(request()->surveyor2);
                $farmers = FarmerPlot::whereHas('final_farmers', function ($q) {
                 $q->where('onboarding_form', 1);
                 $q->where('surveyor_id', request()->surveyor2);
                 $q->where('season', request()->seasons);
                 $q->where('financial_year', request()->years);
             })
             ->when(request()->start_date, function($query, $start_date) {
                 return $query->whereDate('created_at', '>=', $start_date);
             })
             ->when(request()->end_date, function($query, $end_date) {
                 return $query->whereDate('created_at', '<=', $end_date);
             })
             ->latest()
             ->limit(1)
             ->get();
                //dd($farmers);
                 if($farmers->isEmpty()){
                     return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                 }
                 $filename = request()->report2.'_'.request()->modules2.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                // dd($filename);
                  //return Excel::download(new OnboardingSurveyorExport('All' ,json_encode(request()->all())), $filename);
                 $payload=[
                     'uuid'=>\Str::uuid(),
                     'data'=>[
                         'command'   =>'\App\Exports\OnboardingSurveyorExport',
                         'parameters'=>['All' ,request()->all()],
                         'filename'  =>$filename,
                         'drive'     =>'excel'
                     ]
                 ];
                 $job=\DB::table('temp_jobs')->insert([
                     'queue'     =>'excel',
                     'user_id'   => auth()->user()->id,
                     'payload'   =>json_encode($payload),
                     'available_at'=>\Carbon\Carbon::now()->timestamp,
                     'created_at'    =>\Carbon\Carbon::now()->timestamp,
                     'date'      => \Carbon\Carbon::today()->toDateString()
                 ]);
                 if(!$job){
                     return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                 }
                 return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section'],200);
             }

             if (request()->modules2 == 'Polygon') {
              //dd(request()->all());
                 $farmers = PipeInstallation::whereHas('farmerapproved', function ($q) {
                     $q->where('onboarding_form', 1);
                 })
                 ->when(request()->start_date, function($query, $start_date) {
                     return $query->whereDate('created_at', '>=', $start_date);
                 })
                 ->when(request()->end_date, function($query, $end_date) {
                     return $query->whereDate('created_at', '<=', $end_date);
                 })
                 ->when(request()->surveyor2, function($query, $surveyor2) {
                    return $query->where('surveyor_id', $surveyor2);
                })
                 ->when(request()->seasons, function($query, $seasons) {
                     return $query->where('season', $seasons);
                 })
                 ->when(request()->years, function($query, $years) {
                     return $query->where('financial_year', $years);
                 })
                 ->latest()
                 ->limit(1)
                 ->get();
             //dd($farmers);
             if($farmers->isEmpty()){
             return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
         }

                 $filename = request()->report2 . '_' . request()->modules2 . '_' . \Carbon\Carbon::now()->toDateTimeString() . '.xlsx';

                // return Excel::download(new PolygonSurveyorExport('All' ,json_encode(request()->all())), $filename);
                 $payload = [
                     'uuid' => \Str::uuid(),
                     'data' => [
                         'command' => '\App\Exports\PolygonSurveyorExport',
                         'parameters' => ['All', json_encode(request()->all())],
                         'filename' => $filename,
                         'drive' => 'excel'
                     ]
                 ];

                 $job = \DB::table('temp_jobs')->insert([
                     'queue' => 'excel',
                     'user_id' => auth()->user()->id,
                     'payload' => json_encode($payload),
                     'available_at' => \Carbon\Carbon::now()->timestamp,
                     'created_at' => \Carbon\Carbon::now()->timestamp,
                     'date' => \Carbon\Carbon::today()->toDateString()
                 ]);

                 if (!$job) {
                     return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                 }

                 return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section'], 200);
             }


             if (request()->modules2 == 'PipeInstallation') {
                 //dd(request()->seasons);
                    $farmers = PipeInstallationPipeImg::whereHas('farmerapproved', function ($q) {
                        $q->where('onboarding_form', 1);
                    })
                    ->when(request()->start_date, function($query, $start_date) {
                        return $query->whereDate('created_at', '>=', $start_date);
                    })
                    ->when(request()->end_date, function($query, $end_date) {
                        return $query->whereDate('created_at', '<=', $end_date);
                    })
                    ->when(request()->surveyor2, function($query, $surveyor2) {
                        return $query->where('surveyor_id',  $surveyor2);
                    })
                    ->when(request()->seasons, function($query, $seasons) {
                        return $query->where('season', $seasons);
                    })
                    ->when(request()->years, function($query, $years) {
                        return $query->where('financial_year', $years);
                    })
                    ->latest()
                    ->limit(1)
                    ->get();
                //dd($farmers);
                if($farmers->isEmpty()){
                return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
            }

                    $filename = request()->report2 . '_' . request()->modules2 . '_' . \Carbon\Carbon::now()->toDateTimeString() . '.xlsx';

                   // return Excel::download(new PipeSurveyorExport('All' ,json_encode(request()->all())), $filename);
                    $payload = [
                        'uuid' => \Str::uuid(),
                        'data' => [
                            'command' => '\App\Exports\PipeSurveyorExport',
                            'parameters' => ['All', json_encode(request()->all())],
                            'filename' => $filename,
                            'drive' => 'excel'
                        ]
                    ];

                    $job = \DB::table('temp_jobs')->insert([
                        'queue' => 'excel',
                        'user_id' => auth()->user()->id,
                        'payload' => json_encode($payload),
                        'available_at' => \Carbon\Carbon::now()->timestamp,
                        'created_at' => \Carbon\Carbon::now()->timestamp,
                        'date' => \Carbon\Carbon::today()->toDateString()
                    ]);

                    if (!$job) {
                        return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                    }

                    return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section'], 200);
                }



                if (request()->modules2 == 'Aeration') {
                   // dd(request()->all());
                       $farmers = Aeration::whereHas('final_farmer', function ($q) {
                           $q->where('onboarding_form', 1);
                       })
                       ->when(request()->start_date, function($query, $start_date) {
                           return $query->whereDate('created_at', '>=', $start_date);
                       })
                       ->when(request()->end_date, function($query, $end_date) {
                           return $query->whereDate('created_at', '<=', $end_date);
                       })
                       ->when(request()->surveyor2, function($query, $surveyor2) {
                        return $query->where('surveyor_id', $surveyor2);
                    })
                       ->when(request()->seasons, function($query, $seasons) {
                           return $query->where('season', $seasons);
                       })
                       ->when(request()->years, function($query, $years) {
                           return $query->where('financial_year', $years);
                       })
                       ->latest()
                       ->limit(1)
                       ->get();
                   //dd($farmers);
                   if($farmers->isEmpty()){
                   return response()->json(['error' => true, 'message' => 'No Record Found'], 500);
               }

                       $filename = request()->report2 . '_' . request()->modules2 . '_' . \Carbon\Carbon::now()->toDateTimeString() . '.xlsx';
                       //return Excel::download(new AerationSurveyorExport('All' ,json_encode(request()->all())), $filename);

                       $payload = [
                           'uuid' => \Str::uuid(),
                           'data' => [
                               'command' => '\App\Exports\AerationSurveyorExport',
                               'parameters' => ['All', json_encode(request()->all())],
                               'filename' => $filename,
                               'drive' => 'excel'
                           ]
                       ];

                       $job = \DB::table('temp_jobs')->insert([
                           'queue' => 'excel',
                           'user_id' => auth()->user()->id,
                           'payload' => json_encode($payload),
                           'available_at' => \Carbon\Carbon::now()->timestamp,
                           'created_at' => \Carbon\Carbon::now()->timestamp,
                           'date' => \Carbon\Carbon::today()->toDateString()
                       ]);

                       if (!$job) {
                           return response()->json(['error' => true, 'message' => 'Unknown error or check your selection'], 500);
                       }

                       return response()->json(['success' => true, 'message' => 'Export request submitted. Please check download section'], 200);
                   }


         }

        // else{
        //     return response()->json([
        //         'error'=>true,
        //         'message'=>'Unknown error or check your selection'
        //     ],500);
        // }
    }

    /**
     * Excel Download for pipe data l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function benefit_l2($request)
    {
        if($request->level == 'L2-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2BenefitExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2BenefitExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }


    /**
     * Excel Download for pipe data l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function benefit_l1($request)
    {
        if($request->level == 'L1-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L1BenefitExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L1BenefitExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }



    /**
     * Excel Download for pipe data l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function aeration_l2($request)
    {
        if($request->level == 'L2-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2AerationExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2AerationExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Rejected'){
            $filename = request()->modules.'_'.request()->level.'_Rejected_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2AerationExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }


    /**
     * Excel Download for pipe data l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function aeration_l1($request)
    {
        if($request->level == 'L1-Validator' && $request->status == 'Pending'){
            dd('out');
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\AerationExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            return Excel::download(new AerationExport('All' ,json_encode(request()->all())), $filename);
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\AerationExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Rejected'){
            $filename = request()->modules.'_'.request()->level.'_Rejected_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\AerationExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }


    /**
     * Excel Download for pipe data l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function polygondata_l2($request)
    {
        if($request->level == 'L2-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            if(request()->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Pending_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\L2PolygonExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PolygonExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            if(request()->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Approved_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\L2PolygonExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PolygonExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Rejected'){
            $filename = request()->modules.'_'.request()->level.'_Rejected_'.Carbon::now()->toDateTimeString().'.xlsx';
            if(request()->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Rejected_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\L2PolygonExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PolygonExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }
            if(!$job){
                return false;
            }
            return true;
        }
    }


    /**
     * Excel Download for pipe data l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function pipedata_l2($request)
    {
        if($request->level == 'L2-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            if(request()->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Pending_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\L2PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            if(request()->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Approved_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\L2PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Rejected'){
            $filename = request()->modules.'_'.request()->level.'_Rejected_'.Carbon::now()->toDateTimeString().'.xlsx';
            if(request()->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Rejected_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\L2PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }
            if(!$job){
                return false;
            }
            return true;
        }
    }


    /**
     * Excel Download for pipe data l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function pipedata_l1($request)
    {
        if($request->level == 'L1-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            if($request->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Pending_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }

            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            if($request->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Approved_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
            }
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Rejected'){
            $filename = request()->modules.'_'.request()->level.'_Rejected_'.Carbon::now()->toDateTimeString().'.xlsx';
            if($request->type_download == 'Geojson'){
                //for geojson download
                $filename = request()->modules.'_'.request()->level.'_Rejected_geojson_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'  =>$filename,
                        'drive'     =>'geojson'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                    'queue'     =>'geojson',
                    'user_id'   => auth()->user()->id,
                    'payload'   =>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString(),
                    'type'  => 'Geojson'
                ]);
             }else{
                //this will be for excel download
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\PipeInstallationExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
             }
            if(!$job){
                return false;
            }
            return true;
        }
    }

    /**
     * Excel Download for onboarding l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function cropdata_l2($request)
    {
        if($request->level == 'L2-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2CropdataExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2CropdataExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'payload'=>json_encode($payload),
                    'user_id'  => auth()->user()->id,
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }

     /**
     * Excel Download for onboarding l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function cropdata_l1($request)
    {
        if($request->level == 'L1-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\CropdataExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\CropdataExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'payload'=>json_encode($payload),
                    'user_id'  => auth()->user()->id,
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }

    /**
     * Excel Download for onboarding l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function onboarding_l2($request)
    {
        if($request->level == 'L2-Validator' && empty($request->status)){
            $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.geojson';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PendingExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
                $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'payload'=>json_encode($payload),
                        'user_id'  => auth()->user()->id,
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date'      => \Carbon\Carbon::today()->toDateString()
                ]);
                if(!$job){
                    return false;
                }
                return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\ApprovedPlotExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Rejected'){
            $filename = request()->modules.'_'.request()->level.'_Rejected_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2RejectExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L2-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'=>'\App\Exports\L2PendingExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'=>$filename,
                    'drive'=>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'payload'=>json_encode($payload),
                    'user_id'  => auth()->user()->id,
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp,
                    'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }

     /**
     * Excel Download for onboarding l1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function onboarding_l1($request)
    {
        if($request->level == 'L1-Validator' && $request->status == 'Approved'){
            $filename = request()->modules.'_'.request()->level.'_Approved_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'   =>'\App\Exports\L1ApprovedExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'  =>$filename,
                    'drive'     =>'excel'
                ]
            ];

            $job=\DB::table('temp_jobs')->insert([
                'queue'     =>'excel',
                'user_id'   => auth()->user()->id,
                'payload'   =>json_encode($payload),
                'available_at'=>\Carbon\Carbon::now()->timestamp,
                'created_at'    =>\Carbon\Carbon::now()->timestamp,
                'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Rejected'){
            $filename = request()->modules.'_'.request()->level.'_Rejected_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'   =>'\App\Exports\L1RejectedExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'  =>$filename,
                    'drive'     =>'excel'
                ]
            ];
            $job=\DB::table('temp_jobs')->insert([
                'queue'     =>'excel',
                'user_id'   => auth()->user()->id,
                'payload'   =>json_encode($payload),
                'available_at'=>\Carbon\Carbon::now()->timestamp,
                'created_at'    =>\Carbon\Carbon::now()->timestamp,
                'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }elseif($request->level == 'L1-Validator' && $request->status == 'Pending'){
            $filename = request()->modules.'_'.request()->level.'_Pending_'.Carbon::now()->toDateTimeString().'.xlsx';
            $payload=[
                'uuid'=>\Str::uuid(),
                'data'=>[
                    'command'   =>'\App\Exports\L1PendingExport',
                    'parameters'=>['All' ,request()->all()],
                    'filename'  =>$filename,
                    'drive'     =>'excel'
                ]
            ];

            $job=\DB::table('temp_jobs')->insert([
                'queue'     =>'excel',
                'user_id'   => auth()->user()->id,
                'payload'   =>json_encode($payload),
                'available_at'=>\Carbon\Carbon::now()->timestamp,
                'created_at'    =>\Carbon\Carbon::now()->timestamp,
                'date'      => \Carbon\Carbon::today()->toDateString()
            ]);
            if(!$job){
                return false;
            }
            return true;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
