<?php

namespace App\Http\Controllers\Admin\l1validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\VendorLocation;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerPlotImage;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Models\RejectModule;
use App\Models\Minimumvalue;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\Setting;
use App\Exports\L1ApprovedIndividualExport;
use App\Exports\L1PendingIndividualExport;
use App\Exports\L1RejectedIndividualExport;
use App\Models\PlotStatusRecord;
class L1ValidatorController extends Controller
{

  /**
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
public function search($status)
{
    if($status == 'Pending'){
            $Farmers = FarmerPlot::with('farmer')->where('status',$status)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmer',function($q) use($status){
                if(auth()->user()->hasRole('L-1-Validator')){
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
        }else{
            $Farmers = FarmerPlot::with('farmer')->where('status',$status)->where('aprv_recj_userid',auth()->user()->id)->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->whereHas('farmer',function($q) use($status){
                if(auth()->user()->hasRole('L-1-Validator')){
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
        }
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
            $q->whereDate('date_survey','>=',request('start_date'));
        }
        if(request()->has('end_date') && !empty(request('end_date'))){
            $q->whereDate('date_survey','<=',request('end_date'));
        }
        return $q;
        })->orderBy('id','desc');

        return datatables()->of($plots)->make(true);
      }//end layoutout plot

    $others = "0";
    $page_title = 'Farmers';
    $page_description = 'Some description for the page';

    $action = 'table_farmer';
    $farmerscount = DB::table('farmers')->where('onboarding_form','1')->count();
    $farmers_Location = DB::table('farmers')->where('onboarding_form','1')->select('farmer_name','no_of_plots','latitude', 'longitude')->get();


    $total_plot_area = DB::table('farmers')->where('onboarding_form',1)->where('status_onboarding','Approved')->sum('total_plot_area');




        $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
        $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
        $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
        $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
        $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
        $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        // $onboarding_executive = DB::table('farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();

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

    return view('l1validator.all-plot', compact('page_title', 'page_description','action',
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
    $page_title = 'Farmers Details';
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
    return view('l1validator.all-show',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit'
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
    return view('l1validator.all-plot-detail',compact('plot','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','status'));
  }

  /**
   * Display a listing of the approved list of plots.
   *
   * @return \Illuminate\Http\Response
   */
  public function approved_lists()
  {
    if(auth()->user()->cannot('farmer')) abort(403, 'User does not have the right roles.');
	//Plot view
	  if(request()->ajax()){
  		$plots=FarmerPlot::with('farmer')->where('status','Approved')->where('aprv_recj_userid',auth()->user()->id)->whereHas('farmer',function($q){
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

  		$page_title = 'Farmers';
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
      $others = "0";
  		return view('l1validator.approved-plot',compact('page_title','page_description','action','seasons','states',
												  'districts','talukas','panchayats','villages','onboarding_executive','others'));
  }


  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function approved_show($id,$farmer_uniqueid)
  {
    if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $status = DB::table('farmer_plot_detail')->select('final_status','status')->where('id',$id)->first();
    $farmerplots =  FarmerPlot::with('UserApprovedRejected:id,name,email')->where('farmer_uniqueId',$farmer_uniqueid)->get();
    $Farmer = Farmer::find($farmerplots->first()->farmer_id);
    $Farmerplotsimages = FarmerPlotImage::where('farmer_unique_id',$farmer_uniqueid)->where('status','Approved')->get();
    $farmerbenefit = FarmerBenefit::where('farmer_uniqueId',$farmer_uniqueid)->get();
    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();
    $onboarding_plot = explode(',',$Farmer->status_onboarding_plot);
    $onboarding_plot_reject = explode(',',$Farmer->reject_onboarding_plot);
    $reject_module = RejectModule::all();
    $action = 'form_pickers';
    $page_title = 'Farmers Details';
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
    return view('l1validator.approved-show',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot','onboarding_plot_reject'
                      ,'reject_module','allrejected','allapproved','anyrejected','status'));
  }


  /**
   * Display a listing of the approved plot detail.
   *
   * @return \Illuminate\Http\Response
   */
  public function approved_detail($id){
    if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot=FarmerPlot::findOrFail($id);
    $status = $plot->status;//this is use for previous and next button
    $farmerplots_area =  FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $valicountplotapprv = FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('status','Approved')->count();
    $reject_module = RejectModule::all();
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
        $page_title = 'Farmer\'s Plot';
        $page_description = 'Farmer Plot Detail';
        $action = 'table_farmer';
    return view('l1validator.approved-plot-detail',compact('plot','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','status'));
  }

  /**
   * Display a listing of the approved list of plots.
   *
   * @return \Illuminate\Http\Response
   */
  public function pending_lists()
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
  		$plots=FarmerPlot::with('farmer')->where('status','Pending')->whereHas('farmer',function($q){
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

  		$page_title = 'Farmers';
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
  		return view('l1validator.pending-plot',compact('page_title','page_description','action','seasons','states',
												  'districts','talukas','panchayats','villages','onboarding_executive','status','others'));
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
    $farmerplots =  FarmerPlot::with('UserApprovedRejected:id,name,email')->where('farmer_uniqueId',$farmer_uniqueid)->get();
    $Farmer = Farmer::find($farmerplots->first()->farmer_id);
    $Farmerplotsimages = FarmerPlotImage::where('farmer_unique_id',$farmer_uniqueid)->where('status','Approved')->get();
    $farmerbenefit = FarmerBenefit::where('farmer_uniqueId',$farmer_uniqueid)->get();
    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();
    $onboarding_plot = explode(',',$Farmer->status_onboarding_plot);
    $onboarding_plot_reject = explode(',',$Farmer->reject_onboarding_plot);
    $reject_module = RejectModule::all();
    $action = 'form_pickers';
    $page_title = 'Farmers Details';
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
    return view('l1validator.pending-show',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot','onboarding_plot_reject'
                      ,'reject_module','allrejected','allapproved','anyrejected','status'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show_edit($id,$farmer_uniqueid)
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
    return view('l1validator.show-edit',compact('Farmer', 'farmerplots', 'farmerplots_first', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot',
                                              'Relationshipowner','states','districts','talukas','panchayats','villages','minimumvalues'));
  }

  public function plotEdit($id){
    if(auth()->user()->cannot('edit farmer')) abort(403, 'User does not have the right roles.');
    $plot=FarmerPlot::findOrFail($id);
    $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $reject_module = RejectModule::all();
    $page_title = 'Farmer\'s Plot';
    $page_description = 'Farmer Plot Detail';
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
        $minimumvalues = Minimumvalue::select('value','state_id')->where('status',1)->where('state_id',$plot->farmer->state_id)->first();
    return view('l1validator.plot-detail-edit',compact('plot','page_title','page_description','action','farmerplots','reject_module',
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
  {
      if(auth()->user()->cannot('edit farmer')) abort(403, 'User does not have the right roles.');
      if($request->type == 'UpdateBasicform'){
          $validatedData = $request->validate([
            'FarmerName' => 'required',
            'Mobile' => 'required',
          ]);
          $Farmer = Farmer::find($id);
          $Farmer->farmer_name = $request->FarmerName;
          $Farmer->mobile_reln_owner = $request->RelOwner;
          $Farmer->mobile_access = $request->MobileAccess;
          $Farmer->mobile = $request->Mobile;
          $Farmer->save();
          $plot_data = DB::table('farmer_plot_detail')->where('farmer_id',$id)->get();
          foreach($plot_data as $data){
             if($data->land_ownership == 'Own'){
                  $update_name = FarmerPlot::where('farmer_id',$id)->where('plot_no', $data->plot_no)->update(['actual_owner_name'=>$request->FarmerName]);
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
                      $area_acers = DB::table('farmer_plot_detail')->where('farmer_id',$id)->where('plot_no', $value['PlotNo'])->update(['area_in_acers'=>$value['area'] ]);
                  }
                  $farmerplot = FarmerPlot::where('farmer_uniqueId',$request->unique)->sum('area_in_acers');
                  $updatearea = Farmer::where('farmer_uniqueId',$request->unique)->update(['total_plot_area'=>number_format((float) $farmerplot, 2)]);
              }
              foreach($request->ownername as $value){
                  $area_acers = DB::table('farmer_plot_detail')->where('farmer_id',$id)->where('plot_no', $value['PlotNo'])->update(['actual_owner_name'=>$value['actual_owner_name'] ]);
                  $plot_data = DB::table('farmer_plot_detail')->where('farmer_id',$id)->where('plot_no', $value['PlotNo'])->first();
                  if($plot_data->land_ownership == 'Own'){
                      $update_name = Farmer::where('id',$id)->update(['farmer_name'=>$value['actual_owner_name']]);
                  }
                //   $plot_data = DB::table('farmer_plot_detail')->where('farmer_id',$id)->get();
                //   foreach($plot_data as $data){
                //      if($data->land_ownership == 'Own'){
                //           $update_name = FarmerPlot::where('farmer_id',$id)->where('plot_no', $data->plot_no)->update(['actual_owner_name'=>$value['actual_owner_name']]);
                //       }
                //   }
              }

              foreach($request->survey as $value){
                   $plots = DB::table('farmer_plot_detail')->where('farmer_id',$id)->where('plot_no', $value['PlotNo'])->update(['survey_no'=>$value['survey']]);
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
              $Farmer = Farmer::find($id);

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
   * Display a listing of the approved plot detail.
   *
   * @return \Illuminate\Http\Response
   */
  public function pending_detail($id){
    if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot=FarmerPlot::findOrFail($id);
    $status = $plot->status;//this is use for previous and next button
    $farmerplots_area =  FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $valicountplotapprv = FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('status','Approved')->count();
    $reject_module = RejectModule::all();
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
        $page_title = 'Farmer\'s Plot';
        $page_description = 'Farmer Plot Detail';
        $action = 'table_farmer';
    return view('l1validator.pending-plot-detail',compact('plot','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','status'));
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
        $Farmers =  FarmerPlot::with('farmer')->where('aprv_recj_userid',auth()->user()->id)->whereHas('farmer',function($q){
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
  		$plots=FarmerPlot::with('farmer')->where('aprv_recj_userid',auth()->user()->id)->where('status','Rejected')->whereHas('farmer',function($q){
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

  		$page_title = 'Farmers';
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
  		return view('l1validator.reject-plot',compact('page_title','page_description','action','seasons','states',
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
    $Farmer = Farmer::find($farmerplots->first()->farmer_id);
    $Farmerplotsimages = FarmerPlotImage::where('farmer_unique_id',$farmer_uniqueid)->where('status','Approved')->get();
    $farmerbenefit = FarmerBenefit::where('farmer_uniqueId',$farmer_uniqueid)->get();
    $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$farmer_uniqueid)->get();
    $onboarding_plot = explode(',',$Farmer->status_onboarding_plot);
    $onboarding_plot_reject = explode(',',$Farmer->reject_onboarding_plot);
    $reject_module = RejectModule::all();
    $action = 'form_pickers';
    $page_title = 'Farmers Details';
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
    return view('l1validator.reject-show',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot','onboarding_plot_reject'
                      ,'reject_module','allrejected','allapproved','anyrejected','status'));
  }


  /**
   * Display a listing of the approved plot detail.
   *
   * @return \Illuminate\Http\Response
   */
  public function reject_detail($id){
    if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
    $plot=FarmerPlot::findOrFail($id);
    $status = $plot->status;//this is use for previous and next button
    $farmerplots_area =  FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $farmerplots =  FarmerPlot::where('id', $id)->where('farmer_uniqueId',$plot->farmer_uniqueId)->get();
    $valicountplotapprv = FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('status','Approved')->count();
    $reject_module = RejectModule::all();
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
        $page_title = 'Farmer\'s Plot';
        $page_description = 'Farmer Plot Detail';
        $action = 'table_farmer';
    return view('l1validator.reject-plot-detail',compact('plot','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','status'));
  }

  /**
    * Download leased or carbon credit pdf.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function download($UniqueId,$type,$plotno){
      if($type == 'LEASED'){
        $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->first();
        $terms_and_conditions =  Setting::select('terms_and_conditions')->where('id',1)->first();
        $signature = FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no',$plotno)->select('sign_affidavit','sign_affidavit_date')->first();
        $data = ['terms_and_conditions' => $terms_and_conditions->terms_and_conditions,'Farmer'=>$Farmer];
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(view('affidavit',compact('signature','terms_and_conditions','Farmer'))->render());
        return $pdf->download('Leased_plot_'.$plotno.'_'.$UniqueId.'.pdf');
      }elseif($type == 'CARBON'){
        $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->first();
        $carbon_credit = Setting::select('carbon_credit')->where('id',1)->first(); // fetch carbon credit data from db
      //   $signature = Farmer::where('farmer_uniqueId',$UniqueId)->select('sign_carbon_credit')->first(); // fetch signature path
        $data = ['carbon_credit' => $carbon_credit->carbon_credit,'Farmer'=>$Farmer];
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(view('carboncredit',compact('data'))->render());
        return $pdf->download('carbon_'.$UniqueId.'.pdf');
      }
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


        if(request('record') == 'All'){
            // $filename = 'All-plot-'.Carbon::now().'.xlsx';

            // return Excel::download(new L1AllPlotExport('All' ,request()), $filename);
            //  return Excel::download(new FarmerExport(103390), $filename);



        }elseif(request('type') == 'onboarding'){
            $name = 'L-1-'.request('status').'_';
            if(request('status') == 'Pending'){
                $filename = $name.Carbon::now().'.xlsx';
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L1PendingExport',
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

                // return Excel::download(new L1PendingExport(request('unique') ? request('unique') : 'All' ,request()), $filename);  old method
            }elseif(request('status') == 'Approved'){
                $filename = $name.Carbon::now().'.xlsx';
                // return Excel::download(new L1ApprovedExport('All' ,request()), $filename);
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L1ApprovedExport',
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

                // return Excel::download(new L1PendingExport(request('unique') ? request('unique') : 'All' ,request()), $filename);  old method
            }elseif(request('status') == 'Rejected'){
                $filename = $name.Carbon::now().'.xlsx';
                // return Excel::download(new L1RejectedExport('All' ,request()), $filename);
                $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\L1RejectedExport',
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

                // return Excel::download(new L1PendingExport(request('unique') ? request('unique') : 'All' ,request()), $filename);  old method
            }
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
              return Excel::download(new L1ApprovedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Pending'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L1PendingIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Rejected'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L1RejectedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }
       }

     /**
       * Approve or Reject farmer status.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
       public function farmer_status(Request $request, $type,$UniqueId){
         if($type == "onboarding"){
           // $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['status_onboarding_plot'=> implode(',', $request->plots)]);
             foreach($request->plots as $no){
                 $Farmerplot =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no',$no['PlotNo'])
                                  ->UPDATE(['status'=>'Approved',
                                             'approve_comment'=>$no['ApproveComment'],
                                             'check_update'=>0,
                                             'aprv_recj_userid'=>auth()->user()->id,
                                             'appr_timestamp'=>Carbon::now()]);

                 $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           => $UniqueId,
                        'plot_no'                   => $no['PlotNo'],
                        'farmer_plot_uniqueid'      => $UniqueId.'P'.$no['PlotNo'],
                        'level'                     => 'L-1-Validator',
                        'status'                    => 'Approved',
                        'comment'                   => $no['ApproveComment'],
                        'timestamp'                 => Carbon::now(),
                        'user_id'                   => auth()->user()->id,
                        // 'approve_comment'           => $no['ApproveComment'],
                        // 'appr_timestamp'            => Carbon::now(),
                        // 'reject_comment'        => NULL,
                        // 'reject_timestamp'      => NULL,
                        // 'aprvd_recj_userid'      => auth()->user()->id,
                        //
                    ]);
             }//foreach end
           $FarmerAppPlot = FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('status','Approved')->count();
             if($FarmerAppPlot == $request->TotalPlot){
                  $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['status_onboarding'=>'Approved']);
                  return response()->json(['success' =>true, 'message'=>'Onboarding Approved', 'farmer'=>$Farmer],200);
             }
            $farmer = Farmer::where('farmer_uniqueId',$UniqueId)->first();
           if(!$farmer){
             return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
           }
           return response()->json(['success' =>true, 'message'=>'Plot approved', 'farmer'=>$farmer],200);
            //end approve
         }elseif($type == "reject"){// for reject
               //     $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['status_onboarding'=>'Rejected', 'reject_timestamp'=>carbon::now()]);
               //     if($request->reasons == 1  || $request->reasons == 2){//just to reject image
               //         $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
               //                         ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,
               //                                   'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),'aprv_recj_userid'=>auth()->user()->id]);
               //             $image = FarmerPlotImage::where('farmer_unique_id',$UniqueId)->where('plot_no',$request->plotno)->update(['status'=>'Rejected']);
               //   }else{
               //       $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
               //                             ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,
               //                                       'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),'aprv_recj_userid'=>auth()->user()->id]);
               //   }


                   // $Farmerplot =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                   //                         ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),
                   //                                   ]);
               // $Farmer =  DB::table('farmers')->where('farmer_uniqueId',$UniqueId)->first();
               // $Farmerplot =  DB::table('farmer_plot_detail')->where('farmer_uniqueId',$UniqueId)->get();
               // $allrejected=0;$count=1;
               // foreach($Farmerplot as $plot){
               //     if($plot->status == 'Rejected'){
               //         if($count == $Farmer->no_of_plots){
               //             $allrejected = 1;
               //             Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['status_onboarding'=>'Rejected', 'reject_timestamp'=>carbon::now()]);
               //         }
               //         $count++;
               //     }//if end
               // }//foreach end
               //     if(!$Farmer){
               //       return response()->json(['error' =>true, 'message'=>'Something went wrong'],500);
               //     }
               //     return response()->json(['success' =>true, 'farmer'=>$Farmer],200);

             if($request->reasons == 1  || $request->reasons == 2){//just to reject image
             $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                                       ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,
                                                 'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),'aprv_recj_userid'=>auth()->user()->id]);
               $image = FarmerPlotImage::where('farmer_unique_id',$UniqueId)->where('plot_no',$request->plotno)->update(['status'=>'Rejected']);

                $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           => $UniqueId,
                        'plot_no'                   => $request->plotno,
                        'farmer_plot_uniqueid'      => $UniqueId.'P'.$request->plotno,
                        'level'                     => 'L-1-Validator',
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
                        //
                    ]);


             }else{
                 $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                                       ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,
                                                 'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),'aprv_recj_userid'=>auth()->user()->id]);

                    $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           => $UniqueId,
                        'plot_no'                   => $request->plotno,
                        'farmer_plot_uniqueid'      => $UniqueId.'P'.$request->plotno,
                        'level'                     => 'L-1-Validator',
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
                        //
                    ]);
             }
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

         }//endif overall
       }

       /**
        * Show the counting to farmer index page from ajax.
        *
        * @return \Illuminate\Http\Response
        */
       public function counting()
       {
        $approved="0";
           $farmerscount = Farmer::where('onboarding_form','1')->when(request(),function($q){
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
                       $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                       $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                   }
               return $q;
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

           //new method
           if(auth()->user()->hasRole('L-1-Validator')){
               $approved = FarmerPlot::where('status', 'Approved')->where('aprv_recj_userid',auth()->user()->id)->whereHas('final_farmers',function($q){
                    $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
                     $q->whereIn('state_id',explode(',',$VendorLocation->state));
                     if(!empty($VendorLocation->district)){
                       $q->whereIn('district_id',explode(',',$VendorLocation->district));
                     }
                     if(!empty($VendorLocation->taluka)){
                       $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                     }
                 })->count();
           }

           //new method
           if(auth()->user()->hasRole('SuperAdmin')){
               $approved = FarmerPlot::where('status', 'Approved')->count();
           }

           if(auth()->user()->hasRole('L-1-Validator')){
               $pendings=FarmerPlot::with('farmer')->where('status', 'Pending')->whereHas('final_farmers',function($q){
                           $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
                           $q->whereIn('state_id',explode(',',$VendorLocation->state));
                           if(!empty($VendorLocation->district)){
                             $q->whereIn('district_id',explode(',',$VendorLocation->district));
                           }
                           if(!empty($VendorLocation->taluka)){
                             $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                           }
                           return $q;
               })->count();
           }



           $rejected = FarmerPlot::with('farmer')->where('status', 'Rejected')->where('aprv_recj_userid',auth()->user()->id)->whereHas('final_farmers',function($q){
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
                   if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
                       $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                       $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                   }
               return $q;
           })->count();



           $total_plot_area = FarmerPlot::where('status', 'Approved')->whereHas('final_farmers',function($q){
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
                   if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
                       $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                       $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                   }
               return $q;
           })->sum('area_acre_awd');

           $total_plot_area = number_format((float) $total_plot_area, 2);
           $others = "0";
           return response()->json(['success'=>true, 'farmercount'=>$farmerscount, 'plotcount'=>$farmers_count_plot,
                                       'approved'=>$approved??'0','pendings'=>$pendings??'0','rejected'=>$rejected,'totalarea'=>$total_plot_area,'others'=>$others],200);
       }

}
