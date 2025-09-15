<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerImage;
use App\Models\FarmerPlotImage;
use App\Models\Panchayat;
use App\Models\Village;
use App\Models\Taluka;
use App\Models\District;
use App\Models\FarmerConsentForm;
use App\Models\State;
use App\Models\FinalFarmer;
use App\Models\Setting;
use App\Models\FinalFarmerPlotImage;
use App\Models\FarmerFarmDetails;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use PDF;
use Storage;
use Carbon\Carbon;
use DB;
use Log;
use App\Models\PlotStatusRecord;
use PhpParser\Node\Stmt\TryCatch;

class FarmerUpdateController extends Controller
{
    /**
    * User generate otp api
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
     

     

     /**
     * User validate otp api
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      

    /**
   * List all farmer
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
    public function index(){
      $farmer = Farmer::all();
      return response()->json($farmer);
    }

    public function store(Request $request){

    }
    
   
    public function update_farmer_onboarding(Request $request){
    try{

      // dd($request->all());
          $farmers = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->get();
          // dd($farmers);
          if ($farmers->isEmpty()) {
              return response()->json(['error' => true, 'message' => 'No records found for the given farmer_uniqueId'], 422);
          }
          foreach ($farmers as $farmer) {
          $farmer->surveyor_id  = auth()->user()->id;
          // $farmer->surveyor_name  = auth()->user()->name;
          // $farmer->surveyor_email  = auth()->user()->email??NULL;
          // $farmer->surveyor_mobile  = auth()->user()->mobile;
          $farmer->farmer_name  = $request['farmer_name'];
          $farmer->mobile_access = $request['mobile_access'];
          $farmer->mobile_reln_owner = $request['mobile_reln_owner']??"NA";
          $farmer->mobile = $request['mobile'];
          $farmer->mobile_verified = '1';
          $farmer->document_no = $request['document_no'];
          // $farmer->farmer_uniqueId = $request['farmer_uniqueId'];
          // $farmer->farmer_plot_uniqueid = $request->farmer_uniqueId.'P1';
          // $farmer->plot_no = 1;
          // $farmer->no_of_plots = $request['no_of_plots'];
          $farmer->organization_id = $request['organization_id'];
          $farmer->gender = $request['gender'];
          $farmer->guardian_name = $request['guardian_name'];
          $farmer->status_onboarding = 'Pending';  
          $farmer->final_status_onboarding= 'Pending';// need to do direct approval so that it is easily available for crop data
          $farmer->onboarding_form      = 1;
          $farmer->area_in_acers      =  $request->area_in_acers;
          $farmer->own_area_in_acres =  $request->own_area_in_acres;
          $farmer->lease_area_in_acres = $request->lease_area_in_acres;
          $farmer->available_area =      $request->area_in_acers;
          $farmer->final_status       = 'Pending';
          $farmer->L2_aprv_timestamp      = Carbon::now();//by default adding current time in approval time
          $farmer->L2_appr_userid      = 1;
          // $farmer->L1_appr_timestamp      = Carbon::now();//by default adding current time in approval time
          // $farmer->L1_aprv_recj_userid      = 1;
          $farmer->save();
        }
       return response()->json(['success'=>true,'message'=>'Farmer updated Successfully',
                                'FarmerId'=>$farmer->id, 'FarmerUniqueID'=>$request->farmer_uniqueId],200);

    }catch(\Exception $e){
      dd($e);
       return response()->json(['error'=>true,'message'=>'Somethings went wrong']);
    }
   }
   

    public function update_image__last_screen(Request $request){
    // dd($request->all());
    try{
      // dd($request->has('screen'));
        if($request->has('screen')){ //for last screen upload data
          //below code will be active when, last screen data is being send from app
            if (version_compare(phpversion(), '7.1', '>=')){
                ini_set( 'precision', 17 );
                ini_set( 'serialize_precision', -1 );
            }
            $total_plot=0;
            $farmers_data = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->get();
            // dd($farmers_data);
            foreach($farmers_data as $item){
              // dd($item);
                $farmer = FinalFarmer::where('id',$item->id)->first();
                    // $farmerplot = FarmerPlot::where('farmer_uniqueId',$item->farmer_uniqueId)->where('plot_no',$item->plot_no)->sum('area_in_acers');
                  
                    // $farmer->total_plot_area = number_format((float) $farmerplot, 2);
                    // // dd($farmer->total_plot_area );
                    // $farmer->save();
                $farmer->date_survey  = $request->date_survey;
                $farmer->time_survey  = $request->time_survey;
                $farmer->onboarding_form  = '1';
                $farmer->financial_year  = $request->financial_year??NULL;
                $farmer->season  = $request->season??NULL;
                
                if($request->hasFile('plotowner_sign')){
                    $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->plotowner_sign);
                    $farmer->plotowner_sign        =  Storage::disk('s3')->url($path);
                }

                $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->farmer_photo);
                $farmer->farmer_photo        =  Storage::disk('s3')->url($path);

                $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->aadhaar_photo);
                $farmer->aadhaar_photo        =  Storage::disk('s3')->url($path);
                // $path=$request->farmer_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'farmerphoto-'.$request->file('farmer_photo')->getClientOriginalName(), 'public');
                // $farmer->farmer_photo  = asset('storage/'.$path);

                // $path=$request->aadhaar_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'aadhaarphoto-'.$request->file('aadhaar_photo')->getClientOriginalName(), 'public');
                // $farmer->aadhaar_photo  = asset('storage/'.$path);
                if($request->hasFile('others_photo')){

                    $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->others_photo);
                    $farmer->others_photo        =  Storage::disk('s3')->url($path);
                    // $path= $request->others_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'othersphoto-'.$request->file('others_photo')->getClientOriginalName(), 'public');
                    // $farmer->others_photo  = asset('storage/'.$path);
                }
                if ($request->hasFile('signature')){
                  $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->signature);
                    FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->update(['check_carbon_credit'=>'1',
                      'signature'=> Storage::disk('s3')->url($path), 
                      'sign_carbon_date'=>Carbon::now()]);
                }
                $farmer->save();
            }//endof foreach
            if(!$farmers_data){
              return response()->json(['error' => true, 'message' => 'Something went wrong'],500);
            }
            return response()->json(['success' => true, 'message' => 'Updated Succesfull'],200);
        }// code for last screen update

    }catch(\Exception $e){
      // dd($e);
       return response()->json(['error'=>true,'message'=>'Somethings went wrong'],422);
    }
   }

   public function update_area(Request $request)
   {
    try{

      $farmer = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)
          ->first();

      if (!$farmer) {
          return response()->json(['error' => true, 'message' => 'No records found for the given farmer uniqueId'], 422);
      }

      if($farmer)
      {
        DB::table('final_farmers')
        ->where('farmer_uniqueId', $request->farmer_uniqueId)
        ->update([
          'available_area' => $request->available_area,
          'area_in_acers' => $request->area_in_acers,
        ]);
  
        // $farmer->update([
        // ]);
        // Update the farmer_plot_detail record
        DB::table('farmer_plot_detail')
            ->where('farmer_uniqueId', $request->farmer_uniqueId)
            ->update([
                'area_in_acers' => $request->area_in_acers,
            ]);
            
        return response()->json(['success' => true, 'message' => 'Updated successfully'], 200);

      }
  } catch (\Exception $e) {
      // dd($e);
      return response()->json(['error' => true, 'message' => 'Something went wrong']);
  }
   }

    

   public function farmerLocation_update(Request $request){
     \Log::info('Update Location data.', ['request' => $request->all()]);
    $validator = Validator::make($request->all(),[
        'latitude' => 'required',
        'longitude' => 'required',
    ]);
    try{
       //  location screen data store
       $Farmer_data  = DB::table('final_farmers')->where('farmer_uniqueId',$request->farmer_uniqueId)->get();
      //  dd($Farmer_data);
       foreach($Farmer_data as $items){

         $Farmer = FinalFarmer::where('id',$items->id)->first();
        //  $state = State::where('id',$request->state_id)->first();
            //  $Farmer->state        = $state->name;
             $Farmer->state_id     = $request->state_id;
        //  $Farmer->country      = $state->countryname->name;
        //  $Farmer->country_id   = $state->country_id;//we have countries table in India has 101 as ID
        //  $district = District::where('id',$request->district_id)->first();
            //  $Farmer->district     = $district->district;
             $Farmer->district_id  = $request->district_id;
        //  $taluka = Taluka::where('id',$request->taluka_id)->first();
            //  $Farmer->taluka       = $taluka->taluka;
             $Farmer->taluka_id    = $request->taluka_id;
        //  $Panchayat = Panchayat::whereId($request->panchayat_id)->first();
            //  $Farmer->panchayat       = $Panchayat->panchayat;
             $Farmer->panchayat_id    = $request->panchayat_id;
        //  $village = Village::where('id',$request->village_id)->first();
            //  $Farmer->village      = $village->village;
            $Farmer->village_id   = $request->village_id;
         $Farmer->latitude     = $request->latitude;
         $Farmer->longitude    = $request->longitude;
         $Farmer->remarks      = $request->remarks??'NA';
         $Farmer->pincode      = $request->pincode;
         $Farmer->save();
       }        
      if(!$Farmer_data){
        return response()->json(['error'=>true,'message'=>'something went wrong'],500);
      }
      return response()->json(['success'=>true,'farmerId'=>$Farmer_data->first()->id,'farmerUniqueId'=>$Farmer_data->first()->farmer_uniqueId],200);
    }catch(Exception $e){
      // dd($e);
      return response()->json(['error'=>true,'message'=>'something went wrong'],500);
    }
  }

  public function farmer_plot_image_update(Request $request)
  {
    try {
      //to store plot image
      $img = new FinalFarmerPlotImage;
      $img->farmer_id   =       $request->farmer_id;
      $img->farmer_unique_id   = $request->farmer_unique_id;
      $img->plot_no   = "1";
      $img->image   =       'landrecords';
      $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_unique_id . '/' . $request->farmer_unique_id . 'P' . $img->plot_no . '/' . 'ONBOARDPLOT', $request->image); 
      $img->path   = Storage::disk('s3')->url($path); 
      $img->status   = 'Approved';
      $img->save();
      if (!$img) {
        return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
      }
      return response()->json(['success' => true, 'farmerId' => $img->farmer_id, 'farmerUniqueId' => $img->farmer_unique_id], 200);
    } catch (Exception $e) {
      return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
    }
  }


  public function farmerplot_update(Request $request){
    // dd($request->all());
    $validator = Validator::make($request->all(),[
        'land_ownership' => 'required',
        'survey_no' => 'required',
    ]);
    try{
      
       $farmers = FarmerPlot::where('farmer_uniqueId', $request->farmer_uniqueId)->get();
     
      //  if ($farmers->isEmpty()) {
      //      return response()->json(['error' => true, 'message' => 'No records found for the given farmer_uniqueId'], 422);
      //  }
      if ($farmers->isEmpty())
      {  

       $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->get();

       foreach($farmer as $data){
        
         $FarmerPlot  = new FarmerPlot;
         $FarmerPlot->farmer_id        =  $data->id;
         $FarmerPlot->farmer_uniqueId  =  $request->farmer_uniqueId;
         $FarmerPlot->farmer_plot_uniqueid = $data->farmer_plot_uniqueid;
         $FarmerPlot->plot_no = 1;
         $FarmerPlot->area_in_acers    =  $data->area_in_acers; //always in acers
         $FarmerPlot->area_in_other    =  $request->area_in_other ?? "0.00";
         $FarmerPlot->area_in_other_unit   =  $request->area_in_other_unit ?? "0.00";; // record the converted value units
         $FarmerPlot->survey_no             = $request->survey_no;
         $FarmerPlot->land_ownership        = $request->land_ownership;
         //we need to make l1 validator by default approved
         $FarmerPlot->aprv_recj_userid      = 1; // this is admin 1
         $FarmerPlot->appr_timestamp        = Carbon::now(); //this is because we want a survey to display directly in l2 validator.
         $FarmerPlot->status                = 'Pending';
         $FarmerPlot->final_status          = 'Pending';
         $FarmerPlot->finalaprv_timestamp   = Carbon::now();
         $FarmerPlot->finalappr_userid      = 1;
         $FarmerPlot->area_acre_awd    =  $request->area_acre_awd ?? "0.00";
         $FarmerPlot->area_other_awd    =  $request->area_other_awd ?? "0.00"; //here other represent that area value willbe is other conversion also. 
         //  dd($FarmerPlot->area_acre_awd);
         $FarmerPlot->area_other_awd_unit   = $FarmerPlot->area_in_other_unit;  //to represent other conversion units
         //only for assam
         $FarmerPlot->patta_number   =  $request->patta_number;
         $FarmerPlot->daag_number   =  $request->daag_number;
         //for telangana
         $FarmerPlot->khatha_number   =  $request->khatha_number;
         $FarmerPlot->pattadhar_number   =  $request->pattadhar_number;
         //for west bengal
         $FarmerPlot->khatian_number   =  $request->khatian_number;
 
         if ($request->has('actual_owner_name') && !empty($request->actual_owner_name)) {
           $FarmerPlot->actual_owner_name     = $request->actual_owner_name;
           DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_uniqueId)->update(['actual_owner_name' => $request->actual_owner_name]);
         } else {
           $farmername = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->select('farmer_name')->first();
           $FarmerPlot->actual_owner_name     = $farmername->farmer_name;
           DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_uniqueId)->update(['actual_owner_name' => $farmername->farmer_name]);
         }
 
         if ($request->land_ownership == 'Leased') {
           $FarmerPlot->affidavit_tnc         = '1';
           $FarmerPlot->sign_affidavit_date   = Carbon::now();
           DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_uniqueId)->update(['land_ownership' => 'Leased']);
         } elseif ($request->land_ownership == 'Own') {
           $FarmerPlot->affidavit_tnc         = '0';
           DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['land_ownership' => 'Own']);
         }
         $FarmerPlot->save();

         DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['updated_at' => carbon::now()]); //update timestamp
       }
      
          return response()->json(['success' => true, 'message' => 'Plot added successfully'], 200);

     }

      //  $farmer = DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->get();
      // dd( $farmer);
       
      foreach($farmers as $FarmerPlot){

      // $FarmerPlot->farmer_id        =  $farmer->id;
      // $FarmerPlot->farmer_uniqueId  =  $request->farmer_unique_id;
      // $FarmerPlot->farmer_plot_uniqueid = $FarmerPlot->farmer_uniqueId.'P1';
      // $FarmerPlot->plot_no=1;
      // $FarmerPlot->area_in_acers    =  $farmer->area_in_acers; //always in acers
      $FarmerPlot->area_in_other    =  $request->area_in_other??"0.00"; 
      $FarmerPlot->area_in_other_unit   =  $request->area_in_other_unit??"0.00";; // record the converted value units
       $FarmerPlot->survey_no             = $request->survey_no;
       $FarmerPlot->land_ownership        = $request->land_ownership;
       //we need to make l1 validator by default approved
       $FarmerPlot->aprv_recj_userid      = 1;// this is admin 1
       $FarmerPlot->appr_timestamp        = Carbon::now();//this is because we want a survey to display directly in l2 validator.
       $FarmerPlot->status                = 'Pending';
       $FarmerPlot->final_status          = 'Pending';
       $FarmerPlot->finalaprv_timestamp   = Carbon::now();
       $FarmerPlot->finalappr_userid      = 1;
      //  $FarmerPlot->area_acre_awd    =  $request->area_acre_awd??"0.00"; 
      //  $FarmerPlot->area_other_awd    =  $request->area_other_awd??"0.00"; //here other represent that area value willbe is other conversion also. 
      //  dd($FarmerPlot->area_acre_awd);
       $FarmerPlot->area_other_awd_unit   = $FarmerPlot->area_in_other_unit;  //to represent other conversion units
       //only for assam
       $FarmerPlot->patta_number   =  $request->patta_number;
       $FarmerPlot->daag_number   =  $request->daag_number;
       //for telangana
       $FarmerPlot->khatha_number   =  $request->khatha_number;
       $FarmerPlot->pattadhar_number   =  $request->pattadhar_number;
       //for west bengal
       $FarmerPlot->khatian_number   =  $request->khatian_number;

       if($request->has('actual_owner_name') && !empty($request->actual_owner_name)){
          $FarmerPlot->actual_owner_name     = $request->actual_owner_name;
          DB::table('final_farmers')->where('farmer_uniqueId',$request->farmer_uniqueId)->update(['actual_owner_name'=>$request->actual_owner_name]);
       }else{
          $farmername = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->select('farmer_name')->first();
          $FarmerPlot->actual_owner_name     = $farmername->farmer_name;
          DB::table('final_farmers')->where('farmer_uniqueId',$request->farmer_uniqueId)->update(['actual_owner_name'=>$farmername->farmer_name]);
       }

       if($request->land_ownership == 'Leased'){
          $FarmerPlot->affidavit_tnc         = '1';
          $FarmerPlot->sign_affidavit_date   = Carbon::now();
          DB::table('final_farmers')->where('farmer_uniqueId',$request->farmer_uniqueId)->update(['land_ownership'=>'Leased']);

       }elseif($request->land_ownership == 'Own'){
          $FarmerPlot->affidavit_tnc         = '0';
          DB::table('final_farmers')->where('farmer_uniqueId',$request->farmer_uniqueId)->update(['land_ownership'=>'Own']);
       }
       // if ($request->hasFile('sign_affidavit')) {
       //  $FarmerPlot->sign_affidavit = $request->sign_affidavit->storeAs('plot/'.$request->farmer_unique_id, 'affidavit-sign'.$request->file('sign_affidavit')->getClientOriginalName(), 'public');

       // }
       $FarmerPlot->save();
           DB::table('final_farmers')->where('farmer_uniqueId',$request->farmer_uniqueId)->update(['updated_at'=>carbon::now()]);//update timestamp
       }

      return response()->json(['success'=>true,'farmerId'=>$FarmerPlot->farmer_id,'farmerUniqueId'=>$FarmerPlot->farmer_uniqueId],200);

    }catch(\Exception $e){
      // dd($e);
     return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
    }
  }

    
//Created on 07/01/2024 to update the Farmer Farm Details

public function update_image_last_screen_v2(Request $request)
{
    try {
        \Log::info('Update image process started.', ['request' => $request->all()]);

        if ($request->has('screen')) {
            if (version_compare(phpversion(), '7.1', '>=')) {
                ini_set('precision', 17);
                ini_set('serialize_precision', -1);
            }

            $farmers_data = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->get();
            // \Log::info('Farmers data fetched.', ['farmers_data' => $farmers_data]);

            if ($farmers_data->isEmpty()) {
                // \Log::warning('No farmer data found.', ['farmer_uniqueId' => $request->farmer_uniqueId]);
                return response()->json(['error' => true, 'message' => 'No farmer data found'], 404);
            }

            $updateData = [
                'onboarding_form' => '1',
                'financial_year' => $request->financial_year ?? null,
                'season' => $request->season ?? null,
                'onboard_completed' => 'Pending'
            ];

            foreach ($farmers_data as $item) {
                $farmer = FinalFarmer::find($item->id);

                if (!$farmer) {
                    \Log::warning('Farmer not found.', ['farmer_id' => $item->id]);
                    continue;
                }

                // \Log::info('Updating farmer data.', ['farmer_id' => $farmer->id]);

                if ($request->hasFile('plotowner_sign') && $request->file('plotowner_sign') != '0') {
                    $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS', $request->plotowner_sign);
                    $updateData['plotowner_sign'] = Storage::disk('s3')->url($path);
                }

                if ($request->hasFile('farmer_photo') && $request->file('farmer_photo') != '0') {
                    $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS', $request->farmer_photo);
                    $updateData['farmer_photo'] = Storage::disk('s3')->url($path);
                }

                if ($request->hasFile('aadhaar_photo') && $request->file('aadhaar_photo') != '0') {
                    $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS', $request->aadhaar_photo);
                    $updateData['aadhaar_photo'] = Storage::disk('s3')->url($path);
                }

                if ($request->hasFile('aadhaar_back_photo') && $request->file('aadhaar_back_photo') != '0') {
                    $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS', $request->aadhaar_back_photo);
                    $updateData['aadhaar_back_photo'] = Storage::disk('s3')->url($path);
                }

                if ($request->hasFile('others_photo')) {
                    $plotOwnerSigns = $request->file('others_photo');
                    \Log::info('Processing multiple others_photo files.', ['file_count' => count($plotOwnerSigns)]);

                    foreach ($plotOwnerSigns as $index => $file) {
                        $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS', $file);
                        $url = Storage::disk('s3')->url($path);

                        FarmerConsentForm::updateOrCreate(
                            [
                                'farmer_uniqueId' => $request->farmer_uniqueId,
                                'index' => $index,
                                'plot_no' => 1,
                            ],
                            [
                                'images' => $url,
                            ]
                        );

                        // \Log::info('Stored and updated FarmerConsentForm.', [
                        //     'index' => $index,
                        //     'url' => $url,
                        // ]);
                    }
                }

                if ($request->hasFile('signature')) {
                    $path = Storage::disk('s3')->putFileAs(
                        config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/DOCUMENTS',
                        $request->file('signature'),
                        $request->file('signature')->getClientOriginalName()
                    );

                    $updateData['check_carbon_credit'] = '1';
                    $updateData['signature'] = Storage::disk('s3')->url($path);
                    $updateData['sign_carbon_date'] = Carbon::now();
                }

                $farmer->update($updateData);
                // \Log::info('Farmer data saved.', ['farmer_id' => $farmer->id]);
            }

            \Log::info('Update image process completed successfully.');
            return response()->json(['success' => true, 'message' => 'Updated Successfully'], 200);
        }

        \Log::warning('Invalid request: "screen" field missing.');
        return response()->json(['error' => true, 'message' => 'Invalid request'], 400);
    } catch (\Exception $e) {
        \Log::error('Error occurred during update_image_last_screen_v2 process.', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json(['error' => true, 'message' => 'Something went wrong', 'exception' => $e->getMessage()], 422);
    }
}

//Created on 07/01/2024 to update the Farmer Farm Details
public function update_consent_form(Request $request)
{
    // \Log::info('Update consent form.', ['request' => $request->all()]);
    $farmer_farm_data = FarmerFarmDetails::where('farmer_uniqueId', $request->farmer_uniqueId)->first();
    if ($farmer_farm_data) {
        $farmer_farm_data->update([
            'irigation_source' => $request->irigation_source,
            'struble_burning' => $request->struble_burning,
            'double_paddy_status' => $request->double_paddy_status,
            'soil_type' => $request->soil_type,
            'variety' => $request->variety,
            'flooding_type' => $request->flooding_type,
            'proper_drainage' => $request->proper_drainage,
            'awd_previous' => $request->awd_previous,
            'awd_previous_no' => $request->awd_previous_no,
            'community_benefit' => $request->community_benefit,
        ]);
        $farmer = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->first();
        if ($farmer) {
            $farmer->update([
                'onboard_completed' => 'Pending',
            ]);
        }
        return response()->json(['success' => true, 'message' => 'Data Updated Successfully'], 200);
    }
    return response()->json(['error' => true, 'message' => 'Data not found'], 404);
}


}
