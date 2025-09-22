<?php

namespace App\Http\Controllers\Admin\l2validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;
use App\Models\PipeImgValidation;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\User;
use Auth;
use App\Exports\PipeInstallationIndividualExport;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallation;
use App\Models\FarmerCropdata;
use App\Models\Polygon;
use App\Models\PolygonValidation;
use App\Models\Setting;

class L2PipeValidationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search($status)
    {
        if($status == 'Pending'){
            $plots = PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){

                    return $q;
                  })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){
                        $c->where('status','Approved');
                        $c->where('l2status','Pending');
                        return $c;
                    });
                    return $im;
                })
                ->when('filter',function($w){

                });
            return response()->json($plots->get());

        }elseif($status == 'Approved'){
            $plots = PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){

                    return $q;
                  })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){
                        $c->where('l2status','Approved');
                        return $c;
                    });
                    return $im;
                })
                ->when('filter',function($w){
                    $w->where('l2_status','Approved');
                    $w->where('l2_apprv_reject_user_id',auth()->user()->id);
                });
            return response()->json($plots->get());
        }elseif($status == 'Rejected'){
            $plots = PipeInstallation::with('farmerapproved')->whereHas('farmerapproved',function($q){

                    return $q;
                  })
                ->whereHas('pipe_image',function($im){
                    $im->when('filter',function($c){
                        $c->where('l2status','Rejected');
                        return $c;
                    });
                    return $im;
                })
                ->when('filter',function($w){

                });
            return response()->json($plots->get());
        }
    }

  /**
   * level 2 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  //OLd method
//   public function pipe_pending_lists()
//   {
//     // dd("in");
//     // $a = PipeInstallation::where('farmer_plot_uniqueid','122465P2')->with('pipe_image')->first();
//     // dd($a);
//     //level 2 validator get pending plot list of pipe function
// 	  //Plot list
// 	  if(request()->ajax()){
//   		$plots = PipeInstallation::with('farmerapproved','pipe_image')->where('status','Approved')->whereHas('farmerapproved',function($q){
//             if(auth()->user()->hasRole('L-2-Validator')){
               
//                 $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
//                     $q->whereIn('state_id',explode(',',$VendorLocation->state));
//                     if(!empty($VendorLocation->district)){
//                     $q->whereIn('district_id',explode(',',$VendorLocation->district));
//                     }
//                     return $q;
//                 } 
//         if(request()->has('state') && !empty(request('state'))){
//             $q->where('state_id','like',request('state'));
//         }
//         if(request()->has('district') && !empty(request('district'))){
//              $q->where('district_id','like',request('district'));
//         }
//         if(request()->has('taluka') && !empty(request('taluka'))){
//              $q->where('taluka_id','like',request('taluka'));
//         }
//         if(request()->has('panchayats') && !empty(request('panchayats'))){
//              $q->where('panchayat_id','like',request('panchayats'));
//         }
//         if(request()->has('village') && !empty(request('village'))){
//              $q->where('village_id','like',request('village'));
//         }
//         if(request()->has('farmer_status') && !empty(request('farmer_status'))){
//              $q->where('final_status_onboarding','like',request('farmer_status'));
//         }
//         return $q;
//   		})
//         ->whereHas('pipe_image',function($im){
//             $im->when('filter',function($c){
//                 $c->where('l2status','Pending');
//                 return $c;
//             });
//             return $im;
//         })
//         ->when('filter',function($w) {
//             if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
//                 $w->where('surveyor_id',request('executive_onboarding'));
//             }
//             if(request()->has('start_date') && !empty(request('start_date'))){
//                 $w->whereDate('created_at','>=',request('start_date'));
//             }
//             if(request()->has('end_date') && !empty(request('end_date'))){
//                 $w->whereDate('created_at','<=',request('end_date'));
//             }
//             return $w;
//         })
//         ->orderBy('id','desc');
//         //end of datatable
//   		return datatables()->of($plots)->make(true);

// 	  }//end layoutout plot WITH AJAX
//        // Onload below code excute first. And after successful load then again ajax make request to above code
//   		$page_title = 'PipeInstallation | Pending list';
//   		$page_description = 'PipeInstallation | Pending list';
//   		$action = 'table_farmer';
//   		//below process is for first time landing on page}
//         //for admin data
//         $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
//         $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
//         $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
//         $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
//           $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
//           $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
//           $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();
//   	  $seasons = DB::table('seasons')->get();
//       $status = request()->status;
//       $others = "0";
//   	  return view('l2validator.pipe.pending-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
//   }

public function pipe_pending_lists()
  {
    // dd(' cb');
    $farmer_uniqueId = request('farmer_uniqueId')??'';
    $plots_paginate = PipeInstallationPipeImg::with('farmerapproved')
    ->where('l2status', 'Pending')
    ->whereHas('farmerapproved', function ($q) {

        if(request()->has('state') && !empty(request('state'))){
            $q->where('state_id','like',request('state'));
        }
        if(request()->has('district') && !empty(request('district'))){
             $q->where('district_id','like',request('district'));
        }
        if(request()->has('taluka') && !empty(request('taluka'))){
             $q->where('taluka_id','like',request('taluka'));
        }
        if(request()->has('panchayats') && !empty(request('panchayats'))){
             $q->where('panchayat_id','like',request('panchayats'));
        }
        if(request()->has('village') && !empty(request('village'))){
             $q->where('village_id','like',request('village'));
        }
        if(request()->has('farmer_status') && !empty(request('farmer_status'))){
             $q->where('final_status_onboarding','like',request('farmer_status'));
        }
        if(request()->has('farmer_uniqueId') && !empty(request('farmer_uniqueId'))){
             $q->where('farmer_uniqueId',request('farmer_uniqueId'));
        }

        if (auth()->user()->hasRole('L-2-Validator')) {
            $VendorLocation = DB::table('vendor_locations')->where('user_id', auth()->user()->id)->first();
            $q->whereIn('state_id', explode(',', $VendorLocation->state));
            if (!empty($VendorLocation->district)) {
                $q->whereIn('district_id', explode(',', $VendorLocation->district));
            }
        }
    })->orderBy('id','Desc')
    ->paginate(25);
    $path = $plots_paginate->links()->elements;
    $links=[];
    foreach ($path as $key=>$item) {
      if (is_array($item)) {
          $links = array_merge($links, $item);
      }
    }


  		$page_title = 'PipeInstallation | Pending list';
  		$page_description = 'PipeInstallation | Pending list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
        $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
        // $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
        $districts=[];
        // $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
        $talukas =[];
        //   $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
        $panchayats =[];
        //   $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        $villages=[];
          $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();
  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.pipe.pending-plot',compact('farmer_uniqueId','links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }



  public function pipe_pending_detail($plotuniqueid){
    // dd("in1");
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();

    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = Polygon::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    // $PipesLocation="";
    // if($PipeInstallation->pipes_location){
    //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
    // }

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('l2trash',0)->where('farmer_plot_uniqueid',$plotuniqueid)->where('l2status','Pending')->get();
    
    $check_pipedata = DB::table('polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =
    $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();

    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->where('id','8')->get();
    $page_title = 'PipeInstallation | Pending Detail';
    $page_description = 'PipeInstallation | Pending Detail';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
        if($plot->area_in_acers ){
            $mod =  abs($plot->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
            $denominator = $plot->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
            //below percentage error between onboarding area and updated area
            $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
        }
        
    return view('l2validator.pipe.pending-plot-pipe',compact('plot','PipeInstallation','PipesLocation','cropdata','Polygon','page_title','page_description','action','farmerplots','farmerplot',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error','check_pipedata'));
  }

   /**
     * update polygon
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_polygon(Request $request){
        try {
            $pipe_data=Polygon::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
            $process_update_polygon = json_decode($request->updatedpolygon);
            foreach($process_update_polygon  as $index=>$array){
                $data = ["lat"=> $array[0], "lng" =>  $array[1]];
                $multipolygon[$index] = $data;
            }
            $updated_polygon = json_encode($multipolygon);//after processing got polygon.
            //now moving old polygon to table = old_polygon
            //adding old polygon in table, just to hold old record


            DB::table('old_polygons')->insert([
                "farmer_uniqueId"   =>  $pipe_data->farmer_uniqueId,
                "farmer_plot_uniqueid"     =>  $pipe_data->farmer_plot_uniqueid,
                "plot_no"   =>  $pipe_data->plot_no,
                "polygon"   =>  $pipe_data->ranges,//adding old polygon from pipeinstallation table
                "surveyor_id"   =>  auth()->user()->id,
                "type"          => 'OLD POLYGON',
                "google_plot_area" => $pipe_data->plot_area,
                "created_at"     => carbon::now(),
                "updated_at"    => carbon::now(),
            ]);
            //adding update polygon ,just to hold old record
            // DB::table('old_polygons')->insert([
            //     "farmer_uniqueId"   =>  $pipe_data->farmer_uniqueId,
            //     "farmer_plot_uniqueid"     =>  $pipe_data->farmer_plot_uniqueid,
            //     "plot_no"   =>  $pipe_data->plot_no,
            //     "polygon"   =>  $updated_polygon,//adding old polygon from pipeinstallation table
            //     "surveyor_id"   =>  auth()->user()->id,
            //     "type"          => 'UPDATED POLYGON',
            //     "created_at"     => carbon::now(),
            //     "updated_at"    => carbon::now(),
            // ]);

            //now adding update polygon to origin al table
            Polygon::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update([
                'ranges' =>$updated_polygon,
                'plot_area' => number_format($request->updated_poly_area, 2, '.', ''),
                        ]);
            $record =  PlotStatusRecord::create([
                'farmer_uniqueId'           => $pipe_data->farmer_uniqueId,
                'plot_no'                   => $pipe_data->plot_no,
                'farmer_plot_uniqueid'      => $pipe_data->farmer_plot_uniqueid,
                'level'                     => 'L-2-Validator',
                'status'                    => $pipe_data->status,
                'comment'                   => 'Polygon Updated From WEB',
                'timestamp'                 => Carbon::now(),
                'user_id'                   => auth()->user()->id,
            ]);
            return response()->json(['success'=>true, 'message'=>'Updated Successfully'],200);

        } catch (\Exception $e) {

            return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
        }


    }



    public function update_new_polygon(Request $request)
    {
        try {
            // dd($request->all());
            // dd('in');
            // Fetch polygon data from either `Polygon` or `PipeInstallation`
            $pipe_data = Polygon::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
            // if (!$pipe_data) {
            //     $pipe_data = PipeInstallation::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->first();
            // }

            if (!$pipe_data) {
                return response()->json(['error' => true, 'message' => 'Polygon or PipeInstallation not found'], 404);
            }

            // Fetch farmer data
            $farmer_data = FinalFarmer::where('farmer_uniqueId', $pipe_data->farmer_uniqueId)->where('plot_no', 1)->first();
            // dd($farmer_data->area_in_acers);
            // Fetch settings data
            $settings = Setting::select('threshold_pipe_installation')->where('id', 1)->first();
            // dd($settings);
            if (!$farmer_data) {
            return response()->json(['error' => true, 'message' => 'Farmer data not found'], 404);
            }
            $test = ($farmer_data->area_in_acers * $settings->threshold_pipe_installation);
            // dd($test , $farmer_data->area_in_acers);
            // Check if the updated polygon area exceeds the allowed threshold of area_in_acers
            if ($request->updated_poly_area > ($farmer_data->area_in_acers + $test)) {
            return response()->json(['error' => true, 'message' => 'Requested Area is Above area_in_acers'], 400);
            }

            // Store the old polygon data in `old_polygons`
            DB::table('old_polygons')->insert([
                "farmer_uniqueId"      => $pipe_data->farmer_uniqueId,
                "farmer_plot_uniqueid" => $pipe_data->farmer_plot_uniqueid,
                "plot_no"              => $pipe_data->plot_no,
                "polygon"              => $pipe_data->ranges,
                "surveyor_id"          => auth()->user()->id,
                "type"                 => 'OLD POLYGON',
                "google_plot_area"     => $pipe_data->plot_area,
                "created_at"           => Carbon::now(),
                "updated_at"           => Carbon::now(),
            ]);

            // Process the updated polygon data
            $process_update_polygon = json_decode($request->updatedpolygon, true);
            $multipolygon = array_map(function ($array) {
                return ["lat" => $array[0], "lng" => $array[1]];
            }, $process_update_polygon);
            $updated_polygon = json_encode($multipolygon);

            // Update the polygon data in the appropriate table
            if ($pipe_data instanceof Polygon) {
                Polygon::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->update([
                    'ranges'    => $updated_polygon,
                    'plot_area' => number_format($request->updated_poly_area, 2, '.', ''),
                ]);
            }
            // } elseif ($pipe_data instanceof PipeInstallation) {
            //     PipeInstallation::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->update([
            //         'ranges'    => $updated_polygon,
            //         'plot_area' => number_format($request->updated_poly_area, 2, '.', ''),
            //     ]);
            // }

            // Fetch updated polygon data
            $updated_pipe_data =  Polygon::where('farmer_plot_uniqueid', $pipe_data->farmer_plot_uniqueid)->first();

            // Ensure plot_no is dynamic if needed
            $farmer_data = FinalFarmer::where('farmer_uniqueId', $pipe_data->farmer_uniqueId)->where('plot_no', 1)->first();
            // dd($farmer_data);

            if (!$farmer_data) {
                return response()->json(['error' => true, 'message' => 'Farmer data not found'], 404);
            }

            // Calculate available area
            $current_available_area = $farmer_data->available_area;
            $new_polygon_area = $updated_pipe_data->plot_area;
            $old_polygon_area = $pipe_data->plot_area;

            \Log::info('Current Available Area: ' . $current_available_area);
            \Log::info('Old Polygon Area: ' . $old_polygon_area);
            \Log::info('New Polygon Area: ' . $new_polygon_area);

            if (is_null($current_available_area) || is_null($old_polygon_area) || is_null($new_polygon_area)) {
                return response()->json(['error' => true, 'message' => 'Area values are missing or incorrect'], 400);
            }

            $final_available_area = (float) $current_available_area + (float) $old_polygon_area - (float) $new_polygon_area;
            // $final_area_in_acers = (float) $farmer_data->area_in_acers + (float) $old_polygon_area - (float) $new_polygon_area;

            \Log::info('Final Available Area: ' . $final_available_area);
            // \Log::info('Final Area in Acers: ' . $final_area_in_acers);

            \Log::info('Farmer Data Before Save: ' . json_encode($farmer_data->toArray()));
            if ($farmer_data) {
                // Update only the available_area and area_in_acers fields, keeping plot_no unchanged
                $farmer_data->available_area = $final_available_area;
                // $farmer_data->area_in_acers = $final_area_in_acers;
                $farmer_data->save();
            }



            // Update farmer's available area and area in acres
            // $farmer_data->available_area = number_format($final_available_area, 2, '.', '');
            // $farmer_data->area_in_acers = number_format($final_area_in_acers, 2, '.', '');


            // Save the updated data
            // $farmer_data->save();

            \Log::info('Farmer Data After Save: ' . json_encode($farmer_data->toArray()));

            // Log the update in PlotStatusRecord
            PlotStatusRecord::create([
                'farmer_uniqueId'      => $pipe_data->farmer_uniqueId,
                'plot_no'              => $pipe_data->plot_no,
                'farmer_plot_uniqueid' => $pipe_data->farmer_plot_uniqueid,
                'level'                => 'L-2-Validator',
                'status'               => $pipe_data->status,
                'comment'              => 'Polygon Updated From WEB',
                'timestamp'            => Carbon::now(),
                'user_id'              => auth()->user()->id,
                'module'               => 'Update Polygon'
            ]);

            return response()->json(['success' => true, 'message' => 'Updated Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }


    /**
       * Approve or Reject farmer status.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function pipeinstallation_validation(Request $request, $type,$UniqueId){
        // dd("in");
        if($type == "reject"){// for reject
            $pipe_installation_detail = Polygon::where('farmer_plot_uniqueid',$UniqueId)->first();
            // if(!$pipe_installation_detail){
            //     return response()->json(['error'=>true,'message'=>'Already Rejected'],500);
            // }
            
            // $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
            //                                                                                     'l2_status'=> "Rejected",
            //                                                                                     'l2_apprv_reject_user_id'=>auth()->user()->id
            //                                                                                 ]);
            $imgupdate =  PipeInstallationPipeImg::where('farmer_plot_uniqueid',$UniqueId)->where('pipe_no',$request->pipeno)->where('id',$request->pipe_id)->update([
                                                                                                        "l2status" => "Rejected",
                                                                                                        "reason_id" =>   $request->reasons,
                                                                                                            ]);

            //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
            $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
                'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
                'farmer_plot_uniqueid'      => $UniqueId,
                'plot_no'                   => $pipe_installation_detail->plot_no,
                'pipe_no'                   => $request->pipeno,
                'status'                    => 'Rejected',
                'level'                    => 'L-2-Validator',
                'user_id'                   => auth()->user()->id,
                'comment'                   => $request->rejectcomment,
                'reject_reason_id'          => $request->reasons,
                'timestamp'                 => Carbon::now(),
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
            ]);

            //also keep record for validation
            $record =  PlotStatusRecord::create([
                'farmer_uniqueId'           => $UniqueId,
                'plot_no'                   => $pipe_installation_detail->plot_no,
                'farmer_plot_uniqueid'      => $UniqueId,
                'level'                     => 'L-2-Validator',
                'status'                    => 'Rejected',
                'module'                    => 'PipeInstallation-Image-PIPE '.$request->pipeno,
                'comment'                   => "Pipe Image Rejection:. ".$request->rejectcomment,
                'timestamp'                 => Carbon::now(),
                'user_id'                   => auth()->user()->id,
                'reject_reason_id'          => $request->reasons,
            ]);
            // $img_data_reject = PipeInstallationPipeImg::select('l2status')->where('l2status','Rejected')->where('farmer_plot_uniqueid',$UniqueId)
            //                 ->where('l2trash',0)->where('trash',0)->get();
            // if($img_data_reject->count() == $pipe_installation_detail->no_pipe_req){
            //     //if all pipe image is verified then only below code will execute
            //     $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
            //         "l2_status" => "Rejected",
            //         "l2_apprv_reject_user_id" => auth()->user()->id,
            //             ]);

            // }


            if(!$pipe_img_validation){
                return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            }
            return response()->json(['success' =>true, 'pipe_installation'=>$pipe_installation_detail],200);
      }//end if for rejection
        if($type == "approve"){// for approval
            if(!isset($request->pipes) && empty($request->pipes)){
                return response()->json(['error' =>true, 'empty'=>'Please select pipe','message'=>'Something went wrong'],500);
            }
            $pipe_installation = Polygon::where('farmer_plot_uniqueid',$UniqueId)->first();

                    // dd($request->all());
            foreach($request->pipes as $data){
                $img_data = PipeInstallationPipeImg::where('farmer_plot_uniqueid',$UniqueId)->where('pipe_no',$data['pipe_no'])->where('id',$data['pipe_id'])->update([
                    "l2status" => 'Approved',
                    "reason_id" => NULL,
                    ]);

                //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
                $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
                    'farmer_uniqueId'           => $pipe_installation->farmer_uniqueId,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'plot_no'                   => $pipe_installation->plot_no,
                    'pipe_no'                   => $data['pipe_no'],
                    'status'                    => 'Approved',
                    'level'                    => 'L-2-Validator',
                    'user_id'                   => auth()->user()->id,
                    'comment'                   => $data['ApproveComment'],
                    'reject_reason_id'          => NULL,
                    'timestamp'                 => Carbon::now(),
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                ]);
                //also keep record for validation
                $record =  PlotStatusRecord::create([
                    'farmer_uniqueId'           => $pipe_installation->farmer_uniqueId,
                    'plot_no'                   => $pipe_installation->plot_no,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'level'                     => 'L-2-Validator',
                    'status'                    => 'Approved',
                    'module'                    => 'PipeInstallation-Image-PIPE '.$data['pipe_no'],
                    'comment'                   => "Pipe Image Approved:. ".$data['ApproveComment'],
                    'timestamp'                 => Carbon::now(),
                    'user_id'                   => auth()->user()->id,
                    'reject_reason_id'          => $request->reasons,
                ]);

            }
            $img_data_apprv = PipeInstallationPipeImg::select('l2status')->where('l2status','Approved')->where('farmer_plot_uniqueid',$UniqueId)->where('l2trash',0)->get();

            // if($img_data_apprv->count() == $pipe_installation->no_pipe_req){
            //     //if all pipe image is verified then only below code will execute
            //     $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
            //         "l2_status" => "Approved",
            //         "l2_apprv_reject_user_id" => auth()->user()->id,
            //             ]);
            // }

            if(!$pipe_img_validation){
                return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            }
            return response()->json(['success' =>true, 'pipe_installation'=>$pipe_img_validation],200);
        }
    }
    
    //   public function pipeinstallation_validation(Request $request, $type,$UniqueId){
    //     if($type == "reject"){// for reject
    //         $pipe_installation_detail = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->first();
    //         // if(!$pipe_installation_detail){
    //         //     return response()->json(['error'=>true,'message'=>'Already Rejected'],500);
    //         // }
            
    //         // $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
    //         //                                                                                     'l2_status'=> "Rejected",
    //         //                                                                                     'l2_apprv_reject_user_id'=>auth()->user()->id
    //         //                                                                                 ]);
    //         $imgupdate =  PipeInstallationPipeImg::where('farmer_plot_uniqueid',$UniqueId)->where('pipe_no',$request->pipeno)->where('id',$request->pipe_id)->update([
    //                                                                                                     "l2status" => "Rejected",
    //                                                                                                     "reason_id" =>   $request->reasons,
    //                                                                                                         ]);

    //         //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
    //         $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
    //             'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
    //             'farmer_plot_uniqueid'      => $UniqueId,
    //             'plot_no'                   => $pipe_installation_detail->plot_no,
    //             'pipe_no'                   => $request->pipeno,
    //             'status'                    => 'Rejected',
    //             'level'                    => 'L-2-Validator',
    //             'user_id'                   => auth()->user()->id,
    //             'comment'                   => $request->rejectcomment,
    //             'reject_reason_id'          => $request->reasons,
    //             'timestamp'                 => Carbon::now(),
    //             'created_at'                => Carbon::now(),
    //             'updated_at'                => Carbon::now(),
    //         ]);

    //         //also keep record for validation
    //         $record =  PlotStatusRecord::create([
    //             'farmer_uniqueId'           => $UniqueId,
    //             'plot_no'                   => $pipe_installation_detail->plot_no,
    //             'farmer_plot_uniqueid'      => $UniqueId,
    //             'level'                     => 'L-2-Validator',
    //             'status'                    => 'Rejected',
    //             'module'                    => 'PipeInstallation-Image-PIPE '.$request->pipeno,
    //             'comment'                   => "Pipe Image Rejection:. ".$request->rejectcomment,
    //             'timestamp'                 => Carbon::now(),
    //             'user_id'                   => auth()->user()->id,
    //             'reject_reason_id'          => $request->reasons,
    //         ]);
    //         // $img_data_reject = PipeInstallationPipeImg::select('l2status')->where('l2status','Rejected')->where('farmer_plot_uniqueid',$UniqueId)
    //         //                 ->where('l2trash',0)->where('trash',0)->get();
    //         // if($img_data_reject->count() == $pipe_installation_detail->no_pipe_req){
    //         //     //if all pipe image is verified then only below code will execute
    //         //     $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
    //         //         "l2_status" => "Rejected",
    //         //         "l2_apprv_reject_user_id" => auth()->user()->id,
    //         //             ]);

    //         // }


    //         if(!$pipe_img_validation){
    //             return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
    //         }
    //         return response()->json(['success' =>true, 'pipe_installation'=>$pipe_installation_detail],200);
    //   }//end if for rejection
    //     if($type == "approve"){// for approval
    //         if(!isset($request->pipes) && empty($request->pipes)){
    //             return response()->json(['error' =>true, 'empty'=>'Please select pipe','message'=>'Something went wrong'],500);
    //         }
    //         $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->first();

    //                 // dd($request->all());
    //         foreach($request->pipes as $data){
    //             $img_data = PipeInstallationPipeImg::where('farmer_plot_uniqueid',$UniqueId)->where('pipe_no',$data['pipe_no'])->where('id',$data['pipe_id'])->update([
    //                 "l2status" => 'Approved',
    //                 "reason_id" => NULL,
    //                 ]);

    //             //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
    //             $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
    //                 'farmer_uniqueId'           => $pipe_installation->farmer_uniqueId,
    //                 'farmer_plot_uniqueid'      => $UniqueId,
    //                 'plot_no'                   => $pipe_installation->plot_no,
    //                 'pipe_no'                   => $data['pipe_no'],
    //                 'status'                    => 'Approved',
    //                 'level'                    => 'L-2-Validator',
    //                 'user_id'                   => auth()->user()->id,
    //                 'comment'                   => $data['ApproveComment'],
    //                 'reject_reason_id'          => NULL,
    //                 'timestamp'                 => Carbon::now(),
    //                 'created_at'                => Carbon::now(),
    //                 'updated_at'                => Carbon::now(),
    //             ]);
    //             //also keep record for validation
    //             $record =  PlotStatusRecord::create([
    //                 'farmer_uniqueId'           => $pipe_installation->farmer_uniqueId,
    //                 'plot_no'                   => $pipe_installation->plot_no,
    //                 'farmer_plot_uniqueid'      => $UniqueId,
    //                 'level'                     => 'L-2-Validator',
    //                 'status'                    => 'Approved',
    //                 'module'                    => 'PipeInstallation-Image-PIPE '.$data['pipe_no'],
    //                 'comment'                   => "Pipe Image Approved:. ".$data['ApproveComment'],
    //                 'timestamp'                 => Carbon::now(),
    //                 'user_id'                   => auth()->user()->id,
    //                 'reject_reason_id'          => $request->reasons,
    //             ]);

    //         }
    //         $img_data_apprv = PipeInstallationPipeImg::select('l2status')->where('l2status','Approved')->where('farmer_plot_uniqueid',$UniqueId)->where('l2trash',0)->get();

    //         if($img_data_apprv->count() == $pipe_installation->no_pipe_req){
    //             //if all pipe image is verified then only below code will execute
    //             $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
    //                 "l2_status" => "Approved",
    //                 "l2_apprv_reject_user_id" => auth()->user()->id,
    //                     ]);
    //         }

    //         if(!$pipe_img_validation){
    //             return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
    //         }
    //         return response()->json(['success' =>true, 'pipe_installation'=>$pipe_img_validation],200);
    //     }
    // }

    /**
       * Approve or Reject farmer status.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */

       public function polygon_validation(Request $request, $type,$UniqueId){
        if($type == "reject"){// for reject
            $pipe_installation_detail = Polygon::where('farmer_plot_uniqueid',$UniqueId)->where('final_status','Pending')->first();
            if(!$pipe_installation_detail){
                return response()->json(['error'=>true,'message'=>'Already Rejected'],500);
            }

            if($request->reasons == '11'){//for reject reason where
                // here we willdelete polygon and update as 1 in delete_polygon columns
                $pipe_installation = Polygon::where('farmer_plot_uniqueid',$UniqueId)->update([
                    'final_status'=>'Rejected',
                    // 'l2_apprv_reject_user_id'=>auth()->user()->id,
                    'delete_polygon'    => '1',
                    'ranges'=> NULL,
                    'deleted_at'=> now(),
                    // 'reason_id'=>$request->reasons,
                ]);
            }else{
                // here we will only reject a polygon from pipeinstallation table
                $pipe_installation = Polygon::where('farmer_plot_uniqueid',$UniqueId)->update([
                    'final_status'=>'Rejected',
                    // 'l2_apprv_reject_user_id'=>auth()->user()->id,
                    // 'reason_id'=>$request->reasons,
                ]);
            }

            //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
            $pipe_img_validation  = DB::table('polygon_validations')->insert([
                'polygon_id'                => $pipe_installation_detail->id,
                'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
                'farmer_plot_uniqueid'      => $UniqueId,
                'plot_no'                   => $pipe_installation_detail->plot_no,
                'status'                    => 'Rejected',
                'level'                    => 'L-2-Validator',
                'user_id'                   => auth()->user()->id,
                'surveyor_id'               => $pipe_installation_detail->surveyor_id,
                'comment'                   => $request->rejectcomment,
                'reject_reason_id'          => $request->reasons,
                'rejected_date_time'                 => Carbon::now(),
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
            ]);

            //also keep record for validation
            $record =  PlotStatusRecord::create([
                'farmer_uniqueId'           => $UniqueId,
                'plot_no'                   => $pipe_installation_detail->plot_no,
                'farmer_plot_uniqueid'      => $UniqueId,
                'level'                     => 'L-2-Validator',
                'status'                    => 'Rejected',
                'module'                    => 'Polygon',
                'comment'                   => "Polygon Rejection:. ".$request->rejectcomment,
                'timestamp'                 => Carbon::now(),
                'user_id'                   => auth()->user()->id,
                'reject_reason_id'          => $request->reasons,
            ]);

            if(!$pipe_installation_detail){
                return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            }
            return response()->json(['success' =>true, 'pipe_installation'=>$pipe_installation_detail],200);

      }//end if for rejection
        if($type == "approve"){// for approval
            if(!isset($request->pipes) && empty($request->pipes)){
                return response()->json(['error' =>true, 'empty'=>'Please select pipe','message'=>'Something went wrong'],500);
            }

            $pipe_installation_detail = Polygon::where('farmer_plot_uniqueid',$UniqueId)->where('final_status','Pending')->first();
            if(!$pipe_installation_detail){
                return response()->json(['error'=>true,'message'=>'Already Approved'],500);
            }
            foreach($request->pipes as $data){

                $pipe_installation = Polygon::where('farmer_plot_uniqueid',$UniqueId)->update([
                                                                "final_status" => 'Approved',
                                                                // 'l2_apprv_reject_user_id'   => auth()->user()->id,
                                                                // "reason_id" => NULL,
                                                                ]);

                //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
                $pipe_img_validation  = DB::table('polygon_validations')->insert([
                    'polygon_id'                => $pipe_installation_detail->id,
                    'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'plot_no'                   => $pipe_installation_detail->plot_no,
                    'status'                    => 'Approved',
                    'level'                    => 'L-2-Validator',
                    'user_id'                   => auth()->user()->id,
                    'surveyor_id'               => $pipe_installation_detail->surveyor_id,
                    'comment'                   => $data['ApproveComment'],
                    'reject_reason_id'          => NULL,
                    'rejected_date_time'        => Carbon::now(),
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                ]);
                //also keep record for validation
                $record =  PlotStatusRecord::create([
                    'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
                    'plot_no'                   => $pipe_installation_detail->plot_no,
                    'farmer_plot_uniqueid'      => $UniqueId,
                    'level'                     => 'L-2-Validator',
                    'status'                    => 'Approved',
                    'module'                    => 'PipeInstallation-Image-PIPE '.$data['pipe_no'],
                    'comment'                   => "Pipe Image Approved:. ".$data['ApproveComment'],
                    'timestamp'                 => Carbon::now(),
                    'user_id'                   => auth()->user()->id,
                    'reject_reason_id'          => $request->reasons,
                ]);


            }
            if(!$pipe_installation){
                return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            }
            return response()->json(['success' =>true,'message'=>'Approved Successfully' , 'pipe_installation'=>$pipe_installation],200);
        }
    }


    //OLD method
    //    public function polygon_validation(Request $request, $type,$UniqueId){
    //     if($type == "reject"){// for reject
    //         $pipe_installation_detail = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->where('l2_status','Pending')->first();
    //         if(!$pipe_installation_detail){
    //             return response()->json(['error'=>true,'message'=>'Already Rejected'],500);
    //         }

    //         if($request->reasons == '11'){//for reject reason where
    //             // here we willdelete polygon and update as 1 in delete_polygon columns
    //             $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
    //                 'l2_status'=>'Rejected',
    //                 'l2_apprv_reject_user_id'=>auth()->user()->id,
    //                 'delete_polygon'    => '1',
    //                 'ranges'=> NULL,
    //                 'deleted_at'=> now(),
    //                 'reason_id'=>$request->reasons,
    //             ]);
    //         }else{
    //             // here we will only reject a polygon from pipeinstallation table
    //             $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
    //                 'l2_status'=>'Rejected',
    //                 'l2_apprv_reject_user_id'=>auth()->user()->id,
    //                 'reason_id'=>$request->reasons,
    //             ]);
    //         }

    //         //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
    //         $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
    //             'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
    //             'farmer_plot_uniqueid'      => $UniqueId,
    //             'plot_no'                   => $pipe_installation_detail->plot_no,
    //             'status'                    => 'Rejected',
    //             'level'                    => 'L-2-Validator',
    //             'user_id'                   => auth()->user()->id,
    //             'comment'                   => $request->rejectcomment,
    //             'reject_reason_id'          => $request->reasons,
    //             'timestamp'                 => Carbon::now(),
    //             'created_at'                => Carbon::now(),
    //             'updated_at'                => Carbon::now(),
    //         ]);

    //         //also keep record for validation
    //         $record =  PlotStatusRecord::create([
    //             'farmer_uniqueId'           => $UniqueId,
    //             'plot_no'                   => $pipe_installation_detail->plot_no,
    //             'farmer_plot_uniqueid'      => $UniqueId,
    //             'level'                     => 'L-2-Validator',
    //             'status'                    => 'Rejected',
    //             'module'                    => 'Polygon',
    //             'comment'                   => "Polygon Rejection:. ".$request->rejectcomment,
    //             'timestamp'                 => Carbon::now(),
    //             'user_id'                   => auth()->user()->id,
    //             'reject_reason_id'          => $request->reasons,
    //         ]);

    //         if(!$pipe_installation_detail){
    //             return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
    //         }
    //         return response()->json(['success' =>true, 'pipe_installation'=>$pipe_installation_detail],200);
    //   }//end if for rejection
    //     if($type == "approve"){// for approval
    //         if(!isset($request->pipes) && empty($request->pipes)){
    //             return response()->json(['error' =>true, 'empty'=>'Please select pipe','message'=>'Something went wrong'],500);
    //         }

    //         $pipe_installation_detail = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->where('l2_status','Pending')->first();
    //         if(!$pipe_installation_detail){
    //             return response()->json(['error'=>true,'message'=>'Already Approved'],500);
    //         }
    //         foreach($request->pipes as $data){

    //             $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$UniqueId)->update([
    //                                                             "l2_status" => 'Approved',
    //                                                             'l2_apprv_reject_user_id'   => auth()->user()->id,
    //                                                             "reason_id" => NULL,
    //                                                             ]);

    //             //to store record of pipe vlidation of approval and rejection. This is specifically for pipe
    //             $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
    //                 'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
    //                 'farmer_plot_uniqueid'      => $UniqueId,
    //                 'plot_no'                   => $pipe_installation_detail->plot_no,
    //                 'status'                    => 'Approved',
    //                 'level'                    => 'L-2-Validator',
    //                 'user_id'                   => auth()->user()->id,
    //                 'comment'                   => $data['ApproveComment'],
    //                 'reject_reason_id'          => NULL,
    //                 'timestamp'                 => Carbon::now(),
    //                 'created_at'                => Carbon::now(),
    //                 'updated_at'                => Carbon::now(),
    //             ]);
    //             //also keep record for validation
    //             $record =  PlotStatusRecord::create([
    //                 'farmer_uniqueId'           => $pipe_installation_detail->farmer_uniqueId,
    //                 'plot_no'                   => $pipe_installation_detail->plot_no,
    //                 'farmer_plot_uniqueid'      => $UniqueId,
    //                 'level'                     => 'L-2-Validator',
    //                 'status'                    => 'Approved',
    //                 'module'                    => 'PipeInstallation-Image-PIPE '.$data['pipe_no'],
    //                 'comment'                   => "Pipe Image Approved:. ".$data['ApproveComment'],
    //                 'timestamp'                 => Carbon::now(),
    //                 'user_id'                   => auth()->user()->id,
    //                 'reject_reason_id'          => $request->reasons,
    //             ]);


    //         }
    //         if(!$pipe_installation){
    //             return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
    //         }
    //         return response()->json(['success' =>true,'message'=>'Approved Successfully' , 'pipe_installation'=>$pipe_installation],200);
    //     }
    // }


    /**
   * level 2 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function polygon_pending_lists()
  {
    // dd('cdsv');
    // $a = PipeInstallation::where('farmer_plot_uniqueid','122465P2')->with('pipe_image')->first();
    // dd($a);

    //level 2 validator get pending plot list of pipe function
	  //Plot list
	//   if(request()->ajax()){
  		$plots_paginate = Polygon::with('farmerapproved')->where('final_status','Pending')->whereHas('farmerapproved',function($q){
            if(auth()->user()->hasRole('L-2-Validator')){
                $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                // dd($VendorLocation);
                    $q->whereIn('state_id',explode(',',$VendorLocation->state));
                if(!empty($VendorLocation->district)){
                    $q->whereIn('district_id',explode(',',$VendorLocation->district));
                }
                // return $q;
            } 
            if(request()->has('state') && !empty(request('state'))){
                $q->where('state_id','like',request('state'));
            }
            if(request()->has('district') && !empty(request('district'))){
                $q->where('district_id','like',request('district'));
            }
            if(request()->has('taluka') && !empty(request('taluka'))){
                $q->where('taluka_id','like',request('taluka'));
            }
            if(request()->has('panchayats') && !empty(request('panchayats'))){
                $q->where('panchayat_id','like',request('panchayats'));
            }
            if(request()->has('village') && !empty(request('village'))){
                $q->where('village_id','like',request('village'));
            }
            if(request()->has('farmer_status') && !empty(request('farmer_status'))){
                $q->where('final_status_onboarding','like',request('farmer_status'));
            }
            return $q;
  		})
        ->when('filter',function($w) {
            if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
                $w->where('surveyor_id',request('executive_onboarding'));
            }
            if(request()->has('start_date') && !empty(request('start_date'))){
                $w->whereDate('created_at','>=',request('start_date'));
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $w->whereDate('created_at','<=',request('end_date'));
            }
            return $w;
        })
        ->orderBy('id','desc')->paginate(100);
        $path = $plots_paginate->links()->elements;
                        $links=[];
                        foreach ($path as $key=>$item) {
                          if (is_array($item)) {
                              $links = array_merge($links, $item);
                          }
                        }
        //end of datatable
  		// return datatables()->of($plots)->make(true);
	//   }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Polygon | Pending list';
  		$page_description = 'Polygon | Pending list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
          $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.pipe.polygon-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function polygon_pending_detail($plotuniqueid){
    // dd("in");
    $check_pipedata = DB::table('polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
       
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = Polygon::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    // $PipesLocation="";
    // if($PipeInstallation->pipes_location){
    //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
    // }

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('l2trash',0)
                        ->where('farmer_plot_uniqueid',$plotuniqueid)->where('l2status','Pending')->get();

   

    $validation_list = PolygonValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =
    $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();

    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    // dd();
    $reject_module = RejectModule::where('type','PipeInstallation')->whereIn('id',[10,11])->get();//here specifically call polygon reasons
    $page_title = 'Polygon | Pending Detail';
    $page_description = 'Polygon | Pending Detail';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
          $percent_error = "0.0";
        if($PipeInstallation->area_in_acers){
            $mod =  abs($PipeInstallation->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
            $denominator = $PipeInstallation->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
            //below percentage error between onboarding area and updated area
            $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
        }
    return view('l2validator.pipe.polygon-plot-pipe',compact('plot','PipeInstallation','check_pipedata','PipesLocation','cropdata','Polygon','page_title','page_description','action','farmerplots','farmerplot',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }



    /**
   * level 2 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */

   public function pipe_reject_lists()

     {

        // dd('c');
        $farmer_uniqueId = request('farmer_uniqueId')??'';
           $plots_paginate= PipeInstallationPipeImg::with('farmerapproved','reject_validation_detail')
           // ->groupBy('farmer_plot_uniqueid')
           ->where('l2status','Rejected')
           ->where('l2trash',0  )
        //    ->whereHas('reject_validation_detail', function ($p) {
        //        $p->where('user_id', auth()->user()->id);
        //        $p->havingRaw('MAX(created_at) = created_at');
        //        $p->where('status','Rejected');
        //        // return $p;
        //    })
           ->whereHas('farmerapproved',function($q){
   
           if(request()->has('state') && !empty(request('state'))){
   
               $q->where('state_id','like',request('state'));
   
           }
   
           if(request()->has('district') && !empty(request('district'))){
   
                $q->where('district_id','like',request('district'));
   
           }
   
           if(request()->has('taluka') && !empty(request('taluka'))){
   
                $q->where('taluka_id','like',request('taluka'));
   
           }
   
           if(request()->has('panchayats') && !empty(request('panchayats'))){
   
                $q->where('panchayat_id','like',request('panchayats'));
   
           }
   
           if(request()->has('village') && !empty(request('village'))){
   
                $q->where('village_id','like',request('village'));
   
           }
   
           if(request()->has('farmer_status') && !empty(request('farmer_status'))){
   
                $q->where('final_status_onboarding','like',request('farmer_status'));
   
           }
   
           if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
   
                $q->where('surveyor_id',request('executive_onboarding'));
   
           }
           if(request()->has('farmer_uniqueId') && !empty(request('farmer_uniqueId'))){
             $q->where('farmer_uniqueId',request('farmer_uniqueId'));
           }
           if(auth()->user()->hasRole('L-2-Validator')){
   
               $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
   
               $q->whereIn('state_id',explode(',',$VendorLocation->state));
   
               if(!empty($VendorLocation->district)){
   
               $q->whereIn('district_id',explode(',',$VendorLocation->district));
   
               }
   
               // return $q;
   
           } 
   
           // return $q;
   
             })
   
           ->when('filter',function($w) {
   
               // dd(request('start_date'));
   
               if(request()->has('start_date') && !empty(request('start_date'))){
   
                   $w->whereDate('created_at','>=',request('start_date'));
   
               }
   
               if(request()->has('end_date') && !empty(request('end_date'))){
   
                   $w->whereDate('created_at','<=',request('end_date'));
   
               }
   
               // return $w;
   
           })

           ->orderBy('id','desc')->paginate(25);
   
   
   
           // dd($plots_paginate);
   
   
   
           $path = $plots_paginate->links()->elements;
   
           $links=[];
   
           foreach ($path as $key=>$item) {
   
             if (is_array($item)) {
   
                 $links = array_merge($links, $item);
   
             }
   
           }
   
             // return datatables()->of($plots)->make(true);
   
       //   }//end layoutout plot WITH AJAX
   
       // Onload below code excute first. And after successful load then again ajax make request to above code
   
             $page_title = 'PipeInstallation | Reject list';
   
             $page_description = 'PipeInstallation | Reject list';
   
             $action = 'table_farmer';
   
             //below process is for first time landing on page}
   
         //for admin data
   
   
   
         $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
   
             $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
   
             $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
   
             $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
   
             $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
   
             $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
   
   
   
             $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();
   
   
   
             $seasons = DB::table('seasons')->get();
   
         $status = request()->status;
   
         $others = "0";
   
           return view('l2validator.pipe.reject-plot',compact('farmer_uniqueId','links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
   
     }


  //OLD method
//   public function pipe_reject_lists()
//   {
//     //level 2 validator get pending plot list of pipe function
// 	  //Plot list
// 	//   if(request()->ajax()){
//   		$plots_paginate= PipeInstallation::with('farmerapproved')->where('l2_status','Rejected')->where('l2_apprv_reject_user_id',auth()->user()->id)->whereHas('farmerapproved',function($q){
//             if(auth()->user()->hasRole('L-2-Validator')){
//                 $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
//                 $q->whereIn('state_id',explode(',',$VendorLocation->state));
//                 if(!empty($VendorLocation->district)){
//                 $q->whereIn('district_id',explode(',',$VendorLocation->district));
//                 }
//                 return $q;
//             } 

//         if(request()->has('state') && !empty(request('state'))){
//             $q->where('state_id','like',request('state'));
//         }
//         if(request()->has('district') && !empty(request('district'))){
//              $q->where('district_id','like',request('district'));
//         }
//         if(request()->has('taluka') && !empty(request('taluka'))){
//              $q->where('taluka_id','like',request('taluka'));
//         }
//         if(request()->has('panchayats') && !empty(request('panchayats'))){
//              $q->where('panchayat_id','like',request('panchayats'));
//         }
//         if(request()->has('village') && !empty(request('village'))){
//              $q->where('village_id','like',request('village'));
//         }
//         if(request()->has('farmer_status') && !empty(request('farmer_status'))){
//              $q->where('final_status_onboarding','like',request('farmer_status'));
//         }
//         if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
//              $q->where('surveyor_id',request('executive_onboarding'));
//         }
//         return $q;
//   		})
//         ->when('filter',function($w) {
//             // dd(request('start_date'));
//             if(request()->has('start_date') && !empty(request('start_date'))){
//                 $w->whereDate('created_at','>=',request('start_date'));
//             }
//             if(request()->has('end_date') && !empty(request('end_date'))){
//                 $w->whereDate('created_at','<=',request('end_date'));
//             }
//             return $w;
//         })
//         ->orderBy('id','desc')->paginate(100);
//         $path = $plots_paginate->links()->elements;
//         $links=[];
//         foreach ($path as $key=>$item) {
//           if (is_array($item)) {
//               $links = array_merge($links, $item);
//           }
//         }
//   		// return datatables()->of($plots)->make(true);
// 	//   }//end layoutout plot WITH AJAX
//     // Onload below code excute first. And after successful load then again ajax make request to above code
//   		$page_title = 'PipeInstallation | Reject list';
//   		$page_description = 'PipeInstallation | Reject list';
//   		$action = 'table_farmer';
//   		//below process is for first time landing on page}
//       //for admin data

//       $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
//           $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
//           $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
//           $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
//           $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
//           $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();

//           $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();

//   		$seasons = DB::table('seasons')->get();
//       $status = request()->status;
//       $others = "0";
//   	  return view('l2validator.pipe.reject-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
//   }

  public function pipe_reject_detail($plotuniqueid){
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = Polygon::where('farmer_plot_uniqueid', $plotuniqueid)->first();

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('l2trash',0)->where('farmer_plot_uniqueid',$plotuniqueid)->where('status','Approved')->get();



    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);


    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->get();
    $page_title = 'PipeInstallation | Reject Detail';
    $page_description = 'PipeInstallation | Reject Detail';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
        $mod =  abs($plot->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
        $denominator = $plot->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
        //below percentage error between onboarding area and updated area
        $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
    return view('l2validator.pipe.reject-pipe-detail',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }

  /**
   * level 2 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
//   public function pipe_approved_lists()
//   {
//     //level 2 validator get pending plot list of pipe function
// 	  //Plot list
// 	//   if(request()->ajax()){
//   		$plots_paginate= PipeInstallation::with('farmerapproved')->where('l2_status','Approved')->where('l2_apprv_reject_user_id',auth()->user()->id)->whereHas('farmerapproved',function($q){
//         if(request()->has('state') && !empty(request('state'))){
//             $q->where('state_id','like',request('state'));
//         }
//         if(request()->has('district') && !empty(request('district'))){
//              $q->where('district_id','like',request('district'));
//         }
//         if(request()->has('taluka') && !empty(request('taluka'))){
//              $q->where('taluka_id','like',request('taluka'));
//         }
//         if(request()->has('panchayats') && !empty(request('panchayats'))){
//              $q->where('panchayat_id','like',request('panchayats'));
//         }
//         if(request()->has('village') && !empty(request('village'))){
//              $q->where('village_id','like',request('village'));
//         }
//         if(request()->has('farmer_status') && !empty(request('farmer_status'))){
//              $q->where('final_status_onboarding','like',request('farmer_status'));
//         }
//         if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
//              $q->where('surveyor_id',request('executive_onboarding'));
//         }
//         return $q;
//   		})
//         ->when('filter',function($w) {
//             // dd(request('start_date'));
//             if(request()->has('start_date') && !empty(request('start_date'))){
//                 $w->whereDate('created_at','>=',request('start_date'));
//             }
//             if(request()->has('end_date') && !empty(request('end_date'))){
//                 $w->whereDate('created_at','<=',request('end_date'));
//             }
//             return $w;
//         })
//         ->orderBy('id','desc')->paginate(100);
//         $path = $plots_paginate->links()->elements;
//         $links=[];
//         foreach ($path as $key=>$item) {
//           if (is_array($item)) {
//               $links = array_merge($links, $item);
//           }
//         }
//   		// return datatables()->of($plots)->make(true);
// 	//   }//end layoutout plot WITH AJAX
//     // Onload below code excute first. And after successful load then again ajax make request to above code
//   		$page_title = 'PipeInstallation | Approved list';
//   		$page_description = 'PipeInstallation | Approved list';
//   		$action = 'table_farmer';
//   		//below process is for first time landing on page}
//       //for admin data

//       $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
//       $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
//           $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
//           $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
//           $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
//           $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();

//       $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();

//   		$seasons = DB::table('seasons')->get();
//       $status = request()->status;
//       $others = "0";
//   	  return view('l2validator.pipe.approved-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive',
//       'status','others'));
//   }

//New method
public function pipe_approved_lists()
  {
    // dd("in");
    //level 2 validator get pending plot list of pipe function
	  //Plot list
	//   if(request()->ajax()){

        // dd(auth()->user()->id);
        $farmer_uniqueId = request('farmer_uniqueId')??'';
  		$plots_paginate= PipeInstallationPipeImg::with('farmerapproved','approve_validation_detail')
        ->where('l2status','Approved')
        // ->paginate(10);
        // dd($plots_paginate);

        ->whereHas('approve_validation_detail', function ($p) {
            $p->where('user_id', auth()->user()->id);
            // $p->havingRaw('MAX(created_at) = created_at');
            $p->where('status','Approved');
            // return $p;
        })
        ->whereHas('farmerapproved',function($q){

        if(request()->has('state') && !empty(request('state'))){
            $q->where('state_id','like',request('state'));
        }
        if(request()->has('district') && !empty(request('district'))){
             $q->where('district_id','like',request('district'));
        }
        if(request()->has('taluka') && !empty(request('taluka'))){
             $q->where('taluka_id','like',request('taluka'));
        }
        if(request()->has('panchayats') && !empty(request('panchayats'))){
             $q->where('panchayat_id','like',request('panchayats'));
        }
        if(request()->has('village') && !empty(request('village'))){
             $q->where('village_id','like',request('village'));
        }
        if(request()->has('farmer_status') && !empty(request('farmer_status'))){
             $q->where('final_status_onboarding','like',request('farmer_status'));
        }
        if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
             $q->where('surveyor_id',request('executive_onboarding'));
        }
        if(request()->has('farmer_uniqueId') && !empty(request('farmer_uniqueId'))){
             $q->where('farmer_uniqueId',request('farmer_uniqueId'));
        }
        if(auth()->user()->hasRole('L-2-Validator')){
            $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
            $q->whereIn('state_id',explode(',',$VendorLocation->state));
            if(!empty($VendorLocation->district)){
            $q->whereIn('district_id',explode(',',$VendorLocation->district));
            }
            // return $q;
        } 

        // return $q;
  		})
        ->when('filter',function($w) {
            // dd(request('start_date'));
            if(request()->has('start_date') && !empty(request('start_date'))){
                $w->whereDate('created_at','>=',request('start_date'));
            }
            if(request()->has('end_date') && !empty(request('end_date'))){
                $w->whereDate('created_at','<=',request('end_date'));
            }
            // return $w;
        })
        ->orderBy('id','desc')->paginate(25);

        // dd($plots_paginate);    

        $path = $plots_paginate->links()->elements;
        $links=[];
        foreach ($path as $key=>$item) {
          if (is_array($item)) {
              $links = array_merge($links, $item);
          }
        }
  		// return datatables()->of($plots)->make(true);
	//   }//end layoutout plot WITH AJAX
    // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'PipeInstallation | Approved list';
  		$page_description = 'PipeInstallation | Approved list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
      //for admin data

      $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
      $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();

      $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();

  		$seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.pipe.approved-plot',compact('farmer_uniqueId','links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive',
      'status','others'));
  }

  public function pipe_approved_detail($plotuniqueid){
    // dd($plotuniqueid);
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    // dd($plot);
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = Polygon::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();
    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

    // $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('l2trash',0)->where('farmer_plot_uniqueid',$plotuniqueid)->get();
    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);


    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->get();
    $page_title = 'PipeInstallation | Approved Detail';
    $page_description = 'PipeInstallation | Approved Detail';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
        $mod =  abs($plot->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
        $denominator = $plot->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
        //below percentage error between onboarding area and updated area
        $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
    return view('l2validator.pipe.approved-pipe-detail',compact('plot','PipeInstallation','PipesLocation','cropdata','Polygon','page_title','page_description','action','farmerplots','farmerplot',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }

  /**
     * Download excel file.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadFile(){
        set_time_limit(-1);
        ini_set('memory_limit', '640M');
        if(request('file')){
         $name = 'L-2-'.request('status').'_';

         if(request('type') == 'PipeInstalltion'){
             $filename = 'Farmers-pipe-installation_'.Carbon::now().'.xlsx';
            //  return Excel::download(new L2PipeInstallationExport('All',request()), $filename);

            $payload=[
                     'uuid'=>\Str::uuid(),
                     'data'=>[
                         'command'=>'\App\Exports\L2PipeInstallationExport',
                         'parameters'=>['All' ,request()->all()],
                         'user_id'  => auth()->user()->id,
                         'filename'=>$filename,
                         'drive'=>'excel'
                     ]
                 ];
            $job=\DB::table('temp_jobs')->insert([
                    'queue'=>'excel',
                    'user_id'  => auth()->user()->id,
                    'payload'=>json_encode($payload),
                    'available_at'=>\Carbon\Carbon::now()->timestamp,
                    'created_at'=>\Carbon\Carbon::now()->timestamp
            ]);
             if(!$job){
                 return response()->json([
                         'error'=>true,
                         'message'=>'Unknown Error!'
                     ]);
             }
             return response()->json([
                 'success'=>true,
                 'message'=>'Export request submitted. Please check download section'
             ]);
         }
        }// end of request('file')
      }

      /**
       * Download individual excel file
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function excel_download($type, $unique_id, $plot_no, $status){
        if($status == 'Approved'){

            $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
            $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
            return Excel::download(new PipeInstallationIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);


        }elseif($status == 'Pending'){
            $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
            $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
            return Excel::download(new PipeInstallationIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
        }elseif($status == 'Rejected'){
            $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
            $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
            return Excel::download(new L2RejectedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
        }
     }

       /**
   * level 2 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function polygon_approved_lists()
  {
    // dd('ic');
        //level 2 validator get pending plot list of pipe function
	  //Plot list
	//   if(request()->ajax()){
  		$plots_paginate = Polygon::with('farmerapproved','plot_detail')->where('final_status','Approved')->whereHas('farmerapproved',function($q){
            if(auth()->user()->hasRole('L-2-Validator')){
                $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                $q->whereIn('state_id',explode(',',$VendorLocation->state));
                if(!empty($VendorLocation->district)){
                $q->whereIn('district_id',explode(',',$VendorLocation->district));
                }
                return $q;
            } 
            if(request()->has('state') && !empty(request('state'))){
                $q->where('state_id','like',request('state'));
            }
            if(request()->has('district') && !empty(request('district'))){
                $q->where('district_id','like',request('district'));
            }
            if(request()->has('taluka') && !empty(request('taluka'))){
                $q->where('taluka_id','like',request('taluka'));
            }
            if(request()->has('panchayats') && !empty(request('panchayats'))){
                $q->where('panchayat_id','like',request('panchayats'));
            }
            if(request()->has('village') && !empty(request('village'))){
                $q->where('village_id','like',request('village'));
            }
            if(request()->has('farmer_status') && !empty(request('farmer_status'))){
                $q->where('final_status_onboarding','like',request('farmer_status'));
            }
            return $q;
  		})
        ->orderBy('id','desc')->paginate(100);
        $path = $plots_paginate->links()->elements;
                        $links=[];
                        foreach ($path as $key=>$item) {
                          if (is_array($item)) {
                              $links = array_merge($links, $item);
                          }
                        }
        //end of datatable
  		// return datatables()->of($plots)->make(true);
	//   }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'Polygon | Pending list';
  		$page_description = 'Polygon | Pending list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
          $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.pipe.polgon-approved-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function polygon_approved_detail($plotuniqueid){
    $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = Polygon::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    // $PipesLocation="";
    // if($PipeInstallation->pipes_location){
    //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
    // }

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('l2trash',0)
                        ->where('farmer_plot_uniqueid',$plotuniqueid)->where('l2status','Pending')->get();


    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =
    $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();

    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->where('id',10)->get();//here specifically call polygon reasons
    $page_title = 'PipeInstallation | Pending Detail';
    $page_description = 'PipeInstallation | Pending Detail';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
          $percent_error = "0.0";
        if($PipeInstallation->area_in_acers){
            $mod =  abs($PipeInstallation->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
            $denominator = $PipeInstallation->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
            //below percentage error between onboarding area and updated area
            $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
        }
    return view('l2validator.pipe.polygon-approved-detail',compact('plot','PipeInstallation','check_pipedata','PipesLocation','cropdata','Polygon','page_title','page_description','action','farmerplots','farmerplot',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }


    /**
   * level 2 validator. validates pipe data.
   *
   * @return \Illuminate\Http\Response
   */
  public function polygon_reject_lists()
  {
    // dd('cdhv');
    // $a = PipeInstallation::where('farmer_plot_uniqueid','122465P2')->with('pipe_image')->first();
    // dd($a);

    //level 2 validator get pending plot list of pipe function
	  //Plot list
	//   if(request()->ajax()){
  		$plots_paginate = Polygon::with('farmerapproved')->where('final_status','Rejected')->whereHas('farmerapproved',function($q){
            if(auth()->user()->hasRole('L-2-Validator')){
                $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                $q->whereIn('state_id',explode(',',$VendorLocation->state));
                if(!empty($VendorLocation->district)){
                $q->whereIn('district_id',explode(',',$VendorLocation->district));
                }
                return $q;
            } 
        if(request()->has('state') && !empty(request('state'))){
            $q->where('state_id','like',request('state'));
        }
        if(request()->has('district') && !empty(request('district'))){
             $q->where('district_id','like',request('district'));
        }
        if(request()->has('taluka') && !empty(request('taluka'))){
             $q->where('taluka_id','like',request('taluka'));
        }
        if(request()->has('panchayats') && !empty(request('panchayats'))){
             $q->where('panchayat_id','like',request('panchayats'));
        }
        if(request()->has('village') && !empty(request('village'))){
             $q->where('village_id','like',request('village'));
        }
        if(request()->has('farmer_status') && !empty(request('farmer_status'))){
             $q->where('final_status_onboarding','like',request('farmer_status'));
        }
        return $q;
  		})
        ->orderBy('id','desc')->paginate(100);
        $path = $plots_paginate->links()->elements;
        $links=[];
        foreach ($path as $key=>$item) {
          if (is_array($item)) {
              $links = array_merge($links, $item);
          }
        }
        //end of datatable
  		// return datatables()->of($plots)->make(true);
	//   }//end layoutout plot WITH AJAX
       // Onload below code excute first. And after successful load then again ajax make request to above code
  		$page_title = 'PipeInstallation | Pending list';
  		$page_description = 'PipeInstallation | Pending list';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
        //for admin data
        $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
          $onboarding_executive  = DB::table('pipe_installations')->groupBy('pipe_installations.surveyor_name')->get();

  	  $seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.pipe.polygon-reject-plot',compact('links','plots_paginate','page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  public function polygon_reject_detail($plotuniqueid){
    // dd('ics ');
    $check_pipedata = DB::table('polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::with('ApprvFarmerPlot')->where('farmer_plot_uniqueid',$plotuniqueid)->first();
    $farmerplots =  FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $PipeInstallation  = Polygon::where('farmer_plot_uniqueid', $plotuniqueid)->first();
    // $PipesLocation="";
    // if($PipeInstallation->pipes_location){
    //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
    // }

    $PipesLocation = PipeInstallationPipeImg::with('reject_reason','reject_validation_detail')->where('l2trash',0)
                        ->where('farmer_plot_uniqueid',$plotuniqueid)->where('l2status','Pending')->get();

   

    $validation_list = PipeImgValidation::where('farmer_plot_uniqueid',$plotuniqueid)->where('level','L-2-Validator')->get();
    $Polygon = json_decode($PipeInstallation->ranges);
 //   foreach($ploygon as $latlng){
 //       dd($latlng);
 //   }
 //   $Polygon =
    $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotuniqueid)->get();

    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$plotuniqueid)->first();

    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
    $reject_module = RejectModule::where('type','PipeInstallation')->where('id',10)->get();//here specifically call polygon reasons
    $page_title = 'Polygon | Pending Detail';
    $page_description = 'Polygon | Pending Detail';
    $action = 'table_farmer';
    $guntha = 0.025000;
    if($plot->state_id == 36){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;

            if($farmerplots){
                $total_area_acres  = 0;
                foreach($farmerplots_area as $plots){
                    $area = number_format((float)$plots->area_in_acers, 2, '.', '');
                    $split = explode('.', $area);//spliting area
                    $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                    $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                    $conversion = explode('.', $result); // split result
                    $conversion = $conversion[1]??0;
                    $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                    $total_area_acres+=$acers;
                }
                $plot->total_area_acres_of_guntha = $total_area_acres;
            }
        }
          $percent_error = "0.0";
        if($PipeInstallation->area_in_acers){
            $mod =  abs($PipeInstallation->area_in_acers - $PipeInstallation->plot_area); //modules in numerator
            $denominator = $PipeInstallation->area_in_acers;//($PipeInstallation->area_in_acers + $PipeInstallation->plot_area)/2;
            //below percentage error between onboarding area and updated area
            $percent_error = 100 * $mod/$denominator;//need to fixed on two decimal place
        }
    return view('l2validator.pipe.polygon-reject-plot-pipe',compact('plot','PipeInstallation','check_pipedata','PipesLocation','cropdata','Polygon','page_title','page_description','action','farmerplots','farmerplot',
                'reject_module','farmerbenefitimg','updated_polygon','validation_list','percent_error'));
  }

}
