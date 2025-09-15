<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\FarmerCropdata;
use App\Models\FarmerBenefit;
use App\Models\PipeInstallation;
use Storage;
use DB;
use App\Models\Minimumvalue;
use App\Models\Aeration;
use App\Models\PipeInstallationPipeImg;
use App\Models\AerationValidation;


class FarmerViewController extends Controller
{
     /**
     * Famrer data view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function user_form_count(){
          $FarmersApproved = DB::table('final_farmers')->where('status_onboarding','Approved')->where('onboarding_form',1)->where('surveyor_id',auth()->user()->id)->count();
          $FarmerPending   = DB::table('final_farmers')->where('status_onboarding','Pending')->where('onboarding_form',1)->where('surveyor_id',auth()->user()->id)->count();
          $FarmersReject   = DB::table('final_farmers')->where('status_onboarding','Rejected')->where('onboarding_form',1)->where('surveyor_id',auth()->user()->id)->count();

          $CropdataApproved = DB::table('farmer_cropdata')->where('surveyor_id',auth()->user()->id)->where('l2_status','Approved')->count();
          $CropdataPending  = DB::table('farmer_cropdata')->where('surveyor_id',auth()->user()->id)->where('l2_status','Pending')->count();
          $CropdataRejected = 0;//DB::table('farmers')->where('surveyor_id',auth()->user()->id)->where('onboarding_form',1)->where('status_cropdata','Rejected')->count();

          $BenefitApproved = DB::table('farmer_benefits')->where('surveyor_id',auth()->user()->id)->where('l2_status','Approved')->count();
          $BenefitPending = DB::table('farmer_benefits')->where('surveyor_id',auth()->user()->id)->where('l2_status','Pending')->count();
          $BenefitRejected = DB::table('farmer_benefits')->where('surveyor_id',auth()->user()->id)->where('l2_status','Rejected')->count();

          $approved_pipeinstallation = DB::table('pipe_installations')->where('l2_status','Approved')->where('surveyor_id',auth()->user()->id)->count();
          $pending_pipeinstallation = DB::table('pipe_installations')->where('l2_status','Pending')->where('surveyor_id',auth()->user()->id)->count();
          $reject_pipeinstallation = DB::table('pipe_installations')->where('l2_status','Rejected')->where('surveyor_id',auth()->user()->id)->count();

          $approved_polygon = DB::table('pipe_installations')->where('l2_status','Approved')->where('surveyor_id',auth()->user()->id)->count();
          $pending_polygon = DB::table('pipe_installations')->where('l2_status','Pending')->where('surveyor_id',auth()->user()->id)->count();
          $reject_polygon = DB::table('pipe_installations')->where('l2_status','Rejected')->where('surveyor_id',auth()->user()->id)->count();

          $approved_awd = DB::table('aerations')->where('surveyor_id',auth()->user()->id)->where('l2_status','Approved')->count();
          $pending_awd = DB::table('aerations')->where('surveyor_id',auth()->user()->id)->where('l2_status','Pending')->count();
          $reject_awd = DB::table('aerations')->where('surveyor_id',auth()->user()->id)->where('l2_status','Rejected')->count();

          $others = "0";//Farmer::where('status_others',1)->count();
          return response()->json(['success'=>True,'FarmersApproved'=>$FarmersApproved,'FarmerPending'=>$FarmerPending,'FarmersReject'=>$FarmersReject,
                                                   'CropdataApproved'=>$CropdataApproved,'CropdataPending'=>$CropdataPending,'CropdataRejected'=>$CropdataRejected,
                                                   'BenefitApproved'=>$BenefitApproved,'BenefitPending'=>$BenefitPending,'BenefitRejected'=>$BenefitRejected,
                                                   'appr_pipes'=>$approved_pipeinstallation,
                                                   'pending_pipes' =>$pending_pipeinstallation,
                                                   'reject_pipes' =>$reject_pipeinstallation,
                                                   'approved_awd'=>$approved_awd,
                                                   'pending_awd'=>$pending_awd,
                                                   'reject_awd'=>$reject_awd,
                                                   'other'=>0,
                                                   'approved_polygon' => $approved_polygon,
                                                   'pending_polygon' => $pending_polygon,
                                                   'reject_polygon' => $reject_polygon,
                                                ],200);
        }

     /**
     * Famrer registration data view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_registration_list(){
            $farmers = Farmer::where('surveyor_id',auth()->user()->id)->where('onboarding_form','1')->where('onboarding_form','1')
                                                                        ->select('id','surveyor_id','surveyor_name','surveyor_email','surveyor_mobile',
                                                                                                            'farmer_uniqueId','farmer_name','mobile','no_of_plots','total_plot_area',
                                                                                                            'date_survey','time_survey','created_at')->orderBy('created_at','desc')
                                                                                                            ->orderBy('updated_at','desc')
                                                                                                            ->get();
            if(!$farmers){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'farmers'=>$farmers],200);
      }

        /**
     * Famrer registration data view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_registration_list_search(){
            $farmers = Farmer::where('surveyor_id',auth()->user()->id)->where('farmer_uniqueId','like','%'.request('uniqueid').'%')->where('onboarding_form','1')
                                                                        ->select('id','surveyor_id','surveyor_name','surveyor_email','surveyor_mobile',
                                                                                                            'farmer_uniqueId','farmer_name','mobile','no_of_plots','total_plot_area',
                                                                                                            'date_survey','time_survey')->get();
            if(!$farmers){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'farmers'=>$farmers],200);
      }

      /**
     * Famrer registration detail view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_registration_detail(Request $request){
            $Farmers =  Farmer::where('surveyor_id',auth()->user()->id)->where('farmer_uniqueId', $request->farmer_uniqueId)->where('onboarding_form','1')
                                ->select('id','surveyor_id','surveyor_name','surveyor_email','surveyor_mobile','farmer_uniqueId','farmer_name','no_of_plots','total_plot_area','mobile_access',
                                    'mobile_reln_owner','mobile','country','state_id','state','district','taluka','panchayat','village','latitude','longitude','date_survey','time_survey','remarks','status_onboarding')
                                ->with(['FarmerPlot:farmer_id,farmer_uniqueId,plot_no,area_in_acers,land_ownership,actual_owner_name,survey_no,status,reject_comment,reject_timestamp,check_update'])->first();
            $farmerplotimg =  DB::table('farmer_land_img')->where('farmer_id',$Farmers->id)->where('farmer_unique_id',$request->farmer_uniqueId)->select('farmer_id','farmer_unique_id','plot_no','path')->get();
           
            if (version_compare(phpversion(), '7.1', '>=')) {
                    ini_set( 'precision', 17 );
                    ini_set( 'serialize_precision', -1 );
                }
              $minimumvalues = Minimumvalue::select('value','state_id')->where('status',1)->where('state_id',$Farmers->state_id)->first();
            if(!$Farmers){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['Success'=>True,'Farmers'=>$Farmers,'FarmerPlotImg'=>$farmerplotimg,'Basevalue'=>$minimumvalues],200);
      }

      /**
     * Famrer data view based on status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function getOnboardingList(){
            $farmers = Farmer::where('surveyor_id',auth()->user()->id)->where('status_onboarding',request('status'))->where('onboarding_form','1')
                                                                        ->select('id','surveyor_id','surveyor_name','surveyor_email','surveyor_mobile',
                                                                                                            'farmer_uniqueId','farmer_name','mobile','no_of_plots','total_plot_area',
                                                                                                            'date_survey','time_survey')
                                                                                                            ->orderBy('updated_at','desc')
                                                                                                            ->get();
            if(!$farmers){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'farmers'=>$farmers],200);
      }

     /**
     * Famrer registration data view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_cropdata_list(){
            $CropData = FarmerCropdata::where('surveyor_id',auth()->user()->id)
                                                ->select('farmer_id','farmer_uniqueId','farmer_plot_uniqueid','plot_no','area_in_acers','season','crop_variety','dt_irrigation_last','dt_ploughing','dt_transplanting'
                                                    ,'surveyor_id','surveyor_name','updated_at')
                                                ->with(['farmerapproved'=>function($q){
                                                    $q->where('cropdata_form',1)->select('id','farmer_uniqueId','no_of_plots','total_plot_area');}])
                                                    ->orderBy('updated_at','desc')
                                                    ->get();
            if(!$CropData){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'CropData'=>$CropData],200);

      }

       /**
     * Famrer registration data view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_cropdata_list_search(){
            $CropData = FarmerCropdata::where('surveyor_id',auth()->user()->id)->where('farmer_uniqueId','like','%'.request('uniqueid').'%')->where('cropdata_form','1')
                                                ->select('farmer_id','farmer_uniqueId','farmer_plot_uniqueid','plot_no','area_in_acers','season','crop_variety','dt_irrigation_last','dt_ploughing','dt_transplanting'
                                                    ,'surveyor_id','surveyor_name')
                                                ->with(['farmerapproved'=>function($q){
                                                    $q->where('cropdata_form',1)->select('id','farmer_uniqueId','no_of_plots','total_plot_area');}])
                                                    ->orderBy('updated_at','desc')
                                                    ->get();
            if(!$CropData){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'CropData'=>$CropData],200);

      }


       /**
     * Famrer registration detail view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_cropdata_detail(Request $request){
            $CropDataDetail =   FarmerCropdata::where('surveyor_id',auth()->user()->id)->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)
                                                ->select('farmer_id','farmer_uniqueId','farmer_plot_uniqueid','plot_no','area_in_acers','season','crop_variety','dt_irrigation_last','dt_ploughing','dt_transplanting'
                                                    ,'surveyor_id','surveyor_name')->first();
            if(!$CropDataDetail){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['Success'=>True,'CropData'=>$CropDataDetail],200);
      }


        /**
     * Famrer data view based on status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function getCropdataList(){
            $CropData  = Farmer::where('surveyor_id',auth()->user()->id)->where('status_cropdata',request('status'))->select('id','farmer_uniqueId','no_of_plots','total_plot_area')
                                                                ->where('cropdata_form','1')
                                                                ->with('CropData:farmer_id,farmer_uniqueId,plot_no,area_in_acers,season,crop_variety,dt_irrigation_last,dt_ploughing,dt_transplanting')
                                                                ->orderBy('updated_at','desc')
                                                                ->get();
            if(!$CropData){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'CropData'=>$CropData],200);
      }


      /**
     * Famrer registration data view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_benefit_list(){
            $BenefitData = FarmerBenefit::where('surveyor_id',auth()->user()->id)->where('benefit_form','1')
                                                ->select('farmer_id','farmer_uniqueId','total_plot_area','seasons','benefit_id','benefit','surveyor_id','surveyor_name')
                                                ->with(['farmer'=>function($q){
                                                    $q->where('benefit_form',1)->select('id','farmer_uniqueId','no_of_plots','total_plot_area');}])
                                                    ->orderBy('updated_at','desc')
                                                    ->get();
            if(!$BenefitData){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'Benefit'=>$BenefitData],200);
      }

        /**
     * Famrer registration data view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
      public function farmer_benefit_list_search(){
            $BenefitData = FarmerBenefit::where('surveyor_id',auth()->user()->id)->where('farmer_uniqueId','like','%'.request('uniqueid').'%')->where('benefit_form','1')
                                                ->select('farmer_id','farmer_uniqueId','total_plot_area','seasons','benefit_id','benefit','surveyor_id','surveyor_name')
                                                ->with(['farmer'=>function($q){
                                                    $q->where('benefit_form',1)->select('id','farmer_uniqueId','no_of_plots','total_plot_area');}])->get();
            if(!$BenefitData){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'Benefit'=>$BenefitData],200);
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
                                                                ->orderBy('updated_at','desc')
                                                                ->get();
            if(!$benefitData){
                return response()->json(['error'=>True,'Message'=>'No data'],422);
            }
            return response()->json(['success'=>True,'Benefits'=>$benefitData],200);
      }

     /**
     * Get list of aeration list
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function aeration_list(){
        $aeration  = Aeration::where('surveyor_id',auth()->user()->id)->where('l2_status','Rejected')
                                    ->select('id','pipe_installation_id','farmer_uniqueId','farmer_plot_uniqueid','plot_no','aeration_no','pipe_no','date_survey','time_survey','reason_id')
                                    ->with('farmerapproved:farmer_uniqueId,farmer_plot_uniqueid,farmer_name,surveyor_id,surveyor_name',
                                    'reject_reason:id,reasons')
                                    ->orderBy('updated_at','desc')
                                    ->get();
        if(!$aeration){
            return response()->json(['error'=>True,'Message'=>'No data'],422);
        }
        return response()->json(['success'=>True,'aeration'=>$aeration],200);
  }

   /**
     * Get aeration detail
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function aeration_reject_detail(Request $request){
        $reject_detail = AerationValidation::where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('aeration_no', $request->aeration_no)->latest()->first();
        if(!$reject_detail){
            return response()->json(['error'=>True,'Message'=>'No data'],422);
        }
        return response()->json(['success'=>True,'detail'=>$reject_detail],200);
  }

  /**
     * Get list of aeration list
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function pipe_installtion_list(){        
        $pip_intallation  = PipeInstallationPipeImg::where('surveyor_id',auth()->user()->id)->where('l2status','Rejected')->where('l2trash',0)
                                    ->select('id','farmer_uniqueId','farmer_plot_uniqueid','plot_no','created_at','lat','lng','pipe_no','distance','reason_id')
                                    ->with('farmerapproved:farmer_uniqueId,farmer_plot_uniqueid,farmer_name,surveyor_id,surveyor_name',
                                            'reject_reason:id,reasons',
                                            'reject_validation_detail:id,farmer_plot_uniqueid,status,comment',
                                            'pipeinstallation:farmer_uniqueId,farmer_plot_uniqueid,area_in_acers')
                                    ->orderBy('updated_at','desc')
                                    ->when('filter',function($q){
                                        if(request()->has('farmer_uniqueId') && !empty(request('farmer_uniqueId'))){
                                            $q->where('farmer_uniqueId','like','%'.request('farmer_uniqueId').'%');
                                        }                                        
                                        return $q;
                                    })
                                    ->get();
        if(!$pip_intallation){
            return response()->json(['error'=>True,'Message'=>'No data'],422);
        }
        return response()->json(['success'=>True,'pipe'=>$pip_intallation],200);
    }

    /**
     * Get list of rejected polygon
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function polygon_list(){        
        $polygon  = PipeInstallation::where('surveyor_id',auth()->user()->id)->where('l2_status','Rejected')->whereNotNull('reason_id')
                                    ->select('id','farmer_uniqueId','farmer_plot_uniqueid','plot_no','created_at','reason_id','area_in_acers','state','district','taluka','village')
                                    ->with('farmerapproved:farmer_uniqueId,farmer_plot_uniqueid,farmer_name,surveyor_id,surveyor_name,aadhaar,mobile',
                                            'reject_reason:id,reasons',
                                            'reject_validation_detaill2:id,farmer_plot_uniqueid,status,comment')
                                    ->orderBy('updated_at','desc')
                                    ->get();
        if(!$polygon){
            return response()->json(['error'=>True,'Message'=>'No data'],422);
        }
        return response()->json(['success'=>True,'polygon'=>$polygon],200);
    }


    /**
     * Get pipe polygon
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function pipe_installtion_polygon(Request $request){        
        $pipe_intallation  = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->select('ranges')->first();        
        if(!$pipe_intallation){
            return response()->json(['error'=>True,'Message'=>'No data'],422);
        }
        $pipe_intallation = json_decode($pipe_intallation->ranges);
        return response()->json($pipe_intallation,200);
    }


      

}
