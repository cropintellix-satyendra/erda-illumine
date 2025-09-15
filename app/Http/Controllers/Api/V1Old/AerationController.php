<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use PDF;
use Storage;
use Carbon\Carbon;
use DB;
use App\Models\PipeInstallation;
use App\Models\FinalFarmer;
use App\Models\Aeration;
use App\Models\AerationImage;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallationPipeImg;


class AerationController extends Controller
{

  /**
  * Get polygon for plot data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function get_plot_uniqueid(){
        $plot_unique_id = DB::table('final_farmers')->select('farmer_uniqueId','farmer_plot_uniqueid','farmer_name')->where('final_status_onboarding','Approved')
                                ->where('farmer_uniqueId',request('farmer_uniqueId'))->get();
        if(!$plot_unique_id){
          return response()->json(['error'=>true,'message'=>'Something went wrong'],422);
        }
        return response()->json(['success'=>true,'plot_unique_id'=>$plot_unique_id],200);
  }

  /**
  * Get polygon for plot data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function get_plot_pipe(){
      $pipeinstallation = DB::table('pipe_installations')->select('id','farmer_uniqueId','plot_no','pipes_location')->where('farmer_plot_uniqueid',request('farmer_plot_uniqueid'))->first();
      if(!$pipeinstallation){
        //status 0 here represent their is no data for pipeinstallation show we cannot upload aeration for this
        return response()->json(['error'=>true, 'message'=>'No Data','status'=>0],422);
      }
      if($pipeinstallation){
        $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_uniqueId', $pipeinstallation->farmer_uniqueId)->where('plot_no',$pipeinstallation->plot_no)
                      ->select('farmer_uniqueId','farmer_plot_uniqueid','plot_no','pipe_no','lat','lng','images','distance')
                      ->where('trash',0) //->where('status','Approved')
                      ->get();
          // if($pipeinstallation->pipes_location){
          //     $pipeinstallation->pipes_location = json_decode($pipeinstallation->pipes_location);

               return response()->json(['success'=>true,'farmer_plot_uniqueid'=>request('farmer_plot_uniqueid') ,'pipe_installation_id'=>$pipeinstallation->id,
                                   'plot_no'=>$pipeinstallation->plot_no,'PipeList'=>$pipe_data,'status'=>1],200);
          // }
      }
      //status 0 here represent their is no data for pipeinstallation show we cannot upload aeration for this
      return response()->json(['error'=>true, 'message'=>'No Data','status'=>0],422);
  }

  /**
  * Get polygon for plot data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function get_polygon(){
      $pipe_ploygon = PipeInstallation::select('ranges','pipes_location','farmer_uniqueId')
                          ->where('farmer_plot_uniqueid', request('farmer_plot_uniqueid'))->first();
      $pipe_ploygon->ranges = json_decode($pipe_ploygon->ranges);
      $pipe_data = PipeInstallationPipeImg::where('farmer_plot_uniqueid', request('farmer_plot_uniqueid'))->where('pipe_no',request('pipe_no'))
                      ->select('farmer_uniqueId','farmer_plot_uniqueid','plot_no','pipe_no','lat','lng','images','distance')
                      ->first();

      if(!$pipe_ploygon){
        return response()->json(['error'=>true, 'message'=>'No Data'],422);
      }
       return response()->json(['success'=>true, 'polygon'=>$pipe_ploygon->ranges,
                                    'PipeLocation'=>$pipe_data],200);
  }


  /**
  * Aeration info store
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function store(Request $request){
    $validator = Validator::make($request->all(),[
         'pipe_installation_id' => 'required',
         'farmer_plot_uniqueid' => 'required',
         'farmer_uniqueId' => 'required',
         'plot_no' => 'required',
         'aeration_no' => 'required',
         'pipe_no' => 'required',
     ]);
     if ($validator->fails()) {
         return response()->json(collect(['error'=>true,'message'=>'Please fill all details!'])->merge(collect($validator->messages())->map(function($items){ return $items[0]; })), 422);
     }
     $AerationData = DB::table('aerations')->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->where('plot_no', $request->plot_no)
                                ->where('aeration_no', $request->aeration_no)->where('pipe_no', $request->pipe_no)->get();
     if($AerationData->count() > 0){
       return response()->json(['error'=>true,'message'=>'Data Submitted'],422);
     }
    $aeration =  new Aeration;
    $aeration->pipe_installation_id  = $request->pipe_installation_id;
    $aeration->farmer_uniqueId  = $request->farmer_uniqueId;
    $aeration->farmer_plot_uniqueid  = $request->farmer_plot_uniqueid;
    $aeration->plot_no  = $request->plot_no;
    $aeration->aeration_no  = $request->aeration_no;
    $aeration->pipe_no  = $request->pipe_no;
    $aeration->status  = 'Approved';
    $aeration->apprv_reject_user_id  = '1';    
    $aeration->surveyor_id  = auth()->user()->id;
    $aeration->date_survey  = Carbon::parse(Carbon::now())->format('d/m/Y');
    $aeration->time_survey  = Carbon::now()->toTimeString();
    $aeration->save();
    FinalFarmer::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update(['awd_form'=>1]);
    $record =  PlotStatusRecord::create([
                 'farmer_uniqueId'           => $request->farmer_uniqueId,
                 'plot_no'                   => $request->plot_no,
                 'farmer_plot_uniqueid'      => $request->farmer_plot_uniqueid,
                 'level'                     => 'Aeration',
                 'status'                    => 'Pending',
                 'comment'                   => 'Uploaded Aeration Data',
                 'timestamp'                 => Carbon::now(),
                 'user_id'                   => auth()->user()->id,
             ]);

    if(!$aeration){
      return response()->json(['error'=>true,'message'=>'Something went wrong'],422);
    }
    return response()->json(['success'=>true,'farmer_plot_uniqueid'=>$request->farmer_plot_uniqueid ,'pipe_installation_id'=>$request->pipe_installation_id,
                                 'plot_no'=>$request->plot_no],200);
  }

  /**
  * Farmer store benefit images
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function aeration_image(Request $request){
      try{
          //store aeration data image
          $img = new AerationImage;
          $img->pipe_installation_id        = $request->pipe_installation_id;
          $img->farmer_uniqueId  = $request->farmer_uniqueId;
          $img->farmer_plot_uniqueid  = $request->farmer_plot_uniqueid;
          $img->plot_no  = $request->plot_no;
          $img->aeration_no  = $request->aeration_no;
          $img->pipe_no  = $request->pipe_no;
          $img->status  = 'Approved';            
          $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_plot_uniqueid.'/'.'P'.$request->plot_no.'/'.'Aeration_'.$request->aeration_no.'/'.'pipe_'.$request->pipe_no, $request->image);
          $img->path  = Storage::disk('s3')->url($path);//Storage::disk('s3')->url($path);//generate url for s3 path
          $img->save();
          if(!$img){
           return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
          }
         return response()->json(['success'=>true,'pipe_installation_id'=>$request->pipe_installation_id,'farmerUniqueId'=>$request->farmer_uniqueId, 'farmer_plot_uniqueid'=> $request->farmer_plot_uniqueid],200);
       }catch(\Exception $e){
         return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
       }
  }

    /**
  * Aeration check stored data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function check_aeration_data(){
      $aeration = DB::table('aerations')->where('farmer_plot_uniqueid', request('farmer_plot_uniqueid'))
                    ->where('pipe_no',request('pipe_no'))->where('aeration_no',request('aeration_no'))
                    ->select('pipe_installation_id','farmer_uniqueId','farmer_plot_uniqueid','plot_no','aeration_no'
                    ,'pipe_no','path')->first();
      if(!$aeration){
        return response()->json(['error'=>true, 'message'=>'No Data'],422);
      }
      return response()->json(['success'=>true, 'aeration'=>$aeration],200);
  }
}
