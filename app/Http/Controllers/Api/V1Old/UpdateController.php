<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FinalFarmer;
use App\Models\FarmerPlot;
use Storage;

class UpdateController extends Controller
{
    /**
     * Update data for oboarding
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update_onboarding(Request $request)
    {
      try{
        //here updatimg data to finalfarmer table and farmerplotdetail table
        //to migrated data only from here, new data has all field filled
        $farmer_data = FinalFarmer::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        $farmer_data->aadhaar   = $request->aadhaar;
        $farmer_data->mobile   = $request->mobile;
        $farmer_data->gender   = $request->gender;
        $farmer_data->organization_id   = $request->organization_id;   
        if($request->hasFile('plotowner_sign')){
          $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->plotowner_sign);
          $farmer_data->plotowner_sign        =  Storage::disk('s3')->url($path);
        }
        if($request->hasFile('farmer_photo')){
          $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->farmer_photo);
          $farmer_data->farmer_photo        =  Storage::disk('s3')->url($path);
        }
        if($request->hasFile('aadhaar_photo')){
          $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->aadhaar_photo);
          $farmer_data->aadhaar_photo        =  Storage::disk('s3')->url($path);
        }
        if($request->hasFile('others_photo')){
          $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.'DOCUMENTS', $request->others_photo);
          $farmer_data->others_photo        =  Storage::disk('s3')->url($path);
        }     
        $farmer_data->save();  
        //now put plot detail
        $plot_detail=  FarmerPlot::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        // $plot_detail->area_in_acers  =     "0.0";
        // $plot_detail->area_in_other  =     "0.0";
        // $plot_detail->area_acre_awd  =   "0.0";
        // $plot_detail->area_other_awd  =     "0.0";
        $plot_detail->patta_number   = $request->patta_number;
        $plot_detail->daag_number   = $request->daag_number;  
        $plot_detail->khatha_number  = $request->khatha_number;  
        $plot_detail->pattadhar_number   = $request->pattadhar_number;
        $plot_detail->khatian_number   = $request->khatian_number;
        $plot_detail->actual_owner_name   = $request->actual_owner_name;
        $plot_detail->save();
        return response()->json(['success'=>true,'message'=>'Updated Successfully'],200);
      }catch(\Exception $e){
        return response()->json(['error'=>true, 'message'=>'something went wrong'],422);
      }      
    }
}
