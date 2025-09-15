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
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;

class L1ApprovedIndividualExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents,WithStyles,WithChunkReading,ShouldQueue
{
    use Exportable;
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($type, $unique_id, $plot_no, $status, $state_id) {
          $this->type = $type;
          $this->unique_id = $unique_id;
          $this->plot_no = $plot_no;
          $this->status = $status;
          $this->state_id = $state_id; 
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;
        $state_id = $this->state_id; 
                              
        $farmer =   FarmerPlot::query();
        $farmer = $farmer->where('farmer_uniqueId',$unique_id);
        if($type == 'Individuals'){//excel download request come from single plot page
           $farmer = $farmer->where('plot_no',$plot_no);
        }
        $farmer = $farmer->with('farmer');  
        $farmer = $farmer->get();
        
        $farmer = $farmer->map(function($q){
            $q->PlotImgUrl = url('download/').'/'.'PlotImg'.'/'.$q->farmer_id.'/'.$q->farmer_uniqueId.'/'.$q->plot_no;
            return $q;
        });
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;
        $state_id = $this->state_id; 
        
        $payload_fields = [ $farmer->farmer->farmer_uniqueId ??"-", $farmer->farmer->farmer_name ??"-", $farmer->farmer->mobile_access ??"-",
                            $farmer->farmer->mobile_reln_owner ??"-", $farmer->farmer->mobile ??"-",
                            $farmer->farmer->no_of_plots ??"-"];


            if($state_id->state_id == 36){
                //need to add plot area for telangana of converted data
                $farmerplots =  FarmerPlot::where('farmer_uniqueId',$farmer->farmer->farmer_uniqueId)->get();
                    $guntha = 0.025000;
                  $total_area_acres  = 0;
                  foreach($farmerplots as $plotsarea){
                      $area = number_format((float)$plotsarea->area_in_acers, 2, '.', '');
                      $split = explode('.', $area);//spliting area
                      $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                      $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                      $conversion = explode('.', $result); // split result
                      $conversion = $conversion[1]??0;
                      $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data
                      $total_area_acres+=$acers;
                  }
                //

                    array_push($payload_fields,$total_area_acres??"-"  ,
                            $farmer->farmer->country ??"-",$farmer->farmer->state ??"-",$farmer->farmer->district ??"-",
                            $farmer->farmer->taluka ??"-",$farmer->farmer->panchayat ??"-",$farmer->farmer->village ??"-",$farmer->farmer->latitude ??"-",$farmer->farmer->longitude ??"-",
                            $farmer->farmer->date_survey ??"-",$farmer->farmer->time_survey ??"-",$farmer->farmer->remarks ??"-",$farmer->plot_no ??"-",$farmer->PlotImgUrl ??"-",
                            $farmer->farmer_plot_uniqueid ??"-",$farmer->plot_no ??"-"
                            );

                  //second part
                  $guntha = 0.025000;
                  $area = number_format((float)$farmer->area_in_acers, 2, '.', '');
                  $split = explode('.', $area);//spliting area
                  $valueafterdecimal = (isset($split[1]) && $split[1])?$split[1]:0;//take array of index 1 value after decimal point
                  $result = $valueafterdecimal*$guntha; // multiplying value with defined base value
                  $conversion = explode('.', $result); // split result
                  $conversion = $conversion[1]??0;
                  $acers = $split[0].'.'.$conversion;// concat the obtained result with firstly split data

                  array_push($payload_fields, $farmer->area_in_acers ??"-",$acers,$farmer->land_ownership ??"-",$farmer->actual_owner_name ??"-",$farmer->survey_no ??"-",
                               $farmer->status ??"-",
                    $farmer->appr_timestamp? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp??"")->format('d-m-Y') : "-",
                    $farmer->appr_timestamp? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp??"")->format('H:i:s') :"-",
                    $farmer->UserApprovedRejected->name??"-",
                    $farmer->farmer->surveyor_name ??"-",$farmer->farmer->surveyor_mobile ??"-" );
                
            }else{

                 if($state_id->state_id == 29){
                     array_push($payload_fields,$farmer->farmer->total_plot_area??"-"  ,
                            $farmer->farmer->country ??"-",$farmer->farmer->state ??"-",$farmer->farmer->district ??"-",
                            $farmer->farmer->taluka ??"-",$farmer->farmer->panchayat ??"-",$farmer->farmer->village ??"-",$farmer->farmer->latitude ??"-",$farmer->farmer->longitude ??"-",
                            $farmer->farmer->date_survey ??"-",$farmer->farmer->time_survey ??"-",$farmer->farmer->remarks ??"-",$farmer->plot_no ??"-",$farmer->PlotImgUrl ??"-",
                            $farmer->farmer_plot_uniqueid ??"-",$farmer->plot_no ??"-"
                            );
                    array_push($payload_fields,$farmer->area_in_acers ??"-",$farmer->land_ownership ??"-",$farmer->actual_owner_name ??"-",$farmer->survey_no ??"-",$farmer->status ??"-",
                    $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp)->format('d-m-Y') : "-",
                        $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp)->format('H:i:s') : "-",
                        $farmer->UserApprovedRejected->name??"-",$farmer->farmer->surveyor_name ??"-",$farmer->farmer->surveyor_mobile ??"-");
                 }else{
                     array_push($payload_fields,$farmer->farmer->total_plot_area??"-"  ,
                            $farmer->farmer->country ??"-",$farmer->farmer->state ??"-",$farmer->farmer->district ??"-",
                            $farmer->farmer->taluka ??"-",$farmer->farmer->panchayat ??"-",$farmer->farmer->village ??"-",$farmer->farmer->latitude ??"-",$farmer->farmer->longitude ??"-",
                            $farmer->farmer->date_survey ??"-",$farmer->farmer->time_survey ??"-",$farmer->farmer->remarks ??"-",$farmer->plot_no ??"-",$farmer->PlotImgUrl ??"-",
                            $farmer->farmer_plot_uniqueid ??"-",$farmer->plot_no ??"-"
                            );

                    array_push($payload_fields,"-",$farmer->area_in_acers ??"-",$farmer->land_ownership ??"-",$farmer->actual_owner_name ??"-",$farmer->survey_no ??"-" ,
                        $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp)->format('d-m-Y') : "-",
                        $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp)->format('H:i:s') : "-",
                        $farmer->UserApprovedRejected->name??"-",
                        $farmer->farmer->surveyor_name ??"-",$farmer->farmer->surveyor_mobile ??"-");
                 }

            }//end of state

        return $payload_fields;
    }

    public function headings(): array{
       $state_id = $this->state_id; 
        
        $header = ['Farmer UniqueID', 'Farmer Name', 'Mobile Access', 'Mobile Relation Owner', 'Mobile', 'No. of PLots','Total Plot Area (Acres)','Country',
                 'State', 'District', 'Taluka', 'Panchayat','Village', 'Latitude', 'Longitude', 'Date Survey', 'Time Survey',
                 'Remarks',
                 'PlotData','Plot Images', 'Plot Unique ID', 'Plot No'];


             if($state_id->state_id == 29){
                array_push($header,'Area in Acers','Land Ownership','Actual Owner Name','Survey No','PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','Surveyor Name', 'Surveyor Mobile');
             }else{
                array_push($header,'Area in (A.G)','Area in Acres','Land Ownership','Actual Owner Name','Survey No','PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','Surveyor Name', 'Surveyor Mobile');
             }
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
