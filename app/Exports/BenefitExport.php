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

class BenefitExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
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
        $request = $this->request;
        // dd($request->all());
        $farmer = FarmerBenefit::whereHas('farmerapproved', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
            $q->where('final_status_onboarding', 'Approved');
            if (isset($request->rolename)  && $request->rolename != 'SuperAdmin') {
                $q->where('L2_appr_userid', $request->userid);
            }
            if (isset($request->state)  && $request->state) {
                $q->where('state_id', 'like', $request->state);
            }
            if (isset($request->district)  && $request->district) {
                $q->where('district_id', 'like', $request->district);
            }
            if (isset($request->taluka)  && $request->taluka) {
                $q->where('taluka_id', 'like', $request->taluka);
            }
            if (isset($request->panchayats)  && $request->panchayats) {
                $q->where('panchayat_id', 'like', $request->panchayats);
            }
            if (isset($request->village)  && $request->village) {
                $q->where('village_id', 'like', $request->village);
            }
            if (isset($request->l2_validator)  && $request->l2_validator) {
                $q->where('L2_appr_userid', 'like', $request->l2_validator);
            }

            if (isset($request->executive_onboarding)  && $request->executive_onboarding) {
                $q->where('surveyor_id', $request->executive_onboarding);
            }
            if (isset($request->start_date)  && $request->start_date) {
                $q->whereDate('date_survey', '>=', $request->start_date);
            }
            if (isset($request->end_date)  && $request->end_date) {
                $q->whereDate('date_survey', '<=', $request->end_date);
            }
            return $q;
        })
            ->with('farmerapproved')->get();
            // dd($farmer);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
        // dd($farmer->farmerapproved);
        $payload_fields = [
            $farmer->farmerapproved->organization->company??"-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->area_in_acers ?? "-",
            $farmer->farmerplot_details->area_acre_awd ?? "_",
            $farmer->farmerapproved->mobile_access ?? "-",
            $farmer->farmerapproved->mobile_reln_owner ?? "-",
            $farmer->farmerapproved->state ?? "-",
            $farmer->benefit ?? "-",
            $farmer->seasons ?? "-",
            $farmer->surveyor_name ?? "-",
            $farmer->surveyor_mobile ?? "-",
            $farmer->status ?? "-",
            $farmer->farmerapproved->path ?? "-",
            $farmer->farmerapproved->path ?? "-",
            
        ];
        return $payload_fields;
        
    }

    public function headings(): array
    {
        $header = [
            'Organisation Name',
            'Farmer UniqueID',
            'Farmer Name',
            'Mobile',
            'Total Area in Acres',
            'Total AWD Area in Acres',
            'Mobile Access',
            'Mobile Relation Owner',
            'State',
            'Benefit',
            'Seasons',
            'Surveyor Name',
            'Surveyor Mobile',
            'Status',
            'Image 1',
            'Image 2',
            'Validator name',
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
