<?php

namespace App\Http\Controllers\Admin\l1validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\VendorLocation;
use App\Models\Farmer;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\L1PendingExport;
use App\Exports\L1ApprovedExport;
use App\Exports\L1RejectedExport;
use App\Exports\CropdataExport;
use App\Exports\PipeInstallationExport;
use App\Exports\AerationExport;
use App\Exports\L1BenefitExport;
use Artisan;
use App\Models\FarmerPlot;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallation;
use App\Models\Aeration;
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

        $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
        $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
        $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
        $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
        $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
        $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->when(request(),function($q){
              if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka') && !request()->has('panchayats') && !request()->has('village')){
                  $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                  $q->whereIn('state_id',explode(',',$VendorLocation->state));
                  if(!empty($VendorLocation->district)){
                     $q->whereIn('district_id',explode(',',$VendorLocation->district));
                  }
                  if(!empty($VendorLocation->taluka)){
                     $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                  }
                  return $q;
              }
            })->get();
        return view('l1validator.report',compact('page_title','action','page_title','page_description','states','districts','talukas','panchayats','villages','onboarding_executive'));
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
        // Artisan::call('download:excel');
        $job="";
        //start of onboarding
        if(request()->modules == 'Onboarding'){
            $filename = request()->modules.'_'.'L1'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
                if(request()->status == 'Pending'){ 
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                    $Farmers = FarmerPlot::with('farmer')->where('status','Pending')->whereHas('farmer',function($q){
                        if(auth()->user()->hasRole('L-1-Validator')){
                            $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$VendorLocation->state));
                            if(!empty($VendorLocation->district)){
                              $q->whereIn('district_id',explode(',',$VendorLocation->district));
                            }
                            if(!empty($VendorLocation->taluka)){
                              $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                            }
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
                    $payload=[
                        'uuid'=>\Str::uuid(),
                        'data'=>[
                            'command'=>'\App\Exports\L1PendingExport',
                            'parameters'=>['All' ,request()->all()],
                            'filename'=>$filename,
                            'drive'=>'excel'
                        ]
                    ];  
                    // json_encode(request()->all())             
                 // return Excel::download(new L1PendingExport('All' ,json_encode(request()->all())), $filename);
                    $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'user_id'  => auth()->user()->id,
                        'payload'=>json_encode($payload),
                        'available_at'=>\Carbon\Carbon::now()->timestamp,
                        'created_at'=>\Carbon\Carbon::now()->timestamp,
                        'date' => \Carbon\Carbon::today()->toDateString()
                    ]);
                }//end of pending




                //start of approved
                if(request()->status == 'Approved'){
                    //checking if data is available or not, show user get to know about record curent status 
                    //if any changes also update  here
                    $Farmers = FarmerPlot::with('farmer')->where('status','Approved')->where('aprv_recj_userid',auth()->user()->id)->whereHas('farmer',function($q){
                        if(auth()->user()->hasRole('L-1-Validator')){
                            $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$VendorLocation->state));
                            if(!empty($VendorLocation->district)){
                              $q->whereIn('district_id',explode(',',$VendorLocation->district));
                            }
                            if(!empty($VendorLocation->taluka)){
                              $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                            }
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
                    $payload=[
                        'uuid'=>\Str::uuid(),
                        'data'=>[
                            'command'=>'\App\Exports\L1ApprovedExport',
                            'parameters'=>['All' ,request()->all()],
                            'filename'=>$filename,
                            'drive'=>'excel'
                        ]
                    ];
                        // return Excel::download(new L1ApprovedExport('All' ,json_encode(request()->all())), $filename);

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
                    //checking if data is available or not, show user get to know about record curent status 
                    //if any changes also update  here
                    $Farmers = FarmerPlot::with('farmer')->where('status','Rejected')->where('aprv_recj_userid',auth()->user()->id)->whereHas('farmer',function($q){
                        if(auth()->user()->hasRole('L-1-Validator')){
                            $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                            $q->whereIn('state_id',explode(',',$VendorLocation->state));
                            if(!empty($VendorLocation->district)){
                              $q->whereIn('district_id',explode(',',$VendorLocation->district));
                            }
                            if(!empty($VendorLocation->taluka)){
                              $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                            }
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

                        // return Excel::download(new L1RejectedExport('All' ,json_encode(request()->all())), $filename);
                    $payload=[
                        'uuid'=>\Str::uuid(),
                        'data'=>[
                            'command'=>'\App\Exports\L1RejectedExport',
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
            $filename = request()->modules.'_'.'L1'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            


            if(request()->status == 'Pending'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $crop = FarmerCropdata::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->when('fliter',function($w){
                    $w->where('status','Pending');
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

                    // return Excel::download(new CropdataExport('All' ,json_encode(request()->all())), $filename);
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
                        'date' => \Carbon\Carbon::today()->toDateString()
                ]);
            }//end of Pending





            if(request()->status == 'Approved'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $crop = FarmerCropdata::where('apprv_reject_user_id',auth()->user()->id)->whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->when('fliter',function($w){
                    $w->where('status','Approved');
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

                // return Excel::download(new CropdataExport('All' ,json_encode(request()->all())), $filename);
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




        //start of pipe data
        if(request()->modules == 'PipeInstallation'){
            $filename = request()->modules.'_'.'L1'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            


            if(request()->status == 'Pending'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $pipedata = PipeInstallation::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){                      
                        $c->where('status','Pending');
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
                if($pipedata->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }


                    // return Excel::download(new PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                $payload=[
                     'uuid'=>\Str::uuid(),
                     'data'=>[
                         'command'=>'\App\Exports\PipeInstallationExport',
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
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $pipedata = PipeInstallation::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){                      
                        $c->where('status','Approved');
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
                    $w->where('apprv_reject_user_id', auth()->user()->id);
                    return $w;
                })
                ->limit(1)->get();         
                if($pipedata->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                }

                    // return Excel::download(new PipeInstallationExport('All' ,json_encode(request()->all())), $filename);
                $payload=[
                     'uuid'=>\Str::uuid(),
                     'data'=>[
                         'command'=>'\App\Exports\PipeInstallationExport',
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
            }//end of Approved 


            if(request()->status == 'Rejected'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $pipedata = PipeInstallation::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){                      
                        $c->where('status','Rejected');
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
                    $w->where('apprv_reject_user_id', auth()->user()->id);
                    return $w;
                })
                ->limit(1)->get();         
                if($pipedata->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                } 
                   // return Excel::download(new PipeInstallationExport('All' ,json_encode(request()->all())), $filename);


                $payload=[
                     'uuid'=>\Str::uuid(),
                     'data'=>[
                         'command'=>'\App\Exports\PipeInstallationExport',
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
            $filename = request()->modules.'_'.'L1'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
            


            if(request()->status == 'Pending'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $aeration = Aeration::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
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
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                } 

                    // return Excel::download(new AerationExport('All' ,json_encode(request()->all())), $filename);
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
                            'date' => \Carbon\Carbon::today()->toDateString()
                    ]);
            }//end of pending 
            


            if(request()->status == 'Approved'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $aeration = Aeration::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){                 
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    $w->where('status', 'Approved');
                    $w->where('apprv_reject_user_id', auth()->user()->id);
                    return $w;
                })
                ->limit(1)->get();     
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                } 

                    // return Excel::download(new AerationExport('All' ,json_encode(request()->all())), $filename);
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
                            'date' => \Carbon\Carbon::today()->toDateString()
                    ]);
            }//end of Approved 
            


            if(request()->status == 'Rejected'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $aeration = Aeration::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){                 
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    $w->where('status', 'Rejected');
                    $w->where('apprv_reject_user_id', auth()->user()->id);
                    return $w;
                })
                ->limit(1)->get();     
                if($aeration->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                } 
                    // return Excel::download(new AerationExport('All' ,json_encode(request()->all())), $filename);
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
            $filename = request()->modules.'_'.'L1'.'_'.request()->status.'_'.Carbon::now()->toDateTimeString().'.xlsx';
           

            if(request()->status == 'Pending'){
                //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $benefit = FarmerBenefit::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){                 
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    $w->where('status', 'Pending');
                    return $w;
                })
                ->limit(1)->get();

                if($benefit->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                } 

                    // return Excel::download(new L1BenefitExport('All' ,json_encode(request()->all())), $filename);
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
                        'date' => \Carbon\Carbon::today()->toDateString()
                ]);
            }//end of pending 
           

            if(request()->status == 'Approved'){
               //checking if data is available or not, show user get to know about record curent status 
                //if any changes also update  here
                $benefit = FarmerBenefit::whereHas('farmerapproved', function($q){
                    $q->where('onboarding_form',1);  
                    if(auth()->user()->hasRole('L-1-Validator')){
                        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                          $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                          $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                    }                          
                })
                ->when('fliter',function($w){
                    if(isset(request()->start_date) && request()->start_date){                 
                        $w->whereDate('created_at','>=',request()->start_date);
                    }
                    if(isset(request()->end_date) && request()->end_date){
                        $w->whereDate('created_at','<=',request()->end_date);
                    }
                    $w->where('status', 'Approved');
                    $w->where('apprv_reject_user_id', auth()->user()->id);
                    return $w;
                })
                ->limit(1)->get();

                if($benefit->isEmpty()){
                    return response()->json(['error'=>true, 'message'=>'No Record Found'],500);
                } 

                // return Excel::download(new L1BenefitExport('All' ,json_encode(request()->all())), $filename);
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
