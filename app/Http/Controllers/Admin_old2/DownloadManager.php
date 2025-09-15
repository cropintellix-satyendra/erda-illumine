<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 

class DownloadManager extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->ajax()){
            if(auth()->user()->roles->first()->name == 'SuperAdmin'){
               $jobs=\DB::table('temp_jobs')->whereIn('queue',['excel','geojson'])->orderBy('id','desc');//->paginate($per_page);
            }else{
                $jobs=\DB::table('temp_jobs')->whereIn('queue',['excel','geojson'])->where('user_id',auth()->user()->id)->orderBy('id','desc');//->paginate($per_page);
            }


            return datatables()->of($jobs)->addColumn('filename',function($data){
                $data=json_decode($data->payload);
                return $data->data->filename;
            })->addColumn('rolename',function($job){
                $data=json_decode($job->payload);
                return $data->data->parameters[1]->rolename??"NA";
            })->addColumn('status',function($job){
                if($job->queue == 'geojson'){
                     $data=json_decode($job->payload);
                    $status='pending';
                    if($job->attempts==2){
                        $status='processing';
                    }
                    if($job->attempts==1 && $job->reserved_at && file_exists(base_path('public/geojson/'.$data->data->filename))){
                        $status='Available';
                    }
                    return $status;
                }elseif($job->queue == 'excel'){
                    $data=json_decode($job->payload);
                    $status='pending';
                    if($job->attempts==2){
                        $status='processing';
                    }
                    if($job->attempts==1 && $job->reserved_at && file_exists(base_path('public/excel/'.$data->data->filename))){
                        $status='Available';
                    }
                    return $status;
                }
                
            })->addColumn('action',function($job){
                if($job->queue == 'geojson'){
                    $data=json_decode($job->payload);
                    if($job->attempts == 2){
                        $a_tag =  '';
                    }else{
                        $a_tag = '<a target="_blank" href="'.asset('geojson/'.$data->data->filename).'" class="btn"><i class="fa fa-download f-16 mr-15 text-blue"></i></a>';
                    }
                    return '<div class="table-actions text-end '.($job->attempts==0?'d-none':'').'">
                                    '.$a_tag.'
                                    <a class="btn btn-delete" data-id="'.$job->id.'"><i class="fa fa-trash f-16 text-red"></i></a>
                                </div>';
                }elseif($job->queue == 'excel'){
                    $data=json_decode($job->payload);
                    if($job->attempts == 2){
                        $a_tag =  '';
                    }else{
                        $a_tag = '<a target="_blank" href="'.asset('excel/'.$data->data->filename).'" class="btn"><i class="fa fa-download f-16 mr-15 text-blue"></i></a>';
                    }
                    return '<div class="table-actions text-end '.($job->attempts==0?'d-none':'').'">
                                    '.$a_tag.'
                                    <a class="btn btn-delete" data-id="'.$job->id.'"><i class="fa fa-trash f-16 text-red"></i></a>
                                </div>';
                    }      
            })->make(true);
        }
        $action = 'form_pickers';
        $page_title = 'Excel Download';
        return view('admin.download.index',compact('action','page_title'));
        /*set_time_limit(-1);
        ini_set('memory_limit', '64000M');
        $jobs=\DB::table('jobs')->where('queue','excel')->where('attempts',0)->get();
        if($jobs->count()>0){
            foreach($jobs as $job){
                $payload=json_decode($job->payload);
                //dd($payload);
                $command=new $payload->data->command($payload->data->parameters[0],$payload->data->parameters[1]);
                $excel=\Maatwebsite\Excel\Facades\Excel::store(new $payload->data->command($payload->data->parameters[0],(object)$payload->data->parameters[1]), $payload->data->filename,$payload->data->drive);
                if(!$excel){

                }
                $update=\DB::table('jobs')->where('id',$job->id)->update([
                        'attempts'=>1,
                        'reserved_at'=>\Carbon\Carbon::now()->timestamp
                    ]);
            }
        }*/
        /*$filename = 'Farmers-'.\Carbon\Carbon::now().'.xlsx';
        //(new \App\Exports\NewApprovedPlotExport)->store($filename);
        \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\NewApprovedPlotExport,$filename);
        \Artisan::call('queue:work');
         return response()->json([
            'success'=>true,
            'message'=>'Export Started'
          ]);*/

           // return '<div class="table-actions text-end '.($job->attempts==0?'d-none':'').'">
           //                      <a target="_blank" href="'.asset('public/excel/'.$data->data->filename).'" class="btn"><i class="fa fa-download f-16 mr-15 text-blue"></i></a>
           //                      <a class="btn btn-delete" data-id="'.$job->id.'"><i class="fa fa-trash f-16 text-red"></i></a>
           //                  </div>';


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $job=\DB::table('temp_jobs')->where('id',$request->id)->first();
        $data=json_decode($job->payload);
        if(file_exists(base_path('public/excel/'.$data->data->filename))){
            // unlink(base_path('public/excel/'.$data->data->filename));
            // dd(base_path('public/excel/'.$data->data->filename));
            // dd(public_path($data->data->filename));
            // Storage::disk('excel')->delete("{$data->data->filename}");
            // File::delete(base_path('public/excel/'.$data->data->filename));
             File::delete(base_path('public/excel/'.$data->data->filename));
        }
        $job=\DB::table('temp_jobs')->where('id',$request->id)->delete();//delete from db
        return response()->json([
                'success'=>true,
                'message'=>'File(s) removed successfully'
            ]);
    }
}
