<?php

namespace App\Http\Controllers\Admin\l1validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\AerationValidation;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;

use App\Models\PipeImgValidation;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
// use App\Exports\AerationValidation;
use App\Models\Minimumvalue;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\Aeration;
use App\Models\AerationImage; 
use Auth;
use App\Exports\PipeInstallationIndividualExport;
use App\Exports\L1ApprovedExport;
use App\Exports\AerationExport;
use App\Exports\L1AllPlotExport;
use App\Exports\AerationIndividualExport;
use App\Exports\L1PendingIndividualExport;
use App\Exports\PipeInstallationExport;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallation;


class AerationValidationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search($status)
    {
        if($status == 'Pending'){
                $Farmers = Aeration::with('farmerapproved')->where('status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmerapproved',function($q) use($status){
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
                });
                return response()->json($Farmers->get());
            }elseif($status == 'Approved'){
                $Farmers = Aeration::with('farmerapproved')->where('status',$status)->where('apprv_reject_user_id',auth()->user()->id)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmerapproved',function($q) use($status){
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
                });
                return response()->json($Farmers->get());
            }elseif($status == 'Rejected'){
                $Farmers = Aeration::with('farmerapproved')->where('status',$status)->where('apprv_reject_user_id',auth()->user()->id)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmerapproved',function($q) use($status){
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
                });
                return response()->json($Farmers->get());
            }
    }

  /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function awd_pending_lists()
  {
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
  		$plots = Aeration::with('farmerapproved')->where('status','Pending')->whereHas('farmerapproved',function($q){
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
        if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
             $q->where('surveyor_id',request('executive_onboarding'));
        }
        if(request()->has('start_date') && !empty(request('start_date'))){
            $q->whereDate('created_at','>=',request('start_date'));
        }
        if(request()->has('end_date') && !empty(request('end_date'))){
            $q->whereDate('created_at','<=',request('end_date'));
        }
        return $q;
  		})->orderBy('id','desc');
  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Aeration | Pending list';
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
  	  return view('l1validator.aeration.pending-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function awd_pending_detail($plotuniqueid,$aeration_no,$pipe_no){
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
    $validation_list = AerationValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-1-Validator')->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
    $Polygon = json_decode($PipeInstallation->ranges??"");
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','Aeration')->get();

    $awd_data=Aeration::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->first();
    $awd = Aeration::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
    $AwdImage = AerationImage::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();

    $page_title = 'Aeration | Pending Detail';
    $page_description = 'Aeration';
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
    return view('l1validator.aeration.pending-plot-awd',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','awd','AwdImage','awd_data'));
  }

   


    /**
       * Approve or Reject farmer status.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function aeration_validation(Request $request, $type,$UniqueId){
        if($type == "reject"){// for reject
            try{
                $aeration = Aeration::where('farmer_plot_uniqueid',$UniqueId)->where('aeration_no',$request->aeration_no)->where('pipe_no', $request->pipe_no)->first();

                $imgupdate =  AerationImage::where('farmer_plot_uniqueid',$UniqueId)->where('aeration_no',$request->aeration_no)->where('pipe_no', $request->pipe_no)->update([
                                                                                                            "status" => "Rejected", 
                                                                                                            // "reason_id" =>   $request->reasons,                                                                                   
                                                                                                                ]);
                                                                                                                
                $aeration_update =  Aeration::where('farmer_plot_uniqueid',$UniqueId)->where('aeration_no',$request->aeration_no)->where('pipe_no', $request->pipe_no)->update([
                                                                    "status" => "Rejected",  
                                                                    "apprv_reject_user_id" => auth()->user()->id,  
                                                                    "reason_id" =>   $request->reasons,                                                                                       
                                                                        ]);


                //to store record of pipe vlidation of approval and rejection. This is specifically for pipe                                                                                     
                $aeration_img_validation  = DB::table('aeration_validation')->insert([
                    "pipe_installation_id"      => $aeration->pipe_installation_id,
                    'farmer_uniqueId'           => $aeration->farmer_uniqueId,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'plot_no'                   => $aeration->plot_no,
                    'aeration_no'               => $request->aeration_no,
                    'pipe_no'                   => $request->pipe_no,
                    'status'                    => 'Rejected',
                    'level'                     => 'L-1-Validator',
                    'apprv_reject_user_id'      => auth()->user()->id,
                    'comment'                   => $request->rejectcomment,
                    'reject_reason_id'          => $request->reasons,
                    'timestamp'                 => Carbon::now(),
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                ]);                                        

                //also keep record for validation
                $record =  PlotStatusRecord::create([
                    'farmer_uniqueId'           => $UniqueId,
                    'plot_no'                   => $aeration->plot_no,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'level'                     => 'L-1-Validator',
                    'status'                    => 'Rejected',
                    'module'                    => 'Aeration-Image'.$request->aeration_no,
                    'comment'                   => "Aeration Image Rejection:. ".$request->rejectcomment,
                    'timestamp'                 => Carbon::now(),
                    'user_id'                   => auth()->user()->id,
                    'reject_reason_id'          => $request->reasons,
                ]);  
               
                return response()->json(['success' =>true, 'aeration'=>$imgupdate],200);
            
            } catch (\Exception $e) {              
                //   return $e->getMessage();
                return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            }


      }//end if for rejection
        if($type == "approve"){// for approval 
            // try {
                // dd($request->all());             
                    $aeration = Aeration::where('farmer_plot_uniqueid',$UniqueId)->where('aeration_no',$request->aeration_no)->where('pipe_no', $request->pipe_no)->first();

                    // $imgupdate =  AerationImage::where('farmer_plot_uniqueid',$UniqueId)->where('aeration_no',$request->aeration_no)->update([
                    //     "status" => "Approved", 
                    //     // "reason_id" =>   $request->reasons,                                                                                   
                    //         ]);

                    
                    $aeration_update =  Aeration::where('farmer_plot_uniqueid',$UniqueId)->where('aeration_no',$request->aeration_no)->where('pipe_no', $request->pipe_no)->update([
                        "status" => "Approved",  
                        "apprv_reject_user_id" => auth()->user()->id,                                                                                        
                            ]);

                        

                            // dd($request->all());
                    foreach($request->aeration_no as $data){

                        $imgupdate =  AerationImage::where('farmer_plot_uniqueid',$UniqueId)->where('aeration_no',$data['aeration_no'])
                                            ->where('pipe_no', $data['pipe_no'])->where('trash',0)->update([
                            "status" => "Approved", 
                            // "reason_id" =>   $request->reasons,                                                                                   
                                ]);

                        //to store record of pipe vlidation of approval and rejection. This is specifically for pipe                                                                                     
                        $aeration_validation  = DB::table('aeration_validation')->insert([
                            "pipe_installation_id"      => $aeration->pipe_installation_id,
                            'farmer_uniqueId'           => $aeration->farmer_uniqueId,
                            'farmer_plot_uniqueid'      => $UniqueId,
                            'plot_no'                   => $aeration->plot_no,
                            'aeration_no'               => $data['aeration_no'],
                            'pipe_no'                   => $request->pipe_no,
                            'status'                    => 'Approved',
                            'level'                     => 'L-1-Validator',
                            'apprv_reject_user_id'      => auth()->user()->id,
                            'comment'                   => $data['ApproveComment'],
                            'reject_reason_id'          => NULL,
                            'timestamp'                 => Carbon::now(),
                            'created_at'                => Carbon::now(),
                            'updated_at'                => Carbon::now(),
                        ]);  
                        //also keep record for validation
                        $record =  PlotStatusRecord::create([
                            'farmer_uniqueId'           => $aeration->farmer_uniqueId,
                            'plot_no'                   => $aeration->plot_no,
                            'farmer_plot_uniqueid'      => $UniqueId,
                            'level'                     => 'L-1-Validator',
                            'status'                    => 'Approved',
                            'module'                    => 'Aeration-Image-Aeration-'.$data['aeration_no'],
                            'comment'                   => "Aeration Image Approved:. ".$data['ApproveComment'],
                            'timestamp'                 => Carbon::now(),
                            'user_id'                   => auth()->user()->id,
                            'reject_reason_id'          => $request->reasons,
                        ]); 
                    
                    }//foreach end
                    return response()->json(['success' =>true, 'pipe_installation'=>$aeration_update],200); 
            //   } catch (\Exception $e) {              
            //     //   return $e->getMessage();
            //         return response()->json(['error' =>true, 'message'=>$e->getMessage()],500);       
            //   }
            
               
            
        }
    }


    /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function awd_reject_lists()
  {
     //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
        $plots = Aeration::with('farmerapproved')->where('status','Rejected')->whereHas('farmerapproved',function($q){
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
        if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
            $q->where('surveyor_id',request('executive_onboarding'));
        }
        if(request()->has('start_date') && !empty(request('start_date'))){
            $q->whereDate('created_at','>=',request('start_date'));
        }
        if(request()->has('end_date') && !empty(request('end_date'))){
            $q->whereDate('created_at','<=',request('end_date'));
        }
        return $q;
            })->orderBy('id','desc');
            return datatables()->of($plots)->make(true);
        }//end layoutout plot WITH AJAX
        // Onload below code excute first. And after successful load then again ajax make request to above code
            $page_title = 'Aeration | Reject list';
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
      return view('l1validator.aeration.reject-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }

  public function awd_reject_detail($plotuniqueid, $aeration_no,$pipe_no){
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
     $validation_list = AerationValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-1-Validator')->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
     $Polygon = json_decode($PipeInstallation->ranges);
  //   foreach($ploygon as $latlng){
  //       dd($latlng);
  //   }
  //   $Polygon =
 
     $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
     $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
     $reject_module = RejectModule::where('type','Aeration')->get();
 
     $awd_data=Aeration::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->first();
     $awd=Aeration::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
     $AwdImage = AerationImage::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
 
     $page_title = 'Aeration | Reject Detail';
     $page_description = 'Aeration';
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
     return view('l1validator.aeration.reject-plot-awd',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                 'reject_module','farmerbenefitimg','updated_polygon','validation_list','awd','AwdImage','awd_data'));
  }

   /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function awd_approved_lists()
  {
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
  		$plots = Aeration::with('farmerapproved')->where('status','Approved')->where('apprv_reject_user_id',auth()->user()->id)->whereHas('farmerapproved',function($q){
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
        if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
             $q->where('surveyor_id',request('executive_onboarding'));
        }
        if(request()->has('start_date') && !empty(request('start_date'))){
            $q->whereDate('created_at','>=',request('start_date'));
        }
        if(request()->has('end_date') && !empty(request('end_date'))){
            $q->whereDate('created_at','<=',request('end_date'));
        }
        return $q;
  		})->orderBy('id','desc');
  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Aeration | Approved list';
  		$page_description = 'List of Approved plots';
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
  	  return view('l1validator.aeration.approved-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }

  public function awd_approved_detail($plotuniqueid,$aeration_no,$pipe_no){
    // dd($plotuniqueid,$aeration_no,$pipe_no);
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
     $validation_list = AerationValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-1-Validator')->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
     $Polygon = json_decode($PipeInstallation->ranges);
  //   foreach($ploygon as $latlng){
  //       dd($latlng);
  //   }
  //   $Polygon =
 
     $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
     $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
     $reject_module = RejectModule::where('type','Aeration')->get();
 
     $awd_data=Aeration::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->first();

     $awd=Aeration::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
     $AwdImage = AerationImage::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
 
     $page_title = 'Aeration | Approved Detail';
     $page_description = 'Aeration';
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
     return view('l1validator.aeration.approved-plot-awd',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                 'reject_module','farmerbenefitimg','updated_polygon','validation_list','awd','AwdImage','awd_data'));
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

         if(request('type') == 'Aeration'){
            $filename = 'Aeration_'.request('status').'_'.Carbon::now().'.xlsx';
            // return Excel::download(new AerationExport('All',request()), $filename);
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
      public function excel_download($type, $unique_id, $plot_no, $status,$aeration_no){

        $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
        // $state_id = DB::table('final_farmers')->where('farmer_uniqueId', $unique_id)->first();
        return Excel::download(new AerationIndividualExport($type, $unique_id, $plot_no, $status, $aeration_no), $filename);

        // if($status == 'Approved'){

        //     $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
        //     $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
        //     return Excel::download(new AerationIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);


        // }elseif($status == 'Pending'){
        //     $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
        //     $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
        //     return Excel::download(new PipeInstallationIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
        // }elseif($status == 'Rejected'){
        //     $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
        //     $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
        //     return Excel::download(new L2RejectedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
        // }
     }

}
