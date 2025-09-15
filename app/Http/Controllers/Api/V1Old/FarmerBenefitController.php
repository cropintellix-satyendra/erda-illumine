<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerImage;
use App\Models\FarmerPlotImage;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Models\Season;
use App\Models\Benefit;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Storage;
use Carbon\Carbon;
use DB;
use App\Models\FinalFarmer;
use App\Models\FarmerCropdata;
use App\Models\PlotStatusRecord;

class FarmerBenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    * Farmer store benefit images
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public function fetch_benefit_detail(Request $request){
        try{
            //api for benefit data to app
            $farmer = FinalFarmer::select('id','farmer_name','mobile','no_of_plots')
                      ->where('farmer_uniqueId',$request->farmer_uniqueId)->first();
                      
            $cropdata = FarmerCropdata::where('farmer_uniqueId',$request->farmer_uniqueId)->get();
            if(!$cropdata->count() > 0){
                return response()->json(['error'=>true,'farmer'=>$farmer,'Status'=>'0'],422);
            }
            $benefitfetchdetail = FarmerBenefit::select('farmer_id','farmer_uniqueId','seasons','benefit')
                                      ->where('farmer_uniqueId',$request->farmer_uniqueId)->first();
            if($benefitfetchdetail){
                return response()->json(['success'=>true,'farmer'=>$farmer ,'benefitdetail'=>$benefitfetchdetail,'Status'=>'1'],200);
            }else{
                return response()->json(['error'=>true,'farmer'=>$farmer ,'benefitdetail'=>'No data','Status'=>'1'],422);
            }

        }catch(Exception $e){
            return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
        }
    }

    /**
     * Check if benefit data store
     *
     * @return \Illuminate\Http\Response
     */
    public function fetch_benefit_check(Request $request)
    {
        //api to check plot data is already stored or not
        $farmer_benefit_id = FarmerBenefit::where('farmer_uniqueId',$request->farmer_uniqueId)
                            ->where('benefit_id',$request->benefit_id)->first();
        if($farmer_benefit_id){
            return response()->json(['error'=>true, 'message' => 'Benefit provided to farmer'],422);
        }
        return response()->json(['success'=>true, 'message' => 'No data'],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(),[
          'farmer_id' => 'required',
          'farmer_uniqueId' => 'required',
          'seasons' => 'required',
          'benefit' => 'required',
      ]);
      try{
        $farmer_benefit_id = FarmerBenefit::where('farmer_uniqueId',$request->farmer_uniqueId)->where('benefit_id',$request->benefit_id)->first();
        if($farmer_benefit_id){
            return response()->json(['error'=>true, 'message' => 'Benefit provided to farmer'],422);
        }//before store checking one more time plot
        $farmer_benefit = new FarmerBenefit;
        $farmer_benefit->farmer_id        =  $request->farmer_id;
        $farmer_benefit->farmer_uniqueId  =  $request->farmer_uniqueId;
        // any user can store data of farmer benefit so using auth()
        $farmer_benefit->surveyor_id      =  auth()->user()->id;
        $farmer_benefit->surveyor_name    =  auth()->user()->name;
        $farmer_benefit->surveyor_mobile  =  auth()->user()->mobile;
        $farmer_benefit->seasons          =  $request->seasons;
        $farmer_benefit->benefit_id       =  $request->benefit_id;
        $farmer_benefit->benefit          =  $request->benefit;
        $farmer_benefit->total_plot_area  =  $request->total_plot_area; //area_in_acers need to replace
        $farmer_benefit->status             = 'Approved';
        $farmer_benefit->apprv_reject_user_id             = '1';
        $farmer_benefit->date_survey        = Carbon::parse(Carbon::now())->format('d/m/Y');
        $farmer_benefit->date_time          = Carbon::now()->toTimeString();
        $farmer_benefit->save();

        $record =  PlotStatusRecord::create([
                 'farmer_uniqueId'           => $request->farmer_uniqueId,
                 'plot_no'                   => NULL,//$request->plot_no,
                 'farmer_plot_uniqueid'      => NULL,$request->farmer_plot_uniqueid,
                 'level'                     => 'Benefit',
                 'status'                    => 'Approved',
                 'comment'                   => 'Uploaded Benefit Data',
                 'timestamp'                 => Carbon::now(),
                 'user_id'                   => auth()->user()->id,
             ]);


        FinalFarmer::where('farmer_uniqueId',$request->farmer_uniqueId)->update(['benefit_form'=>1]);//first stage it is on farmer benefits
        if(!$farmer_benefit){
          return response()->json(['error'=>true, 'message' => 'Something went wrong'],500);
        }
        return response()->json(['success'=>true, 'message' => 'Saved Succefully','farmer_benefit_id'=>$farmer_benefit->id],200);
      }catch(Exception $e){
        return response()->json(['error'=>true, 'message' => 'Something went wrong'],500);
      }
    }

    /**
    * Farmer store benefit images
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public function benefit_image(Request $request){
        try{
            $img = new FarmerBenefitImage;
            $img->farmer_id        = $request->farmer_id;
            $img->farmer_uniqueId  = $request->farmer_uniqueId;
            $img->farmer_benefit_id  = $request->farmer_benefit_id;
            $path = Storage::disk('s3')->put(config('storagesystems.path').'/'.$request->farmer_uniqueId.'/'.$request->farmer_benefit_id.'/BENEFIT', $request->image);
            $path = Storage::disk('s3')->url($path);
            $img->path             = $path;
            $img->save();
            
            if(!$img){
             return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
            }
           return response()->json(['success'=>true,'farmerId'=>$request->farmer_id,'farmerUniqueId'=>$request->farmer_uniqueId, 'farmer_benefit_id'=> $request->farmer_benefit_id],200);
         }catch(Exception $e){
           return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
         }
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
    public function destroy($id)
    {
        //
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSeasons()
    {
        $seasons = Season::select('id','name')->where('status',1)->get();
        return response()->json(['success'=>true, 'seasons'=>$seasons],200);
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBenefits()
    {
        $benefits = Benefit::select('id','name')->where('status',1)->get();
        return response()->json(['success'=>true, 'benefits'=>$benefits],200);
    }


}
