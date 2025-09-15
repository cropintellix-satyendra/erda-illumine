<?php

namespace App\Http\Controllers\Admin\l1validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;
use App\Models\PipeImgValidation;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\User;
use Auth;
use App\Exports\PipeInstallationIndividualExport;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallation;

use App\Exports\PipeInstallationExport;

class PipeValidationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search($status)
    {
        if($status == 'Pending'){
            $plots = PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){
                if(auth()->user()->hasRole('L-1-Validator')){
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
                    return $q;
                  })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){
                        $c->where('status','Pending');
                        return $c;
                    });
                    return $im;
                })
                ->when('filter',function($w){
                    
                });
            return response()->json($plots->get());

        }elseif($status == 'Approved'){
            $plots = PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){
                if(auth()->user()->hasRole('L-1-Validator')){
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
                    return $q;
                  })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){
                        $c->where('status','Approved');
                        return $c;
                    });
                    return $im;
                })
                ->when('filter',function($w){
                    $w->where('status','Approved');
                    $w->where('apprv_reject_user_id',auth()->user()->id);
                });
            return response()->json($plots->get());
        }elseif($status == 'Rejected'){
            $plots = PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){
                if(auth()->user()->hasRole('L-1-Validator')){
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
                    return $q;
                  })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){
                        $c->where('status','Rejected');
                        return $c;
                    });
                    return $im;
                })
                ->when('filter',function($w){
                    
                });
            return response()->json($plots->get());
        }
    }

  /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function pipe_pending_lists()
  {
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
  		$plots = PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){
            if(auth()->user()->hasRole('L-1-Validator')){
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
        if(request()->has('state') && !empty(request('state'))){
            $q->where('state_id','like',request('state'));
        }
        if(request()->has('district') && !empty(request('district'))){
             $q->where('district_id','like',request('district'));
        }
        if(request()->has('taluka') && !empty(request('taluka'))){
             $q->where('taluka_id','like',request('taluka'));
        }
        if(request()->has('panchayats') && !empty(request('panchayats'))){
             $q->where('panchayat_id','like',request('panchayats'));
        }
        if(request()->has('village') && !empty(request('village'))){
             $q->where('village_id','like',request('village'));
        }
        if(request()->has('farmer_status') && !empty(request('farmer_status'))){
             $q->where('final_status_onboarding','like',request('farmer_status'));
        }
              
        return $q;
  		})
        ->whereHas('pipe_image',function($im){
            // $im->where('status','Pending');
            $im->when('filter',function($c){
                $c->where('status','Approved');
                $c->where('l2status','Pending');
                return $c;
            });
            return $im;
        })
        ->when('filter',function($w){
            // dd(request('start_date'));
            if(request()->has('start_date') && !empty(request('start_date'))){
                $w->whereDate('created_at','>=',request('start_date'));
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $w->whereDate('created_at','<=',request('end_date'));
            }
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $w->where('surveyor_id',request('executive_onboarding'));
            }  
            return $w;
        })
        ->orderBy('id','desc');
        //end of datatable
  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'PipeInstallation | Pending list';
  		$page_description = 'List of pending plots';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $states = DB::table('states')->where('status',1)->get();
        $districts = DB::table('districts')->where('status',1)->get();
        $talukas = DB::table('talukas')->where('status',1)->get();
        $panchayats = DB::table('panchayats')->get();
        $villages = DB::table('villages')->get();
        $onboarding_executive  = DB::table('farmers')->where('onboarding_form','1')->groupBy('farmers.surveyor_name')
                            ->join('farmer_plot_detail', 'farmers.id' ,'=','farmer_plot_detail.farmer_id')
                            ->where('farmer_plot_detail.final_status', '=','Pending')
                            ->get();

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l1validator.pipe.pending-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function pipe_pending_detail($plotuniqueid){
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = PipeInstallation::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    // $PipesLocation="";
    // if($PipeInstallation->pipes_location){
    //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
    // }

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->where('trash', 0)->get();
    
    
  


    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-1-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->get();
    $page_title = 'PipeInstallation | Pending Detail';
    $page_description = 'Pipe Installation';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
        $mod =  abs($PipeInstallation->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
        $denominator = $PipeInstallation->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
        //below percentage error between onboarding area and updated area
        $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
    return view('l1validator.pipe.pending-plot-pipe',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }

   /**
     * update polygon
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_polygon(Request $request){                        
        try {
            $pipe_data=PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
            $process_update_polygon = json_decode($request->updatedpolygon);
            foreach($process_update_polygon  as $index=>$array){
                $data = ["lat"=> $array[0], "lng" =>  $array[1]];
                $multipolygon[$index] = $data;
            }
            $updated_polygon = json_encode($multipolygon);//after processing got polygon.
            //now moving old polygon to table = old_polygon
            //adding old polygon in table, just to hold old record


            DB::table('old_polygons')->insert([
                "farmer_uniqueId"   =>  $pipe_data->farmer_uniqueId,
                "farmer_plot_uniqueid"     =>  $pipe_data->farmer_plot_uniqueid,
                "plot_no"   =>  $pipe_data->plot_no,
                "polygon"   =>  $pipe_data->ranges,//adding old polygon from pipeinstallation table
                "surveyor_id"   =>  auth()->user()->id,
                "type"          => 'OLD POLYGON',
                "google_plot_area" => $pipe_data->plot_area,
                "created_at"     => carbon::now(),
                "updated_at"    => carbon::now(),
            ]);
            //adding update polygon ,just to hold old record
            // DB::table('old_polygons')->insert([
            //     "farmer_uniqueId"   =>  $pipe_data->farmer_uniqueId,
            //     "farmer_plot_uniqueid"     =>  $pipe_data->farmer_plot_uniqueid,
            //     "plot_no"   =>  $pipe_data->plot_no,
            //     "polygon"   =>  $updated_polygon,//adding old polygon from pipeinstallation table
            //     "surveyor_id"   =>  auth()->user()->id,
            //     "type"          => 'UPDATED POLYGON',
            //     "created_at"     => carbon::now(),
            //     "updated_at"    => carbon::now(),
            // ]);

            //now adding update polygon to origin al table
            PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update([
                'ranges' =>$updated_polygon,
                'plot_area' => number_format($request->updated_poly_area, 2, '.', ''),
                        ]);
            $record =  PlotStatusRecord::create([
                'farmer_uniqueId'           => $pipe_data->farmer_uniqueId,
                'plot_no'                   => $pipe_data->plot_no,
                'farmer_plot_uniqueid'      => $pipe_data->farmer_plot_uniqueid,
                'level'                     => 'L-2-Validator',
                'status'                    => $pipe_data->status,
                'comment'                   => 'Polygon Updated From WEB',
                'timestamp'                 => Carbon::now(),
                'user_id'                   => auth()->user()->id,
            ]);
            return response()->json(['success'=>true, 'message'=>'Updated Successfully'],200);
            
        } catch (\Exception $e) {
        
            return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
        }

         
    }


    /**
       * Approve or Reject farmer status.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function pipeinstallation_validation(Request $request, $type,$UniqueId){
        if($type == "reject"){// for reject   
            $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->first();
            $imgupdate =  PipeInstallationPipeImg::where('farmer_plot_uniqueid',$UniqueId)->where('pipe_no',$request->pipeno)->where('id',$request->pipe_id)->update([
                                                                                                        "status" => "Rejected", 
                                                                                                        "reason_id" =>   $request->reasons,                                                                                   
                                                                                                            ]);
           
            //to store record of pipe vlidation of approval and rejection. This is specifically for pipe                                                                                     
            $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
                'farmer_uniqueId'           => $pipe_installation->farmer_uniqueId,
                'farmer_plot_uniqueid'      => $UniqueId,
                'plot_no'                   => $pipe_installation->plot_no,
                'pipe_no'                   => $request->pipeno,
                'status'                    => 'Rejected',
                'level'                     => 'L-1-Validator',
                'user_id'                   => auth()->user()->id,
                'comment'                   => $request->rejectcomment,
                'reject_reason_id'          => $request->reasons,
                'timestamp'                 => Carbon::now(),
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
            ]);                                        

            //also keep record for validation
            $record =  PlotStatusRecord::create([
                'farmer_uniqueId'           => $UniqueId,
                'plot_no'                   => $pipe_installation->plot_no,
                'farmer_plot_uniqueid'      => $UniqueId,
                'level'                     => 'L-1-Validator',
                'status'                    => 'Rejected',
                'module'                    => 'PipeInstallation-Image-PIPE '.$request->pipeno,
                'comment'                   => "Pipe Image Rejection:. ".$request->rejectcomment,
                'timestamp'                 => Carbon::now(),
                'user_id'                   => auth()->user()->id,
                'reject_reason_id'          => $request->reasons,
            ]);  
            
            $img_data_reject = PipeInstallationPipeImg::select('status')->where('status','Rejected')->where('farmer_plot_uniqueid',$UniqueId)
                        ->where('plot_no',$pipe_installation->plot_no)->where('trash',0)->get(); 
            if($img_data_reject->count() == $pipe_installation->installed_pipe){
                //if all pipe image is rejected then only below code will execute
                $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
                    "status" => "Rejected",  
                    "apprv_reject_user_id" => auth()->user()->id,                                                                                        
                        ]);
            }

            if(!$pipe_img_validation){
                return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            }
            return response()->json(['success' =>true, 'pipe_installation'=>$pipe_img_validation],200);
      }//end if for rejection
        if($type == "approve"){// for approval  
            if(!isset($request->pipes) && empty($request->pipes)){
                return response()->json(['error' =>true, 'empty'=>'Please select pipe','message'=>'Something went wrong'],500);
            }
            $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->first();
                     
            foreach($request->pipes as $data){
                //update status of image
                $img_data = PipeInstallationPipeImg::where('farmer_plot_uniqueid',$UniqueId)->where('pipe_no',$data['pipe_no'])->where('id',$data['pipe_id'])->update([
                                                                                                                        "status" => 'Approved',
                                                                                                                        "reason_id" => NULL,        
                                                                                                                        ]);    

                //to store record of pipe vlidation of approval and rejection. This is specifically for pipe                                                                                     
                $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
                    'farmer_uniqueId'           => $pipe_installation->farmer_uniqueId,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'plot_no'                   => $pipe_installation->plot_no,
                    'pipe_no'                   => $data['pipe_no'],
                    'status'                    => 'Approved',
                    'level'                    => 'L-1-Validator',
                    'user_id'                   => auth()->user()->id,
                    'comment'                   => $data['ApproveComment'],
                    'reject_reason_id'          => NULL,
                    'timestamp'                 => Carbon::now(),
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                ]);  
                //also keep record for validation
                $record =  PlotStatusRecord::create([
                    'farmer_uniqueId'           => $pipe_installation->farmer_uniqueId,
                    'plot_no'                   => $pipe_installation->plot_no,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'level'                     => 'L-1-Validator',
                    'status'                    => 'Approved',
                    'module'                    => 'PipeInstallation-Image-PIPE '.$data['pipe_no'],
                    'comment'                   => "Pipe Image Approved:. ".$data['ApproveComment'],
                    'timestamp'                 => Carbon::now(),
                    'user_id'                   => auth()->user()->id,
                    'reject_reason_id'          => $request->reasons,
                ]);                 
            }
            $img_data_apprv = PipeInstallationPipeImg::select('status')->where('status','Approved')->where('farmer_plot_uniqueid',$UniqueId)->where('trash',0)->get(); 
            
            if($img_data_apprv->count() == $pipe_installation->no_pipe_req){
                //if all pipe image is verified then only below code will execute
                $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
                    "status" => "Approved",   
                    "apprv_reject_user_id" => auth()->user()->id,                                                                                          
                        ]);
            }
            if(!$pipe_img_validation){
                return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            }
            return response()->json(['success' =>true, 'pipe_installation'=>$img_data],200); 
        }
    }


    /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function pipe_reject_lists()
  {
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
        // ->where('apprv_reject_user_id',auth()->user()->id)
  		$plots= PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){
            if(auth()->user()->hasRole('L-1-Validator')){
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
        if(request()->has('state') && !empty(request('state'))){
            $q->where('state_id','like',request('state'));
        }
        if(request()->has('district') && !empty(request('district'))){
             $q->where('district_id','like',request('district'));
        }
        if(request()->has('taluka') && !empty(request('taluka'))){
             $q->where('taluka_id','like',request('taluka'));
        }
        if(request()->has('panchayats') && !empty(request('panchayats'))){
             $q->where('panchayat_id','like',request('panchayats'));
        }
        if(request()->has('village') && !empty(request('village'))){
             $q->where('village_id','like',request('village'));
        }
        if(request()->has('farmer_status') && !empty(request('farmer_status'))){
             $q->where('final_status_onboarding','like',request('farmer_status'));
        }
       
        return $q;
  		})
          ->whereHas('pipe_image',function($im){
            $im->when('filter',function($c){
                $c->where('status','Rejected');
                $c->where('trash',0);
                return $c;
            });
            return $im;
        })
        //   ->whereHas('reject_validation_detail',function($im){
        //     $im->where('status','Rejected');
        //     $im->where('user_id',auth()->user()->id);
        //     // 22476P1
        //     // $im->when('filter',function($c){
        //     //     $c->where('status','Rejected');
        //     //     return $c;
        //     // });
        //     return $im;
        // })          
        // ->whereHas('pipe_image',function($im){
        //     $im->where('status','Rejected');
        //     // 22476P1
        //     // $im->when('filter',function($c){
        //     //     $c->where('status','Rejected');
        //     //     return $c;
        //     // });
        //     return $im;
        // })
        ->when('filter',function($w) {
            // dd(request('start_date'));
            if(request()->has('start_date') && !empty(request('start_date'))){
                $w->whereDate('created_at','>=',request('start_date'));
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $w->whereDate('created_at','<=',request('end_date'));
            }
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $w->where('surveyor_id',request('executive_onboarding'));
            }  
            return $w;
        })
        ->orderBy('id','desc');

  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot WITH AJAX
    // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'PipeInstallation | Reject list';
  		$page_description = 'List of Reject plots';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
      //for admin data

      $states = DB::table('states')->where('status',1)->get();
      $districts = DB::table('districts')->where('status',1)->get();
      $talukas = DB::table('talukas')->where('status',1)->get();
      $panchayats = DB::table('panchayats')->get();
      $villages = DB::table('villages')->get();

        $onboarding_executive  = DB::table('farmers')->where('onboarding_form','1')->groupBy('farmers.surveyor_name')
                        ->join('farmer_plot_detail', 'farmers.id' ,'=','farmer_plot_detail.farmer_id')
                        ->where('farmer_plot_detail.final_status', '=','Pending')
                        ->get();

  		$seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l1validator.pipe.reject-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }

  public function pipe_reject_detail($plotuniqueid){
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = PipeInstallation::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    // $PipesLocation="";
    // if($PipeInstallation->pipes_location){
    //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
    // }

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('trash',0)->where('farmer_plot_uniqueid',$plotuniqueid)->where('status','Rejected')->get();
    
    

    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-1-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->get();
    $page_title = 'PipeInstallation | Reject Detail';
    $page_description = 'Pipe Installation';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
        $mod =  abs($PipeInstallation->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
        $denominator = $PipeInstallation->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
        //below percentage error between onboarding area and updated area
        $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
    return view('l1validator.pipe.reject-pipe-detail',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }

  /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function pipe_approved_lists()
  {
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
  		$plots= PipeInstallation::with('farmerapproved')->where('status','Approved')->where('apprv_reject_user_id',auth()->user()->id)->whereHas('farmerapproved',function($q){
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
        if(request()->has('state') && !empty(request('state'))){
            $q->where('state_id','like',request('state'));
        }
        if(request()->has('district') && !empty(request('district'))){
             $q->where('district_id','like',request('district'));
        }
        if(request()->has('taluka') && !empty(request('taluka'))){
             $q->where('taluka_id','like',request('taluka'));
        }
        if(request()->has('panchayats') && !empty(request('panchayats'))){
             $q->where('panchayat_id','like',request('panchayats'));
        }
        if(request()->has('village') && !empty(request('village'))){
             $q->where('village_id','like',request('village'));
        }
        if(request()->has('farmer_status') && !empty(request('farmer_status'))){
             $q->where('final_status_onboarding','like',request('farmer_status'));
        }
        return $q;
  		})
        ->whereHas('pipe_image',function($im){
            $im->when('filter',function($c){
                $c->where('status','Approved');
                return $c;
            });
            return $im;
        })
        ->when('filter',function($w) {
            if(request()->has('start_date') && !empty(request('start_date'))){
                $w->whereDate('created_at','>=',request('start_date'));
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $w->whereDate('created_at','<=',request('end_date'));
            }
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $w->where('surveyor_id',request('executive_onboarding'));
            }  
            return $w;
        })
        ->orderBy('id','desc');
  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot WITH AJAX
    // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'PipeInstallation | Approved list';
  		$page_description = 'List of Approve plots';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
      //for admin data

      $states = DB::table('states')->where('status',1)->get();
      $districts = DB::table('districts')->where('status',1)->get();
      $talukas = DB::table('talukas')->where('status',1)->get();
      $panchayats = DB::table('panchayats')->get();
      $villages = DB::table('villages')->get();

        $onboarding_executive  = DB::table('farmers')->where('onboarding_form','1')->groupBy('farmers.surveyor_name')
                        ->join('farmer_plot_detail', 'farmers.id' ,'=','farmer_plot_detail.farmer_id')
                        ->where('farmer_plot_detail.final_status', '=','Pending')
                        ->get();

       
  		$seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l1validator.pipe.approved-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive',
      'status','others'));
  }

  public function pipe_approved_detail($plotuniqueid){
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = PipeInstallation::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    // $PipesLocation="";
    // if($PipeInstallation->pipes_location){
    //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
    // }

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('trash',0)->where('farmer_plot_uniqueid',$plotuniqueid)->get();
    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-1-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->get();
    $page_title = 'PipeInstallation | Approved Detail';
    $page_description = 'Pipe Installation';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
        $mod =  abs($PipeInstallation->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
        $denominator = $PipeInstallation->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
        //below percentage error between onboarding area and updated area
        $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
    return view('l1validator.pipe.approved-pipe-detail',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }

  /**
     * Download excel file.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadFile(){
        set_time_limit(-1);
        ini_set('memory_limit', '640M');
        if(request('file')){
         $name = 'L-2-'.request('status').'_';

         if(request('type') == 'PipeInstalltion'){
            $filename = 'pipe-installation_'.request('status').'_'.Carbon::now().'.xlsx';
            //  return Excel::download(new PipeInstallationExport('All',request()), $filename);

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
                         'created_at'=>\Carbon\Carbon::now()->timestamp
                 ]);
             if(!$job){
                 return response()->json([
                         'error'=>true,
                         'message'=>'Unknown Error!'
                     ]);
             }
             return response()->json([
                 'success'=>true,
                 'message'=>'Export request submitted. Please check download section'
             ]);
         }
        }// end of request('file')
      }

      /**
       * Download individual excel file
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function excel_download($type, $unique_id, $plot_no, $status){
        if($status == 'Approved'){

            $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
            $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
            return Excel::download(new PipeInstallationIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);


        }elseif($status == 'Pending'){
            $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
            $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
            return Excel::download(new PipeInstallationIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
        }elseif($status == 'Rejected'){
            $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
            $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
            return Excel::download(new L2RejectedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
        }
     }

}
