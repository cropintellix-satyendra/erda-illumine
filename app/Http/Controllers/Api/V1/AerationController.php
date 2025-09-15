<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\AreationDate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use PDF;
use Log;
use Storage;
use Carbon\Carbon;
use App\Models\PipeInstallation;
use App\Models\FinalFarmer;
use App\Models\Aeration;
use App\Models\Polygon;
use App\Models\AerationImage;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallationPipeImg;
use App\Models\UserTarget;
use Illuminate\Support\Facades\DB;

class AerationController extends Controller
{

  /**
  * Get polygon for plot data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function get_plodt_uniqueid(){
        $plot_unique_id = DB::table('final_farmers')->select('farmer_uniqueId','farmer_plot_uniqueid','farmer_name')->where('final_status_onboarding','Approved')
                                ->where('farmer_uniqueId',request('farmer_uniqueId'))->get();
        if(!$plot_unique_id){
          return response()->json(['error'=>true,'message'=>'Something went wrong'],422);
        }
        return response()->json(['success'=>true,'plot_unique_id'=>$plot_unique_id],200);
  }

  public function getdd_plot_uddniqueid(){
        $plot_unique_id = DB::table('final_farmers')->select('farmer_uniqueId','farmer_plot_uniqueid','farmer_name')->where('final_status_onboarding','Approved')
                                ->where('farmer_uniqueId',request('farmer_uniqueId'))->get();

        $areation=DB::table('aerations')->where('farmer_uniqueId',request('farmer_uniqueId'))->latest()->first();
        if(!$plot_unique_id){
          return response()->json(['error'=>true,'message'=>'Something went wrong'],422);
        }
        return response()->json(['success'=>true,'plot_unique_id'=>$plot_unique_id,'last_date'=>$areation->date_survey??'NA'],200);
  }

//     public function get_plot_uniqueid(){
//     $plot_unique_id = DB::table('final_farmers')->select('farmer_uniqueId','farmer_plot_uniqueid','farmer_name')
//     // ->where('final_status_onboarding','Approved')   // Commented this line due we now not want to check aproved
//                             ->where('farmer_uniqueId',request('farmer_uniqueId'))->get();

   

//     $data=[];
//    $date=AreationDate::where('id',1)->first();
    
//     foreach($plot_unique_id as $plot_unique_id_dta){
//       $areation=DB::table('aerations')->where('farmer_plot_uniqueid',$plot_unique_id_dta->farmer_plot_uniqueid)->latest()->first();
//     $data[]=[
//       "farmer_uniqueId"=>$plot_unique_id_dta->farmer_uniqueId,
//       "farmer_plot_uniqueid"=>$plot_unique_id_dta->farmer_plot_uniqueid,
//       "farmer_name"=>$plot_unique_id_dta->farmer_name,
//       "last_date"=>$date->date,
//       "areation_name"=>$areation->aeration_no??'0'

//     ];
//     }
//     if(!$plot_unique_id){
//       return response()->json(['error'=>true,'message'=>'Something went wrong'],422);
//     }
//     return response()->json(['success'=>true,'plot_unique_id'=>$data],200);
// } 


public function get_plot_uniqueid(){
  $plot_unique_id = DB::table('final_farmers')->select('farmer_uniqueId','farmer_plot_uniqueid','farmer_name')
  // ->where('final_status_onboarding','Approved')
                          ->where('farmer_uniqueId',request('farmer_uniqueId'))->get();
    $date = DB::table('areation_date')->select('id','aeration_duration')->first();
  $data=[];

  foreach($plot_unique_id as $plot_unique_id_dta){

    $areation=DB::table('aerations')->where('farmer_plot_uniqueid',$plot_unique_id_dta->farmer_plot_uniqueid)->latest()->first();
  $data[]=[
    "farmer_uniqueId"=>$plot_unique_id_dta->farmer_uniqueId,
    "farmer_plot_uniqueid"=>$plot_unique_id_dta->farmer_plot_uniqueid,
    "farmer_name"=>$plot_unique_id_dta->farmer_name,
    "last_date"=>$areation->date_survey??'0',
    "areation_name"=>$areation->aeration_no??'0',
    "aeration_duration"=>$date??'1'
  ];
  }
  if(!$plot_unique_id){
    return response()->json(['error'=>true,'message'=>'Something went wrong'],422);
  }
  return response()->json(['success'=>true,'plot_unique_id'=>$data],200);
}

// public function get_plot_uniqueid_pipewise(){
//   // Fetch the constant aeration duration value from areation_date table
//   $constant_aeration_duration = DB::table('areation_date')->select('aeration_duration')->value('aeration_duration');

//   // Fetch the plot unique IDs based on farmer_uniqueId
//   $plot_unique_id = DB::table('final_farmers')
//       ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'farmer_name')
//       ->where('farmer_uniqueId', request('farmer_uniqueId'))
//       ->get();

//   $data = [];

//   foreach ($plot_unique_id as $plot_unique_id_dta) {
//       // Fetch the latest aeration record for the given plot unique ID
//       $areation = DB::table('aerations')
//           ->where('farmer_plot_uniqueid', $plot_unique_id_dta->farmer_plot_uniqueid)
//           ->first();

//       // Using correct column names from the aerations table
//       $aeration_no = $areation->aeration_no ?? '0';
//       $pipe_no = $areation->pipe_no ?? '0';

//       // Check if the same aeration_no and pipe_no exists
//       $matching_aeration = DB::table('aerations')
//           ->where('aeration_no', $aeration_no)
//           ->where('pipe_no', $pipe_no)
//           ->where('farmer_plot_uniqueid', $plot_unique_id_dta->farmer_plot_uniqueid)
//           ->exists(); // Check existence without retrieving all columns

//           $date = DB::table('areation_date')->select('id','aeration_duration')->first();
//       // If a matching aeration record exists, use the constant aeration duration
//       $aeration_duration = $matching_aeration ? $date : '0';

//       $data[] = [
//           "farmer_uniqueId" => $plot_unique_id_dta->farmer_uniqueId,
//           "farmer_plot_uniqueid" => $plot_unique_id_dta->farmer_plot_uniqueid,
//           "farmer_name" => $plot_unique_id_dta->farmer_name,
//           "last_date" => $areation->date_survey ?? '0',
//           "areation_name" => $aeration_no,
//           "pipe_number" => $pipe_no,
//           "aeration_duration" => $aeration_duration
//       ];
//   }

//   if ($plot_unique_id->isEmpty()) {
//       return response()->json(['error' => true, 'message' => 'Something went wrong'], 422);
//   }

//   return response()->json(['success' => true, 'plot_unique_id' => $data], 200);
// }

public function get_plot_uniqueid_pipewise() {
  // Fetch the constant aeration duration value from the aeration_date table
  $constant_aeration_duration = DB::table('areation_date')->select('aeration_duration')->value('aeration_duration');

  // Fetch all plot unique IDs for the given farmer_uniqueId
  $plot_unique_ids = DB::table('final_farmers')
      ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'farmer_name')
      ->where('farmer_plot_uniqueid', request('farmer_plot_uniqueid'))
      ->get();

  $data = [];

  foreach ($plot_unique_ids as $plot_unique_id_dta) {
      // Fetch all aeration records for the given plot unique ID
      $aerations = DB::table('aerations')
          ->where('farmer_plot_uniqueid', $plot_unique_id_dta->farmer_plot_uniqueid)
          ->get();

      foreach ($aerations as $aeration) {
          // Extract values from aeration record
          $aeration_no = $aeration->aeration_no ?? '0';
          $pipe_no = $aeration->pipe_no ?? '0';

          // Check if the same aeration_no and pipe_no exists
          $matching_aeration = DB::table('aerations')
              ->where('aeration_no', $aeration_no)
              ->where('pipe_no', $pipe_no)
              ->where('farmer_plot_uniqueid', $plot_unique_id_dta->farmer_plot_uniqueid)
              ->exists(); // Check existence without retrieving all columns

          // Use the constant aeration duration if a matching aeration exists
          $aeration_duration = $matching_aeration ? $constant_aeration_duration : '0';

          // Add each aeration record to the response data
          $data[] = [
              "pipe_no" => $pipe_no,
              "aeration_no" => $aeration_no,
              "last_date" => $aeration->date_survey ?? '0',
              "aeration_duration" => $aeration_duration
          ];
      }
  }

  if ($plot_unique_ids->isEmpty()) {
      return response()->json(['error' => true, 'message' => 'No plot unique IDs found for the given farmer unique ID'], 422);
  }

  return response()->json(['success' => true, 'plot_unique_id' => $data], 200);
}



  /**
  * Get polygon for plot data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function get_plot_pipe(){
      $pipeinstallation = DB::table('polygons')->select('id','farmer_uniqueId','plot_no')->where('farmer_plot_uniqueid',request('farmer_plot_uniqueid'))->first();
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
      $pipe_ploygon = Polygon::select('ranges','farmer_uniqueId')
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
     $AerationData = DB::table('aerations')->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->where('plot_no', $request->plot_no)->where('financial_year',$request->financial_year)->where('season',$request->season)
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
    $aeration->financial_year  = $request->financial_year;
    $aeration->season  = $request->season;
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

     $userTarget = UserTarget::updateOrCreate(
        [
            'user_id' => auth()->user()->id,
            'module_id' => '5',
            'date' => now()->toDateString(),
        ],
        [
            'count' => DB::raw('count + 1'),
        ]
    );

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
    \Log::info('Aeration Image Request:', $request->all());
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
      try{

        $AerationData = DB::table('aeration_images')->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->where('plot_no', $request->plot_no)->where('financial_year',$request->financial_year)->where('season',$request->season)
              ->where('aeration_no', $request->aeration_no)->where('pipe_no', $request->pipe_no)->get();
      if($AerationData->count() > 2){
      return response()->json(['success'=>true,'message'=>'Data Submitted'],200);
      }

          //store aeration data image
          $img = new AerationImage;
          $img->pipe_installation_id        = $request->pipe_installation_id;
          $img->farmer_uniqueId  = $request->farmer_uniqueId;
          $img->farmer_plot_uniqueid  = $request->farmer_plot_uniqueid;
          $img->plot_no  = $request->plot_no;
          $img->aeration_no  = $request->aeration_no;
          $img->pipe_no  = $request->pipe_no;
          $img->financial_year  = $request->financial_year;
          $img->season  = $request->season;
          $img->status  = 'Approved';            
          $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_plot_uniqueid.'/'.'P'.$request->plot_no.'/'.'Aeration_'.$request->aeration_no.'/'.'pipe_'.$request->pipe_no, $request->image);
          $img->path  = Storage::disk('s3')->url($path);//Storage::disk('s3')->url($path);//generate url for s3 path
          $img->save();
          if(!$img){
           return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
          }
         return response()->json(['success'=>true,'pipe_installation_id'=>$request->pipe_installation_id,'farmerUniqueId'=>$request->farmer_uniqueId, 'farmer_plot_uniqueid'=> $request->farmer_plot_uniqueid],200);
       }catch(\Exception $e){
         \Log::info($e);
         return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
       }
  }

    /**
  * Aeration check stored data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function check_aeration_data() {

    $aerationNo = request('aeration_no');

    if ($aerationNo > 1) {
        // Check if previous aeration data exists
        $previousAeration = DB::table('aerations')
            ->where('farmer_plot_uniqueid', request('farmer_plot_uniqueid'))
            ->where('pipe_no', request('pipe_no'))
            ->where('aeration_no', $aerationNo - 1)
            ->first();

        if (!$previousAeration) {
            return response()->json(['error' => true, 'message' => 'Previous aeration data does not exist for aeration number ' . ($aerationNo - 1)  ],  423); // This response show the aeartion not available for filling.
        }
    }

    // Retrieve current aeration data
    $aeration = DB::table('aerations')
        ->where('farmer_plot_uniqueid', request('farmer_plot_uniqueid'))
        ->where('pipe_no', request('pipe_no'))
        ->where('aeration_no', $aerationNo)
        ->select('pipe_installation_id', 'farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'aeration_no', 'pipe_no', 'path')
        ->first();

    if (!$aeration) {
        return response()->json(['error' => true, 'message' => 'No Data'], 422);  // Dont Change message its check in app
    }

    return response()->json(['success' => true, 'message' => 'success', 'aeration' => $aeration], 200);
}


public function check_aeration_data_new(Request $request) {
  $aerationNo = request('aeration_no');
  $pipeNo = request('pipe_no');
  $farmerPlotUniqueId = request('farmer_plot_uniqueid');
  \Log::info('Request data Aeration Check Data:', $request->all());

  // Step 1: Check if the requested aeration is 1
  if ($aerationNo == 1) {
      // Check if the aeration 1 for the specific pipe and plot is already filled
      $aeration = DB::table('aerations')
          ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
          ->where('pipe_no', $pipeNo)
          ->where('aeration_no', 1)
          ->first();

      if ($aeration) {
          return response()->json(['error' => true, 'message' => 'Aeration 1 for Pipe ' . $pipeNo . ' is already filled.'], 423);
      } else {
          return response()->json(['success' => true, 'message' => 'You can proceed with Aeration 1 of Pipe no ' . $pipeNo], 200);
      }
  }

  // Step 2: For aeration greater than 1, check if previous aeration is filled
  if ($aerationNo > 1) {
      $previousAeration = DB::table('aerations')
          ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
          ->where('pipe_no', $pipeNo)
          ->where('aeration_no', $aerationNo - 1)
          ->first();

      if (!$previousAeration) {
          return response()->json(['error' => true, 'message' => 'Fill Aeration ' . ($aerationNo - 1) . ' first.'], 425);
      }
  }


  // Step 3: Check if the current aeration data already exists
  $aeration = DB::table('aerations')
      ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
      ->where('pipe_no', $pipeNo)
      ->where('aeration_no', $aerationNo)
      ->select('id', 'farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'aeration_no', 'pipe_no', 'date_survey')
      ->first();

  if (!$aeration) {
      // Fetch the date_survey of the previous aeration if current aeration is not found
      $previousAeration = DB::table('aerations')
          ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
          ->where('pipe_no', $pipeNo)
          ->where('aeration_no', $aerationNo - 1)
          ->select('date_survey')
          ->first();

      if ($previousAeration) {
        $aeration_date = DB::table('areation_date')->select('id', 'aeration_duration')->first();
          return response()->json(['error' => true, 'message' => 'No Data', 'last_date' => $previousAeration->date_survey , 'aeration_duration' => $aeration_date], 200);
      } else {
          return response()->json(['error' => true, 'message' => ( $aerationNo - 1 ) . ' Not Found ' ], 422);
      }
  }

  $aeration_date = DB::table('areation_date')->select('id', 'aeration_duration')->first();

  $data = [
      'farmer_plot_uniqueid' => $aeration->farmer_plot_uniqueid,
      'aeration_no' => $aeration->aeration_no,
      'pipe_no' => $aeration->pipe_no,
      'last_date' => $aeration->date_survey,
      'aeration_duration' => $aeration_date
  ];

  return response()->json(['success' => true, 'message' => 'Aeration '. $aerationNo . ' Already Filled', 'aeration' => $data], 423);
}




public function check_aeration_data_latest(Request $request) {
    $aerationNo = request('aeration_no');
    $pipeNo = request('pipe_no');
    $farmerPlotUniqueId = request('farmer_plot_uniqueid');
    $season = request('season'); // Assuming season is passed in the request
    $financialYear = request('financial_year'); // Assuming financial_year is passed in the request
  
    // Step 1: Check if the requested aeration is 1
    if ($aerationNo == 1) {
        // Check if the aeration 1 for the specific pipe, plot, season, and financial year is already filled
        $aeration = DB::table('aerations')
            ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
            ->where('pipe_no', $pipeNo)
            ->where('aeration_no', 1)
            ->where('season', $season)
            ->where('financial_year', $financialYear)
            ->first();
  
        if ($aeration) {
            $seasonName = \App\Models\Season::find($season)->name;
            return response()->json(['error' => true, 'message' => 'Aeration 1 for Pipe ' . $pipeNo . ' is already filled for '.$seasonName . ' season and ' . $financialYear . ' year.'], 423);
        } else {
          $seasonName = \App\Models\Season::find($season)->name;
            return response()->json(['success' => true, 'message' => 'You can proceed with Aeration 1 of Pipe no ' . $pipeNo . ' for '.$seasonName . ' season and ' . $financialYear . ' year.'], 200);
        }
    }
  
    // Step 2: For aeration greater than 1, check if previous aeration is filled
    if ($aerationNo > 1) {
        $previousAeration = DB::table('aerations')
            ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
            ->where('pipe_no', $pipeNo)
            ->where('aeration_no', $aerationNo - 1)
            ->where('season', $season)
            ->where('financial_year', $financialYear)
            ->first();
            // dd($previousAeration);
  
        if (!$previousAeration) {
          $seasonName = \App\Models\Season::find($season)->name;
            return response()->json(['error' => true, 'message' => 'Fill Aeration ' . ($aerationNo - 1) . ' first for  '. $seasonName . ' season and ' . $financialYear . ' year.'], 425);
        }
    }
  
    // Step 3: Check if the current aeration data already exists
    $aeration = DB::table('aerations')
        ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
        ->where('pipe_no', $pipeNo)
        ->where('aeration_no', $aerationNo)
        ->where('season', $season)
        ->where('financial_year', $financialYear)
        ->select('id', 'farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'aeration_no', 'pipe_no', 'date_survey')
        ->first();
  
    if (!$aeration) {
        // Fetch the date_survey of the previous aeration if current aeration is not found
        $previousAeration = DB::table('aerations')
            ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
            ->where('pipe_no', $pipeNo)
            ->where('aeration_no', $aerationNo - 1)
            ->where('season', $season)
            ->where('financial_year', $financialYear)
            ->select('date_survey')
            ->first();
  
        if ($previousAeration) {
            $aeration_date = DB::table('areation_date')->select('id', 'aeration_duration')->first();
            return response()->json(['error' => true, 'message' => 'No Data', 'last_date' => $previousAeration->date_survey, 'aeration_duration' => $aeration_date], 422);
        } else {
            return response()->json(['error' => true, 'message' => ($aerationNo - 1) . ' Not Found'], 422);
        }
    }
  
    $aeration_date = DB::table('areation_date')->select('id', 'aeration_duration')->first();
    $seasonName = \App\Models\Season::find($season)->name;
  
    $data = [
        'farmer_plot_uniqueid' => $aeration->farmer_plot_uniqueid,
        'aeration_no' => $aeration->aeration_no,
        'pipe_no' => $aeration->pipe_no,
        'last_date' => $aeration->date_survey,
        'aeration_duration' => $aeration_date,
    ];
  
    return response()->json(['success' => true, 'message' => 'Aeration ' . $aerationNo . ' Already Filled in this '. $seasonName . ' season and' . $financialYear. ' Year.', 'aeration' => $data], 423);
  }




  public function plotwise_aeration_no(Request $request){
    // Get the latest aeration number for the given farmer plot unique ID
    $latestAeration = Aeration::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                              ->orderBy('aeration_no', 'desc')
                              ->first();
                            //   dd($latestAeration);
  
    // If there's no record found, start from 1; otherwise, increment the latest aeration number
    $nextAerationNo = $latestAeration ? $latestAeration->aeration_no + 1 : 1;
    // dd($nextAerationNo);
  
    return response()->json(['success' => true, 'message' => 'Successfully Generated', 'aeration_no' => $nextAerationNo], 200);
  }

}


// public function check_aeration_data_latest(Request $request) {
//     $aerationNo = request('aeration_no');
//     $pipeNo = request('pipe_no');
//     $farmerPlotUniqueId = request('farmer_plot_uniqueid');
//     $season = request('season'); // Assuming season is passed in the request
//     $financialYear = request('financial_year'); // Assuming financial_year is passed in the request
  
//     // Step 1: Check if the requested aeration is 1
//     if ($aerationNo == 1) {
//         // Check if the aeration 1 for the specific pipe, plot, season, and financial year is already filled
//         $aeration = DB::table('aerations')
//             ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
//             ->where('pipe_no', $pipeNo)
//             ->where('aeration_no', 1)
//             ->where('season', $season)
//             ->where('financial_year', $financialYear)
//             ->first();
  
//         if ($aeration) {
//             $seasonName = \App\Models\Season::find($season)->name;
//             return response()->json(['error' => true, 'message' => 'Aeration 1 for Pipe ' . $pipeNo . ' is already filled for '.$seasonName . ' season and ' . $financialYear . ' year.'], 423);
//         } else {
//           $seasonName = \App\Models\Season::find($season)->name;
//             return response()->json(['success' => true, 'message' => 'You can proceed with Aeration 1 of Pipe no ' . $pipeNo . ' for '.$seasonName . ' season and ' . $financialYear . ' year.'], 200);
//         }
//     }
  
//     // Step 2: For aeration greater than 1, check if previous aeration is filled
//     if ($aerationNo > 1) {
//         $previousAeration = DB::table('aerations')
//             ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
//             ->where('pipe_no', $pipeNo)
//             ->where('aeration_no', $aerationNo - 1)
//             ->where('season', $season)
//             ->where('financial_year', $financialYear)
//             ->first();
  
//         if (!$previousAeration) {
//           $seasonName = \App\Models\Season::find($season)->name;
//             return response()->json(['error' => true, 'message' => 'Fill Aeration ' . ($aerationNo - 1) . ' first for  '. $seasonName . ' season and ' . $financialYear . ' year.'], 425);
//         }
//     }
  
//     // Step 3: Check if the current aeration data already exists
//     $aeration = DB::table('aerations')
//         ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
//         ->where('pipe_no', $pipeNo)
//         ->where('aeration_no', $aerationNo)
//         ->where('season', $season)
//         ->where('financial_year', $financialYear)
//         ->select('id', 'farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'aeration_no', 'pipe_no', 'date_survey')
//         ->first();
  
//     if (!$aeration) {
//         // Fetch the date_survey of the previous aeration if current aeration is not found
//         $previousAeration = DB::table('aerations')
//             ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
//             ->where('pipe_no', $pipeNo)
//             ->where('aeration_no', $aerationNo - 1)
//             ->where('season', $season)
//             ->where('financial_year', $financialYear)
//             ->select('date_survey')
//             ->first();
  
//         if ($previousAeration) {
//             $aeration_date = DB::table('areation_date')->select('id', 'aeration_duration')->first();
//             return response()->json(['error' => true, 'message' => 'No Data', 'last_date' => $previousAeration->date_survey, 'aeration_duration' => $aeration_date], 422);
//         }
//     }
  
//     $aeration_date = DB::table('areation_date')->select('id', 'aeration_duration')->first();
//     $seasonName = \App\Models\Season::find($season)->name;
  
//     $data = [
//         'farmer_plot_uniqueid' => $aeration->farmer_plot_uniqueid,
//         'aeration_no' => $aeration->aeration_no,
//         'pipe_no' => $aeration->pipe_no,
//         'last_date' => $aeration->date_survey,
//         'aeration_duration' => $aeration_date,
//     ];
  
//     return response()->json(['success' => true, 'message' => 'Aeration ' . $aerationNo . ' Already Filled in this '. $seasonName . ' season and' . $financialYear. ' Year.', 'aeration' => $data], 423);
  
