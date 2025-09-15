<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FarmerPlotImage;
use App\Models\PlotStatusRecord;
use App\Models\FarmerCropdata;
use App\Models\FarmerBenefit;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Storage;
use Carbon\Carbon;
use DB;
use App\Models\AerationImage;
use App\Models\Aeration;
use App\Models\PipeInstallationPipeImg;
use App\Models\PipeInstallation;
use App\Models\FarmerPlot;
use SebastianBergmann\Type\NullType;

class FarmerEditController extends Controller
{
      /**
     * Famrer registration detail view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
        public function farmer_registration_edit($unique, $plotno){
            $plot = FarmerPlot::where('status','Rejected')->where('farmer_uniqueId',$unique)->where('plot_no',$plotno)
                                            ->select('farmer_id','farmer_uniqueId','reason_id','status','plot_no','area_in_acers','survey_no',
                                            'actual_owner_name','reject_comment','reject_timestamp')
                                            ->with(['Reasons:id,reasons'])->first();

            $farmerplotimg =  DB::table('farmer_land_img')->where('farmer_unique_id',$unique)->where('plot_no',$plotno)->select('id','farmer_id','farmer_unique_id','plot_no','path')->get();
            $farmerplotimg=$farmerplotimg->map(function($q){
                $q->path = Storage::disk('s3')->url($q->path);
                return $q;
            });
            if(!$plot){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['Success'=>True,'plot'=>$plot,'Image'=>$farmerplotimg],200);
        }

        /**
     * Famrer registration detail view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
        public function farmer_registration_update(Request $request){
            if(!empty($request->survey_no)){
                $survey_no = FarmerPlot::where('farmer_uniqueId',$request->unique)->where('reason_id',$request->reason_id)->where('plot_no',$request->plotno)
                                ->update(['survey_no'=>$request->survey_no,'status'=>'Pending','check_update'=>'1','final_status'=>'Pending','finalreject_timestamp'=>null
                                                ,'reason_id'=>null,'reject_comment'=>null,'reject_timestamp'=>null]);
                $record =  PlotStatusRecord::create([
                     'farmer_uniqueId'           => $request->unique,
                     'plot_no'                   => $request->plotno,
                     'farmer_plot_uniqueid'      => $request->unique.'P'.$request->plotno,
                     'level'                     => 'AppUser',
                     'status'                    => 'Pending',
                     'comment'                   => 'Survey no Updated From APP',
                     'timestamp'                 => Carbon::now(),
                     'user_id'                   => auth()->user()->id,
                 ]);
                if(!$survey_no){
                    return response()->json(['error'=>true, 'message'=>'Something went wrong'],422);
                }
                return response()->json(['success'=>true, 'message'=>'Saved Successfully'],200);
            }
            if(!empty($request->plot_area)){
                // return response()->json([$request->plot_area, $request->all()]);
                $plot_area = FarmerPlot::where('farmer_uniqueId',$request->unique)->where('reason_id',$request->reason_id)->where('plot_no',$request->plotno)
                                            ->update(['area_in_acers'=>$request->plot_area,'status'=>'Pending','final_status'=>'Pending','check_update'=>'1','finalreject_timestamp'=>null
                                            ,'reason_id'=>null,'reject_comment'=>null,'reject_timestamp'=>null]);

                $farmerplot = FarmerPlot::where('farmer_uniqueId',$request->unique)->sum('area_in_acers');
                $updatearea = Farmer::where('farmer_uniqueId',$request->unique)->update(['total_plot_area'=>number_format((float) $farmerplot, 2)]);
                $record =  PlotStatusRecord::create([
                     'farmer_uniqueId'           => $request->unique,
                     'plot_no'                   => $request->plotno,
                     'farmer_plot_uniqueid'      => $request->unique.'P'.$request->plotno,
                     'level'                     => 'AppUser',
                     'status'                    => 'Pending',
                     'comment'                   => 'Plot area Updated From APP',
                     'timestamp'                 => Carbon::now(),
                     'user_id'                   => auth()->user()->id,
                 ]);
                if(!$plot_area){
                    return response()->json(['error'=>true, 'message'=>'Something went wrong'],422);
                }
                return response()->json(['success'=>true, 'message'=>'Saved Successfully'],200);
            }
            if(!empty($request->actual_owner_name)){
                $plot_area = FarmerPlot::where('farmer_uniqueId',$request->unique)->where('reason_id',$request->reason_id)->where('plot_no',$request->plotno)
                                            ->update(['actual_owner_name'=>$request->actual_owner_name,'status'=>'Pending','check_update'=>'1','final_status'=>'Pending','finalreject_timestamp'=>null
                                            ,'reason_id'=>null,'reject_comment'=>null,'reject_timestamp'=>null]);
                $record =  PlotStatusRecord::create([
                     'farmer_uniqueId'           => $request->unique,
                     'plot_no'                   => $request->plotno,
                     'farmer_plot_uniqueid'      => $request->unique.'P'.$request->plotno,
                     'level'                     => 'AppUser',
                     'status'                    => 'Pending',
                     'comment'                   => 'owner name Updated From APP',
                     'timestamp'                 => Carbon::now(),
                     'user_id'                   => auth()->user()->id,
                 ]);
                if(!$plot_area){
                    return response()->json(['error'=>true, 'message'=>'Something went wrong'],422);
                }
                return response()->json(['success'=>true, 'message'=>'Saved Successfully'],200);
            }
        }

          /**
     * Famrer registration detail view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
        public function farmer_registration_updateimg(Request $request){
            try{
              //to upload new plot image
                $img = new FarmerPlotImage;
                $img->farmer_id   =       $request->farmer_id;
                $img->farmer_unique_id   = $request->unique;
                $img->plot_no   = $request->plotno;
                $img->image   =       'landrecords';
                $img->path   = Storage::disk('s3')->put(config('storagesystems.store').'/'.$request->unique, $request->image); //$request->image->storeAs('plot/'.$request->farmer_unique_id, 'plotImg-'.$request->sr.'-'.$request->file('image')->getClientOriginalName(), 'public');//
                $img->save();
                $plot_area = FarmerPlot::where('farmer_uniqueId',$request->unique)->where('plot_no',$request->plotno)
                                            ->update(['check_update'=>'1','status'=>'Pending'
                                            ,'reason_id'=>'','reject_comment'=>'','reject_timestamp'=>'','final_status'=>'Pending','finalreject_timestamp'=>'']);

                $record =  PlotStatusRecord::create([
                     'farmer_uniqueId'           => $request->unique,
                     'plot_no'                   => $request->plotno,
                     'farmer_plot_uniqueid'      => $request->unique.'P'.$request->plotno,
                     'level'                     => 'AppUser',
                     'status'                    => 'Pending',
                     'comment'                   => 'Image Updated From APP',
                     'timestamp'                 => Carbon::now(),
                     'user_id'                   => auth()->user()->id,
                 ]);
                if(!$img){
                    return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
                }
              return response()->json(['success'=>true,'farmerId'=>$img->farmer_id,'farmerUniqueId'=>$img->farmer_unique_id],200);
            }catch(Exception $e){
              return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
            }
        }


       /**
     * Famrer registration detail view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_cropdata_detail(Request $request){
            $CropDataDetail =   FarmerCropdata::where('surveyor_id',auth()->user()->id)->where('farmer_uniqueId',$request->farmer_uniqueId)
                                                ->select('farmer_id','farmer_uniqueId','plot_no','area_in_acers','season','crop_variety','dt_irrigation_last','dt_ploughing','dt_transplanting'
                                                    ,'surveyor_id','surveyor_name')->first();
             if(!$CropDataDetail){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['Success'=>True,'CropData'=>$CropDataDetail,'BenefitImage'=>$farmerbenefitimg],200);
      }



       /**
     * Famrer registration detail view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_benefit_detail(Request $request){
            $CropDataDetail = FarmerBenefit::where('surveyor_id',auth()->user()->id)->where('farmer_uniqueId',$request->farmer_uniqueId)
                                                ->select('farmer_id','farmer_uniqueId','total_plot_area','seasons','benefit_id','benefit','surveyor_id','surveyor_name')->first();
            $farmerbenefitimg =  DB::table('farmer_benefit_images')->where('farmer_uniqueId',$request->farmer_uniqueId)->select('farmer_id','farmer_uniqueId','path')->get();
            if(!$CropDataDetail){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['Success'=>True,'CropData'=>$CropDataDetail,'BenefitImage'=>$farmerbenefitimg],200);
      }


     /**
     * Famrer data view based on status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function getBenefitList(){
            $benefitData  = Farmer::where('surveyor_id',auth()->user()->id)->where('status_benefits',request('status'))->select('id','farmer_uniqueId','no_of_plots','total_plot_area')->where('benefit_form','1')
                                                                ->with('BenefitsData:farmer_id,farmer_uniqueId,total_plot_area,seasons,benefit_id,benefit,surveyor_id,surveyor_name')
                                                                ->paginate(10);
            if(!$benefitData){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'Benefits'=>$benefitData],200);
      }



      /**
     * aeration image upload update
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function aeration_img_update(Request $request){
        //moving old image to other folder..
        // $aeration_img = AerationImage::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('aeration_no', $request->aeration_no)
        //                             ->where('pipe_no', $request->pipe_no)->where('trash',0)->get();
        // //call image from S3 bucket
        // $aeration_old_img = Storage::disk('s3')->allFiles(config('storagesystems.test').'/'.$request->farmer_uniqueId);
        // $sign_array_count = count($aeration_old_img);//get count

        // foreach($aeration_img as $path){
        //     $move_to_reject_path=$aeration_old_path="";
        //     for($s = 0; $s <= $sign_array_count; $s++){
        //         $oldpathfile = explode('/',$path->path);//seprate file name
        //         if($path->path == Storage::disk('s3')->url($aeration_old_img[$s])){//checking the url stored in db and from s3 bucket
        //             $aeration_old_path = $aeration_old_img[$s];//store old path of the s3 bucket for aeration
        //             $move_to_reject_path = config('storagesystems.test').'/'.$request->farmer_uniqueId.'/P'.$path->plot_no.'/Rejected/'.$oldpathfile[8];//create new path to move file
        //             $newpathfroms3 =  Storage::disk('s3')->move($aeration_old_path, $move_to_reject_path);// move data from folder to rejected one

        //             break;
        //         }
        //     }//forloop end
        //     $update_img_old = AerationImage::where('id', $path->id)->update([
        //                     'path' => Storage::disk('s3')->url($move_to_reject_path) ,
        //                     'status' => 'Rejected',
        //                     'trash'  => 1,
        //     ]);

        // }//foreach end

          //store aeration data image

        //   dd($request->image, gettype($request->image));
        foreach($request->image as $img_data){
            $img = new AerationImage;
            $img->pipe_installation_id        = $request->pipe_installation_id;
            $img->farmer_uniqueId  = $request->farmer_uniqueId;
            $img->farmer_plot_uniqueid  = $request->farmer_plot_uniqueid;
            $img->plot_no  = $request->plot_no;
            $img->aeration_no  = $request->aeration_no;
            $img->status  = 'Approved';            
            $img->pipe_no  = $request->pipe_no;
            $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_plot_uniqueid.'/'.'P'.$request->plot_no.'/'.'Aeration_'.$request->aeration_no.'/'.'pipe_'.$request->pipe_no, $img_data);
            $img->path  = Storage::disk('s3')->url($path);//generate url for s3 path
            $img->save();
        }
        //   $img = new AerationImage;
        //   $img->pipe_installation_id        = $request->pipe_installation_id;
        //   $img->farmer_uniqueId  = $request->farmer_uniqueId;
        //   $img->farmer_plot_uniqueid  = $request->farmer_plot_uniqueid;
        //   $img->plot_no  = $request->plot_no;
        //   $img->aeration_no  = $request->aeration_no;
        //   $img->pipe_no  = $request->pipe_no;
        //   $path = Storage::disk('s3')->put(config('storagesystems.test').'/'.$request->farmer_uniqueId.'/'.'P'.$request->plot_no.'/'.'Aeration_'.$request->aeration_no.'/'.'pipe_'.$request->plot_no, $request->image);
        //   $img->path  = Storage::disk('s3')->url($path);//generate url for s3 path
        //   $img->save();


        $aeration_updt = Aeration::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('aeration_no', $request->aeration_no)->where('pipe_no', $request->pipe_no)->update([
                                                                                                                    'status' => 'Approved',
                                                                                                                    'l2_status' => 'Pending'
                                                                                                                ]);
        $record =  PlotStatusRecord::create([
            'farmer_uniqueId'           => $request->farmer_uniqueId,
            'plot_no'                   => $request->plot_no,
            'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
            'level'                     => 'AppUser',
            'status'                    => 'Pending',
            'comment'                   => 'Aeration Updated Image From APP',
            'timestamp'                 => Carbon::now(),
            'user_id'                   => auth()->user()->id,
        ]);

          if(!$img){
           return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
          }
         return response()->json(['success'=>true,'pipe_installation_id'=>$request->pipe_installation_id,'farmerUniqueId'=>$request->farmer_uniqueId, 'farmer_plot_uniqueid'=> $request->farmer_plot_uniqueid],200);


    }


      /**
     * aeration image upload update
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function pipe_installtion_img_update(Request $request){
         //moving old image to other folder
         $pipe_img = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('id', $request->pipe_img_id)->where('l2status','Rejected')->first();
         $pipe_img_id = $pipe_img->id;

        //  //call image from S3 bucket
        //  $pipe_old_img = Storage::disk('s3')->allFiles(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_plot_uniqueid.'/'.'P'.$request->plot_no.'/'.'PipeInstallation');
        //  $pipe_array_count = count($pipe_old_img);//get count


        //      $move_to_reject_path=$aeration_old_path="";
        //      for($s = 0; $s <= $pipe_array_count; $s++){
        //          $oldpathfile = explode('/',$pipe_img->images);//seprate file name

        //          if($pipe_img->images == Storage::disk('s3')->url($pipe_old_img[$s])){//checking the url stored in db and from s3 bucket


        //             // dd($s, $pipe_img->images , Storage::disk('s3')->url($pipe_old_img[$s]));


        //              $pipe_old_path =$pipe_old_img[$s];//store old path of the s3 bucket for aeration

        //              $move_to_reject_path = config('storagesystems.test').'/'.$request->farmer_uniqueId.'/P'.$pipe_img->plot_no.'/Rejected/'.$oldpathfile[7];//create new path to move file

        //             //  dd($move_to_reject_path, $pipe_old_path);

        //              $newpathfroms3 =  Storage::disk('s3')->move($pipe_old_path, $move_to_reject_path);// move data from folder to rejected one

        //              break;
        //          }
        //      }//forloop end


        

        // //  }//foreach end



             $update_img_old = PipeInstallationPipeImg::where('id', $pipe_img_id)->update([
                             'status' => 'Rejected',
                             'trash'  =>   1,
                             'l2status' => 'Rejected',
                             'l2trash'  =>   1
             ]);


       $pipe_img_update = new PipeInstallationPipeImg;
       $pipe_img_update->farmer_uniqueId          =  $request->farmer_uniqueId;
       $pipe_img_update->farmer_plot_uniqueid          =  $request->farmer_plot_uniqueid;

       $pipe_img_update->plot_no          =  $request->plot_no;
       $pipe_img_update->pipe_no          =  $request->pipe_no;
       $pipe_img_update->lat          =  $request->lat;
       $pipe_img_update->lng          =  $request->lng;

       $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_plot_uniqueid.'/'.'P'.$request->plot_no.'/'.'PipeInstallation', $request->images);
       $pipe_img_update->images        =  Storage::disk('s3')->url($path);

       $pipe_img_update->distance      =  $request->distance;
       $pipe_img_update->date          =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('d-m-Y');
       $pipe_img_update->time          =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('h:i A');
       $pipe_img_update->surveyor_id   =  auth()->user()->id;
       $pipe_img_update->save();

    //    $pipe_installation = PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
    //    $img_data_reject = PipeInstallationPipeImg::select('status')->where('status','Pending')->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)
    //                    ->where('plot_no',$request->plot_no)->where('trash',0)->get();
    //         if($img_data_reject->count() == $pipe_installation->installed_pipe){
    //            //if all pipe image is rejected then only below code will execute
    //            $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update([
    //                "status" => "Pending",
    //                "l2_status" => "Pending",
    //                "apprv_reject_user_id" => NULL
    //                    ]);
    //         }

        $imgupdate =  PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update([
                            "status" => "Approved",
                            "l2_status" => "Pending",
                            "apprv_reject_user_id" => NULL,
                            'reason_id'     =>   NULL,
                            'l2_apprv_reject_user_id'=> NULL,
                        ]);

        $record =  PlotStatusRecord::create([
            'farmer_uniqueId'           => $request->farmer_uniqueId,
            'plot_no'                   => $request->plot_no,
            'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
            'module'                    => 'PipeInstallation-Image-PIPE '.$request->pipeno,
            'level'                     => 'AppUser',
            'status'                    => 'Pending',
            'comment'                   => 'Updated PipeInstallation Image',
            'timestamp'                 => Carbon::now(),
            'user_id'                   => auth()->user()->id,
        ]);

        // $pipe = PipeInstallation::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no',$request->plot_no)->first();
        // $pipe->installed_pipe  = $pipe->installed_pipe + 1;
        // $pipe->no_pipe_req     = $request->no_pipe_req;
        // $pipe->no_pipe_avl     = $request->no_pipe_avl;
        // $pipe->installing_pipe = $request->installing_pipe;
        // $pipe->save();


        if(!$pipe_img_update){
           return response()->json(['error'=>true,'message'=>'Somethings went wrong'],422);
        }
        return response()->json(['success'=>true,'message'=>'Pipe image Successfully','FarmerId'=>$request->farmer_id, 'FarmerUniqueID'=>$request->farmer_uniqueId,'FarmerUniquePlotID'=>$request->farmer_plot_uniqueid,'PlotNo'=>$request->plot_no,'pipeno'=>$request->pipe_no],200);


    }


    public function updatePipeInstallation(Request $request){
        // try{
                $pipe_data=PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
                $pipe_img = DB::table('pipe_img_validation')->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
                $process_update_ranges = json_encode($request->updated_polygon);//store new polygon in variable
                DB::table('old_polygons')->insert([//moving old polygon to this table
                    "farmer_uniqueId"   =>  $pipe_data->farmer_uniqueId,
                    "farmer_plot_uniqueid"     =>  $pipe_data->farmer_plot_uniqueid,
                    "plot_no"   =>  $pipe_data->plot_no,
                    "polygon"   =>  $pipe_data->ranges,//adding old polygon from pipeinstallation table
                    "surveyor_id"   =>  auth()->user()->id,
                    "polygon_date_time"  => $pipe_data->polygon_date_time,
                    "type"          => 'OLD POLYGON',
                    "google_plot_area" => $pipe_data->plot_area,
                    "created_at"     => carbon::now(),
                    "updated_at"    => carbon::now(),
                ]);
                //updating new polygon
                PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update([
                    'ranges' => $process_update_ranges,
                    'plot_area' => $request->updated_poly_area,//number_format($request->updated_poly_area, 2, '.', ''),
                    'polygon_date_time'  => $request->polygon_date_time??$pipe_data->polygon_date_time,
                    'status'             => 'Approved',
                    'l2_status'          => 'Pending',
                    'reason_id'          => NULL,
                    'delete_polygon'     => NULL,
                ]);
                //also updating pipeimage status to pending
                // PipeInstallationPipeImg::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update([
                //     'status'=>"Pending",
                //     'l2status'=>'Pending',
                //     'reason_id'=>NUll
                // ]);
                //updating this table to record who is change status
                $pipe_img_validation  = DB::table('pipe_img_validation')->insert([
                    'farmer_uniqueId'           => $pipe_data->farmer_uniqueId,
                    'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
                    'plot_no'                   => $pipe_data->plot_no,
                    'pipe_no'                   => $pipe_img->pipe_no,
                    'status'                    => 'Pending',
                    'level'                     => 'AppUser',
                    'user_id'                   => auth()->user()->id,
                    'comment'                   => Null,
                    'reject_reason_id'          => NULL,
                    'timestamp'                 => Carbon::now(),
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                ]);
                //update record
                $record =  PlotStatusRecord::create([
                    'farmer_uniqueId'           => $pipe_data->farmer_uniqueId,
                    'plot_no'                   => $pipe_data->plot_no,
                    'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
                    'level'                     => 'AppUser',
                    "module"                    => 'PipeInstallation-polygon-'.$pipe_data->farmer_uniqueId,
                    'status'                    => 'Pending',
                    'comment'                   => 'Updating PipeInstallation polygon',
                    'timestamp'                 => Carbon::now(),
                    'user_id'                   => auth()->user()->id,
                ]);
                return response()->json(['success'=>true, 'message'=>'Updated Successfully'],200);
            // } catch (\Exception $e) {
            //     return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
            // }
        }

}
