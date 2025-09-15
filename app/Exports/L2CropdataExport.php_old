<?php

namespace App\Exports;

use App\Models\FarmerCropdata;
use App\Models\CropDataValidation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class L2CropdataExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // $request = json_decode($this->request);//whil development use this
        $request = $this->request;
        // dd($request); //in production use this
        $farmer = FarmerCropdata::whereHas('farmerapproved', function ($q) use ($request) {
            $q->where('onboarding_form', 1);

            if (isset($request->state)  && $request->state) {
                $q->where('state_id', 'like', $request->state);
            }
            if (isset($request->district)  && $request->district) {
                $q->where('district_id', 'like', $request->district);
            }
            if (isset($request->taluka)  && $request->taluka) {
                $q->where('taluka_id', 'like', $request->taluka);
            }
            if (isset($request->l2_validator)  && $request->l2_validator) {
                $q->where('L2_appr_userid', 'like', $request->l2_validator);
            }
            if (isset($request->panchayats)  && $request->panchayats) {
                $q->where('panchayat_id', 'like', $request->panchayats);
            }
            if (isset($request->village)  && $request->village) {
                $q->where('village_id', 'like', $request->village);
            }
            return $q;
        })
            ->when('filter', function ($w) use ($request) {
                if (isset($request->executive_onboarding)  && $request->executive_onboarding) {
                    $w->where('surveyor_id', $request->executive_onboarding);
                }
                if (isset($request->start_date)  && $request->start_date) {
                    $w->whereDate('created_at', '>=', $request->start_date);
                }
                if (isset($request->end_date)  && $request->end_date) {
                    $w->whereDate('created_at', '<=', $request->end_date);
                }

                if (isset($request->userid) && !empty($request->userid) && $request->status != 'Pending' && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer') {
                    $w->where('l2_apprv_reject_user_id', request('userid'));
                }
                if (isset($request->status) && !empty($request->status)) {
                    $w->where('status', 'Approved');
                    $w->where('l2_status', $request->status);
                }
                if (isset($request->l2_validator) && $request->l2_validator) {
                    //mainly this will be used when admin, viewer are downloading data
                    $w->where('l2_apprv_reject_user_id', $request->l2_validator);
                }
                return $w;
            })
            ->limit(100)->with('farmerapproved')->get();
        // dd($farmer);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
    //    dd($farmer);
        $payload_fields = [
            $farmer->farmerapproved->organization->company??"-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->own_area_in_acres ?? "-",
            $farmer->farmerapproved->lease_area_in_acres ?? "-",
            $farmer->farmerapproved->actual_owner_name ?? "-",
            $farmer->area_in_acers ?? "-",
            $farmer->area_in_acers/0.330578512396694 ?? "-",
            $farmer->plot_no ?? "-",
            $farmer->farmerapproved->country ?? "-",
            $farmer->farmerapproved->state ?? "-",
            $farmer->farmerapproved->district ?? "-",
            $farmer->farmerapproved->taluka ?? "-",
            $farmer->farmerapproved->panchayat ?? "-",
            $farmer->farmerapproved->village ?? "-",
            $farmer->PlotCropDetails->nursery ??"-",
            $farmer->farmer_plot_uniqueid ??"_", 
            $farmer->plot_no ??"_", 
            $farmer->area_in_acers ??"-",    //total area in acers
            $farmer->PlotCropDetails->crop_season_currentyrs ??"_", 
            $farmer->PlotCropDetails->yeild_lastyrs ??"_",
            $farmer->pipe_images??"NA",
            \Carbon\Carbon::createFromFormat('d/m/Y', $farmer->dt_transplanting)->format('d-m-Y'),
            $farmer->farmerapproved->land_ownership ??"_", 
            $farmer->date_survey ??"-", 
            $farmer->date_time ??"_",
            $farmer->farmerapproved->survey_no ??"_", 
            $farmer->surveyor_name ??"_", 
            $farmer->surveyor_email ?? "-", 
            $farmer->surveyor_mobile ??"-", 
            $farmer->PlotCropDetails->crop_season_lastyrs ?? "-",
            $farmer->PlotCropDetails->crop_season_currentyrs ?? "", 
            $farmer->PlotCropDetails->crop_variety_lastyrs ?? "-",
            $farmer->PlotCropDetails->crop_variety_currentyrs ?? "-",
            $farmer->PlotCropDetails->fertilizer_1_currentyrs ?? "-",
            $farmer->PlotCropDetails->fertilizer_2_currentyrs ?? "-",
            $farmer->PlotCropDetails->fertilizer_3_currentyrs ?? "-",
            $farmer->status ?? "-",
            $farmer->farmerapproved->users->name?? "-" 
            // $farmer->PlotCropDetails->yeild_currentyrs ?? "",





            // $farmer->farmerapproved->farmer_uniqueId ?? "-", 
            // $farmer->farmerapproved->farmer_name ?? "-", 
            // $farmer->farmerapproved->mobile_access ?? "-",

            // $farmer->farmerapproved->mobile_reln_owner ?? "-", 
            // $farmer->farmerapproved->mobile ?? "-", 
            // $farmer->farmerapproved->state ?? "-",
            // $farmer->farmerapproved->no_of_plots ?? "-",
            //  $farmer->farmerapproved->total_plot_area ?? "-",
            // $farmer->plot_no, $farmer->farmer_plot_uniqueid, 
            // $farmer->plot_no, $farmer->area_in_acers, 
            // $farmer->season, $farmer->crop_variety, 
            // $farmer->dt_irrigation_last,
            // $farmer->dt_ploughing, 
            // $farmer->dt_transplanting, 
            // $farmer->farmerapproved->land_ownership, 
            // $farmer->date_survey, 
            // $farmer->date_time,
            // $farmer->farmerapproved->survey_no, 
            // $farmer->surveyor_name, 
            // $farmer->surveyor_email ?? "-", 
            // $farmer->surveyor_mobile, 
            // $farmer->farmerplot_details->area_in_other,
            // $farmer->farmerplot_details->area_in_other, 
            // $farmer->farmerplot_details->area_in_acers, 
            // $farmer->farmerplot_details->area_other_awd, 
            // $farmer->farmerplot_details->area_acre_awd,
            // \Carbon\Carbon::createFromFormat('d/m/Y', $farmer->dt_transplanting)->format('d-m-Y'), 
            // \Carbon\Carbon::createFromFormat('d/m/Y', $farmer->dt_ploughing)->format('d-m-Y'),
            // $farmer->PlotCropDetails->crop_season_lastyrs ?? "", 
            // $farmer->PlotCropDetails->crop_season_currentyrs ?? "", 
            // $farmer->PlotCropDetails->crop_variety_lastyrs ?? "",
            // $farmer->PlotCropDetails->crop_variety_currentyrs ?? "" ?? "", 
            // $farmer->PlotCropDetails->fertilizer_1_name ?? "", 
            // $farmer->PlotCropDetails->fertilizer_1_lastyrs ?? "", 
            // $farmer->PlotCropDetails->fertilizer_1_currentyrs ?? "",
            // $farmer->PlotCropDetails->fertilizer_2_name ?? "", 
            // $farmer->PlotCropDetails->fertilizer_2_lastyrs ?? "", 
            // $farmer->PlotCropDetails->fertilizer_2_currentyrs ?? "",
            // $farmer->PlotCropDetails->fertilizer_3_name ?? "", 
            // $farmer->PlotCropDetails->fertilizer_3_lastyrs ?? "", 
            // $farmer->PlotCropDetails->fertilizer_3_currentyrs ?? "",
            // $farmer->PlotCropDetails->water_mng_lastyrs ?? "", 
            // $farmer->PlotCropDetails->water_mng_currentyrs ?? "",
            // $farmer->PlotCropDetails->yeild_lastyrs ?? "", 
            // $farmer->PlotCropDetails->yeild_currentyrs ?? "",



        ];
        $validator = CropDataValidation::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('level', 'L-2-Validator')->latest()->first();
        // dd($validator);
        array_push($payload_fields,  
        // $farmer->status, 
        // $validator->ValidatorUserDetail->name ?? "-"
    );

        return $payload_fields;
    }

    public function headings(): array
    {
        $header = [
            'Organisation Name',
            'Farmer UniqueID', 
            'Farmer Name', 
            // 'Mobile Access', 
            // 'Mobile Relation Owner',
            'Mobile',
            'Own Land area in Acres',
            'Lease Land area in Acres',
            'Lease Land Owner Name',
            'Total Area in Acres',
            'Total Area in Bigha',
            'Khatian no/Plot No',
            'country','State', 
            'District',
            'Block',
            'Panchayat',
            'Village',
            'Date of Nursery',
            'Plot Unique ID',
            'Plot No', 
            'Area of plots', 
            'Crop season',
            'Yield kg/acre',
            'Pipe installation date',
            'Date of Transplanting',
            'landOwnership',
            'Date',
            'Time', 
            'Survey No', 
            'Surveyor Name', 
            'Surveyor Email', 
            'Surveyor Mobile', 
            'Crop Season last Year',
            'Crop Season Current Year',
            'Crop Variety last Year', 
            'Crop Variety Current Year', 
            'Nitrogen current year kg/acre',
            'phosphorus current year kg/acre',
            'potassium  current year kg/acre',
            'Status',
            'Validator name',







            // 'Farmer UniqueID', 
            // 'Farmer Name', 
            // 'Mobile Access', 
            // 'Mobile Relation Owner', 
            // 'Mobile', 
            // 'State', 
            // 'No. of PLots', 
            // 'Total Plot Area',
            // 'CROPDATA', 
            // 'Plot Unique ID', 
            // 'Plot No', 
            // 'Area of plots', 
            // 'Seasons', 
            // 'Crop Variety', 
            // 'Lastirrigation date', 
            // 'Date of Ploughing', 
            // 'Date of Transplanting',
            // 'landOwnership', 
            // 'Date', 
            // 'Time', 
            // 'Survey No', 
            // 'Surveyor Name', 
            // 'Surveyor Email', 
            // 'Surveyor Mobile', 
            // 'l1_status', 
            // 'l1_validator', 
            // 'Area In Bigha',
            // 'Area In Acres', 
            // 'Area Chosen For AWD(Bigha)', 
            // 'Area Chosen For AWD(Acres)', 
            // 'Dates of Transplanting', 
            // ' Crop Season last Year', 
            // 'Crop Season Current Year',
            // ' Crop Variety last Year', 
            // 'Crop Variety Current Year', 
            // ' Fertilizer Management last Year', 
            // 'Fertilizer Management Current Year',
            // ' Fertilizer Management last Year', 
            // 'Fertilizer Management Current Year', 
            // ' Fertilizer Management last Year', 
            // 'Fertilizer Management Current Year',
            // ' Fertilizer Management last Year', 
            // 'Fertilizer Management Current Year',
            // 'Water Management Irrigation last Year', 
            // 'Water Management Irrigation Current Year', 
            // 'Water Management last Year', 
            // 'Water Management Current Year',

        ];
        return $header;
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
