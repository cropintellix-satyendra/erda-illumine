<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\CropdataDetail;
use App\Models\FarmerPlot;
use App\Models\FinalFarmer;
use App\Models\PlotStatusRecord;
use App\Models\FarmerCropdata;
use App\Models\UserTarget;
use App\Models\FarmerConsentForm;
use App\Models\Polygon;
use Exception;
use DB;
use Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CropDataController extends Controller
{
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
        //store 1 to final farmer table means cropdata form collemeted

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
        FinalFarmer::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update(['cropdata_form'=>1]);
        if(!$cropdata){
          return response()->json(['error' => true, 'message'=>'Sometime went wrong'],500);
        }
        return response()->json(['success' => true, 'farmerid' => $cropdata->farmer_id, 'farmer_unique_id' =>$cropdata->farmer_uniqueId],200);
      }catch(Exception $e){
        return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
      }
    }










  public function store_image__last_screen(Request $request)
  {
    // dd($request->all());
    Log::info('Request data:', $request->all());
    try {
      if ($request->has('screen')) { //for last screen upload data
        //below code will be active when, last screen data is being send from app
        if (version_compare(phpversion(), '7.1', '>=')) {
          ini_set('precision', 17);
          ini_set('serialize_precision', -1);
        }
        $total_plot = 0;
        $farmers_data = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->get();
        // dd($farmers_data);
        foreach ($farmers_data as $item) {
          $farmer = FinalFarmer::where('id', $item->id)->first();
          // $farmerplot = FarmerPlot::where('farmer_uniqueId',$item->farmer_uniqueId)->where('plot_no',$item->plot_no)->sum('area_in_acers');

          // $farmer->total_plot_area = number_format((float) $farmerplot, 2);
          // // dd($farmer->total_plot_area );
          // $farmer->save();
          $farmer->date_survey  = $request->date_survey;
          $farmer->time_survey  = $request->time_survey;
          $farmer->financial_year  = $request->financial_year;
          $farmer->season  = $request->season;
          $farmer->onboarding_form  = '1';
          $farmer->onboard_completed  = 'Pending';
          if ($request->hasFile('plotowner_sign')) {
            $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->plotowner_sign);
            $farmer->plotowner_sign        =  Storage::disk('s3')->url($path);
          }
           if ($request->hasFile('others_photo')) {
             $plotOwnerSigns = $request->file('others_photo');
            //  dd($plotOwnerSigns);

            foreach ($plotOwnerSigns as $index => $file) {
                // Define the path and save the file to S3
                $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS', $file);
                $url = Storage::disk('s3')->url($path);

                // Store the URL in the appropriate attribute based on the index
                // if ($index == 0) {
                //     $farmer->others_photo1 = $url;
                // } elseif ($index == 1) {
                //     $farmer->others_photo2 = $url;
                // }
                
                FarmerConsentForm::create([
                    'farmer_uniqueId' => $request->farmer_uniqueId,
                    'images' => $url,
                    'plot_no' => 1,
                    'index' => $index
                ]);
            }
            //  $farmer->save();
         }

          $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->farmer_photo);
          $farmer->farmer_photo        =  Storage::disk('s3')->url($path);

          $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->aadhaar_photo);
          $farmer->aadhaar_photo        =  Storage::disk('s3')->url($path);


          if ($request->hasFile('aadhaar_back_photo')) {
            $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->aadhaar_back_photo);
            $farmer->aadhaar_back_photo        =  Storage::disk('s3')->url($path);
          }
          // $path=$request->farmer_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'farmerphoto-'.$request->file('farmer_photo')->getClientOriginalName(), 'public');
          // $farmer->farmer_photo  = asset('storage/'.$path);

          // $path=$request->aadhaar_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'aadhaarphoto-'.$request->file('aadhaar_photo')->getClientOriginalName(), 'public');
          // $farmer->aadhaar_photo  = asset('storage/'.$path);
        //   if ($request->hasFile('others_photo')) {

        //     $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->others_photo);
        //     $farmer->others_photo        =  Storage::disk('s3')->url($path);
        //     // $path= $request->others_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'othersphoto-'.$request->file('others_photo')->getClientOriginalName(), 'public');
        //     // $farmer->others_photo  = asset('storage/'.$path);
        //   }
          // dd($farmer);
          $farmer->save();
          if ($request->hasFile('signature')) {
            $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->signature);
            FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->update([
              'check_carbon_credit' => '1',
              'signature' => Storage::disk('s3')->url($path),
              'sign_carbon_date' => Carbon::now()
            ]);
          }
        } //endof foreach
        if (!$farmers_data) {
          return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
        }
        return response()->json(['success' => true, 'message' => 'Saved Succesfull'], 200);
      } // code for last screen update

    } catch (\Exception $e) {
        Log::error('Error storing farmer crop detail:', ['error' => $e->getMessage()]);
      return response()->json(['error' => true, 'message' => 'Somethings went wrong']);
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
            $plot->polygon_area = $polygon_area ? $polygon_area->plot_area : 0;
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


//   if ($request->hasFile('others_photo')) {
//     $plotOwnerSigns = $request->file('others_photo');
//    //  dd($plotOwnerSigns);

//    foreach ($plotOwnerSigns as $index => $file) {
//        // Define the path and save the file to S3
//        $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS', $file);
//        $url = Storage::disk('s3')->url($path);

//        // Store the URL in the appropriate attribute based on the index
//        if ($index == 0) {
//            $farmer->others_photo1 = $url;
//        } elseif ($index == 1) {
//            $farmer->others_photo2 = $url;
//        }
//    }
//     $farmer->save();
// }




public function farmercropdata_new_year_season_wise(Request $request)
{
  $validator = Validator::make($request->all(),[
      'farmer_uniqueId' => 'required',
      'farmer_id' => 'required',
      'plot_no' => 'required',
  ]);
  try{
      // $CropData = FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->first();
      $CropData = FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->where('financial_year',$request->financial_year)->where('financial_season',$request->financial_season)->first();


      $numericPart = ''; // Initialize numericPart to an empty string
      preg_match('/P(\d+)$/', $request->farmer_plot_uniqueid, $matches);
      if (isset($matches[1])) {
          $numericPart = $matches[1];
      }

      if($CropData){//prevent multiple entry of cropdata
          // FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->forceDelete();
          return response()->json(['success' => true, 'message'=>"Data already submitted", 'farmerid' => $CropData->farmer_id, 'farmer_unique_id' =>$CropData->farmer_uniqueId],200);

      }
       //here updatimg data to finalfarmer table and farmerplotdetail table
      //to migrated data only from here, new data has all field filled
      $farmer_data = FinalFarmer::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
      // dd( $farmer_data);
      if($request->has('aadhaar') && !empty($request->aadhaar)){
        $farmer_data->aadhaar   = $request->aadhaar;
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

      // now calculating total plot area from all plots
      $plot_area =  FarmerPlot::where('farmer_uniqueId', $request->farmer_uniqueId)->sum('area_acre_awd');
      //now updating the total area from plot and upate to final table
      FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->update(['total_plot_area'=>$plot_area, 'cropdata_form'=>1]);

      //create a record for farmer crop data
     $cropdata = new farmercropdata; //now storing data in new farmer_cropdata table
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
     $cropdata->financial_year          = $request->financial_year;
     $cropdata->financial_season          = $request->financial_season;
     $cropdata->save();
     $detail = new CropdataDetail();
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

               //used to store the daily count & module wise count & for a report
               $userTarget = UserTarget::updateOrCreate(
                 [
                     'user_id' => auth()->user()->id,
                     'module_id' => '2',
                     'date' => now()->toDateString(),
                 ],
                 [
                     'module_name' => 'cropdata',
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

}
