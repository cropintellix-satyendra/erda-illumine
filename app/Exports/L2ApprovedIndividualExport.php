<?php

namespace App\Exports;

use App\Models\FinalFarmer;
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
use Carbon\Carbon;

class L2ApprovedIndividualExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents,WithStyles,WithChunkReading
{
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
       

        $farmer =   FinalFarmer::query();
     
        $farmer = $farmer->where('farmer_uniqueId',$unique_id);
        
        if($type == 'Individuals'){//excel download request come from single plot page
           $farmer = $farmer->where('plot_no',$plot_no);
        }
        $farmer = $farmer->with('ApprvFarmerPlotImages','ApprvFarmerPlot');  
        $farmer = $farmer->get();
       
        $farmer = $farmer->map(function($q){
            $q->PlotImgUrl = url('download/').'/'.'Apprvplot'.'/'.'PlotImg'.'/'.$q->id.'/'.$q->farmer_uniqueId.'/'.$q->plot_no;
            return $q;
        });
        // dd($farmer);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;
        $state_id = $this->state_id; 

        $payload_fields = [$farmer->organization->company ??"_", $farmer->farmer_uniqueId ??"-", $farmer->farmer_name ??"-",$farmer->gender ??"-",$farmer->guardian_name ??"-",$farmer->mobile ??"-", $farmer->mobile_access ??"-",
                            $farmer->mobile_reln_owner ??"-",$farmer->aadhaar ??"-", 
                            $farmer->no_of_plots ??"-",];
       
        
       
                    // $payload_fields = [$farmer->organization->company ??"_", $farmer->farmer_uniqueId ??"-", $farmer->farmer_name ??"-",$farmer->gender ??"-", $farmer->guardian_name ??"-",$farmer->mobile ??"-", $farmer->mobile_access ??"-",
                    //         $farmer->mobile_reln_owner ??"-",$farmer->aadhaar ??"-", $farmer->own_area_in_acres ??"-",$farmer->lease_area_in_acres ??"-",$farmer->actual_owner_name ??"-",$farmer->area_in_acers ??"-",$farmer->no_of_plots ??"-",
                    //         $farmer->country ??"-",$farmer->state ??"-",$farmer->district ??"-",$farmer->taluka ??"-",$farmer->panchayat ??"-",$farmer->village ??"-",$farmer->remarks ??"-",$farmer->L2_aprv_timestamp ??"NA",
                    //         $farmer->date_survey??"-",$farmer->time_survey??"-",$farmer->surveyor_name??"-",$farmer->PlotImgUrl??"NA",$farmer->plotowner_sign??"-",$farmer->plotowner_sign??"-",$farmer->farmer_photo??"-",$farmer->aadhaar_photo??"-",
                    //         $farmer->others_photo??"-",$farmer->final_status_onboarding??"-",$farmer->FinalUserApprovedRejected->name??"-",
                    //     ];
                            
                    // dd($farmer->state_id == 29);
        if($farmer->state_id == 36){
            // dd('in');
                //need to add plot area for telangana of converted data
                $farmerplots =  FinalFarmer::where('farmer_uniqueId',$farmer->farmer_uniqueId)->get();
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
                            $farmer->country ??"-",$farmer->state ??"-",$farmer->district ??"-",
                            $farmer->taluka ??"-",$farmer->panchayat ??"-",$farmer->village ??"-",$farmer->latitude ??"-",$farmer->longitude ??"-",
                            $farmer->date_survey ??"-",$farmer->time_survey ??"-",$farmer->remarks ??"-",$farmer->plot_no ??"-",$farmer->PlotImgUrl ??"-",
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
                  $farmer->final_status,
                        $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('d-m-Y') : "-",
                        $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('H:i:s') : "-",
                        $farmer->UserApprovedRejected->name??"-", $farmer->final_status_onboarding ??"-",
                        $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('d-m-Y') : "-",
                        $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('H:i:s') : "-" , $farmer->FinalUserApprovedRejected->name??"-", $farmer->surveyor_name ??"-",$farmer->surveyor_mobile ??"-");
            }else{
                 if($state_id->state_id == 29){
                    // dd('in');
                    // dd($state_id);
                      array_push($payload_fields,$farmer->total_plot_area??"-"  ,
                            $farmer->country ??"-",$farmer->state ??"-",$farmer->district ??"-",
                            $farmer->taluka ??"-",$farmer->panchayat ??"-",$farmer->village ??"-",$farmer->remarks ??"-",$farmer->L2_aprv_timestamp ??"NA",$farmer->date_survey??"-",$farmer->time_survey??"-",$farmer->latitude ??"-",$farmer->longitude ??"-",
                            $farmer->plot_no ??"-",$farmer->PlotImgUrl ??"-",$farmer->plotowner_sign??"-",$farmer->plotowner_sign??"-",$farmer->farmer_photo??"-",$farmer->aadhaar_photo??"-",$farmer->others_photo??"-",$farmer->final_status_onboarding??"-",$farmer->FinalUserApprovedRejected->name??"-",
                            $farmer->farmer_plot_uniqueid ??"-",$farmer->plot_no ??"-"
                            );
                    array_push($payload_fields,$farmer->own_area_in_acres ??"-",$farmer->lease_area_in_acres ??"-",$farmer->actual_owner_name ??"-", $farmer->area_in_acers ??"-",$farmer->area_in_acers/0.330578512396694 ??"-",$farmer->no_of_plots ??"-",$farmer->land_ownership ??"-",$farmer->actual_owner_name ??"-",$farmer->survey_no ??"-",
                    $farmer->final_status,
                        $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('d-m-Y') : "-",
                        $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('H:i:s') : "-",
                        $farmer->UserApprovedRejected->name??"-", $farmer->final_status_onboarding ??"-",
                        $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('d-m-Y') : "-",
                        $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('H:i:s') : "-" , $farmer->FinalUserApprovedRejected->name??"-", $farmer->surveyor_name ??"-",$farmer->surveyor_mobile ??"-");
                 }else{
                    // dd('in');
                    array_push($payload_fields,$farmer->total_plot_area??"-"  ,
                            $farmer->country ??"-",$farmer->state ??"-",$farmer->district ??"-",
                            $farmer->taluka ??"-",$farmer->panchayat ??"-",$farmer->village ??"-",$farmer->remarks ??"-",$farmer->L2_aprv_timestamp ??"NA",$farmer->date_survey??"-",$farmer->time_survey??"-",$farmer->latitude ??"-",$farmer->longitude ??"-",
                            $farmer->plot_no ??"-",$farmer->PlotImgUrl ??"-",$farmer->plotowner_sign??"-",$farmer->plotowner_sign??"-",$farmer->farmer_photo??"-",$farmer->aadhaar_photo??"-",$farmer->others_photo??"-",$farmer->final_status_onboarding??"-",$farmer->FinalUserApprovedRejected->name??"-",
                            $farmer->farmer_plot_uniqueid ??"-",$farmer->plot_no ??"-"
                            );
                    //   array_push($payload_fields,$farmer->total_plot_area??"-"  ,
                    //         $farmer->country ??"-",$farmer->state ??"-",$farmer->district ??"-",
                    //         $farmer->taluka ??"-",$farmer->panchayat ??"-",$farmer->village ??"-",$farmer->latitude ??"-",$farmer->longitude ??"-",
                    //         $farmer->date_survey ??"-",$farmer->time_survey ??"-",$farmer->remarks ??"-",$farmer->plot_no ??"-",$farmer->PlotImgUrl ??"-",
                    //         $farmer->farmer_plot_uniqueid ??"-",$farmer->plot_no ??"-"
                    //         );

                    array_push($payload_fields,"-",$farmer->area_in_acers ??"-",$farmer->land_ownership ??"-",$farmer->actual_owner_name ??"-",$farmer->survey_no ??"-",
                        $farmer->final_status,
                        $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('d-m-Y') : "-",
                        $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('H:i:s') : "-",
                        $farmer->UserApprovedRejected->name??"-", $farmer->final_status_onboarding ??"-",
                        $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('d-m-Y') : "-",
                        $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('H:i:s') : "-" , $farmer->FinalUserApprovedRejected->name??"-", $farmer->surveyor_name ??"-",$farmer->surveyor_mobile ??"-");
                 }
            }

        return $payload_fields;
    }

    public function headings(): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;
        $state_id = $this->state_id; 

        $header = ['Organization_name','Farmer UniqueID', 'Farmer Name','Gender','Guardian Name', 'Mobile','Mobile Access', 'Mobile Relation Owner', 'Aadhar Number',
        'No. of PLots', 'Total Plot Area (Acres)','Country',
        'State', 'District', 'Block', 'Panchayat','Village', 'Village Remarks','Date & Time of Onboarding','Date Form Submitted',
        'Time Form Submitted',
        'Latitude', 'Longitude',
        'PlotData','Land Record Photo','Farmer Signature','Lease Land Owner Signature','Farmer Photo','Aadhar Photo','Others Photo','Status','Validator name', 'Plot Unique ID', 'Plot No'];
        
        // $header = ['Organization_name','Farmer UniqueID', 'Farmer Name','Gender','Guardian Name', 'Mobile', 'Mobile Access', 'Mobile Relation Owner', 'Aadhar Number',
        // 'Own Land area in Acres','Lease Land area in Acres','Lease Land Owner Name','Total Area in Acres','Khatian no/Plot No','Country','State','District','Block','Panchayat',
        // 'Village','Village Remaks','Date & Time of Onboarding','Date Form Submitted','Time Form Submitted','Surveyor Name','Land Record Photo','Farmer Signature','Lease Land Owner Signature',
        // 'Farmer Photo','Aadhar Photo','Others Photo','Status','Validator name',];
    


     if($state_id->state_id == "29"){
        array_push($header,'Own Land area in Acres','Lease Land area in Acres','Lease Land Owner Name','Total Area in Acres','Total Area in Bigha ','Khatian no/Plot No','Land Ownership','Actual Owner Name','Survey No','L1PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','L2 PlotStatus','L2 Plotstatus update Date','L2 Plotstatus update Time','L2 Final Validator Name','Surveyor Name', 'Surveyor Mobile');
     }else{
        array_push($header,'Area in (A.G)','Area in Acres','Land Ownership','Actual Owner Name','Survey No','L1PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','L2 PlotStatus','L2 Plotstatus update Date','L2 Plotstatus update Time','L2 Final Validator Name','Surveyor Name', 'Surveyor Mobile');
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
