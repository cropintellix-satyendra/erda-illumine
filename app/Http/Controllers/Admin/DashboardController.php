<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Company;
use App\Models\State;
use App\Models\Organization;
use App\Models\FarmerPlot;
use App\Models\PipeInstallationPipeImg;
use DB;
use Carbon\Carbon;
use App\Models\VendorLocation;
use App\Models\ViewerLocation;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallation;
use App\Models\FarmerBenefit;
use App\Models\Aeration;
use App\Models\Benefit;
use App\Models\District;
use App\Models\FinalFarmer;
use App\Models\Polygon;
use App\Models\Taluka;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{

    public function clear(Request $request)
    {
        Artisan::call('cache:clear');
        return response()->json(['success' => true, 'message' => 'Cache cleared successfully']);
    }
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

                        $states = State::select('id','name')->pluck('id')->toArray();
                        // dd($states);

                      // Initialize arrays to store counts for each state
                      $approveCounts = [];
                      $rejectCounts = [];
                      $pendingCounts = [];

                      foreach ($states as $state) {
                        // dd($state);
                        $approveCounts[$state] = FinalFarmer::where('state_id', $state)
                            ->where('onboarding_form','1')
                            ->where('onboard_completed','!=','Processing')
                            ->where('final_status', 'Approved')
                            ->count();

                        $rejectCounts[$state] = FinalFarmer::where('state_id', $state)
                            ->where('onboarding_form','1')
                            ->where('onboard_completed','!=','Processing')
                            ->where('final_status', 'Rejected')
                            ->count();


                        $pendingCounts[$state] = FinalFarmer::where('state_id', $state)
                        ->where('onboarding_form','1')
                        ->where('onboard_completed','!=','Processing')
                        ->where('final_status', 'Pending')
                        ->where('plot_no',1)
                        ->count();
                      }



    return view('dashboard.index', compact('page_title', 'page_description','action','logo','logoText',
                                          'FarmersLocation', 'states', 'approveCounts', 'rejectCounts','pendingCounts'));
  }

    /**
     * Show the counting to farmer index page from ajax.
     *
     * @return \Illuminate\Http\Response
     */
    public function counting()
    {
        $farmers_count = FinalFarmer::where('onboarding_form','1')->where('onboard_completed','!=', 'Processing')->when(request(),function($q){
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
        })->distinct('farmer_uniqueId')->count();

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
        })->distinct('farmer_plot_uniqueid')->count();


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


        $pipeinstallation = PipeInstallationPipeImg::whereHas('farmerapproved',function($q){
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

  $polygon = Polygon::whereHas('farmerapproved',function($q){
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
                                  'crop_data'=>$crop_data,'pipeinstall'=>$pipeinstallation,'awd'=>$awd,'poly_data'=> $polygon,'benefit'=>$Farmerbenefits,'totalarea'=>$total_plot_area],200);
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
        })->distinct('farmer_plot_uniqueId')->count();

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
        })->distinct('farmer_plot_uniqueId')->count();


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



        $polygon = Polygon::whereHas('farmerapproved',function($q){
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



        $pipeinstallation = PipeInstallationPipeImg::whereHas('farmerapproved',function($q){
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

        $total_plot_area = FinalFarmer::where('onboarding_form', '1')
        ->sum('plot_area');
        $total_plot_area = number_format((float) $total_plot_area, 2);
        $others = "0";
        return response()->json(['success'=>true, 'farmercount'=>$farmers_count, 'total_plot' =>$total_plot,
                                    'crop_data'=>$crop_data,'pipeinstall'=>$pipeinstallation, 'poly_data'=>$polygon,'awd'=>$awd,'benefit'=>$Farmerbenefits,'totalarea'=>$total_plot_area],200);
    }






    public function farmer_counting(){
      // $groupby=FinalFarmer::select('state')->groupBy('state')->get();
      // dd($groupby);
      $andhra_pradesh = FinalFarmer::where('state_id','37')->where('deleted_at',Null)->count();
      $uttarakhand = FinalFarmer::where('state_id','38')->where('deleted_at',Null)->count();
      $maharashtra=FinalFarmer::where('state_id','41')->where('deleted_at',Null)->count();
      $odisha = FinalFarmer::where('state_id','43')->where('deleted_at',Null)->count();

      return response()->json(['success'=>true, 'west_bengal'=>$andhra_pradesh, 'telangana' =>$uttarakhand,
      'assam'=>$maharashtra  , 'odisha' => $odisha],200);
    }

    public function daily_data_entry_records(){
      $currentMonth = Carbon::now()->month;
      $currentYear = Carbon::now()->year;

          $register_farmers_count = FinalFarmer::whereYear('created_at', $currentYear)
          ->whereMonth('created_at', $currentMonth)
          ->count();
          $register_crop_data = FarmerCropdata::whereYear('created_at', $currentYear)
          ->whereMonth('created_at', $currentMonth)
          ->count();
          $register_polygon = PipeInstallation::whereYear('created_at', $currentYear)
          ->whereMonth('created_at', $currentMonth)
          ->count();
          $register_pipe_installation = PipeInstallationPipeImg::whereYear('created_at', $currentYear)
          ->whereMonth('created_at', $currentMonth)
          ->count();
          $register_areation = Aeration::whereYear('created_at', $currentYear)
          ->whereMonth('created_at', $currentMonth)
          ->count();
          $register_benifit = Benefit::whereYear('created_at', $currentYear)
          ->whereMonth('created_at', $currentMonth)
          ->count();

          // $register_benifit =;
       return response()->json(['success'=>true, 'register_farmers_count'=>$register_farmers_count, 'register_crop_data' =>$register_crop_data,
      'register_polygon'=>$register_polygon,'register_pipe_installation'=>$register_pipe_installation,'register_areation'=>$register_areation,
      'register_benifit'=>$register_benifit],200);

    }


    public function monthly_data_entry_records_in_6month(){
      $monthlyData = [];
      for ($i = 0; $i < 6; $i++) {
          $currentMonth = Carbon::now()->subMonths($i)->month;
          $currentYear = Carbon::now()->subMonths($i)->year;

          $register_farmers_count = FinalFarmer::whereYear('created_at', $currentYear)
              ->whereMonth('created_at', $currentMonth)
              ->count();
          $register_crop_data = FarmerCropdata::whereYear('created_at', $currentYear)
              ->whereMonth('created_at', $currentMonth)
              ->count();
          $register_polygon = PipeInstallation::whereYear('created_at', $currentYear)
              ->whereMonth('created_at', $currentMonth)
              ->count();
          $register_pipe_installation = PipeInstallationPipeImg::whereYear('created_at', $currentYear)
              ->whereMonth('created_at', $currentMonth)
              ->count();
          $register_areation = Aeration::whereYear('created_at', $currentYear)
              ->whereMonth('created_at', $currentMonth)
              ->count();
          $register_benifit = Benefit::whereYear('created_at', $currentYear)
              ->whereMonth('created_at', $currentMonth)
              ->count();

          $monthlyData[] = [
              'month' => Carbon::create($currentYear, $currentMonth)->format('M Y'),
              'register_farmers_count' => $register_farmers_count,
              'register_crop_data' => $register_crop_data,
              'register_polygon' => $register_polygon,
              'register_pipe_installation' => $register_pipe_installation,
              'register_areation' => $register_areation,
              'register_benifit' => $register_benifit,
          ];
      }
      return response()->json(['success'=>true, 'monthly_data' => $monthlyData], 200);
  }


  public function filter_pie_chart(Request $request){
    // dd($request->all());
      $formattedto_date=null;
      $formattedfrom_date=null;
      $state_id=null;
      $currentDate = Carbon::now()->toDateString();
    if($request->state_id){
    }
    if($request->to_date){
      $formattedto_date=date('Y-m-d', strtotime($request->to_date));
    }
    if($request->from_date){

      $formattedfrom_date=date('Y-m-d', strtotime($request->from_date));
    }

    if($request->forr =="farmers"){


    }else if($request->forr =="onboarding"){

    }
    else if($request->forr == "cropData") {

      if($formattedto_date){

        $cropdata_approved = \App\Models\FarmerCropdata::where('l2_status', 'Approved')
        ->where('deleted_at', Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $currentDate

        ])
        ->count();

        $cropdata_pending = \App\Models\FarmerCropdata::where('l2_status', 'Pending')
        ->where('deleted_at', Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $currentDate
        ])
        ->count();
        return response()->json(['success' => true, 'cropdata_approved' => $cropdata_approved, 'cropdata_pending' => $cropdata_pending,'forr'=>'cropData'], 200);

      }else if($formattedto_date && $formattedfrom_date){
        $cropdata_approved = \App\Models\FarmerCropdata::where('l2_status', 'Approved')
        ->where('deleted_at', Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $formattedfrom_date

        ])
        ->count();

    $cropdata_pending = \App\Models\FarmerCropdata::where('l2_status', 'Pending')
        ->where('deleted_at', Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $formattedfrom_date
        ])
        ->count();
        return response()->json(['success' => true, 'cropdata_approved' => $cropdata_approved, 'cropdata_pending' => $cropdata_pending,'forr'=>'cropData'], 200);
      }
     $cropdata_approved=\App\Models\FarmerCropdata::where('l2_status','Approved')->where('deleted_at',Null)->count();
     $cropdata_pending=\App\Models\FarmerCropdata::where('l2_status','Pending')->where('deleted_at',Null)->count();
    return response()->json(['success' => true, 'cropdata_approved' => $cropdata_approved, 'cropdata_pending' => $cropdata_pending,'forr'=>'cropData'], 200);



  }
    else if($request->forr =="polygon"){
      if($formattedto_date){
        $polygon_approve=\App\Models\PipeInstallation::where('l2_status','Approved')->where('deleted_at',Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $currentDate
        ])->count();
      $polygon_pending=\App\Models\PipeInstallation::where('l2_status','Pending')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $currentDate

      ])->count();
      $polygon_rejected=\App\Models\PipeInstallation::where('l2_status','Rejected')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $currentDate

      ])->count();
      return response()->json(['success' => true, 'polygon_approve' => $polygon_approve, 'polygon_pending' => $polygon_pending,'polygon_rejected'=>$polygon_rejected,'forr'=>'polygon'], 200);

      }else if($formattedto_date && $formattedfrom_date){
        $polygon_approve=\App\Models\PipeInstallation::where('l2_status','Approved')->where('deleted_at',Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $formattedfrom_date
        ])->count();
      $polygon_pending=\App\Models\PipeInstallation::where('l2_status','Pending')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date

      ])->count();
      $polygon_rejected=\App\Models\PipeInstallation::where('l2_status','Rejected')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date

      ])->count();
      return response()->json(['success' => true, 'polygon_approve' => $polygon_approve, 'polygon_pending' => $polygon_pending,'polygon_rejected'=>$polygon_rejected,'forr'=>'polygon'], 200);

      }else{
        $polygon_approve=\App\Models\PipeInstallation::where('l2_status','Approved')->where('deleted_at',Null)->count();
      $polygon_pending=\App\Models\PipeInstallation::where('l2_status','Pending')->where('deleted_at',Null)->count();
      $polygon_rejected=\App\Models\PipeInstallation::where('l2_status','Rejected')->where('deleted_at',Null)->count();
      return response()->json(['success' => true, 'polygon_approve' => $polygon_approve, 'polygon_pending' => $polygon_pending,'polygon_rejected'=>$polygon_rejected,'forr'=>'polygon'], 200);

      }


    }
    else if($request->forr =="pipeInstallation"){
  if($formattedto_date){
    $pipeinstallation_approve=\App\Models\PipeInstallationPipeImg::where('l2status','Approved')->where('deleted_at',Null)
    ->whereBetween('created_at', [
      $formattedto_date,
      $currentDate
    ])->count();
  $pipeinstallation_pending=\App\Models\PipeInstallationPipeImg::where('l2status','Pending')->where('deleted_at',Null)
  ->whereBetween('created_at', [
    $formattedto_date,
    $currentDate

  ])->count();
  $pipeinstallation_rejected=\App\Models\PipeInstallationPipeImg::where('l2status','Rejected')->where('deleted_at',Null)
  ->whereBetween('created_at', [
    $formattedto_date,
    $currentDate

  ])->count();
  return response()->json(['success' => true, 'pipeinstallation_approve' => $pipeinstallation_approve, 'pipeinstallation_pending' => $pipeinstallation_pending,'pipeinstallation_rejected'=>$pipeinstallation_rejected,'forr'=>'pipeInstallation'], 200);

  }else if($formattedto_date && $formattedfrom_date){
    $pipeinstallation_approve=\App\Models\PipeInstallationPipeImg::where('l2status','Approved')->where('deleted_at',Null)
    ->whereBetween('created_at', [
      $formattedto_date,
      $formattedfrom_date
    ])->count();
  $pipeinstallation_pending=\App\Models\PipeInstallationPipeImg::where('l2status','Pending')->where('deleted_at',Null)
  ->whereBetween('created_at', [
    $formattedto_date,
    $formattedfrom_date

  ])->count();
  $pipeinstallation_rejected=\App\Models\PipeInstallationPipeImg::where('l2status','Rejected')->where('deleted_at',Null)
  ->whereBetween('created_at', [
    $formattedto_date,
    $formattedfrom_date

  ])->count();
  return response()->json(['success' => true, 'pipeinstallation_approve' => $polygon_approve, 'pipeinstallation_pending' => $pipeinstallation_pending,'pipeinstallation_rejected'=>$pipeinstallation_rejected,'forr'=>'pipeInstallation'], 200);

  }else{
    $pipeinstallation_approve=\App\Models\PipeInstallationPipeImg::where('l2status','Approved')->where('deleted_at',Null)->count();
  $pipeinstallation_pending=\App\Models\PipeInstallationPipeImg::where('l2status','Pending')->where('deleted_at',Null)
 ->count();
  $pipeinstallation_rejected=\App\Models\PipeInstallationPipeImg::where('l2status','Rejected')->where('deleted_at',Null)->count();
  return response()->json(['success' => true, 'pipeinstallation_approve' => $pipeinstallation_approve, 'pipeinstallation_pending' => $pipeinstallation_pending,'pipeinstallation_rejected'=>$pipeinstallation_rejected,'forr'=>'pipeInstallation'], 200);

  }


    }
    else if($request->forr =="areation"){
      if($formattedto_date){
        $areation_approve=\App\Models\Aeration::where('l2_status','Approved')->where('deleted_at',Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $currentDate
        ])->count();
      $areation_pending=\App\Models\Aeration::where('l2_status','Pending')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $currentDate

      ])->count();
      $areation_rejected=\App\Models\Aeration::where('l2_status','Rejected')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $currentDate

      ])->count();
      return response()->json(['success' => true, 'areation_approve' => $areation_approve, 'areation_pending' => $areation_pending,'areation_rejected'=>$areation_rejected,'forr'=>'areation'], 200);

      }else if($formattedto_date && $formattedfrom_date){
        $areation_approve=\App\Models\Aeration::where('l2_status','Approved')->where('deleted_at',Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $formattedfrom_date
        ])->count();
      $areation_pending=\App\Models\Aeration::where('l2_status','Pending')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date

      ])->count();
      $areation_rejected=\App\Models\Aeration::where('l2_status','Rejected')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date

      ])->count();
      return response()->json(['success' => true, 'areation_approve' => $areation_approve, 'areation_pending' => $areation_pending,'areation_rejected'=>$areation_rejected,'forr'=>'areation'], 200);

      }else{
        $areation_approve=\App\Models\Aeration::where('l2_status','Approved')->where('deleted_at',Null)->count();
      $areation_pending=\App\Models\Aeration::where('l2_status','Pending')->where('deleted_at',Null)->count();
      $areation_rejected=\App\Models\Aeration::where('l2_status','Rejected')->where('deleted_at',Null)->count();
      return response()->json(['success' => true, 'areation_approve' => $areation_approve, 'areation_pending' => $areation_pending,'areation_rejected'=>$areation_rejected,'forr'=>'areation'], 200);

      }


    }else if($request->forr =="benifit"){
      if($formattedto_date){
        $benifits_approve=\App\Models\Aeration::where('l2_status','Approved')->where('deleted_at',Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $currentDate
        ])->count();
      $benifits_pending=\App\Models\Aeration::where('l2_status','Pending')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $currentDate

      ])->count();
      return response()->json(['success' => true, 'benifits_approve' => $benifits_approve, 'benifits_pending' => $benifits_pending,'forr'=>'benifit'], 200);

      }else if($formattedto_date && $formattedfrom_date){
        $benifits_approve=\App\Models\Aeration::where('l2_status','Approved')->where('deleted_at',Null)
        ->whereBetween('created_at', [
          $formattedto_date,
          $formattedfrom_date
        ])->count();
      $benifits_pending=\App\Models\Aeration::where('l2_status','Pending')->where('deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date

      ])->count();

      return response()->json(['success' => true, 'benifits_approve' => $benifits_approve, 'benifits_pending' => $benifits_pending,'forr'=>'benifit'], 200);

      }else{
        $benifits_approve=\App\Models\Aeration::where('l2_status','Approved')->where('deleted_at',Null)->count();
      $benifits_pending=\App\Models\Aeration::where('l2_status','Pending')->where('deleted_at',Null)->count();
      return response()->json(['success' => true, 'benifits_approve' => $benifits_approve, 'benifits_pending' => $benifits_pending,'forr'=>'benifit'], 200);

      }

    }else{
      return response()->json(['error'=>true, 'message' =>'Something Went Wrong'], 200);
    }


  }


  public function crop_data_count(){

    $states = State::select('id','name')->get();
    // dd($states);

  $approveCounts = [];
  $rejectCounts = [];
  $pendingCounts = [];

  foreach ($states as $state) {

    $approveCounts[$state->id] = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
    ->where('final_farmers.state_id', $state->id)
    ->where('farmer_cropdata.l2_status', 'Approved')
    ->count();
    // dd($state->id);

    $rejectCounts[$state->id] = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
    ->where('final_farmers.state_id', $state->id)
    ->where('farmer_cropdata.l2_status', 'Rejected')
    ->count();

    $pendingCounts[$state->id] = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                      ->where('final_farmers.state_id', $state->id)
                                      ->where('farmer_cropdata.l2_status', 'Pending')
                                      ->count();

  }

  return response()->json(['success' => true,
        'states' => $states,
        'approveCounts' => $approveCounts,
        'rejectCounts' => $rejectCounts,
        'pendingCounts' => $pendingCounts,
], 200);



  //   $cropassampendingcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //                                   ->where('final_farmers.state', 'Assam')
  //                                   ->where('farmer_cropdata.l2_status', 'Pending')
  //                                   ->count();

  //   $cropassamapprovedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('farmer_cropdata.l2_status', 'Approved')
  //   ->count();

  //   $cropassamrejectedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('farmer_cropdata.l2_status', 'Rejected')
  //   ->count();

  //   $cropwestbengalpendingcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //                                   ->where('final_farmers.state', 'West Bengal')
  //                                   ->where('farmer_cropdata.l2_status', 'Pending')
  //                                   ->count();

  //     $cropwestbengalpprovedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //     ->where('final_farmers.state', 'West Bengal')
  //     ->where('farmer_cropdata.l2_status', 'Approved')
  //     ->count();

  //     $cropwestbengalrejectedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //     ->where('final_farmers.state', 'West Bengal')
  //     ->where('farmer_cropdata.l2_status', 'Rejected')
  //     ->count();


  //     $croptelanganapendingcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //     ->where('final_farmers.state', 'Telangana')
  //     ->where('farmer_cropdata.l2_status', 'Pending')
  //     ->count();

  //   $croptelanganapprovedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //   ->where('final_farmers.state', 'Telangana')
  //   ->where('farmer_cropdata.l2_status', 'Approved')
  //   ->count();

  //   $croptelaganarejectedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //   ->where('final_farmers.state', 'Telangana')
  //   ->where('farmer_cropdata.l2_status', 'Rejected')
  //   ->count();

  //   $cropotherpendingcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  //   ->where('final_farmers.state', Null)
  //   ->where('farmer_cropdata.l2_status', 'Pending')
  //   ->count();

  // $cropotherpprovedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  // ->where('final_farmers.state', Null)
  // ->where('farmer_cropdata.l2_status', 'Approved')
  // ->count();

  // $cropotherrejectedcount = FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_id', '=', 'final_farmers.id')
  // ->where('final_farmers.state',Null)
  // ->where('farmer_cropdata.l2_status', 'Rejected')
  // ->count();
  // return response()->json(['success' => true, 'cropassampendingcount' => $cropassampendingcount,
  // 'cropassamapprovedcount' => $cropassamapprovedcount,'cropassamrejectedcount'=>$cropassamrejectedcount,'cropwestbengalpendingcount'=>$cropwestbengalpendingcount,'cropwestbengalpprovedcount'=>$cropwestbengalpprovedcount,
  // 'cropwestbengalrejectedcount'=>$cropwestbengalrejectedcount,'croptelanganapendingcount'=>$croptelanganapendingcount,'croptelanganapprovedcount'=>$croptelanganapprovedcount,'croptelaganarejectedcount'=>$croptelaganarejectedcount,'cropotherpendingcount'=>$cropotherpendingcount,
  // 'cropotherpprovedcount'=>$cropotherpprovedcount,'cropotherrejectedcount'=>$cropotherrejectedcount], 200);



  }


  public function polygon_data_count(){

    $states = State::select('id','name')->get();
    // dd($states);

  $approveCounts = [];
  $rejectCounts = [];
  $pendingCounts = [];

  foreach ($states as $state) {

    $approveCounts[$state->id] = Polygon::join('final_farmers', 'polygons.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                         ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('polygons.final_status', 'Approved')->count();
    $rejectCounts[$state->id] = Polygon::join('final_farmers', 'polygons.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                         ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('polygons.final_status','Rejected')->count();
    $pendingCounts[$state->id] = Polygon::join('final_farmers', 'polygons.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                          ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('polygons.final_status','Pending')->count();

  }

  return response()->json(['success' => true,
        'states' => $states,
        'approveCounts' => $approveCounts,
        'rejectCounts' => $rejectCounts,
        'pendingCounts' => $pendingCounts,
], 200);


  //   $polygon_assam_approve_count=PipeInstallation::where('state','Assam')->where('l2_status','Approved')->count();
  //   $polygon_assam_pending_count=PipeInstallation::where('state','Assam')->where('l2_status','Pending')->count();
  //   $polygon_assam_rejected_count=PipeInstallation::where('state','Assam')->where('l2_status','Rejected')->count();

  //   $polygon_westbengal_approve_count=PipeInstallation::where('state','West Bengal')->where('l2_status','Approved')->count();
  //   $polygon_westbengal_pending_count=PipeInstallation::where('state','West Bengal')->where('l2_status','Pending')->count();
  //   $polygon_westbengal_rejected_count=PipeInstallation::where('state','West Bengal')->where('l2_status','Rejected')->count();

  //   $polygon_telangana_approve_count=PipeInstallation::where('state','Telangana')->where('l2_status','Approved')->count();
  //   $polygon_telangana_pending_count=PipeInstallation::where('state','Telangana')->where('l2_status','Pending')->count();
  //   $polygon_telangana_rejected_count=PipeInstallation::where('state','Telangana')->where('l2_status','Rejected')->count();

  //   $polygon_other_approve_count=PipeInstallation::where('state',Null)->where('l2_status','Approved')->count();
  //   $polygon_other_pending_count=PipeInstallation::where('state',Null)->where('l2_status','Pending')->count();
  //   $polygon_other_rejected_count=PipeInstallation::where('state',Null)->where('l2_status','Rejected')->count();

  //   return response()->json(['success'=>true,'polygon_assam_approve_count'=>$polygon_assam_approve_count,'polygon_assam_pending_count'=>$polygon_assam_pending_count,
  //  'polygon_assam_rejected_count'=>$polygon_assam_rejected_count, 'polygon_westbengal_approve_count'=>$polygon_westbengal_approve_count,'polygon_westbengal_pending_count'=>$polygon_westbengal_pending_count,
  //  'polygon_westbengal_rejected_count'=>$polygon_westbengal_rejected_count,'polygon_telangana_approve_count'=>$polygon_telangana_approve_count,'polygon_telangana_pending_count'=>$polygon_telangana_pending_count,
  //  'polygon_telangana_rejected_count'=>$polygon_telangana_rejected_count,'polygon_other_approve_count'=>$polygon_other_approve_count,'polygon_other_pending_count'=>$polygon_other_pending_count,
  //  'polygon_other_rejected_count'=>$polygon_other_rejected_count
  // ]);

  }


  public function pipeinstallation_data_count(){

  $states = State::select('id','name')->get();
    // dd($states);

  $approveCounts = [];
  $rejectCounts = [];
  $pendingCounts = [];

  foreach ($states as $state) {

    $approveCounts[$state->id] = PipeInstallationPipeImg::join('final_farmers', 'pipe_installation_pipeimg.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
      ->where('final_farmers.state_id', $state->id)
      ->where('pipe_installation_pipeimg.l2status', 'Approved')
      ->count();
    $rejectCounts[$state->id] = PipeInstallationPipeImg::join('final_farmers', 'pipe_installation_pipeimg.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
      ->where('final_farmers.state_id', $state->id)
      ->where('pipe_installation_pipeimg.l2status', 'Rejected')
      ->count();
    $pendingCounts[$state->id] = PipeInstallationPipeImg::join('final_farmers', 'pipe_installation_pipeimg.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
      ->where('final_farmers.state_id', $state->id)
      ->where('pipe_installation_pipeimg.l2status', 'Pending')
      ->count();
  }

  return response()->json(['success' => true,
        'states' => $states,
        'approveCounts' => $approveCounts,
        'rejectCounts' => $rejectCounts,
        'pendingCounts' => $pendingCounts,
], 200);



  //   $pipe_assam_approve_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'Assam')
  //   ->where('pipe_installation_pipeimg.l2status', 'Approved')
  //   ->count();

  //   $pipe_assam_pending_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'Assam')
  //   ->where('pipe_installation_pipeimg.l2status', 'Pending')
  //   ->count();

  //   $pipe_assam_rejected_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'Assam')
  //   ->where('pipe_installation_pipeimg.l2status', 'Rejected')
  //   ->count();

  //   $pipe_westbengal_approve_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'West Bengal')
  //   ->where('pipe_installation_pipeimg.l2status', 'Approved')
  //   ->count();
  //   $pipe_westbengal_pending_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'West Bengal')
  //   ->where('pipe_installation_pipeimg.l2status', 'Pending')
  //   ->count();
  //   $pipe_westbengal_rejected_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'West Bengal')
  //   ->where('pipe_installation_pipeimg.l2status', 'Rejected')
  //   ->count();

  //   $pipe_telangana_approve_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'Telangana')
  //   ->where('pipe_installation_pipeimg.l2status', 'Approved')
  //   ->count();
  //   $pipe_telangana_pending_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'Telangana')
  //   ->where('pipe_installation_pipeimg.l2status', 'Pending')
  //   ->count();
  //   $pipe_telangana_rejected_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', 'Telangana')
  //   ->where('pipe_installation_pipeimg.l2status', 'Rejected')
  //   ->count();


  //   $pipe_other_approve_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state',Null)
  //   ->where('pipe_installation_pipeimg.l2status', 'Approved')
  //   ->count();
  //   $pipe_other_pending_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', Null)
  //   ->where('pipe_installation_pipeimg.l2status', 'Pending')
  //   ->count();
  //   $pipe_other_rejected_count=PipeInstallationPipeImg::join('pipe_installations', 'pipe_installation_pipeimg.farmer_uniqueId', '=', 'pipe_installations.farmer_uniqueId')
  //   ->where('pipe_installations.state', Null)
  //   ->where('pipe_installation_pipeimg.l2status', 'Rejected')
  //   ->count();

  //   return response()->json(['success'=>true,'pipe_assam_approve_count'=>$pipe_assam_approve_count,'pipe_assam_pending_count'=>$pipe_assam_pending_count,
  //  'pipe_assam_rejected_count'=>$pipe_assam_rejected_count, 'pipe_westbengal_approve_count'=>$pipe_westbengal_approve_count,'pipe_westbengal_pending_count'=>$pipe_westbengal_pending_count,
  //  'pipe_westbengal_rejected_count'=>$pipe_westbengal_rejected_count,'pipe_telangana_approve_count'=>$pipe_telangana_approve_count,'pipe_telangana_pending_count'=>$pipe_telangana_pending_count,
  //  'pipe_telangana_rejected_count'=>$pipe_telangana_rejected_count,'pipe_other_approve_count'=>$pipe_other_approve_count,'pipe_other_pending_count'=>$pipe_other_pending_count,
  //  'pipe_other_rejected_count'=>$pipe_other_rejected_count
  // ],200);
  }



  public function areation_data_count(){
    $states = State::select('id','name')->get();
    // dd($states);

  $approveCounts = [];
  $rejectCounts = [];
  $pendingCounts = [];

//   foreach ($states as $state) {

//     $aerationQuery = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
//         ->where('final_farmers.state_id', $state->id)
//         ->where('aerations.aeration_no', '1');

//     $approveCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Approved')->count();

//     $rejectCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Rejected')->count();

//     $pendingCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Pending')->count();
//   }

//   return response()->json(['success' => true,
//         'states' => $states,
//         'approveCounts' => $approveCounts,
//         'rejectCounts' => $rejectCounts,
//         'pendingCounts' => $pendingCounts,
// ], 200);


foreach ($states as $state) {

$approveCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                     ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','1')->where('aerations.l2_status', 'Approved')->count();
$rejectCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                     ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','1')->where('aerations.l2_status','Rejected')->count();
$pendingCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                      ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','1')->where('aerations.l2_status','Pending')->count();

}

return response()->json(['success' => true,
    'states' => $states,
    'approveCounts' => $approveCounts,
    'rejectCounts' => $rejectCounts,
    'pendingCounts' => $pendingCounts,
], 200);

  //   $areation1_assam_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation1_assam_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation1_assam_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   $areation1_westbengal_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation1_westbengal_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation1_westbengal_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   $areation1_telangana_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation1_telangana_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation1_telangana_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   $areation1_other_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation1_other_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation1_other_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','1')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   return response()->json(['success'=>true,'areation1_assam_approve_count'=>$areation1_assam_approve_count,'areation1_assam_pending_count'=>$areation1_assam_pending_count,
  //  'areation1_assam_rejected_count'=>$areation1_assam_rejected_count, 'areation1_westbengal_approve_count'=>$areation1_westbengal_approve_count,'areation1_westbengal_pending_count'=>$areation1_westbengal_pending_count,
  //  'areation1_westbengal_rejected_count'=>$areation1_westbengal_rejected_count,'areation1_telangana_approve_count'=>$areation1_telangana_approve_count,'areation1_telangana_pending_count'=>$areation1_telangana_pending_count,
  //  'areation1_telangana_rejected_count'=>$areation1_telangana_rejected_count,'areation1_other_approve_count'=>$areation1_other_approve_count,'areation1_other_pending_count'=>$areation1_other_pending_count,
  //  'areation1_other_rejected_count'=>$areation1_other_rejected_count
  // ]);

  }



  public function areation2_data_count(){

    $states = State::select('id','name')->get();
    // dd($states);

  $approveCounts = [];
  $rejectCounts = [];
  $pendingCounts = [];

  // foreach ($states as $state) {

  //   $aerationQuery = Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //       ->where('final_farmers.state_id', $state->id)
  //       ->where('aerations.aeration_no', '2');

  //   $approveCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Approved')->count();

  //   $rejectCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Rejected')->count();

  //   $pendingCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Pending')->count();
  // }

  foreach ($states as $state) {

    $approveCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                         ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','2')->where('aerations.l2_status', 'Approved')->count();
    $rejectCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                         ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','2')->where('aerations.l2_status','Rejected')->count();
    $pendingCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                          ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','2')->where('aerations.l2_status','Pending')->count();
    
    }

  return response()->json(['success' => true,
        'states' => $states,
        'approveCounts' => $approveCounts,
        'rejectCounts' => $rejectCounts,
        'pendingCounts' => $pendingCounts,
], 200);


  //   $areation2_assam_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation2_assam_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation2_assam_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Assam')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   $areation2_westbengal_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'West Bengal')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation2_westbengal_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'West Bengal')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation2_westbengal_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'West Bengal')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   $areation2_telangana_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Telangana')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation2_telangana_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Telangana')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation2_telangana_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', 'Telangana')
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   $areation2_other_approve_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state',null)
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Approved')
  //   ->count();
  //   $areation2_other_pending_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', null)
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Pending')
  //   ->count();
  //   $areation2_other_rejected_count=Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
  //   ->where('final_farmers.state', null)
  //   ->where('aerations.aeration_no','2')
  //   ->where('aerations.l2_status', 'Rejected')
  //   ->count();

  //   return response()->json(['success'=>true,'areation2_assam_approve_count'=>$areation2_assam_approve_count,'areation2_assam_pending_count'=>$areation2_assam_pending_count,
  //  'areation2_assam_rejected_count'=>$areation2_assam_rejected_count, 'areation2_westbengal_approve_count'=>$areation2_westbengal_approve_count,'areation2_westbengal_pending_count'=>$areation2_westbengal_pending_count,
  //  'areation2_westbengal_rejected_count'=>$areation2_westbengal_rejected_count,'areation2_telangana_approve_count'=>$areation2_telangana_approve_count,'areation2_telangana_pending_count'=>$areation2_telangana_pending_count,
  //  'areation2_telangana_rejected_count'=>$areation2_telangana_rejected_count,'areation2_other_approve_count'=>$areation2_other_approve_count,'areation2_other_pending_count'=>$areation2_other_pending_count,
  //  'areation2_other_rejected_count'=>$areation2_other_rejected_count
  // ]);

  }

  public function areation3_data_count(){

        $states = State::select('id','name')->get();
        // dd($states);

      $approveCounts = [];
      $rejectCounts = [];
      $pendingCounts = [];

      // foreach ($states as $state) {

      //   $aerationQuery = Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
      //       ->where('final_farmers.state_id', $state->id)
      //       ->where('aerations.aeration_no', '2');

      //   $approveCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Approved')->count();

      //   $rejectCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Rejected')->count();

      //   $pendingCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Pending')->count();
      // }

      foreach ($states as $state) {

        $approveCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                            ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','3')->where('aerations.l2_status', 'Approved')->count();
        $rejectCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                            ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','3')->where('aerations.l2_status','Rejected')->count();
        $pendingCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                              ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','3')->where('aerations.l2_status','Pending')->count();
        
        }

      return response()->json(['success' => true,
            'states' => $states,
            'approveCounts' => $approveCounts,
            'rejectCounts' => $rejectCounts,
            'pendingCounts' => $pendingCounts,
    ], 200);
  }


  public function areation4_data_count(){

    $states = State::select('id','name')->get();
      // dd($states);

    $approveCounts = [];
    $rejectCounts = [];
    $pendingCounts = [];

    // foreach ($states as $state) {

    //   $aerationQuery = Aeration::join('final_farmers', 'aerations.farmer_uniqueId', '=', 'final_farmers.farmer_uniqueId')
    //       ->where('final_farmers.state_id', $state->id)
    //       ->where('aerations.aeration_no', '2');

    //   $approveCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Approved')->count();

    //   $rejectCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Rejected')->count();

    //   $pendingCounts[$state->id] = $aerationQuery->where('aerations.l2_status', 'Pending')->count();
    // }

    foreach ($states as $state) {

      $approveCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                          ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','4')->where('aerations.l2_status', 'Approved')->count();
      $rejectCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                          ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','4')->where('aerations.l2_status','Rejected')->count();
      $pendingCounts[$state->id] = Aeration::join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                                            ->where('final_farmers.state_id', $state->id)->where('state_id',$state->id)->where('aerations.aeration_no','4')->where('aerations.l2_status','Pending')->count();
      
      }

    return response()->json(['success' => true,
          'states' => $states,
          'approveCounts' => $approveCounts,
          'rejectCounts' => $rejectCounts,
          'pendingCounts' => $pendingCounts,
  ], 200);
  }

  public function fetch_organization(Request $request){
   $organization=Company::where('state_id',$request->state_id)->get();

   if($organization){
    return response()->json(['success'=>true,'data'=>$organization],200);
   }
   return response()->json(['error'=>true,'message'=>'Something Went Wrong.'],200);
  }



  public function filter_graph(Request $request){
    // dd(request()); // reuqested data  token , forr , to date , from_date , state_id
      $currentDate = Carbon::now()->toDateString();
    if($request->state_id){
      $state=State::where('id',$request->state_id)->first();
      $statename=$state->name;
    }
    if($request->to_date){
      $formattedto_date=date('Y-m-d', strtotime($request->to_date));
    }
    if($request->from_date){
      $formattedfrom_date=date('Y-m-d', strtotime($request->from_date));
    }
    if($request->forr =="farmers"){
      $approved = \App\Models\FinalFarmer::where('final_status', 'Approved')
      ->where('state_id', $state->id)
      ->where('onboarding_form','1')->where('onboard_completed','!=','Processing')
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();

      $pending=\App\Models\FinalFarmer::where('final_status','Pending')
      ->where('state_id',$state->id)
      ->where('onboarding_form','1')->where('onboard_completed','!=','Processing')
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();

      $rejected=\App\Models\FinalFarmer::where('final_status','Rejected')
      ->where('state_id',$state->id)
      ->where('onboarding_form','1')->where('onboard_completed','!=','Processing')
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();


    }
    else if($request->forr == "cropData") {
      $approved=\App\Models\FarmerCropdata::join('final_farmers','farmer_cropdata.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->where('farmer_cropdata.l2_status','Approved')->where('farmer_cropdata.deleted_at',Null)
      ->where('final_farmers.state_id',$state->id)
      ->whereBetween('farmer_cropdata.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $pending=\App\Models\FarmerCropdata::join('final_farmers','farmer_cropdata.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->where('farmer_cropdata.l2_status','Pending')->where('farmer_cropdata.deleted_at',Null)
      ->where('final_farmers.state_id',$state->id)
      ->whereBetween('farmer_cropdata.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $rejected=\App\Models\FarmerCropdata::join('final_farmers','farmer_cropdata.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->where('farmer_cropdata.l2_status','Rejected')->where('farmer_cropdata.deleted_at',Null)
      ->where('final_farmers.state_id',$state->id)
      ->whereBetween('farmer_cropdata.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();

   }
    else if($request->forr =="polygon"){
      $approved=\App\Models\Polygon::join('final_farmers','polygons.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('polygons.final_status','Approved')->where('polygons.deleted_at',Null)
      ->where('final_farmers.state_id',$state->id)
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $pending=\App\Models\Polygon::join('final_farmers','polygons.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('polygons.final_status','Pending')->where('polygons.deleted_at',Null)
      ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $rejected=\App\Models\Polygon::join('final_farmers','polygons.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('polygons.final_status','Rejected')->where('polygons.deleted_at',Null)
           ->whereBetween('created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();


    }
    else if($request->forr =="pipeInstallation"){
      $approved=\App\Models\PipeInstallationPipeImg::join('final_farmers','pipe_installation_pipeimg.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('pipe_installation_pipeimg.l2status','Approved')->where('pipe_installation_pipeimg.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('pipe_installation_pipeimg.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $pending=\App\Models\PipeInstallationPipeImg::join('final_farmers','pipe_installation_pipeimg.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('pipe_installation_pipeimg.l2status','Pending')->where('pipe_installation_pipeimg.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('pipe_installation_pipeimg.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $rejected=\App\Models\PipeInstallationPipeImg::join('final_farmers','pipe_installation_pipeimg.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('pipe_installation_pipeimg.l2status','Rejected')->where('pipe_installation_pipeimg.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('pipe_installation_pipeimg.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
    }
    else if($request->forr =="areation"){
      $approved=\App\Models\Aeration::join('final_farmers','aerations.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('aerations.l2_status','Approved')->where('aerations.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('aerations.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $pending=\App\Models\Aeration::join('final_farmers','aerations.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('aerations.l2_status','Pending')->where('aerations.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('aerations.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $rejected=\App\Models\Aeration::join('final_farmers','aerations.farmer_plot_uniqueid','=','final_farmers.farmer_plot_uniqueid')
      ->where('aerations.l2_status','Rejected')->where('aerations.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('aerations.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
    }else{
      $approved=\App\Models\FarmerBenefit::join('final_farmers','farmer_benefits.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->where('farmer_benefits.l2_status','Approved')->where('farmer_benefits.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('farmer_benefits.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $pending=\App\Models\FarmerBenefit::join('final_farmers','farmer_benefits.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->where('farmer_benefits.l2_status','Pending')->where('farmer_benefits.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('farmer_benefits.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
      $rejected=\App\Models\FarmerBenefit::join('final_farmers','farmer_benefits.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->where('farmer_benefits.l2_status','Rejected')->where('farmer_benefits.deleted_at',Null)
      ->where('final_farmers.state',$state->id)
      ->whereBetween('farmer_benefits.created_at', [
        $formattedto_date,
        $formattedfrom_date
      ])->count();
    }

    // dd($approved , $pending , $rejected ,$request->forr,$statename);
    return response()->json(['success'=>true,'approved'=>$approved,
    'pending'=>$pending,'rejected'=>$rejected,'forr'=>$request->forr,'statename'=>$statename],200);


  }

  public function organization_filter_graph(Request $request){

    if($request->state_id){
      $state=State::where('id',$request->state_id)->first();
      $statename=$state->name;
      $organization = Company::where('state_id',$state->id)->first();
    //   dd($organization);
      $organizationName = $organization->company;
    }
    if($request->to_date){
      $formattedto_date=date('Y-m-d', strtotime($request->to_date));

    }
    if($request->from_date){

      $formattedfrom_date=date('Y-m-d', strtotime($request->from_date));
    }

    if($request->forr == "farmers"){
        $organizationCounts = FinalFarmer::select('organization_id', 'final_status', \DB::raw('count(*) as count'))
            ->where('state_id', $state->id)
            ->where('organization_id', $organization->id)
            ->whereBetween('created_at', [$formattedto_date, $formattedfrom_date])
            ->groupBy('organization_id', 'final_status')
            ->get();
            // dd($organizationCounts);

        $organizationData = [];
        foreach ($organizationCounts as $count) {
            $organization = \App\Models\Company::where('id', $count->organization_id)->first();
            $organizationId = $count->organization_id;
            $organizationName = $organization ? $organization->company : "NA";
            $status = $count->final_status;

            if (!isset($organizationData[$organizationId])) {
                $organizationData[$organizationId] = [
                    'name' => $organizationName,
                    'Approved' => 0,
                    'Pending' => 0,
                    'Rejected' => 0,
                ];
            }

            if ($status === 'Approved') {
                $organizationData[$organizationId]['Approved'] = $count->count;
            } elseif ($status === 'Pending') {
                $organizationData[$organizationId]['Pending'] = $count->count;
            } elseif ($status === 'Rejected') {
                $organizationData[$organizationId]['Rejected'] = $count->count;
            }
        }
        // dd($organizationData);
        return response()->json(['success' => true, 'organizationData' => $organizationData]);
    }else if($request->forr == "cropData"){
        $organizationCounts = \App\Models\FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.organization_id', 'farmer_cropdata.l2_status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state_id', $statename)
        ->whereBetween('farmer_cropdata.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.organization_id', 'farmer_cropdata.l2_status')
        ->get();

    $organizationData = [];
    foreach ($organizationCounts as $count) {
        $organization = \App\Models\Organization::where('id', $count->organization_id)->first();
        $organizationId = $count->organization_id;
        $organizationName = $organization ? $organization->company : "NA";
        $status = $count->l2_status; // Corrected column name
        $organizationData[$organizationId]['name'] = $organizationName;
        $organizationData[$organizationId][$status] = $count->count;
    }
    dd($organizationData);
    return response()->json(['success' => true, 'organizationData' => $organizationData]);

      }else if($request->forr =="polygon"){
        $organizationCounts = \App\Models\PipeInstallation::join('final_farmers', 'pipe_installations.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.organization_id', 'pipe_installations.l2_status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state_id', $statename)
        ->whereBetween('pipe_installations.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.organization_id', 'pipe_installations.l2_status')
        ->get();

    $organizationData = [];
    foreach ($organizationCounts as $count) {
        $organization = \App\Models\Organization::where('id', $count->organization_id)->first();
        $organizationId = $count->organization_id;
        $organizationName = $organization ? $organization->name : "NA";
        $status = $count->l2_status; // Corrected column name
        $organizationData[$organizationId]['name'] = $organizationName;
        $organizationData[$organizationId][$status] = $count->count;
    }
    return response()->json(['success' => true, 'organizationData' => $organizationData]);
      }
      else if($request->forr =="pipeInstallation"){

        $organizationCounts = \App\Models\PipeInstallationPipeImg::join('final_farmers', 'pipe_installation_pipeimg.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.organization_id', 'pipe_installation_pipeimg.l2status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state', $statename)
        ->whereBetween('pipe_installation_pipeimg.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.organization_id', 'pipe_installation_pipeimg.l2status')
        ->get();

    $organizationData = [];
    foreach ($organizationCounts as $count) {
        $organization = \App\Models\Organization::where('id', $count->organization_id)->first();
        $organizationId = $count->organization_id;
        $organizationName = $organization ? $organization->name : "NA";
        $status = $count->l2status; // Corrected column name
        $organizationData[$organizationId]['name'] = $organizationName;
        $organizationData[$organizationId][$status] = $count->count;
    }
    return response()->json(['success' => true, 'organizationData' => $organizationData]);
      }else if($request->forr =="areation"){

        $organizationCounts = \App\Models\Aeration::join('final_farmers', 'aerations.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.organization_id', 'aerations.l2_status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state', $statename)
        ->whereBetween('aerations.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.organization_id', 'aerations.l2_status')
        ->get();

    $organizationData = [];
    foreach ($organizationCounts as $count) {
        $organization = \App\Models\Organization::where('id', $count->organization_id)->first();
        $organizationId = $count->organization_id;
        $organizationName = $organization ? $organization->name : "NA";
        $status = $count->l2_status; // Corrected column name
        $organizationData[$organizationId]['name'] = $organizationName;
        $organizationData[$organizationId][$status] = $count->count;
    }
    return response()->json(['success' => true, 'organizationData' => $organizationData]);
      }else if($request->forr =="benifit"){

        $organizationCounts = \App\Models\FarmerBenefit::join('final_farmers', 'farmer_benefits.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.organization_id', 'farmer_benefits.l2_status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state', $statename)
        ->whereBetween('farmer_benefits.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.organization_id', 'farmer_benefits.l2_status')
        ->get();

    $organizationData = [];
    foreach ($organizationCounts as $count) {
        $organization = \App\Models\Organization::where('id', $count->organization_id)->first();
        $organizationId = $count->organization_id;
        $organizationName = $organization ? $organization->name : "NA";
        $status = $count->l2_status; // Corrected column name
        $organizationData[$organizationId]['name'] = $organizationName;
        $organizationData[$organizationId][$status] = $count->count;
    }
    return response()->json(['success' => true, 'organizationData' => $organizationData]);
      }

      else{
        $organizationCounts = FinalFarmer::select('organization_id', 'final_status_onboarding', \DB::raw('count(*) as count'))
        ->where('state',$statename)
        ->whereBetween('created_at', [
          $formattedto_date,
          $formattedfrom_date
        ])
        ->groupBy('organization_id', 'final_status_onboarding')
        ->get();

        $organizationData = [];
        foreach ($organizationCounts as $count) {
            $organization = \App\Models\Organization::where('id',$count->organization_id)->first();
            $organizationId = $count->organization_id;
            $organizationName = $organization ? $organization->name : "NA";
            $status = $count->final_status_onboarding;
            $organizationData[$organizationId]['name'] = $organizationName;
            $organizationData[$organizationId][$status] = $count->count;
        }
    return response()->json(['success' => true, 'organizationData' => $organizationData]);
      }


  }








  public function district_filter(Request $request){
    if($request->state_id){
        $state = State::where('id', $request->state_id)->first();
        $statename = $state->name;
        $company = Company::where('state_id', $state->id)->first();

        if ($company) {
            // Handle multiple district IDs
            $districtIds = explode(',', $company->district_id);
            $districts = District::whereIn('id', $districtIds)->get();

            // // Debugging
            // dd($districts);

            $districtNames = $districts->pluck('district')->toArray();
        }
    }

    if($request->to_date){
        $formattedto_date = date('Y-m-d', strtotime($request->to_date));
    }

    if($request->from_date){
        $formattedfrom_date = date('Y-m-d', strtotime($request->from_date));
    }

    if($request->forr == "farmers"){
        $districtCounts = FinalFarmer::select('district_id', 'final_status', \DB::raw('count(*) as count'))
            ->where('state_id', $state->id)
            ->whereBetween('created_at', [$formattedto_date, $formattedfrom_date])
            ->groupBy('district_id', 'final_status')
            ->get();

        $districtData = [];
        foreach ($districtCounts as $count) {
            $district = District::find($count->district_id);
            $districtName = $district ? $district->district : 'NA'; // Ensure $districtName is a string
            $status = $count->final_status;

            if (!isset($districtData[$districtName])) {
                $districtData[$districtName] = [
                    'Approved' => 0,
                    'Pending' => 0,
                    'Rejected' => 0
                ];
            }

            if ($status === 'Approved') {
                $districtData[$districtName]['Approved'] = $count->count;
            } elseif ($status === 'Pending') {
                $districtData[$districtName]['Pending'] = $count->count;
            } elseif ($status === 'Rejected') {
                $districtData[$districtName]['Rejected'] = $count->count;
            }
        }

        return response()->json(['success' => true, 'district_data' => $districtData, 'state_name' => $statename]);
    }else if($request->forr == "cropData"){

      $districtcounts =  \App\Models\FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->select('final_farmers.district_id', 'farmer_cropdata.l2_status', \DB::raw('count(*) as count'))
      ->where('final_farmers.state', $statename)
      ->whereBetween('farmer_cropdata.created_at', [
          $formattedto_date,
          $formattedfrom_date
      ])
      ->groupBy('final_farmers.district_id', 'farmer_cropdata.l2_status')
      ->get();

      $district_data = [];
      foreach ($districtcounts as $count) {
          $districtname = $count->district??'NA'; // Corrected variable name
          $status = $count->l2_status;
          $district_data[$districtname][$status] = $count->count;
      }

      return response()->json(['success' => true, 'district_data' => $district_data, 'state_name' => $statename]);



    }else if($request->forr =="polygon"){

     $districtcounts= \App\Models\PipeInstallation::join('final_farmers', 'pipe_installations.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
     ->select('final_farmers.district_id', 'pipe_installations.l2_status', \DB::raw('count(*) as count'))
     ->where('final_farmers.state_id', $statename)
     ->whereBetween('pipe_installations.created_at', [
         $formattedto_date,
         $formattedfrom_date
     ])
     ->groupBy('final_farmers.district_id', 'pipe_installations.l2_status')
     ->get();

      $district_data = [];
      foreach ($districtcounts as $count) {
          $districtname = $count->district??'NA'; // Corrected variable name
          $status = $count->l2_status;
          $district_data[$districtname][$status] = $count->count;
      }

      return response()->json(['success' => true, 'district_data' => $district_data, 'state_name' => $statename]);


    }else if($request->forr =="pipeinstallation"){

     $districtcounts=\App\Models\PipeInstallationPipeImg::join('final_farmers', 'pipe_installation_pipeimg.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
     ->select('final_farmers.district', 'pipe_installation_pipeimg.l2status', \DB::raw('count(*) as count'))
     ->where('final_farmers.state_id', $statename)
     ->whereBetween('pipe_installation_pipeimg.created_at', [
         $formattedto_date,
         $formattedfrom_date
     ])
     ->groupBy('final_farmers.district_id', 'pipe_installation_pipeimg.l2status')
     ->get();

      $district_data = [];
      foreach ($districtcounts as $count) {
          $districtname = $count->district??'NA'; // Corrected variable name
          $status = $count->l2status;
          $district_data[$districtname][$status] = $count->count;
      }

      return response()->json(['success' => true, 'district_data' => $district_data, 'state_name' => $statename]);
    }else if($request->forr =="areation"){
     $districtcounts= \App\Models\Aeration::join('final_farmers', 'aerations.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.district', 'aerations.l2_status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state_id', $statename)
        ->whereBetween('aerations.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.district', 'aerations.l2_status')
        ->get();

      $district_data = [];
      foreach ($districtcounts as $count) {
          $districtname = $count->district??'NA'; // Corrected variable name
          $status = $count->l2_status;
          $district_data[$districtname][$status] = $count->count;
      }

      return response()->json(['success' => true, 'district_data' => $district_data, 'state_name' => $statename]);
    }else if($request->forr =="benifit"){
      $districtcounts=\App\Models\FarmerBenefit::join('final_farmers', 'farmer_benefits.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
      ->select('final_farmers.district', 'farmer_benefits.l2_status', \DB::raw('count(*) as count'))
      ->where('final_farmers.state_id', $statename)
      ->whereBetween('farmer_benefits.created_at', [
          $formattedto_date,
          $formattedfrom_date
      ])
      ->groupBy('final_farmers.district', 'farmer_benefits.l2_status')
      ->get();
      $district_data = [];
      foreach ($districtcounts as $count) {
          $districtname = $count->district??'NA'; // Corrected variable name
          $status = $count->l2_status;
          $district_data[$districtname][$status] = $count->count;
      }

      return response()->json(['success' => true, 'district_data' => $district_data, 'state_name' => $statename]);
    }


    else{

      $districtcounts = FinalFarmer::select('district', 'final_status_onboarding', \DB::raw('count(*) as count'))
      ->where('state_id', $statename)
      ->whereBetween('created_at', [$formattedto_date, $formattedfrom_date])
      ->groupBy('district', 'final_status_onboarding')
      ->get();

  $district_data = [];
  foreach ($districtcounts as $count) {
      $districtname = $count->district??'NA'; // Corrected variable name
      $status = $count->final_status_onboarding;
      $district_data[$districtname][$status] = $count->count;
  }

  return response()->json(['success' => true, 'district_data' => $district_data, 'state_name' => $statename]);
    }
  }



//   if($request->state_id){
//     $state = State::where('id', $request->state_id)->first();
//     $statename = $state->name;
//     $company = Company::where('state_id', $state->id)->first();

//     if ($company) {
//         // Handle multiple district IDs
//         $districtIds = explode(',', $company->district_id);
//         $districts = District::whereIn('id', $districtIds)->get();

//         // // Debugging
//         // dd($districts);

//         $districtNames = $districts->pluck('district')->toArray();
//     }
// }

// if($request->to_date){
//     $formattedto_date = date('Y-m-d', strtotime($request->to_date));
// }

// if($request->from_date){
//     $formattedfrom_date = date('Y-m-d', strtotime($request->from_date));
// }

// if($request->forr == "farmers"){
//     $districtCounts = FinalFarmer::select('district_id', 'final_status', \DB::raw('count(*) as count'))
//         ->where('state_id', $state->id)
//         ->whereBetween('created_at', [$formattedto_date, $formattedfrom_date])
//         ->groupBy('district_id', 'final_status')
//         ->get();

//     $districtData = [];
//     foreach ($districtCounts as $count) {
//         $district = District::find($count->district_id);
//         $districtName = $district ? $district->district : 'NA'; // Ensure $districtName is a string
//         $status = $count->final_status;

//         if (!isset($districtData[$districtName])) {
//             $districtData[$districtName] = [
//                 'Approved' => 0,
//                 'Pending' => 0,
//                 'Rejected' => 0
//             ];
//         }

//         if ($status === 'Approved') {
//             $districtData[$districtName]['Approved'] = $count->count;
//         } elseif ($status === 'Pending') {
//             $districtData[$districtName]['Pending'] = $count->count;
//         } elseif ($status === 'Rejected') {
//             $districtData[$districtName]['Rejected'] = $count->count;
//         }
//     }

//     return response()->json(['success' => true, 'district_data' => $districtData, 'state_name' => $statename]);
// }


    public function taluka_filter(Request $request){
        if($request->state_id){
            $state = State::where('id', $request->state_id)->first();
            $statename = $state->name;
            $company = Company::where('state_id', $state->id)->first();

            if ($company) {
                // Handle multiple district IDs
                $districtIds = explode(',', $company->district_id);
                $districts = District::whereIn('id', $districtIds)->get();
                $talukas = Taluka::whereIn('district_id', $districtIds)->get();

                // // Debugging
                // dd($districts);

                $districtNames = $talukas->pluck('taluka')->toArray();
            }
        }
      if($request->to_date){
          $formattedto_date = date('Y-m-d', strtotime($request->to_date));
      }
      if($request->from_date){
          $formattedfrom_date = date('Y-m-d', strtotime($request->from_date));
      }


      // $districtcounts = FinalFarmer::select('taluka', 'final_status_onboarding', \DB::raw('count(*) as count'))
      //     ->where('state', $statename)
      //     ->whereBetween('created_at', [$formattedto_date, $formattedfrom_date])
      //     ->groupBy('taluka', 'final_status_onboarding')
      //     ->get();

      // $taluka_data = [];
      // foreach ($districtcounts as $count) {
      //     $talukaname = $count->taluka??'NA'; //
      //     $status = $count->final_status_onboarding;
      //     $taluka_data[$talukaname][$status] = $count->count;
      // }



      if($request->forr =="farmers"){
        $districtcounts = FinalFarmer::select('taluka_id', 'final_status', \DB::raw('count(*) as count'))
            ->where('state_id', $state->id)
            ->whereBetween('created_at', [$formattedto_date, $formattedfrom_date])
            ->groupBy('taluka_id', 'final_status')
            ->get();

            $taluka_data = [];
            foreach ($districtcounts as $count) {
                $taluka = Taluka::find($count->taluka_id);
        $talukaName = $taluka ? $taluka->taluka : 'NA'; // Ensure $districtName is a string
        $status = $count->final_status;

        if (!isset($taluka_data[$talukaName])) {
            $taluka_data[$talukaName] = [
                'Approved' => 0,
                'Pending' => 0,
                'Rejected' => 0
            ];
        }

        if ($status === 'Approved') {
            $taluka_data[$talukaName]['Approved'] = $count->count;
        } elseif ($status === 'Pending') {
            $taluka_data[$talukaName]['Pending'] = $count->count;
        } elseif ($status === 'Rejected') {
            $taluka_data[$talukaName]['Rejected'] = $count->count;
        }
            }

            // dd($taluka_data);
            return response()->json(['success' => true, 'taluka_data' => $taluka_data, 'state_name' => $statename]);
      }else if($request->forr == "cropData"){

        $districtcounts =  \App\Models\FarmerCropdata::join('final_farmers', 'farmer_cropdata.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.taluka_id', 'farmer_cropdata.l2_status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state_id', $statename)
        ->whereBetween('farmer_cropdata.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.taluka_id', 'farmer_cropdata.l2_status')
        ->get();
        $taluka_data = [];
        foreach ($districtcounts as $count) {
            $talukaname = $count->taluka??'NA'; //
            $status = $count->l2_status;
            $taluka_data[$talukaname][$status] = $count->count;
        }

        return response()->json(['success' => true, 'taluka_data' => $taluka_data, 'state_name' => $statename]);



      }else if($request->forr =="polygon"){

       $districtcounts= \App\Models\PipeInstallation::join('final_farmers', 'pipe_installations.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
       ->select('final_farmers.taluka_id', 'pipe_installations.l2_status', \DB::raw('count(*) as count'))
       ->where('final_farmers.state_id', $statename)
       ->whereBetween('pipe_installations.created_at', [
           $formattedto_date,
           $formattedfrom_date
       ])
       ->groupBy('final_farmers.taluka_id', 'pipe_installations.l2_status')
       ->get();

       $taluka_data = [];
       foreach ($districtcounts as $count) {
           $talukaname = $count->taluka??'NA'; //
           $status = $count->l2_status;
           $taluka_data[$talukaname][$status] = $count->count;
       }


       return response()->json(['success' => true, 'taluka_data' => $taluka_data, 'state_name' => $statename]);


      }else if($request->forr =="pipeinstallation"){

       $districtcounts=\App\Models\PipeInstallationPipeImg::join('final_farmers', 'pipe_installation_pipeimg.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
       ->select('final_farmers.taluka_id', 'pipe_installation_pipeimg.l2status', \DB::raw('count(*) as count'))
       ->where('final_farmers.state_id', $statename)
       ->whereBetween('pipe_installation_pipeimg.created_at', [
           $formattedto_date,
           $formattedfrom_date
       ])
       ->groupBy('final_farmers.taluka_id', 'pipe_installation_pipeimg.l2status')
       ->get();

       $taluka_data = [];
       foreach ($districtcounts as $count) {
           $talukaname = $count->taluka??'NA'; //
           $status = $count->l2status;
           $taluka_data[$talukaname][$status] = $count->count;
       }


       return response()->json(['success' => true, 'taluka_data' => $taluka_data, 'state_name' => $statename]);
      }else if($request->forr =="areation"){
       $districtcounts= \App\Models\Aeration::join('final_farmers', 'aerations.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
          ->select('final_farmers.taluka_id', 'aerations.l2_status', \DB::raw('count(*) as count'))
          ->where('final_farmers.state_id', $statename)
          ->whereBetween('aerations.created_at', [
              $formattedto_date,
              $formattedfrom_date
          ])
          ->groupBy('final_farmers.taluka_id', 'aerations.l2_status')
          ->get();

          $taluka_data = [];
          foreach ($districtcounts as $count) {
              $talukaname = $count->taluka??'NA'; //
              $status = $count->l2_status;
              $taluka_data[$talukaname][$status] = $count->count;
          }


          return response()->json(['success' => true, 'taluka_data' => $taluka_data, 'state_name' => $statename]);
      }else if($request->forr =="benifit"){
        $districtcounts=\App\Models\FarmerBenefit::join('final_farmers', 'farmer_benefits.farmer_uniqueId','=','final_farmers.farmer_uniqueId')
        ->select('final_farmers.taluka_id', 'farmer_benefits.l2_status', \DB::raw('count(*) as count'))
        ->where('final_farmers.state_id', $statename)
        ->whereBetween('farmer_benefits.created_at', [
            $formattedto_date,
            $formattedfrom_date
        ])
        ->groupBy('final_farmers.taluka_id', 'farmer_benefits.l2_status')
        ->get();
        $taluka_data = [];
        foreach ($districtcounts as $count) {
            $talukaname = $count->taluka??'NA'; //
            $status = $count->l2_status;
            $taluka_data[$talukaname][$status] = $count->count;
        }

        return response()->json(['success' => true, 'taluka_data' => $taluka_data, 'state_name' => $statename]);
      }


      else{

        $districtcounts = FinalFarmer::select('taluka_id', 'final_status_onboarding', \DB::raw('count(*) as count'))
        ->where('state_id', $statename)
        ->whereBetween('created_at', [$formattedto_date, $formattedfrom_date])
        ->groupBy('taluka_id', 'final_status_onboarding')
        ->get();

        $taluka_data = [];
        foreach ($districtcounts as $count) {
            $talukaname = $count->taluka??'NA'; //
            $status = $count->final_status_onboarding;
            $taluka_data[$talukaname][$status] = $count->count;
        }

    return response()->json(['success' => true, 'taluka_data' => $taluka_data, 'state_name' => $statename]);

    // return response()->json(['success' => true, 'district_data' => $district_data, 'state_name' => $statename]);
      }
    }


}
