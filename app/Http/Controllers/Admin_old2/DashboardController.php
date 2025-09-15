<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\FarmerPlot;
use DB;
use App\Models\VendorLocation;
use App\Models\ViewerLocation;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallation;
use App\Models\FarmerBenefit;
use App\Models\Aeration;
use App\Models\FinalFarmer;

class DashboardController extends Controller
{
  /**
     * Display dashboard.
     *
     * @return \Illuminate\Http\Response
     */
  public function index(){
    $page_title = 'Dashboard';
    $page_description = 'Dashboard';
    $logo = "images/logo.png";
    $logoText = "images/logo-text.png";
    $action = 'dashboard_1';
    $FarmersLocation = FinalFarmer::where('onboarding_form','1')->select('farmer_name','no_of_plots','latitude', 'longitude')->where('latitude','!=','0')
                        ->where('longitude','!=','0')->when(request(),function($q){
                                if(auth()->user()->hasRole('L-1-Validator') && !request()->has('state')  && !request()->has('district') && !request()->has('taluka') && !request()->has('panchayats') && !request()->has('village')){
                                    // condition for level-1 validator according to their location data stored in DB
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
                                  // condition for SuperValidator according to their location data stored in DB
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
                                    // condition for Viewer according to their location data stored in DB
                                    $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                                    if(!empty($ViewerLocation->state)){
                                      $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                                    }
                                    
                                }
                            return $q;
                        })->get();
    return view('dashboard.index', compact('page_title', 'page_description','action','logo','logoText',
                                          'FarmersLocation'));
  }

    /**
     * Show the counting to farmer index page from ajax.
     *
     * @return \Illuminate\Http\Response
     */
    public function counting()
    {
        $farmers_count = FinalFarmer::where('onboarding_form','1')->when(request(),function($q){
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

        $total_plot = FarmerPlot::whereHas('final_farmers',function($q){
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
                if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
                    $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                }
            return $q;
        })->count();


        $crop_data = FarmerCropdata::whereHas('farmerapproved',function($q){
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


        $pipeinstallation = PipeInstallation::whereHas('farmerapproved',function($q){
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

        $awd = Aeration::whereHas('farmerapproved',function($q){
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

        $Farmerbenefits = FarmerBenefit::whereHas('farmerapproved',function($q){
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

        $total_plot_area = FarmerPlot::whereHas('final_farmers',function($q){
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
                if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
                    $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                }
            return $q;
        })->sum('area_acre_awd');
        //calculating total area of all plot
        $total_plot_area = number_format((float) $total_plot_area, 2);
        $others = "0";
        return response()->json(['success'=>true, 'farmercount'=>$farmers_count, 'farmerplot'=>$total_plot,
                                    'crop_data'=>$crop_data,'pipeinstall'=>$pipeinstallation,'awd'=>$awd,'benefit'=>$Farmerbenefits,'totalarea'=>$total_plot_area],200);
    }


    /**
     * Show the counting to all farmer page from ajax.
     *
     * @return \Illuminate\Http\Response
     */
    public function all_farmers_counting()
    {
        $farmers_count = FinalFarmer::where('onboarding_form','1')->when(request(),function($q){
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

        $total_plot = FarmerPlot::whereHas('final_farmers',function($q){
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
                if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
                    $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                }
            return $q;
        })->count();


        $crop_data = FarmerCropdata::whereHas('farmerapproved',function($q){
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


        $pipeinstallation = PipeInstallation::whereHas('farmerapproved',function($q){
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

        $awd = Aeration::whereHas('farmerapproved',function($q){
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

        $Farmerbenefits = FarmerBenefit::whereHas('farmerapproved',function($q){
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

        $total_plot_area = FarmerPlot::whereHas('final_farmers',function($q){
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
                if(auth()->user()->hasRole('Viewer') && !request()->has('state')){
                    $ViewerLocation = ViewerLocation::where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$ViewerLocation->state));
                }
            return $q;
        })->sum('area_in_acers');
        //calculating total area of all plot
        $total_plot_area = number_format((float) $total_plot_area, 2);
        $others = "0";
        return response()->json(['success'=>true, 'farmercount'=>$farmers_count, 'total_plot' =>$total_plot,
                                    'crop_data'=>$crop_data,'pipeinstall'=>$pipeinstallation,'awd'=>$awd,'benefit'=>$Farmerbenefits,'totalarea'=>$total_plot_area],200);
    }
}
