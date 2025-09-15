<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use PDF;
use Storage;
use Carbon\Carbon;
use DB;
use App\Models\FarmerPlot;
use App\Models\PipeInstallation;
use App\Models\FinalFarmer;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallationPipeImg;
use App\Models\PlotStatusRecord;

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
        $plot = DB::table('pipe_installations')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
        if($plot){
            $plot->ranges = json_decode($plot->ranges);

            if($plot->ranges){
                $polygon_status = 1;
            }else{
                $polygon_status = 0;
            }

            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                                                ->where('plot_no',$request->plot_no)
                                                ->where('status','Pending')
                                                ->where('trash',0)
                                                ->get();

            if($pipe_data->count() > 0){
                $plot->pipes_location = $pipe_data;
                $status = 1;
            }else{
                $status = 0;
            }

            return response()->json(['error'=>true,'message'=>'Data Available','data'=>$plot,'status'=>$status,'polygon_status' =>$polygon_status],200);
        }else{
            return response()->json(['error'=>true,'data'=>'No Data'],422);
        }
   }

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
   public function check_polygon_nearby(Request $request){
        //get data of plot, for which polygon is going to store
        $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        //now checking all record in pipeinstallation table, with the data of stateid, districtid, talukaid, panchayatid,villageid, using farmerapproved function
        $polygon_data = PipeInstallation::select('ranges')->whereHas('farmerapproved', function($q) use($plot){
                        if($plot->state_id){
                            $q->where('state_id',$plot->state_id);
                        }
                        if($plot->district_id){
                             $q->where('district_id',$plot->district_id);
                        }
                        //below is commented just because to avoid filter  
                        // if($plot->taluka_id){
                        //      $q->where('taluka_id',$plot->taluka_id);
                        // }
                        // if($plot->panchayat_id){
                        //      $q->where('panchayat_id',$plot->panchayat_id);
                        // }
                        // if($plot->village_id){
                        //      $q->where('village_id',$plot->village_id);
                        // }
                        if($plot->farmer_plot_uniqueid){
                            //this condition is used to avoid same plot in response
                            $q->where('farmer_plot_uniqueid', '!=',$plot->farmer_plot_uniqueid);
                        }
                        return $q;
                        })
                        ->get();


        $nearby_polygons = array();//define array

        $radius = 0.3;//0.25;//1;//define radius for calculation
        foreach($polygon_data as $data){
            $polygon = array_values(json_decode($data->ranges, true));
             foreach($polygon as $pol){
                // haversine function has three parameter 1. plot lat from app 2. plot lng from app 3. fetch lat from db, 4. fetch lng from db
                $distance = $this->haversine($request->lat, $request->lng, $pol['lat'], $pol['lng']);
             }

            if ($distance <= $radius) {// if distance calculated by haversine function is less than radius than add polygon in $nearby_polygons valriable
                $nearby_polygons[] = $polygon;
            }
        }

        $polygon_data=[];
        foreach($nearby_polygons as $data){
            //now checking if data at index 0 = to data at last index
            $firstarray = $data[0];
            $lastarray = $data[array_key_last($data)];

            if($firstarray != $lastarray){
                $data[array_key_last($data) + 1]=$data[0];
            }
            // assign data to ranges key
            $data = array(['ranges'  =>  $data]);
            $polygon_data[]= $data;
        }
        if($polygon_data){
            return response()->json($polygon_data);
        }else{
            return response()->json(['error'=>true,'data'=>'No Data'],422);
        }
   }

     function haversine($lat1, $lng1, $lat2, $lng2) {
        $earth_radius = 6371; // in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earth_radius * $c;
        return $distance;
    }


    /**
    * Filter record and ceck for corrdinates inside polygon
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public function check_lat_lng_inside_polygon(Request $request){
        if (version_compare(phpversion(), '7.1', '>=')) {
           ini_set( 'precision', 17 );
           ini_set( 'serialize_precision', -1 );
        }
        $plot = DB::table('final_farmers')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
        $polygon_data = PipeInstallation::select('ranges')->whereHas('farmerapproved', function($q) use($plot){
                        if($plot->state_id){
                            $q->where('state_id',$plot->state_id);
                        }
                        if($plot->district_id){
                             $q->where('district_id',$plot->district_id);
                        }
                        if($plot->taluka_id){
                             $q->where('taluka_id',$plot->taluka_id);
                        }
                        if($plot->panchayat_id){
                             $q->where('panchayat_id',$plot->panchayat_id);
                        }
                        if($plot->village_id){
                             $q->where('village_id',$plot->village_id);
                        }
                        if($plot->farmer_plot_uniqueid){
                            //this condition is used to avoid same plot in response
                            $q->where('farmer_plot_uniqueid', '!=',$plot->farmer_plot_uniqueid);
                        }
                        return $q;
                        })
                    ->get();
         $inside=false;
        foreach($polygon_data as $items){
                $range_data=collect($items)->map(function($items){
                    $items=collect(json_decode($items))->map(function($latlng){
                        return collect($latlng)->toArray();
                    });
                    return $items;
                })->toArray();
                if(count($range_data)>0){
                    foreach($range_data as $points){
                        $vertices_x=[];
                        $vertices_y=[];
                        $points_polygon = count($points);
                        foreach($points as $point){
                            $vertices_x[]=$point['lat'];
                            $vertices_y[]=$point['lng'];
                        }
                        $check_inside =  $this->inside($request->lat, $request->lng, $range_data['ranges']);
                        if($check_inside){
                            return response()->json(['status' =>true, 'lat'=>$request->lat, 'lng'=> $request->lng,'message'=>'Inside Polygon'],200);
                            break;
                        }
                    }//foreach end
                }//if end
        }//foreach end
        return response()->json(['status' =>false, 'lat'=>$request->lat, 'lng'=> $request->lng,'message'=>'OutSide Polygon'],422);
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
   public function no_of_pipe(Request $request){

      $farmer = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
      $farmerplot = FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->where('plot_no',$request->plot_no)->first();
      $FarmerPlotdetail = FarmerPlot::where('farmer_plot_uniqueid',$farmerplot->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
      if(!$farmerplot){
          return response()->json(['error'=>true, 'message'=>'Plot Not available'],422);
      }
      $pipesettings = DB::table('pipe_settings')->where('id',1)->first();//from DB pipe_settings
      $nohectares =  DB::table('settings')->select('no_of_hectares')->where('id',1)->first();//from DB settings
        $acresInhectares="";
        if($pipesettings->type == 'hectare'){// checking if using hectare
          $acresInhectares = $FarmerPlotdetail->area_acre_awd;
          for ($x = 1; $x <= $nohectares->no_of_hectares; $x++){
            if(0 <  $acresInhectares && $acresInhectares <= $pipesettings->area * $x){
                // dd('in','area in hect '.$acresInhectares, 'actual area of hec '.$x.' '.$pipesettings->area * $x);
                $acresInhectares = (int) $acresInhectares;
                if($acresInhectares == 0){
                    $acresInhectares = 1;
                }
                return response()->json(['success'=>true,
                                         'uinique_id'=>$request->farmer_uniqueId,
                                         'farmer_plot_uniqueid' => $farmerplot->farmer_plot_uniqueid,
                                         'plot_area'=>$FarmerPlotdetail->area_acre_awd,
                                         'required_pipes'=>"1",//$acresInhectares, curenlty we are sending 1 by default

                                         'areainhectare' =>$acresInhectares,
                                         'hctares '.(int) $acresInhectares =>  $pipesettings->area * $x,

                                         'no' =>$x,
                                        ],200);
            }//if end
          }//forloop end

          // if(0 <  $acresInhectares && $acresInhectares <= $pipesettings->area){
          //     dd('in', $pipesettings->area, $acresInhectares,'required pipe 1');
          // }elseif($acresInhectares > $pipesettings->area && $acresInhectares <= $pipesettings->area * 2){
          //   dd('in twice hectare',$pipesettings->area * 2,$acresInhectares ,'required pipe 2');
          // }
        }// end hectare
        elseif($pipesettings->type == 'acres'){
          dd('in acres');
        }else{
             return response()->json(['error'=>true, 'message'=>'Plot Not available'],422);
        }

   }

   /**
   * Pipe info store
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function store(Request $request){
         $validator = Validator::make($request->all(),[
              'latitude' => 'required',
              'longitude' => 'required',
              'state' => 'required',
              'district' => 'required',
              'taluka' => 'required',
              'village' => 'required',
              'khasara_no' => 'required',
          ]);
        $plot = DB::table('pipe_installations')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
        if($plot){
            $plot->ranges = json_decode($plot->ranges);

            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                        ->where('plot_no',$request->plot_no)
                        ->where('status','Pending')
                        ->where('trash',0)
                        ->get();
            $plot->pipes_location = $pipe_data;

            return response()->json(['error'=>true,'message'=>'Data Available','data'=>$plot],422);
        }
       try{
            $area_plot = DB::table('farmer_plot_detail')->select('area_acre_awd')->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
           
            $onboarding_area_acres = $area_plot->area_acre_awd;//for odisha
            $pipe = PipeInstallation::create([
               "farmer_id"       => $request->farmer_id,
               "farmer_uniqueId" => $request->farmer_uniqueId,
               "farmer_plot_uniqueid"   => $request->farmer_plot_uniqueid,
               "plot_no"         => $request->plot_no,
               "ranges"          => json_encode($request->ranges),
               "latitude"        => $request->latitude,
               "longitude"       => $request->longitude,
               "state"           => $request->state,
            //   "district"        => $request->district,
            //   "taluka"          => $request->taluka,
            //   "village"         => $request->village,
               "khasara_no"      => $request->khasara_no,
               "acers_units"     => $request->acers_units,
               "area_in_acers"   => $onboarding_area_acres,
               "plot_area"       => $request->plot_area,
               "status"          => 'Approved',
               'apprv_reject_user_id'=>1,
               "surveyor_id"     => auth()->user()->id,
               "surveyor_name"   => auth()->user()->name,
               "surveyor_mobile" => auth()->user()->mobile,
               'date_survey'     => Carbon::parse(Carbon::now())->format('d/m/Y'),
               'date_time'       => Carbon::now()->toTimeString(),
               'polygon_date_time'  => $request->polygon_date_time??NULL,
            ]);
            $record =  PlotStatusRecord::create([
                 'farmer_uniqueId'           => $request->farmer_uniqueId,
                 'plot_no'                   => $request->plot_no,
                 'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
                 'level'                     => 'AppUser',
                 'status'                    => 'Pending',
                 'comment'                   => 'Uploaded PipeInstallation Data',
                 'timestamp'                 => Carbon::now(),
                 'user_id'                   => auth()->user()->id,
             ]);
            FinalFarmer::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update(['pipe_form'=>1]);
           if(!$pipe){
              return response()->json(['error'=>true,'message'=>'Somethings went wrong']);
           }
           return response()->json(['success'=>true,'message'=>'Pipe Store Successfully',
                                        'FarmerId'=>$request->farmer_id, 'FarmerUniqueID'=>$request->farmer_uniqueId,'PlotNo'=>$request->plot_no],200);
      }catch(Exception $e){
         return response()->json(['error'=>true,'message'=>'Somethings went wrong'],500);
      }
   }



    /**
   * Pipe location store new
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function pipes_location(Request $request){
       $pipe_img = new PipeInstallationPipeImg;
       $pipe_img->farmer_uniqueId          =  $request->farmer_uniqueId;
       $pipe_img->farmer_plot_uniqueid          =  $request->farmer_plot_uniqueid;

       $pipe_img->plot_no          =  $request->plot_no;
       $pipe_img->pipe_no          =  $request->pipe_no;
       $pipe_img->lat          =  $request->lat;
       $pipe_img->lng          =  $request->lng;
       $pipe_img->status        = 'Approved';
    //    $path = $request->images->storeAs(config('storagesystems.final_store').'/'.$request->farmer_uniqueId.'/'.'P'.$request->plot_no.'/'.'PipeInstalltion', $request->file('images')->getClientOriginalName(), 'public');
       $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_plot_uniqueid.'/'.'P'.$request->plot_no.'/'.'PipeInstallation', $request->images);
       $pipe_img->images        =  Storage::disk('s3')->url($path);

       $pipe_img->distance      =  $request->distance;
       $pipe_img->date          =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('d-m-Y');
       $pipe_img->time          =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('h:i A');
       $pipe_img->surveyor_id   =  auth()->user()->id;
       $pipe_img->save();

        $pipe = PipeInstallation::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
        $pipe->installed_pipe  = $pipe->installed_pipe + 1;
        $pipe->no_pipe_req     = $request->no_pipe_req;
        $pipe->no_pipe_avl     = $request->no_pipe_avl;
        $pipe->installing_pipe = $request->installing_pipe;
        $pipe->save();


        if(!$pipe_img){
           return response()->json(['error'=>true,'message'=>'Somethings went wrong'],422);
        }
        return response()->json(['success'=>true,'message'=>'Pipe image Successfully','FarmerId'=>$request->farmer_id, 'FarmerUniqueID'=>$request->farmer_uniqueId,'FarmerUniquePlotID'=>$request->farmer_plot_uniqueid,'PlotNo'=>$request->plot_no,'pipeno'=>$request->pipe_no],200);

    }

}
