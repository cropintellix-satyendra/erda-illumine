<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use PDF;
use Illuminate\Support\Facades\Log;
use Storage;    
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserTarget;
use App\Models\Polygon;
use App\Models\FarmerPlot;
use App\Models\PipeInstallation;
use App\Models\FinalFarmer;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallationPipeImg;
use App\Models\PlotStatusRecord;
use App\Models\Cordinate;


class PipeIntallationController extends Controller
{
    /**
    * Send threshold pipeinstallation
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public function get_threshold(Request $request){
        if (version_compare(phpversion(), '7.1', '>=')) {
           ini_set( 'precision', 17 );
           ini_set( 'serialize_precision', -1 );
        }
        $setting = Setting::find(1);
        return response()->json(['success'=>true,'threshold' =>$setting->threshold_pipe_installation]);
    }
    /**
    * Farmer store benefit images
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public function detail_pipe_plot_id(Request $request){
        try{
            $cropdata = FarmerCropdata::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->where('l2_status','Approved')->first();
            // if(!$cropdata){
            //     return response()->json(['error'=>true,'Status'=>'1','message'=>'Please Approved Cropdata'],422);
            // }
            //api for benefit data to app
            $farmer = FinalFarmer::select('id','farmer_uniqueId','farmer_plot_uniqueid','farmer_name','mobile','no_of_plots','area_in_acers')
                      ->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
            $plot_detail = FarmerPlot::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->select('farmer_uniqueId','farmer_plot_uniqueid','plot_no','area_in_acers','area_in_other','area_in_other_unit','area_acre_awd','area_other_awd','area_other_awd_unit')->first();
            $guntha = 0.025000;
                // if($farmer->state_id == 36){
                //       $area = number_format((float)$farmer->area_in_acers, 2, '.', '');
                //       $split = explode('.', $area);//spliting area
                //       $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                //       $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                //       $conversion = explode('.', $result); // split result
                //       $conversion = $conversion[1]??0;
                //       $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                //       $farmer->area_in_acers = $acers;
                // }


            // $cropdata = FarmerCropdata::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->get();
            // if(!$cropdata->count() > 0){
            //     return response()->json(['error'=>true,'farmer'=>$farmer,'Status'=>'0'],422);
            // }


            if($farmer){
                return response()->json(['success'=>true,'farmer'=>$farmer, 'Status'=>'1','plot'=>$plot_detail],200);
            }else{
                return response()->json(['error'=>true,'farmer'=>$farmer,'Status'=>'1'],422);
            }
        }catch(\Exception $e){
            return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
        }
    }

   /**
   * Pipe info check data
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */

   public function check_pipe_data(Request $request){
    // dd($request->all());
        $plot = DB::table('polygons')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
        if($plot){

            if($plot->final_status=="Rejected")
            {
                $polygon_status = 0;
                return response()->json(['error'=>true,  'data'=>'Rejected data'],422);
            }
            $plot->ranges = json_decode($plot->ranges);

            if($plot->ranges){
                $polygon_status = 1;
            }else{
                $polygon_status = 0;
            }
            
            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                                                ->where('plot_no',$request->plot_no)
                                                ->where('status','Approved')
                                                ->where('trash',0)
                                                ->get();
                                                // dd($pipe_data);
            if($pipe_data->count() > 0){
                $plot->pipes_location = $pipe_data;
                $status = 1;
                foreach ($pipe_data as $data) {
                    // dd($data);
                if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                    // dd($status);
                    $status = 0;
                }else{
                    // dd("in");
                    $status = 0;
                }

                }
            }else{
                $status = 0;
            }

            if($pipe_data->isNotEmpty() && isset($pipe_data[0]->financial_year) && isset($pipe_data[0]->season)){
                foreach ($pipe_data as $data) {
                    if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                        return response()->json(['error' => true, 'message' => 'Data already submitted' ,'status'=>0, ] , 423);
                    }
                }
            }

            return response()->json(['error'=>true,'message'=>'Data Available','data'=>$plot,'status'=>$status,'polygon_status' =>$polygon_status],200);
        }else{
            return response()->json(['error'=>true,'data'=>'No Data'],422);
        }
   }



   public function check_pipe_data_new(Request $request)
   {
       // Check if plot exists for the given farmer_plot_uniqueid and plot_no
       $plot = DB::table('polygons')
                   ->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                   ->where('plot_no', $request->plot_no)
                   ->first();
   
       if ($plot) {
           // If plot is rejected, return an error
           if ($plot->final_status == "Rejected") {
               return response()->json(['error' => true, 'data' => 'Rejected data'], 422);
           }
   
           // Decode the ranges JSON if available
           $plot->ranges = json_decode($plot->ranges);
           $polygon_status = $plot->ranges ? 1 : 0;
   
           // Check if there is existing pipe data for the given details
           $pipe_data = DB::table('pipe_installation_pipeimg')
                            ->select(
                                [
                                    "id",
                                    "farmer_uniqueId",
                                    "farmer_plot_uniqueid",
                                    "plot_no",
                                    "pipe_no",
                                    "lat",
                                    "lng",
                                    "images",
                                    "status",
                                    "financial_year",
                                    "season"
                                ]
                            )
                           ->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                           ->where('plot_no', $request->plot_no)
                           ->where('pipe_no', $request->pipe_no)
                           ->where('status', 'Approved')
                           ->where('trash', 0)
                           ->get();
           // If data is found, check if it matches the financial year and season
           if ($pipe_data->isNotEmpty()) {
               foreach ($pipe_data as $data) {
                   if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                       // Return an error if data for the same financial year and season is already submitted
                       return response()->json(['error' => true, 'message' => 'Data already submitted', 'status' => 0], 423);
                   }
               }
           }
           $pipe_data_existing = DB::table('pipe_installation_pipeimg')
                            ->select(
                                [
                                    "id",
                                    "farmer_uniqueId",
                                    "farmer_plot_uniqueid",
                                    "lat",
                                    "lng",
                                    "plot_no",
                                    "pipe_no",
                                ]
                            )
                           ->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                        //    ->where('plot_no', $request->plot_no)
                        //    ->where('pipe_no', $request->pipe_no)
                           ->where('status', 'Approved')
                           ->where('trash', 0)
                           ->get();
           // Return the plot data with the found pipe data
        //    $plot->pipes_location = $pipe_data;
           $plot->pipes_location = $pipe_data_existing;
           return response()->json([
               'error' => false,
               'message' => 'Data Available',
               'data' => $plot,
               'status' => $pipe_data->isNotEmpty() ? 1 : 0,
               'polygon_status' => $polygon_status,
               'pipe_data_existing' => $pipe_data_existing,
           ], 200);
   
       } else {
           // Return an error if no plot data is found
           return response()->json(['error' => true, 'data' => 'No Data'], 422);
       }
   }
   
   
//    public function check_pipe_data(Request $request){
//         $plot = DB::table('pipe_installations')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
//         if($plot){
//             $plot->ranges = json_decode($plot->ranges);

//             if($plot->ranges){
//                 $polygon_status = 1;
//             }else{
//                 $polygon_status = 0;
//             }

//             $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
//                                                 ->where('plot_no',$request->plot_no)
//                                                 ->where('status','Approved')
//                                                 ->where('trash',0)
//                                                 ->get();

//             if($pipe_data->count() > 0){
//                 $plot->pipes_location = $pipe_data;
//                 $status = 1;
//             }else{
//                 $status = 0;
//             }

//             return response()->json(['error'=>true,'message'=>'Data Available','data'=>$plot,'status'=>$status,'polygon_status' =>$polygon_status],200);
//         }else{
//             return response()->json(['error'=>true,'data'=>'No Data'],422);
//         }
//    }

   /**
   * Pipe info check data
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function check_pipe_location_image(Request $request){
        $plot = DB::table('pipe_installation_pipeimg')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no',$request->plot_no)->where('trash',0)->first();
        if($plot){
            return response()->json(['error'=>true,'status'=>1,'data'=>$plot],200);
        }else{
            return response()->json(['error'=>true,'status'=>0,'data'=>$plot],422);
        }
    }


   /**
   * Check Polygon near by
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
    // public function check_polygon_nearby(Request $request){
   
    //     $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
       
    //     if($plot)
    //     {   
    //          $polygon_data= Polygon::whereNotNull('ranges')->select('ranges')->get();
    //                 $nearby_polygons = array();//define array
    //                 $radius = 0.250;//0.25;//1;//define radius for calculation
    //                 foreach($polygon_data as $data){
    //                 $polygon = array_values(json_decode($data->ranges, true));
    //                     foreach($polygon as $pol){
    //                     // haversine function has three parameter 1. plot lat from app 2. plot lng from app 3. fetch lat from db, 4. fetch lng from db
    //                     $distance = $this->haversine($request->lat, $request->lng, $pol['lat'], $pol['lng']);
    //                     }

    //                 if ($distance <= $radius) {// if distance calculated by haversine function is less than radius than add polygon in $nearby_polygons valriable
    //                     $nearby_polygons[] = $polygon;
    //                 }
    //                 }
    //                 $polygon_data=[];
    //                 foreach($nearby_polygons as $data){
    //                 //now checking if data at index 0 = to data at last index
    //                 $firstarray = $data[0];
    //                 $lastarray = $data[array_key_last($data)];

    //                 if($firstarray != $lastarray){
    //                     $data[array_key_last($data) + 1]=$data[0];
    //                 }
    //                 // assign data to ranges key
    //                 $data = array(['ranges'  =>  $data]);
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

    //  function haversine($lat1, $lng1, $lat2, $lng2) {
    //     $earth_radius = 6371; // in km
    //     $dLat = deg2rad($lat2 - $lat1);
    //     $dLon = deg2rad($lng2 - $lng1);
    //     $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    //     $distance = $earth_radius * $c;
    //     return $distance;
    // }





            public function nearby(Request $request)
        {
            $lat = $request->input('lat');
            $lng = $request->input('lng');
            $radius = 0.1;
            $allCoordinates = Cordinate::select('ranges', 'gid', 'fid')->get();
        
            $nearbyCoordinates = $allCoordinates->filter(function ($coordinate) use ($lat, $lng, $radius) {
                $coordinates = json_decode($coordinate->ranges, true);
               
                foreach ($coordinates as $coord) {
                    // Calculate the distance between each coordinate in the JSON array
                    $distance = $this->haversines($lat, $lng, $coord['lat'], $coord['lng']);
                     
                    // If any coordinate within the JSON array is within the radius, consider the entire entry
                    if ($distance <= $radius) {
                        return true;
                    }
                }
        
                return false;
            });
        
            // Reformat the response to have "ranges" as an array of objects
            $formattedResponse = $nearbyCoordinates->map(function ($coordinate) {
                return [
                    'gid' => $coordinate->gid,
                    'fid' => $coordinate->fid,
                    'ranges' => json_decode($coordinate->ranges, true)
                ];
            });
        
            if ($formattedResponse->isEmpty()) {
                return response()->json(['error' => true, 'data' => 'No nearby polygons found'], 422);
            }
        
             if(request()->ajax())
            {
              return response()->json($formattedResponse);
            }
                return response()->json($formattedResponse->values());
        }
        
        
        private function haversines($lat1, $lon1, $lat2, $lon2)
        {
            $lat1 = deg2rad($lat1);
            $lon1 = deg2rad($lon1);
            $lat2 = deg2rad($lat2);
            $lon2 = deg2rad($lon2);
        
            $dlat = $lat2 - $lat1;
            $dlon = $lon2 - $lon1;
        
            $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
            $radius = 6371; // Earth's radius in kilometers
        
            return $radius * $c;
        }


        public function check_polygon_area(Request $request)
        {
            $input = $request->json()->all();

        if (isset($input['coordinates'])) {
            $coordinates = $input['coordinates'];
            if (count($coordinates) >= 3) {
                $polygonArea = $this->calculateArea($coordinates);
                $areaAcres = $polygonArea * 247.105;

                return response()->json(['area_acres' => $areaAcres]);
            } else {
                return response()->json(['error' => 'Invalid polygon, at least 3 points required'], 400);
            }
        } else {
            return response()->json(['error' => 'No coordinates provided'], 400);
        }
        }
        
        private function calculateArea($coordinates)
        {
        if ($coordinates[0] !== end($coordinates)) {
            $coordinates[] = $coordinates[0];
        }
        $area = 0;
        for ($i = 0, $j = count($coordinates) - 1; $i < count($coordinates); $i++) {
            $x1 = $coordinates[$i]['lat'];
            $x2 = $coordinates[$j]['lat'];
            $y1 = $coordinates[$i]['lng'];
            $y2 = $coordinates[$j]['lng'];
            $area += ($x1 + $x2) * ($y2 - $y1);
            $j = $i;
        }
        $area = abs($area) / 2;
        return $area;
    }
        



    /**
    * Filter record and ceck for corrdinates inside polygon
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    // public function check_lat_lng_inside_polygon(Request $request){
    //     if (version_compare(phpversion(), '7.1', '>=')) {
    //        ini_set( 'precision', 17 );
    //        ini_set( 'serialize_precision', -1 );
    //     }
    //     $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
    //     $polygon_data = PipeInstallation::select('ranges')->whereHas('farmerapproved', function($q) use($plot){
    //                     if($plot->state_id){
    //                         $q->where('state_id',$plot->state_id);
    //                     }
    //                     if($plot->district_id){
    //                          $q->where('district_id',$plot->district_id);
    //                     }
    //                     if($plot->taluka_id){
    //                          $q->where('taluka_id',$plot->taluka_id);
    //                     }
    //                     if($plot->panchayat_id){
    //                          $q->where('panchayat_id',$plot->panchayat_id);
    //                     }
    //                     if($plot->village_id){
    //                          $q->where('village_id',$plot->village_id);
    //                     }
    //                     if($plot->farmer_plot_uniqueid){
    //                         //this condition is used to avoid same plot in response
    //                         $q->where('farmer_plot_uniqueid', '!=',$plot->farmer_plot_uniqueid);
    //                     }
    //                     return $q;
    //                     })
    //                 ->get();
    //      $inside=false;
    //     foreach($polygon_data as $items){
    //             $range_data=collect($items)->map(function($items){
    //                 $items=collect(json_decode($items))->map(function($latlng){
    //                     return collect($latlng)->toArray();
    //                 });
    //                 return $items;
    //             })->toArray();
    //             if(count($range_data)>0){
    //                 foreach($range_data as $points){
    //                     $vertices_x=[];
    //                     $vertices_y=[];
    //                     $points_polygon = count($points);
    //                     foreach($points as $point){
    //                         $vertices_x[]=$point['lat'];
    //                         $vertices_y[]=$point['lng'];
    //                     }
    //                     $check_inside =  $this->inside($request->lat, $request->lng, $range_data['ranges']);
    //                     if($check_inside){
    //                         return response()->json(['status' =>true, 'lat'=>$request->lat, 'lng'=> $request->lng,'message'=>'Inside Polygon'],200);
    //                         break;
    //                     }
    //                 }//foreach end
    //             }//if end
    //     }//foreach end
    //     return response()->json(['status' =>false, 'lat'=>$request->lat, 'lng'=> $request->lng,'message'=>'OutSide Polygon'],422);
    // }

    public function check_lat_lng_inside_polygon(Request $request) {
        // Ensure you have appropriate database indexes for the WHERE clauses.
    
        $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        $polygon_data= PipeInstallation::whereNotNull('ranges')->select('id', 'ranges')->get();
      
    
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

    /**
    * Check coordinates inside polygon
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    function inside($lat, $lng, $fenceArea) {
        if(version_compare(phpversion(), '7.1', '>=')) {
           ini_set( 'precision', 17 );
           ini_set( 'serialize_precision', -1 );
        }
        $x = $lat; $y = $lng;
        $inside = false;
        for ($i = 0, $j = count($fenceArea) - 1; $i <  count($fenceArea); $j = $i++) {
            $xi = $fenceArea[$i]['lat']; $yi = $fenceArea[$i]['lng'];
            $xj = $fenceArea[$j]['lat']; $yj = $fenceArea[$j]['lng'];
            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
    }

   /**
   * Pipe info check data
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  // Commented because now only available pipe is 1
//    public function no_of_pipe(Request $request){

//       $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
//       $farmerplot = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
//       $FarmerPlotdetail = FarmerPlot::where('farmer_plot_uniqueid',$farmerplot->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
//       if(!$farmerplot){
//           return response()->json(['error'=>true, 'message'=>'Plot Not available'],422);
//       }
//       $pipesettings = DB::table('pipe_settings')->where('id',1)->first();//from DB pipe_settings
//       $nohectares =  DB::table('settings')->select('no_of_hectares')->where('id',1)->first();//from DB settings
//         $acresInhectares="";
//         if($pipesettings->type == 'hectare'){// checking if using hectare
//         //   $acresInhectares = $FarmerPlotdetail->area_acre_awd;
//         $acresInhectares = $farmerplot->plot_area;
//           for ($x = 1; $x <= $nohectares->no_of_hectares; $x++){
//             if(0 <  $acresInhectares && $acresInhectares <= $pipesettings->area * $x){
//                 // dd('in','area in hect '.$acresInhectares, 'actual area of hec '.$x.' '.$pipesettings->area * $x);
//                 $acresInhectares = (int) $acresInhectares;
//                 if($acresInhectares == 0){
//                     $acresInhectares = 1;
//                 }
//                 return response()->json(['success'=>true,
//                                          'uinique_id'=>$request->farmer_uniqueId,
//                                          'farmer_plot_uniqueid' => $farmerplot->farmer_plot_uniqueid,
//                                          'plot_area'=>$farmerplot->plot_area,
//                                          'required_pipes'=>"1",//$acresInhectares, curenlty we are sending 1 by default

//                                          'areainhectare' =>$acresInhectares,
//                                          'hctares '.(int) $acresInhectares =>  $pipesettings->area * $x,

//                                          'no' =>$x,
//                                         ],200);
//             }//if end
//           }//forloop end

//           // if(0 <  $acresInhectares && $acresInhectares <= $pipesettings->area){
//           //     dd('in', $pipesettings->area, $acresInhectares,'required pipe 1');
//           // }elseif($acresInhectares > $pipesettings->area && $acresInhectares <= $pipesettings->area * 2){
//           //   dd('in twice hectare',$pipesettings->area * 2,$acresInhectares ,'required pipe 2');
//           // }
//         }// end hectare
//         elseif($pipesettings->type == 'acres'){
//         //   dd('in acres');
//         }else{
//              return response()->json(['error'=>true, 'message'=>'Plot Not available'],422);
//         }

//    }


// public function no_of_pipe(Request $request)
// {   
//  $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
//  return response()->json(['success'=>true,
//                                           'uinique_id'=>$request->farmer_uniqueId,
//                                           'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid,
//                                         //   'plot_area'=>$plot_check,
//                                           'required_pipes'=>"1",//$acresInhectares, curenlty we are sending 1 by default
//                                          ],200);
//       if(!$farmer)
//       {
//         return response()->json(['error'=>true,
//         "message" => "No data found",
//        ],422);
//       }
// }

// public function no_of_pipe(Request $request)
// {   
//  $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();

//  $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
//     ->where('plot_no',$request->plot_no)
//     ->where('status','Approved')
//     ->where('trash',0)
//     ->get();

//      if($pipe_data->isNotEmpty() && isset($pipe_data[0]->financial_year) && isset($pipe_data[0]->season)){
//          foreach ($pipe_data as $data) {
//              if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
//                  return response()->json(['error' => true, 'message' => 'Requested year and season already exist.'], 422);
//              }
//          }
//      }

//  return response()->json(['success'=>true,
//                                           'uinique_id'=>$request->farmer_uniqueId,
//                                           'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid,
//                                         //   'plot_area'=>$plot_check,
//                                           'required_pipes'=>"1",//$acresInhectares, curenlty we are sending 1 by default
//                                          ],200);

//       if(!$farmer)
//       {
//         return response()->json(['error'=>true,
//         "message" => "No data found",
//        ],422);
//       }
// }




// public function getRequiredPipes($area)
// {
//     // Round the area to the nearest integer
//     $roundedArea = floor($area);

//     // Special case for areas greater than 3
//     if ($roundedArea >= 3) {
//         return (string) $roundedArea;
//     }

//     // Retrieve the pipe setting that matches the requested area
//     $pipeSetting = DB::table('pipe_settings')
//         ->where('less_area', '<=', $area)
//         ->where('max_area', '>=', $area)
//         ->first();

//     if ($pipeSetting) {
//         return $pipeSetting->no_of_pipe ?? "1";
//     } else {
//         return "1";
//     }
// }




public function no_of_pipe(Request $request)
{   
 $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
 $area = $request->input('plot_area')??"1";

 $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
    ->where('plot_no',$request->plot_no)
    ->where('status','Approved')
    ->where('trash',0)
    ->get();

     if($pipe_data->isNotEmpty() && isset($pipe_data[0]->financial_year) && isset($pipe_data[0]->season)){
         foreach ($pipe_data as $data) {
             if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                 return response()->json(['error' => true, 'message' => 'Requested year and season already exist.'], 422);
             }
         }
     }

     $requiredPipes = $this->getRequiredPipes($area);

    
 return response()->json(['success'=>true,
                                          'uinique_id'=>$request->farmer_uniqueId,
                                          'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid,
                                        //   'plot_area'=>$plot_check,
                                          'required_pipes'=> $requiredPipes??"1",//$acresInhectares, curenlty we are sending 1 by default
                                         ],200);

      if(!$farmer)
      {
        return response()->json(['error'=>true,
        "message" => "No data found",
       ],422);
      }
}



/**
   * Pipe info store
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  
  
  
  //Now we not using this table to store
public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
            'state' => 'required',
            'district' => 'required',
            'taluka' => 'required',
            'village' => 'required',
            // 'khasara_no' => 'required',
        ]);
        if($request->has("date")){
            $clientTs = Carbon::createFromFormat('Y-m-d H:i:s', $request->date.' '.$request->time, config('app.timezone'));

            if ($clientTs->toDateString() !== now()->toDateString()) {
                return response()->json([
                'error' => 'false',
                'message' => 'Date does not match server date.'
                ], 422);
            }

            if ($clientTs->lt(now()->subHour())) {
                return response()->json([
                'error' => 'false',
                'message' => 'Time is older than 1 hour.'
                ], 422);
            }
            }
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
            // dd($farmer_acre);
            
            $farmer_plot_area = DB::table('farmer_plot_detail')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('plot_no', 1)->first();
            // dd($farmer_plot_area->area_other_awd);
            
            // If farmer_plot_uniqueid is null, update it with the provided value
            if ($existingNullFarmer) {
                // dd("in");
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
                // $farmer->surveyor_name  = auth()->user()->name;
                // $farmer->surveyor_email  = auth()->user()->email ?? NULL;
                // $farmer->surveyor_mobile  = auth()->user()->mobile;
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
                // $farmer->no_of_plots = $request['no_of_plots'];
                $farmer->organization_id = $existingFarmerDetails->organization_id;
                $farmer->gender = $existingFarmerDetails->gender;
                $farmer->guardian_name = $existingFarmerDetails->guardian_name;
                $farmer->status_onboarding = 'Pending';
                $farmer->final_status_onboarding = 'Pending'; // need to do direct approval so that it is easily available for crop data
                $farmer->onboarding_form      = 1;
                $farmer->area_in_acers      =  $farmer_acre->area_in_acers;
                $farmer->own_area_in_acres =  $farmer_acre->own_area_in_acres;
                $farmer->lease_area_in_acres =  $farmer_acre->lease_area_in_acres;
                $farmer->final_status       = 'Pending';
                $farmer->onboard_completed  = 'Pending';
                $farmer->L2_aprv_timestamp      = Carbon::now(); //by default adding current time in approval time
                $farmer->L2_appr_userid      = 1;
                // $farmer->L1_appr_timestamp      =  Carbon::now(); //by default adding current time in approval time
                // $farmer->L1_aprv_recj_userid      = 1;
                // farmer location 
                $farmer->country_id =   $farmer_acre->country_id;
                // $farmer->country =   $farmer_acre->country;
                $farmer->state_id =   $farmer_acre->state_id;
                // $farmer->state =   $farmer_acre->state;
                $farmer->district_id =   $farmer_acre->district_id;
                // $farmer->district =   $farmer_acre->district;
                $farmer->taluka_id =   $farmer_acre->taluka_id;
                // $farmer->taluka =   $farmer_acre->taluka;
                $farmer->panchayat_id =   $farmer_acre->panchayat_id;
                // $farmer->panchayat =   $farmer_acre->panchayat;
                $farmer->village_id =   $farmer_acre->village_id;
                // $farmer->village =   $farmer_acre->village;
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
                $farmer->save();
            }

            $plot = DB::table('pipe_installations')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
            if ($plot) {
                if ($plot->ranges == null) {
                    DB::table('pipe_installations')
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

            // $area_plot = DB::table('farmer_plot_detail')->select('area_acre_awd')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
            // $onboarding_area_acres = $area_plot->area_acre_awd;//for odisha
            $pipe = PipeInstallation::create([
                'farmer_id' => $request->farmer_id,
                'farmer_uniqueId' => $request->farmer_uniqueId,
                'farmer_plot_uniqueid' => $request->farmer_plot_uniqueid,
                'plot_no' => $numericPart,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'state' => $request->state,
                'district' => $request->district,
                'taluka' => $request->taluka,
                'village' => $request->village,
                'panchayat' => $request->panchayat,
                'khasara_no' => $request->khasara_no,
                "acers_units"     => $request->acers_units,
                "area_in_acers"   => $farmer_acre->area_in_acers,
                "plot_area"       => $request->plot_area,
                'status' => 'Approved', // Assuming it's initially approved
                'apprv_reject_user_id' => 1, // Set the user ID as needed
                'surveyor_id' => auth()->user()->id,
                'surveyor_name' => auth()->user()->name,
                'surveyor_mobile' => auth()->user()->mobile,
                'date_survey' => now()->format('d/m/Y'),
                'date_time' => now()->toTimeString(),
                'polygon_date_time' => $request->polygon_date_time ?? null,
                'ranges' => json_encode($request->ranges),
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
                'success' => true, 'message' => 'Pipe Store Successfully',
                'FarmerId' => $request->farmer_id, 'FarmerUniqueID' => $request->farmer_uniqueId, 'PlotNo' => $numericPart
            ], 200);
        } catch (\Exception $e) {
            
            return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
        }
    }

    
    public function no_of_pipe_new(Request $request)
    {   
     $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
    
     $area = $request->input('plot_area')??"1";
    
     $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
        ->where('plot_no',$request->plot_no)
        ->where('status','Approved')
        ->where('trash',0)
        ->get();
    
         if($pipe_data->isNotEmpty() && isset($pipe_data[0]->financial_year) && isset($pipe_data[0]->season)){
             foreach ($pipe_data as $data) {
                 if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                     return response()->json(['error' => true, 'message' => 'Requested year and season already exist.'], 422);
                 }
             }
         }
    
         $requiredPipes = $this->getRequiredPipes($area);
    
        
     return response()->json(['success'=>true,
                                              'uinique_id'=>$request->farmer_uniqueId,
                                              'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid,
                                            //   'plot_area'=>$plot_check,
                                              'required_pipes'=> "1",//$acresInhectares, curenlty we are sending 1 by default
                                             ],200);
    
          if(!$farmer)
          {
            return response()->json(['error'=>true,
            "message" => "No data found",
           ],422);
          }
    }
    /**
   * Pipe location store new
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function pipes_location(Request $request){
    // \Log::info('upload pipeimage');
    \Log::info('Installation Store req: ', $request->all());

        if(!$request->hasFile('images')){
            return response()->json(['error'=>'false','message'=>'Please upload image'],422);
        }
       $pipe_img = new PipeInstallationPipeImg;
       $pipe_img->farmer_uniqueId          =  $request->farmer_uniqueId;
       $pipe_img->farmer_plot_uniqueid          =  $request->farmer_plot_uniqueid;
       $pipe_img->plot_no          =  $request->plot_no;
       $pipe_img->pipe_no          =  $request->pipe_no;
       $pipe_img->lat          =  $request->lat;
       $pipe_img->lng          =  $request->lng;
       $pipe_img->status        = 'Approved';
       $pipe_img->financial_year=$request->financial_year;
       $pipe_img->season=$request->season;
       
       // Handle array of images
       $imageUrls = [];
       if ($request->hasFile('images')) {
           $images = $request->file('images');
           
           // Check if it's a single file or array
           if (!is_array($images)) {
               $images = [$images];
           }
           
           foreach ($images as $image) {
               if ($image && $image->isValid()) {
                   // Generate unique filename
                   $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                   
                   // Store image to S3
                   $path = Storage::disk('s3')->putFileAs(
                       config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_plot_uniqueid.'/'.'P'.$request->plot_no.'/'.'PipeInstallation',
                       $image,
                       $filename
                   );
                   
                   // Get full URL
                   $imageUrl = Storage::disk('s3')->url($path);
                   $imageUrls[] = $imageUrl;
               }
           }
       }
       
       // Store images as JSON
       $pipe_img->images = json_encode($imageUrls);
       $pipe_img->distance      =  $request->distance;
       $pipe_img->date          =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('d-m-Y');
       $pipe_img->time          =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('h:i A');
       $pipe_img->surveyor_id   =  auth()->user()->id;
       $pipe_img->save();

    //     $pipe = Polygon::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
    //     $pipe->installed_pipe  = $pipe->installed_pipe + 1;
    //    $pipe->no_pipe_req     = $request->no_pipe_req;
    //    $pipe->no_pipe_avl     = $request->no_pipe_avl;
    //    $pipe->installing_pipe = $request->installing_pipe;
    //    $pipe->financial_year=$request->financial_year;
    //    $pipe->season=$request->season;
    //     $pipe->save();


    $userTarget = UserTarget::updateOrCreate(
        [
            'user_id' => auth()->user()->id,
            'module_id' => '4',
            'date' => now()->toDateString(),
        ],
        [
            'count' => DB::raw('count + 1'),
        ]
    );
        if(!$pipe_img){
           return response()->json(['error'=>true,'message'=>'Somethings went wrong'],422);
        }
        return response()->json(['success'=>true,'message'=>'Pipe image Successfully uploaded','FarmerId'=>$request->farmer_id, 'FarmerUniqueID'=>$request->farmer_uniqueId,'FarmerUniquePlotID'=>$request->farmer_plot_uniqueid,'PlotNo'=>$request->plot_no,'pipeno'=>$request->pipe_no],200);

    }


    // $check_poly = PipeInstallation::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
    //     ->whereNotNull('ranges')
    //     ->select('id', 'ranges', 'farmer_plot_uniqueid')
    //     ->latest()
    //     ->first();

    // if (!$check_poly) {

    // }
    public function update_location(Request $request)
    {
        try{
            $check_poly_polygon = Polygon::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                ->whereNotNull('ranges')
                ->select('id', 'ranges', 'farmer_plot_uniqueid')
                ->latest()
                ->first();
            
            if (!$check_poly_polygon) {
                return response()->json(['error' => true, 'message' => 'Polygon Not Found'], 422);
            }
            
            // Check for an existing pipe
            $new_pipe = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                ->where('pipe_no', $request->pipe_no)
                ->first();
            
            if ($new_pipe) {
                // Update latitude and longitude for the pipe
                $new_pipe->lat = $request->latitude;
                $new_pipe->lng = $request->longitude;
                $new_pipe->save();
            
                return response()->json([
                    'success' => true,
                    'message' => 'Pipe location has been updated',
                    'lat' => $new_pipe->lat,
                    'lng' => $new_pipe->lng
                ], 200);
            } else {
                return response()->json(['error' => true, 'message' => 'Pipe not found'], 404);
            }
        }catch (\Exception $e) {
        //    dd($e);
           return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
       }
}
        // Check for an existing polygon
     


    // Commented because now only available pipe is 1
//    public function no_of_pipe(Request $request){

//       $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
//       $farmerplot = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
//       $FarmerPlotdetail = FarmerPlot::where('farmer_plot_uniqueid',$farmerplot->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
//       if(!$farmerplot){
//           return response()->json(['error'=>true, 'message'=>'Plot Not available'],422);
//       }
//       $pipesettings = DB::table('pipe_settings')->where('id',1)->first();//from DB pipe_settings
//       $nohectares =  DB::table('settings')->select('no_of_hectares')->where('id',1)->first();//from DB settings
//         $acresInhectares="";
//         if($pipesettings->type == 'hectare'){// checking if using hectare
//         //   $acresInhectares = $FarmerPlotdetail->area_acre_awd;
//         $acresInhectares = $farmerplot->plot_area;
//           for ($x = 1; $x <= $nohectares->no_of_hectares; $x++){
//             if(0 <  $acresInhectares && $acresInhectares <= $pipesettings->area * $x){
//                 // dd('in','area in hect '.$acresInhectares, 'actual area of hec '.$x.' '.$pipesettings->area * $x);
//                 $acresInhectares = (int) $acresInhectares;
//                 if($acresInhectares == 0){
//                     $acresInhectares = 1;
//                 }
//                 return response()->json(['success'=>true,
//                                          'uinique_id'=>$request->farmer_uniqueId,
//                                          'farmer_plot_uniqueid' => $farmerplot->farmer_plot_uniqueid,
//                                          'plot_area'=>$farmerplot->plot_area,
//                                          'required_pipes'=>"1",//$acresInhectares, curenlty we are sending 1 by default

//                                          'areainhectare' =>$acresInhectares,
//                                          'hctares '.(int) $acresInhectares =>  $pipesettings->area * $x,

//                                          'no' =>$x,
//                                         ],200);
//             }//if end
//           }//forloop end

//           // if(0 <  $acresInhectares && $acresInhectares <= $pipesettings->area){
//           //     dd('in', $pipesettings->area, $acresInhectares,'required pipe 1');
//           // }elseif($acresInhectares > $pipesettings->area && $acresInhectares <= $pipesettings->area * 2){
//           //   dd('in twice hectare',$pipesettings->area * 2,$acresInhectares ,'required pipe 2');
//           // }
//         }// end hectare
//         elseif($pipesettings->type == 'acres'){
//         //   dd('in acres');
//         }else{
//              return response()->json(['error'=>true, 'message'=>'Plot Not available'],422);
//         }

//    }


//Added on 04-02-2025 by Akash
public function pipeinstallation_no(Request $request)
{
    \Log::info('Pipe Installation Pipe Number: ' . json_encode($request->all()));

    $farmer = FinalFarmer::where('farmer_uniqueId', $request->farmer_uniqueId)
        ->where('plot_no', $request->plot_no)
        ->first();

    if (!$farmer) {
        return response()->json(['error' => true, 'message' => 'No data found'], 422);
    }

    $area = $request->input('plot_area') ?? "1";

    // Get the required pipes for the area
    $requiredPipes = $this->getRequiredPipes($area);

    // Check existing pipes
    $existingPipes = DB::table('pipe_installation_pipeimg')
        ->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
        ->where('plot_no', $request->plot_no)
        ->where('season', $request->season)
        ->where('financial_year', $request->financial_year)
        ->where('status', 'Approved')
        ->where('trash', 0)
        ->pluck('pipe_no')
        ->toArray();

    $pipeNumbers = range(1, $requiredPipes);
    $availablePipeNumbers = array_values(array_diff($pipeNumbers, $existingPipes));

    // Check if all required pipes are filled
    if (count($availablePipeNumbers) === 0) {
        return response()->json([
            'error' => true,
            'message' => 'All required pipes are already filled.',
        ], 423);
    }

    // Check for year and season conflict
    $pipe_data = DB::table('pipe_installation_pipeimg')
        ->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
        ->where('plot_no', $request->plot_no)
        ->where('season', $request->season)
        ->where('financial_year', $request->financial_year)
        ->where('status', 'Approved')
        ->where('trash', 0)
        ->get();

        //Need to add the below code to check the year and season conflict in next Realease
    // foreach ($pipe_data as $data) {
    //     if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
    //         return response()->json(['error' => true, 'message' => 'Requested year and season already exist.'], 422);
    //     }
    // }

    return response()->json([
        'success' => true,
        'unique_id' => $request->farmer_uniqueId,
        'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid,
        'required_pipes' => $requiredPipes,
        'pipe_no_list' => $availablePipeNumbers,
    ], 200);
}

public function getRequiredPipes($area)
{
    // Log the area being processed
    \Log::info('Processing area: ' . $area);

    $pipeSetting = DB::table('pipe_settings')
    ->whereRaw('CAST(less_area AS DECIMAL(10, 6)) <= ?', [$area])
    ->whereRaw('CAST(max_area AS DECIMAL(10, 6)) >= ?', [$area])
    ->first();

    if ($pipeSetting) {
        \Log::info('Pipe setting found: ' . json_encode($pipeSetting));
        return $pipeSetting->no_of_pipe ?? "1";
    } else {
        $maxPipeSetting = DB::table('pipe_settings')
            ->orderBy('no_of_pipe', 'desc')
            ->first();
            // dd($maxPipeSetting);

        if ($area > $maxPipeSetting->max_area) {
            // \Log::info('Area exceeds max setting. Returning maximum pipes: ' . $maxPipeSetting->no_of_pipe);
            return $maxPipeSetting->no_of_pipe ?? "1";
        }

        return "1";
    }
}


public function incrementPipeSettings(Request $request)
{
    // Fetch the latest pipe settings record
    $maxPipeSetting = DB::table('pipe_settings')
        ->orderBy('id', 'desc')
        ->first();

    if (!$maxPipeSetting) {
        return response()->json(['message' => 'No existing pipe settings found.', 'status' => false], 404);
    }

    if ($maxPipeSetting->no_of_pipe >= 100) {
        return response()->json(['message' => 'Max pipe limit reached', 'status' => false], 400);
    }

    // Increment the values by 1
    $newLessArea = number_format($maxPipeSetting->less_area + 1, 6);
    $newMaxArea = number_format($maxPipeSetting->max_area + 1, 6);
    $newNoOfPipe = min($maxPipeSetting->no_of_pipe + 1, 100); // Ensure it doesn't exceed 100

    // Insert the new entry
    DB::table('pipe_settings')->insert([
        'unit' => '1',
        'area' => '2.471053',
        'less_area' => $newLessArea,
        'max_area' => $newMaxArea,
        'no_of_pipe' => $newNoOfPipe,
        'type' => 'hectare',
        'status' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return response()->json(['message' => 'Pipe setting incremented successfully', 'status' => true], 201);
}

    /**
     * Get images array from JSON string
     */
    public function getImagesArray($jsonString)
    {
        if (empty($jsonString)) {
            return [];
        }
        
        $decoded = json_decode($jsonString, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get first image URL from JSON string
     */
    public function getFirstImage($jsonString)
    {
        $images = $this->getImagesArray($jsonString);
        return !empty($images) ? $images[0] : null;
    }

    /**
     * Get all image URLs from JSON string
     */
    public function getAllImages($jsonString)
    {
        return $this->getImagesArray($jsonString);
    }

    public function submit_pipe_data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farmer_uniqueId' => 'required',
            'farmer_plot_uniqueid' => 'required',
            'plot_no' => 'required',
        ]);
    }
}
