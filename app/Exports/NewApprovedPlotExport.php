<?php

namespace App\Exports;

use App\Models\FarmerPlot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;

class NewApprovedPlotExport implements FromQuery,ShouldQueue
{
    use Exportable;
    
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($farmeruniqueid,$request) {
          $this->farmeruniqueid = $farmeruniqueid;
          $this->request = $request;
    }
    public function query()
    {
        $request = $this->request;
        return \App\Models\FinalFarmer::when('filter',function($q) use($request){
                                        //$q->where('onboarding_form',1);
                                        if($request->modules == 'CropData'){//when plot has cropdata
                                            $q->whereHas('PlotCropData');
                                        }
                                        if($request->modules == 'Benefit'){//when plot has benefit
                                            $q->whereHas('BenefitsData');
                                        }
                                        if($request->modules == 'PipeInstalltion'){//when plot has pipeinstalltion
                                            $q->whereHas('PlotPipeData');
                                        }
                                        if($request->modules == 'Aeration'){//when plot has aeration
                                            $q->whereHas('AerationData');
                                        }
                                        if(request()->has('status') && !empty(request()->status)){
                                  			     $q->where('final_status_onboarding',request()->status);
                                  		  }
                                        if(is_numeric($this->farmeruniqueid)){
                                            $q->where('farmer_uniqueId',$this->farmeruniqueid);
                                        }
                                        if($request->has('state')){
                                            $q->where('state_id','like',$request->state);
                                        }
                                        if(request()->has('district')){
                                             $q->where('district_id','like',$request->district);
                                        }
                                        if(request()->has('taluka')){
                                             $q->where('taluka_id','like',$request->taluka);
                                        }
                                        if(request()->has('panchayats')){
                                             $q->where('panchayat_id','like',$request->panchayats);
                                        }
                                        if(request()->has('village')){
                                             $q->where('village_id','like',$request->village);
                                        }
                                        if(request()->has('farmer_status')){
                                             $q->where('status','like',$request->farmer_status);
                                        }
                                        if(request()->has('executive_onboarding')){
                                             $q->where('surveyor_id',$request->executive_onboarding);
                                        }
                                        if(request()->has('start_date')){
                                            $q->whereDate('updated_at','>=',$request->start_date);
                                        }
                                        if(request()->has('end_date')){
                                            $q->whereDate('updated_at','<=',$request->end_date);
                                        }
                                        return $q;
        });
    }
    
}
