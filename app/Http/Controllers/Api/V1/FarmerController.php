<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Company;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerImage;
use App\Models\FarmerPlotImage;
use App\Models\Panchayat;
use App\Models\Village;
use App\Models\Taluka;
use App\Models\District;
use App\Models\State;
use App\Models\FinalFarmer;
use App\Models\Setting;
use App\Models\FinalFarmerPlotImage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use PDF;
use App\Models\DocumentType;
use App\Models\FarmerConsentForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PlotStatusRecord;
use App\Models\FarmerQuestion;
use App\Models\FarmerFarmDetails;
use App\Models\UserTarget;
use Illuminate\Support\Facades\Storage ;

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
    $time = DB::table('settings')->select('otpTime')->where('id', 1)->first();
    $sms_key = DB::table('app_settings')->select('value')->where('title', 'sms_key')->first();
    //create otp record in db
    $user_otp = DB::insert('insert into register_otp (mobile, otp, otp_time,status,ip,created_at) values (?, ?, ?, ?, ?,?)', [$request->mobile, $otp, $time->otpTime, 'Checking', $_SERVER['REMOTE_ADDR'] ?? NULL, carbon::now()]);
    $curl = curl_init(); //initialize curl
    // abd19922e8887923
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.authkey.io/request?authkey=' . $sms_key->value . '&mobile=' . $request->mobile . '&country_code=91&sid=6560&otp=' . $otp . '&time=' . $time->otpTime . '%20mins',
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
      return response()->json(['error' => true, 'message' => 'Something went wrong'], 422);
    } else {
      return response()->json(['success' => true, 'mobile' => $request->mobile, 'otp' => $otp], 200);
    }
  }

  public function search_type()
  {
    $data = DB::table('type_of_search')->select('id','name')->get();

    if(!$data)
    {
      return response()->json([
        'error'=> true,
        "message" => "Data not found",
      ],422);
    }

    return response()->json([
     'sucess'=> true,
     "type" => $data,
   ],200);
  
  }


  public function farmer_details(Request $request)
  {

    // $farmer_details = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)
    // ->select('state','district','taluka','village','panchayat')
    // ->where('plot_no',1)->first();

    $farmer_details = FinalFarmer::with('state', 'district', 'taluka', 'panchayat', 'village')->where('farmer_uniqueId', $request->farmer_uniqueId)
      ->select('state_id', 'district_id', 'taluka_id', 'village_id', 'panchayat_id')
      ->where('plot_no', 1)->first();
      
    if (!$farmer_details) {
      return response()->json([
        'error' => true,
        "message" => "Data not found",
      ], 422);
    }

    if ($farmer_details) {
      $modified_farmer_details = [
        'state' => $farmer_details->state->name,
        'state_id' => $farmer_details->state_id,
        'district' => $farmer_details->district->district,
        'district_id' => $farmer_details->district_id,
        'taluka' => $farmer_details->taluka->taluka,
        'taluka_id' => $farmer_details->taluka_id,
        'panchayat' => $farmer_details->panchayat->panchayat,
        'panchayat_id' => $farmer_details->panchayat_id,
        'village' => $farmer_details->village->village,
        'village_id' => $farmer_details->village_id,
      ];

      $modified_farmer_details = (object) $modified_farmer_details;
    }

    return response()->json([
      'sucess' => true,
      "farmer_details" => $modified_farmer_details,
    ], 200);
  }


  public function documents(Request $request)
  {

    $data = DocumentType::select('id', 'document_name')->get();

    return response()->json([
      'sucess' => true,
      "documents" => $data,
    ], 200);
    if (!$data) {
      return response()->json([
        'error' => true,
        "message" => "Data not found",
      ], 422);
    }
  }



   public function farmer_status(Request $request)
  {

    // $farmer = FarmerFarmDetails::where('farmer_uniqueId', $request->farmer_uniqueId)->select('id', 'farmer_uniqueId', 'final_status')->first();

    // if ($farmer->final_status == "Rejected") {
    //   return response()->json([
    //     'sucess' => true,
    //     "farmer_status" => $farmer,
    //     'message' => 'Data submitted but Famer not eligible for AWD'
    //   ], 200);
    // }

    // if (!$farmer) {
    //   return response()->json(['error' => true, 'message' => 'Somethings went wrong'], 422);
    // }

    // return response()->json([
    //   'sucess' => true,
    //   "farmer_status" => $farmer,
    //   'message' => 'Data Submitted and Famer is eligible for AWD'
    // ], 200);

    // if (!$farmer) {
    //   return response()->json([
    //     'error' => true,
    //     "message" => "Data not found",
    //   ], 422);
    // }

    $farmer = FarmerFarmDetails::where('farmer_uniqueId', $request->farmer_uniqueId)->select('id', 'farmer_uniqueId', 'final_status')->first();

if (!$farmer) {
    return response()->json(['error' => true, 'message' => 'Farmer Details Not Found'], 422);
}

if ($farmer->final_status == "Rejected") {
    return response()->json([
        'success' => true,
        'farmer_status' => $farmer,
        'message' => 'Data submitted but Farmer not eligible for AWD'
    ], 200);
}

return response()->json([
    'success' => true,
    'farmer_status' => $farmer,
    'message' => 'Data Submitted and Farmer is eligible for AWD'
], 200);

  }

  /**
   * User validate otp api
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function validate_otp(Request $request)
  {
    if ($request->mobile == '9820098200') {
      $verify = DB::table('register_otp')->where('mobile', $request->mobile)->where('otp', $request->otp)->first();
      if ($verify) {
        DB::table('register_otp')->where('mobile', $request->mobile)->where('otp', $request->otp)->update(['status' => 'Verified']);
        return response()->json(['success' => true, 'message' => 'Verified Successfully'], 200);
      }
    } else {
      $verify = DB::table('register_otp')->where('mobile', $request->mobile)->where('otp', $request->otp)->first();
      if ($verify) {
        if (Carbon::now() <= Carbon::parse($verify->created_at)->addMinutes(10)) {
          DB::table('register_otp')->where('mobile', $request->mobile)->where('otp', $request->otp)->update(['status' => 'Verified']);
          // $verify->forceDelete();
          return response()->json(['success' => true, 'message' => 'Verified Successfully'], 200);
        } else {
          return response()->json(['success' => true, 'message' => 'Time exceeded 10 mins'], 422);
        }
      }
    }
    return response()->json(['error' => true, 'message' => 'Otp validation failed'], 422);
  }

  /**
   * List all farmer
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function index()
  {
    $farmer = Farmer::all();
    return response()->json($farmer);
  }

  public function get_farmer_data(Request $request)
  {
    try {

      if ($request->has('screen')) {

        if ($request->screen == 1) {
          $farmer = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)
            ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'farmer_name', 'mobile_access', 'mobile_reln_owner', 'mobile', 'gender', 'guardian_name', 'document_no', 'area_in_acers', 'own_area_in_acres', 'lease_area_in_acres',  'state_id','district_id', 'taluka_id', 'panchayat_id','village_id','season','financial_year')
            ->where('plot_no', 1)
            ->with('season:id,name')
            ->first();

            $farmer->state_name = $farmer->state->name ?? null;
            $farmer->district_district = $farmer->district->district ?? null;
            $farmer->taluka_taluka = $farmer->taluka->taluka ?? null;
            $farmer->panchayat_panchayat = $farmer->panchayat->panchayat ?? null;
            $farmer->village_village = $farmer->village->village ?? null;

            unset($farmer->state); 
            unset($farmer->district); 
            unset($farmer->taluka); 
            unset($farmer->panchayat); 
            unset($farmer->village); 

          return response()->json(
            [
              'success' => true,
              'message' => 'farmer data fetched',
              'farmer' => $farmer,
            ],
            200
          );
        }
        if ($request->screen == 2) {
          $farmer_land = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)
            ->select('farmer_plot_uniqueid', 'country_id', 'pincode', 'state_id', 'latitude','longitude', 'district_id', 'taluka_id', 'panchayat_id','village_id','remarks')
            ->where('plot_no', 1)
            ->with('state','district','taluka','panchayat','village')
            ->first();

            $farmer_land->state_name = $farmer_land->state->name ?? null;
            $farmer_land->district_district = $farmer_land->district->district ?? null;
            $farmer_land->taluka_taluka = $farmer_land->taluka->taluka ?? null;
            $farmer_land->panchayat_panchayat = $farmer_land->panchayat->panchayat ?? null;
            $farmer_land->village_village = $farmer_land->village->village ?? null;

        unset($farmer_land->state); 
        unset($farmer_land->district); 
        unset($farmer_land->taluka); 
        unset($farmer_land->panchayat); 
        unset($farmer_land->village); 

          return response()->json(
            [
              'success' => true,
              'message' => 'farmer data fetched',
              'farmer' => $farmer_land,
            ],
            200
          );
        }
        if ($request->screen == 3) {

          $farmer_plot = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)
          ->where('plot_no', 1) // Replace $plot_no with the actual plot number
          ->with(['ApprvFarmerPlot','PlotImages' => function ($query) {
            
            $query->select('id', 'farmer_id', 'farmer_unique_id','image','path','status');
        }]) 
          ->first();

          // dd($farmer_plot);

          if ($farmer_plot) {
            $finalFarmer = $farmer_plot->ApprvFarmerPlot;
            $plotImages = $farmer_plot->PlotImages; 
            // $farmer= $finalFarmer->farmer_id;
            // $farmer = $farmer_plot->farmer_uniqueId;

            $data = [
              'farmer_id' => $finalFarmer->farmer_id ?? "",
              'farmer_uniqueId' => $farmer_plot->farmer_uniqueId,
              'farmer_plot_uniqueid' => $farmer_plot->farmer_plot_uniqueid,
              'area_in_acres' => $farmer_plot->area_in_acers,
              'plot_no' => $farmer_plot->plot_no,
              'land_ownership' => $finalFarmer->land_ownership ?? "",
              'actual_owner_name' => $finalFarmer->actual_owner_name ?? "",
              'patta_number' => $finalFarmer->patta_number ?? "",
              'daag_number' => $finalFarmer->daag_number ?? "",
              'khatha_number' => $finalFarmer->khatha_number ?? "",
              'pattadhar_number' => $finalFarmer->pattadhar_number ?? "",
              'khatian_number' => $finalFarmer->khatian_number ?? "",
              'survey_no' => $finalFarmer->survey_no ?? "",
            ];

            return response()->json([
              'success' => true,
              'message' => 'Farmer data fetched',
              'farmer' => $data,
              'plot_images'=> $plotImages,
            ], 200);
          } else {
            return response()->json(
              [
                'error' => true,
                'message' => 'No data found'
              ],
              200
            );
          }
        }


        if ($request->screen == 4) {
          $farmer_data = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)
              ->select('farmer_plot_uniqueid', 'signature', 'others_photo', 'aadhaar_photo', 'aadhaar_back_photo','farmer_photo', 'plotowner_sign', 'time_survey', 'date_survey')
              ->where('plot_no', 1)
              ->first();
      
          $farmerConsentForm = FarmerConsentForm::select('id','farmer_uniqueId','images','plot_no','index')->where('farmer_uniqueId', $request->farmer_uniqueId)->get();
      
          if ($farmer_data) {
              $farmer_data = $farmer_data->toArray(); 
              $farmer_data['farmerConsentForm'] = $farmerConsentForm;
          }
      
          return response()->json(
              [
                  'success' => true,
                  'message' => 'farmer data fetched',
                  'farmer' => $farmer_data,
              ],
              200
          );
      }
      

        if ($request->screen == 5) {
          $farmer_farm_data = FarmerFarmDetails::with('variety:id,name')->where('farmer_uniqueId', $request->farmer_uniqueId)
            ->select('id','farmer_uniqueId', 'irigation_source', 'struble_burning', 'double_paddy_status', 'soil_type', 'variety', 'flooding_type', 'proper_drainage','awd_previous','awd_previous_no','community_benefit')
            // ->where('plot_no', 1)
            ->first();
          return response()->json(
            [
              'success' => true,
              'message' => 'farmer data fetched',
              'farmer' => $farmer_farm_data,
            ],
            200
          );
        }


      } else {

        $farmers = FinalFarmer::with('ApprvFarmerPlot:farmer_plot_uniqueid,area_in_other,area_in_other_unit,area_acre_awd,area_other_awd,area_other_awd_unit')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no', 1)
          ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'farmer_name', 'mobile_access', 'mobile_reln_owner', 'mobile', 'gender', 'guardian_name', 'document_no', 'area_in_acers', 'own_area_in_acres', 'lease_area_in_acres', 'plot_no', 'land_ownership', 'country', 'state', 'state_id', 'district', 'district_id', 'taluka', 'taluka_id', 'panchayat', 'panchayat_id', 'village_id', 'village', 'signature', 'others_photo', 'aadhaar_photo', 'farmer_photo', 'plotowner_sign', 'time_survey', 'date_survey')
          ->first();

        return response()->json(
          [
            'success' => true,
            'message' => 'farmer data fetched',
            'farmer' => $farmers,
          ],
          200
        );
      }

      
    } catch (\Exception $e) {
      // dd($e);
      return response()->json(['error' => true, 'message' => 'Somethings went wrong'], 422);
    }
  }





  public function get_questions(Request $request)
  {
    $questions = FarmerQuestion::with(['values' => function ($query) {
      $query->where('status', 1)->select('farmer_question_id', 'id', 'question_value');
    }])
      ->select('id', 'question_text')
      ->get();

    // Return JSON response
    return response()->json(['questions' => $questions]);
  }




  public function store(Request $request)
  {
    try {
      //      $validator = Validator::make($request->all(),[
      //          'farmer_name' => 'required|string',
      //         //  'email' => 'required|email|unique:farmers',
      //          'mobile' => 'required|unique:users',
      //      ]);
      //      if ($validator->fails()) {
      //       return response()->json(['message' => $validator->messages()->first()], 400);
      //   }

      $existing_farmer = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no', 1)->first();

      if (!$existing_farmer) {
        $farmer = new FinalFarmer;
        $farmer->surveyor_id  = auth()->user()->id;
        // $farmer->surveyor_name  = auth()->user()->name;
        // $farmer->surveyor_email  = auth()->user()->email??NULL;
        // $farmer->surveyor_mobile  = auth()->user()->mobile;
        $farmer->farmer_name  = $request['farmer_name'];
        $farmer->mobile_access = $request['mobile_access'];
        $farmer->mobile_reln_owner = $request['mobile_reln_owner'] ?? "NA";
        $farmer->mobile = $request['mobile'];
        $farmer->mobile_verified = '1';
        $farmer->document_no = $request['document_no'];
        $farmer->farmer_uniqueId = $request['farmer_uniqueId'];
        $farmer->farmer_plot_uniqueid = $request->farmer_uniqueId . 'P1';
        $farmer->plot_no = 1;
        // $farmer->no_of_plots = $request['no_of_plots'];
        $farmer->organization_id = $request['organization_id'];
        $farmer->gender = $request['gender'];
        $farmer->guardian_name = $request['guardian_name'];
        $farmer->status_onboarding = 'Pending';
        $farmer->final_status_onboarding = 'Pending'; // need to do direct approval so that it is easily available for crop data
        $farmer->onboarding_form      = 1;
        $farmer->area_in_acers  =  $request->area_in_acers;
        $farmer->own_area_in_acres =  $request->own_area_in_acres ?? null;
        $farmer->lease_area_in_acres = $request->lease_area_in_acres ?? null;
        $farmer->available_area =      $request->area_in_acers;
        $farmer->document_id = $request->document_id ?? null;
        $farmer->final_status       = 'Pending';
        // $farmer->L2_aprv_timestamp      = Carbon::now(); //by default adding current time in approval time
        // $farmer->L2_appr_userid      = 1;
        // $farmer->L1_appr_timestamp      = Carbon::now();//by default adding current time in approval time
        // $farmer->L1_aprv_recj_userid      = 1;
        $farmer->onboard_completed      = 'Processing';
        $farmer->save();

        if (!$farmer) {
          return response()->json(['error' => true, 'message' => 'Somethings went wrong'], 422);
        }
        return response()->json([
          'success' => true, 'message' => 'Farmer Store Successfully',
          'FarmerId' => $farmer->id, 'FarmerUniqueID' => $request->farmer_uniqueId
        ], 200);
      } else {
        return response()->json(['error' => true, 'message' => 'Farmer already onboarded'], 422);
      }
    } catch (\Exception $e) {

      return response()->json(['error' => true, 'message' => 'Somethings went wrong'], 422);
    }
  }


  public function generate_plot_id(Request $request)
  {
    try {
      // Check if a farmer with the given farmer_uniqueId exists
      $farmer = DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_uniqueId)->first();

      if ($farmer) {
        // Find the last plot_id for this farmer
        $last_plot = DB::table('polygons')
          ->where('farmer_uniqueId', $request->farmer_uniqueId)
          ->orderBy('id', 'desc')
          ->first();
        // dd($last_plot);

        if (!$last_plot || empty($last_plot->farmer_plot_uniqueid)) {
          // If no previous plot_id exists, create the first one as "P1"
          $new_plot_id_value =  1;
          $new_plot_id = $request->farmer_uniqueId . 'P'.$new_plot_id_value;
        } else {
          // Extract the last plot_id value and increment it
          $last_plot_id = $last_plot->farmer_plot_uniqueid;
          preg_match('/(\d+)$/', $last_plot_id, $matches);
          $last_plot_id_value = (int)$matches[0];
          $new_plot_id_value = $last_plot_id_value + 1;
          $new_plot_id = $request->farmer_uniqueId . 'P' . $new_plot_id_value;
        }

        // Update the new plot_id for the farmer in the database
        // DB::table('final_farmers')->where('id', $farmer->id)->update(['plot_id' => $new_plot_id]);

        return response()->json(['message' => 'Plot ID generated successfully', 'plot_id' => $new_plot_id, 'plot_no' => $new_plot_id_value], 200);
      } else {
        return response()->json(['message' => 'Farmer not found'], 422);
      }
    } catch (\Exception $e) {
      // dd($e);
      return response()->json(['message' => 'An error occurred'], 400);
    }
  }

  public function store_farm_details(Request $request)
  {
    try {
      // Find the farmer data by unique ID
      $checkfarmer = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->first();
      // dd($checkfarmer);


      if (!$checkfarmer) {
        return response()->json(['error' => true, 'message' => 'Farmer data not found'], 404);
      }

      $farmer = new FarmerFarmDetails;
      $farmer->farmer_uniqueId = $request->farmer_uniqueId;
      $farmer->irigation_source = $request->irigation_source;
      $farmer->struble_burning = $request->struble_burning;
      $farmer->double_paddy_status = $request->double_paddy_status;
      $farmer->soil_type = $request->soil_type;
      $farmer->variety = $request->variety;
      $farmer->proper_drainage = $request->proper_drainage;
      $farmer->awd_previous = $request->awd_previous;
      $farmer->community_benefit = $request->community_benefit;
      $farmer->flooding_type = $request->flooding_type;
      $farmer->awd_previous_no = $request->awd_previous_no ?? "0";
     
      $farmer->save();

      
      $checkfarmer->onboard_completed  = 'Pending';
      $checkfarmer->save();

      // $userTarget = UserTarget::updateOrCreate(
      //       [
      //         'user_id' => auth()->user()->id,
      //         'module_id' => 1,
      //         'module_name' => 'onboarding',
      //         'date' => now()->toDateString(),
      //       ],
            
      //     [
      //         'count' => DB::raw('count + 1'),
      //     ]
      // );

      if ($farmer->irigation_source == "Rainfed" || $farmer->struble_burning == "Yes" ||  $farmer->soil_type == "Saline") {
        $checkfarmer->final_status_onboarding = "Rejected";
        $checkfarmer->status_onboarding = "Rejected";
        $checkfarmer->final_status = "Rejected";
        $checkfarmer->L2_reject_timestamp = Carbon::now();
        $checkfarmer->L2_reject_userid = '1';
        
        $checkfarmer->save();
        $farmer->save();
        $farmer->final_status = "Rejected";
        $userTarget = UserTarget::updateOrCreate(
          [
            'user_id' => auth()->user()->id,
            'module_id' => 1,
            'module_name' => 'onboarding',
            'date' => now()->toDateString(),
          ],
          
        [
            'count' => DB::raw('count + 1'),
        ]
    );
        // dd($farmer, $checkfarmer)
        // dd($userTarget);
        return response()->json(['error' => true, 'message' => 'Data submitted but Famer not eligible for AWD'], 423);
      }
      return response()->json(['success' => true, 'message' => 'Data Submitted and Famer is eligible for AWD'], 200);
    } catch (\Exception $e) {
      return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
    }
  }


  public function number_of_season()
  {
    $numbers = range(1, 10);
    return response()->json(['success' => true, 'message' => 'Number fetch successfully', 'number' => $numbers], 200);
    // return $numbers;
  }



  public function store_image__last_screen(Request $request)
  {
    // dd($request->all());
    try {
      // dd($request->has('screen'));
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
          // $farmer->onboard_completed  = 'Pending';
          $farmer->onboard_completed      = 'Processing';
          if ($request->hasFile('plotowner_sign')) {
            $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->plotowner_sign);
            $farmer->plotowner_sign        =  Storage::disk('s3')->url($path);
          }

          $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->farmer_photo);
          $farmer->farmer_photo        =  Storage::disk('s3')->url($path);

          $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->aadhaar_photo);
          $farmer->aadhaar_photo        =  Storage::disk('s3')->url($path);
          // $path=$request->farmer_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'farmerphoto-'.$request->file('farmer_photo')->getClientOriginalName(), 'public');
          // $farmer->farmer_photo  = asset('storage/'.$path);

          // $path=$request->aadhaar_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'aadhaarphoto-'.$request->file('aadhaar_photo')->getClientOriginalName(), 'public');
          // $farmer->aadhaar_photo  = asset('storage/'.$path);
          if ($request->hasFile('others_photo')) {

            $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->others_photo);
            $farmer->others_photo        =  Storage::disk('s3')->url($path);
            // $path= $request->others_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'othersphoto-'.$request->file('others_photo')->getClientOriginalName(), 'public');
            // $farmer->others_photo  = asset('storage/'.$path);
          }
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
      // dd($e);
      return response()->json(['error' => true, 'message' => 'Somethings went wrong']);
    }
  }


  

  /**
   * Farmer store
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function StoreImage(Request $request)
  {
    //   store farmer images
    $img = new FarmerImage;
    $img->farmer_id   =       $request->farmer_id;
    $img->farmer_unique_id   = $request->farmer_unique_id;
    $img->image   =       'profile';
    $img->path   = $request->image->storeAs('plot/' . $request->farmer_unique_id, $request->file('image')->getClientOriginalName(), 'public');
    $img->save();
    if (!$img) {
      return response()->json(['error' => true], 500);
    }
    return response()->json(['success' => true, 'message' => 'successfull'], 200);
  }

  /**
   * Farmer store location info
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function storeLocation(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'latitude' => 'required',
      'longitude' => 'required',
    ]);
    try {
      //  location screen data store
      $Farmer_data  = DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_uniqueId)->get();
      foreach ($Farmer_data as $items) {
        $Farmer = FinalFarmer::where('id', $items->id)->first();
        $state = State::where('id', $request->state_id)->first();
        // $Farmer->state        = $state->name;
        $Farmer->state_id     = $request->state_id;
        // $Farmer->country      = $state->countryname->name;
        $Farmer->country_id   = $state->country_id; //we have countries table in India has 101 as ID
        $district = District::where('id',$request->district_id)->first();
        // $Farmer->district     = $district->district;
        $Farmer->district_id  = $request->district_id;
        $taluka = Taluka::where('id',$request->taluka_id)->first();
        // $Farmer->taluka       = $taluka->taluka;
        $Farmer->taluka_id    = $request->taluka_id;
        // $Panchayat = Panchayat::whereId($request->panchayat_id)->first();
        // $Farmer->panchayat       = $Panchayat->panchayat;
        $Farmer->panchayat_id    = $request->panchayat_id;
        $village = Village::where('id',$request->village_id)->first();
        // $Farmer->village      = $village->village;
        $Farmer->village_id   = $request->village_id;
        $Farmer->latitude     = $request->latitude;
        $Farmer->longitude    = $request->longitude;
        $Farmer->remarks      = $request->remarks ?? 'NA';
        $Farmer->pincode      = $request->pincode ?? NULL;
        $Farmer->farmer_survey_id = substr($district->district, 0, 2).'/'.substr($taluka->taluka, 0, 3).'/'.substr($village->village, 0, 3).'/'.$request->farmer_uniqueId;
        $Farmer->save();
      }
      if (!$Farmer_data) {
        return response()->json(['error' => true, 'message' => 'something went wrong'], 500);
      }
      return response()->json(['success' => true, 'farmerId' => $Farmer_data->first()->id, 'farmerUniqueId' => $Farmer_data->first()->farmer_uniqueId], 200);
    } catch (Exception $e) {
      return response()->json(['error' => true, 'message' => 'something went wrong'], 500);
    }
  }

  

  public function storePlot(Request $request)
  {
    // dd($request->all());
    $validator = Validator::make($request->all(), [
      'land_ownership' => 'required',
      'survey_no' => 'required',
    ]);
    try {
      // dd("in");
      //store plot data individually from app

      $farmer = DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->first();

      $farmerPlotExists = FarmerPlot::where('farmer_uniqueId', $request->farmer_unique_id)
        ->where('plot_no', 1)
        ->exists();

      if ($farmerPlotExists) {
        return response()->json(['error' => true, 'message' => 'Plot Created'], 200);
      }

      if ($farmer) {
        $FarmerPlot  = new FarmerPlot;
        $FarmerPlot->farmer_id        =  $farmer->id;
        $FarmerPlot->farmer_uniqueId  =  $request->farmer_unique_id;
        $FarmerPlot->farmer_plot_uniqueid = $FarmerPlot->farmer_uniqueId . 'P1';
        $FarmerPlot->plot_no = 1;
        $FarmerPlot->area_in_acers    =  $farmer->area_in_acers; //always in acers
        $FarmerPlot->area_in_other    =  $request->area_in_other ?? "0.00";
        $FarmerPlot->area_in_other_unit   =  $request->area_in_other_unit ?? "0.00";; // record the converted value units
        $FarmerPlot->survey_no             = $request->survey_no;
        $FarmerPlot->land_ownership        = $request->land_ownership;
        //we need to make l1 validator by default approved
        // $FarmerPlot->aprv_recj_userid      = 1; // this is admin 1
        // $FarmerPlot->appr_timestamp        = Carbon::now(); //this is because we want a survey to display directly in l2 validator.
        $FarmerPlot->status                = 'Pending';
        $FarmerPlot->final_status          = 'Processing';
        // $FarmerPlot->finalaprv_timestamp   = Carbon::now();
        // $FarmerPlot->finalappr_userid      = 1;
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
          DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['actual_owner_name' => $request->actual_owner_name]);
        } else {
          $farmername = FinalFarmer::where('farmer_uniqueId', $request->farmer_unique_id)->select('farmer_name')->first();
          $FarmerPlot->actual_owner_name     = $farmername->farmer_name;
          DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['actual_owner_name' => $farmername->farmer_name]);
        }

        if ($request->land_ownership == 'Leased') {
          $FarmerPlot->affidavit_tnc         = '1';
          $FarmerPlot->sign_affidavit_date   = Carbon::now();
          DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['land_ownership' => 'Leased']);
        } elseif ($request->land_ownership == 'Own') {
          $FarmerPlot->affidavit_tnc         = '0';
          DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['land_ownership' => 'Own']);
        }
        // if ($request->hasFile('sign_affidavit')) {
        //  $FarmerPlot->sign_affidavit = $request->sign_affidavit->storeAs('plot/'.$request->farmer_unique_id, 'affidavit-sign'.$request->file('sign_affidavit')->getClientOriginalName(), 'public');

        // }

        $FarmerPlot->save();
        //   signature moved in last screen

        //   if ($request->hasFile('signature')){
        //     $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_unique_id.'/'.'DOCUMENTS', $request->signature);

        //       //  $path = $request->signature->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'signature-'.$request->file('signature')->getClientOriginalName(), 'public'); //Storage::putFile('public/'.config('storagesystems.store').'/'.$request->farmer_unique_id,  $request->image);
        //       FinalFarmer::where('farmer_uniqueId',$request->farmer_unique_id)->update(['check_carbon_credit'=>'1',
        //                                                       'signature'=> Storage::disk('s3')->url($path), //$request->signature->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'signature-'.$request->file('signature')->getClientOriginalName(), 'public'),
        //                                                     //'signature'=>Storage::disk('s3')->put(config('storagesystems.store').'/'.$request->farmer_unique_id, $request->signature),
        //                                                     'sign_carbon_date'=>Carbon::now()]);
        //   }



        DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['updated_at' => carbon::now()]); //update timestamp
      }
      if (!$farmer) {

        return response()->json(['error' => true, 'message' => 'something went wrong'], 500);
      }
      return response()->json(['success' => true, 'farmerId' => $FarmerPlot->farmer_id, 'farmerUniqueId' => $FarmerPlot->farmer_uniqueId], 200);
    } catch (Exception $e) {
      return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
    }
  }
  /**
   * Farmer store land images
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function FarmerPlotImage(Request $request)
  {
    try {
      //to store plot image
      $img = new FinalFarmerPlotImage;
      $img->farmer_id   =       $request->farmer_id;
      $img->farmer_unique_id   = $request->farmer_unique_id;
      $img->plot_no   = $request->sr?? "1";
      $img->image   =       'landrecords';
      $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_unique_id . '/' . $request->farmer_unique_id . 'P' . $img->plot_no . '/' . 'ONBOARDPLOT', $request->image); //Storage::putFile('public/'.config('storagesystems.store').'/'.$request->farmer_unique_id,  $request->image);
      $img->path   = Storage::disk('s3')->url($path); //$request->image->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id, 'plotImg-'.$request->sr.'-'.$request->file('image')->getClientOriginalName(), 'public');//


      // dd($path, asset('storage/'.$path), strlen(asset('storage/'.$path)));
      // $path = Storage::disk('s3')->put(config('storagesystems.final_store').'/'.$request->farmer_uniqueId.'/'.'P'.$request->plot_no.'/'.'PipeInstalltion', $request->images);
      // $pipe_img_update->images        =  Storage::disk('s3')->url($path);


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

  /**
   * Farmer store land images
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function getnc()
  {
    //api for affidavit and carbon credit
    $setting = Setting::select('terms_and_conditions', 'carbon_credit')->find(1);
    $html1 = $setting->terms_and_conditions;
    $html2 = $setting->carbon_credit;

    $user = User::where('id',auth()->user()->id)->first();
    $company = Company::where('id', $user->company_id)->first();

    $organization_name = $company->company;
    // dd($setting->terms_and_conditions , $organization_name);

    $html = str_replace('VARIABLE', $organization_name, $html1);
    $data = str_replace('VARIABLE', $organization_name, $html2);

    $response = [
      'terms_and_conditions' => $html,
      'carbon_credit' => $data
  ];

    return response()->json($response);
  }


 

  /**
   * Farmer store land images
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function search_survey_list()
  {
    $data = FinalFarmer::select(
      'id',
      'farmer_uniqueId',
      'document_no',
      'farmer_survey_id',
      'farmer_name',
      'mobile_access',
      'mobile_reln_owner',
      'mobile',
      'gender',
      'guardian_name',
      'country_id',
      'country',
      'state_id',
      'state',
      'district_id',
      'district',
      'taluka_id',
      'taluka',
      'panchayat_id',
      'panchayat',
      'village_id',
      'village'
    )
      ->where('mobile', 'like', '%' . request()->data . '%')
      ->orWhere('document_no', 'like', '%' . request()->data . '%')
      ->orWhere('farmer_uniqueId', 'like', '%' . request()->data . '%')
      ->orWhere('farmer_survey_id', 'like', '%' . request()->data . '%')
      ->where('onboarding_form', '1')->orderBy('id', 'DESC')
      ->groupBy('farmer_uniqueId')
      ->first();

    if ($data) {
      $latest_plot = FinalFarmer::where('farmer_uniqueId', $data->farmer_uniqueId)->select('farmer_uniqueId', 'plot_no')->orderBy('id', 'desc')->first();
      $plot = FinalFarmer::where('farmer_uniqueId', $data->farmer_uniqueId)->select('farmer_uniqueId', 'plot_no')->get();
    }
    return response()->json(['success' => true, 'survey_id' => $data, 'plots' => $plot ?? NULL, 'latest_plot' => $latest_plot->plot_no ?? NULL], 200);
  }


  /**
   * Function is used to add more plot to existing farme rplots
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function onboard_existing(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'email' => 'required|email|unique:farmers',
      'mobile' => 'required|unique:users',
    ]);
    try {
      if ($request->has('screen')) { //for last screen upload data
        //below code will be active when, last screen data is being send from app
        if (version_compare(phpversion(), '7.1', '>=')) {
          ini_set('precision', 17);
          ini_set('serialize_precision', -1);
        }
        $total_plot = 0;
        $farmers_data = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->get();
        foreach ($farmers_data as $item) {
          $farmer = FinalFarmer::where('id', $item->id)->first();

          $farmerplot = FarmerPlot::where('farmer_uniqueId', $item->farmer_uniqueId)->sum('area_acre_awd'); //->where('plot_no',$item->plot_no)
          $farmer->total_plot_area = number_format((float) $farmerplot, 2);
          $farmer->no_of_plots =    FarmerPlot::where('farmer_uniqueId', $item->farmer_uniqueId)->count();
          // $farmer->save();


          $farmer->date_survey  = $request->date_survey;
          $farmer->time_survey  = $request->time_survey;
          $farmer->onboarding_form  = '1';

          if ($request->hasFile('plotowner_sign')) {
            $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->plotowner_sign);
            $farmer->plotowner_sign        =  Storage::disk('s3')->url($path);
          }

          $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->farmer_photo);
          $farmer->farmer_photo        =  Storage::disk('s3')->url($path);

          $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->aadhaar_photo);
          $farmer->aadhaar_photo        =  Storage::disk('s3')->url($path);
          // $path=$request->farmer_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'farmerphoto-'.$request->file('farmer_photo')->getClientOriginalName(), 'public');
          // $farmer->farmer_photo  = asset('storage/'.$path);

          // $path=$request->aadhaar_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'aadhaarphoto-'.$request->file('aadhaar_photo')->getClientOriginalName(), 'public');
          // $farmer->aadhaar_photo  = asset('storage/'.$path);
          if ($request->hasFile('others_photo')) {

            $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_uniqueId . '/' . 'DOCUMENTS', $request->others_photo);
            $farmer->others_photo        =  Storage::disk('s3')->url($path);
            // $path= $request->others_photo->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'othersphoto-'.$request->file('others_photo')->getClientOriginalName(), 'public');
            // $farmer->others_photo  = asset('storage/'.$path);
          }
          // dd($farmer);
          $farmer->save();
        } //endof foreach
        if (!$farmers_data) {
          return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
        }
        return response()->json(['success' => true, 'message' => 'Saved Succesfull'], 200);
      } // code for last screen update


      //when api first hit from app for onboarding then this below code is used.

      //now directly uploading data to finalfarmer table
      //this way it will be directly available for crop data and further 
      $PlotDetail = (array) $request->plot_detail;
      foreach ($PlotDetail as $value) {
        $farmer = new FinalFarmer;
        $farmer->surveyor_id  = auth()->user()->id;
        $farmer->surveyor_name  = auth()->user()->name;
        $farmer->surveyor_email  = auth()->user()->email ?? NULL;
        $farmer->surveyor_mobile  = auth()->user()->mobile;
        $farmer->farmer_name  = $request['farmer_name'];
        $farmer->mobile_access = $request['mobile_access'];
        $farmer->mobile_reln_owner = $request['mobile_reln_owner'] ?? "NA";
        $farmer->mobile = $request['mobile'];
        $farmer->mobile_verified = '1';
        $farmer->aadhaar = $request['aadhar'];
        $farmer->farmer_uniqueId    =     $request['farmer_uniqueId'];
        $farmer->farmer_plot_uniqueid     =     $request->farmer_uniqueId . 'P' . $value['sr'];
        $farmer->farmer_survey_id     =     $request->farmer_survey_id;
        $farmer->plot_no = $value['sr'];
        $farmer->no_of_plots = $request['no_of_plots'];
        $farmer->organization_id = $request['organization_id'];
        $farmer->gender = $request['gender'];
        $farmer->guardian_name = $request['guardian_name'];
        $farmer->status_onboarding = 'Approved';
        $farmer->final_status_onboarding = 'Approved'; // need to do direct approval so that it is easily available for crop data
        $farmer->onboarding_form      = 1;
        $farmer->area_in_acers      = $value['area_in_hectare']; //area_in_hectare its name is in hectare but actual value is in acres
        $farmer->final_status       = 'Approved';
        $farmer->L2_aprv_timestamp      = Carbon::now(); //by default adding current time in approval time
        $farmer->L2_appr_userid      = 1;
        // $farmer->L1_appr_timestamp      =       Carbon::now(); //by default adding current time in approval time
        // $farmer->L1_aprv_recj_userid      = 1;
        $farmer->save();
        $farmer_id = $farmer->id;

        //now creating data for plot details, with connection to finalfarmer table
        $plot = new FarmerPlot;
        $plot->farmer_id        =  $farmer->id;
        $plot->farmer_uniqueId  =  $request->farmer_uniqueId;
        $plot->farmer_plot_uniqueid = $request->farmer_uniqueId . 'P' . $value['sr'];
        $plot->plot_no          =  $value['sr'];
        $plot->area_in_acers    =  $value['area_in_hectare']; //always in acers
        $plot->area_in_other    =  $value['area_in_other']; //record the value which is converted to respective units
        $plot->area_in_other_unit   =  $value['area_in_other_unit']; // record the converted value units
        $plot->save();


        //  now here creating a record for plot status, it will keep record who create a record
        $record =  PlotStatusRecord::create([
          'farmer_uniqueId'           => $request['farmer_uniqueId'],
          'plot_no'                   => $value['sr'],
          'farmer_plot_uniqueid'      => $request->farmer_uniqueId . 'P' . $value['sr'],
          'level'                     => 'AppUser',
          'status'                    => 'Approved',
          'comment'                   => 'Onboarding',
          'timestamp'                 => Carbon::now(),
          'user_id'                   => auth()->user()->id,
        ]);
      }


      DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->update(['organization_id' => $request['organization_id']]); //update organiztion id

      if (!$plot) {
        return response()->json(['error' => true, 'message' => 'Somethings went wrong']);
      }
      return response()->json([
        'success' => true, 'message' => 'Farmer Store Successfully',
        'FarmerId' => $farmer_id, 'FarmerUniqueID' => $request->farmer_uniqueId
      ], 200);
    } catch (\Exception $e) {
      return response()->json(['error' => true, 'message' => 'Somethings went wrong']);
    }
  }


  /**
   * Farmer store location info
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function storeLocation_update(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'latitude' => 'required',
      'longitude' => 'required',
    ]);
    try {
      //  location screen data store
      $Farmer_data  = DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_uniqueId)->get();
      foreach ($Farmer_data as $items) {
        $Farmer = FinalFarmer::where('id', $items->id)->first();
        $state = State::where('id', $request->state_id)->first();
        $Farmer->state        = $state->name;
        $Farmer->state_id     = $request->state_id;
        $Farmer->country      = $state->countryname->name;
        $Farmer->country_id   = $state->country_id; //we have countries table in India has 101 as ID
        $district = District::where('id', $request->district_id)->first();
        $Farmer->district     = $district->district;
        $Farmer->district_id  = $request->district_id;
        $taluka = Taluka::where('id', $request->taluka_id)->first();
        $Farmer->taluka       = $taluka->taluka;
        $Farmer->taluka_id    = $request->taluka_id;
        $Panchayat = Panchayat::whereId($request->panchayat_id)->first();
        $Farmer->panchayat       = $Panchayat->panchayat;
        $Farmer->panchayat_id    = $request->panchayat_id;
        $village = Village::where('id', $request->village_id)->first();
        $Farmer->village      = $village->village;
        $Farmer->village_id   = $request->village_id;
        $Farmer->latitude     = $request->latitude;
        $Farmer->longitude    = $request->longitude;
        $Farmer->remarks      = $request->remarks ?? 'NA';
        $Farmer->save();
      }
      if (!$Farmer_data) {
        return response()->json(['error' => true, 'message' => 'something went wrong'], 500);
      }
      return response()->json(['success' => true, 'farmerId' => $Farmer_data->first()->id, 'farmerUniqueId' => $Farmer_data->first()->farmer_uniqueId], 200);
    } catch (Exception $e) {
      return response()->json(['error' => true, 'message' => 'something went wrong'], 500);
    }
  }

  /**
   * Farmer store location info
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function storePlot_update(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'land_ownership' => 'required',
      'survey_no' => 'required',
    ]);
    try {
      //   \Log::info($request->all());
      //  store plot data individually from app
      $FarmerPlot  = FarmerPlot::where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->first();
      $FarmerPlot->survey_no             = $request->survey_no;
      $FarmerPlot->land_ownership        = $request->land_ownership;

      //we need to make l1 validator by default approved
      $FarmerPlot->aprv_recj_userid      = 1; // this is admin 1
      $FarmerPlot->appr_timestamp        = Carbon::now(); //this is because we want a survey to display directly in l2 validator.
      $FarmerPlot->status                = 'Approved';

      $FarmerPlot->final_status          = 'Approved';
      // $FarmerPlot->finalaprv_timestamp   = Carbon::now();
      $FarmerPlot->finalappr_userid      = 1;


      $FarmerPlot->area_acre_awd    =  $request->area_acre_awd;
      $FarmerPlot->area_other_awd    =  $request->area_other_awd; //here other represent that area value willbe is other conversion also. 
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
        DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->update(['actual_owner_name' => $request->actual_owner_name]);
      } else {
        $farmername = FinalFarmer::where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->select('farmer_name')->first();
        $FarmerPlot->actual_owner_name     = $farmername->farmer_name;
        DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->update(['actual_owner_name' => $farmername->farmer_name]);
      }

      if ($request->land_ownership == 'Leased') {
        $FarmerPlot->affidavit_tnc         = '1';
        $FarmerPlot->sign_affidavit_date   = Carbon::now();
        DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->update(['land_ownership' => 'Leased']);
      } elseif ($request->land_ownership == 'Own') {
        $FarmerPlot->affidavit_tnc         = '0';
        DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->update(['land_ownership' => 'Own']);
      }
      // if ($request->hasFile('sign_affidavit')) {
      //  $FarmerPlot->sign_affidavit = $request->sign_affidavit->storeAs('plot/'.$request->farmer_unique_id, 'affidavit-sign'.$request->file('sign_affidavit')->getClientOriginalName(), 'public');

      // }

      $FarmerPlot->save();
      if ($request->hasFile('signature')) {
        $path = Storage::disk('s3')->put(config('storagesystems.path') . '/' . $request->farmer_unique_id . '/' . 'DOCUMENTS', $request->signature);

        //  $path = $request->signature->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'signature-'.$request->file('signature')->getClientOriginalName(), 'public'); //Storage::putFile('public/'.config('storagesystems.store').'/'.$request->farmer_unique_id,  $request->image);
        FinalFarmer::where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->update([
          'check_carbon_credit' => '1',
          'signature' => Storage::disk('s3')->url($path), //$request->signature->storeAs(config('storagesystems.store').'/'.$request->farmer_unique_id,'signature-'.$request->file('signature')->getClientOriginalName(), 'public'),
          //'signature'=>Storage::disk('s3')->put(config('storagesystems.store').'/'.$request->farmer_unique_id, $request->signature),
          'sign_carbon_date' => Carbon::now()
        ]);
      }
      DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_unique_id)->where('plot_no', $request->plot_no)->update(['updated_at' => carbon::now()]); //update timestamp
      if (!$FarmerPlot) {
        return response()->json(['error' => true, 'message' => 'something went wrong'], 500);
      }
      return response()->json(['success' => true, 'farmerId' => $FarmerPlot->farmer_id, 'farmerUniqueId' => $FarmerPlot->farmer_uniqueId], 200);
    } catch (Exception $e) {
      return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
    }
  }
  public function Check_mobile_no(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'mobile' => 'required',
    ]);
    $mobile = $request->mobile;
    $farmer = FinalFarmer::where('mobile', $mobile)->while("onboard_completed","!=","Processing")->first();
    if ($farmer) {
      return response()->json(['success' => true, 'message' => 'Mobile number already exists'], 200);
    } else {
      return response()->json(['success' => false, 'message' => 'Mobile number does not exist'], 200);
    }
  }
}
