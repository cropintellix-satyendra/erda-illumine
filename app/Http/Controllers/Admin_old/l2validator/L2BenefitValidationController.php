<?php

namespace App\Http\Controllers\Admin\l2validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\FarmerCropdata;
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
    public function search($status)
    {
        if($status == 'Pending'){
                $Farmers = FarmerBenefit::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmerapproved',function($q) use($status){

                });
                return response()->json($Farmers->get());

            }else{//for approve
                $Farmers = FarmerBenefit::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('l2_apprv_reject_user_id',auth()->user()->id)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmerapproved',function($q) use($status){

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
  		$plots = FarmerBenefit::with('farmerapproved')->where('status','Approved')->where('l2_status','Pending')->whereHas('farmerapproved',function($q){
            if(auth()->user()->hasRole('L-2-Validator')){
                $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                $q->whereIn('state_id',explode(',',$VendorLocation->state));
                if(!empty($VendorLocation->district)){
                $q->whereIn('district_id',explode(',',$VendorLocation->district));
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
  		$page_title = 'benefit | Pending list';
  		$page_description = 'List of pending benefit';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
          $onboarding_executive  = DB::table('farmer_benefits')->groupBy('farmer_benefits.surveyor_name')->get();

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.benefit.pending-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function benefit_pending_detail($plotuniqueid){
    $plot = FinalFarmer::with('PlotCropData')->where('farmer_uniqueId',$plotuniqueid)->first();
    // dd($plot);
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();

    $seasons = Season::select('id','name')->where('status',1)->get();

    $benefit_data = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->get();
    $benefit_data_detail = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->first();

    $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();
// dd($cropdata);
    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    // dd($farmerplot);

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
    return view('l2validator.benefit.pending-plot-benefit',compact('plot','cropdata','farmerplot','page_title','page_description','action','farmerplots',
                'farmerbenefitimg','updated_polygon','seasons','benefit_data_detail','benefit_data','check_pipedata','validation_list'));
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
            if(auth()->user()->hasRole('L-2-Validator')){
                $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                $q->whereIn('state_id',explode(',',$VendorLocation->state));
                if(!empty($VendorLocation->district)){
                $q->whereIn('district_id',explode(',',$VendorLocation->district));
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
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
          $onboarding_executive  = DB::table('farmer_benefits')->groupBy('farmer_benefits.surveyor_name')->get();
        $seasons = DB::table('seasons')->get();
        $status = request()->status;
        $others = "0";
      return view('l2validator.benefit.reject-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
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
     return view('l2validator.benefit.reject-plot-awd',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
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
  		$plots = FarmerBenefit::with('farmerapproved')->where('status','Approved')->where('l2_status','Approved')->where('l2_apprv_reject_user_id',auth()->user()->id)->whereHas('farmerapproved',function($q){
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
            if(auth()->user()->hasRole('L-2-Validator')){
                $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                $q->whereIn('state_id',explode(',',$VendorLocation->state));
                if(!empty($VendorLocation->district)){
                $q->whereIn('district_id',explode(',',$VendorLocation->district));
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
  		$page_title = 'Benefit | Approved list';
  		$page_description = 'List of Approved plots';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
          $onboarding_executive  = DB::table('farmer_benefits')->groupBy('farmer_benefits.surveyor_name')->get();

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.benefit.approved-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }

  public function benefit_approved_detail($plotuniqueid){
    $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_uniqueId',$plotuniqueid)->first();
    // dd($plot);
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();

    $seasons = Season::select('id','name')->where('status',1)->get();

    $benefit_data = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->get();
    $benefit_data_detail = FarmerBenefit::where('farmer_uniqueId',$plotuniqueid)->first();

    $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();

    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

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
    return view('l2validator.benefit.approved-benefit-detail',compact('plot','page_title','cropdata','farmerplot','page_description','action','farmerplots',
                'farmerbenefitimg','updated_polygon','seasons','benefit_data_detail','benefit_data','check_pipedata','validation_list'));
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
