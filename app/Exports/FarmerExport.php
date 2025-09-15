<?php

namespace App\Exports;

use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Models\FarmerCropdata;
use App\Models\Benefit;
use App\Exports\FarmerExport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class FarmerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents,WithStyles,WithChunkReading
{
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($farmeruniqueid) {
          $this->farmeruniqueid = $farmeruniqueid;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // $farmer =  Farmer::query();
        // $farmer = $farmer->with('FarmerPlot','users')->where('onboarding_form','1');
        // if(is_numeric($this->farmeruniqueid)){// w'll be used when request come from farmer detail page
        //   $farmer = $farmer->where('farmer_uniqueId',$this->farmeruniqueid);
        // }
        // $farmer = $farmer->get();
        // $farmer = $farmer->map(function($q){
        //     $q->PlotImgUrl = url('download/').'/'.'PlotImg'.'/'.$q->id.'/'.$q->farmer_uniqueId;
        //     $q->BenefitImgUrl = url('download/').'/'.'BenefitImg'.'/'.$q->id.'/'.$q->farmer_uniqueId;
        //     return $q;
        // });

        // dd(request()->all());
        $farmer = Farmer::where('onboarding_form','1')->with('CropData')->when(request(),function($q){
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
                    if(!empty($VendorLocation->panchayat)){
                        $q->whereIn('panchayat_id',explode(',',$VendorLocation->panchayat));
                    }
                    if(!empty($VendorLocation->village)){
                        $q->whereIn('village_id',explode(',',$VendorLocation->village));
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
                     $q->where('farmer_status','like',request('farmer_status'));
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
            });

        // dd($farmer->get());

        // dd('stop');

        return $farmer->get();
    }

    // here you select the row that you want in the file
    public function map($farmer): array{

        // dd($farmer);

      $payload_fields = [ $farmer['farmer_uniqueId']??"-", $farmer['farmer_name']??"-", $farmer['mobile_access']??"-",
                          $farmer['mobile_reln_owner']??"-", $farmer['mobile']??"-", $farmer['no_of_plots']??"-",
                          $farmer['total_plot_area']??"-", $farmer['country']??"-",  $farmer['state']??"-",
                          $farmer['district']??"-",$farmer['taluka']??"-",
                           $farmer['village']??"-", $farmer['latitude']??"-",
                          $farmer['longitude']??"-", $farmer['date_survey']??"-",
                          $farmer['time_survey']??"-", $farmer['remarks']??"-",
                          $farmer['surveyor_name']??"-",$farmer['surveyor_email']??"-",$farmer['surveyor_mobile']??"-",$farmer['PlotImgUrl']??"-", $farmer['BenefitImgUrl']??"-"
                        ];
        $CropdataCount = DB::table('farmers')->max('no_of_plots');
        // 1. below procedure is to make add data based on no of cropdata record and benefits record for particular farmer.
        // 2. based on count of cropdata and benefit record stored in DB now adding adding data in Excel same goes for benfits also
        //e.x. if this farmer has only two plot, then data of cropdata added from collection.
        if($farmer->CropData->count() > 0){//for  cropdata
          foreach($farmer->CropData as $q){
                $plotdetail = FarmerPlot::where('farmer_uniqueId',$q->farmer_uniqueId)->where('plot_no',$q->plot_no)
                                          ->select('land_ownership','survey_no')->first();
                array_push($payload_fields, $q->plot_no, $q->area_in_acers, $q->season  , $q->crop_variety, $q->dt_irrigation_last,
                            $q->dt_ploughing, $q->dt_transplanting,$plotdetail->land_ownership,
                            $plotdetail->survey_no,$q->surveyor_name, $q->surveyor_email, $q->surveyor_mobile);

          }
          if($farmer->no_of_plots <= $CropdataCount){
            $MoretoAdd = ($CropdataCount - $farmer->no_of_plots);
            for ($x = 1; $x <= $MoretoAdd; $x++) {
              array_push($payload_fields, ' ', ' ', ' '  , ' ', ' ',' ', ' ', ' ', ' ', ' ', ' ', ' ');
            }
          }
        }else{
            if(is_numeric($this->farmeruniqueid)){ // w'll be used when request come from farmer detail page
              for($x = 1; $x <= $farmer->FarmerPlot->count(); $x++){
                array_push($payload_fields, $x, ' ', ' '  , ' ', ' ',' ', ' ', ' ', ' ', ' ', ' ', ' ');
              }
            }else{
              for ($x = 1; $x <= $CropdataCount; $x++) {
                array_push($payload_fields, $x, ' ', ' '  , ' ', ' ',' ', ' ', ' ', ' ', ' ', ' ', ' ');
              }
            }
        }
        $benefitCount = 1;
        if($farmer->BenefitsData->count() > 0){
          foreach($farmer->BenefitsData as $b){
                array_push($payload_fields, $benefitCount,$b->total_plot_area, $b->seasons, $b->benefit ,$b->surveyor_name, $b->surveyor_email, $b->surveyor_mobile);
                $benefitCount++;
          }
        }
        return $payload_fields;
    }

    public function headings(): array{
      $header = ['organization_id','Farmer UniqueID', 'Farmer Name', 'Mobile Access', 'Mobile Relation Owner', 'Mobile', 'No. of PLots', 'Total Plot Area','Country',
                 'State', 'District', 'Taluka', 'Village', 'Latitude', 'Longitude', 'Date Survey', 'Time Survey',
                 'Remarks','Surveyor Name', 'Surveyor Email','Surveyor Mobile','PlotImgUrl','BenefitImgUrl'];
      if(is_numeric($this->farmeruniqueid)){ // w'll be used when request come from farmer detail page
        $farmer = $this->collection()->first();//get the record from collection
        // 1. below procedure is to make header based on no of cropdata record and benefits record for particular farmer.
        // 2. based on count of cropdata and benefit record stored in DB now making adding header in Excel same goes for benfits also
        //e.x. if this farmer has only two plot that header of cropdata added two times
        //doing this just to take count of plot
        $CropData =  DB::table('farmer_plot_detail')->where('farmer_uniqueId',$farmer->farmer_uniqueId)->count();
        if($CropData > 0){
          for($x = 1; $x <= $CropData; $x++){
                array_push($header, 'CROPDATA', 'Area of plots', 'Seasons', 'Crop Variety','Lastirrigation date', 'Date of Ploughing', 'Date of Transplanting',
                'landOwnership', 'Survey No','Surveyor Name', 'Surveyor Email','Surveyor Mobile');
          }//end foreach
        }//cropdata end if
        //for benefits
        $benefits = DB::table('benefits')->where('status',1)->select('name')->get();
        if($benefits->count() > 0){
          foreach($benefits as $q){
                array_push($header, $q->name, 'Total Plot Area', 'Seasons', 'Benefits','Surveyor Name', 'Surveyor Email','Surveyor Mobile');
          }//end foreach
        }//benefit if end
      }else{
        $farmer = $this->collection();
        $benefits = DB::table('benefits')->where('status',1)->select('name')->get();
        $Cropdata = DB::table('farmers')->max('no_of_plots');
        for ($x = 1; $x <= $Cropdata; $x++) {
          array_push($header, 'CROPDATA', 'Area of plots', 'Seasons', 'Crop Variety','Lastirrigation date', 'Date of Ploughing', 'Date of Transplanting',
          'landOwnership', 'Survey No','Surveyor Name', 'Surveyor Email','Surveyor Mobile');
        }
        foreach ($benefits as $list) {
          array_push($header, $list->name, 'Total Plot Area', 'Seasons', 'Benefits','Surveyor Name', 'Surveyor Email','Surveyor Mobile');
        }
      }//elseif
      return $header;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registerEvents(): array{
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:AJ1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);

            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }

     public function chunkSize(): int
    {
        return 100;
    }
}
