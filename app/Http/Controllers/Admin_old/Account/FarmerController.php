<?php

namespace App\Http\Controllers\Admin\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Exports\FarmerExport;
use App\Models\FarmerPlotImage;
use App\Models\FarmerCropdata;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Setting;
use Dompdf;
use App\Models\VendorLocation;
use DB;
use App\Models\RejectModule;
use App\Models\ViewerLocation;
use App\Models\Minimumvalue;
use App\Models\FinalFarmer;
use App\Models\FinalFarmerBenefitImage;
use Storage;
use App\Exports\L1ApprovedIndividualExport;
use App\Exports\L1PendingIndividualExport;
use App\Exports\L1RejectedIndividualExport;

class FarmerController extends Controller
{

  

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function search_all()
  {
    $status='';
     if(request()->has('query') && !empty(request()->query)){//for search box in show page
        // $Farmers = Farmer::where('onboarding_form','1')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){
        $Farmers =  FarmerPlot::with('final_farmers')->where('farmer_plot_uniqueid','like','%'.request()->query.'%')->whereHas('final_farmers',function($q) use($status){
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

  }

    /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function search($status)
  {
     if(request()->has('query') && !empty(request()->query)){//for search box in show page
        // $Farmers = Farmer::where('onboarding_form','1')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){
        $Farmers =  FarmerPlot::with('farmer')->whereHas('farmer',function($q) use($status){
            $q->where('onboarding_form','1');
            if($status){
      		      if(auth()->user()->hasRole('L-2-Validator')){
      		           $q->where('final_status',$status);
      		           if($status == 'Rejected'){
      		               $q->where('status',$status);
      		           }else{
      		               $q->where('status','Approved');
      		           }
      		      }else{
      		           $q->where('status',$status);
      		      }
      		  }
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

  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
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


	  if(request()->ajax() && request()->has('layout') && request()->layout=='plot'){
  		$plots=FarmerPlot::with('farmer')->whereHas('farmer',function($q){
        $q->where('onboarding_form','1');
        //this is here just to see how it will filter if working correct then good
        if(auth()->user()->hasRole('SuperValidator')){
            $q->where('status_onboarding','Approved');
        }
  		  if(request()->has('status') && !empty(request()->status)){
  		      if(auth()->user()->hasRole('L-2-Validator')){

  		        //   dd(request()->status);
  		           $q->where('final_status',request()->status);
  		           if(request()->status == 'Rejected'){
  		               $q->where('status',request()->status);
  		           }else{
  		               $q->where('status','Approved');
  		           }
  		      }else{
  		           $q->where('status',request()->status);
  		      }
  		  }

  		//   dd('stop');
        if(request()->has('seasons') && !empty(request('seasons'))){
            $q->whereHas('CropData',function($u){
              $u->where('season','like',request('seasons'));
            });
        }
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
  		})->when(request(),function($a){
            //this is to fetch data who has validated
            if(request()->has('l1_validator') && !empty(request('l1_validator'))){
              $a->where('aprv_recj_userid',request('l1_validator'));
            }
          })->orderBy('id','desc');

  		// if(auth()->user()->hasRole('L-2-Validator')){
  		//   $plots=$plots->where('status','Approved');
  		// }

  		return datatables()->of($plots)->make(true);
	  }//end layoutout plot
	  if(request()->has('layout') && request()->layout=='plot'){
  		$page_title = 'final_farmers';
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
          // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
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
      }elseif(auth()->user()->hasRole('SuperValidator')){
          $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
          // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
            $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->when(request(),function($q){
              if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka') && !request()->has('panchayats') && !request()->has('village')){
                  $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                  $q->whereIn('state_id',explode(',',$VendorLocation->state));
                  $q->where('status_onboarding','Approved');
                  if(!empty($VendorLocation->district)){
                     $q->whereIn('district_id',explode(',',$VendorLocation->district));
                  }
                  if(!empty($VendorLocation->taluka)){
                     $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                  }
                  return $q;
              }
            })->get();
      }elseif(auth()->user()->hasRole('Viewer')){
          $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$ViewerLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$ViewerLocation->village))->get();
          // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
            $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->when(request(),function($q){
              if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')){
                  $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                  $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                  return $q;
              }
            })->get();
      }else{//for admin data
          $states = DB::table('states')->where('status',1)->get();
          $districts = DB::table('districts')->where('status',1)->get();
          $talukas = DB::table('talukas')->where('status',1)->get();
          $panchayats = DB::table('panchayats')->get();
          $villages = DB::table('villages')->get();
         $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
      }
  		$seasons = DB::table('seasons')->get();
        $l1_validators =   User::whereHas('roles', function($q){
                              $q->whereIn('name',['L-1-Validator']);//fetch user from users table hasrole SuperValidator  L-1-Validator
                          })->where('status',1)->orderBy('created_at','desc')->get();
  		
      $status = request()->status;
      $others = "0";
  		return view('admin.farmers.plot',compact('page_title','page_description','action','seasons','states',
												  'districts','talukas','panchayats','villages','onboarding_executive','status','others','l1_validators'));
	  }
	  // end Plot view
    $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
    if(request()->ajax()){
            if(request()->has('query') && !empty(request()->query)){//for search box in show page
                $Farmers = Farmer::where('onboarding_form','1')->where('farmer_uniqueId','like','%'.request()->get('query').'%')->limit(10)->when(request(),function($q){
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
              //start code for datatable
          $Farmers = Farmer::where('onboarding_form','1')->with('CropData')->when(request(),function($q){
              if(request()->has('seasons') && !empty(request('seasons'))){
                  $q->whereHas('CropData',function($u){
                    $u->where('season','like',request('seasons'));
                  });
              }

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
                   $q->where('status_onboarding','like',request('farmer_status'));
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
          $columns=request()->get('columns')??[];
          $dt= datatables($Farmers);
          if(count($columns)>0){
              foreach($columns as $column){
                  if (str_contains($column['name'], 'surveyImage.')) {
                      $dt->addColumn($column['data'],function($Farmers)use($column){
                          return $Farmers->surveyImage->{$column['data']};
                      });
                  }
                  else{
                      //to do
                  }
              }
          }
          return $dt->make(true);
      }//ajax end

    $farmers = DB::table('final_farmers')->where('onboarding_form','1')->orderBy('created_at','DESC')->get();


    $page_title = 'final_farmers';
    $page_description = 'Some description for the page';
    $action = 'table_farmer';
    $farmerscount = DB::table('final_farmers')->where('onboarding_form','1')->count();
    $farmers_Location = DB::table('final_farmers')->where('onboarding_form','1')->select('farmer_name','no_of_plots','latitude', 'longitude')->get();
    $cropdata = DB::table('final_farmers')->where('cropdata_form',1)->count();
    $pipeinstallation = DB::table('final_farmers')->where('status_pipes',1)->count();
    $awd = DB::table('final_farmers')->where('status_awd',1)->count();
    $Farmerbenefits = DB::table('final_farmers')->where('benefit_form',1)->count();
    $others = "0";
    $total_plot_area = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding','Approved')->sum('total_plot_area');
    $pendings = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding', 'Pending')->count();
    $approved = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding', 'Approved')->count();
    $rejected = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding', 'Rejected')->count();
    $seasons = DB::table('seasons')->get();
   //adding condition here for if roles is admin or vemdor. For vendor according to this varying the data based on location value stored in DB vendorloaction
    //all data will be showed for admin
    if(auth()->user()->hasRole('L-1-Validator')){
        $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
        $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
        $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
        $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
        $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
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
       $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
    }
    $benefits = DB::table('benefits')->get();
    $cropvariety = DB::table('cropvarietys')->get();
    $country = DB::table('countries')->get();
    return view('admin.farmers.index', compact('page_title', 'page_description','action',
                                          'farmerscount','cropdata','pipeinstallation','awd','Farmerbenefits',
                                          'others','farmers_Location','seasons','states','districts','talukas',
                                          'panchayats','villages','onboarding_executive','total_plot_area','pendings', 'approved', 'rejected'));

  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function all_plot()
  {
    //Plot view
      if(request()->ajax()){
        $plots = FinalFarmer::with('ApprvFarmerPlot')->when(request(),function($q){
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
            //  $q->where('status','like',request('farmer_status'));
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

    $status = request()->status;
    $others = "0";
    $page_title = 'final_farmers';
    $page_description = 'Some description for the page';
    $action = 'table_farmer';
    $farmerscount = DB::table('final_farmers')->where('onboarding_form','1')->count();
    $farmers_Location = DB::table('final_farmers')->where('onboarding_form','1')->select('farmer_name','no_of_plots','latitude', 'longitude')->get();

    $others = "0";
    $total_plot_area = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding','Approved')->sum('total_plot_area');
    $pendings = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding', 'Pending')->count();
    $approved = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding', 'Approved')->count();
    $rejected = DB::table('final_farmers')->where('onboarding_form',1)->where('status_onboarding', 'Rejected')->count();

   //adding condition here for if roles is admin or L-1-Validator. For vendor according to this varying the data based on location value stored in DB vendorloaction
    //all data will be showed for admin

    if(auth()->user()->hasRole('L-1-Validator')){
        $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
        $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$VendorLocation->state))->get();
        $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$VendorLocation->district))->get();
        $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$VendorLocation->taluka))->get();
        $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$VendorLocation->panchayat))->get();
        $villages = DB::table('villages')->whereIn('id',explode(',',$VendorLocation->village))->get();
        // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();

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

    }elseif(auth()->user()->hasRole('Viewer')){
          $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
          $states = DB::table('states')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->state))->get();
          $districts = DB::table('districts')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->district))->get();
          $talukas = DB::table('talukas')->where('status',1)->whereIn('id',explode(',',$ViewerLocation->taluka))->get();
          $panchayats = DB::table('panchayats')->whereIn('id',explode(',',$ViewerLocation->panchayat))->get();
          $villages = DB::table('villages')->whereIn('id',explode(',',$ViewerLocation->village))->get();
          // $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
            $onboarding_executive =Farmer::where('onboarding_form','1')->groupBy('surveyor_name')->when(request(),function($q){
              if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')){
                  $ViewerLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                  $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                  return $q;
              }
            })->get();
      }else{//for admin data
        $states = DB::table('states')->where('status',1)->get();
        $districts = DB::table('districts')->where('status',1)->get();
        $talukas = DB::table('talukas')->where('status',1)->get();
        $panchayats = DB::table('panchayats')->get();
        $villages = DB::table('villages')->get();
       $onboarding_executive = DB::table('final_farmers')->where('onboarding_form',1)->groupBy('surveyor_name')->get();
    }
    return view('admin.farmers.all-plot', compact('page_title', 'page_description','action',
                                          'farmerscount',
                                          'others','farmers_Location','states','districts','talukas',
                                          'panchayats','villages','onboarding_executive','total_plot_area','pendings', 'approved', 'rejected'));

  }

    /**
     * Show the counting to farmer index page from ajax.
     *
     * @return \Illuminate\Http\Response
     */
    public function counting()
    {
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

        $farmers_count_plot = FarmerPlot::whereHas('farmer',function($q){
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

        $approved = Farmer::where('onboarding_form','1')->where('status_onboarding', 'Approved')->when(request(),function($q){
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

        //new method
        if(auth()->user()->hasRole('L-1-Validator')){
            $approved = FarmerPlot::where('status', 'Approved')->where('aprv_recj_userid',auth()->user()->id)->count();
        }
        //new method
        if(auth()->user()->hasRole('SuperAdmin')){
            $approved = FarmerPlot::where('status', 'Approved')->count();
        }
        if(auth()->user()->hasRole('L-2-Validator')){  //new method
            $approved = FinalFarmer::where('final_status_onboarding', 'Approved')->where('L2_appr_userid',auth()->user()->id)->count();
        }

        $pendings = Farmer::where('onboarding_form','1')->where('status_onboarding', 'Pending')->with('CropData')->when(request(),function($q){
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

        if(auth()->user()->hasRole('L-1-Validator')){
            $pendings=FarmerPlot::with('farmer')->where('status', 'Pending')->whereHas('farmer',function($q){
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
        if(auth()->user()->hasRole('L-2-Validator')){
            $pendings = FarmerPlot::where('final_status', 'Pending')->where('status','Approved')->count();
        }

        $rejected = FarmerPlot::with('farmer')->where('status', 'Rejected')->whereHas('farmer',function($q){
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

        if(auth()->user()->hasRole('L-1-Validator')){
            // $rejected = FarmerPlot::where('status', 'Rejected')->where('aprv_recj_userid',auth()->user()->id)->count();
            // $pendings=FarmerPlot::with('farmer')->where('status', 'Rejected')->where('aprv_recj_userid',auth()->user()->id)->whereHas('farmer',function($q){
            //             $VendorLocation = VendorLocation::where('user_id',auth()->user()->id)->first();
            //             $q->whereIn('state_id',explode(',',$VendorLocation->state));
            //             if(!empty($VendorLocation->district)){
            //               $q->whereIn('district_id',explode(',',$VendorLocation->district));
            //             }
            //             if(!empty($VendorLocation->taluka)){
            //               $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
            //             }
            //             return $q;
            // })->count();
        }
        if(auth()->user()->hasRole('L-2-Validator')){
            $rejected = FarmerPlot::where('final_status', 'Rejected')->where('finalreject_userid',auth()->user()->id)->count();
        }


        $total_plot_area = FarmerPlot::where('status', 'Approved')->whereHas('farmer',function($q){
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
        })->sum('area_in_acers');

        $total_plot_area = number_format((float) $total_plot_area, 2);
        $others = "0";
        return response()->json(['success'=>true, 'farmercount'=>$farmerscount, 'plotcount'=>$farmers_count_plot,
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
      return view('admin.farmers.all-show',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit'
                        ,'reject_module','allrejected','allapproved','anyrejected'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,$farmer_uniqueid)
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
        //   dd($farmerplots);
          $Farmer->total_area_acres_of_guntha = $total_area_acres;
      }

      return view('admin.farmers.show',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot','onboarding_plot_reject'
                        ,'reject_module','allrejected','allapproved','anyrejected','status'));
    }

    public function all_plot_detail($id){
      if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
      $plot = FinalFarmer::where('id',$id)->first();
   
      $plotdetail=FarmerPlot::findOrFail($id);
      if(auth()->user()->hasRole('L-1-Validator')){
        $status = $plotdetail->status;//this is use for previous an next button
      }elseif(auth()->user()->hasRole('L-2-Validator')){
        $status = $plotdetail->final_status;//this is use for previous an next button
      }else{
        $status = $plotdetail->final_status;
      }
      
      $plot = FinalFarmer::where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid)->first();
      $farmerplots_area =  FarmerPlot::where('farmer_plot_uniqueid',$plot->farmer_plot_uniqueid)->get();
      $farmerplots =  FarmerPlot::where('farmer_uniqueId',$plot->farmer_plot_uniqueid)->get();
      $valicountplotapprv = FarmerPlot::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('status','Approved')->count();
      $finalcountplotapprv = FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('final_status_onboarding','Approved')->count();
      $finalfarmers = FinalFarmer::where('farmer_uniqueId',$plot->farmer_uniqueId)->where('final_status_onboarding','Approved')->first();
      $reject_module = RejectModule::all();
      $cropdata = FarmerCropdata::with('PlotCropDetails')->where('farmer_plot_uniqueid',$plotdetail->farmer_plot_uniqueid)->get();
      $page_title = 'Farmer\'s Plot';
      $page_description = 'Farmer Plot Detail';
      $action = 'table_farmer';
         $guntha = 0.025000;
        //  $guntha = 0.025;
        $farmerbenefitimg = FarmerBenefitImage::select('farmer_uniqueId','path')->where('farmer_uniqueId',$plotdetail->farmer_uniqueId)->get();
      if($finalfarmers->state_id == 36){
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
      return view('admin.farmers.all-plot-detail',compact('plot','plotdetail','cropdata','farmerbenefitimg','finalfarmers','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','finalcountplotapprv','status'));
    }

    public function plot($id){
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

      if($plot->final_farmers->state_id == 36){
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
      return view('admin.farmers.plot-detail',compact('plot','page_title','page_description','action','farmerplots','reject_module','valicountplotapprv','finalcountplotapprv','status'));
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
      return view('admin.farmers.plot-detail-edit',compact('plot','page_title','page_description','action','farmerplots','reject_module',
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
      return view('admin.farmers.edit',compact('Farmer', 'farmerplots', 'farmerplots_first', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot',
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
            return Excel::download(new FarmerExport(103390), $filename);
        }elseif(request('type') == 'onboarding'){

            if(request('status') == 'Rejected' && request('from') == 'L2-Validator'){
                $filename = 'Rejected_'.Carbon::now().'.xlsx';
              // return Excel::download(new AdminL2RejectExport(request('unique') ? request('unique') : 'All' ,request()), $filename);

              $payload=[
                    'uuid'=>\Str::uuid(),
                    'data'=>[
                        'command'=>'\App\Exports\AdminL2RejectExport',
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


            }elseif(request('status') == 'Pending' && request('from') == 'L2-Validator'){
              $filename = 'Pending_'.Carbon::now().'.xlsx';
                // return Excel::download(new L2PendingExport(request('unique') ? request('unique') : 'All' ,request()), $filename);

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


            }elseif(request('record') == 'All' && request('type') == 'onboarding'){
                $filename = 'Onboarding_'.Carbon::now().'.xlsx';
                // dd($filename);
                 // return Excel::download(new AllOnboardingExport(request('unique') ? request('unique') : 'All' ,request()), $filename);
                $payload=[
                  'uuid'=>\Str::uuid(),
                  'data'=>[
                      'command'=>'\App\Exports\AllOnboardingExport',
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
            }else{
                $filename = 'Onboarding_'.Carbon::now().'.xlsx';
                // dd($filename);
                 // return Excel::download(new OnboardingExport(request('unique') ? request('unique') : 'All' ,request()), $filename);
                  $payload=[
                        'uuid'=>\Str::uuid(),
                        'data'=>[
                            'command'=>'\App\Exports\OnboardingExport',
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
              return Excel::download(new L1ApprovedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Pending'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('final_farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L1PendingIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }elseif($status == 'Rejected'){
              $filename = $unique_id.'_'.$plot_no.'_'.Carbon::now().'.xlsx';
              $state_id = DB::table('final_farmers')->where('farmer_uniqueId', $unique_id)->first();
              return Excel::download(new L1RejectedIndividualExport($type, $unique_id, $plot_no, $status, $state_id), $filename);
          }
       }

    /**
      * Download leased or carbon credit pdf.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
      public function download($UniqueId,$type,$plotno){
        if($type == 'LEASED'){
          $Farmer = FinalFarmer::where('farmer_uniqueId',$UniqueId)->where('plot_no',$plotno)->first();
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
          return $pdf->download('carbon_'.$UniqueId.'.pdf');
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
                                    ->UPDATE(['status'=>'Approved','approve_comment'=>$no['ApproveComment'],'check_update'=>0,'aprv_recj_userid'=>auth()->user()->id,'appr_timestamp'=>Carbon::now()]);
              }
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
                // $Farmer =  DB::table('final_farmers')->where('farmer_uniqueId',$UniqueId)->first();
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
              }else{
                  $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                                        ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,
                                                  'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),'aprv_recj_userid'=>auth()->user()->id]);
              }
                $Farmer =  DB::table('final_farmers')->where('farmer_uniqueId',$UniqueId)->first();
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
          * Approve or Reject farmer status.
          *
          * @param  int  $id
          * @return \Illuminate\Http\Response
          */
          public function final_farmer_status(Request $request, $type,$UniqueId){
            if($type == "finalonboarding"){
              // $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['status_onboarding_plot'=> implode(',', $request->plots)]);


// FinalFarmer
                foreach($request->plots as $no){
                    $Farmerplot =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no',$no['PlotNo'])
                                      ->UPDATE(['final_status'=>'Approved','check_update'=>0,
                                       'finalaprv_timestamp'=>carbon::now(),'finalaprv_remark'=>$no['FinalApproveComment'], 'finalappr_userid'=>auth()->user()->id]);
                }

                // $FarmerAppPlot = FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('final_status','Approved')->count();
                // if($FarmerAppPlot == $request->TotalPlot){
                //      $Farmer = Farmer::where('farmer_uniqueId',$UniqueId)->UPDATE(['final_status_onboarding'=>'Approved']);
                //      return response()->json(['success' =>true, 'message'=>'Onboarding Approved', 'farmer'=>$Farmer],200);
                // }

// dd($request->plots[0], $request->plots[0]['PlotNo']);
               $CheckFinalStatus = DB::table('farmer_plot_detail')->where('farmer_uniqueId',$UniqueId)->where('plot_no',$request->plots[0]['PlotNo'])->where('final_status','Approved')->first();
               $farmer = Farmer::where('farmer_uniqueId',$UniqueId)->first();

            //   dd($request->all(), $type,$UniqueId,$farmer,$CheckFinalStatus);
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
                                          ->UPDATE(['status'=>'Rejected','final_status'=>'Rejected','reason_id'=>$request->reasons,
                                                    'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),
                                                    'aprv_recj_userid'=>null,'finalreject_timestamp'=>Carbon::now(),'finalreject_userid'=>auth()->user()->id,
                                                  'approve_comment'=>Null,'appr_timestamp'=>Null,]);
                              $image = FarmerPlotImage::where('farmer_unique_id',$UniqueId)->where('plot_no',$request->plotno)->update(['status'=>'Rejected']);
                    }else{
                        $plot_status_update =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                                              ->UPDATE(['status'=>'Rejected','final_status'=>'Rejected','reason_id'=>$request->reasons,
                                                        'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),
                                                        'aprv_recj_userid'=>null,'finalreject_timestamp'=>Carbon::now(),'finalreject_userid'=>auth()->user()->id,
                                                      'approve_comment'=>Null,'appr_timestamp'=>Null,]);
                    }


                      // $Farmerplot =  FarmerPlot::where('farmer_uniqueId',$UniqueId)->where('plot_no', $request->plotno)
                      //                         ->UPDATE(['status'=>'Rejected','reason_id'=>$request->reasons,'reject_comment'=>$request->rejectcomment,'reject_timestamp'=>Carbon::now(),
                      //                                   ]);
                  $Farmer =  DB::table('final_farmers')->where('farmer_uniqueId',$UniqueId)->first();
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
              //     $Farmer =  DB::table('final_farmers')->where('farmer_uniqueId',$UniqueId)->first();
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
           $page_title = 'Benefits';
     $page_description = 'Some description for the page';
     $action = 'table_landownerships';

           return view('admin.farmers.trash',compact('final_farmers','page_title','page_description','action'));
       }


       public function final_approved_record($request, $type,$UniqueId,$farmer,$CheckFinalStatus){
         // dd($farmer,$CheckFinalStatus);


         $finalfarmer = new FinalFarmer;
         $finalfarmer->surveyor_id  = $farmer->surveyor_id;
         $finalfarmer->surveyor_name= $farmer->surveyor_name;
         $finalfarmer->surveyor_email= $farmer->surveyor_email;
         $finalfarmer->surveyor_mobile= $farmer->surveyor_mobile;
         $finalfarmer->farmer_uniqueId= $farmer->farmer_uniqueId;
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

         //call image from S3 bucket
            $Signimg = Storage::disk('s3')->allFiles(config('storagesystems.store').'/'.$farmer->farmer_uniqueId);
            $sign_array_count = count($Signimg);
            $count = 1;
                for ($s = 0; $s <= $sign_array_count; $s++){
                    $signoldpathfile = explode('/',$farmer->signature);//seprate file name
                    if($farmer->signature == $Signimg[$s]){
                        $S3_sign_old_path = $Signimg[$s];//store old of the s3 bucket for signature
                        $new_sign_S3_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.'Signature'.'/'.$signoldpathfile[2];
                        $newpathfroms3 =  Storage::disk('s3')->copy($S3_sign_old_path, $new_sign_S3_path);
                        break;
                    }
                }//forloop end
            //call s3 image end

         $finalfarmer->signature        = $new_sign_S3_path;//$farmer->signature; new  signature path will be stored here
         $finalfarmer->signature_old        = $farmer->signature; //and old signature path will be stored here


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


            //call image from S3 bucket
            $images = Storage::disk('s3')->allFiles(config('storagesystems.store').'/'.$farmer->farmer_uniqueId);
            $img_array_count = count($images);
            $count = 1;
                for ($x = 0; $x <= $img_array_count; $x++){

                    // $oldpath = explode('/',$img->path);
                    $S3path = explode('/',$images[$x]);



                    // if($oldpath[2] == $S3path[2]){
                    if($img->path == $images[$x]){
                        $S3_old_path = $images[$x];
                        $new_S3_path = config('storagesystems.final_store').'/'.$farmer->farmer_uniqueId.'/'.'P'.$img->plot_no.'/'.$S3path[2];
                        $newpathfroms3 =  Storage::disk('s3')->copy($S3_old_path, $new_S3_path);
                        break;
                    }
                }//forloop end
            //call s3 image end

            $plotimg->path  =  $new_S3_path; // new path for plot images
            $plotimg->oldpath  =  $img->path; // old path for plot images
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

       public function showtrash($id,$farmer_uniqueid)
   {
     if(auth()->user()->cannot('show farmer')) abort(403, 'User does not have the right roles.');
     $Farmer = Farmer::find($id);
     $farmerplots =  FarmerPlot::with('UserApprovedRejected:id,name,email')->where('farmer_id', $id)->where('farmer_uniqueId',$farmer_uniqueid)->get();
     $Farmerplotsimages = FarmerPlotImage::where('farmer_id', $id)->where('farmer_unique_id',$farmer_uniqueid)->where('status','Approved')->get();
   //   $farmerplots_first = FarmerPlot::where('farmer_id', $id)->where('farmer_uniqueId',$farmer_uniqueid)->first();
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
         foreach($farmerplots as $plot){
             $area = number_format((float)$plot->area_in_acers, 2, '.', '');
             $split = explode('.', $area);//spliting area
             $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
             $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
             $conversion = explode('.', $result); // split result
             $conversion = $conversion[1]??0;
             $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
             $plot->convertedacres = $acers;
         }
     }
     if(request()->has('old')){
       return view('admin.farmers.show_old',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','onboarding_plot'));
     }
     return view('admin.farmers.trashshow',compact('Farmer', 'farmerplots', 'farmerbenefitimg', 'Farmerplotsimages', 'action','page_title','farmerbenefit','onboarding_plot','onboarding_plot_reject'
                       ,'reject_module','allrejected','allapproved','anyrejected'));
   }

        /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_trash(Request $request, $id)
    {
      try {
            $trash = Farmer::where('onboarding_form', '0')->where('farmer_uniqueId', $request->unique)->forceDelete();
            FarmerPlot::where('farmer_uniqueId', $request->unique)->forceDelete();
            FarmerPlotImage::where('farmer_unique_id', $request->unique)->forceDelete();

            if(!$trash){
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_trashall()
    {
      try {
            $farmers  = Farmer::where('onboarding_form', '0')->where('status_onboarding','Pending')->get();
            foreach($farmers as $farmer){
                $trash = Farmer::where('onboarding_form', '0')->where('farmer_uniqueId', $farmer->farmer_uniqueId)->forceDelete();
                FarmerPlot::where('farmer_uniqueId', $farmer->farmer_uniqueId)->forceDelete();
                FarmerPlotImage::where('farmer_unique_id', $farmer->farmer_uniqueId)->forceDelete();
            }
            if(!$farmers){
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

     public function updte_unique_plot(){
        try {
            $farmerplots =FarmerPlot::select('id','farmer_uniqueId','farmer_plot_uniqueid','plot_no')->with('farmer')->whereHas('farmer',function($q){
                $q->where('onboarding_form','1');
                return $q;
            })->get();

            // $farmerplots = DB::table('final_farmers')->where('onboarding_form','1')->select('farmer_uniqueId')->get();


            // dd($farmerplots);
            foreach($farmerplots as $farmerplot){
                $farmerplot->farmer_plot_uniqueid = $farmerplot->farmer_uniqueId.'P'.$farmerplot->plot_no;
                $farmerplot->save();
            }
            return response()->json(['success'=>true,'farmer' => $farmerplots],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
     }
}
