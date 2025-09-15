<?php

namespace App\Http\Controllers\Admin\l2validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\AerationValidation;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
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
use App\Models\CropdataDetail;
use App\Models\FarmerPlot;
use DateTime;

class L2CropDataValidationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search($status)
    {
        if($status == 'Pending'){
                $Farmers = FarmerCropdata::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmerapproved',function($q) use($status){

                });
                return response()->json($Farmers->get());

            }else{
                $Farmers = FarmerCropdata::with('farmerapproved')->where('status','Approved')->where('l2_status',$status)->where('l2_apprv_reject_user_id',auth()->user()->id)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmerapproved',function($q) use($status){

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
    // dd('bdhb');
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	//   if(request()->ajax()){
  		$plots_paginate = FarmerCropdata::with('farmerapproved')->where('l2_status','Pending')->whereHas('farmerapproved',function($q){
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
        ->orderBy('id','desc')->paginate(100);

        $path = $plots_paginate->links()->elements;
                        $links=[];
                        foreach ($path as $key=>$item) {
                          if (is_array($item)) {
                              $links = array_merge($links, $item);
                          }
                        }
  		// return datatables()->of($plots)->make(true);
	//   }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Cropdata | Pending list';
  		$page_description = 'Cropdata | Pending list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        // $onboarding_executive  = DB::table('final_farmers')->where('onboarding_form','1')->groupBy('final_farmers.surveyor_name')
        //                     ->join('farmer_plot_detail', 'final_farmers.id' ,'=','farmer_plot_detail.farmer_id')
        //                     ->where('farmer_plot_detail.final_status', '=','Pending')
        //                     ->get();
        $onboarding_executive = [];

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.cropdata.pending-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function cropdata_pending_detail($plotuniqueid,$plot_no){
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    // $plot_detail=FarmerPlot::with('final_farmerno')->findOrFail($plotuniqueid);

    $seasons = Season::select('id','name')->where('status',1)->get();

    $crop_data = FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();


    $crop_data_detail = FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $cropdata=FarmerCropdata::with('PlotCropDetails' ,'farmerplot_details')->where('farmer_plot_uniqueid',$plotuniqueid)->get();

    $dt_irrigation_last = DateTime::createFromFormat('d/m/Y', $crop_data_detail->dt_irrigation_last);
    $crop_data_detail->dt_irrigation_last =  $dt_irrigation_last->format('Y-m-d');

    $dt_ploughing = DateTime::createFromFormat('d/m/Y', $crop_data_detail->dt_ploughing);
    $crop_data_detail->dt_ploughing =  $dt_ploughing->format('Y-m-d');

    $dt_transplanting = DateTime::createFromFormat('d/m/Y', $crop_data_detail->dt_transplanting);
    $crop_data_detail->dt_transplanting =  $dt_transplanting->format('Y-m-d');

    $nursery = DateTime::createFromFormat('d/m/Y', $crop_data_detail->PlotCropDetails->nursery);
    $crop_data_detail->PlotCropDetails->nursery =  $nursery->format('Y-m-d');

    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();


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
    return view('l2validator.cropdata.pending-plot-crop',compact('plot','page_title','page_description','cropdata','action','farmerplots','farmerplot',
                'reject_module','farmerbenefitimg','updated_polygon','crop_data','check_pipedata','seasons',
                'CropvarietyList','crop_data_detail','validation_list','date_setting'));
  }

    public function cropdata_pending_update(Request $request, $unique_id){
        // try {
            $cropdata_detail = FarmerCropdata::where('farmer_plot_uniqueid', $unique_id)->first();
            $dt_irrigation_last = Carbon::parse($request->dt_irrigation_last)->format('d/m/Y');
            $dt_ploughing = Carbon::parse($request->dt_ploughing)->format('d/m/Y');
            $dt_transplanting = Carbon::parse($request->dt_transplanting)->format('d/m/Y');
            $nursery = Carbon::parse($request->nursery)->format('d/m/Y');
            
            $cropdata = FarmerCropdata::where('farmer_plot_uniqueid', $unique_id)->update([
                "crop_variety" =>$request->crop_variety,
                'dt_irrigation_last'   => $dt_irrigation_last,
                'dt_ploughing'   => $dt_ploughing,
                'dt_transplanting'   => $dt_transplanting,   
            ]);
            $CropdataDetail = CropdataDetail::where('farmer_cropdata_id', $cropdata_detail->id)->update([
                'nursery' => $nursery,
                'crop_season_lastyrs' => $request->crop_season_lastyrs,
                'crop_season_currentyrs' => $request->crop_season_currentyrs,
                'crop_variety_lastyrs' => $request->crop_variety_lastyrs,
                'crop_variety_currentyrs' => $request->crop_variety_currentyrs   ,
                'fertilizer_1_name' => $request->fertilizer_1_name  ,
                'fertilizer_1_lastyrs' => $request->fertilizer_1_lastyrs   ,
                'fertilizer_1_currentyrs' => $request->fertilizer_1_currentyrs   ,
                'fertilizer_2_name' => $request->fertilizer_2_name    ,
                'fertilizer_2_lastyrs' => $request->fertilizer_2_lastyrs    ,
                'fertilizer_2_currentyrs' => $request->fertilizer_2_currentyrs    ,
                'fertilizer_3_name' => $request->fertilizer_3_name    ,
                'fertilizer_3_lastyrs' => $request->fertilizer_3_lastyrs    ,
                'fertilizer_3_currentyrs' => $request->fertilizer_3_currentyrs    ,
                'water_mng_lastyrs' =>$request->water_mng_lastyrs,
                'water_mng_currentyrs' => $request->water_mng_currentyrs    ,
                'yeild_lastyrs' => $request->yeild_lastyrs    ,
                'yeild_currentyrs' => $request->yeild_currentyrs    ,
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
        //   } catch (\Exception $e) {
        //     return response()->json(['error' => true, 'message' => 'Something went wrong'],500);

        //   }
    }

    /**
       * Approve or Reject farmer status.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function bulk_approval(Request $request){
         try {

            foreach($request->farmer_unique_id_array as $item){
                    $crop_detail = DB::table('farmer_cropdata')->where('farmer_plot_uniqueid',$item['farmer_unique_id'])->first();
                    $cropupdate =  FarmerCropdata::where('farmer_plot_uniqueid',$item['farmer_unique_id'])->update([
                                                "l2_status" => "Approved",
                                                'l2_apprv_reject_user_id' => auth()->user()->id,
                                                ]);

                        //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
                        $cropdata_validation  = DB::table('cropdata_validation')->insert([
                        // 'farmer_uniqueId'           => $cropdata->farmer_uniqueId,
                        'farmer_plot_uniqueid'      => $item['farmer_unique_id'],
                        'plot_no'                   => $crop_detail->plot_no,
                        'status'                    => 'Approved',
                        'level'                     => 'L-2-Validator',
                        'user_id'                   => auth()->user()->id,
                        'comment'                   => 'BULK APPROVED',
                        'reject_reason_id'          => NULL,
                        'timestamp'                 => Carbon::now(),
                        'created_at'                => Carbon::now(),
                        'updated_at'                => Carbon::now(),
                        ]);
                        //also keep record for validation
                        $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           =>  $crop_detail->farmer_uniqueId,
                        'plot_no'                   =>  $crop_detail->plot_no,
                        'farmer_plot_uniqueid'      =>  $item['farmer_unique_id'],
                        'level'                     => 'L-2-Validator',
                        'status'                    => 'Approved',
                        'module'                    => 'Cropdata-'.$crop_detail->plot_no,
                        'comment'                   => "Cropdata Approved:. BULK APPROVED",
                        'timestamp'                 => Carbon::now(),
                        'user_id'                   => auth()->user()->id,
                        'reject_reason_id'          => NULL,
                        ]);
            }
            return response()->json(['success' =>true],200);
          } catch (\Exception $e) {
                return response()->json(['error' =>true, 'message'=>$e->getMessage()],500);
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
                if($cropdata->l2_status == 'Approved'){
                    return response()->json(['error' =>true,  'message'=>'Already Approve'],200);
                }
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
	//   if(request()->ajax()){
        $plots_paginate = FarmerCropdata::with('farmerapproved')->where('status','Rejected')->whereHas('farmerapproved',function($q){
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
        ->orderBy('id','desc')->paginate(100);
        $path = $plots_paginate->links()->elements;
                        $links=[];
                        foreach ($path as $key=>$item) {
                          if (is_array($item)) {
                              $links = array_merge($links, $item);
                          }
                        }
        // return datatables()->of($plots)->make(true);
        // }//end layoutout plot WITH AJAX
        // Onload below code excute first. And after successful load then again ajax make request to above code
            $page_title = 'Cropdata | Reject list';
            $page_description = 'Cropdata | Reject list';
            $action = 'table_farmer';
            //below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        $onboarding_executive  = DB::table('final_farmers')->where('onboarding_form','1')->groupBy('farmers.surveyor_name')
                            ->join('farmer_plot_detail', 'farmers.id' ,'=','farmer_plot_detail.farmer_id')
                            ->where('farmer_plot_detail.final_status', '=','Pending')
                            ->get();

        $seasons = DB::table('seasons')->get();
        $status = request()->status;
        $others = "0";
      return view('l1validator.cropdata.reject-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
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

   /**
   * level 1 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function cropdata_approved_lists()
  {
    // dd('sdxdhg');
    //level 1 validator get pending plot list of pipe function
	  //Plot list
	//   if(request()->ajax()){
  		$plots_paginate = FarmerCropdata::with('farmerapproved')->whereHas('farmerapproved',function($q){
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
        ->orderBy('id','desc')->paginate(100);

        $path = $plots_paginate->links()->elements;
        $links=[];
        foreach ($path as $key=>$item) {
          if (is_array($item)) {
              $links = array_merge($links, $item);
          }
        }
  		// return datatables()->of($plots)->make(true);
	//   }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Cropdata | Approved list';
  		$page_description = 'Cropdata | Approved list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        // $onboarding_executive  = DB::table('final_farmers')->where('onboarding_form','1')->groupBy('final_farmers.surveyour_id')
        //                     ->join('farmer_plot_detail', 'final_farmers.farmer_uniqueId' ,'=','farmer_plot_detail.farmer_uniqueId')
        //                     ->where('farmer_plot_detail.final_status', '=','Pending')
        //                     ->get();
        $onboarding_executive = [];

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.cropdata.approved-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }




  public function cropdata_approved_detail($plotuniqueid,$plot_no){
   // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
   $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
   $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
   $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();

   $seasons = Season::select('id','name')->where('status',1)->get();

   $crop_data = FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();
// dd($crop_data );
$farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

   $CropvarietyList =  Cropvariety::select('id','name','state_id' )->where('state_id',$plot->state_id)->get();

   $validation_list = CropDataValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
   $crop_data_detail = FarmerCropdata::where('farmer_plot_uniqueid',$plotuniqueid)->first();

   $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
   $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
   $reject_module = RejectModule::where('type','Aeration')->get();

   $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
   $cropdata=FarmerCropdata::with('PlotCropDetails' ,'farmerplot_details')->where('farmer_plot_uniqueid',$plotuniqueid)->get();

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
     return view('l2validator.cropdata.approved-cropdata-detail',compact('plot','page_title','cropdata','page_description','action','farmerplots','farmerplot',
     'reject_module','farmerbenefitimg','updated_polygon','crop_data','check_pipedata','seasons','CropvarietyList','crop_data_detail','validation_list'));
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
