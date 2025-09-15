<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerCropdata; 
use App\Models\FarmerBenefit; 
use App\Models\Setting;

class UserController extends Controller
{
    /**
   * User profile detail
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
    public function profile(){
        $User  = User::select('id','name','email','mobile')->find(auth()->user()->id);
        return response()->json($User);
    }
    
    /**
    * User profile update
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
     public function UpdateProfile(Request $request){
          $user = User::find(auth()->user()->id);
          if($request->has('name')){
            $user->name = $request->name;
          }
          if($request->has('email')){
            $user->email = $request->email;
          }
          if($request->has('mobile')){
            $user->mobile = $request->mobile;
          }
          $user->save();
          $user =User::select('id','name','email','mobile')->whereId(auth()->user()->id)->first();
          return response()->json(['success'=>True,'message'=>'Saved Successfully', 'user'=>$user],200);
     }
     
     /**
   * Privacy policy
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function getPrivacy(){ 
       //api for affidavit and carbon credit
    $setting = Setting::select('app_privacypolicy')->find(1);
    return response()->json($setting);
   }
   
    /**
   * Get termand contion for app
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function getTermcondition(){ 
       //api for affidavit and carbon credit
    $setting = Setting::select('app_termncond')->find(1);
    return response()->json($setting);
   }
   
}
