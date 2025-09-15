<?php

namespace App\Exports;

use App\Models\FarmerPlot;
use App\Models\PipeImgValidation;
use App\Models\PipeInstallation;
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

class PipeInstallationIndividualExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents,WithStyles,WithChunkReading
{
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($type, $unique_id, $plot_no, $status, $state_id) {
          $this->type = $type;
          $this->unique_id = $unique_id;
          $this->plot_no = $plot_no;
          $this->status = $status;
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

        $farmer =   PipeInstallation::query();
        $farmer = $farmer->where('farmer_plot_uniqueid',$unique_id);
        $farmer = $farmer->where('plot_no',$plot_no);
       
        $farmer = $farmer->with('farmerapproved');  
        $farmer = $farmer->get();

        return $farmer;
        // $farmer = PipeInstallation::whereHas('farmerapproved', function($q) use($request){
        //                                 $q->where('onboarding_form',1);
        //                                 $q->where('final_status_onboarding','Approved');
                                        
        //                                 // if(isset($request->rolename)  && $request->rolename != 'SuperAdmin'){
        //                                 //     $q->where('L2_appr_userid', $request->userid);
        //                                 // }

        //                                 if(isset($request->state)  && $request->state){
        //                                     $q->where('state_id','like',$request->state);
        //                                 }
        //                                 if(isset($request->district)  && $request->district){
        //                                      $q->where('district_id','like',$request->district);
        //                                 }
        //                                 if(isset($request->taluka)  && $request->taluka){
        //                                      $q->where('taluka_id','like',$request->taluka);
        //                                 }
        //                                 if(isset($request->l2_validator)  && $request->l2_validator){
        //                                     $q->where('L2_appr_userid','like',$request->l2_validator);
        //                                 }
        //                                 if(isset($request->panchayats)  && $request->panchayats){
        //                                      $q->where('panchayat_id','like',$request->panchayats);
        //                                 }
        //                                 if(isset($request->village)  && $request->village){
        //                                      $q->where('village_id','like',$request->village);
        //                                 }
        //                                 if(isset($request->executive_onboarding)  && $request->executive_onboarding){
        //                                      $q->where('surveyor_id',$request->executive_onboarding);
        //                                 }
        //                                 if(isset($request->start_date)  && $request->start_date){
        //                                     $q->whereDate('date_survey','>=',$request->start_date);
        //                                 }
        //                                 if(isset($request->end_date)  && $request->end_date){
        //                                     $q->whereDate('date_survey','<=',$request->end_date);
        //                                 }
        //                                 return $q;
        //                                 })
        //                         ->with('farmerapproved')
        //                         ->when($request,function($w) use($request){
        //                             if(isset($request->status)  && $request->status){
        //                                 // dd('dd');
        //                                 $w->where('status',$request->status);
        //                             }
        //                             return $w;
        //                         })
        //                         ->get();
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;

      $payload_fields = [ $farmer->farmerapproved->farmer_uniqueId ??"-", $farmer->farmerapproved->farmer_name ??"-",$farmer->farmerapproved->no_of_plots ??"-",$farmer->farmerapproved->total_plot_area ??"-",
                          $farmer->farmerapproved->country ??"-",$farmer->farmerapproved->state ??"-",$farmer->farmerapproved->district ??"-",$farmer->farmerapproved->taluka ??"-",
                          $farmer->farmerapproved->panchayat ??"-",$farmer->farmerapproved->village ??"-",$farmer->farmer_plot_uniqueid ??"-",$farmer->plot_no ??"-",$farmer->area_in_acers ??"-",
                          $farmer->plot_area ??"-",$farmer->ranges ??"-",$farmer->polygon_date_time ??"-",$farmer->date_survey ??"-",$farmer->date_time ??"-",$farmer->FormSubmitBy->name ??"-",$farmer->FormSubmitBy->mobile ??"-",
                          $farmer->installed_pipe ??"-"
                        ];
        
        $validator = PipeImgValidation::with('ValidatorUserDetail')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('plot_no',$farmer->plot_no)->latest()->first();

        

        if($farmer->pipes_location){
            $pipe_data = (Array)json_decode($farmer->pipes_location);//get installed pipe location
            foreach($pipe_data as $data){
                    array_push(
                        $payload_fields, $data->distance??"-",
                        'Lat: '.$data->lat.', '.'Lng: '.$data->lng??"-",
                        'Date: '.$data->date.', '.'Time: '.$data->time,
                        $farmer->FormSubmitBy->name ??"-",$farmer->FormSubmitBy->mobile ??"-"
                        );
            }
        }else{            
            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
                        ->where('plot_no',$farmer->plot_no)->where('status','Approved')->where('trash',0)->get();
// dd($pipe_data, $farmer->farmer_plot_uniqueid, $farmer->plot_no, $unique_id);
            foreach($pipe_data as $data){

                    array_push(
                        $payload_fields, $data->distance??"0",
                        'Lat: '.$data->lat.', '.'Lng: '.$data->lng??"-",
                        'Date: '.$data->date.', '.'Time: '.$data->time,
                        $farmer->FormSubmitBy->name ??"-",$farmer->FormSubmitBy->mobile ??"-",                        
                        );
                    if($validator){
                        array_push($payload_fields,$farmer->status, $validator->ValidatorUserDetail->name,$validator->ValidatorUserDetail->mobile,
                        $validator->timestamp ?  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $validator->timestamp)->format('Y-m-d H:i:s') : "-");
                    }
                        
            }
        }

        // $validator = DB::table('pipe_img_validation')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('plot_no',$farmer->plot_no)->latest()->first();
        // array_push($payload_fields,$farmer->status,$validator-> );



      return $payload_fields;
    }

    public function headings(): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;
      $header = ['Farmer UniqueID', 'Farmer Name', 'No. of Plots', 'Total Plot Area (Acres)', 'Country', 'State', 'District', 'Taluka', 'Panchayat',
                  'Village', 'Plot Unique Id', 'Plot No', 'Area in Acres', 'Area From Google map', 'Polygon', 'Date & Time of Polygon', 'Date Form Submitted','Time Form Submitted','Surveyor Name', 'Surveyor Mobile',
                  'No of pipes installed',
                //   'Pipe 1 Distance', 'Pipe 1 Location', 'Date & Time', 'Surveyor Name', 'Surveyor Mobile',
                //   'Pipe 2 Distance', 'Pipe 2 Location', 'Date & Time', 'Surveyor Name', 'Surveyor Mobile',
                //   'Pipe 3 Distance', 'Pipe 3 Location', 'Date & Time', 'Surveyor Name', 'Surveyor Mobile'
                ];
    
    $no_pipes = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $unique_id)->where('status','Approved')->where('trash',0)->count();
    // dd($no_pipes);
    for ($x = 1; $x <= $no_pipes; $x++) {
        array_push($header, 'Pipe '.$x.' Distance', 'Pipe '.$x.' Location', 'Date & Time', 'Surveyor Name', 'Surveyor Mobile','L1status','L1validatorName','L1validatorMobile','L1timestamp');
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
