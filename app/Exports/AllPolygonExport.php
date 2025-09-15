<?php

namespace App\Exports;

use App\Models\PipeImgValidation;
use App\Models\PipeInstallation;
use App\Models\Polygon;
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

class AllPolygonExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
    protected $farmeruniqueid;
    protected $request;
    protected $organization;
    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
        $this->organization = $request->organization ?? null;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    

    public function collection()
    {
        // $request = json_decode($this->request); // For development use
        $request = $this->request;
    
        // Retrieve and filter data from PipeInstallation
        $pipeInstallations = PipeInstallation::query()
            ->whereHas('farmerapproved', function ($q) use ($request) {
                $this->applyFilters($q, $request);
            })
            ->when($request, function ($w) use ($request) {
                $this->applyDateFilters($w, $request);
            })
            ->with('farmerapproved')
            ->cursor();
    
        // Retrieve and filter data from Polygon
        $polygons = Polygon::query()
            ->whereHas('farmerapproved', function ($q) use ($request) {
                $this->applyFilters($q, $request);
            })
            ->when($request, function ($w) use ($request) {
                $this->applyDateFilters($w, $request);
            })
            ->with('farmerapproved')
            ->cursor();
    
        $data = $pipeInstallations->concat($polygons);
    
        // \Log::info('Request', ['data' => $data]);
        return $data;
    }
    
        // Helper function to apply common filters
            protected function applyFilters($query, $request)
            {
                $query->where('onboarding_form', 1);
    
                if (isset($request->rolename) && $request->rolename == 'Viewer') {
                    $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                    $query->whereIn('state_id', explode(',', $viewerlocation->state));
                } elseif (isset($request->rolename) && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer') {
                    $vendorLocation = DB::table('vendor_locations')->where('user_id', $request->userid)->first();
                    $query->whereIn('state_id', explode(',', $vendorLocation->state));
                    if (!empty($vendorLocation->district)) {
                        $query->whereIn('district_id', explode(',', $vendorLocation->district));
                    }
                    if (!empty($vendorLocation->taluka)) {
                        $query->whereIn('taluka_id', explode(',', $vendorLocation->taluka));
                    }
                }
    
                $query->where('final_status_onboarding', 'Approved');
    
                if (isset($request->state) && $request->state) {
                    $query->where('state_id', 'like', $request->state);
                }
                if (isset($request->district) && $request->district) {
                    $query->where('district_id', 'like', $request->district);
                }
                if (isset($request->taluka) && $request->taluka) {
                    $query->where('taluka_id', 'like', $request->taluka);
                }
                if (isset($request->panchayats) && $request->panchayats) {
                    $query->where('panchayat_id', 'like', $request->panchayats);
                }
                if (isset($request->village) && $request->village) {
                    $query->where('village_id', 'like', $request->village);
                }
                if (isset($request->organization) && $request->organization) {
                    $query->where('organization_id', $request->organization);
                }
            }
    
            // Helper function to apply date filters
            protected function applyDateFilters($query, $request)
            {
                if (isset($request->start_date) && $request->start_date) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                }
                if (isset($request->end_date) && $request->end_date) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
                if (isset($request->executive_onboarding) && $request->executive_onboarding) {
                    $query->where('surveyor_id', $request->executive_onboarding);
                }
                if (isset($request->l1_validator) && $request->l1_validator) {
                    $query->where('apprv_reject_user_id', 'like', $request->l1_validator);
                }
            }


    // here you select the row that you want in the file
    public function map($farmer): array
    {
        $validatorDate = "-";
        if ($farmer->l2_status == "Rejected") {
            $validatorDate = $farmer->farmerapproved->L2_reject_timestamp ?? "-";
        } elseif ($farmer->l2_status == "Approved") {
            $validatorDate = $farmer->farmerapproved->L2_aprv_timestamp ?? "-";
        }

        if ($farmer->final_status == "Rejected") {
            $validatorDate = $farmer->reject_validation_detaill2->rejected_date_time ?? "-";
        } elseif ($farmer->final_status == "Approved") {
            $validatorDate = $farmer->reject_validation_detaill2->rejected_date_time ?? "-";
        }



         $payload_fields = [
            $farmer->financial_year ?? "-",
            $farmer->season ?? "-",
            $farmer->farmerapproved->organization->company??"-",
            $farmer->financial_year ?? "-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_plot_uniqueid ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->area_in_acers ?? "-",
            $farmer->ranges ?? "-",
            $farmer->plot_area ?? "-",
            $farmer->farmerapproved->state->name ?? "-",
            $farmer->farmerapproved->district->district ?? "-",
            $farmer->farmerapproved->taluka->taluka ?? "-",
            $farmer->farmerapproved->village->village ?? "-",
            $farmer->FormSubmitBy->name??"_",
            $farmer->polygon_date_time ?? "-",
            $farmer->l2_status??$farmer->final_status??"-",
            $farmer->validator->name??$farmer->reject_validation_detaill2->ValidatorUserDetail->name??"-",
            $validatorDate
        ];



        return $payload_fields;
    }

    public function headings(): array
    {
        $header = [
            'Year',
            'Season',
            'Organisation Name',
            'Onboarding year',
            'Farmer ID', 
            'Farmer PlotID',
            'Farmer Name', 
            'Mobile number',
            'Onboarding area in acres',
            'polygon area acres',
            'State',
            'District',
            'Taluka',
            'Village',
            'Surveyor Name',
            'Date of Survey',
            'status',
            'Validator name',
            'validation Date'
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
