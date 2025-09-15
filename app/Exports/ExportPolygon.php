<?php

namespace App\Exports;

use App\Models\AerationValidation;
use App\Models\Aeration;
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
use App\Models\ViewerLocation;
use AWS\CRT\HTTP\Request;
use Illuminate\Support\Facades\DB as FacadesDB;

class ExportPolygon implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
  
    protected $data;
    // protected $sumPlotArea;

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
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
            'Polygon Date',
            'Plot Area',
            'Financial Year',
            'Season',
            'Surveyor Name',
            'Surveyor Mobile',
            'Status',
            'Onboarding Date',
            'Validator Name',
        ];
    }

    public function map($row): array
    {
        // dd($row);

        // $sumPlotArea = PipeInstallation::select('farmer_uniqueId', FacadesDB::raw('SUM(plot_area) as total_plot_area'))
        // ->groupBy('farmer_uniqueId')
        // ->pluck('total_plot_area', 'farmer_uniqueId');

        // // Retrieve the sum of plot_area for the current farmer_uniqueId
        // $totalPlotArea = $sumPlotArea[$row->farmer_uniqueId] ?? null;

        // $sumPlotArea = $this->sumPlotArea[$row->farmer_uniqueId] ?? null;
           
      
        return [

            $row->farmerapproved->organization->company ??"-",
            $row->farmer_uniqueId??"-",
            $row->farmerapproved->farmer_name??"-",
            $row->farmer_plot_uniqueid??"-",
            $row->farmerapproved->state->name??"-",
            $row->farmerapproved->district->district??"-",
            $row->farmerapproved->taluka->taluka??"-",
            $row->farmerapproved->panchayat->panchayat??"-",
            $row->farmerapproved->village->village??"-",
            $row->polygon_date_time??"-",
            // $sumPlotArea ?? "-",
            // $totalPlotArea??"-", 
            $row->plot_area??"-",
            $row->financial_year??"-",
            $row->seasons->name??"-",
            $row->FormSubmitBy->name ?? "-",
            $row->FormSubmitBy->mobile ?? "-",
            $row->final_status??"-",
            $row->farmerapproved->date_survey??"-",
            $row->PolygonValidation->ValidatorUserDetail->name ?? "-",
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
