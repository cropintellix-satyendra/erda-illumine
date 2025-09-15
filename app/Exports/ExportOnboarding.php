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

class ExportOnboarding implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
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
            'Mobile',
            'Gender',
            'Guardian name',
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
            'Date survey',
            'Own area in acres',
            'Lease area in acres',
            'Total Area in Acres',
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

        return [
            $row->organization->company ??"-",
            $row->farmer_uniqueId??"-",
            $row->farmer_name??"-",
            $row->mobile??"-",
            $row->gender??"-",
            $row->guardian_name??"-",
            $row->state->name??"-",
            $row->district->district??"-",
            $row->taluka->taluka??"-",
            $row->panchayat->panchayat??"-",
            $row->village->village??"-",
            $row->date_survey??"-",
            $row->own_area_in_acres??"-",
            $row->lease_area_in_acres??"-",
            $row->area_in_acers??"-",
            $row->financial_year??"-",
            $row->season??"-",
            $row->users->name??"-",
            $row->users->mobile ?? "-",
            $row->final_status??"-",
            $row->validator->name??"-",
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
