<?php

namespace App\Exports;

use App\Models\Aeration;
use App\Models\PipeInstallation;
use App\Models\Polygon;
use App\Models\ViewerLocation;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AerationNoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
    protected $farmeruniqueid;
    protected $request;
    protected $organization;
    protected $aerationNos;

    public function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        // $this->request = json_decode($request);
        $this->request = $request; 
        $this->organization = $this->request->organization ?? null;
        $this->aerationNos = isset($this->request->aeration_no) ? (array) $this->request->aeration_no : [];
        //dd($request);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Aeration::whereHas('farmerapproved', function ($q) {
            $q->where('onboarding_form', 1);
    //dd($this->request->aeration_no);
            // Role-based filtering
            if (isset($this->request->rolename) && $this->request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $this->request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } elseif (isset($this->request->rolename) && $this->request->rolename != 'SuperAdmin' && $this->request->rolename != 'Viewer') {
                $VendorLocation = DB::table('vendor_locations')->where('user_id', $this->request->userid)->first();
                $q->whereIn('state_id', explode(',', $VendorLocation->state));
                if (!empty($VendorLocation->district)) {
                    $q->whereIn('district_id', explode(',', $VendorLocation->district));
                }
                if (!empty($VendorLocation->taluka)) {
                    $q->whereIn('taluka_id', explode(',', $VendorLocation->taluka));
                }
            }

            // Additional filters based on request parameters
            if (isset($this->request->state) && $this->request->state) {
                $q->where('state_id', 'like', $this->request->state);
            }
            if (isset($this->request->district) && $this->request->district) {
                $q->where('district_id', 'like', $this->request->district);
            }
            if (isset($this->request->taluka) && $this->request->taluka) {
                $q->where('taluka_id', 'like', $this->request->taluka);
            }
            if (isset($this->request->panchayats) && $this->request->panchayats) {
                $q->where('panchayat_id', 'like', $this->request->panchayats);
            }
            if (isset($this->request->village) && $this->request->village) {
                $q->where('village_id', 'like', $this->request->village);
            }
            if (isset($this->request->organization) && $this->request->organization) {
                $q->where('organization_id', 'like', $this->request->organization);
            }
        })
        ->with('farmerapproved')
       // ->where('aeration_no', $this->aerationNos) // Filter based on aeration_no array
        ->when('filter', function ($w) {
            // Apply additional filters based on request parameters
            if (isset($this->request->executive_onboarding) && $this->request->executive_onboarding) {
                $w->where('surveyor_id', $this->request->executive_onboarding);
            }
            if (isset($this->request->start_date) && $this->request->start_date) {
                $w->whereDate('created_at', '>=', $this->request->start_date);
            }
            if (isset($this->request->end_date) && $this->request->end_date) {
                $w->whereDate('created_at', '<=', $this->request->end_date);
            }
            if (isset($this->request->aeration_no) && $this->request->aeration_no) {
               // $w->whereDate('created_at', '<=', $this->request->end_date)
               $w->where('aeration_no', $this->aerationNos);
            }

            return $w;
        })
        ->latest();
       
       // dd($query);
        $farmers = $query->get(); // Use cursor to optimize memory usage
          //dd($farmers);
        return $farmers;
    }

    public function map($farmer): array
    {
        // Fetch required data from relationships and related models
        $plotAreas = Polygon::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
                        ->pluck('plot_area');
        $totalPolygonArea = $plotAreas->sum();

        // Fetch aerations filtered by selected aeration_no
        // $aerations = Aeration::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
        //                 ->whereIn('aeration_no', $this->aerationNos)
        //                 ->orderBy('date_survey')
        //                 ->get(['aeration_no', 'date_survey']);

        // $totalAeration = $aerations->count('aeration_no');

        // $aerationCreationDates = $aerations->pluck('date_survey')->map(function ($date) {
        //     $dateObj = $date ? \DateTime::createFromFormat('d/m/Y', $date) : null;
        //     return $dateObj ? $dateObj->format('Y-m-d') : "-";
        // })->toArray();
        
        

        // Prepare payload fields in the order of headings
        $payload_fields = [
            $farmer->financial_year ?? "-",
            $farmer->seasons->name ?? "-",
            $farmer->farmerapproved->organization->company ?? "-",
            $farmer->financial_year ?? "-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_plot_uniqueid ?? "-",
            $farmer->plot_no ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->document_no ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->area_in_acers ?? "-",
            $totalPolygonArea ?? "-",
            $farmer->pipe_no ?? "-",
            // $totalAeration ?? "-",
             $farmer->aeration_no ?? "-",
            $farmer->date_survey ?? "-",
            //implode(", ", $aerationCreationDates) ?? "-",
            $farmer->farmerapproved->state->name ?? "-",
            $farmer->farmerapproved->district->district ?? "-",
            $farmer->farmerapproved->taluka->taluka ?? "-",
            $farmer->farmerapproved->panchayat->panchayat ?? "-",
            $farmer->farmerapproved->village->village ?? "-",
            $farmer->surveyor->name ?? "-",
            $farmer->surveyor->mobile ?? "-",
            $farmer->l2_status ??"-",
            $farmer->aeration_validation->ValidatorUserDetail->name??"-",
            $farmer->aeration_validation->created_at??"-",
            str_replace('\\/', '/',$farmer->AerationImages()->select('path')->get()),
        ];

        return $payload_fields;
    }

    public function headings(): array
    {
        return [
            'Year',
            'Season',
            'Organisation Name',
            'Onboarding year',
            'Farmer UniqueID',
            'farmer plotId',
            'No of Plot',
            'Farmer Name',
            'Aadhar Card no.',
            'Mobile Number',
            'Onboarding area acres',
            'Total Polygon',
            'Pipe No',
            'Aeration No',
            'Aeration Date',
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
            'Surveyor Name',
            'Surveyor Mobile',
            'Status',
            'Validator Name',
            'Validation Date and Time',
            'Images',
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
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:AJ1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
