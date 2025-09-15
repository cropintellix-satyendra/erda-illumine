<?php

namespace App\Http\Controllers\Admin\l2validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\VendorLocation;
use DB;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Models\FarmerPlotImage;
use App\Models\RejectModule;
use App\Models\FinalFarmer;
use App\Models\PipeInstallation;
use App\Models\FarmerCropdata;
use App\Models\FinalFarmerPlotImage;
use App\Models\AerationImage;
use App\Models\Aeration;
use App\Exports\ApprovedPlotExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Storage;
use App\Exports\L2ApprovedIndividualExport;
use App\Exports\L2PendingIndividualExport;
use App\Exports\L2RejectedIndividualExport;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallationPipeImg;


// use App\Models\ViewerLocation;
use App\Models\Minimumvalue;

use App\Models\FinalFarmerBenefitImage;

use App\Exports\AerationExport;
use App\Exports\PipeInstallationExport;
use App\Exports\L2RejectExport;

use App\Exports\L2PendingExport;

class L2ValidatorController extends Controller
{

  /**
  * Search list of farmer.
  *
  * @return \Illuminate\Http\Response
  */
 public function approved_search()
 {
     if(request()->has('query') && !empty(request()->query)){//for search box in show page
       $Farmers = FinalFarmer::where('L2_appr_userid',auth()->user()->id)->where('final_status_onboarding','Approved')->where('farmer_uniqueId','like','%'.request()->get('query'))->limit(10)->when(request(),function($q){

       });
       return response()->json($Farmers->get());
     }//end for search box in show page
 }


 /**
    * Search list of farmer.
    *
    * @return \Illuminate\Http\Response
    */
    public function pending_search()
    {
       if(request()->has('query') && !empty(request()->query)){//for search box in show page
         $Farmers = FarmerPlot::where('status','Approved')->where('final_status','Pending')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){

         });
         return response()->json($Farmers->get());
       }//end for search box in show page
    }

 /**
 * Search list of farmer.
 *
 * @return \Illuminate\Http\Response
 */
public function reject_search()
{
    if(request()->has('query') && !empty(request()->query)){//for search box in show page
      $Farmers = FarmerPlot::where('finalreject_userid',auth()->user()->id)->where('final_status','Rejected')->where('status','Rejected')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){

      });
      return response()->json($Farmers->get());
    }//end for search box in show page
}




  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function all_plots()
  {
    //Plot view
      if(request()->ajax()){
        $plots = FarmerPlot::with('farmer')->whereHas('farmer',function($q){
        $q->where('onboarding_form','1');

        if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka') && !request()->has('panchayats') && !request()->has('village')){
            $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
            $q->whereIn('state_id',explode(',',$VendorLocation->state));
            if(!empty($VendorLocation->district)){
               $q->whereIn('district_id',explode(',',$VendorLocation->district));
            }
            if(!empty($VendorLocation->taluka)){
               $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
            }
        }

        if(auth()->user()->hasRole('SuperValidator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka')){
            $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
            $q->whereIn('state_id',explode(',',$VendorLocation->state));
            $q->where('status_onboarding','Approved');
            if(!empty($VendorLocation->district)){
               $q->whereIn('district_id',explode(',',$VendorLocation->district));
            }
            if(!empty($VendorLocation->taluka)){
               $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
            }
        }

        if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
            $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
            $q->whereIn('state_id',explode(',',$viewerlocation->state));
        }//end of viewer


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
             $q->where('status','like',request('farmer_status'));
        }
        if(request()->has('executive_onboarding') && !empty(request('executive_onboarding'))){
             $q->where('surveyor_id',request('executive_onboarding'));
        }
        if(request()->has('start_date') && !empty(request('start_date'))){
            $q->whereDate('updated_at','>=',request('start_date'));
        }
        if(request()->has('end_date') && !empty(request('end_date'))){
            $q->whereDate('updated_at','<=',request('end_date'));
        }
        return $q;
        })->orderBy('id','desc');

        return datatables()->of($plots)->make(true);
      }//end layoutout plot

    $others = "0";
    $page_title = 'Onboarding | All plot list';
    $page_description = 'Onboarding | All plot list';
    $action = 'table_farmer';
    $farmerscount = DB::table('farmers')->where('onboarding_form','1')->count();
    $farmers_Location = DB::table('farmers')->where('onboarding_form','1')->select('farmer_name','no_of_plots','latitude', 'longitude')->get();

    $others = "0";
    $total_plot_area = DB::table('farmers')->where('onboarding_form',1)->where('status_onboarding','Approved')->sum('total_plot_area');

    $states = DB::table('states')->where('status',1)->get();
    $districts = DB::table('districts')->where('status',1)->get();
    $talukas = DB::table('talukas')->where('status',1)->get();
    $panchayats = DB::table('panchayats')->get();
    $villages = DB::table('villages')->get();
    $onboarding_executive = DB::table('farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();

    return view('l2validator.all-plot', compact('page_title', 'page_description','action',
                                          'farmerscount',
                                          'others','farmers_Location','states','districts','talukas',
                                          'panchayats','villages','onboarding_executive','total_plot_area'));

  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function all_show_plot($id,$farmer_uniqueid)
  {
    if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $farmerplots =  FarmerPlot::with('UserApprovedRejected:id,name,email')->where('farmer_uniqueId',$farmer_uniqueid)->get();
    $Farmer = Farmer::find($farmerplots->first()->farmer_id);
    $Farmerplotsimages = FarmerPlotImage::where('farmer_id', $id)->where('farmer_unique_id',$farmer_uniqueid)->where('status','Approved')->get();
    $farmerplots_first = FarmerPlot::where('farmer_id', $id)->where('farmer_uniqueId',$farmer_uniqueid)->first();
    $farmerbenefit = FarmerBenefit::where('farmer_uniqueId',$farmer_uniqueid)->get();
    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();

    $reject_module = RejectModule::all();
    $action = 'form_pickers';
    $page_title = 'Onboarding | All Show';
    $count = 1;
    $allrejected=0;
    $allapproved=0;
    foreach($farmerplots as $plot){
        if($plot->status == 'Rejected'){
            if($count == $Farmer->no_of_plots){
                $allrejected= 1;
            }
            $count++;
        }
    }
    $appcount=1;
    foreach($farmerplots as $plot){
        if($plot->status == 'Approved'){
            if($appcount == $Farmer->no_of_plots){
                $allapproved = 1;
            }
            $appcount++;
        }
    }
    $anyrejected = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$farmer_uniqueid)->where('status','Rejected')->count();
    $guntha = 0.025000;
      //  $guntha = 0.025;
    if($Farmer->state_id == 36){
        $total_area_acres  = 0;
        foreach($farmerplots as $plot){
            $area = number_format((float)$plot->area_in_acers, 2, '.', '');
            $split = explode('.', $area);//spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
            $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
              // dd($split,$valueafterdecimal, $result, $conversion);

            $conversion = $conversion[1]??0;


            $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
            $plot->convertedacres = $acers;
            $total_area_acres+=$acers;
        }
        $Farmer->total_area_acres_of_guntha = $total_area_acres;
    }
    return view('l2validator.all-show',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit'
                      ,'reject_module','allrejected','allapproved','anyrejected'));
  }


  public function all_plot_detail($id){
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

    $reject_module = RejectModule::all();
    $page_title = 'Onboarding | All plot Detail';
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
    return view('l2validator.all-plot-detail',compact('plot','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','status'));
  }

  /**
   * Show the counting to farmer index page from ajax.
   *
   * @return \Illuminate\Http\Response
   */
  public function counting()
  {
      $farmer_count = FarmerPlot::groupBy('farmer_uniqueId')->whereHas('final_farmers',function($q){
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

       $farmers_count_plot = FarmerPlot::whereHas('final_farmers',function($q){
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

      $approved = FinalFarmer::where('final_status_onboarding', 'Approved')->where('L2_appr_userid',auth()->user()->id)->when(request(),function($q){
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

      $total_plot_area = FarmerPlot::when(request(),function($q){

          return $q;
      })->sum('area_acre_awd');

      $total_plot_area = number_format((float) $total_plot_area, 2);
      $others = "0";
      return response()->json(['success'=>true,'farmer_count'=>$farmer_count ,'plot_count'=>$farmers_count_plot, 
                                  'approved'=>$approved,'pendings'=>$pendings,'rejected'=>$rejected,'totalarea'=>$total_plot_area,'others'=>$others],200);
  }



  /**
   * Display a listing of the approved list of plots.
   *
   * @return \Illuminate\Http\Response
   */
  public function approved_lists()
  {
    if(auth()->user()->cannot('farmer')) abort(403, 'User does not have the right roles.');
    //Plot list
    if(request()->ajax()){
      $plots= FinalFarmer::where('L2_appr_userid',auth()->user()->id)->when(request(),function($q){
        $q->where('onboarding_form','1');
        if(request('module') == 'CropData'){//when plot has cropdata
            $q->whereHas('PlotCropData');
        }
        if(request('module') == 'Benefit'){//when plot has benefit
            $q->whereHas('BenefitsData');
        }
        if(request('module') == 'PipeInstalltion'){//when plot has pipeinstalltion
            $q->whereHas('PlotPipeData');
        }
        if(request('module') == 'Aeration'){//when plot has aeration
            $q->whereHas('AerationData');
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

      $page_title = 'Onboarding | Approved list';
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
      return view('l2validator.approved-plot',compact('page_title','page_description','action','seasons','states',
                          'districts','talukas','panchayats','villages','onboarding_executive','status','others'));

    // end Plot view without ajax
  }


  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function approved_show($farmer_uniqueid)
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
    $page_title = 'Onboarding | Show Detail';
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

    return view('l2validator.approved-show-detail',compact('Farmer', 'farmerplots', 'farmerbenefitimg','action','page_title','reject_module','crop_data',
                                              'Farmerplotsimages','PipeInstallation','awd'));
  }


  /**
   * Display a listing of the approved plot detail.
   *
   * @return \Illuminate\Http\Response
   */
  public function approved_detail($id){
    if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot = FinalFarmer::with('PlotPipeData','ApprvFarmerPlot')->where('farmer_plot_uniqueid',$id)->first();
    // dd($plot);
    $farmer = Farmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->first();
    $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $check_pipedata = DB::table('pipe_installations')->where('farmer_plot_uniqueid',$id)->first();
    $reject_module = RejectModule::all();
    $page_title = 'Onboarding | Approved plot detil';
    $page_description = 'Approved Farmer Plot Detail';
    $action = 'table_farmer';
    $guntha = 0.025000;
    $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$id)->first();
    $cropdata=FarmerCropdata::with('PlotCropDetails' ,'farmerplot_details')->where('farmer_plot_uniqueid',$id)->get();
    // dd($cropdata);
    // dd($plot,$farmer,$farmerplots,$farmerplots_area,$farmerbenefitimg,$check_pipedata,$reject_module);
    $valicountplotapprv = "1";
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
    return view('l2validator.approved-plot-detail',compact('cropdata','farmerplot','plot','farmer','page_title','page_description','action','farmerplots','reject_module','farmerbenefitimg','check_pipedata','valicountplotapprv'));
  }

  /**
   * level 2 validator user can get list of pending plotss.
   *
   * @return \Illuminate\Http\Response
   */
  public function pending_lists()
  {
    //level 2 validator get pending plot list from this function
    if(auth()->user()->cannot('farmer')) abort(403, 'User does not have the right roles.');
	  //Plot list
	  if(request()->ajax()){
  		$plots= FarmerPlot::with('farmer')->whereHas('farmer',function($q){
          //final_status column should be pending to displayed in l2 validator list
          $q->where('final_status','Pending');
          //because this status is for level 1 validator,
           // to be display in l2 pending list plot should approved by l1 validator
          $q->where('status','Approved');
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
  		$page_title = 'Onboarding | Plot Pending';
  		$page_description = 'List of pending plots';
  		$action = 'table_farmer';
  		//below process is for first time landing on page}
      //for admin data

      $states = DB::table('states')->where('status',1)->get();
      $districts = DB::table('districts')->where('status',1)->get();
      $talukas = DB::table('talukas')->where('status',1)->get();
      $panchayats = DB::table('panchayats')->get();
      $villages = DB::table('villages')->get();

      // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
    //   $onboarding_executive =Farmer::with('FarmerPlot')->where('onboarding_form','1')->groupBy('surveyor_name')->select('surveyor_name')->whereHas('FarmerPlot',function($q){
    //       $q->where('final_status','Pending');
    //   })->get();

        // $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->whereHas('FarmerPlot',function($q){
        //   $q->where('final_status','Pending');
        // })->get();

        $onboarding_executive  = DB::table('farmers')->where('onboarding_form','1')->groupBy('farmers.surveyor_name')
                        ->join('farmer_plot_detail', 'farmers.id' ,'=','farmer_plot_detail.farmer_id')
                        ->where('farmer_plot_detail.final_status', '=','Pending')
                        ->get();

  		$seasons = DB::table('seasons')->get();
      $status = request()->status;
      $others = "0";
  	  return view('l2validator.pending-plot',compact('page_title','page_description','action','seasons','states', 'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
  }


  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function pending_show($id,$farmer_uniqueid)
  {
    if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $status = DB::table('farmer_plot_detail')->select('final_status','status')->where('id',$id)->first();
    $farmerplots =  FarmerPlot::with('UserApprovedRejected:id,name,email')->where('farmer_uniqueId',$farmer_uniqueid)->where('status','Approved')->get();
    $Farmer = Farmer::find($farmerplots->first()->farmer_id);
    $Farmerplotsimages = FarmerPlotImage::where('farmer_unique_id',$farmer_uniqueid)->where('status','Approved')->get();
    $farmerbenefit = FarmerBenefit::where('farmer_uniqueId',$farmer_uniqueid)->get();
    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();
    $onboarding_plot = explode(',',$Farmer->status_onboarding_plot);
    $onboarding_plot_reject = explode(',',$Farmer->reject_onboarding_plot);
    $reject_module = RejectModule::all();
    $action = 'form_pickers';
    $page_title = 'Onboarding | Pending Show';
    $count = 1;
    $allrejected=0;
    $allapproved=0;
    foreach($farmerplots as $plot){
        if($plot->status == 'Rejected'){
            if($count == $Farmer->no_of_plots){
                $allrejected= 1;
            }
            $count++;
        }
    }
    $appcount=1;
    foreach($farmerplots as $plot){
        if($plot->status == 'Approved'){
            if($appcount == $Farmer->no_of_plots){
                $allapproved = 1;
            }
            $appcount++;
        }
    }
    $anyrejected = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$farmer_uniqueid)->where('status','Rejected')->count();
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
        $Farmer->total_area_acres_of_guntha = $total_area_acres;
    }

    $valicountplotapprv   =  '1';
    return view('l2validator.pending-show',compact('Farmer', 'plot', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot','onboarding_plot_reject'
                      ,'reject_module','allrejected','allapproved','anyrejected','status','valicountplotapprv'));
  }

  public function pending_detail($id){
      if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
      $plot = FinalFarmer::with('PlotPipeData','ApprvFarmerPlot')->where('farmer_plot_uniqueid',$id)->first();
      dd($plot->final_status);
      $status = $plot->final_status;//this is use for previous an next button

    //   dd($plot);
      $plot_farmer=FarmerPlot::with('final_farmerno')->findOrFail($id);



      $farmerplots_area =  FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
      $valicountplotapprv = FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('status','Approved')->count();
      $finalcountplotapprv = FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('final_status_onboarding','Approved')->count();
      $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$id)->get();

      $reject_module = RejectModule::where('type','Onboarding')->get();
      $page_title = 'Onboarding | Pending Plot Detail';
      $page_description = 'Farmer Plot Detail';
      $action = 'table_farmer';
         $guntha = 0.025000;
        //  $guntha = 0.025;
        $farmerplot=DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$id)->first();
        $cropdata=FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$id)->get();
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
      return view('l2validator.pending-plot-detail',compact('plot','farmer','plot_farmer','page_title','cropdata','page_description','action','farmerplots','reject_module','valicountplotapprv','finalcountplotapprv','status'));
    }

    /**
        * Approve or Reject farmer status.
        *
        * @param  int  $id
        * @return \Illuminate\Http\Response
        */
        public function final_farmer_status(Request $request, $type,$UniqueId){
          if($type == "finalonboarding"){
              foreach($request->plots as $no){
                  $Farmerplot =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no',$no['PlotNo'])
                                    ->UPDATE(['final_status'=>'Approved','check_update'=>0,
                                     'finalaprv_timestamp'=>carbon::now(),'finalaprv_remark'=>$no['FinalApproveComment'], 'finalappr_userid'=>auth()->user()->id]);

                  $record =  PlotStatusRecord::create([
                          'farmer_uniqueId'           => $UniqueId,
                          'plot_no'                   => $no['PlotNo'],
                          'farmer_plot_uniqueid'      => $UniqueId.'P'.$no['PlotNo'],
                          'level'                     => 'L-2-Validator',
                          'status'                    => 'Approved',
                          'comment'                   => $no['FinalApproveComment'],
                          'timestamp'                 => Carbon::now(),
                          'user_id'                   => auth()->user()->id,
                          // 'approve_comment'           => $no['FinalApproveComment'],
                          // 'appr_timestamp'            => Carbon::now(),
                          // 'reject_comment'        => NULL,
                          // 'reject_timestamp'      => NULL,
                          // 'aprvd_recj_userid'      => auth()->user()->id,

                      ]);

              }



             $CheckFinalStatus = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$UniqueId)->where('plot_no',$request->plots[0]['PlotNo'])->where('final_status','Approved')->first();
             $farmer = Farmer::where('farmer_uniqueId',$UniqueId)->first();


             if($CheckFinalStatus){
               //now copy data to final table
               $upload_to_finaltable = $this->final_approved_record($request, $type,$UniqueId,$farmer,$CheckFinalStatus);
             }
            if($upload_to_finaltable){
              $FarmerAppPlot = FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('final_status','Approved')->count();
              if($FarmerAppPlot == $request->TotalPlot){
                   $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['final_status_onboarding'=>'Approved']);
                   return response()->json(['success' =>true, 'message'=>'Onboarding Approved', 'farmer'=>$Farmer],200);
              }
              return response()->json(['success' =>true, 'message'=>'Approved Successfully', 'farmer'=>$CheckFinalStatus],200);
            }
              return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
             //end approve
          }
          elseif($type == "finalreject"){// for reject
                    $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['reject_timestamp'=>carbon::now()]);
                    if($request->reasons == 1  || $request->reasons == 2){//just to reject image
                        $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                                        ->UPDATE(['status'=>'Rejected',
                                                  'final_status'=>'Rejected',
                                                  'reason_id'=>$request->reasons,
                                                  'reject_comment'=>$request->rejectcomment,
                                                  'reject_timestamp'=>Carbon::now(),
                                                  'finalreject_timestamp'=>Carbon::now(),
                                                  'finalreject_userid'=>auth()->user()->id,]);



                            $image = FarmerPlotImage::where('farmer_unique_id',$UniqueId)->where('plot_no',$request->plotno)->update(['status'=>'Rejected']);

                       $record =  PlotStatusRecord::create([
                          'farmer_uniqueId'           => $UniqueId,
                          'plot_no'                   => $request->plotno,
                          'farmer_plot_uniqueid'      => $UniqueId.'P'.$request->plotno,
                          'level'                     => 'L-2-Validator',
                          'status'                    => 'Rejected',
                          'comment'                   => $request->rejectcomment,
                          'timestamp'                 => Carbon::now(),
                          'user_id'                   => auth()->user()->id,
                          'reject_reason_id'          => $request->reasons,

                          // 'approve_comment'           => NULL,
                          // 'appr_timestamp'            => NULL,
                          // 'reject_comment'        => $request->rejectcomment,
                          // 'reject_timestamp'      => Carbon::now(),
                          // 'aprvd_recj_userid'      => auth()->user()->id,
                          // 'reject_reason_id'     => $request->reasons,

                      ]);

                  }else{
                      $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                                            ->UPDATE(['status'=>'Rejected',
                                            'final_status'=>'Rejected',
                                            'reason_id'=>$request->reasons,
                                                      'reject_comment'=>$request->rejectcomment,
                                                      'reject_timestamp'=>Carbon::now(),
                                                      'finalreject_timestamp'=>Carbon::now(),
                                                      'finalreject_userid'=>auth()->user()->id,]);

                      $record =  PlotStatusRecord::create([
                          'farmer_uniqueId'           => $UniqueId,
                          'plot_no'                   => $request->plotno,
                          'farmer_plot_uniqueid'      => $UniqueId.'P'.$request->plotno,
                          'level'                     => 'L-2-Validator',
                          'status'                    => 'Rejected',
                          'comment'                   => $request->rejectcomment,
                          'timestamp'                 => Carbon::now(),
                          'user_id'                   => auth()->user()->id,
                          'reject_reason_id'          => $request->reasons,
                          // 'approve_comment'           => NULL,
                          // 'appr_timestamp'            => NULL,
                          // 'reject_comment'        => $request->rejectcomment,
                          // 'reject_timestamp'      => Carbon::now(),
                          // 'aprvd_recj_userid'      => auth()->user()->id,
                          // 'reject_reason_id'     => $request->reasons,

                      ]);
                  }

                    // $Farmerplot =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                    //                         ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),
                    //                                   ]);
                $Farmer =  DB::table('farmers')->where('farmer_uniqueId',$UniqueId)->first();
                $Farmerplot =  DB::table('farmer_plot_detail')->where('farmer_uniqueId',$UniqueId)->get();
                $allrejected=0;$count=1;
                foreach($Farmerplot as $plot){
                    if($plot->status == 'Rejected'){
                        if($count == $Farmer->no_of_plots){
                            $allrejected = 1;
                            Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['status_onboarding'=>'Rejected', 'reject_timestamp'=>carbon::now()]);
                        }
                        $count++;
                    }//if end
                }//foreach end
                    if(!$Farmer){
                      return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
                    }
                    return response()->json(['success' =>true, 'farmer'=>$Farmer],200);

            //   if($request->reasons == 1  || $request->reasons == 2){//just to reject image
            //   $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
            //                             ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,
            //                                       'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),'aprv_recj_userid'=>auth()->user()->id]);
            //     $image = FarmerPlotImage::where('farmer_unique_id',$UniqueId)->where('plot_no',$request->plotno)->update(['status'=>'Rejected']);
            //   }else{
            //       $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
            //                             ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,
            //                                       'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),'aprv_recj_userid'=>auth()->user()->id]);
            //   }
            //     $Farmer =  DB::table('farmers')->where('farmer_uniqueId',$UniqueId)->first();
            //     $Farmerplot =  DB::table('farmer_plot_detail')->where('farmer_uniqueId',$UniqueId)->get();
            //     $allrejected=0;$count=1;
            //     foreach($Farmerplot as $plot){
            //         if($plot->status == 'Rejected'){
            //             if($count == $Farmer->no_of_plots){
            //                 $allrejected = 1;
            //                 Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['status_onboarding'=>'Rejected', 'reject_timestamp'=>carbon::now()]);
            //             }
            //             $count++;
            //         }//if end
            //     }//foreach end
            //     if(!$Farmer){
            //       return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
            //     }
            //     return response()->json(['success' =>true, 'farmer'=>$Farmer],200);
          }//endif overall
        }

    public function list_trash(){
       $farmers = Farmer::where('onboarding_form', '0')->orderBy('farmer_uniqueId','desc')->get();
       $page_title = 'Trash';
 $page_description = 'Some description for the page';
 $action = 'table_landownerships';

       return view('admin.farmers.trash',compact('farmers','page_title','page_description','action'));
   }


   public function final_approved_record($request, $type,$UniqueId,$farmer,$CheckFinalStatus){
     // dd($farmer,$CheckFinalStatus);


     $finalfarmer = new FinalFarmer;
     $finalfarmer->surveyor_id  = $farmer->surveyor_id;
     $finalfarmer->surveyor_name= $farmer->surveyor_name;
     $finalfarmer->surveyor_email= $farmer->surveyor_email;
     $finalfarmer->surveyor_mobile= $farmer->surveyor_mobile;
     $finalfarmer->farmer_uniqueId= $farmer->farmer_uniqueId;
     $finalfarmer->organization_id= $farmer->organization_id;
     $finalfarmer->gender= $farmer->gender;
     $finalfarmer->guardian_name= $farmer->guardian_name;
     $finalfarmer->farmer_name= $farmer->farmer_name;
     $finalfarmer->mobile_access= $farmer->mobile_access;
     $finalfarmer->mobile_reln_owner= $farmer->mobile_reln_owner;
     $finalfarmer->mobile= $farmer->mobile;
     $finalfarmer->mobile_verified= $farmer->mobile_verified;
     $finalfarmer->no_of_plots        = $farmer->no_of_plots;
     $finalfarmer->total_plot_area        = $farmer->total_plot_area;
     $finalfarmer->country_id        = $farmer->country_id;
     $finalfarmer->country        = $farmer->country;
     $finalfarmer->state_id        = $farmer->state_id;
     $finalfarmer->state        = $farmer->state;
     $finalfarmer->district_id        = $farmer->district_id;
     $finalfarmer->district        = $farmer->district;
     $finalfarmer->taluka_id        = $farmer->taluka_id;
     $finalfarmer->taluka        = $farmer->taluka;
     $finalfarmer->panchayat_id        = $farmer->panchayat_id;
     $finalfarmer->panchayat        = $farmer->panchayat;
     $finalfarmer->village_id        = $farmer->village_id;
     $finalfarmer->village        = $farmer->village;
     $finalfarmer->latitude        = $farmer->latitude;
     $finalfarmer->longitude        = $farmer->longitude;
     $finalfarmer->date_survey        = $farmer->date_survey;
     $finalfarmer->time_survey        = $farmer->time_survey;
     $finalfarmer->check_carbon_credit        = $farmer->check_carbon_credit;
    //signature move to new folder final folder
    $filename = explode('/',$farmer->signature);
    $old_path = config('storagesystems.store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $new_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $storageAt = storage_path() . "/app/public/".config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId;
    if(!\File::exists($storageAt)) {
        \File::makeDirectory($storageAt, 0755, true, true);
    }
    Storage::copy('public/'.$old_path, 'public/'.$new_path);//first argument has old signature path. And second argument has new path
     $finalfarmer->signature        = asset('storage/'.$new_path);//$new_sign_S3_path;//$farmer->signature; new  signature path will be stored here
     $finalfarmer->signature_old        = asset('storage/'.$old_path); //and old signature path will be stored here

// dd($finalfarmer);

    //plotowner_sign move to new folder final folder
    $filename = explode('/',$farmer->plotowner_sign);
    $old_path = config('storagesystems.store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $new_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $storageAt = storage_path() . "/app/public/".config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId;
    if(!\File::exists($storageAt)) {
        \File::makeDirectory($storageAt, 0755, true, true);
    }
    Storage::copy('public/'.$old_path, 'public/'.$new_path);//first argument has old plotowner_sign path. And second argument has new path
     $finalfarmer->plotowner_sign        = asset('storage/'.$new_path);//$new_sign_S3_path;//$farmer->plotowner_sign; new  plotowner_sign path will be stored here
     $finalfarmer->plotowner_sign_old        = asset('storage/'.$old_path); //and old plotowner_sign path will be stored here


    //farmer_photo move to new folder final folder
    $filename = explode('/',$farmer->farmer_photo);
    $old_path = config('storagesystems.store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $new_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $storageAt = storage_path() . "/app/public/".config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId;
    if(!\File::exists($storageAt)) {
        \File::makeDirectory($storageAt, 0755, true, true);
    }
    Storage::copy('public/'.$old_path, 'public/'.$new_path);//first argument has old farmer_photo path. And second argument has new path
     $finalfarmer->farmer_photo        = asset('storage/'.$new_path);//$new_sign_S3_path;//$farmer->farmer_photo; new  farmer_photo path will be stored here
     $finalfarmer->farmer_photo_old        = asset('storage/'.$old_path); //and old farmer_photo path will be stored here


    //aadhaar_photo move to new folder final folder
    $filename = explode('/',$farmer->aadhaar_photo);
    $old_path = config('storagesystems.store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $new_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $storageAt = storage_path() . "/app/public/".config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId;
    if(!\File::exists($storageAt)) {
        \File::makeDirectory($storageAt, 0755, true, true);
    }
    Storage::copy('public/'.$old_path, 'public/'.$new_path);//first argument has old aadhaar_photo path. And second argument has new path
     $finalfarmer->aadhaar_photo        = asset('storage/'.$new_path);//$new_sign_S3_path;//$farmer->aadhaar_photo; new  aadhaar_photo path will be stored here
     $finalfarmer->aadhaar_photo_old        = asset('storage/'.$old_path); //and old aadhaar_photo path will be stored here

    //others_photo move to new folder final folder
    $filename = explode('/',$farmer->others_photo);
    $old_path = config('storagesystems.store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $new_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);
    $storageAt = storage_path() . "/app/public/".config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId;
    if(!\File::exists($storageAt)) {
        \File::makeDirectory($storageAt, 0755, true, true);
    }
    Storage::copy('public/'.$old_path, 'public/'.$new_path);//first argument has old others_photo path. And second argument has new path
     $finalfarmer->others_photo        = asset('storage/'.$new_path);//$new_sign_S3_path;//$farmer->others_photo; new  others_photo path will be stored here
     $finalfarmer->others_photo_old        = asset('storage/'.$old_path); //and old others_photo path will be stored here

     $finalfarmer->sign_carbon_credit        = $farmer->sign_carbon_credit;
     $finalfarmer->sign_carbon_date        = $farmer->sign_carbon_date;
     $finalfarmer->remarks        = $farmer->remarks;
     $finalfarmer->status_onboarding        = $farmer->status_onboarding;
     $finalfarmer->final_status_onboarding        = 'Approved';
     $finalfarmer->onboarding_form        = $farmer->onboarding_form;
     $finalfarmer->farmer_plot_uniqueid   = $CheckFinalStatus->farmer_plot_uniqueid;
     $finalfarmer->plot_no   = $CheckFinalStatus->plot_no;
     $finalfarmer->area_in_acers   = $CheckFinalStatus->area_in_acers;
     $finalfarmer->land_ownership   = $CheckFinalStatus->land_ownership;
     $finalfarmer->actual_owner_name   = $CheckFinalStatus->actual_owner_name;
     $finalfarmer->affidavit_tnc   = $CheckFinalStatus->affidavit_tnc;
     $finalfarmer->sign_affidavit   = $CheckFinalStatus->sign_affidavit;
     $finalfarmer->sign_affidavit_date   = $CheckFinalStatus->sign_affidavit_date;
     $finalfarmer->survey_no   = $CheckFinalStatus->survey_no;
     //for L2 data store
     $finalfarmer->final_status   = $CheckFinalStatus->final_status;
     $finalfarmer->L2_aprv_timestamp   = $CheckFinalStatus->finalaprv_timestamp;
     $finalfarmer->L2_aprv_remark   = $CheckFinalStatus->finalaprv_remark;
     $finalfarmer->L2_appr_userid   = $CheckFinalStatus->finalappr_userid;
     $finalfarmer->L2_reject_timestamp   = $CheckFinalStatus->finalreject_timestamp;
     $finalfarmer->L2_reject_userid   = $CheckFinalStatus->finalreject_userid;
     //for L1 data store
     $finalfarmer->L1_approve_comment   = $CheckFinalStatus->approve_comment;
     $finalfarmer->L1_reason_id   = $CheckFinalStatus->reason_id;
     $finalfarmer->L1_reject_comment   = $CheckFinalStatus->reject_comment;
     $finalfarmer->L1_reject_timestamp   = $CheckFinalStatus->reject_timestamp;
     $finalfarmer->L1_appr_timestamp   = $CheckFinalStatus->appr_timestamp;
     $finalfarmer->L1_aprv_recj_userid   = $CheckFinalStatus->aprv_recj_userid;

     $finalfarmer->save();
     // FarmerPlotImage
      $imgs  =  DB::table('farmer_land_img')->where('farmer_unique_id',$UniqueId)->where('plot_no',$finalfarmer->plot_no)->get();
      foreach($imgs as $img){
        $plotimg = new FinalFarmerBenefitImage;
        $plotimg->farmer_id  =  $finalfarmer->id;
        $plotimg->farmer_unique_id  =  $img->farmer_unique_id;
        $plotimg->plot_no  =  $img->plot_no;
        $plotimg->image  =  $img->image;

        $filename = explode('/',$img->path);//get filename
        $old_path = config('storagesystems.store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);//build old path
        $new_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.end($filename);//build new path

        $storageAt = storage_path() . "/app/public/".config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId;
        // dd($storageAt);
        if(!\File::exists($storageAt)) {
            \File::makeDirectory($storageAt, 0755, true, true);
        }
        Storage::copy('public/'.$old_path, 'public/'.$new_path);//first argument has old signature path. And second argument has new path

        $plotimg->path  =  asset('storage/'.$new_path);//$new_S3_path; // new path for plot images
        $plotimg->oldpath  =  asset('storage/'.$old_path);//$img->path; // old path for plot images


        $plotimg->status  =  $img->status;
        $plotimg->save();
      }//foreach end for image

     $CheckTotalArea = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$UniqueId)->where('final_status','Approved')->sum('area_in_acers');
     $CheckTotalPlot = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$UniqueId)->where('final_status','Approved')->count();

     FinalFarmer::where('farmer_uniqueId', $UniqueId)->update(['no_of_plots'=> $CheckTotalPlot, 'total_plot_area'=>$CheckTotalArea]);

     if($finalfarmer){
       return true;
     }
     return false;
   }


   public function plotEdit($id){
     if(auth()->user()->cannot('edit farmer')) abort(403, 'User does not have the right roles.');
     $plot=FarmerPlot::findOrFail($id);
     $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
     $reject_module = RejectModule::where('type','PipeInstallation')->get();
     $page_title = 'Onboarding | Pending Edit';
     $page_description = 'Farmer Plot Detail';
     $action = 'table_farmer';
     $Farmerplotsimages = FarmerPlotImage::where('farmer_unique_id',$plot->farmer_uniqueId)->where('status','Approved')->get();
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
         $minimumvalues = Minimumvalue::select('value','state_id')->where('status',1)->where('state_id',$plot->farmer->state_id)->first();
     return view('l2validator.pending-plot-edit',compact('plot','page_title','page_description','action','farmerplots','reject_module',
                                 'Relationshipowner','states','districts','talukas','panchayats','villages','minimumvalues','Farmerplotsimages'));
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
       if(auth()->user()->cannot('edit farmer')) abort(403, 'User does not have the right roles.');
       if($request->type == 'UpdateBasicform'){
           $validatedData = $request->validate([
             'FarmerName' => 'required',
             'Mobile' => 'required',
           ]);
           $Farmer = Farmer::where('farmer_uniqueId', $id)->first();
           $Farmer->farmer_name = $request->FarmerName;
           $Farmer->mobile_reln_owner = $request->RelOwner;
           $Farmer->mobile_access = $request->MobileAccess;
           $Farmer->mobile = $request->Mobile;
           $Farmer->save();

           $plot_data = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$id)->get();
              foreach($plot_data as $data){
                 if($data->land_ownership == 'Own'){
                      $update_name = FarmerPlot::where('farmer_uniqueId',$id)->where('plot_no', $data->plot_no)->update(['actual_owner_name'=>$request->FarmerName]);
                  }
              }
           if(!$Farmer){
             return response()->json(['error'=>true,'message'=>'Something went wrong'],500);
           }
       }elseif($request->type == 'UpdatePlot'){
           $validatedData = $request->validate([
             'area' => 'required',
             'survey' => 'required',
           ]);
           try{
               if(count($request->area) > 0){
                   foreach($request->area as $value){
                       $area_acers = DB::table('farmer_plot_detail')->where('id',$id)->where('plot_no', $value['PlotNo'])->update(['area_in_acers'=>$value['area'] ]);
                   }
                   $farmerplot = FarmerPlot::where('farmer_uniqueId',$request->unique)->sum('area_in_acers');
                   $updatearea = Farmer::where('farmer_uniqueId',$request->unique)->update(['total_plot_area'=>number_format((float) $farmerplot, 2)]);
               }
               foreach($request->ownername as $value){

                  $area_acers = DB::table('farmer_plot_detail')->where('id',$id)->where('plot_no', $value['PlotNo'])->update(['actual_owner_name'=>$value['actual_owner_name'] ]);
                  $plot_data = DB::table('farmer_plot_detail')->where('id',$id)->where('plot_no', $value['PlotNo'])->first();


                  if($plot_data->land_ownership == 'Own'){
                      $update_name = Farmer::where('id',$plot_data->farmer_id)->update(['farmer_name'=>$value['actual_owner_name']]);
                    //  foreach($plot_data as $data){
                    //  if($data->land_ownership == 'Own'){
                    //           $update_name = FarmerPlot::where('farmer_uniqueId',$data->farmer_uniqueId)->where('plot_no', $data->plot_no)->update(['actual_owner_name'=>$value['actual_owner_name']]);
                    //       }
                    //   }
                  }





               }
               foreach($request->survey as $value){
                    $plots = DB::table('farmer_plot_detail')->where('id',$id)->where('plot_no', $value['PlotNo'])->update(['survey_no'=>$value['survey']]);
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
                $plot_data = DB::table('farmer_plot_detail')->where('id',$id)->first();
               $Farmer = Farmer::where('id',$plot_data->farmer_id)->first();

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
    * Display a listing of the approved list of plots.
    *
    * @return \Illuminate\Http\Response
    */
   public function reject_lists()
   {
     if(auth()->user()->cannot('farmer')) abort(403, 'User does not have the right roles.');
 	//Plot view
 	if(request()->has('query') && !empty(request()->query)){//for search box in show page
         // $Farmers = Farmer::where('onboarding_form','1')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){
         $Farmers =  FarmerPlot::with('farmer')->whereHas('farmer',function($q){
             $q->where('onboarding_form','1');
           if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka') && !request()->has('panchayats') && !request()->has('village')){
               $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
               $q->whereIn('state_id',explode(',',$VendorLocation->state));
               if(!empty($VendorLocation->district)){
                  $q->whereIn('district_id',explode(',',$VendorLocation->district));
               }
               if(!empty($VendorLocation->taluka)){
                  $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
               }
               return $q;
           }
         });
         return response()->json($Farmers->get());
       }//end for search box in show page
 	  if(request()->ajax()){
   		$plots=FarmerPlot::with('farmer')->where('finalreject_userid', auth()->user()->id)->where('status','Rejected')->whereHas('farmer',function($q){
         $q->where('onboarding_form','1');
         //this is here just to see how it will filter if working correct then good
         if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka') && !request()->has('panchayats') && !request()->has('village')){
             $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
             $q->whereIn('state_id',explode(',',$VendorLocation->state));
             if(!empty($VendorLocation->district)){
                $q->whereIn('district_id',explode(',',$VendorLocation->district));
             }
             if(!empty($VendorLocation->taluka)){
                $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
             }
         }


         if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
             $viewerlocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
             $q->whereIn('state_id',explode(',',$viewerlocation->state));
         }//end of viewer

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
              $q->where('status','like',request('farmer_status'));
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
     }

   		$page_title = 'Onboarding | Reject list';
   		$page_description = 'Some description for the page';
   		$action = 'table_farmer';
   		//below process is for first time landing on page
       if(auth()->user()->hasRole('L-1-Validator')){
           $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
           $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
           $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
           $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
           $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
           $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
             $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->when(request(),function($q){
               if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka') && !request()->has('panchayats') && !request()->has('village')){
                   $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                   $q->whereIn('state_id',explode(',',$VendorLocation->state));
                   if(!empty($VendorLocation->district)){
                      $q->whereIn('district_id',explode(',',$VendorLocation->district));
                   }
                   if(!empty($VendorLocation->taluka)){
                      $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                   }
                   return $q;
               }
             })->get();

       }else{//for admin data
           $states = DB::table('states')->where('status',1)->get();
           $districts = DB::table('districts')->where('status',1)->get();
           $talukas = DB::table('talukas')->where('status',1)->get();
           $panchayats = DB::table('panchayats')->get();
           $villages = DB::table('villages')->get();
          $onboarding_executive = DB::table('farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
       }
   		$seasons = DB::table('seasons')->get();
       $status = request()->status;
       $others = "0";
   		return view('l2validator.reject-plot',compact('page_title','page_description','action','seasons','states',
 												  'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
   }

   /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function reject_show($id,$farmer_uniqueid)
   {
     if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
     $status = DB::table('farmer_plot_detail')->select('final_status','status')->where('id',$id)->first();
     $farmerplots =  FarmerPlot::with('UserApprovedRejected:id,name,email')->where('farmer_uniqueId',$farmer_uniqueid)->get();
     $plots = Farmer::find($farmerplots->first()->farmer_id);
     $Farmerplotsimages = FarmerPlotImage::where('farmer_unique_id',$farmer_uniqueid)->where('status','Approved')->get();
     $farmerbenefit = FarmerBenefit::where('farmer_uniqueId',$farmer_uniqueid)->get();
     $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();
     $onboarding_plot = explode(',',$plots->status_onboarding_plot);
     $onboarding_plot_reject = explode(',',$plots->reject_onboarding_plot);
     $reject_module = RejectModule::where('type','PipeInstallation')->get();
     $action = 'form_pickers';
     $page_title = 'Onboarding | Reject Show';
     $count = 1;
     $allrejected=0;
     $allapproved=0;
     foreach($farmerplots as $plot){
         if($plot->status == 'Rejected'){
             if($count == $plots->no_of_plots){
                 $allrejected= 1;
             }
             $count++;
         }
     }
     $appcount=1;
     foreach($farmerplots as $plot){
         if($plot->status == 'Approved'){
             if($appcount == $plots->no_of_plots){
                 $allapproved = 1;
             }
             $appcount++;
         }
     }
     $anyrejected = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$farmer_uniqueid)->where('status','Rejected')->count();
     $guntha = 0.025000;
     if($plots->state_id == 36){
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
         $plots->total_area_acres_of_guntha = $total_area_acres;
     }
     $valicountplotapprv = '1';
     return view('l2validator.reject-show',compact('plots', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot','onboarding_plot_reject'
                       ,'reject_module','allrejected','allapproved','anyrejected','status','valicountplotapprv'));
   }


   /**
    * Display a listing of the approved plot detail.
    *
    * @return \Illuminate\Http\Response
    */
   public function reject_detail($id){
     if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
     $plot=FarmerPlot::findOrFail($id);
     $farmer = Farmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->first();
     $status = $plot->status;//this is use for previous and next button
     $farmerplots_area =  FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
     $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
     $valicountplotapprv = FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('status','Approved')->count();
     $reject_module = RejectModule::where('type','PipeInstallation')->get();
     $guntha = 0.025000;
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
         $page_title = 'Onboarding | Reject Plot Detail';
         $page_description = 'Farmer Plot Detail';
         $action = 'table_farmer';
     return view('l2validator.reject-plot-detail',compact('plot','farmer','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','status'));
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
        if(request('type') == 'ALL'){
            $filename = 'Farmers-'.Carbon::now().'.xlsx';
            // return Excel::download(new FarmerExport(103390), $filename);
        }elseif(request('type') == 'onboarding' && request('status') == 'Approved'){
            $filename = 'L2_'.request('status')."_".Carbon::now().'.xlsx';

            // return Excel::download(new ApprovedPlotExport('All' ,request()), $filename);
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
            $filename = 'Cropdata_'.Carbon::now().'.xlsx';
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
            $filename = 'Farmers-aeration_'.Carbon::now().'.xlsx';
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
            $filename = 'Farmers-pipe-installation_'.Carbon::now().'.xlsx';
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
        }elseif(request('type') == 'onboarding' && request('status') == 'Pending'){
            $filename = 'L-2-Pending_'.Carbon::now().'.xlsx';
            // return Excel::download(new L2PendingExport('All' ,request()), $filename);
            $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2PendingExport',
                        'parameters'=>['All' ,request()->all()],
                        'filename'=>$filename,
                        'drive'=>'excel'
                    ]
                ];
            $job=\DB::table('temp_jobs')->insert([
                        'queue'=>'excel',
                        'payload'=>json_encode($payload),
                        'user_id'  => auth()->user()->id,
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
        }elseif(request('type') == 'onboarding' && request('status') == 'Rejected'){
            $filename = 'L-2-Rejected_'.Carbon::now().'.xlsx';
            // return Excel::download(new L2RejectExport('All' ,request()), $filename);
            $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L2RejectExport',
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
              $state_id = DB::table('final_farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L2ApprovedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Pending'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('final_farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L2PendingIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Rejected'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('final_farmers')->where('farmer_uniqueId', $unique_id)->first();
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


      public function pipeinstalltion_plot($plotuniqueid){
       // if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
       $plot        =  FinalFarmer::where('farmer_plot_uniqueid',$plotuniqueid)->first();
       $farmerplots =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
       $farmerplots_area =  FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
       $PipeInstallation  = PipeInstallation::where('farmer_plot_uniqueid', $plotuniqueid)->first();
       // $PipesLocation="";
       // if($PipeInstallation->pipes_location){
       //      $PipesLocation = json_decode($PipeInstallation->pipes_location);
       // }

       $PipesLocation = PipeInstallationPipeImg::where('farmer_plot_uniqueid',$plotuniqueid)->get();
       $Polygon = json_decode($PipeInstallation->ranges);
    //   foreach($ploygon as $latlng){
    //       dd($latlng);
    //   }
    //   $Polygon =

       $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
       $updated_polygon = DB::table('old_polygons')->where('farmer_plot_uniqueid',$plotuniqueid)->select('polygon')->get();
       $reject_module = RejectModule::where('type','PipeInstallation')->get();
       $page_title = 'Onboarding | Approved list';
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
       return view('l2validator.approved-plot-pipe',compact('plot','PipeInstallation','PipesLocation','Polygon','page_title','page_description','action','farmerplots','reject_module','farmerbenefitimg','updated_polygon'));
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
      $page_title = 'Aeration | Detail';
      $page_description = 'Aeration Detail';
      $action = 'table_farmer';
      return view('l2validator.awd-captured',compact('plot',
                'page_title','page_description',
                'action','farmerplots','PipeInstallation','PipesLocation','Polygon','awd','farmerbenefitimg','AwdImage'));
    }


    /**
     * update polygon
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_polygon(Request $request){
        try {
            $pipe_data=PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->first();
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
            PipeInstallation::where('farmer_plot_uniqueid',$request->farmer_plot_uniqueid)->update([
                'ranges' =>$updated_polygon,
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




}
