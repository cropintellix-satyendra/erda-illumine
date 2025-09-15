<?php

namespace App\Http\Controllers\Admin\Account\l2validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\AerationValidation;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
use App\Models\FarmerPlot;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\Season;
use App\Models\AerationImage; 
use App\Exports\L2CropdataExport;
use App\Exports\L2CropDataIndividualExport;
use App\Models\PlotStatusRecord;
use App\Models\FarmerCropdata;
use App\Models\Cropvariety;
use App\Models\Setting;
use App\Models\CropDataValidation;
use DateTime;
use Illuminate\Pagination\LengthAwarePaginator;


class L2CropDataValidationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search($role, $status)
    {
        if($status == 'Pending'){
                $Farmers = FarmerCropdata::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)
                ->whereHas('farmerapproved',function($q) use($status){
                    if(auth()->user()->hasRole('Viewer')){
                        $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
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

            }else{
                $Farmers = FarmerCropdata::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)
                ->whereHas('farmerapproved',function($q) use($status){
                    if(auth()->user()->hasRole('Viewer')){
                        $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
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
  public function cropdata_pending_lists()
  {
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	//   if(request()->ajax()){
        $l1_state=$l1_district_id=$l1_taluka_id="";
            if(auth()->user()->hasRole('Viewer')){
                //this condition is used when l1 user is login, to from vendor location table 
                $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                $l1_state = $VendorLocation->state; 
            
                if(!empty($VendorLocation->district)){
                $l1_district_id = "OR final_farmers.district_id IN (".$VendorLocation->district.")";
                }
                if(!empty($VendorLocation->taluka)){
                $l1_taluka_id = "OR final_farmers.taluka_id IN (".$VendorLocation->taluka.")";
                }  
                $condition = "AND (final_farmers.state_id IN (".$l1_state.") 
                                ".$l1_district_id." 
                                ".$l1_taluka_id."
                            )";
                            
                            
            }elseif(auth()->user()->hasRole('SuperAdmin')){
                $condition="";   
            }
         
            $state_query=$district_query=$taluka_query=$panchayats_q=$village_query="";
            //below query will be applicable when filter from above the table is used.
            if(request()->has('state') && !empty(request('state'))){
                $state_query = 'AND (final_farmers.state_id = '.request('state').')';
            }
            if(request()->has('district') && !empty(request('district'))){
                $district_query = 'AND (final_farmers.district_id = '.request('district').')';
            }
            if(request()->has('taluka') && !empty(request('taluka'))){
                 $taluka_query =  "AND (final_farmers.taluka_id = ".request('taluka').") ";
            }
            if(request()->has('panchayats') && !empty(request('panchayats'))){
                 $panchayats_q = "AND (final_farmers.panchayat_id = ".request('panchayats').")";
            }
            if(request()->has('village') && !empty(request('village'))){
                 $village_query = "AND (final_farmers.village_id = ".request('village').")";
            }    


            $start_date=$surveyor_id=$end_date=$season="";
            //below query will be applicable when filter from above the table is used.
            if(request()->has('start_date') && !empty(request('start_date'))){
                $start_date = "AND (farmer_cropdata.created_at >= ".request('start_date').")";
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $end_date = "AND (farmer_cropdata.created_at <= ".request('end_date').")";
            }
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $end_date = "AND (farmer_cropdata.surveyor_id = ".request('executive_onboarding').")";
            }
            if(request()->has('seasons') && !empty(request('seasons'))){
                $season = "AND (farmer_cropdata.season = '".request('seasons')."' )";
            }

        $crop_sql = "
            SELECT * FROM farmer_cropdata 
            JOIN final_farmers ON farmer_cropdata.farmer_plot_uniqueid = final_farmers.farmer_plot_uniqueid
            JOIN farmer_plot_detail ON farmer_cropdata.farmer_plot_uniqueid = farmer_plot_detail.farmer_plot_uniqueid
            WHERE farmer_cropdata.status = 'Approved' 
            AND farmer_cropdata.deleted_at IS NULL
            AND (farmer_cropdata.l2_status = 'Pending' )    
                ".$condition."        
                ".$state_query."
                ".$district_query."
                ".$taluka_query."
                ".$panchayats_q."
                ".$village_query."         

                ".$season."
                ".$start_date." 
                ".$end_date."  
                ".$end_date."
            ORDER BY farmer_cropdata.id DESC;
        ";// end of sql

        $crop_sql = strtr($crop_sql, array("\r\n" => "","\r" => "","\n" => "","\t" => " "));
        $crop_data  = DB::select($crop_sql);

         // Paginate the results manually
         $perPage = 100; // Number of records per page
         $page = request()->get('page', 1); // Get the current page number from the request
 
         // Calculate the starting index of records for the current page
         $start = ($page - 1) * $perPage;
 
         // Slice the array to get the records for the current page
         $paginatedcrop_data = array_slice($crop_data, $start, $perPage);
 
         $pagination = new LengthAwarePaginator(
             $paginatedcrop_data,
             count($crop_data),
             $perPage,
             $page,
             ['path' => request()->url(), 'query' => request()->query()]
         );

         $path = $pagination->links()->elements;
            $links=[];
            foreach ($path as $key=>$item) {
            if (is_array($item)) {
                $links = array_merge($links, $item);
            }
            }
        // FOR YAJRA DATATABLES VERSION > 8.0
    //     return datatables()->of($crop_data)->toJson();
    //     return datatables()->of($crop_data)->make(true);
	//   }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Cropdata | Pending list';
  		$page_description = 'Cropdata | Pending list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $states = DB::table('states')->where('status',1)->get();
        $districts = DB::table('districts')->where('status',1)->get();
        $talukas = DB::table('talukas')->where('status',1)->get();
        $panchayats = DB::table('panchayats')->get();
        $villages = DB::table('villages')->get();
        $onboarding_executive  = DB::table('farmer_cropdata')->groupBy('farmer_cropdata.surveyor_name')->get();

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('admin.l2validator.cropdata.pending-plot',compact('links','pagination','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function cropdata_pending_detail($rolename, $plotuniqueid,$plot_no){
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid )->get();
    $farmerplots_area =  FinalFarmer::where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid )->get();

    $seasons = Season::select('id','name')->where('status',1)->get();

    $cropdata = FarmerCropdata::where('farmer_plot_uniqueid',$plotuniqueid)->get();
   

    $crop_data_detail = FarmerCropdata::where('farmer_plot_uniqueid',$plotuniqueid)->first();
 

    $CropvarietyList =  Cropvariety::select('id','name','state_id' )->where('state_id',$plot->state_id)->get();

    $validation_list = CropDataValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
    
    $date_setting = Setting::where('id','1')->select('preparation_date_interval','transplantation_date_interval')->first();
  

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','Aeration')->get();

    $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
   

    $page_title = 'Cropdata | Pending Detail';
    $page_description = 'Cropdata | Pending Detail';
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
    return view('admin.l2validator.cropdata.pending-plot-crop',compact('plot','page_title','page_description','action','farmerplots',
                'reject_module','farmerbenefitimg','updated_polygon','cropdata','check_pipedata','seasons','CropvarietyList','crop_data_detail','validation_list','date_setting','rolename'));
  }

    public function cropdata_pending_update(Request $request, $unique_id){
        try {
            
            $cropdata_detail = FarmerCropdata::where('farmer_plot_uniqueid', $unique_id)->first();

            $dt_irrigation_last = Carbon::parse($request->dt_irrigation_last)->format('d/m/Y');
            $dt_ploughing = Carbon::parse($request->dt_ploughing)->format('d/m/Y');
            $dt_transplanting = Carbon::parse($request->dt_transplanting)->format('d/m/Y');
            $cropdata = FarmerCropdata::where('farmer_plot_uniqueid', $unique_id)->update([
                "crop_variety" =>$request->crop_variety,
                'dt_irrigation_last'   => $dt_irrigation_last,
                'dt_ploughing'   => $dt_ploughing,
                'dt_transplanting'   => $dt_transplanting,
            ]);
            $record =  PlotStatusRecord::create([
                'farmer_uniqueId'           => $cropdata_detail->farmer_uniqueId,
                'plot_no'                   => $cropdata_detail->plot_no,
                'farmer_plot_uniqueid'      => $unique_id,
                'level'                     => 'L-2-Validator',
                'status'                    => 'Approved',
                'module'                    => 'Cropdata-edit-'.$cropdata_detail->plot_no,
                'comment'                   => "Cropdata edit",
                'timestamp'                 => Carbon::now(),
                'user_id'                   => auth()->user()->id,
                'reject_reason_id'          => NULL,
            ]); 
            return response()->json(['success' => true],200);          
          } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Something went wrong'],500);
             
          }   
    }


    /**
       * Approve or Reject farmer status.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function cropdata_validation(Request $request,$UniqueId,$plotno){
        // try {
                // dd($request->all());             
                $cropdata = FarmerCropdata::where('farmer_plot_uniqueid',$UniqueId)->where('plot_no',$plotno)->first();
                foreach($request->plots as $data){
                    $cropupdate =  FarmerCropdata::where('farmer_plot_uniqueid',$UniqueId)->where('plot_no',$data['PlotNo'])->update([
                                                                                                        "l2_status" => "Approved",  
                                                                                                        'l2_apprv_reject_user_id' => auth()->user()->id,                                                                               
                                                                                                        ]);

                    //to store record of pipe vlidation of approval and rejection. This is specifically for pipe                                                                                     
                    $cropdata_validation  = DB::table('cropdata_validation')->insert([
                        // 'farmer_uniqueId'           => $cropdata->farmer_uniqueId,
                        'farmer_plot_uniqueid'      => $UniqueId,
                        'plot_no'                   => $data['PlotNo'],
                        'status'                    => 'Approved',
                        'level'                     => 'L-2-Validator',
                        'user_id'                   => auth()->user()->id,
                        'comment'                   => $data['ApproveComment'],
                        'reject_reason_id'          => NULL,
                        'timestamp'                 => Carbon::now(),
                        'created_at'                => Carbon::now(),
                        'updated_at'                => Carbon::now(),
                    ]);  
                    //also keep record for validation
                    $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           => $cropdata->farmer_uniqueId,
                        'plot_no'                   => $data['PlotNo'],
                        'farmer_plot_uniqueid'      => $UniqueId,
                        'level'                     => 'L-2-Validator',
                        'status'                    => 'Approved',
                        'module'                    => 'Cropdata-'.$data['PlotNo'],
                        'comment'                   => "Cropdata Approved:. ".$data['ApproveComment'],
                        'timestamp'                 => Carbon::now(),
                        'user_id'                   => auth()->user()->id,
                        'reject_reason_id'          => NULL,
                    ]); 
                
                }//foreach end
                return response()->json(['success' =>true, 'crop_data'=>$cropdata],200); 
        //   } catch (\Exception $e) {              
        //     //   return $e->getMessage();
        //         return response()->json(['error' =>true, 'message'=>$e->getMessage()],500);       
        //   }
        
    }


    /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function cropdata_reject_lists()
  {
     //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
        $plots = FarmerCropdata::with('farmerapproved')->where('status','Rejected')->whereHas('farmerapproved',function($q){
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
        ->when('filter',function($w){
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $w->where('surveyor_id',request('executive_onboarding'));
            }
            if(request()->has('start_date') && !empty(request('start_date'))){
                $w->whereDate('created_at','>=',request('start_date'));
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $w->whereDate('created_at','<=',request('end_date'));
            }
            return $w;
        })
        ->orderBy('id','desc');
        return datatables()->of($plots)->make(true);
        }//end layoutout plot WITH AJAX
        // Onload below code excute first. And after successful load then again ajax make request to above code
            $page_title = 'Cropdata | Reject list';
            $page_description = 'Cropdata | Reject list';
            $action = 'table_farmer';
            //below process is for first time landing on page}
        //for admin data
        $states = DB::table('states')->where('status',1)->get();
        $districts = DB::table('districts')->where('status',1)->get();
        $talukas = DB::table('talukas')->where('status',1)->get();
        $panchayats = DB::table('panchayats')->get();
        $villages = DB::table('villages')->get();
        $onboarding_executive  = DB::table('farmer_cropdata')->groupBy('farmer_cropdata.surveyor_name')->get();

        $seasons = DB::table('seasons')->get();
        $status = request()->status;
        $others = "0";
      return view('l1validator.cropdata.reject-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }

  public function cropdata_reject_detail($plotuniqueid, $aeration_no,$pipe_no){
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
 
     $awd_data=FarmerCropdata::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->first();
     $awd=FarmerCropdata::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
     $AwdImage = AerationImage::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
 
     $page_title = 'Cropdata | Reject detail';
     $page_description = 'Cropdata | Reject detail';
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
     return view('l1validator.cropdata.reject-plot-awd',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                 'reject_module','farmerbenefitimg','updated_polygon','validation_list','awd','AwdImage','awd_data'));
  }


  public function cropdata_approved_lists()
{

    $cacheExpirationInSeconds=60;

    if(auth()->user()->hasRole('Viewer')){
                    //this condition is used when l1 user is login, to from vendor location table 
                    $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                    $l1_state = $VendorLocation->state; 
                
                    if(!empty($VendorLocation->district)){
                      $l1_district_id = "OR final_farmers.district_id IN (".$VendorLocation->district.")";
                    }
                    if(!empty($VendorLocation->taluka)){
                      $l1_taluka_id = "OR final_farmers.taluka_id IN (".$VendorLocation->taluka.")";
                    }  
                    $condition = "AND (final_farmers.state_id IN (".$l1_state.") 
                                    ".$l1_district_id." 
                                    ".$l1_taluka_id."
                                )";
                                
                                
                }elseif(auth()->user()->hasRole('SuperAdmin')){
                
                    $condition="";   
                }

               


                $state_query=$district_query=$taluka_query=$panchayats_q=$village_query="";
                    //below query will be applicable when filter from above the table is used.
                    if(request()->has('state') && !empty(request('state'))){
                        $state_query = 'AND (final_farmers.state_id = '.request('state').')';
                    }
                    if(request()->has('district') && !empty(request('district'))){
                        $district_query = 'AND (final_farmers.district_id = '.request('district').')';
                    }
                    if(request()->has('taluka') && !empty(request('taluka'))){
                         $taluka_query =  "AND (final_farmers.taluka_id = ".request('taluka').") ";
                    }
                    if(request()->has('panchayats') && !empty(request('panchayats'))){
                         $panchayats_q = "AND (final_farmers.panchayat_id = ".request('panchayats').")";
                    }
                    if(request()->has('village') && !empty(request('village'))){
                         $village_query = "AND (final_farmers.village_id = ".request('village').")";
                    }    
        
                    $start_date=$surveyor_id=$end_date=$season="";
                    //below query will be applicable when filter from above the table is used.
                    if(request()->has('start_date') && !empty(request('start_date'))){
                        $start_date = "AND (farmer_cropdata.created_at >= ".request('start_date').")";
                    }
                    if(request()->has('end_date') && !empty(request('end_date'))){
                        $end_date = "AND (farmer_cropdata.created_at <= ".request('end_date').")";
                    }
                    if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                        $end_date = "AND (farmer_cropdata.surveyor_id = ".request('executive_onboarding').")";
                    }
                    if(request()->has('seasons') && !empty(request('seasons'))){
                        $season = "AND (farmer_cropdata.season = '".request('seasons')."' )";
                    }

// $data = cache()->remember('plots_paginate_cropdata_approved', $cacheExpirationInSeconds, function () {
//     return 
    $query  = DB::table('farmer_cropdata')
        ->join('final_farmers', 'farmer_cropdata.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
        ->join('farmer_plot_detail', 'farmer_cropdata.farmer_plot_uniqueid', '=', 'farmer_plot_detail.farmer_plot_uniqueid')
        ->where('farmer_cropdata.status', 'Approved')
        ->where('farmer_cropdata.l2_status', 'Approved');
    if (!empty($condition)) {
        $query->whereRaw($condition);
    }
    if (!empty($state_query)) {
        $query->whereRaw($state_query);
    }
    if (!empty($district_query)) {
        $query->whereRaw($district_query);
    }
    if (!empty($taluka_query)) {
        $query->whereRaw($taluka_query);
    }
    if (!empty($panchayats_q)) {
        $query->whereRaw($panchayats_q);
    }
    if (!empty($village_query)) {
        $query->whereRaw($village_query);
    }
    if (!empty($season)) {
        $query->whereRaw($season);
    }
    if (!empty($start_date)) {
        $query->whereRaw($start_date);
    }
    if (!empty($end_date)) {
        $query->whereRaw($end_date);
    }
    if (!empty($executive_onboarding)) {
        $query->where('farmer_cropdata.surveyor_id', $executive_onboarding);
    }

    $crop_data = $query->orderBy('farmer_cropdata.id', 'DESC')->paginate(100);

// });


 // End Of cache function


    // dd($crop_data);
    // $perPage = 100; // Number of records per page
    //         $page = request()->get('page', 1); // Get the current page number from the request
    //         // Calculate the starting index of records for the current page
    //         $start = ($page - 1) * $perPage;
    //         // Slice the array to get the records for the current page
    //         $paginatedCropData = array_slice($crop_data, $start, $perPage);
            
    //         $pagination = new LengthAwarePaginator(
    //             $paginatedCropData,
    //             count($crop_data),
    //             $perPage,
    //             $page,
    //             ['path' => request()->url(), 'query' => request()->query()]
    //         );
    
    //         $path = $pagination->links()->elements;
    //         $links=[];
    //         foreach ($path as $key=>$item) {
    //           if (is_array($item)) {
    //               $links = array_merge($links, $item);
    //           }
    //         }
    
            // FOR YAJRA DATATABLES VERSION > 8.0
            // return datatables()->of($crop_data)->toJson();
            // return datatables()->of($crop_data)->make(true);
        
           // Onload below code excute first. And after successful load then again ajax make request to above code
      		$page_title = 'Cropdata | Approved list';
      		$page_description = 'Cropdata | Approved list';
      		$action = 'table_farmer';
      		//below process is for first time landing on page}
            //for admin data
            $states = DB::table('states')->where('status',1)->get();
            $districts = DB::table('districts')->where('status',1)->get();
            $talukas = DB::table('talukas')->where('status',1)->get();
            $panchayats = DB::table('panchayats')->get();
            $villages = DB::table('villages')->get();
            $onboarding_executive  = DB::table('farmer_cropdata')->groupBy('farmer_cropdata.surveyor_name')->get();
      	  $seasons = DB::table('seasons')->get();
          $status = request()->status;
          $others = "0";

          return view('admin.l2validator.cropdata.approved-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others','crop_data'));
}

   /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  //Commented by sunil
//   public function cropdata_approved_lists()
//   {
//     //level 1 validator get pending plot list of pipe function
// 	  //Plot list
//         if(auth()->user()->hasRole('Viewer')){
//             //this condition is used when l1 user is login, to from vendor location table 
//             $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
//             $l1_state = $VendorLocation->state; 
        
//             if(!empty($VendorLocation->district)){
//               $l1_district_id = "OR final_farmers.district_id IN (".$VendorLocation->district.")";
//             }
//             if(!empty($VendorLocation->taluka)){
//               $l1_taluka_id = "OR final_farmers.taluka_id IN (".$VendorLocation->taluka.")";
//             }  
//             $condition = "AND (final_farmers.state_id IN (".$l1_state.") 
//                             ".$l1_district_id." 
//                             ".$l1_taluka_id."
//                         )";
                        
                        
//         }elseif(auth()->user()->hasRole('SuperAdmin')){
		
//             $condition="";   
//         }
//         $state_query=$district_query=$taluka_query=$panchayats_q=$village_query="";
//             //below query will be applicable when filter from above the table is used.
//             if(request()->has('state') && !empty(request('state'))){
//                 $state_query = 'AND (final_farmers.state_id = '.request('state').')';
//             }
//             if(request()->has('district') && !empty(request('district'))){
//                 $district_query = 'AND (final_farmers.district_id = '.request('district').')';
//             }
//             if(request()->has('taluka') && !empty(request('taluka'))){
//                  $taluka_query =  "AND (final_farmers.taluka_id = ".request('taluka').") ";
//             }
//             if(request()->has('panchayats') && !empty(request('panchayats'))){
//                  $panchayats_q = "AND (final_farmers.panchayat_id = ".request('panchayats').")";
//             }
//             if(request()->has('village') && !empty(request('village'))){
//                  $village_query = "AND (final_farmers.village_id = ".request('village').")";
//             }    

//             $start_date=$surveyor_id=$end_date=$season="";
//             //below query will be applicable when filter from above the table is used.
//             if(request()->has('start_date') && !empty(request('start_date'))){
//                 $start_date = "AND (farmer_cropdata.created_at >= ".request('start_date').")";
//             }
//             if(request()->has('end_date') && !empty(request('end_date'))){
//                 $end_date = "AND (farmer_cropdata.created_at <= ".request('end_date').")";
//             }
//             if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
//                 $end_date = "AND (farmer_cropdata.surveyor_id = ".request('executive_onboarding').")";
//             }
//             if(request()->has('seasons') && !empty(request('seasons'))){
//                 $season = "AND (farmer_cropdata.season = '".request('seasons')."' )";
//             }

//             // SELECT 
//             //     farmer_plot_detail.farmer_uniqueId,
//             //     farmer_plot_detail.farmer_plot_uniqueid,
//             //     farmer_plot_detail.area_acre_awd,
//             //     final_farmers.mobile,
//             //     final_farmers.state,
//             //     final_farmers.district,
//             //     final_farmers.taluka,
//             //     final_farmers.village,
//             //     final_farmers.l2_status,
//             //     final_farmers.plot_no,
//             //     final_farmers.farmer_name
//         $crop_sql = "
//             SELECT * FROM farmer_cropdata 
//             JOIN final_farmers ON farmer_cropdata.farmer_plot_uniqueid = final_farmers.farmer_plot_uniqueid
//             JOIN farmer_plot_detail ON farmer_cropdata.farmer_plot_uniqueid = farmer_plot_detail.farmer_plot_uniqueid
//             WHERE farmer_cropdata.status = 'Approved' 
//             AND (farmer_cropdata.l2_status = 'Approved')      
//                 ".$condition."          
//                 ".$state_query."
//                 ".$district_query."
//                 ".$taluka_query."
//                 ".$panchayats_q."
//                 ".$village_query."         
//                 ".$season."
//                 ".$start_date." 
//                 ".$end_date."  
//                 ".$end_date."
//             ORDER BY farmer_cropdata.id DESC;
//         ";// end of sql
//         $crop_data = DB::select($crop_sql); // This executes the raw query
//         // Paginate the results manually
//         $perPage = 25; // Number of records per page
//         $page = request()->get('page', 1); // Get the current page number from the request
//         // Calculate the starting index of records for the current page
//         $start = ($page - 1) * $perPage;
//         // Slice the array to get the records for the current page
//         $paginatedCropData = array_slice($crop_data, $start, $perPage);
//         $pagination = new LengthAwarePaginator(
//             $paginatedCropData,
//             count($crop_data),
//             $perPage,
//             $page,
//             ['path' => request()->url(), 'query' => request()->query()]
//         );

//         $path = $pagination->links()->elements;
//         $links=[];
//         foreach ($path as $key=>$item) {
//           if (is_array($item)) {
//               $links = array_merge($links, $item);
//           }
//         }

//         // FOR YAJRA DATATABLES VERSION > 8.0
//         // return datatables()->of($crop_data)->toJson();
//         // return datatables()->of($crop_data)->make(true);
	
//        // Onload below code excute first. And after successful load then again ajax make request to above code
//   		$page_title = 'Cropdata | Approved list';
//   		$page_description = 'Cropdata | Approved list';
//   		$action = 'table_farmer';
//   		//below process is for first time landing on page}
//         //for admin data
//         $states = DB::table('states')->where('status',1)->get();
//         $districts = DB::table('districts')->where('status',1)->get();
//         $talukas = DB::table('talukas')->where('status',1)->get();
//         $panchayats = DB::table('panchayats')->get();
//         $villages = DB::table('villages')->get();
//         $onboarding_executive  = DB::table('farmer_cropdata')->groupBy('farmer_cropdata.surveyor_name')->get();
//   	  $seasons = DB::table('seasons')->get();
//       $status = request()->status;
//       $others = "0";
//   	  return view('admin.l2validator.cropdata.approved-plot',compact('links','pagination','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
//   }

//   public function cropdata_approved_lists()
//   {
//     //level 1 validator get pending plot list of pipe function
// 	  //Plot list
// 	  if(request()->ajax()){
//         if(auth()->user()->hasRole('Viewer')){
//             //this condition is used when l1 user is login, to from vendor location table 
//             $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
//             $l1_state = $VendorLocation->state; 
        
//             if(!empty($VendorLocation->district)){
//               $l1_district_id = "OR final_farmers.district_id IN (".$VendorLocation->district.")";
//             }
//             if(!empty($VendorLocation->taluka)){
//               $l1_taluka_id = "OR final_farmers.taluka_id IN (".$VendorLocation->taluka.")";
//             }  
//             $condition = "AND (final_farmers.state_id IN (".$l1_state.") 
//                             ".$l1_district_id." 
//                             ".$l1_taluka_id."
//                         )";
                        
                        
//         }elseif(auth()->user()->hasRole('SuperAdmin')){
//             $condition="";   
//         }
//         $state_query=$district_query=$taluka_query=$panchayats_q=$village_query="";
//             //below query will be applicable when filter from above the table is used.
//             if(request()->has('state') && !empty(request('state'))){
//                 $state_query = 'AND (final_farmers.state_id = '.request('state').')';
//             }
//             if(request()->has('district') && !empty(request('district'))){
//                 $district_query = 'AND (final_farmers.district_id = '.request('district').')';
//             }
//             if(request()->has('taluka') && !empty(request('taluka'))){
//                  $taluka_query =  "AND (final_farmers.taluka_id = ".request('taluka').") ";
//             }
//             if(request()->has('panchayats') && !empty(request('panchayats'))){
//                  $panchayats_q = "AND (final_farmers.panchayat_id = ".request('panchayats').")";
//             }
//             if(request()->has('village') && !empty(request('village'))){
//                  $village_query = "AND (final_farmers.village_id = ".request('village').")";
//             }    


//             $start_date=$surveyor_id=$end_date=$season="";
//             //below query will be applicable when filter from above the table is used.
//             if(request()->has('start_date') && !empty(request('start_date'))){
//                 $start_date = "AND (farmer_cropdata.created_at >= ".request('start_date').")";
//             }
//             if(request()->has('end_date') && !empty(request('end_date'))){
//                 $end_date = "AND (farmer_cropdata.created_at <= ".request('end_date').")";
//             }
//             if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
//                 $end_date = "AND (farmer_cropdata.surveyor_id = ".request('executive_onboarding').")";
//             }
//             if(request()->has('seasons') && !empty(request('seasons'))){
//                 $season = "AND (farmer_cropdata.season = '".request('seasons')."' )";
//             }

//             // SELECT 
//             //     farmer_plot_detail.farmer_uniqueId,
//             //     farmer_plot_detail.farmer_plot_uniqueid,
//             //     farmer_plot_detail.area_acre_awd,
//             //     final_farmers.mobile,
//             //     final_farmers.state,
//             //     final_farmers.district,
//             //     final_farmers.taluka,
//             //     final_farmers.village,
//             //     final_farmers.l2_status,
//             //     final_farmers.plot_no,
//             //     final_farmers.farmer_name
//         $crop_sql = "
//             SELECT * FROM farmer_cropdata 
//             JOIN final_farmers ON farmer_cropdata.farmer_plot_uniqueid = final_farmers.farmer_plot_uniqueid
//             JOIN farmer_plot_detail ON farmer_cropdata.farmer_plot_uniqueid = farmer_plot_detail.farmer_plot_uniqueid
//             WHERE farmer_cropdata.status = 'Approved' 
//             AND (farmer_cropdata.l2_status = 'Approved' )      
//                 ".$condition."          
//                 ".$state_query."
//                 ".$district_query."
//                 ".$taluka_query."
//                 ".$panchayats_q."
//                 ".$village_query."         

//                 ".$season."
//                 ".$start_date." 
//                 ".$end_date."  
//                 ".$end_date."
//             ORDER BY farmer_cropdata.id DESC;
//         ";// end of sql

//         $crop_sql = strtr($crop_sql, array("\r\n" => "","\r" => "","\n" => "","\t" => " "));
//         $crop_data  = DB::select($crop_sql)->paginate(50);

// // dd( $crop_data);

//         // FOR YAJRA DATATABLES VERSION > 8.0
//         return datatables()->of($crop_data)->toJson();
//         return datatables()->of($crop_data)->make(true);
// 	  }//end layoutout plot WITH AJAX
//        // Onload below code excute first. And after successful load then again ajax make request to above code
//   		$page_title = 'Cropdata | Approved list';
//   		$page_description = 'Cropdata | Approved list';
//   		$action = 'table_farmer';
//   		//below process is for first time landing on page}
//         //for admin data
//         $states = DB::table('states')->where('status',1)->get();
//         $districts = DB::table('districts')->where('status',1)->get();
//         $talukas = DB::table('talukas')->where('status',1)->get();
//         $panchayats = DB::table('panchayats')->get();
//         $villages = DB::table('villages')->get();
//         $onboarding_executive  = DB::table('farmer_cropdata')->groupBy('farmer_cropdata.surveyor_name')->get();

//   	  $seasons = DB::table('seasons')->get();
//       $status = request()->status;
//       $others = "0";
//   	  return view('admin.l2validator.cropdata.approved-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
//   }

  public function cropdata_approved_detail($rolename, $plotuniqueid,$plot_no){
   // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');

//    $plot = FarmerPlot::where('farmer_plot_uniqueid',$plotuniqueid)->first();

   $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
//    dd($plot);
   $farmerplots =  FinalFarmer::where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid)->get();
   $farmerplots_area =  FinalFarmer::where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid)->get();
   $plot_areas_sum = 0; // Initialize the sum variable
   foreach ($farmerplots_area as $plot_area) {
       $plot_area_value = floatval($plot_area->plot_area);
       $plot_areas_sum += $plot_area_value; 
   }
   $seasons = Season::select('id','name')->where('status',1)->get();

   $cropdata = FarmerCropdata::where('farmer_plot_uniqueid',$plotuniqueid)->get();
  

   $CropvarietyList =  Cropvariety::select('id','name','state_id' )->where('state_id',$plot->state_id)->get();

   $validation_list = CropDataValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
   $crop_data_detail = FarmerCropdata::where('farmer_plot_uniqueid',$plotuniqueid)->first();

   $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
   $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
   $reject_module = RejectModule::where('type','Aeration')->get();

   $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
   
   $page_title = 'Cropdata | Approved detail';
   $page_description = 'Cropdata | Approved detail';
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
     return view('admin.l2validator.cropdata.approved-cropdata-detail',compact('plot','page_title','page_description','action','farmerplots',
     'reject_module','farmerbenefitimg','updated_polygon','cropdata','check_pipedata','seasons','CropvarietyList','crop_data_detail','validation_list','rolename','plot_areas_sum'));
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

         if(request('type') == 'CropData'){
            $filename = 'Farmers-CropData_'.Carbon::now().'.xlsx';
            // return Excel::download(new L2CropdataExport('All',request()), $filename);
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
        $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
        // $state_id = DB::table('final_farmers')->where('farmer_uniqueId', $unique_id)->first();
        return Excel::download(new L2CropDataIndividualExport($type, $unique_id, $plot_no, $status), $filename);
     }

}
