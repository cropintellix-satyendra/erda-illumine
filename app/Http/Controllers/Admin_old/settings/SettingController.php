<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\PipeSetting;
use App\Models\Minimumvalue;
use DB;
use Carbon\Carbon;
use App\Models\AppSettings;
use App\Models\Setting;
use App\Models\AppkeySettings;
use Storage;
use App\Models\AppDashboard;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UploadImport;


class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $minimum_values = Minimumvalue::all();
      $page_title = 'Minimum Value';
      $page_description = 'Minimum Value';
 	    $action = 'table_landownerships';
      return view('admin.settings.minimum.index',compact('minimum_values','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getminimumvalue()
    {
        if (version_compare(phpversion(), '7.1', '>=')) {
            ini_set( 'precision', 17 );
            ini_set( 'serialize_precision', -1 );
        }
      $state = State::whereCountryId(101)->where('id',request('state_id'))
                               ->where('status',1)->first();
      $minimumvalues = Minimumvalue::select('value','state_id')->where('status',1)->where('state_id',request('state_id'))->first();
      if(!$minimumvalues){
          return response()->json(['error'=>true,'message'=>'No data'],422);
      }
      return response()->json(['success'=>true,'value'=>$minimumvalues,'state'=>$state],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $States = State::orderBy('id','desc')->get();
      $action = 'form_pickers';
      $page_title = ' Create benefits';
      return view('admin.settings.minimum.create',compact('action','page_title','States'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $validatedData = $request->validate([
         'value' => 'required',
         'status' => 'required',
       ]);
       $Minimumvalue =Minimumvalue::select('state_id')->where('state_id',$request->state)->first();
        //   if($Minimumvalue){
        //     return redirect()->back()->with('error', 'State already exists');
        //   }
      $value = new Minimumvalue;
      $value->value = $request->value;
      $value->state_id = $request->state;
      $value->status = $request->status;
      $value->save();
      if(!$value){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->back()->with('success', 'Saved Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $States = State::orderBy('id','desc')->get();
        $value = Minimumvalue::find($id);
        $action = 'form_pickers';
        $page_title = ' Edit Value';
        return view('admin.settings.minimum.edit',compact('action','value','page_title','States'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $validatedData = $request->validate([
         'value' => 'required',
         'state' => 'required',
         'status' => 'required',
       ]);
      $value = Minimumvalue::find($id);
      $value->value = $request->value;
      $value->state_id = $request->state;
      $value->status = $request->status;
      $value->save();
      if(!$value){
        return redirect()->back()->with('error', 'Something went wrong');
      }
       return redirect()->back()->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
      try {
            $Minimumvalue =Minimumvalue::destroy($request->id);
            if(!$Minimumvalue){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function main_settings()
    {
      //this process is used for app purpose while PipeInstallation to maintain value for
      $settings = DB::table('pipe_settings')->get();
 	    $action = 'table_landownerships';
      $page_title = 'Main Settings';
      $page_description = 'Main setting';
      return view('admin.settings.main.index',compact('settings','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pipe_setting_edit($id)
    {
      $settings = DB::table('pipe_settings')->where('id',$id)->first();
      $page_title = 'Main Settings';
      $action = 'form_pickers';
      $page_title = ' Edit Settings';
      $page_description = 'Main setting';
      return view('admin.settings.main.edit',compact('settings','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pipe_setting_update(Request $request, $id)
    {
      $settings = DB::table('pipe_settings')->where('id',$id)->update([
                                                      'unit'  =>  $request->unit,
                                                      'area'  =>  $request->area,
                                                      'no_of_pipe'  =>  $request->no_of_pipe,
                                                      'type'  =>  $request->type,
                                                      'status'  =>  $request->status,
                                                  ]);
      if(!$settings){
        return redirect()->back()->with('error', 'Something went wrong');
      }
       return redirect()->back()->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pipe_setting_delete(Request $request)
    {
       try {
            $settings =PipeSetting::destroy($request->id);
            if(!$settings){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function app_setting()
    {
      $app_settings = DB::table('app_versions')->get();
      $page_title = 'App Settings';
      $page_description = 'App setting';
 	    $action = 'table_landownerships';
      return view('admin.settings.appsetting.index',compact('app_settings','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function app_setting_edit($id)
    {
      $app_settings = DB::table('app_versions')->where('id',$id)->first();
      $page_title = 'App Settings';
      $action = 'form_pickers';
      $page_title = 'Edit App Settings';
      $page_description = 'Edit App setting';
      return view('admin.settings.appsetting.edit',compact('app_settings','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function app_setting_update(Request $request, $id)
    {
      $app_settings = AppSettings::where('id',$id)->first();
      $app_settings->version  =  $request->version;
      $app_settings->path  =  $request->apk;
      // if ($request->hasFile('apk')) {
      //     $fileName = $request->version.'_'.$request->apk->getClientOriginalName();
      //     if($app_settings->path) {
      //         Storage::disk('public')->delete($app_settings->path);
      //     }
      //     $app_settings->path  = $request->apk->storeAs('apk', config('storagesystems.appfilename').Carbon::now()->toDateTimeString().'_'.$request->file('apk')->getClientOriginalName(), 'public');
      // }
      $app_settings->status  =  $request->status;
      $app_settings->updated_at  =  carbon::now();
      $app_settings->save();
      if(!$app_settings){
        return redirect()->back()->with('error', 'Something went wrong');
      }
       return redirect()->back()->with('success', 'Saved Successfully');
    }

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function app_setting_delete(Request $request)
    {
       try {
            $appsettings =PipeSetting::destroy($request->id);
            if(!$appsettings){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function keys_update_list()
    {
      $sms_key = DB::table('app_settings')->where('title','sms_key')->first();
      $page_title = 'App Settings';
      $page_description = 'App setting';
 	    $action = 'table_landownerships';
      return view('admin.settings.appsetting.keys',compact('sms_key','page_title', 'page_description','action'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function keys_update(Request $request)
    {
      if($request->has('sms_key')){
        $sms_key = AppkeySettings::where('title', 'sms_key')->first();
        $sms_key->value   = $request->sms_key;
        $sms_key->save();
      }
      return redirect()->back()->with('success', 'Saved');
    }


     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cropdata_settings()
    {
      $cropdata = DB::table('settings')->first();
      $page_title = 'Crop Data Settings';
      $page_description = 'Crop Data setting';
 	  $action = 'table_landownerships';
      return view('admin.settings.appsetting.cropdata-settings',compact('cropdata','page_title', 'page_description','action'));
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cropdata_setting_update(Request $request, $id)
    {
      $cropdata_settings = Setting::where('id',1)->first();
      $cropdata_settings->preparation_date_interval      =  $request->preparation_date_interval;
      $cropdata_settings->transplantation_date_interval  =  $request->transplantation_date_interval;
      $cropdata_settings->updated_at  =  carbon::now();
      $cropdata_settings->save();
      if(!$cropdata_settings){
        return redirect()->back()->with('error', 'Something went wrong');
      }
       return redirect()->back()->with('success', 'Saved Successfully');
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pipe_threshold_settings()
    {
      $pipe_threshold = DB::table('settings')->first();
      $page_title = 'Pipe Threshold Settings';
      $page_description = 'Pipe Threshold Settings';
      $action = 'table_landownerships';
      return view('admin.settings.appsetting.pipe-settings',compact('pipe_threshold','page_title', 'page_description','action'));
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pipe_threshold_setting_update(Request $request, $id)
    {
      $pipe_threshold_settings = Setting::where('id',1)->first();
      $pipe_threshold_settings->threshold_pipe_installation      =  $request->threshold_pipe_installation;
      $pipe_threshold_settings->save();
      if(!$pipe_threshold_settings){
        return redirect()->back()->with('error', 'Something went wrong');
      }
       return redirect()->back()->with('success', 'Saved Successfully');
    }


     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function app_dashboard()
    {
      $app_dashboard = DB::table('settings')->first();
      $states = DB::table('states')->get();
      $page_title = 'App Dashboard Settings';
      $action = 'form_pickers';
      $page_title = 'App Dashboard Settings';
      $page_description = 'App Dashboard setting';
      return view('admin.settings.appdashboard.edit',compact('page_title', 'page_description','action','app_dashboard','states'));
    }
    
    
     /**
     * Checking enabled or disabled dashboard
     *
     * @return \Illuminate\Http\Response
     */
    public function check_state_dashboard($state_id)
    {
        $app_settings = DB::table('app_dashboards')->where('state_id',$state_id)->first();
        return response()->json(['success'=>true,
                            "state_id"                  => $app_settings->state_id,
                            "farmer_registration"       => $app_settings->farmer_registration,
                            "crop_data"                 => $app_settings->crop_data,
                            "pipe_installation"         => $app_settings->pipe_installation,
                            "polygon"                   => $app_settings->polygon,
                            "capture_aeration"          => $app_settings->capture_aeration,
                            "farmer_benefit"            => $app_settings->farmer_benefit,
                    ],200);
    }
    

    /**
     * Dashboard setting update.
     *
     * @return \Illuminate\Http\Response
     */
    public function app_dashboard_update(Request $request)
    {
        $app_dashboard = AppDashboard::whereStateId($request->state_id)->first();
        if(!$app_dashboard){
            $state = State::where('id',$request->state_id)->first();
            $app_dashboard = new AppDashboard;//this can be used for new state
            $app_dashboard->state  = $state->name;
            $app_dashboard->state_id  = $request->state_id; 
        }
        if($request->name  == 'farmer_registration'){
            if($request->value == 1){
                $app_dashboard->farmer_registration  = 0;
            }else{
                $app_dashboard->farmer_registration  = 1;
            }
        }
        if($request->name  == 'crop_data'){
            if($request->value == 1){
                $app_dashboard->crop_data  = 0;
            }else{
                $app_dashboard->crop_data  = 1;
            }
        }
        if($request->name  == 'pipe_installation'){
            if($request->value == 1){
                $app_dashboard->pipe_installation  = 0;
            }else{
                $app_dashboard->pipe_installation  = 1;
            }
        }
        if($request->name  == 'polygon'){
          if($request->value == 1){
              $app_dashboard->polygon  = 0;
          }else{
              $app_dashboard->polygon  = 1;
          }
      }
        if($request->name  == 'capture_aeration'){
            if($request->value == 1){
                $app_dashboard->capture_aeration  = 0;
            }else{
                $app_dashboard->capture_aeration  = 1;
            }
        }
        if($request->name  == 'farmer_benefit'){
            if($request->value == 1){
                $app_dashboard->farmer_benefit  = 0;
            }else{
                $app_dashboard->farmer_benefit  = 1;
            }
        }
        $app_dashboard->updated_at  = Carbon::now();
        $app_dashboard->save();
        if(!$app_dashboard){
            return response()->json(['error'=>true,'message'=>'Failed'],500);
        }
        return response()->json(['success'=>true,'message'=>'Updated Successfully'],200);
    }

    /**
     * Dashboard setting api.
     *
     * @return \Illuminate\Http\Response
     */
    public function app_dashboard_api(Request $request)
    {
        $app_settings = DB::table('app_dashboards')->where('state_id',$request->state_id)->first();
        return response()->json(['success'=>true,
                            "state_id" => $app_settings->state_id,
                            "farmer_registration" => $app_settings->farmer_registration,
                            "crop_data" => $app_settings->crop_data,
                            "pipe_installation" => $app_settings->pipe_installation,
                            "polygon" => $app_settings->polygon,
                            "capture_aeration" => $app_settings->capture_aeration,
                            "farmer_benefit" => $app_settings->farmer_benefit,
                    ],200);
        // dd($app_settings);
        // $appsettings = DB::table('settings')->where('id',1)->first();
        // $farmer_registration = DB::table('app_dashboards')->where('id',1)->first();
        // $crop_data = DB::table('app_dashboards')->where('id',2)->first();
        // $pipe_installation = DB::table('app_dashboards')->where('id',3)->first();
        // $capture_aeration = DB::table('app_dashboards')->where('id',4)->first();
        // $farmer_benefit = DB::table('app_dashboards')->where('id',5)->first();
        
        // return response()->json([
            
        //             "farmer_registration" => $farmer_registration->status,
        //             "farmer_registration_state" => explode(',',$farmer_registration->state),
        //             "farmer_registration_state_id" => explode(',',$farmer_registration->state_id),
                    
        //             "crop_data" => $crop_data->status,
        //             "crop_data_state" => explode(',',$crop_data->state),
        //             "crop_data_state_id" => explode(',',$crop_data->state_id),
                    
        //             "pipe_installation" => $pipe_installation->status,
        //             "pipe_installation_state" => explode(',',$pipe_installation->state),
        //             "pipe_installation_state_id" => explode(',',$pipe_installation->state_id),
                    
        //             "capture_aeration" => $capture_aeration->status,
        //             "capture_aeration_state" => explode(',',$capture_aeration->state),
        //             "capture_aeration_state_id" => explode(',',$capture_aeration->state_id),
                    
        //             "farmer_benefit" => $farmer_benefit->status,
        //             "farmer_benefit_state" => explode(',',$farmer_benefit->state),
        //             "farmer_benefit_state_id" => explode(',',$farmer_benefit->state_id),
        //     ],200);
        
        
        
        // foreach($appsettings as $sett){
        //     $sett->state = explode(',',$sett->state);
        //     $sett->state_id = explode(',',$sett->state_id);
        // }
        // return response()->json($appsettings);
    }
}
