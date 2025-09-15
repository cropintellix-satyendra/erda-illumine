<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Farmer;
use App\Models\CropdataDetail;
use App\Models\FarmerPlot;
use App\Models\FarmerPlotImage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Cropvariety;
use App\Models\FarmerCropdata;
use App\Models\Fertilizer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\FinalFarmer;
use App\Models\PlotStatusRecord;
use App\Models\Polygon;
use App\Models\UserTarget;
use Exception;

class CropdataController extends Controller
{

   /**
   * Display a listing of the resource through api response.
   *
   * @return \Illuminate\Http\Response
   */
  public function cropdata_settings(){
    try{
     $value = DB::table('settings')->where('id','1')->select('preparation_date_interval','transplantation_date_interval','cropdata_end_days')->first();
      if(!$value){
        return response()->json(['error'=>true,'message'=>'No record'],200);
      }
      return response()->json(['success'=>true,'setting'=>$value],200);
    }catch(Exception $e){
      return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
    }
  }


  /**
   * Display a listing of the resource through api response.
   *
   * @return \Illuminate\Http\Response
   */
  public function plotuniqueid_list(Request $request){
    try{
      $list = FinalFarmer::where('onboard_completed', '!=', "Processing")
            ->select('id','farmer_uniqueId')
      //here surveyor can search from various fields
                        ->where('mobile','like','%'.$request->data.'%')
                        ->orWhere('document_no','like','%'.$request->data.'%')
                        ->orWhere('farmer_uniqueId','like','%'.$request->data.'%')
                        ->orWhere('farmer_survey_id','like','%'.$request->data.'%')
                        ->where('onboarding_form','1')->orderBy('id','DESC')
                        ->groupBy('farmer_uniqueId')
                        ->get();
      if(!$list){
        return response()->json(['error'=>true,'message'=>'No record'],200);
      }
      return response()->json(['success'=>true,'list'=>$list],200);
    }catch(Exception $e){
      return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
    }
  }



  public function plotuniqueid_list_new(Request $request){
    try {
        $searchType = $request->search_type; 

        // Start building the query
        $query = FinalFarmer::where('onboard_completed', '!=', 'Processing')
            ->where('onboarding_form', '1');

        // Based on search type, adjust the search fields
        switch ($searchType) {
            case 1:
                $query->where('mobile',$request->data);
                break;
            case 2:
                $query->where('aadhaar',$request->data);
                break;
            case 3:
                $query->where('farmer_uniqueId',$request->data);
                break;
            case 4:
                $query->where('farmer_survey_id',$request->data);
                break;
            default:
                return response()->json(['error' => true, 'message' => 'Invalid search type'], 400);
        }

        // Execute the query
        $list = $query->orderBy('id', 'DESC')
            ->groupBy('farmer_uniqueId')
            ->select('id', 'farmer_uniqueId')
            ->get();

        if ($list->isEmpty()) {
            return response()->json(['error' => true, 'message' => 'No record'], 422);
        }

        return response()->json(['success' => true, 'list' => $list], 200);
    } catch (Exception $e) {
        return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
    }
}

  /**
     * Display a listing of the resource through api response.
     *
     * @return \Illuminate\Http\Response
     */
    public function subplots_list(Request $request){
      try{
        $plotlist = FinalFarmer::with('ApprvFarmerPlot:farmer_plot_uniqueid,area_in_acers,area_in_other,area_in_other_unit,area_acre_awd,area_other_awd,area_other_awd_unit')
                                ->select('id','farmer_plot_uniqueid','farmer_uniqueId','plot_no','area_in_acers','state_id','available_area','plot_area')
                                ->where('farmer_uniqueId',$request->farmer_uniqueId)
                                ->orderBy('plot_no','asc')->get();
        $available_area = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',1)->first();

        
        foreach ($plotlist as $plot) {

          if ($plot->ApprvFarmerPlot === null) {
              return response()->json(['message' => 'No plot found! Please update Plot'], 422);
          }
         }
                                    
        // $guntha = 0.025000;
        // foreach($plotlist as $plot){
        //     if($plot->state_id == 36){
        //         //   $area = number_format((float)$plot->area_in_acers, 2, '.', '');
        //         //   $split = explode('.', $area);//spliting area
        //         //   $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
        //         //   $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
        //         //   $conversion = explode('.', $result); // split result
        //         //   $conversion = $conversion[1]??0;
        //         //   $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
        //         //   $plot->area_in_acers = $acers;
        //         // //   $plot->area_acre_awd    =   $plot->area_in_other;
        //     }elseif($plot->state_id == 29){
        //         // $plot->ApprvFarmerPlot->area_acre_awd    =   $plot->ApprvFarmerPlot->area_in_other;
        //     }
        // }
        return response()->json(['success'=>true,'plotlist'=>$plotlist, 'available_area' => $available_area->available_area, ],200);
      }catch(Exception $e){
        return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
      }
    }
    
//       public function subplots_list(Request $request){
//  try {
      
  
//     $plotlist = DB::table('farmer_plot_detail')->select('id','plot_no',
// 'farmer_plot_uniqueid','area_acre_awd')->where('farmer_uniqueId',$request->farmer_uniqueId)->get();
   

//     return response()->json(["plotlist"=>$plotlist], 200);
// } catch (Exception $e) {
//     return response()->json(['error' => true, 'message' => 'Something Went wrong'], 500);
// }
//     }

    
    /**
     * for cropdata
     *
     * @return \Illuminate\Http\Response
     */
    public function crop_subplots_list(Request $request){
      try{
        $plotlist = FinalFarmer::with('ApprvFarmerPlot:farmer_plot_uniqueid,area_in_acers,area_in_other,area_in_other_unit,area_acre_awd,area_other_awd,area_other_awd_unit,area_in_acers,area_in_other,area_in_other_unit,area_acre_awd,area_other_awd,area_other_awd_unit,patta_number,daag_number,khatha_number,pattadhar_number,khatian_number')
                                ->select('id','farmer_plot_uniqueid','farmer_uniqueId','plot_no','state_id','organization_id','document_no','gender','mobile')
                                ->where('farmer_uniqueId',$request->farmer_uniqueId)
                                ->orderBy('plot_no','asc')->get();
        //below code is for checking data which is missing, specially for migrated data to database of westbengal, assam.

        foreach ($plotlist as $plot) {

          if ($plot->ApprvFarmerPlot === null) {
              return response()->json(['message' => 'No plot found! Please update Plot'], 422);
          }
          }   

        $plotlist =$plotlist->map(function($q){
          //here check if null then sending 1 to key "update_data" this will then open update screen in app to update data
          if(!$q->organization_id || !$q->document_no || !$q->mobile ||!$q->gender || !$q->gender){
            $q->update_data = '1';
          }else{
            //if all data is filled then 0 is being send update_data
            $q->update_data = '0';
          }
          return $q;
        });  
                         
        
        return response()->json(['success'=>true,'plotlist'=>$plotlist],200);
      }catch(\Exception $e){
        return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
      }
    }

    /**
     * Display a listing of the resource through api response.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetch_plot_detail(Request $request){
      try{
        $farmer = DB::table('final_farmers')->select('id','farmer_name','mobile','no_of_plots','state_id','date_survey','organization_id','document_no','gender','mobile')
                        ->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();

        $plot_data = DB::table('farmer_plot_detail')
                      ->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();

        $CropDetail  = FarmerCropdata::with('PlotCropDetails','farmerapproved')->select('id','farmer_id','farmer_uniqueId','plot_no','season','dt_irrigation_last','crop_variety','dt_ploughing','dt_transplanting')
                           ->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
        $state = DB::table('states')->where('id',$farmer->state_id)->first();
        if($CropDetail){
            return response()->json(['success'=>true,'farmer'=>$farmer, 'plotdetail'=> $plot_data ,'cropdetail'=>$CropDetail,'state'=>$state],200);
        }else{
            return response()->json(['error'=>true,'farmer'=>$farmer , 'plotdetail'=> $plot_data,'cropdetail'=>'No data','state'=>$state],422);
        }
      }catch(Exception $e){
        return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
      }
    }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
   public function farmercropdata(Request $request)
  {
    $validator = Validator::make($request->all(),[
        'farmer_uniqueId' => 'required',
        'farmer_id' => 'required',
        'plot_no' => 'required',
    ]);
    try{
        $CropData = FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->first();

        $numericPart = ''; // Initialize numericPart to an empty string
        preg_match('/P(\d+)$/', $request->farmer_plot_uniqueid, $matches);
        if (isset($matches[1])) {
            $numericPart = $matches[1];
        }

        if($CropData){//prevent multiple entry of cropdata
            FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->forceDelete();
        }
         //here updatimg data to finalfarmer table and farmerplotdetail table
        //to migrated data only from here, new data has all field filled
        $farmer_data = FinalFarmer::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        // dd( $farmer_data);
        if($request->has('document_no') && !empty($request->document_no)){
          $farmer_data->document_no   = $request->document_no;
        }

        if($request->has('mobile') && !empty($request->mobile)){
          $farmer_data->mobile   = $request->mobile;
        }

        if($request->has('gender') && !empty($request->gender)){
          $farmer_data->gender   = $request->gender;
        }

        if($request->has('organization_id') && !empty($request->organization_id)){
          $farmer_data->organization_id   = $request->organization_id;  
        }

        if($request->has('area_in_acers') && !empty($request->area_in_acers)){
          $farmer_data->area_in_acers  = $request->area_in_acers;
        }

        $farmer_data->save();  

        // //here storing data regarding 
        // $plot_detail=  FarmerPlot::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        // if($request->has('area_in_acers') && !empty($request->area_in_acers)){
        //  $plot_detail->area_in_acers  =     $request->area_in_acers;
        // }

        // if($request->has('area_in_other') && !empty($request->area_in_other)){
        //   $plot_detail->area_in_other  =     $request->area_in_other;
        // }

        // if($request->has('area_in_other_unit') && !empty($request->area_in_other_unit)){
        //   $plot_detail->area_in_other_unit  =     $request->area_in_other_unit;
        // }

        // if($request->has('area_acre_awd') && !empty($request->area_acre_awd)){
        //   $plot_detail->area_acre_awd  =   $request->area_acre_awd;
        // }

        // if($request->has('area_other_awd') && !empty($request->area_other_awd)){
        //   $plot_detail->area_other_awd  =     $request->area_other_awd;
        // }

        // if($request->has('area_other_awd_unit') && !empty($request->area_other_awd_unit)){
        //   $plot_detail->area_other_awd_unit  =     $request->area_other_awd_unit;
        // }

        // if($request->has('patta_number') && !empty($request->patta_number)){
        //   $plot_detail->patta_number   = $request->patta_number;
        // }

        // if($request->has('daag_number') && !empty($request->daag_number)){
        //   $plot_detail->daag_number   = $request->daag_number; 
        // }

        // if($request->has('khatha_number') && !empty($request->khatha_number)){
        //   $plot_detail->khatha_number  = $request->khatha_number;  
        // }

        // if($request->has('pattadhar_number') && !empty($request->pattadhar_number)){
        //   $plot_detail->pattadhar_number   = $request->pattadhar_number;
        // }

        // if($request->has('khatian_number') && !empty($request->khatian_number)){
        //   $plot_detail->khatian_number   = $request->khatian_number;
        // }
        // $plot_detail->save();

        // now calculating total plot area from all plots
        $plot_area =  FarmerPlot::where('farmer_uniqueId', $request->farmer_uniqueId)->sum('area_acre_awd');
        //now updating the total area from plot and upate to final table
        FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->update(['total_plot_area'=>$plot_area, 'cropdata_form'=>1]);

        //create a record for farmer crop data
       $cropdata = new FarmerCropdata; //now storing data in new farmer_cropdata table
       $cropdata->farmer_id          = $request->farmer_id;
       $cropdata->farmer_uniqueId    = $request->farmer_uniqueId;
       $cropdata->farmer_plot_uniqueid = $request->farmer_plot_uniqueid;
       $cropdata->plot_no            =  $numericPart;
       $cropdata->area_in_acers      = $farmer_data->area_in_acers??"0.00"; // now we not store AWD area then we store total area acres instead of AWD area
       $cropdata->season             = $request->season;
       $cropdata->dt_irrigation_last = $request->dt_irrigation_last;
       $cropdata->crop_variety       = $request->crop_variety;
       $cropdata->dt_ploughing       = $request->dt_ploughing;
       $cropdata->dt_transplanting   = $request->dt_transplanting;
       $cropdata->status             = 'Approved';//'Pending';
       $cropdata->apprv_reject_user_id  = '1';
       $cropdata->surveyor_id        = auth()->user()->id;//store surveyor id
       $cropdata->surveyor_name      = auth()->user()->name;//store surveyor name
       $cropdata->surveyor_mobile    = auth()->user()->mobile;
       $cropdata->date_survey        = Carbon::parse(Carbon::now())->format('d/m/Y');
       $cropdata->date_time          = Carbon::now()->toTimeString();
       //  for some period of time we will be making approved from l2 level
       $cropdata->l2_status          = 'Approved';
       $cropdata->l2_apprv_reject_user_id          = '1';       
       $cropdata->save();
       $detail = new CropdataDetail;
       $detail->farmer_cropdata_id      = $cropdata->id;
       $detail->crop_season_lastyrs     = $request->crop_season_lastyrs;
       $detail->crop_season_currentyrs     = $request->crop_season_currentyrs;
       $detail->crop_variety_lastyrs     = $request->crop_variety_lastyrs;
       $detail->crop_variety_currentyrs     = $request->crop_variety_currentyrs;
    //   $detail->water_mng_lastyrs     = $request->water_mng_lastyrs;
    //   $detail->water_mng_currentyrs     = $request->water_mng_currentyrs;
       $detail->yeild_lastyrs     = $request->yeild_lastyrs;
       $detail->yeild_currentyrs     = $request->yeild_currentyrs;

       $detail->fertilizer_1_name          =  $request->fertilizer_1_name;
       $detail->fertilizer_1_lastyrs      = $request->fertilizer_1_lastyrs;
       $detail->fertilizer_1_currentyrs   = $request->fertilizer_1_currentyrs;

       $detail->fertilizer_2_name         = $request->fertilizer_2_name;
       $detail->fertilizer_2_lastyrs      = $request->fertilizer_2_lastyrs;
       $detail->fertilizer_2_currentyrs   = $request->fertilizer_2_currentyrs;

       $detail->fertilizer_3_name         = $request->fertilizer_3_name;
       $detail->fertilizer_3_lastyrs      = $request->fertilizer_3_lastyrs;
       $detail->fertilizer_3_currentyrs    = $request->fertilizer_3_currentyrs;
       $detail->nursery    = $request->nursery;
       $detail->save();
       $record =  PlotStatusRecord::create([
                     'farmer_uniqueId'           => $request->farmer_uniqueId,
                     'plot_no'                   => $numericPart,
                     'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
                     'level'                     => 'CropData',
                     'status'                    => 'Pending',
                     'comment'                   => 'Uploaded CropData',
                     'timestamp'                 => Carbon::now(),
                     'user_id'                   => auth()->user()->id,
                 ]);


        
                 $userTarget = UserTarget::updateOrCreate(
                    [
                        'user_id' => auth()->user()->id,
                        'module_id' => '2',
                        'date' => now()->toDateString(),
                    ],
                    [
                        'count' => DB::raw('count + 1'),
                    ]
                );
      //store 1 to final farmer table means cropdata form collemeted
      FinalFarmer::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update(['cropdata_form'=>1]);
      if(!$cropdata){
        return response()->json(['error' => true, 'message'=>'Sometime went wrong'],500);
      }
      return response()->json(['success' => true, 'farmerid' => $cropdata->farmer_id, 'farmer_unique_id' =>$cropdata->farmer_uniqueId],200);
    }catch(Exception $e){
      return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
    }
  }

//   public function farmercropdata(Request $request)
//   {
//     $validator = Validator::make($request->all(),[
//         'farmer_uniqueId' => 'required',
//         'farmer_id' => 'required',
//         'plot_no' => 'required',
//     ]);
//     try{
//         $CropData = FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
//         if($CropData){//prevent multiple entry of cropdata
//             FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->forceDelete();
//         }

//          //here updatimg data to finalfarmer table and farmerplotdetail table
//         //to migrated data only from here, new data has all field filled
//         $farmer_data = FinalFarmer::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
//         if($request->has('aadhaar') && !empty($request->aadhaar)){
//           $farmer_data->aadhaar   = $request->aadhaar;
//         }

//         if($request->has('mobile') && !empty($request->mobile)){
//           $farmer_data->mobile   = $request->mobile;
//         }

//         if($request->has('gender') && !empty($request->gender)){
//           $farmer_data->gender   = $request->gender;
//         }

//         if($request->has('organization_id') && !empty($request->organization_id)){
//           $farmer_data->organization_id   = $request->organization_id;  
//         }

//         if($request->has('area_in_acers') && !empty($request->area_in_acers)){
//           $farmer_data->area_in_acers  =     $request->area_in_acers;
//         }

//         $farmer_data->save();  

//         //here storing data regarding 
//         $plot_detail=  FarmerPlot::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
//         if($request->has('area_in_acers') && !empty($request->area_in_acers)){
//          $plot_detail->area_in_acers  =     $request->area_in_acers;
//         }

//         if($request->has('area_in_other') && !empty($request->area_in_other)){
//           $plot_detail->area_in_other  =     $request->area_in_other;
//         }

//         if($request->has('area_in_other_unit') && !empty($request->area_in_other_unit)){
//           $plot_detail->area_in_other_unit  =     $request->area_in_other_unit;
//         }

//         if($request->has('area_acre_awd') && !empty($request->area_acre_awd)){
//           $plot_detail->area_acre_awd  =   $request->area_acre_awd;
//         }

//         if($request->has('area_other_awd') && !empty($request->area_other_awd)){
//           $plot_detail->area_other_awd  =     $request->area_other_awd;
//         }

//         if($request->has('area_other_awd_unit') && !empty($request->area_other_awd_unit)){
//           $plot_detail->area_other_awd_unit  =     $request->area_other_awd_unit;
//         }

//         if($request->has('patta_number') && !empty($request->patta_number)){
//           $plot_detail->patta_number   = $request->patta_number;
//         }

//         if($request->has('daag_number') && !empty($request->daag_number)){
//           $plot_detail->daag_number   = $request->daag_number; 
//         }

//         if($request->has('khatha_number') && !empty($request->khatha_number)){
//           $plot_detail->khatha_number  = $request->khatha_number;  
//         }

//         if($request->has('pattadhar_number') && !empty($request->pattadhar_number)){
//           $plot_detail->pattadhar_number   = $request->pattadhar_number;
//         }

//         if($request->has('khatian_number') && !empty($request->khatian_number)){
//           $plot_detail->khatian_number   = $request->khatian_number;
//         }
//         $plot_detail->save();

//         // now calculating total plot area from all plots
//         $plot_area =  FarmerPlot::where('farmer_uniqueId', $request->farmer_uniqueId)->sum('area_acre_awd');
//         //now updating the total area from plot and upate to final table
//         FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->update(['total_plot_area'=>$plot_area, 'cropdata_form'=>1]);

//         //create a record for farmer crop data
//       $cropdata = new FarmerCropdata; //now storing data in new farmer_cropdata table
//       $cropdata->farmer_id          = $request->farmer_id;
//       $cropdata->farmer_uniqueId    = $request->farmer_uniqueId;
//       $cropdata->farmer_plot_uniqueid   = $request->farmer_plot_uniqueid;
//       $cropdata->plot_no            = $request->plot_no;
//       $cropdata->area_in_acers      = $request->area_acre_awd;
//       $cropdata->season             = $request->season;
//       $cropdata->dt_irrigation_last = $request->dt_irrigation_last;
//       $cropdata->crop_variety       = $request->crop_variety;
//       $cropdata->dt_ploughing       = $request->dt_ploughing;
//       $cropdata->dt_transplanting   = $request->dt_transplanting;
//       $cropdata->status             = 'Approved';//'Pending';
//       $cropdata->apprv_reject_user_id  = '1';
//       $cropdata->surveyor_id        = auth()->user()->id;//store surveyor id
//       $cropdata->surveyor_name      = auth()->user()->name;//store surveyor name
//       $cropdata->surveyor_mobile    = auth()->user()->mobile;
//       $cropdata->date_survey        = Carbon::parse(Carbon::now())->format('d/m/Y');
//       $cropdata->date_time          = Carbon::now()->toTimeString();
       
//       //  for some period of time we will be making approved from l2 level
//       $cropdata->l2_status          = 'Approved';
//       $cropdata->l2_apprv_reject_user_id          = '1';       
//       $cropdata->save();

//       $detail = new CropdataDetail;
//       $detail->farmer_cropdata_id      = $cropdata->id;
//       $detail->crop_season_lastyrs     = $request->crop_season_lastyrs;
//       $detail->crop_season_currentyrs     = $request->crop_season_currentyrs;
//       $detail->crop_variety_lastyrs     = $request->crop_variety_lastyrs;
//       $detail->crop_variety_currentyrs     = $request->crop_variety_currentyrs;
//     //   $detail->water_mng_lastyrs     = $request->water_mng_lastyrs;
//     //   $detail->water_mng_currentyrs     = $request->water_mng_currentyrs;
//       $detail->yeild_lastyrs     = $request->yeild_lastyrs;
//       $detail->yeild_currentyrs     = $request->yeild_currentyrs;

//       $detail->fertilizer_1_name          =  $request->fertilizer_1_name;
//       $detail->fertilizer_1_lastyrs      = $request->fertilizer_1_lastyrs;
//       $detail->fertilizer_1_currentyrs   = $request->fertilizer_1_currentyrs;

//       $detail->fertilizer_2_name         = $request->fertilizer_2_name;
//       $detail->fertilizer_2_lastyrs      = $request->fertilizer_2_lastyrs;
//       $detail->fertilizer_2_currentyrs   = $request->fertilizer_2_currentyrs;

//       $detail->fertilizer_3_name         = $request->fertilizer_3_name;
//       $detail->fertilizer_3_lastyrs      = $request->fertilizer_3_lastyrs;
//       $detail->fertilizer_3_currentyrs    = $request->fertilizer_3_currentyrs;
//       $detail->nursery    = $request->nursery;
//       $detail->save();

//       $record =  PlotStatusRecord::create([
//                      'farmer_uniqueId'           => $request->farmer_uniqueId,
//                      'plot_no'                   => $request->plot_no,
//                      'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
//                      'level'                     => 'CropData',
//                      'status'                    => 'Pending',
//                      'comment'                   => 'Uploaded CropData',
//                      'timestamp'                 => Carbon::now(),
//                      'user_id'                   => auth()->user()->id,
//                  ]);
//       //store 1 to final farmer table means cropdata form collemeted
//       FinalFarmer::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update(['cropdata_form'=>1]);
//       if(!$cropdata){
//         return response()->json(['error' => true, 'message'=>'Sometime went wrong'],500);
//       }
//       return response()->json(['success' => true, 'farmerid' => $cropdata->farmer_id, 'farmer_unique_id' =>$cropdata->farmer_uniqueId],200);
//     }catch(Exception $e){
//       return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
//     }
//   }

    /**
     * Display a listing of the resource through api response.
     *
     * @return \Illuminate\Http\Response
     */
    public function cropvariety(Request $request){
      try{
        $CropvarietyList = Cropvariety::query();
        $CropvarietyList = $CropvarietyList->select('id','name','state_id' );
        if($request->has('state_id')){
          $CropvarietyList = $CropvarietyList->where('state_id',$request->state_id);
        }
        if($request->has('season_id')){
          $CropvarietyList = $CropvarietyList->where('season_id',$request->season_id);
        }
        if($request->has('state_name')){
          $CropvarietyList = $CropvarietyList->where('state','like', '%'.$request->state_name.'%');
        }
        $CropvarietyList = $CropvarietyList->get();
        return response()->json(['success'=>true,'cropvarietylist'=>$CropvarietyList],200);
      }catch(Exception $e){
        return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
      }
    }

    /**
     * Display a listing of the resource through api response.
     *
     * @return \Illuminate\Http\Response
     */
    public function mobileno(Request $request){
      try{
        $mobilelist = Farmer::select('mobile')
                              ->where('mobile','like',"%".$request->mobile."%")->get();
        return response()->json(['success'=>true,'mobilelist'=>$mobilelist],200);
      }catch(Exception $e){
        return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
      }
    }


        public function get_surveyor_list_offline(Request $request)
    {
        try {
            $surveyor_list = FinalFarmer::where('surveyor_id', $request->id)
                ->select('farmer_uniqueId', 'farmer_name', 'area_in_acers', 'own_area_in_acres', 'lease_area_in_acres')
                ->get();

            return response()->json(['success' => true, 'surveyor_list' => $surveyor_list], 200);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
        }
    }



    public function subplots_list_new(Request $request){
      try {
          $plotlist = FinalFarmer::with('ApprvFarmerPlot:farmer_plot_uniqueid,area_in_acers,area_in_other,area_in_other_unit,area_acre_awd,area_other_awd,area_other_awd_unit')
              ->select('id','farmer_plot_uniqueid','farmer_uniqueId','plot_no','area_in_acers','state_id','available_area','plot_area')
              ->where('farmer_uniqueId', $request->farmer_uniqueId)
              ->orderBy('plot_no', 'asc')->get();
          $available_area = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no', 1)->first();
  
          foreach ($plotlist as $plot) {
              if ($plot->ApprvFarmerPlot === null) {
                  return response()->json(['message' => 'No plot found! Please update Plot'], 422);
              }
              $polygon_area = Polygon::select('plot_area')
                  ->where('farmer_plot_uniqueid', $plot->farmer_plot_uniqueid)
                  ->where('plot_no', $plot->plot_no)
                  ->first();
  
              // Add the polygon area to the plot object
              $plot->polygon_area = $polygon_area ? $polygon_area->plot_area : null;
          }
  
          return response()->json([
              'success' => true,
              'plotlist' => $plotlist,
              'available_area' => $available_area->available_area,
          ], 200);
      } catch (Exception $e) {
          return response()->json(['error' => true, 'message' => 'Something Went wrong'], 500);
      }
  }
  
  /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        //
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
