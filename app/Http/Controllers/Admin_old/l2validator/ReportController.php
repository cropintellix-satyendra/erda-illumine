<?php

namespace App\Http\Controllers\Admin\l2validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\VendorLocation;
use App\Models\Farmer;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\L2PendingExport;
use App\Exports\ApprovedPlotExport;
use App\Exports\L2RejectExport;
use App\Exports\L2CropdataExport;
use App\Exports\L2PipeInstallationExport;
use App\Exports\L2AerationExport;
use App\Exports\L2BenefitExport;
use Artisan;
use App\Models\FarmerPlot;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallation;
use App\Models\Aeration;
use App\Models\User;
use App\Models\FarmerBenefit;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = 'Report';
        $action = 'table_farmer';
        $page_description = 'Report';

        $states = DB::table('states')->where('status',1)->get();
        $districts = DB::table('districts')->where('status',1)->get();
        $talukas = DB::table('talukas')->where('status',1)->get();
        $panchayats = DB::table('panchayats')->get();
        $villages = DB::table('villages')->get();
        $onboarding_executive = User::whereHas('roles', function($q){
            $q->where('name', 'AppUser');//fetch user from users table hasrole User
        }
        )->select('id','name')->orderBy('created_at','desc')->get();
        return view('report',compact('page_title','action','page_title','page_description','states','districts','talukas','panchayats','villages','onboarding_executive'));
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        $job="";
        //start of onboarding
        if(request()->modules == 'Onboarding'){
            $filename = request()->modules.'_'.'L2'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';      
                if(request()->status == 'Pending'){  
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                    $Farmers = FarmerPlot::where('status','Approved')->where('final_status','Pending')->whereHas('farmer', function($q){
                        if(auth()->user()->hasRole('L-2-Validator')){
                            $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$VendorLocation->state));
                            if(!empty($VendorLocation->district)){
                            $q->whereIn('district_id',explode(',',$VendorLocation->district));
                            }
                            return $q;
                        } 
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
                    if($Farmers->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                    // json_encode(request()->all())             
                //  return Excel::download(new L2PendingExport('All' ,json_encode(request()->all())), $filename);
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
                            'date' => \Carbon\Carbon::today()->toDateString()
                    ]);

                }//end of pending


                //start of approved
                if(request()->status == 'Approved'){
                    $Farmers = FarmerPlot::where('status','Approved')->where('final_status','Approved')->where('finalappr_userid',auth()->user()->id)->whereHas('farmer', function($q){
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
                    if($Farmers->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                         // return Excel::download(new ApprovedPlotExport('All' ,json_encode(request()->all())), $filename);
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
                                'date' => \Carbon\Carbon::today()->toDateString()
                        ]);


                }//end of approved

                if(request()->status == 'Rejected'){
                    $Farmers = FarmerPlot::where('status','Rejected')->where('final_status','Rejected')->where('finalreject_userid',auth()->user()->id)->whereHas('farmer', function($q){
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
                    if($Farmers->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }
                        // return Excel::download(new L2RejectExport('All' ,json_encode(request()->all())), $filename);
                   
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
                                'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
                }//end of approved
                if(!$job){
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
        //end of Onboarding
        //start of cropdata
        if(request()->modules == 'CropData'){
            $filename = request()->modules.'_'.'L2'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            

            if(request()->status == 'Pending'){
                $crop = FarmerCropdata::where('status','Approved')->where('l2_status','Pending')->whereHas('farmerapproved', function($q){
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

                    // return Excel::download(new L2CropdataExport('All' ,json_encode(request()->all())), $filename);
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
                            'date' => \Carbon\Carbon::today()->toDateString()
                    ]);
            }//end of Pending


            if(request()->status == 'Approved'){
                $crop = FarmerCropdata::where('status','Approved')->where('l2_status','Approved')->where('l2_apprv_reject_user_id',auth()->user()->id)->whereHas('farmerapproved', function($q){
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
                    // return Excel::download(new L2CropdataExport('All' ,json_encode(request()->all())), $filename);
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
                                'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
            }//end of Approved 


            if(!$job){
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
        //end of crop data

        if(request()->modules == 'Polygon'){
            $filename = request()->modules.'_'.'L2'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            

            if(request()->status == 'Pending'){
                $pipe = PipeInstallation::whereHas('farmerapproved', function($q){
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
                    ->whereHas('pipe_image',function($im){
                        $im->when('filter',function($c){                                     
                                $c->where('status','Approved');
                                $c->where('l2status','Pending');     
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
                    })
                    ->limit(1)->get();                  
                    if($pipe->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }
                    
                       if(request()->type_download == 'Geojson'){                  
                            //for geojson download
                            $filename = request()->modules.'_'.'L2-geojson'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.geojson';
                            $payload=[
                                'uuid'=>\Str::uuid(),
                                'data'=>[
                                    'command'=>'\App\Exports\L2PipeInstallationExport',
                                    'parameters'=>['All' ,request()->all()],
                                    'user_id'  => auth()->user()->id,
                                    'filename'=>$filename,
                                    'drive'=>'geojson'
                                ]
                            ];
                            $job=\DB::table('temp_jobs')->insert([
                                'queue'=>'geojson',
                                'user_id'  => auth()->user()->id,
                                'payload'=>json_encode($payload),
                                'available_at'=>\Carbon\Carbon::now()->timestamp,
                                'created_at'=>\Carbon\Carbon::now()->timestamp,
                                'date' => \Carbon\Carbon::today()->toDateString(),
                                'type'  => 'Geojson'
                            ]);
                            if(!$job){
                                return response()->json([
                                    'error'=>true,
                                    'message'=>'Unknown error or check your selection'
                                ],500);
                            }
                            return response()->json([
                                'success'=>true,
                                'message'=>'Export request Submitted. Please check download section'
                            ]); 
                         }
                     
                     

                    // return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                 
                        //this will be for excel download
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
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
                            'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
                        
                        
                   
                    
            }//end of pending  


            if(request()->status == 'Approved'){
                $pipe = PipeInstallation::whereHas('farmerapproved', function($q){
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
                    ->whereHas('pipe_image',function($im){
                        $im->when('filter',function($c){                                     
                                $c->where('status','Approved');
                                $c->where('l2status','Approved');        
                            // if(isset($request->status)  && $request->status == 'Rejected'){                                            
                            //     $c->where('l2status','Rejected');
                            //     $c->where('l2trash',0);
                            // } 
                            // if(isset($request->status)  && $request->status == 'Approved'){   
                            //     $c->where('status','Approved');                                        
                            //     $c->where('l2status','Approved');
                            // } 
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
                        $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                    })
                    ->limit(1)->get();                  
                    if($pipe->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                    // return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                    if(request()->type_download == 'Geojson'){                  
                        //for geojson download
                        $filename = request()->modules.'_'.'L2-geojson'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.geojson';
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
                                'filename'=>$filename,
                                'drive'=>'geojson'
                            ]
                        ];
                        $job=\DB::table('temp_jobs')->insert([
                            'queue'=>'geojson',
                            'user_id'  => auth()->user()->id,
                            'payload'=>json_encode($payload),
                            'available_at'=>\Carbon\Carbon::now()->timestamp,
                            'created_at'=>\Carbon\Carbon::now()->timestamp,
                            'date' => \Carbon\Carbon::today()->toDateString(),
                            'type'  => 'Geojson'
                        ]);
                     }else{
                        //this will be for excel download
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
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
                                'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
                     }
                    
            }//end of Approved  


            if(request()->status == 'Rejected'){
                $pipe = PipeInstallation::whereHas('farmerapproved', function($q){
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
                    ->whereHas('pipe_image',function($im){
                        $im->when('filter',function($c){        
                                $c->where('l2status','Rejected');        
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
                        $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                    })
                    ->limit(1)->get();                  
                    if($pipe->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                    // return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                    if(request()->type_download == 'Geojson'){                  
                        //for geojson download
                        $filename = request()->modules.'_'.'L2-geojson'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.geojson';
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
                                'filename'=>$filename,
                                'drive'=>'geojson'
                            ]
                        ];
                        $job=\DB::table('temp_jobs')->insert([
                            'queue'=>'geojson',
                            'user_id'  => auth()->user()->id,
                            'payload'=>json_encode($payload),
                            'available_at'=>\Carbon\Carbon::now()->timestamp,
                            'created_at'=>\Carbon\Carbon::now()->timestamp,
                            'date' => \Carbon\Carbon::today()->toDateString(),
                            'type'  => 'Geojson'
                        ]);
                     }else{
                        //this will be for excel download
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
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
                                'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
                     }
                    

            }//end of Rejected  
            if(!$job){
                return response()->json([
                    'error'=>true,
                    'message'=>'Unknown error or check your selection'
                ],500);
            }
            return response()->json([
                'success'=>true,
                'message'=>'Export request Submitted. Please check download section'
            ]);  
        }
        //end of polygon  


        //start of pipe data
        if(request()->modules == 'PipeInstallation'){
            $filename = request()->modules.'_'.'L2'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            

            if(request()->status == 'Pending'){
                $pipe = PipeInstallation::whereHas('farmerapproved', function($q){
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
                    ->whereHas('pipe_image',function($im){
                        $im->when('filter',function($c){                                     
                                $c->where('status','Approved');
                                $c->where('l2status','Pending');     
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
                    })
                    ->limit(1)->get();                  
                    if($pipe->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }
                    
                       if(request()->type_download == 'Geojson'){                  
                            //for geojson download
                            $filename = request()->modules.'_'.'L2-geojson'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.geojson';
                            $payload=[
                                'uuid'=>\Str::uuid(),
                                'data'=>[
                                    'command'=>'\App\Exports\L2PipeInstallationExport',
                                    'parameters'=>['All' ,request()->all()],
                                    'user_id'  => auth()->user()->id,
                                    'filename'=>$filename,
                                    'drive'=>'geojson'
                                ]
                            ];
                            $job=\DB::table('temp_jobs')->insert([
                                'queue'=>'geojson',
                                'user_id'  => auth()->user()->id,
                                'payload'=>json_encode($payload),
                                'available_at'=>\Carbon\Carbon::now()->timestamp,
                                'created_at'=>\Carbon\Carbon::now()->timestamp,
                                'date' => \Carbon\Carbon::today()->toDateString(),
                                'type'  => 'Geojson'
                            ]);
                            if(!$job){
                                return response()->json([
                                    'error'=>true,
                                    'message'=>'Unknown error or check your selection'
                                ],500);
                            }
                            return response()->json([
                                'success'=>true,
                                'message'=>'Export request Submitted. Please check download section'
                            ]); 
                         }
                     
                     

                    // return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                 
                        //this will be for excel download
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
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
                            'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
                        
                        
                   
                    
            }//end of pending  


            if(request()->status == 'Approved'){
                $pipe = PipeInstallation::whereHas('farmerapproved', function($q){
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
                    ->whereHas('pipe_image',function($im){
                        $im->when('filter',function($c){                                     
                                $c->where('status','Approved');
                                $c->where('l2status','Approved');        
                            // if(isset($request->status)  && $request->status == 'Rejected'){                                            
                            //     $c->where('l2status','Rejected');
                            //     $c->where('l2trash',0);
                            // } 
                            // if(isset($request->status)  && $request->status == 'Approved'){   
                            //     $c->where('status','Approved');                                        
                            //     $c->where('l2status','Approved');
                            // } 
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
                        $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                    })
                    ->limit(1)->get();                  
                    if($pipe->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                    // return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                    if(request()->type_download == 'Geojson'){                  
                        //for geojson download
                        $filename = request()->modules.'_'.'L2-geojson'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.geojson';
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
                                'filename'=>$filename,
                                'drive'=>'geojson'
                            ]
                        ];
                        $job=\DB::table('temp_jobs')->insert([
                            'queue'=>'geojson',
                            'user_id'  => auth()->user()->id,
                            'payload'=>json_encode($payload),
                            'available_at'=>\Carbon\Carbon::now()->timestamp,
                            'created_at'=>\Carbon\Carbon::now()->timestamp,
                            'date' => \Carbon\Carbon::today()->toDateString(),
                            'type'  => 'Geojson'
                        ]);
                     }else{
                        //this will be for excel download
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
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
                                'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
                     }
                    
            }//end of Approved  


            if(request()->status == 'Rejected'){
                $pipe = PipeInstallation::whereHas('farmerapproved', function($q){
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
                    ->whereHas('pipe_image',function($im){
                        $im->when('filter',function($c){        
                                $c->where('l2status','Rejected');        
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
                        $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                    })
                    ->limit(1)->get();                  
                    if($pipe->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                    // return Excel::download(new L2PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                    if(request()->type_download == 'Geojson'){                  
                        //for geojson download
                        $filename = request()->modules.'_'.'L2-geojson'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.geojson';
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
                                'filename'=>$filename,
                                'drive'=>'geojson'
                            ]
                        ];
                        $job=\DB::table('temp_jobs')->insert([
                            'queue'=>'geojson',
                            'user_id'  => auth()->user()->id,
                            'payload'=>json_encode($payload),
                            'available_at'=>\Carbon\Carbon::now()->timestamp,
                            'created_at'=>\Carbon\Carbon::now()->timestamp,
                            'date' => \Carbon\Carbon::today()->toDateString(),
                            'type'  => 'Geojson'
                        ]);
                     }else{
                        //this will be for excel download
                        $payload=[
                            'uuid'=>\Str::uuid(),
                            'data'=>[
                                'command'=>'\App\Exports\L2PipeInstallationExport',
                                'parameters'=>['All' ,request()->all()],
                                'user_id'  => auth()->user()->id,
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
                                'date' => \Carbon\Carbon::today()->toDateString()
                        ]);
                     }
                    

            }//end of Rejected  
            if(!$job){
                return response()->json([
                    'error'=>true,
                    'message'=>'Unknown error or check your selection'
                ],500);
            }
            return response()->json([
                'success'=>true,
                'message'=>'Export request Submitted. Please check download section'
            ]);  
        }
        //end of pipe installation        


        if(request()->modules == 'Aeration'){
            $filename = request()->modules.'_'.'L2'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            

            if(request()->status == 'Pending'){
                $aeration = Aeration::where('status','Approved')->where('l2_status','Pending')->whereHas('farmerapproved', function($q){
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
                    if($aeration->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }
                    // return Excel::download(new L2AerationExport('All' ,json_encode(request()->all())), $filename);
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
                        'date' => \Carbon\Carbon::today()->toDateString()
                ]);
            }//end of pending 


            if(request()->status == 'Approved'){

                $aeration = Aeration::where('status','Approved')->where('l2_status','Approved')->whereHas('farmerapproved', function($q){
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
                        $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                    })
                    ->limit(1)->get();                  
                    if($aeration->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                    // return Excel::download(new L2AerationExport('All' ,json_encode(request()->all())), $filename);
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
                        'date' => \Carbon\Carbon::today()->toDateString()
                ]);
            }//end of Approved 


            if(request()->status == 'Rejected'){
                $aeration = Aeration::where('l2_status','Rejected')->whereHas('farmerapproved', function($q){
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
                        $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                    })
                    ->limit(1)->get();                  
                    if($aeration->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }

                    // return Excel::download(new L2AerationExport('All' ,json_encode(request()->all())), $filename);
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
                            'date' => \Carbon\Carbon::today()->toDateString()
                    ]);
            }//end of rejected 
            if(!$job){
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
        //end of aeration
        
        
        if(request()->modules == 'Benefit'){
            $filename = request()->modules.'_'.'L2'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            

            if(request()->status == 'Pending'){
                $benefit = FarmerBenefit::where('status','Approved')->where('l2_status','Pending')->whereHas('farmerapproved', function($q) {
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
                    if($benefit->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }
                    // return Excel::download(new L2BenefitExport('All' ,json_encode(request()->all())), $filename);
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
                            'created_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
            }//end of pending 


            if(request()->status == 'Approved'){
                $benefit = FarmerBenefit::where('status','Approved')->where('l2_status','Approved')->whereHas('farmerapproved', function($q) {
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
                        $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                    })
                    ->limit(1)->get();                  
                    if($benefit->isEmpty()){
                        return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                    }
                // return Excel::download(new L2BenefitExport('All' ,json_encode(request()->all())), $filename);
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
                        'created_at'=>\Carbon\Carbon::now()->timestamp
                ]);
        }//end of Approved 
            if(!$job){
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
        return response()->json([
            'error'=>true,
            'message'=>'Unknown error or check your selection'
        ],500);
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
