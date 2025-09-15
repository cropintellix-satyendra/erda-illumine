<?php

namespace App\Http\Controllers\Admin\Apprvfarmer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Models\FarmerPlotImage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Setting;
use App\Models\VendorLocation;
use DB;
use App\Models\RejectModule;
use App\Models\Aeration;
use App\Models\ViewerLocation;
use App\Models\Minimumvalue;
use App\Models\FinalFarmer;
use App\Models\PipeInstallation;
use App\Models\FarmerCropdata;
use App\Models\FinalFarmerPlotImage;
use App\Models\AerationImage;
use App\Exports\PipeInstallationExport;
use App\Exports\L2ApprovedIndividualExport;
use App\Exports\L2PendingIndividualExport;
use App\Exports\L2RejectedIndividualExport;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallationPipeImg;

class FarmerApprovController extends Controller
{
   /**
   * Search list of farmer.
   *
   * @return \Illuminate\Http\Response
   */
  public function searchlist()
  {
      if(request()->has('query') && !empty(request()->query)){//for search box in show page
        $Farmers = FinalFarmer::where('onboarding_form','1')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){

        });
        return response()->json($Farmers->get());
      }//end for search box in show page
  }

   /**
   * Search list of farmer.
   *
   * @return \Illuminate\Http\Response
   */
  public function search_pending_reject($status,$final_status)
  {
     if(request()->has('query') && !empty(request()->query)){//for search box in show page
        // $Farmers = Farmer::where('onboarding_form','1')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){
        $Farmers =  FarmerPlot::with('farmer')->whereHas('farmer',function($q) use($status,$final_status){
            $q->where('onboarding_form','1');
            if($status){
      		      if(auth()->user()->hasRole('L-2-Validator')){
      		           $q->where('final_status',$final_status);
      		           if($status == 'Rejected'){
      		               $q->where('status',$status);
      		           }else{
      		               $q->where('status',$status);
      		           }
      		      }else{
      		          $q->where('final_status',$final_status);
      		          $q->where('status',$status);
      		      }
      		  }
        });
        return response()->json($Farmers->get());
      }//end for search box in show page
  }




  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    if(auth()->user()->cannot('farmer')) abort(403, 'User does not have the right roles.');
	  //Plot list
	  if(request()->ajax() && request()->has('layout') && request()->layout=='approvedfarm'){
  		$plots= FinalFarmer::with('ApprvFarmerPlot')->when(request(),function($q){
        $q->where('onboarding_form','1');
        if(request('module') == 'CropData'){//when plot has cropdata
            $q->whereHas('PlotCropData');
        }
        if(request('module') == 'Benefit'){//when plot has benefit
            $q->whereHas('BenefitsData');
        }
        if(auth()->user()->hasRole('Viewer')){
            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
            $q->whereIn('state_id',explode(',',$viewerlocation->state));
        }//end of viewer

        if(request('module') == 'PipeInstalltion'){//when plot has pipeinstalltion
            $q->whereHas('PlotPipeData');
        }
        if(request('module') == 'Aeration'){//when plot has aeration
            $q->whereHas('AerationData');
        }
        if(request()->has('l2_validator') && !empty(request('l2_validator'))){
            $q->where('L2_appr_userid',request('l2_validator'));
        }
        //this is here just to see how it will filter if working correct then good
  		  if(request()->has('status') && !empty(request()->status)){
  			     $q->where('final_status_onboarding',request()->status);
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
        if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
             $q->where('surveyor_id',request('executive_onboarding'));
        }
        if(request()->has('start_date') && !empty(request('start_date'))){
            $q->whereDate('date_survey','>=',request('start_date'));
        }
        if(request()->has('end_date') && !empty(request('end_date'))){
            $q->whereDate('date_survey','<=',request('end_date'));
        }
        return $q;
  		})->orderBy('id','desc');
  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot WITH AJAX

    // Onload below code excute first. And after successful load then again ajax make request to above code
	  if(request()->has('layout') && request()->layout=='approvedfarm'){
  		$page_title = 'Approved Farmers';
  		$page_description = 'List of finalised farmer plots';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
      //for admin data
      $states = DB::table('states')->where('status',1)->get();
      $districts = DB::table('districts')->where('status',1)->get();
      $talukas = DB::table('talukas')->where('status',1)->get();
      $panchayats = DB::table('panchayats')->get();
      $villages = DB::table('villages')->get();
      $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
  		$seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
      $l2_validators =   User::whereHas('roles', function($q){
        $q->whereIn('name',['L-2-Validator']);//fetch user from users table hasrole SuperValidator  L-1-Validator
         })->where('status',1)->orderBy('created_at','desc')->get();

      if(auth()->user()->hasRole('Viewer')){
          $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$ViewerLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$ViewerLocation->village))->get();
          // $onboarding_executive = DB::table('farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
            $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->when(request(),function($q){
              if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')){
                  $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                  $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                  return $q;
              }
            })->get();
      }
  		return view('admin.ApprovFarm.plot',compact('page_title','page_description','action','seasons','states',
												  'districts','talukas','panchayats','villages','onboarding_executive','status','others','l2_validators'));
	  }
	  // end Plot view without ajax
  }

  /**
   * level 2 user can get list.
   *
   * @return \Illuminate\Http\Response
   */
  public function l2_pending_plot_list()
  {
    //level 2 validator get pending plot list from this function
    if(auth()->user()->cannot('farmer')) abort(403, 'User does not have the right roles.');
	  //Plot list
	  if(request()->ajax() && request()->has('layout') && request()->layout=='l2plot'){
  		$plots= FarmerPlot::with('farmer')->whereHas('farmer',function($q){


        if(request()->status == 'Pending'){
            // dd(request()->status);
          //final_status column should be pending to displayed in l2 validator list
          $q->where('final_status','Pending');
          //because this status is for level 1 validator,
           // to be display in l2 pending list plot should approved by l1 validator
          $q->where('status','Approved');

        }elseif(request()->status == 'Rejected'){
            //   dd(request()->status);
          //final_status column should be reject to displayed in l2 validator list
          $q->where('final_status','Rejected');
           // to be display in l2 reject list plot should rejected by l2 validator
           // and this same plot also be displayed in level 1 validator reject list
          $q->where('status','Rejected');
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
        if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
             $q->where('surveyor_id',request('executive_onboarding'));
        }
        if(request()->has('start_date') && !empty(request('start_date'))){
            $q->whereDate('date_survey','>=',request('start_date'));
        }
        if(request()->has('end_date') && !empty(request('end_date'))){
            $q->whereDate('date_survey','<=',request('end_date'));
        }
        return $q;
  		})->when(request(),function($a){
            //this is to fetch data who has validated
            if(request()->has('l2_validator') && !empty(request('l2_validator'))){
              $a->where('finalreject_userid',request('l2_validator'));
            }
        })->orderBy('id','desc');

  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot WITH AJAX
    // Onload below code excute first. And after successful load then again ajax make request to above code
	  if(request()->has('layout') && request()->layout=='l2plot'){
  		$page_title = 'Pending Plot l2';
  		$page_description = 'List of pending plots';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
      //for admin data
      $states = DB::table('states')->where('status',1)->get();
      $districts = DB::table('districts')->where('status',1)->get();
      $talukas = DB::table('talukas')->where('status',1)->get();
      $panchayats = DB::table('panchayats')->get();
      $villages = DB::table('villages')->get();
      $l2_validators =   User::whereHas('roles', function($q){
        $q->whereIn('name',['L-2-Validator']);//fetch user from users table hasrole SuperValidator  L-1-Validator
         })->where('status',1)->orderBy('created_at','desc')->get();
      // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();

    //   $onboarding_executive =Farmer::with('FarmerPlot')->where('onboarding_form','1')->groupBy('surveyor_name')->select('surveyor_name')->whereHas('FarmerPlot',function($q){
    //       $q->where('final_status','Pending');
    //   })->get();

       $onboarding_executive  = DB::table('farmers')->where('onboarding_form','1')->groupBy('farmers.surveyor_name')
                        ->join('farmer_plot_detail', 'farmers.id' ,'=','farmer_plot_detail.farmer_id')
                        ->where('farmer_plot_detail.final_status', '=','Pending')
                        ->get();


  		$seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
      if(auth()->user()->hasRole('Viewer')){
          $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$ViewerLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$ViewerLocation->village))->get();
          // $onboarding_executive = DB::table('farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
            $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->when(request(),function($q){
              if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')){
                  $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                  $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                  return $q;
              }
            })->get();
      }
  		return view('admin.ApprovFarm.L2-pending_reject-plot',compact('page_title','page_description','action','seasons','states',
												  'districts','talukas','panchayats','villages','onboarding_executive','status','others','l2_validators'));
	  }
	  // end Plot view without ajax
  }

  public function plot_pending_reject($id){
      if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
      $plot=FarmerPlot::findOrFail($id);
      if(auth()->user()->hasRole('L-1-Validator')){
        $status = $plot->status;//this is use for previous an next button
      }elseif(auth()->user()->hasRole('L-2-Validator')){
        $status = $plot->final_status;//this is use for previous an next button
      }else{
        $status = $plot->final_status;
      }
      $farmerplots_area =  FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $valicountplotapprv = FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('status','Approved')->count();
      $finalcountplotapprv = FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('final_status_onboarding','Approved')->count();
      $reject_module = RejectModule::all();
      $page_title = 'Farmer\'s Plot';
      $page_description = 'Farmer Plot Detail';
      $action = 'table_farmer';
         $guntha = 0.025000;
        //  $guntha = 0.025;

      if($plot->farmer->state_id == 36){
              $area = number_format((float)$plot->area_in_acers, 2, '.', '');
              $split = explode('.', $area);//spliting area
              $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
              $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
              $conversion = explode('.', $result); // split result
            //   dd($area, $split, $valueafterdecimal, $result, $conversion);
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
      return view('admin.ApprovFarm.plot-detail',compact('plot','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','finalcountplotapprv','status'));
    }

  //

    /**
     * Show the counting to farmer index page from ajax.
     *
     * @return \Illuminate\Http\Response
     */
    public function counting()
    {
        $farmer_count = FarmerPlot::where('final_status', 'Approved')->where('status', 'Approved')->groupBy('farmer_uniqueId')->whereHas('final_farmers',function($q){

            if(auth()->user()->hasRole('L-1-Validator')){
                $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
                $q->whereIn('state_id',explode(',',$VendorLocation->state));
                if(!empty($VendorLocation->district)){
                   $q->whereIn('district_id',explode(',',$VendorLocation->district));
                }
                if(!empty($VendorLocation->taluka)){
                   $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                }
            }
        })->count();

         $farmers_count_plot = FarmerPlot::where('final_status', 'Approved')->where('status', 'Approved')->whereHas('final_farmers',function($q){
                $q->where('onboarding_form','1');
                if(auth()->user()->hasRole('L-1-Validator')){
                    $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$VendorLocation->state));
                    if(!empty($VendorLocation->district)){
                       $q->whereIn('district_id',explode(',',$VendorLocation->district));
                    }
                    if(!empty($VendorLocation->taluka)){
                       $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                    }
                }
            return $q;
        })->count();

        $approved = FarmerPlot::where('final_status', 'Approved')->where('status', 'Approved')->whereHas('final_farmers',function($q){
            $q->where('onboarding_form','1');
                if(auth()->user()->hasRole('L-1-Validator')){
                    $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$VendorLocation->state));
                    if(!empty($VendorLocation->district)){
                       $q->whereIn('district_id',explode(',',$VendorLocation->district));
                    }
                    if(!empty($VendorLocation->taluka)){
                       $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                    }
                }
            return $q;
        })->count();

        $pendings = FarmerPlot::where('final_status', 'Pending')->where('status', 'Approved')->count();

        $rejected = FarmerPlot::where('final_status', 'Rejected')->where('finalreject_userid',auth()->user()->id)->count();
        if(auth()->user()->hasRole('SuperAdmin')){  //new method
            $rejected = FarmerPlot::where('final_status', 'Rejected')->count();
        }

        $total_plot_area = FarmerPlot::where('final_status','Approved')->when(request(),function($q){
            return $q;
        })->sum('area_acre_awd');

        $total_plot_area = number_format((float) $total_plot_area, 2);
        $others = "0";
        return response()->json(['success'=>true,'farmer_count'=>$farmer_count ,'plot_count'=>$farmers_count_plot,
                                    'approved'=>$approved,'pendings'=>$pendings,'rejected'=>$rejected,'totalarea'=>$total_plot_area,'others'=>$others],200);
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
    public function show($farmer_uniqueid)
    {
      //before using need to modify code. And shift db table connection to final_famers table for show()
      if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
      $Farmer = FinalFarmer::where('farmer_uniqueId',$farmer_uniqueid)->first();
      $farmerplots =  FinalFarmer::where('farmer_uniqueId',$farmer_uniqueid)->get();
      $Farmerplotsimages = FinalFarmerPlotImage::where('farmer_unique_id',$farmer_uniqueid)->get();
      $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$Farmer->farmer_uniqueId)->get();
      $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();
      $crop_data = FarmerCropdata::where('farmer_uniqueId',$farmer_uniqueid)->get();
      $PipeInstallation  = PipeInstallation::where('farmer_uniqueId', $farmer_uniqueid)->get();
      $PipesLocation="";
    //   if($PipeInstallation->first()->pipes_location){
    //       $PipesLocationmap = json_decode($PipeInstallation->first()->pipes_location);
    //   }
    //   $Polygonmap = json_decode($PipeInstallation->first()->ranges);
    //     $allpolygon =[];

    //   foreach($PipeInstallation as $ranges){
    //       // $allpolygon[] =  json_decode($ranges->ranges);
    //       $allpolygon[] =  $ranges->ranges;
    //   }

// dd($allpolygon);

      $awd=Aeration::where('farmer_uniqueId',$farmer_uniqueid)->get();
      $reject_module = RejectModule::all();
      $action = 'form_pickers';
      $page_title = 'Farmers Details';
      $guntha = 0.025000;
      if($Farmer->state_id == 36){
        $total_area_acres  = 0;
          foreach($farmerplots as $plot){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1]??0;
            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;
            $total_area_acres+=$acers;
          }
          $plot->total_area_acres_of_guntha = $total_area_acres;
          }
        $status_timeline = PlotStatusRecord::with('UserApprovedRejected','Reasons')->where('farmer_uniqueId',$farmer_uniqueid)->get();
      return view('admin.ApprovFarm.show',compact('Farmer', 'farmerplots', 'farmerbenefitimg','action','page_title','reject_module','crop_data',
                                                'Farmerplotsimages','PipeInstallation','awd','status_timeline'));
    }

    public function plot($id){
      if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
      $plot = FinalFarmer::with(['PlotPipeData','ApprvFarmerPlot'])->where('farmer_plot_uniqueid',$id)->first();
      $farmerplots =  FinalFarmer::where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid)->get();
      $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      
      $cropdata = FarmerCropdata::with('PlotCropDetails','farmerapproved')->where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid)->where('plot_no',$plot->plot_no)->get();
      $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$id)->first();
      $farmer = Farmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->first();
      $reject_module = RejectModule::all();
      $page_title = 'Approved Farmer\'s Plot';
      $page_description = 'Approved Farmer Plot Detail';
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
        $status_timeline = PlotStatusRecord::with('UserApprovedRejected','Reasons')->where('farmer_plot_uniqueid',$id)->get();
      return view('admin.ApprovFarm.approved-plot-detail',compact('plot','page_title','farmer','page_description','action','farmerplots','reject_module','farmerbenefitimg','check_pipedata','status_timeline','cropdata'));
    }

    public function plotEdit($id){
      if(auth()->user()->cannot('edit farmer')) abort(403, 'User does not have the right roles.');
      $plot=FinalFarmer::findOrFail($id);
      $farmerplots =  FinalFarmer::where('id',$id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $reject_module = RejectModule::all();
      $page_title = 'Approved Farmer\'s Plot';
      $page_description = 'Approved Farmer Plot Detail';
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
          }
          $Relationshipowner = DB::table('relatioshipowners')->orderBy('id','asc')->where('status',1)->get();
          $states = DB::table('states')->get();
          $districts = DB::table('districts')->get();
          $talukas = DB::table('talukas')->get();
          $panchayats = DB::table('panchayats')->get();
          $villages = DB::table('villages')->get();
          $minimumvalues = Minimumvalue::select('value','state_id')->where('status',1)->where('state_id',$plot->state_id)->first();
      return view('admin.ApprovFarm.approved-plot-detail-edit',compact('plot','page_title','page_description','action','farmerplots','reject_module',
                                  'Relationshipowner','states','districts','talukas','panchayats','villages','minimumvalues'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,$farmer_uniqueid)
    {
      if(auth()->user()->cannot('edit farmer')) abort(403, 'User does not have the right roles.');
      $Farmer = Farmer::find($id);
      $farmerplots =  FarmerPlot::where('farmer_id', $id)->where('farmer_uniqueId',$farmer_uniqueid)->get();
      $Farmerplotsimages = FarmerPlotImage::where('farmer_id', $id)->where('farmer_unique_id',$farmer_uniqueid)->get();
      $farmerplots_first = FarmerPlot::where('farmer_id', $id)->where('farmer_uniqueId',$farmer_uniqueid)->first();
      $farmerbenefit = FarmerBenefit::where('farmer_uniqueId',$farmer_uniqueid)->get();
      $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();
      $onboarding_plot = explode(',',$Farmer->status_onboarding_plot);
      $Relationshipowner = DB::table('relatioshipowners')->orderBy('id','asc')->where('status',1)->get();
      $states = DB::table('states')->get();
      $districts = DB::table('districts')->get();
      $talukas = DB::table('talukas')->get();
      $panchayats = DB::table('panchayats')->get();
      $villages = DB::table('villages')->get();
      $action = 'form_pickers';
      $page_title = 'Farmers Details';
      $minimumvalues = Minimumvalue::select('value','state_id')->where('status',1)->where('state_id',$Farmer->state_id)->first();
      return view('admin.ApprovFarm.edit',compact('Farmer', 'farmerplots', 'farmerplots_first', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot',
                                                'Relationshipowner','states','districts','talukas','panchayats','villages','minimumvalues'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {  // if(auth()->user()->cannot('edit farmer')) abort(403, 'User does not have the right roles.');
        if($request->type == 'UpdateBasicform'){
            $validatedData = $request->validate([
              'FarmerName' => 'required',
              'Mobile' => 'required',
            ]);
            $FinalFarmer = FinalFarmer::find($id);
            $FinalFarmer->farmer_name = $request->FarmerName;
            $FinalFarmer->mobile_reln_owner = $request->RelOwner??'NA';
            $FinalFarmer->mobile_access = $request->MobileAccess??'NA';
            $FinalFarmer->mobile = $request->Mobile;
            $FinalFarmer->save();
            if(!$FinalFarmer){
              return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
            }
        }elseif($request->type == 'UpdatePlot'){
            $validatedData = $request->validate([
              'area' => 'required',
              'survey' => 'required',
            ]);
            try{
              // dd($request->unique, $id);
                if(count($request->area) > 0){
                    foreach($request->area as $value){
                        $area_acers = DB::table('final_farmers')->where('id',$id)->where('plot_no', $value['PlotNo'])->update(['area_in_acers'=>$value['area'] ]);
                    }
                    $farmerplot = FinalFarmer::where('farmer_uniqueId',$request->unique)->sum('area_in_acers');
                    $updatearea = FinalFarmer::where('farmer_uniqueId',$request->unique)->update(['total_plot_area'=>number_format((float) $farmerplot, 2)]);
                }
                foreach($request->ownername as $value){
                    $area_acers = DB::table('final_farmers')->where('id',$id)->where('plot_no', $value['PlotNo'])->update(['actual_owner_name'=>$value['actual_owner_name'] ]);
                }

                foreach($request->survey as $value){
                     $plots = DB::table('final_farmers')->where('id',$id)->where('plot_no', $value['PlotNo'])->update(['survey_no'=>$value['survey']]);
                }
            }catch(Exception $e){
                return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
            }
        }elseif($request->type == 'UpdateLocation'){
                $validatedData = $request->validate([
                  'panchayat' => 'required',
                  'village' => 'required',
                ]);
            try{
                $Farmer = FinalFarmer::find($id);

                // $state = State::where('id',$request->state)->first();
                // $Farmer->state        = $state->name;
                // $Farmer->state_id     = $request->state;
                // $Farmer->country      = $state->countryname->name;
                // $Farmer->country_id   = $state->country_id;//we have countries table in India has 101 as ID
                // $district = DB::table('districts')->where('id',$request->district)->first();
                //     $Farmer->district     = $district->district;
                //     $Farmer->district_id  = $request->district;
                // $taluka = DB::table('talukas')->where('id',$request->taluka)->first();
                //     $Farmer->taluka       = $taluka->taluka;
                //     $Farmer->taluka_id    = $request->taluka;
                $Panchayat = DB::table('panchayats')->whereId($request->panchayat)->first();
                    $Farmer->panchayat       = $Panchayat->panchayat;
                    $Farmer->panchayat_id    = $request->panchayat;
                $village = DB::table('villages')->where('id',$request->village)->first();
                $Farmer->village      = $village->village;
                $Farmer->village_id   = $request->village;
                $Farmer->save();
            }catch(Exception $e){
                return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
            }
        }
        return response()->json(['success'=>true,'message'=>'Saved Successfully'],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
      if(auth()->user()->cannot('delete farmer')) abort(403, 'User does not have the right roles.');
      try {
            $Farmer =Farmer::destroy($request->id);
            if(!$Farmer){
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
     * Show the form for show the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fetch_detail(Request $request)
    {
      $detail = FarmerPlot::where('farmer_uniqueId',$request->farmerUnique)
                          ->where('plot_no',$request->plotno)
                          ->select('farmer_uniqueId','plot_no','area_in_acers','dt_irrigation_last','crop_variety','dt_ploughing',
                                'dt_transplanting', 'benefit_seasons', 'benefit')
                            ->first();
      $farmer_name  = Farmer::select('farmer_name')->find($request->farmer_id);
      return response()->json(['success'=>true, 'detail'=>$detail, 'farmername'=>$farmer_name]);
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
        if(request('type') == 'ALL'){
            $filename = 'Farmers-'.Carbon::now().'.xlsx';
            // return Excel::download(new FarmerExport(103390), $filename);
        }elseif(request('type') == 'onboarding'){
            $filename = 'L2-Approved_'.Carbon::now().'.xlsx';
            // return Excel::download(new ApprovedPlotExport(request('unique') ? request('unique') : 'All' ,request()), $filename);

            $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\ApprovedPlotExport',
                        'parameters'=>['All' ,request()->all()],
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

        }elseif(request('type') == 'cropdata'){
            $filename = 'Approved-cropdata_'.Carbon::now().'.xlsx';
            // return Excel::download(new CropdataExport('All',request()), $filename);

            $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\CropdataExport',
                        'parameters'=>['All' ,request()->all()],
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


        }elseif(request('type') == 'Aeration'){
            $filename = 'Approved-aeration_'.Carbon::now().'.xlsx';
            // return Excel::download(new AerationExport('All',request()), $filename);

            $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\AerationExport',
                        'parameters'=>['All' ,request()->all()],
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


        }elseif(request('type') == 'benefitsdata'){
            $filename = 'Farmers-benefits_'.Carbon::now().'.xlsx';
            // return Excel::download(new BenefitExport('All',request()), $filename);

            $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\BenefitExport',
                        'parameters'=>['All' ,request()->all()],
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

        }elseif(request('type') == 'PipeInstalltion'){
            $filename = 'Approved-pipe-installation_'.Carbon::now().'.xlsx';
            // return Excel::download(new PipeInstallationExport('All',request()), $filename);
            $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\PipeInstallationExport',
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

        }elseif(request('type') == 'PipeGeojson'){
            $this->pipe_installation_geojson(request());
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
              return Excel::download(new L2ApprovedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Pending'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L2PendingIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Rejected'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L2RejectedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }
       }


     /**
     * Download excel file.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function pipe_installation_geojson($request){
       \Artisan::call('cache:clear');
       if (version_compare(phpversion(), '7.1', '>=')) {
           ini_set( 'precision', 17 );
           ini_set( 'serialize_precision', -1 );
       }
       $raw = new PipeInstallationExport('All',$request);
       $json=$raw->collection();
       $features=[];
         if(count($json)>0){
           foreach($json as $items){
             $rawdata=[];
             $polygon  =  json_decode($items['ranges']);
             $polygon_act  =  json_decode($items['ranges']);
             if(count($polygon)>2){
               /*If survey has updated polygon then */
               /*for updatedpolygon no need of doing array chunk*/
               $multipolygon = json_decode($items['ranges']);
               foreach($multipolygon as $index=>$array){
                 $data = [0=> floatval($array->lng), 1 =>  floatval($array->lat)];
                 $multipolygon[$index] = $data;
               }
               //check whether first and last coordinates are matching or not
               $firstarray = $multipolygon[0];
               $lastarray = $multipolygon[array_key_last($multipolygon)];
               if($firstarray != $lastarray){
                   $multipolygon[]=$multipolygon[0];
               }
               $multipolygonreverse = [];
               $rawdata[] = [$multipolygon]; //here adding square bracs to match geojson format for multipolygon coordinates need to be deeply nested
             }//end check polygon count
             $features[]=[
                     "type"=> "Feature",
                     "geometry"=>[
                             "type"=>"MultiPolygon",
                             "coordinates"=>$rawdata,
                         ],
                     'properties'=>$items
                 ];
           }//forach end
         }//if json end
         $collection=[
                 "type"=> "FeatureCollection",
                 "features"=>$features
             ];
        $collection=collect($collection)->toJson();
        $filename = 'pipe-installation_geojson_'.time().'.geojson';
        $path = base_path('public/geojson/').$filename;
        $handle = fopen($path, 'w+');
        fputs($handle, $collection);
        fclose($handle);
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
        readfile(base_path('public/geojson/'.$filename));
     }

    /**
      * Download leased or carbon credit pdf.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
      public function download($UniqueId,$type,$plotno){
        if($type == 'LEASED'){
          $Farmer = FinalFarmer::where('farmer_uniqueId',$UniqueId)->first();
          $terms_and_conditions =  Setting::select('terms_and_conditions')->where('id',1)->first();
          $signature = FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no',$plotno)->select('sign_affidavit','sign_affidavit_date')->first();
          $data = ['terms_and_conditions' => $terms_and_conditions->terms_and_conditions,'Farmer'=>$Farmer];
          $pdf = \App::make('dompdf.wrapper');
          $pdf->loadHTML(view('affidavit',compact('signature','terms_and_conditions','Farmer'))->render());
          return $pdf->download('Leased_plot_'.$plotno.'_'.$UniqueId.'.pdf');
        }elseif($type == 'CARBON'){




          $Farmer = FinalFarmer::where('farmer_uniqueId',$UniqueId)->where('plot_no',$plotno)->first();


          $carbon_credit = Setting::select('carbon_credit')->where('id',1)->first(); // fetch carbon credit data from db
        //   $signature = Farmer::where('farmer_uniqueId',$UniqueId)->select('sign_carbon_credit')->first(); // fetch signature path
          $data = ['carbon_credit' => $carbon_credit->carbon_credit,'Farmer'=>$Farmer];
          $pdf = \App::make('dompdf.wrapper');
          $pdf->loadHTML(view('carboncredit',compact('data'))->render());
          return $pdf->download('carbon_'.$Farmer->farmer_plot_uniqueid.'.pdf');
        }
      }





     public function pipeinstalltion_plot($plotuniqueid){
       // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
       $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
       $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
       $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
       $PipeInstallation  = PipeInstallation::where('farmer_plot_uniqueid', $plotuniqueid)->first();
       $PipesLocation="";
    //    if($PipeInstallation->pipes_location){
    //         $PipesLocation = json_decode($PipeInstallation->pipes_location);
    //    }
       $PipesLocation = PipeInstallationPipeImg::where('farmer_plot_uniqueid',$plotuniqueid)->get();
       $Polygon = json_decode($PipeInstallation->ranges);
    //   foreach($ploygon as $latlng){
    //       dd($latlng);
    //   }
    //   $Polygon =

       $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();

       $reject_module = RejectModule::all();
       $page_title = 'Pipe Installation';
       $page_description = 'Pipe Installation';
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
       return view('admin.ApprovFarm.approved-plot-pipe',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots','reject_module','farmerbenefitimg','PipesLocation'));
     }

    public function getPolygon($plotuniqueid){
        $PipeInstallation  = PipeInstallation::where('farmer_plot_uniqueid', $plotuniqueid)->first();
        if($PipeInstallation->ranges){
            $Polygon = json_decode($PipeInstallation->ranges);
        }
        return response()->json(['success'=>true, 'polygon'=>$Polygon],200);
    }

    public function awd_captured(Request $request, $plotuniqueid){
      $plot = FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
      $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $PipeInstallation  = PipeInstallation::where('farmer_plot_uniqueid', $plotuniqueid)->first();
      $PipesLocation="";
      if($PipeInstallation->pipes_location){
           $PipesLocation = json_decode($PipeInstallation->pipes_location);
      }
      $Polygon = json_decode($PipeInstallation->ranges);
      $awd=Aeration::where('farmer_plot_uniqueid',$plotuniqueid)->get();
      $AwdImage = AerationImage::where('farmer_plot_uniqueid',$plotuniqueid)->get();
      $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $page_title = 'Aeration Detail';
      $page_description = 'Aeration Detail';
      $action = 'table_farmer';
      return view('admin.ApprovFarm.awd-captured',compact('plot',
                'page_title','page_description',
                'action','farmerplots','PipeInstallation','PipesLocation','Polygon','awd','farmerbenefitimg','AwdImage'));
    }
}
