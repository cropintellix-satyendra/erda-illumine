<?php

namespace App\Exports;

use App\Models\CropDataValidation;
use App\Models\FarmerCropdata;
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

class CropDataIndividualExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents,WithStyles,WithChunkReading,ShouldQueue
{
    use Exportable;
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($type, $unique_id, $plot_no, $status) {
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
        $farmer =   FarmerCropdata::query();
        $farmer = $farmer->where('farmer_plot_uniqueid',$unique_id);
        $farmer = $farmer->where('plot_no',$plot_no);
        $farmer = $farmer->with('farmerapproved');
        $farmer = $farmer->get();
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array{
        $type = $this->type;
        $unique_id = $this->unique_id;
        $plot_no = $this->plot_no;
        $status = $this->status;
        $payload_fields = [$farmer->farmerapproved->farmer_uniqueId ??"-", $farmer->farmerapproved->farmer_name ??"-", $farmer->farmerapproved->mobile_access ??"-",

                            $farmer->farmerapproved->mobile_reln_owner ??"-", $farmer->farmerapproved->mobile ??"-",$farmer->farmerapproved->state ??"-",
                            $farmer->farmerapproved->no_of_plots ??"-", $farmer->farmerapproved->total_plot_area ??"-",
                            $farmer->plot_no, $farmer->farmer_plot_uniqueid,$farmer->plot_no,$farmer->area_in_acers, $farmer->season  , $farmer->crop_variety, $farmer->dt_irrigation_last,
                            $farmer->dt_ploughing, $farmer->dt_transplanting,$farmer->farmerapproved->land_ownership,$farmer->date_survey,$farmer->date_time,
                            $farmer->farmerapproved->survey_no,$farmer->surveyor_name, $farmer->surveyor_email??"-", $farmer->surveyor_mobile,$farmer->farmerplot_details->area_in_other,
                            $farmer->farmerplot_details->area_in_other,$farmer->farmerplot_details->area_in_acers, $farmer->farmerplot_details->area_other_awd, $farmer->farmerplot_details->area_acre_awd,
                            \Carbon\Carbon::createFromFormat('d/m/Y', $farmer->dt_transplanting)->format('d-m-Y'), \Carbon\Carbon::createFromFormat('d/m/Y', $farmer->dt_ploughing)->format('d-m-Y'),
                            $farmer->PlotCropDetails->crop_season_lastyrs??"", $farmer->PlotCropDetails->crop_season_currentyrs??"", $farmer->PlotCropDetails->crop_variety_lastyrs??"",
                            $farmer->PlotCropDetails->crop_variety_currentyrs??""??"", $farmer->PlotCropDetails->fertilizer_1_name??"", $farmer->PlotCropDetails->fertilizer_1_lastyrs??"",$farmer->PlotCropDetails->fertilizer_1_currentyrs??"",
                            $farmer->PlotCropDetails->fertilizer_2_name??"", $farmer->PlotCropDetails->fertilizer_2_lastyrs??"", $farmer->PlotCropDetails->fertilizer_2_currentyrs??"",
                            $farmer->PlotCropDetails->fertilizer_3_name??"",$farmer->PlotCropDetails->fertilizer_3_lastyrs??"",$farmer->PlotCropDetails->fertilizer_3_currentyrs??"",
                            $farmer->PlotCropDetails->water_mng_lastyrs??"",$farmer->PlotCropDetails->water_mng_currentyrs??"",
                            $farmer->PlotCropDetails->yeild_lastyrs??"",$farmer->PlotCropDetails->yeild_currentyrs??"",
                        ];
        $validator = CropDataValidation::where('farmer_plot_uniqueid',$farmer->farmer_plot_uniqueid)->where('level','L-1-Validator')->latest()->first();

        array_push($payload_fields,  $farmer->status,$validator->ValidatorUserDetail->name??"-");

        return $payload_fields;
    }

    public function headings(): array{
        $header = ['Farmer UniqueID', 'Farmer Name', 'Mobile Access', 'Mobile Relation Owner', 'Mobile', 'State','No. of PLots','Total Plot Area',
        'CROPDATA','Plot Unique ID', 'Plot No', 'Area of plots', 'Seasons', 'Crop Variety','Lastirrigation date', 'Date of Ploughing', 'Date of Transplanting',
                  'landOwnership', 'Date', 'Time','Survey No','Surveyor Name', 'Surveyor Email','Surveyor Mobile','l1_status','l1_validator','Area In Bigha',
                  'Area In Acres','Area Chosen For AWD(Bigha)','Area Chosen For AWD(Acres)','Dates of Transplanting',' Crop Season last Year','Crop Season Current Year',
                  ' Crop Variety last Year','Crop Variety Current Year',' Fertilizer Management last Year','Fertilizer Management Current Year',
                  ' Fertilizer Management last Year','Fertilizer Management Current Year',' Fertilizer Management last Year','Fertilizer Management Current Year',
                  ' Fertilizer Management last Year','Fertilizer Management Current Year',
                  'Water Management Irrigation last Year','Water Management Irrigation Current Year','Water Management last Year','Water Management Current Year',
                ];
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
