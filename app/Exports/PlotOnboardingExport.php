<?php

namespace App\Exports;

use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FinalFarmer;
use App\Models\pipe_installations;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Models\FarmerCropcdata;
use App\Models\Benefit;
use App\Exports\FarmerExport;
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
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\ViewerLocation;


class PlotOnboardingExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading, ShouldQueue
{
    use Exportable;

    protected $farmeruniqueid;
    protected $request;
    protected $organization;
    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
        $this->organization = $request->organization ?? null;
       // dd($request);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // $request = json_decode($this->request);
        $request = $this->request;
        //dd($request);
        \Log::info('Data Fetched in PlotOnboardingExport...');
        $farmer = FarmerPlot::whereHas('final_farmers', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
            $q->where('onboard_completed', '!=', "Processing");
            if (isset($request->rolename) && $request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } //end of viewer

            if (isset($request->state) && $request->state) {
                $q->where('state_id', 'like', $request->state);
            }
            if (isset($request->district) && $request->district) {
                $q->where('district_id', 'like', $request->district);
            }
            if (isset($request->taluka) && $request->taluka) {
                $q->where('taluka_id', 'like', $request->taluka);
            }
            if (isset($request->panchayats) && $request->panchayats) {
                $q->where('panchayat_id', 'like', $request->panchayats);
            }
            if (isset($request->village) && $request->village) {
                $q->where('village_id', 'like', $request->village);
            }

            if (isset($request->executive_onboarding) && $request->executive_onboarding) {
                $q->where('surveyor_id', $request->executive_onboarding);
            }
            if (isset($request->start_date) && $request->start_date) {
                $q->whereDate('date_survey', '>=', $request->start_date);
            }
            if (isset($request->end_date) && $request->end_date) {
                $q->whereDate('date_survey', '<=', $request->end_date);
            }
            if (isset($request->organization) && $request->organization) {
                $q->where('organization_id',  $request->organization);
            }

            return $q;
        })->when('filter', function ($w) use ($request) {
               
                if (isset($request->l1_validator) && $request->l1_validator) {
                    //mainly this will be used when admin, viewer are downloading data
                    $w->where('aprv_recj_userid', $request->l1_validator);
                }
            })
            ->with('final_farmers')
            ->latest()
            ->cursor();

            // dd($farmer);
            // ->groupBy('farmer_uniqueId')
            // ->get();
            // ->where('plot_no',1)

            // dd($farmer);
           
        // $farmer = $farmer->map(function ($q) {
        //     $q->PlotImgUrl = url('download/') . '/' . 'PlotImg' . '/' . $q->farmer_id . '/' . $q->farmer_uniqueId . '/' . $q->plot_no;
        //     return $q;
        // });
        // dd($farmer[0]);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
        $plotAreas = Polygon::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
        ->pluck('plot_area');
        $totalPolygonArea = $plotAreas->sum();

        $no_of_plot = FarmerPlot::select('id','farmer_uniqueId')->where('farmer_uniqueId', $farmer->farmer_uniqueId)->count();
        // dd($farmer);
        $validatorDate = "-";
        if ($farmer->final_farmers->final_status == "Rejected") {
            $validatorDate = $farmer->final_farmers->L2_reject_timestamp ?? "-";
        } elseif ($farmer->final_farmers->final_status == "Approved") {
            $validatorDate = $farmer->final_farmers->L2_aprv_timestamp ?? "-";
        }


        $validatorName ="-";
        if($farmer->final_farmers->final_status == "Rejected") {
            $validatorName = $farmer->final_farmers->FinalUserRejected->name ?? "-";
        } elseif ($farmer->final_farmers->final_status == "Approved") {
            $validatorName = $farmer->final_farmers->FinalUserApproved->name ?? "-";
        }
  
    
        $payload_fields = [
            $farmer->final_farmers->financial_year ?? "-",
            $farmer->final_farmers->seasons->name ?? "-",
            $farmer->final_farmers->organization->company ?? "-",
            $farmer->final_farmers->financial_year ?? "-",
            $farmer->final_farmers->farmer_uniqueId ?? "-",
            $farmer->farmer_plot_uniqueid ?? "-",
            $no_of_plot ?? "-",
            $farmer->final_farmers->farmer_name ?? "-",
            $farmer->final_farmers->mobile ?? "-",
            $farmer->final_farmers->gender ?? "-",
            $farmer->final_farmers->guardian_name ?? "-",
            $farmer->final_farmers->area_in_acers ?? "-",
            $farmer->pipeinstallation->area_in_acers ?? "-",
            // $totalPolygonArea ?? "-", 
            $farmer->final_farmers->state->name ?? "-",
            $farmer->final_farmers->district->district ?? "-",
            $farmer->final_farmers->taluka->taluka ?? "-",
            $farmer->final_farmers->panchayat->panchayat ?? "-",
            $farmer->final_farmers->village->village ?? "-",
            $farmer->final_farmers->surveyor->name ?? "-",
            $farmer->final_farmers->surveyor->mobile ?? "-",
            $farmer->final_farmers->date_survey ?? "-",
            $farmer->final_farmers->final_status ?? "-",
            $validatorName ,
            $validatorDate
        ];
    
        return $payload_fields;
    }
    
    public function headings(): array
    {
        $request = $this->request;
        $header = [
            'Year',
            'Season',
            'Organization Name',
            'Onboarding Year',
            'Farmer UniqueID', 
            'Farmer PlotID',
            'No of Plot',
            'Farmer Name', 
            'Mobile Number',
            'Gender',
            'Guardian Name',
            'onboarding Area in Acres',
            'Polygon Area',
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
            'Surveyor Name',
            'Surveyor Mobile',
            'Date of Survey',
            'Status',
            'validator name',
            'validaton Date'
          
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
