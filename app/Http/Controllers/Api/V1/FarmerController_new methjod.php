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
use App\Models\State;
use App\Models\Country;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use PDF;
use Storage;
use Carbon\Carbon;
use DB;
use App\Models\PlotStatusRecord;

class FarmerController extends Controller
{
    /**
    * User generate otp api
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
     public function generate_otp(Request $request)
     {
        $request->validate([
            'mobile' => 'required|digits:10|numeric',
        ]);
        $otp = random_int(100000, 999999);
        $time = DB::table('settings')->select('otpTime')->where('id',1)->first();
        $user_otp = DB::insert('insert into register_otp (mobile, otp, otp_time,status,ip,created_at) values (?, ?, ?, ?, ?,?)', [$request->mobile, $otp, $time->otpTime,'Checking',$_SERVER['REMOTE_ADDR']??NULL ,carbon::now()]);
        $curl = curl_init();
        // 86c30badb535a802 new
            // b7634c3d6d6a0079 old
        curl_setopt_array($curl, array(  
            CURLOPT_URL => 'https://api.authkey.io/request?authkey=abd19922e8887923&mobile='.$request->mobile.'&country_code=91&sid=6560&otp='.$otp.'&time='.$time->otpTime.'%20mins',
          //CURLOPT_URL => 'https://api.authkey.io/request?authkey=86c30badb535a802&mobile='.$request->mobile.'&country_code=91&sid=5910&otp='.$otp.'&time='.$time->otpTime.'%20mins',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
          return response()->json(['error'=>true,'message'=>'Something went wrong'],422);
        } else {
          return response()->json(['success'=>true,'mobile'=> $request->mobile,'otp'=>$otp],200);
        }
     }

     /**
     * User validate otp api
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function validate_otp(Request $request)
      {
        if($request->mobile == '9820098200'){
            $verify = DB::table('register_otp')->where('mobile',$request->mobile)->where('otp',$request->otp)->first();
            if($verify){
                DB::table('register_otp')->where('mobile',$request->mobile)->where('otp',$request->otp)->update(['status' => 'Verified']);
                return response()->json(['success'=>true,'message'=> 'Verified Successfully'],200);
            }
        }else{
            $verify = DB::table('register_otp')->where('mobile',$request->mobile)->where('otp',$request->otp)->first();
            if($verify){
                if(Carbon::now() <= Carbon::parse($verify->created_at)->addMinutes(10)){
                    DB::table('register_otp')->where('mobile',$request->mobile)->where('otp',$request->otp)->update(['status' => 'Verified']);
                    return response()->json(['success'=>true,'message'=> 'Verified Successfully'],200);
                }else{
                     return response()->json(['success'=>true,'message'=> 'Time exceeded 10 mins'],422);
                }
            }
        }
        return response()->json(['error'=>true,'message'=>'Otp validation failed'],422);
      }

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

   /**
   * Farmer store
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function store(Request $request){
     $validator = Validator::make($request->all(),[
         'name' => 'required|string',
         'email' => 'required|email|unique:farmers',
         'mobile' => 'required|unique:users',
     ]);
    try{
        if($request->has('screen')){ //for last screen upload data
            if (version_compare(phpversion(), '7.1', '>=')){
                ini_set( 'precision', 17 );
                ini_set( 'serialize_precision', -1 );
            }
            $total_plot=0;
            $farmerplot = FarmerPlot::where('farmer_id',$request->farmer_id)->sum('area_in_acers');
            $farmer = Farmer::find($request->farmer_id);
            $farmer->total_plot_area  =  number_format((float) $farmerplot, 2);
            $farmer->date_survey  = $request->date_survey;
            $farmer->time_survey  = $request->time_survey;
            $farmer->onboarding_form  = '1';
            $path="";
            // $path=$request->farmer_sign->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'farmersign-'.$request->file('farmer_sign')->getClientOriginalName(), 'public');
            // $farmer->farmer_sign  = asset('storage/'.$path);

            if($request->hasFile('plotowner_sign')){
              $path=$request->plotowner_sign->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'plotowner-'.$request->file('plotowner_sign')->getClientOriginalName(), 'public');
              $farmer->plotowner_sign  = asset('storage/'.$path);
            }

            $path=$request->farmer_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'farmerphoto-'.$request->file('farmer_photo')->getClientOriginalName(), 'public');
            $farmer->farmer_photo  = asset('storage/'.$path);

            $path=$request->aadhaar_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'aadhaarphoto-'.$request->file('aadhaar_photo')->getClientOriginalName(), 'public');
            $farmer->aadhaar_photo  = asset('storage/'.$path);

            if($request->hasFile('others_photo')){
                $path= $request->others_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'othersphoto-'.$request->file('others_photo')->getClientOriginalName(), 'public');
                $farmer->others_photo  = asset('storage/'.$path);
            }
            $farmer->save();
            if(!$farmer){
              return response()->json(['error' => true, 'message' => 'Something went wrong'],500);
            }
            return response()->json(['success' => true, 'message' => 'Saved Succesfull'],200);
        }// code for last screen update
        //to prevent multiple record
        $farmer = Farmer::where('farmer_uniqueId', $request['farmer_uniqueId'])->first();
        if($farmer){
            Farmer::where('farmer_uniqueId', $request['farmer_uniqueId'])->forceDelete();
            FarmerPlot::where('farmer_uniqueId', $request['farmer_uniqueId'])->forceDelete();
        }
      //  $request->request->add(collect($request->json())->toArray());
       $farmer = new Farmer;
       $farmer->surveyor_id  = auth()->user()->id;
       $farmer->surveyor_name  = auth()->user()->name;
       $farmer->surveyor_email  = auth()->user()->email??'';
       $farmer->surveyor_mobile  = auth()->user()->mobile;
       $farmer->farmer_name  = $request['farmer_name'];
       $farmer->mobile_access = $request['mobile_access'];
       $farmer->mobile_reln_owner = $request['mobile_reln_owner']??"NA";
       $farmer->mobile = $request['mobile'];
       $farmer->mobile_verified = '1';
       $farmer->aadhaar = $request['aadhar']; 
       $farmer->farmer_uniqueId = $request['farmer_uniqueId'];
       $farmer->no_of_plots = $request['no_of_plots'];
       $farmer->organization_id = $request['organization_id'];
       $farmer->gender = $request['gender'];
       $farmer->guardian_name = $request['guardian_name'];    
       $farmer->status_onboarding = 'Approved';  
       $farmer->save();
       $PlotDetail = (array) $request->plot_detail;
        foreach($PlotDetail as $value){
           $plot = new FarmerPlot;
           $plot->farmer_id        =  $farmer->id;
           $plot->farmer_uniqueId  =  $request->farmer_uniqueId;
           $plot->farmer_plot_uniqueid = $request->farmer_uniqueId.'P'.$value['sr'];
           $plot->plot_no          =  $value['sr'];// floatval($value['sr']);  //$value->sr;
           $plot->area_in_acers    =  $value['area_in_hectare']; //$value->area_in_hectare;
           $plot->area_in_other    =  $value['area_in_other']; //$value->area_in_hectare;
           $plot->area_in_other_unit   =  $value['area_in_other_unit']; //$value->area_in_hectare;
           $plot->save();

           $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           => $request['farmer_uniqueId'],
                        'plot_no'                   => $value['sr'],
                        'farmer_plot_uniqueid'      => $request->farmer_uniqueId.'P'.$value['sr'],
                        'level'                     => 'AppUser',
                        'status'                    => 'Pending',
                        'comment'                   => 'Onboarding',
                        'timestamp'                 => Carbon::now(),
                        'user_id'                   => auth()->user()->id,
                    ]);
        }
       if(!$plot){
        return response()->json(['error'=>true,'message'=>'Somethings went wrong']);
       }
       return response()->json(['success'=>true,'message'=>'Farmer Store Successfully',
                                'FarmerId'=>$farmer->id, 'FarmerUniqueID'=>$request->farmer_uniqueId],200);
    }catch(Exception $e){
       return response()->json(['error'=>true,'message'=>'Somethings went wrong']);
    }
   }

   /**
   * Farmer store
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function StoreImage(Request $request){
    //   store farmer images
        $img = new FarmerImage;
        $img->farmer_id   =       $request->farmer_id;
        $img->farmer_unique_id   = $request->farmer_unique_id;
        $img->image   =       'profile';
        $img->path   = $request->image->storeAs('plot/'.$request->farmer_unique_id, $request->file('image')->getClientOriginalName(), 'public');
        $img->save();
        if(!$img){
            return response()->json(['error'=>true],500);
        }
      return response()->json(['success'=>true,'message'=>'successfull'],200);
   }

   /**
   * Farmer store location info
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function storeLocation(Request $request){
     $validator = Validator::make($request->all(),[
         'latitude' => 'required',
         'longitude' => 'required',
     ]);
     try{
        //  location screen data store
        $Farmer  = Farmer::where('id',$request->farmer_id)->first();
        $state = State::where('id',$request->state_id)->first();
            $Farmer->state        = $state->name;
            $Farmer->state_id     = $request->state_id;
        $Farmer->country      = $state->countryname->name;
        $Farmer->country_id   = $state->country_id;//we have countries table in India has 101 as ID
        $district = District::where('id',$request->district_id)->first();
            $Farmer->district     = $district->district;
            $Farmer->district_id  = $request->district_id;
        $taluka = Taluka::where('id',$request->taluka_id)->first();
            $Farmer->taluka       = $taluka->taluka;
            $Farmer->taluka_id    = $request->taluka_id;
        $Panchayat = Panchayat::whereId($request->panchayat_id)->first();
            $Farmer->panchayat       = $Panchayat->panchayat;
            $Farmer->panchayat_id    = $request->panchayat_id;
        $village = Village::where('id',$request->village_id)->first();
            $Farmer->village      = $village->village;
            $Farmer->village_id   = $request->village_id;
        $Farmer->latitude     = $request->latitude;
        $Farmer->longitude    = $request->longitude;
        $Farmer->remarks      = $request->remarks??'NA';
        $Farmer->save();
       if(!$Farmer){
         return response()->json(['error'=>true,'message'=>'something went wrong'],500);
       }
       return response()->json(['success'=>true,'farmerId'=>$Farmer->id,'farmerUniqueId'=>$Farmer->farmer_uniqueId],200);
     }catch(Exception $e){
       return response()->json(['error'=>true,'message'=>'something went wrong'],500);
     }
   }

   /**
   * Farmer store location info
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function storePlot(Request $request){
    $validator = Validator::make($request->all(),[
        'land_ownership' => 'required',
        'survey_no' => 'required',
    ]);
    try{
       //  store plot data individually from app
       $FarmerPlot  = FarmerPlot::where('farmer_uniqueId',$request->farmer_unique_id)->where('plot_no',$request->plot_no)->first();
       $FarmerPlot->survey_no             = $request->survey_no;
       $FarmerPlot->land_ownership        = $request->land_ownership;
       
       //we need to make l1 validator by default approved
       $FarmerPlot->aprv_recj_userid        = 1;// this is admin 1
       $FarmerPlot->appr_timestamp        = Carbon::now();//this is because we want a survey to display directly in l2 validator.
       $FarmerPlot->status       = 'Approved';  
       
             
       $FarmerPlot->area_acre_awd    =  $request->area_acre_awd; //$value->area_in_hectare;
       $FarmerPlot->area_other_awd    =  $request->area_other_awd; //$value->area_in_hectare;
       $FarmerPlot->area_other_awd_unit   = $FarmerPlot->area_in_other_unit; //$request->area_other_awd_unit; 

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
       }else{
          $farmername = Farmer::where('id',$request->farmer_id)->select('farmer_name')->first();
          $FarmerPlot->actual_owner_name     = $farmername->farmer_name;
       }
       if($request->land_ownership == 'Leased'){
          $FarmerPlot->affidavit_tnc         = '1';
          $FarmerPlot->sign_affidavit_date   = Carbon::now();
       }elseif($request->land_ownership == 'Own'){
          $FarmerPlot->affidavit_tnc         = '0';
       }
       // if ($request->hasFile('sign_affidavit')) {
       //  $FarmerPlot->sign_affidavit = $request->sign_affidavit->storeAs('plot/'.$request->farmer_unique_id, 'affidavit-sign'.$request->file('sign_affidavit')->getClientOriginalName(), 'public');

       // }

       $FarmerPlot->save();
       if ($request->hasFile('signature')){
           
           $path = $request->signature->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'signature-'.$request->file('signature')->getClientOriginalName(), 'public'); //Storage::putFile('public/'.config('storagesystems.store').'/'.$request->farmer_unique_id,  $request->image);
           Farmer::where('id',$request->farmer_id)->update(['check_carbon_credit'=>'1',
                                                          'signature'=> asset('storage/'.$path),//$request->signature->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'signature-'.$request->file('signature')->getClientOriginalName(), 'public'),
                                                        //'signature'=>Storage::disk('s3')->put(config('storagesystems.store').'/'.$request->farmer_unique_id, $request->signature),
                                                        'sign_carbon_date'=>Carbon::now()]);
       }
       DB::table('farmers')->where('id',$request->farmer_id)->update(['updated_at'=>carbon::now()]);//update timestamp
      if(!$FarmerPlot){
        return response()->json(['error'=>true,'message'=>'something went wrong'],500);
      }
      return response()->json(['success'=>true,'farmerId'=>$FarmerPlot->farmer_id,'farmerUniqueId'=>$FarmerPlot->farmer_uniqueId],200);
    }catch(Exception $e){
     return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
    }
  }

   /**
   * Farmer store land images
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function FarmerPlotImage(Request $request){
      try{
          //to store plot image
            $img = new FarmerPlotImage;
            $img->farmer_id   =       $request->farmer_id;
            $img->farmer_unique_id   = $request->farmer_unique_id;
            $img->plot_no   = $request->sr;
            $img->image   =       'landrecords';
            $path = $request->image->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id, 'plotImg-'.$request->sr.'-'.$request->file('image')->getClientOriginalName(), 'public'); //Storage::putFile('public/'.config('storagesystems.store').'/'.$request->farmer_unique_id,  $request->image);
            // dd($path, asset('storage/'.$path), strlen(asset('storage/'.$path)));
            $img->path   = asset('storage/'.$path);//$request->image->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id, 'plotImg-'.$request->sr.'-'.$request->file('image')->getClientOriginalName(), 'public');//
            $img->status   = 'Approved';
            $img->save();
            if(!$img){
                return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
            }
          return response()->json(['success'=>true,'farmerId'=>$img->farmer_id,'farmerUniqueId'=>$img->farmer_unique_id],200);
        }catch(Exception $e){
          return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
        }
   }

   /**
   * Farmer store land images
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function getnc(){
       //api for affidavit and carbon credit
    $setting = Setting::select('terms_and_conditions','carbon_credit')->find(1);
    return response()->json($setting);
   }


//   public function test_generate_unique_plotid(){
//         // $farmer = DB::table('farmer_plot_detail')->get();

//     $farmerplots =FarmerPlot::with('farmer')->whereHas('farmer',function($q){
//         $q->where('onboarding_form','1');
//         return $q;
//     });

//         foreach($farmerplots as $plot){
//             dd($plot);
//         }
//   }
}
