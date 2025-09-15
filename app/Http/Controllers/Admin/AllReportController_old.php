<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
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
use App\Exports\AllCropDataExport;
use App\Exports\AllPipeInstallationExport;
use App\Exports\AllAerationExport;
use App\Exports\AllBenefitExport;
use App\Exports\AllPolygonExport;
use App\Models\FarmerPlot;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallation;
use App\Models\Aeration;
use App\Models\FarmerBenefit;
use App\Models\PipeInstallationPipeImg;
use App\Models\Polygon;
use App\Models\ViewerLocation;
use DB;
use Exception;
use Illuminate\Support\Facades\Log;

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
        $organizations = DB::table('companies')->where('status',1)->get();
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

        set_time_limit(-1);
        ini_set('memory_limit', '640000M');
        $job="";
        //start of onboarding
        if(request()->modules == 'Onboarding'){

            if (request()->level == 'All') {
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

                    $filename = request()->modules . '_' . request()->level . '_' . Carbon::now()->toDateTimeString() . '.xlsx';
                    Log::info('Filename: ' . $filename);

                    return Excel::download(new AllOnboardingExport('All', json_encode(request()->all())), $filename);

                    $payload = [
                        'uuid' => \Str::uuid(),
                        'data' => [
                            'command' => '\App\Exports\AllOnboardingExport',
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

            //end of level all



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
                // dd(request()    );
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
                        // dd($w);
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
                 $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                 return Excel::download(new L2RejectExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->onboarding_l2(request());
            }
            // L2PendingExport  ApprovedPlotExport  L2RejectExport
            // if(!$data){
            //     return response()->json([
            //         'error'=>true,
            //         'message'=>'Unknown error or check your selection'
            //     ],500);
            // }
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
                ->get();
                if($crop->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                $filename = request()->modules.'_'.request()->level.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                return Excel::download(new CropdataExport('All' ,json_encode(request()->all())), $filename);
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
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
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
                ->get();
                if($crop->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new CropdataExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
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
                ->get();
                if($crop->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new L2CropdataExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
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
            if(request()->level == 'All'){
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
                    // if(request()->type_download == 'Geojson'){
                    //     //for geojson download
                    //     $filename = request()->modules.'_'.request()->level.'_geojson_'.Carbon::now()->toDateTimeString().'.geojson';


                    //     $payload=[
                    //         'uuid'=>\Str::uuid(),
                    //         'data'=>[
                    //             'command'   =>'\App\Exports\AllPipeInstallationExport',
                    //             'parameters'=>['All' ,request()->all()],
                    //             'filename'  =>$filename,
                    //             'drive'     =>'geojson'
                    //         ]
                    //     ];

                    //     $job=\DB::table('temp_jobs')->insert([
                    //         'queue'     =>'geojson',
                    //         'user_id'   => auth()->user()->id,
                    //         'payload'   =>json_encode($payload),
                    //         'available_at'=>\Carbon\Carbon::now()->timestamp,
                    //         'created_at'    =>\Carbon\Carbon::now()->timestamp,
                    //         'date'      => \Carbon\Carbon::today()->toDateString(),
                    //         'type'  => 'Geojson'
                    //     ]);
                    //     if(!$job){
                    //         return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                    //     }
                    //     return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section']);
                    //  }



                    $filename = request()->modules.'_'.request()->level.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                    return Excel::download(new AllPolygonExport('All' ,json_encode(request()->all())), $filename);
                    dd('File Send ho gayi');
                    //this will be for excel download
                    $payload=[
                        'uuid'=>\Str::uuid(),
                        'data'=>[
                            'command'   =>'\App\Exports\AllPolygonExport',
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
                        if(isset($request->status)  && request()->status == 'Rejected'){
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
               ->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }


                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new PipeInstallationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->pipedata_l1(request());
            }

            if(request()->level == 'L2-Validator'){
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
                        if(isset($request->status)  && $request->status == 'Pending'){
                            $c->where('status','Approved');
                            $c->where('l2status','Pending');
                        }
                        if(isset($request->status)  && $request->status == 'Rejected'){
                            $c->where('l2status','Rejected');
                            $c->where('l2trash',0);
                        }
                        if(isset($request->status)  && $request->status == 'Approved'){
                            $c->where('status','Approved');
                            $c->where('l2status','Approved');
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
                    if(isset(request()->l2_validator) && request()->l2_validator){
                        $w->where('l2_apprv_reject_user_id', request()->l2_validator);
                    }
                    return $w;
                })
               ->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->polygondata_l2(request());
            }
            // if(!$data){
            //     return response()->json([
            //         'error'=>true,
            //         'message'=>'Unknown error or check your selection'
            //     ],500);
            // }
            return response()->json([
                'success'=>true,
                'message'=>'Export request submitted. Please check download section'
            ]);
        }elseif(request()->modules == 'PipeInstallation'){
            if(request()->level == 'All'){

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


                //Enabled this if ypu want to genrate the GEO JSON else ignore


                // if(request()->type_download == 'Geojson'){
                //     //for geojson download
                //     $filename = request()->modules.'_'.request()->level.'_geojson_'.Carbon::now()->toDateTimeString().'.geojson';


                //     $payload=[
                //         'uuid'=>\Str::uuid(),
                //         'data'=>[
                //             'command'   =>'\App\Exports\AllPipeInstallationExport',
                //             'parameters'=>['All' ,request()->all()],
                //             'filename'  =>$filename,
                //             'drive'     =>'geojson'
                //         ]
                //     ];

                //     $job=\DB::table('temp_jobs')->insert([
                //         'queue'     =>'geojson',
                //         'user_id'   => auth()->user()->id,
                //         'payload'   =>json_encode($payload),
                //         'available_at'=>\Carbon\Carbon::now()->timestamp,
                //         'created_at'    =>\Carbon\Carbon::now()->timestamp,
                //         'date'      => \Carbon\Carbon::today()->toDateString(),
                //         'type'  => 'Geojson'
                //     ]);
                //     if(!$job){
                //         return response()->json(['error'=>true,'message'=>'Unknown error or check your selection'],500);
                //     }
                //     return response()->json(['success'=>true,'message'=>'Export request submitted. Please check download section']);
                //  }


                    $filename = request()->modules.'_'.request()->level.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                    return Excel::download(new AllPipeInstallationExport('All' ,json_encode(request()->all())), $filename);
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
                        if(isset($request->status)  && request()->status == 'Rejected'){
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
                ->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }


                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new PipeInstallationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->pipedata_l1(request());
            }

            if(request()->level == 'L2-Validator'){
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
                        if(isset($request->status)  && $request->status == 'Pending'){
                            $c->where('status','Approved');
                            $c->where('l2status','Pending');
                        }
                        if(isset($request->status)  && $request->status == 'Rejected'){
                            $c->where('l2status','Rejected');
                            $c->where('l2trash',0);
                        }
                        if(isset($request->status)  && $request->status == 'Approved'){
                            $c->where('status','Approved');
                            $c->where('l2status','Approved');
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
                    if(isset(request()->l2_validator) && request()->l2_validator){
                        $w->where('l2_apprv_reject_user_id', request()->l2_validator);
                    }
                    return $w;
                })
                ->get();
                if($pipe->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
                $data = $this->pipedata_l2(request());
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
        }elseif(request()->modules == 'Aeration'){
            if(request()->level == 'All'){
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
                    if(isset(request()->start_date) && request()->start_date){
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    return $w;
                })
                ->get();
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                $filename = request()->modules.'_'.request()->level.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                // return Excel::download(new AllAerationExport('All' ,json_encode(request()->all())), $filename);
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'   =>'\App\Exports\AllAerationExport',
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
            //start of aeration
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
                    return $w;
                })
                ->get();
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new AerationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
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
                ->get();
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
                return Excel::download(new L2AerationExport('All' ,json_encode(request()->all())), $filename);  //just for testing while development you have to change file name accordingly
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
                ->get();
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
                ->get();
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
                ->get();
                if($benefit->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }
                // $filename = request()->modules.'_'.request()->level.'_All_'.Carbon::now()->toDateTimeString().'.xlsx';//just for testing while development
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
