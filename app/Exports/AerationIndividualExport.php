<?php

namespace App\Exports;

use App\Models\AerationValidation;
use App\Models\Aeration;
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

class AerationIndividualExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents,WithStyles,WithChunkReading
{
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($type, $unique_id, $plot_no, $status, $aeration_no) {
          $this->type = $type;
          $this->unique_id = $unique_id;
          $this->plot_no = $plot_no;
          $this->status = $status;
          $this->aeration_no = $aeration_no; 
          
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
        $aeration_no = $this->aeration_no;

        $farmer =   Aeration::query();
        $farmer = $farmer->where('farmer_plot_uniqueid',$unique_id);
        $farmer = $farmer->where('aeration_no',$aeration_no);
       
        $farmer = $farmer->with('farmerapproved','AerationImages');  
        $farmer = $farmer->get();
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;
        $aeration_no = $this->aeration_no;

        $payload_fields = [$farmer->farmerapproved->farmer_uniqueId ??"-", $farmer->farmerapproved->farmer_name ??"-", $farmer->farmerapproved->mobile_access ??"-",
                $farmer->farmerapproved->mobile_reln_owner ??"-", $farmer->farmerapproved->mobile ??"-",$farmer->farmerapproved->state ??"-",
                $farmer->aeration_no ??"-",   $farmer->pipe_no ??"-"                          
            ];

            if($status == 'Rejected'){
                $image = $farmer->AerationImages()->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('aeration_no',$farmer->aeration_no)->where('status','Rejected')->select('path')->get();

            }else{
                $image = $farmer->AerationImages()->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('aeration_no',$farmer->aeration_no)->where('status','Approved')->select('path')->get();
            }                 

          

            if($image->count() > 0){//check collection of image is present 
                foreach($image as $img){
                array_push($payload_fields, $img->path??"-");           
                }
                }else{
                array_push($payload_fields, "-","-");      // if no then add hypen      
            }


            $validator = AerationValidation::with('ValidatorUserDetail')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('aeration_no',$farmer->aeration_no)->latest()->first();

            array_push($payload_fields,$farmer->date_survey??"-",$farmer->time_survey??"-",$farmer->users->name??"-", $farmer->users->mobile??"-",  $validator->status??"-" , $validator->ValidatorUserDetail->name??"-", $validator->timestamp??"-" );            
            return $payload_fields;
    }

    public function headings(): array{
        $header = ['Farmer UniqueID', 'Farmer Name', 'Mobile Access', 'Mobile Relation Owner', 'Mobile', 'State',
        'Aeration','Plot No', 'Img-1','Img-2','Date', 'Time','Surveyor Name', 'Surveyor Mobile','L1Status','L1ValidatorName','L1ValidateTime'];
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
