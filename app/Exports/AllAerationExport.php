<?php

namespace App\Exports;

use App\Models\AerationValidation;
use App\Models\Aeration;
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
use App\Models\Village;

class AllAerationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
    protected $farmeruniqueid;
    protected $request;
    protected $organization;
    protected $maxAerations;

    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
        $this->organization = $request->organization ?? null;
        $this->maxAerations = 4;

       // dd($request);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // $request = json_decode($this->request);//whil development use this
       $request = $this->request; //in production use this
   // dd($request->organization);
        $farmer = Aeration::whereHas('farmerapproved', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
            if (isset($request->rolename) && $request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } //end of viewer 
            if (isset($request->rolename)  && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer') {
                $VendorLocation = DB::table('vendor_locations')->where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $VendorLocation->state));
                if (!empty($VendorLocation->district)) {
                    $q->whereIn('district_id', explode(',', $VendorLocation->district));
                }
                if (!empty($VendorLocation->taluka)) {
                    $q->whereIn('taluka_id', explode(',', $VendorLocation->taluka));
                }
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
            if (isset($request->organization)  && $request->organization) {
                $q->where('organization_id', 'like', $request->organization);
            }
            // if(isset($request->l2_validator)  && $request->l2_validator){
            //     $q->where('L2_appr_userid','like',$request->l2_validator);
            // }                                        
            return $q;
            
        })
            ->with('farmerapproved')
            ->when('fliter', function ($w) use ($request) {
                if (isset($request->executive_onboarding)  && $request->executive_onboarding) {
                    $w->where('surveyor_id', $request->executive_onboarding);
                }
                if (isset($request->start_date)  && $request->start_date) {
                    $w->whereDate('created_at', '>=', $request->start_date);
                }
                if (isset($request->end_date)  && $request->end_date) {
                    $w->whereDate('created_at', '<=', $request->end_date);
                }

                return $w;
            })
            ->latest()
            ->cursor();
           
            // ->get();
            // dd($farmer[0]);
        return $farmer;
    }

    public function map($farmer): array
    {
        $plotAreas = Polygon::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
                        ->pluck('plot_area');
        
        $totalPolygonArea = $plotAreas->sum();
    
        // $aerations = Aeration::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('pipe_no')
        //             ->get(['aeration_no', 'date_survey']);
        $aerations = Aeration::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
        ->orderBy('aeration_no')
        ->get(['aeration_no', 'date_survey']);
    
        $totalAeration = $aerations->count('aeration_no');
    
        // Map over the first 4 aeration dates
        // $aerationCreationDates = $aerations->take(4)->map(function ($aeration) {
        //     return $aeration->date_survey ? \DateTime::createFromFormat('d/m/Y', $aeration->date_survey)->format('Y-m-d') : "-";
        // });
    
        // // Extract individual dates from the mapped collection
        // $aeration1CreationDates = $aerationCreationDates->get(0) ?? "-";
        // $aeration2CreationDates = $aerationCreationDates->get(1) ?? "-";
        // $aeration3CreationDates = $aerationCreationDates->get(2) ?? "-";
        // $aeration4CreationDates = $aerationCreationDates->get(3) ?? "-";

        $sortedDates = $aerations->map(function ($aeration) {
            return $aeration->date_survey 
                ? \DateTime::createFromFormat('d/m/Y', $aeration->date_survey)->format('Y-m-d') 
                : "-";
        })->toArray();

        $aerationDates = [];
        for ($i = 0; $i < $this->maxAerations; $i++) {
            // If a date exists at this index, use it; otherwise, use placeholder
            $aerationDates[$i] = isset($sortedDates[$i]) ? $sortedDates[$i] : "-";
        }

    
        $payload_fields = [
            $farmer->financial_year ?? "-",
            $farmer->seasons->name ?? "-",
            $farmer->farmerapproved->organization->company ?? "-",
            $farmer->financial_year ?? "-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_plot_uniqueid ?? "-",
            $farmer->plot_no ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->document_no?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->area_in_acers ?? "-",
            $totalPolygonArea ?? "-",
            $totalAeration ?? "-",
            $farmer->farmerapproved->state->name ?? "-",
            $farmer->farmerapproved->district->district ?? "-",
            $farmer->farmerapproved->taluka->taluka ?? "-",
            $farmer->farmerapproved->panchayat->panchayat ?? "-",
            $farmer->farmerapproved->village->village ?? "-",
            $farmer->surveyor->name ?? "-",
            $farmer->surveyor->mobile ?? "-",
            $farmer->date_survey ?? "-",
            str_replace('\\/', '/',$farmer->AerationImages()->select('path')->get()),
            // $aeration1CreationDates,
            // $aeration2CreationDates,
            // $aeration3CreationDates,
            // $aeration4CreationDates,
        ];
        
        return array_merge($payload_fields, $aerationDates);
        // return $payload_fields;
    }
    
    public function headings(): array
    {
        $headers  = [
            'Year',
            'Season',
            'Organisation Name',
            'Onboarding year',
            'Farmer UniqueID',
            'farmer plotId',
            'No of Plot',
            'Farmer Name',
            'Aadhar Card no',
            'Mobile Number',
            'Onboarding area acres',
            'Total Polygon',
            'Total Areation',
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
            'Surveyor Name',
            'Surveyor Mobile',
            'Date of Survey',
            'Images',
            // 'Aeration 1',
            // 'Aeration 2',
            // 'Aeration 3',
            // 'Aeration 4',
        ];

        for ($i = 1; $i <= $this->maxAerations; $i++) {
            $headers[] = "Aeration $i";
        }
        return $headers;

        // return $header;
    }

    // public function map($farmer): array
    // {
    //     $plotAreas = Polygon::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
    //                     ->pluck('plot_area');
    //     $totalPolygonArea = $plotAreas->sum();

    //     // Fetch aeration data grouped by plot_no and pipe_no
    //     $aerations = Aeration::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
    //                     ->get(['plot_no', 'pipe_no', 'aeration_no', 'date_survey'])
    //                     ->groupBy(['plot_no', 'pipe_no']);

    //     // Initialize result rows
    //     $rows = [];

    //     foreach ($aerations as $plotNo => $pipes) {
    //         foreach ($pipes as $pipeNo => $records) {
    //             $dates = $records->take(4)->pluck('date_survey')->map(function ($date) {
    //                 return $date ? \DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d') : "-";
    //             });

    //             // Create a row for each plot and pipe combination
    //             $rows[] = [
    //                 'Plot No' => $plotNo,
    //                 'Pipe No' => $pipeNo,
    //                 'Aeration 1' => $dates->get(0) ?? "-",
    //                 'Aeration 2' => $dates->get(1) ?? "-",
    //                 'Aeration 3' => $dates->get(2) ?? "-",
    //                 'Aeration 4' => $dates->get(3) ?? "-",
    //             ];
    //         }
    //     }

    //     // Combine base farmer data with aeration rows
    //     $payload_fields = [
    //         $farmer->financial_year ?? "-",
    //         $farmer->seasons->name ?? "-",
    //         $farmer->farmerapproved->organization->company ?? "-",
    //         $farmer->financial_year ?? "-",
    //         $farmer->farmerapproved->farmer_uniqueId ?? "-",
    //         $farmer->farmerapproved->farmer_plot_uniqueid ?? "-",
    //         $farmer->plot_no ?? "-",
    //         $farmer->farmerapproved->farmer_name ?? "-",
    //         $farmer->farmerapproved->document_no ?? "-",
    //         $farmer->farmerapproved->mobile ?? "-",
    //         $farmer->farmerapproved->area_in_acers ?? "-",
    //         $totalPolygonArea ?? "-",
    //         $farmer->farmerapproved->state->name ?? "-",
    //         $farmer->farmerapproved->district->district ?? "-",
    //         $farmer->farmerapproved->taluka->taluka ?? "-",
    //         $farmer->farmerapproved->panchayat->panchayat ?? "-",
    //         $farmer->farmerapproved->village->village ?? "-",
    //         $farmer->surveyor->name ?? "-",
    //         $farmer->surveyor->mobile ?? "-",
    //         $farmer->date_survey ?? "-",
    //     ];

    //     // Flatten rows by adding aeration data for each plot/pipe
    //     $result = [];
    //     foreach ($rows as $row) {
    //         $result[] = array_merge($payload_fields, $row);
    //     }

    //     return $result;
    // }

    // public function headings(): array
    // {
    //     return [
    //         'Year',
    //         'Season',
    //         'Organisation Name',
    //         'Onboarding year',
    //         'Farmer UniqueID',
    //         'Farmer PlotID',
    //         'No of Plot',
    //         'Farmer Name',
    //         'Aadhar Card no',
    //         'Mobile Number',
    //         'Onboarding Area Acres',
    //         'Total Polygon Area',
    //         'State',
    //         'District',
    //         'Taluka',
    //         'Panchayat',
    //         'Village',
    //         'Surveyor Name',
    //         'Surveyor Mobile',
    //         'Date of Survey',
    //         'Plot No',
    //         'Pipe No',
    //         'Aeration 1',
    //         'Aeration 2',
    //         'Aeration 3',
    //         'Aeration 4',
    //     ];
    // }

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
