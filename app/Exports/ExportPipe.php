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
use App\Models\ViewerLocation;
use AWS\CRT\HTTP\Request;

class ExportPipe implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
  
    protected $data;
   

    public function __construct($data)
    {
        $this->data = $data;
        
    }

    public function collection()
    {
        return $this->data;
    }


    public function headings(): array
    {
      
        return [
            'Organization Name',
            'Farmer uniqueID',
            'Farmer Name',
            'Farmer Plot Unique ID',
            'Pipe No',
            'Pipe Install Date',
            'Onboarding Date',
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
            'Financial Year',
            'Season',
            'Surveyor Name',
            'Surveyor Mobile',
            'Status',
            'Validator Name',
        ];
    }

    public function map($row): array
    {
        // dd($row);
        $validatorName = "-";
        if ($row->l2status == "Rejected") {
            $validatorName = $row->reject_validation_detail->ValidatorUserDetail->name ?? "-";
        } elseif ($row->l2status == "Approved") {
            $validatorName = $row->approve_validation_detail->ValidatorUserDetail->name ?? "-";
        }

        return [
            $row->farmerapproved->organization->company??"-",
            $row->farmer_uniqueId??"-",
            $row->farmerapproved->farmer_name??"-",
            $row->farmer_plot_uniqueid??"-",
            $row->pipe_no??"-",
            $row->date??"-",
            $row->farmerapproved->date_survey??"-",
            $row->farmerapproved->state->name??"-",
            $row->farmerapproved->district->district??"-",
            $row->farmerapproved->taluka->taluka??"-",
            $row->farmerapproved->panchayat->panchayat??"-",
            $row->farmerapproved->village->village??"-",
            $row->financial_year??"-",
            $row->season??"-",
            $row->farmerapproved->surveyor->name?? "-",
            $row->farmerapproved->surveyor->mobile?? "-",
            $row->l2status??"-",
            $validatorName,
            
            
        ];
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
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
