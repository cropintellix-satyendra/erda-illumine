<?php

namespace App\Http\Controllers\Admin\Account\l2validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\Season;
use App\Models\PlotStatusRecord;
use App\Models\FarmerBenefit;
use App\Models\Cropvariety;
use App\Models\Setting;
use App\Models\BenefitDataValidation;
use DateTime;
use App\Exports\L2BenefitExport;
use App\Exports\L2BenefitDataIndividualExport;

class L2BenefitValidationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search($role, $status)
    {
        if($status == 'Pending'){
                $Farmers = FarmerBenefit::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)
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

            }else{//for approve
                $Farmers = FarmerBenefit::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)
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
  public function benefit_pending_lists()
  {
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	  if(request()->ajax()){
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
                $start_date = "AND (farmer_benefits.created_at >= ".request('start_date').")";
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $end_date = "AND (farmer_benefits.created_at <= ".request('end_date').")";
            }
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $end_date = "AND (farmer_benefits.surveyor_id = ".request('executive_onboarding').")";
            }
            if(request()->has('seasons') && !empty(request('seasons'))){
                $season = "AND (farmer_benefits.season = '".request('seasons')."' )";
            }

        $benefit_sql = "
            SELECT * FROM farmer_benefits 
            JOIN final_farmers ON farmer_benefits.farmer_uniqueId = final_farmers.farmer_uniqueId
            WHERE farmer_benefits.status = 'Approved' 
            AND farmer_benefits.deleted_at IS NULL
            AND (farmer_benefits.l2_status = 'Pending' ) 
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
            ORDER BY farmer_benefits.id DESC;
        ";// end of sql

        $benefit_sql = strtr($benefit_sql, array("\r\n" => "","\r" => "","\n" => "","\t" => " "));
        $benefit_data  = DB::select($benefit_sql);

        // FOR YAJRA DATATABLES VERSION > 8.0
        return datatables()->of($benefit_data)->toJson();
        return datatables()->of($benefit_data)->make(true);
	  }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'benefit | Pending list';
  		$page_description = 'List of pending benefit';
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
  	  return view('admin.l2validator.benefit.pending-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function benefit_pending_detail($rolename, $plotuniqueid){
    $plot = FinalFarmer::where('farmer_uniqueId',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();

    $seasons = Season::select('id','name')->where('status',1)->get();

    $benefit_data = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->get();
    $benefit_data_detail = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->first();
   


    $validation_list = BenefitDataValidation::where('farmer_uniqueId',$plotuniqueid)->where('level','L-2-Validator')->get();
    

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    
    $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
   

    $page_title = 'Benefit | Pending Detail';
    $page_description = 'Benefit';
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
    return view('admin.l2validator.benefit.pending-plot-benefit',compact('plot','page_title','page_description','action','farmerplots',
                'farmerbenefitimg','updated_polygon','seasons','benefit_data_detail','benefit_data','check_pipedata','validation_list','rolename'));
  }

    public function cropdata_pending_update(Request $request, $unique_id){
        try {
            $dt_irrigation_last = Carbon::parse($request->dt_irrigation_last)->format('d/m/Y');
            $dt_ploughing = Carbon::parse($request->dt_ploughing)->format('d/m/Y');
            $dt_transplanting = Carbon::parse($request->dt_transplanting)->format('d/m/Y');
            $cropdata = FarmerBenefit::where('farmer_plot_uniqueid', $unique_id)->update([
                "crop_variety" =>$request->crop_variety,
                'dt_irrigation_last'   => $dt_irrigation_last,
                'dt_ploughing'   => $dt_ploughing,
                'dt_transplanting'   => $dt_transplanting,
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
      public function benefit_validation(Request $request,$UniqueId){

                    $benefit = FarmerBenefit::where('farmer_uniqueId',$UniqueId)->first();

          
                    foreach($request->uniqueids as $data){

                        $benefitdate =  FarmerBenefit::where('farmer_uniqueId',$UniqueId)->update([
                                                                                "l2_status" => "Approved",  
                                                                                'l2_apprv_reject_user_id' => auth()->user()->id,                                                                               
                                                                                ]);

                        //to store record of pipe vlidation of approval and rejection. This is specifically for pipe                                                                                     
                        $benefit_validation  = DB::table('benefitdata_validation')->insert([
                            'farmer_uniqueId'           => $UniqueId,
                            'status'                    => 'Approved',
                            'benefit_id'                => $benefit->benefit_id,
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
                            'farmer_uniqueId'           => $benefit->farmer_uniqueId,
                            'plot_no'                   => NULL,
                            'farmer_plot_uniqueid'      => NULL,
                            'level'                     => 'L-2-Validator',
                            'status'                    => 'Approved',
                            'module'                    => 'Benefit-Approval',
                            'comment'                   => "Benefit Approved:. ".$data['ApproveComment'],
                            'timestamp'                 => Carbon::now(),
                            'user_id'                   => auth()->user()->id,
                            'reject_reason_id'          => NULL,
                        ]); 
                    
                    }//foreach end
                    return response()->json(['success' =>true, 'benefit_data'=>$benefit],200); 
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
        $plots = FarmerBenefit::with('farmerapproved')->where('status','Rejected')->whereHas('farmerapproved',function($q){
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
      return view('admin.l2validator.benefit.reject-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
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
     $validation_list = CropDataValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('status','Rejected')->where('level','L-1-Validator')->get();
     $Polygon = json_decode($PipeInstallation->ranges);
  //   foreach($ploygon as $latlng){
  //       dd($latlng);
  //   }
  //   $Polygon =
 
     $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
     $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
     $reject_module = RejectModule::where('type','Aeration')->get();
 
     $awd_data=FarmerBenefit::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->first();
     $awd=FarmerBenefit::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
     $AwdImage = AerationImage::where('farmer_plot_uniqueid',$plotuniqueid)->where('aeration_no',$aeration_no)->where('pipe_no',$pipe_no)->get();
 
     $page_title = 'Cropdata | Reject list';
     $page_description = 'Cropdata';
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
     return view('admin.l2validator.benefit.reject-plot-awd',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                 'reject_module','farmerbenefitimg','updated_polygon','validation_list','awd','AwdImage','awd_data'));
  }

   /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function benefit_approved_lists()
  {
	  //Plot list
	  if(request()->ajax()){  		
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
                $start_date = "AND (farmer_benefits.created_at >= ".request('start_date').")";
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $end_date = "AND (farmer_benefits.created_at <= ".request('end_date').")";
            }
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $end_date = "AND (farmer_benefits.surveyor_id = ".request('executive_onboarding').")";
            }
            if(request()->has('seasons') && !empty(request('seasons'))){
                $season = "AND (farmer_benefits.season = '".request('seasons')."' )";
            }

        $benefit_sql = "
            SELECT * FROM farmer_benefits 
            JOIN final_farmers ON farmer_benefits.farmer_uniqueId = final_farmers.farmer_uniqueId
            WHERE farmer_benefits.status = 'Approved' 
             AND farmer_benefits.deleted_at IS NULL
            AND (farmer_benefits.l2_status = 'Approved' ) 
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
            ORDER BY farmer_benefits.id DESC;
        ";// end of sql

        $benefit_sql = strtr($benefit_sql, array("\r\n" => "","\r" => "","\n" => "","\t" => " "));
        $benefit_data  = DB::select($benefit_sql);

        // FOR YAJRA DATATABLES VERSION > 8.0
        return datatables()->of($benefit_data)->toJson();
        return datatables()->of($benefit_data)->make(true);
	  }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Benefit | Approved list';
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
  	  return view('admin.l2validator.benefit.approved-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }

  public function benefit_approved_detail($rolename, $plotuniqueid){
    $plot = FinalFarmer::where('farmer_uniqueId',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();

    $seasons = Season::select('id','name')->where('status',1)->get();

    $benefit_data = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->get();
    $benefit_data_detail = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->first();
   


    $validation_list = BenefitDataValidation::where('farmer_uniqueId',$plotuniqueid)->where('level','L-2-Validator')->get();
    

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    
    $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
   

    $page_title = 'Benefit | Pending Detail';
    $page_description = 'Benefit';
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
    return view('admin.l2validator.benefit.approved-benefit-detail',compact('plot','page_title','page_description','action','farmerplots',
                'farmerbenefitimg','updated_polygon','seasons','benefit_data_detail','benefit_data','check_pipedata','validation_list','rolename'));
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
         if(request('type') == 'BenefitData'){
            $filename = 'L2_BenefitData'.request('status').'_'.Carbon::now().'.xlsx';
            // return Excel::download(new L2BenefitExport('All',request()), $filename);
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
      public function excel_download($type, $unique_id, $status){
        $filename = 'L2_Benefit_'.$unique_id.'_'.Carbon::now().'.xlsx';
        return Excel::download(new L2BenefitDataIndividualExport($type, $unique_id, $status), $filename);
     }

}
