<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use PDF;
use Storage;    
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserTarget;
use App\Models\FarmerPlot;
use App\Models\PipeInstallation;
use App\Models\Polygon;
use App\Models\FinalFarmer;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallationPipeImg;
use App\Models\PlotStatusRecord;
use App\Models\Cordinate;
use App\Models\State;

class PolygonController extends Controller
{
    

    public function check_polygon_nearby(Request $request){

        // if(!request()->has('farmer_plot_uniqueid')){
        //     return response()->json(['error'=>true,'data'=>'Farmer Plot Unique ID is required'],422);
        // }

        if(!request()->has('lat')){
            return response()->json(['error'=>true,'data'=>'Latitude is required'],422);
        }

        if(!request()->has('lng')){
            return response()->json(['error'=>true,'data'=>'Longitude is required'],422);
        }
   
        $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
       
        if($plot)
        {   
             $polygon_data= Polygon::whereNotNull('ranges')->select(['ranges','farmer_plot_uniqueid'])->get();
                
                    $nearby_polygons = array();//define array
                    $radius = 0.250;//0.25;//1;//define radius for calculation
                    foreach($polygon_data as $data){
                        $farmer_plot_uniqueid = $data->farmer_plot_uniqueid;
                        $polygon = array_values(json_decode($data->ranges, true));
                        foreach($polygon as $pol){
                            // haversine function has three parameter 1. plot lat from app 2. plot lng from app 3. fetch lat from db, 4. fetch lng from db
                            $distance = $this->haversine($request->lat, $request->lng, $pol['lat'], $pol['lng']);
                        }

                        if ($distance <= $radius) {// if distance calculated by haversine function is less than radius than add polygon in $nearby_polygons valriable
                            $nearby_polygons[] = ["ranges" => $polygon, "plot_uniqueid" => $farmer_plot_uniqueid];
                        }
                    }
                    $polygon_data=[];
                    foreach($nearby_polygons as $data){
                    //now checking if data at index 0 = to data at last index
                    $firstarray = $data["ranges"][0];
                    $lastarray = $data["ranges"][array_key_last($data["ranges"])];

                    // if($firstarray != $lastarray){
                    //     $data[array_key_last($data) + 1]=$data[0];
                    // }
                    // assign data to ranges key
                    
                    $data = array(['ranges'  =>  $data["ranges"],"plot_uniqueid"  =>  $data["plot_uniqueid"]]);
                    $polygon_data[]= $data;
                    }
                    
                    if($polygon_data){
                        return response()->json($polygon_data);
                    }else{
                        return response()->json(['error'=>true,'data'=>'No Data'],422);
                    }
        }
        else{

            return response()->json(['error'=>true,'data'=>'No Data'],422);
        } 
    }



    // public function check_polygon_nearby(Request $request){
   
    //     $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
       
    //     if($plot)
    //     {   
    //          $polygon_data= Polygon::whereNotNull('ranges')->select('ranges','farmer_plot_uniqueid')->get();
    //                 $nearby_polygons = array();//define array
    //                 $radius = 0.250;//0.25;//1;//define radius for calculation
    //                 foreach($polygon_data as $data){
    //                 $polygon = array_values(json_decode($data->ranges, true));
    //                     foreach($polygon as $pol){
    //                     // haversine function has three parameter 1. plot lat from app 2. plot lng from app 3. fetch lat from db, 4. fetch lng from db
    //                     $distance = $this->haversine($request->lat, $request->lng, $pol['lat'], $pol['lng']);
    //                     }

    //                 if ($distance <= $radius) {// if distance calculated by haversine function is less than radius than add polygon in $nearby_polygons valriable
    //                     // $nearby_polygons[] = $polygon;

    //                     $nearby_polygons[] = [
    //                         'farmer_plot_uniqueid' => $data->farmer_plot_uniqueid,
    //                         'ranges' => $polygon
    //                     ];

    //                 }
    //                 }
                    
    //                 $polygon_data=[];
    //                 foreach($nearby_polygons as $data){
    //                 //now checking if data at index 0 = to data at last index
    //                 // $firstarray = $data[0];
    //                 // $lastarray = $data[array_key_last($data)];
    //                 $firstarray = $data['ranges'][0];
    //                 $lastarray = $data['ranges'][array_key_last($data['ranges'])];

    //                 // if($firstarray != $lastarray){
    //                 //     $data[array_key_last($data) + 1]=$data[0];
    //                 // }
    //                 // assign data to ranges key
    //                 // $data = array(['ranges'  =>  $data]);

    //                 $data = [
    //                     'farmer_plot_uniqueid' => $data['farmer_plot_uniqueid'],
    //                     'ranges' => $data['ranges']
    //                 ];


    //                 $polygon_data[]= $data;
    //                 }
                    
    //                 if($polygon_data){
    //                     return response()->json($polygon_data);
    //                 }else{
    //                     return response()->json(['error'=>true,'data'=>'No Data'],422);
    //                 }
    //     }
    //     else{

    //         return response()->json(['error'=>true,'data'=>'No Data'],422);
    //     } 
    // }

     function haversine($lat1, $lng1, $lat2, $lng2) {
        $earth_radius = 6371; // in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earth_radius * $c;
        return $distance;
    }


    public function check_lat_lng_inside_polygon(Request $request) {
        // Ensure you have appropriate database indexes for the WHERE clauses.
    
        $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        $polygon_data= Polygon::whereNotNull('ranges')->select('id', 'ranges')->get();
        $lat = $request->lat;
        $lng = $request->lng;
    
        // Iterate through polygon data and check if the point is inside any polygon.
        foreach ($polygon_data as $item) {
            $rangeData = json_decode($item->ranges, true);

            if ($this->isPointInsidePolygon($lat, $lng, $rangeData)) {
                return response()->json(['status' => true, 'lat' => $lat, 'lng' => $lng, 'message' => 'Inside Polygon'], 200);
            }
        }
        return response()->json(['status' => false, 'lat' => $lat, 'lng' => $lng, 'message' => 'Outside Polygon'], 422);
    }

    // Function to check if a point is inside a polygon.
    private function isPointInsidePolygon($lat, $lng, $points) {
    $numPoints = count($points);
    $inside = false;
    
    for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
        $xi = $points[$i]['lat'];
        $yi = $points[$i]['lng'];
        $xj = $points[$j]['lat'];
        $yj = $points[$j]['lng'];

        if ((($yi > $lng) != ($yj > $lng)) && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi)) {
            $inside = !$inside;
        }
    }
    return $inside;
   }



public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
       
        try {
            // Check if a record with the provided farmer_plot_uniqueid exists
            $existingFarmer = DB::table('final_farmers')
                ->where('farmer_uniqueId', $request->farmer_uniqueId)
                ->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                ->first();

            $existingNullFarmer = DB::table('final_farmers')
                ->where('farmer_uniqueId', $request->farmer_uniqueId)
                ->where('farmer_plot_uniqueid', null)
                ->first();

            $numericPart = ''; // Initialize numericPart to an empty string
            preg_match('/P(\d+)$/', $request->farmer_plot_uniqueid, $matches);
            if (isset($matches[1])) {
                $numericPart = $matches[1];
            }
            $farmer_acre = DB::table('final_farmers')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no', 1)->first();
            $farmer_plot_area = DB::table('farmer_plot_detail')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no', 1)->first();

            // If farmer_plot_uniqueid is null, update it with the provided value
            if ($existingNullFarmer) {
                FinalFarmer::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->update([
                    'pipe_form' => 1,
                    'farmer_plot_uniqueid' => $request->farmer_plot_uniqueid,
                ]);
            }
            if (!$existingFarmer){
                // Create a new record in the final_farmers table with the same details
                $existingFarmerDetails = DB::table('final_farmers')
                    ->where('farmer_uniqueId', $request->farmer_uniqueId)
                    ->first();
                $farmer = new FinalFarmer;
                $farmer->surveyor_id  = auth()->user()->id;
                $farmer->farmer_name  = $existingFarmerDetails->farmer_name;
                $farmer->mobile_access = $existingFarmerDetails->mobile_access;
                $farmer->mobile_reln_owner = $existingFarmerDetails->mobile_reln_owner ?? "NA";
                $farmer->mobile = $existingFarmerDetails->mobile ?? "NA";
                $farmer->mobile_verified = '1';
                $farmer->document_no = $existingFarmerDetails->document_no;
                $farmer->document_id = $existingFarmerDetails->document_id;
                $farmer->farmer_uniqueId =  $existingFarmerDetails->farmer_uniqueId;
                $farmer->farmer_survey_id = $existingFarmerDetails->farmer_survey_id;
                $farmer->farmer_plot_uniqueid = $request->farmer_plot_uniqueid;
                $farmer->plot_no = $numericPart;
                $farmer->organization_id = $existingFarmerDetails->organization_id;
                $farmer->gender = $existingFarmerDetails->gender;
                $farmer->guardian_name = $existingFarmerDetails->guardian_name;
                $farmer->status_onboarding = 'Pending';
                $farmer->final_status_onboarding = 'Pending'; // need to do direct approval so that it is easily available for crop data
                $farmer->onboarding_form      = 1;
                $farmer->area_in_acers      =  $farmer_acre->area_in_acers;
                $farmer->own_area_in_acres =   $farmer_acre->own_area_in_acres;
                $farmer->lease_area_in_acres =  $farmer_acre->lease_area_in_acres;
                $farmer->final_status       = 'Pending';
                $farmer->onboard_completed  = 'Pending';
                $farmer->L2_aprv_timestamp      = Carbon::now(); //by default adding current time in approval time
                $farmer->L2_appr_userid      = 1;
                $farmer->country_id =   $farmer_acre->country_id;
                $farmer->state_id =   $farmer_acre->state_id;
                $farmer->district_id =   $farmer_acre->district_id;
                $farmer->taluka_id =   $farmer_acre->taluka_id;
                $farmer->panchayat_id =   $farmer_acre->panchayat_id;
                $farmer->village_id =   $farmer_acre->village_id;
                $farmer->date_survey =   $farmer_acre->date_survey;
                $farmer->time_survey =   $farmer_acre->time_survey;
                $farmer->check_carbon_credit =  $farmer_acre->check_carbon_credit;
                $farmer->remarks =   $farmer_acre->remarks;
                $farmer->land_ownership =   $farmer_acre->land_ownership;
                $farmer->actual_owner_name =   $farmer_acre->actual_owner_name;
                $farmer->signature = $farmer_acre->signature;
                $farmer->sign_carbon_date = $farmer_acre->sign_carbon_date;
                $farmer->farmer_photo = $farmer_acre->farmer_photo;
                $farmer->aadhaar_photo = $farmer_acre->aadhaar_photo;
                $farmer->others_photo = $farmer_acre->others_photo;
                $farmer->financial_year = $request->financial_year;
                $farmer->season = $request->season;
                $farmer->save();
            }

            $plot = DB::table('polygons')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
            if ($plot) {
                if ($plot->ranges == null) {
                    DB::table('polygons')
                        ->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                        ->update(['ranges' => json_encode($request->ranges)]);

                    return response()->json(['error' => true, 'message' => 'Polygon Created', 'data' => $plot], 422);
                } else {
                    $plot->ranges = json_decode($plot->ranges);
                    $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                        ->where('status', 'Pending')
                        ->where('trash', 0)
                        ->get();
                    $plot->pipes_location = $pipe_data;
                    return response()->json(['error' => true, 'message' => 'Data Available', 'data' => $plot], 422);
                }
            }
            // Create a new record in the pipe_installations table
            
            $pipe = Polygon::create([
                'farmer_id' => $request->farmer_id,
                'farmer_uniqueId' => $request->farmer_uniqueId,
                'farmer_plot_uniqueid' => $request->farmer_plot_uniqueid,
                'plot_no' => $numericPart,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                "area_units"     => $request->area_units,
                "plot_area"       => $request->plot_area,
                'surveyor_id' => auth()->user()->id,
                'polygon_date_time' => $request->polygon_date_time ?? null,
                'ranges' => json_encode($request->ranges),
                'financial_year' => $request->financial_year,
                'season' => $request->season,
                'final_status' => 'Pending',
            ]);
            // $farmer_plot_exist = DB::table('farmer_plot_detail')->where('farmer_uniqueId', $request->farmer_unique_id)->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
            $farmer_plot_exist = DB::table('farmer_plot_detail')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->exists();
            
            if(!$farmer_plot_exist)
            {
            // now creating data for plot details, with connection to finalfarmer table
            $farmer_plot = new FarmerPlot;
            $farmer_plot->farmer_id        =  $request->farmer_id;
            $farmer_plot->farmer_uniqueId  =  $request->farmer_uniqueId;
            $farmer_plot->farmer_plot_uniqueid = $request->farmer_plot_uniqueid;
            $farmer_plot->plot_no          =  $numericPart;
            $farmer_plot->area_in_acers    = $farmer_acre->area_in_acers;
            $farmer_plot->area_in_other    =  $farmer_plot_area->area_in_other??"0.00";
            $farmer_plot->area_acre_awd    = $farmer_plot_area->area_acre_awd??"0.00";
            $farmer_plot->area_in_other_unit   =   $farmer_plot_area->area_in_other_unit??"0.00";
            $farmer_plot->area_other_awd   =   $farmer_plot_area->area_other_awd??"0.00";
            $farmer_plot->patta_number   =   $farmer_plot_area->patta_number;
            $farmer_plot->daag_number   =   $farmer_plot_area->daag_number;
            $farmer_plot->khatha_number   =   $request->khatha_number??$farmer_plot_area->khatha_number;
            $farmer_plot->pattadhar_number   =   $request->patta_number??$farmer_plot_area->pattadhar_number;
            $farmer_plot->khatian_number   =   $farmer_plot_area->khatian_number;
            $farmer_plot->land_ownership   =   $request->land_ownership??$farmer_plot_area->land_ownership;
            $farmer_plot->actual_owner_name   =   $request->actual_owner_name??$farmer_plot_area->actual_owner_name;
            $farmer_plot->finalaprv_timestamp   =  Carbon::now();
            $farmer_plot->appr_timestamp   =  Carbon::now();
            $farmer_plot->finalappr_userid   =  1;
            $farmer_plot->aprv_recj_userid   =  1;
            $farmer_plot->survey_no   = $request->survey_no??$farmer_plot_area->survey_no;
            $farmer_plot->status                = 'Pending';
            $farmer_plot->final_status          = 'Pending';
            $farmer_plot->save();
            }

            // Create a record in PlotStatusRecord if needed
            PlotStatusRecord::create([
                'farmer_uniqueId' => $request->farmer_uniqueId,
                'plot_no' => $numericPart,
                'farmer_plot_uniqueid' => $request->farmer_plot_uniqueid,
                'level' => 'AppUser',
                'status' => 'Pending',
                'comment' => 'Uploaded PipeInstallation Data',
                'timestamp' => now(),
                'user_id' => auth()->user()->id,
            ]);
            $userTarget = UserTarget::updateOrCreate(
                [
                    'user_id' => auth()->user()->id,
                    'module_id' => '3',
                    'module_name' => 'polygon',
                    'date' => now()->toDateString(),
                ],
                [
                    'count' => DB::raw('count + 1'),
                ]
            );
            // dd($userTarget);
            // dd($numericPart);

            // if ($numericPart == "1") {
            //     $available_area = ($existingFarmer->available_area) - ($request->plot_area);
            // } 
            // else{
            //     $previousPlot = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
            //     if ($previousPlot) {
            //         dd()
            //         $available_area = ($previousPlot->available_area) - ($request->plot_area);

            //     } else {
            //         // Handle the case when the previous plot is not found
            //         // You can set $available_area to some default value or handle it as needed.
            //         $available_area = null;
            //     }
            // }

            $available_area = ($farmer_acre->available_area)-($request->plot_area);

            FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no', 1)->update([
                'available_area' => $available_area,
            ]);

            FinalFarmer::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->update([
                'pipe_form' => 1,
                'plot_area' => $request->plot_area,
            ]);

            
            if (!$pipe) {
                return response()->json(['error' => true, 'message' => 'Somethings went wrong'], 422);
            }

            return response()->json([
                'success' => true, 'message' => 'Polygon Store Successfully',
                'FarmerId' => $request->farmer_id, 'FarmerUniqueID' => $request->farmer_uniqueId, 'PlotNo' => $numericPart
            ], 200);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
        }
    }

   

    public function state_wise_data(Request $request){
        try{

        
        $conversion = State::select('id','name', 'lm_units','base_value','hectares_to_acres')->where('id',$request->id)->first();
        if(!$conversion){
            return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
        }
        $data=[
            'id' => $conversion->id ,
            'name' =>'0.01' ,
            'unit' => $conversion->lm_units,
            'acre_to_hectare' => $conversion->base_value ,
            'hectares_to_acres' => $conversion->hectares_to_acres
        ];
        return response()->json(['success' => true, 'message' => 'Successfully Fetched','data' => $data], 200);

       } catch (\Exception $e) {
           dd($e);
           return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
       }
}
}