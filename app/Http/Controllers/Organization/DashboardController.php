<?php

namespace App\Http\Controllers\Organization;

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

class DashboardController extends Controller
{
  /**
     * Display dashboard.
     *
     * @return \Illuminate\Http\Response
     */
  public function index(){
    // dd('adas');
    $page_title = 'Dashboard';
    $page_description = 'Dashboard';
    $logo = "images/logo.png";
    $logoText = "images/logo-text.png";
    $action = 'dashboard_1';
    $FarmersLocation = Farmer::where('onboarding_form','1')->where('organization_id',auth()->user()->id)->select('farmer_name','no_of_plots','latitude', 'longitude')->where('latitude','!=','0')
                        ->where('longitude','!=','0')->when(request(),function($q){

                            return $q;
                        })->get();
    return view('organization.dashboard.index', compact('page_title', 'page_description','action','logo','logoText','FarmersLocation'));
  }

    /**
     * Show the counting to farmer index page from ajax.
     *
     * @return \Illuminate\Http\Response
     */
    public function counting()
    {
        $farmers_count = Farmer::where('onboarding_form','1')->when(request(),function($q){
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

        $total_plot = FarmerPlot::whereHas('farmer',function($q){
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

        $total_plot_area = FarmerPlot::whereHas('farmer',function($q){
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
        $farmers_count = Farmer::where('onboarding_form','1')->when(request(),function($q){
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

        $total_plot = FarmerPlot::whereHas('farmer',function($q){
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

        $total_plot_area = FarmerPlot::whereHas('farmer',function($q){
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
