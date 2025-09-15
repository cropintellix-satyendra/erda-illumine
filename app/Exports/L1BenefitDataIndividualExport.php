<?php

namespace App\Exports;

use App\Models\BenefitDataValidation;
use App\Models\FarmerBenefit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;

class L1BenefitDataIndividualExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents,WithStyles,WithChunkReading,ShouldQueue
{
    use Exportable;
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($type, $unique_id, $status) {
          $this->type = $type;
          $this->unique_id = $unique_id;
          $this->status = $status;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $type = $this->type;
        $unique_id = $this->unique_id;
        $status = $this->status;

        $farmer =   FarmerBenefit::query();
        $farmer = $farmer->where('farmer_uniqueId',$unique_id);
        $farmer = $farmer->with('farmerapproved');  
        $farmer = $farmer->get();
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $status = $this->status;
        $payload_fields = [ $farmer->farmerapproved->farmer_uniqueId ??"-", $farmer->farmerapproved->farmer_name ??"-", $farmer->farmerapproved->mobile_access ??"-",
        $farmer->farmerapproved->mobile_reln_owner ??"-", $farmer->farmerapproved->mobile ??"-",$farmer->farmerapproved->state ??"-",
        $farmer->benefit??"-", $farmer->seasons??"-", $farmer->date_survey??"-", $farmer->surveyor_name??"-", $farmer->surveyor_mobile??"-",
      ];
            $validation = BenefitDataValidation::where('farmer_uniqueId',$farmer->farmer_uniqueId)->where('level','L-1-Validator')->latest()->first();
                    array_push($payload_fields, $validation->status??"Pending", $validation->timestamp??"-",$validation->ValidatorUserDetail->name??"-");



            return $payload_fields;

    }

    public function headings(): array{
        $header = ['Farmer UniqueID', 'Farmer Name', 'Mobile Access', 'Mobile Relation Owner', 'Mobile', 'State','Benefit',
                              'Seasons', 'DateSurvey','Surveyor Name','Surveyor Mobile','L1 Plotstatus','L1 Plotstatus DateTime','L1 Validator Name'];
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
