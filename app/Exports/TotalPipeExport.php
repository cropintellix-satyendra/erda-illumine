<?php
namespace App\Exports;

use App\Models\PipeInstallationPipeImg;
use App\Models\ViewerLocation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use DB;

class TotalPipeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
    protected $farmeruniqueid;
    protected $request;
    protected $organization;
    protected $maxPipeInstallations = 0;

    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
        $this->organization = $this->request->organization ?? null;
    }

    public function collection()
    {
        
        $request = $this->request;
        //dd($request->all());
        $farmers = PipeInstallationPipeImg::whereHas('farmerapproved', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
            $q->where('l2status','Approved');
            if (isset($request->rolename) && $request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } 
            if (isset($request->rolename) && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer') {
                $VendorLocation = DB::table('vendor_locations')->where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $VendorLocation->state));
                if (!empty($VendorLocation->district)) {
                    $q->whereIn('district_id', explode(',', $VendorLocation->district));
                }
                if (!empty($VendorLocation->taluka)) {
                    $q->whereIn('taluka_id', explode(',', $VendorLocation->taluka));
                }
                return $q;
            }

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
            if (isset($request->organization) && $request->organization) {
                $q->where('organization_id', '<=', $request->organization);
            }
            return $q;
        })
            ->with('farmerapproved')
            ->when($request, function ($w) use ($request) {
                if (isset($request->start_date) && $request->start_date) {
                    $w->whereDate('created_at', '>=', $request->start_date);
                }
                if (isset($request->end_date) && $request->end_date) {
                    $w->whereDate('created_at', '<=', $request->end_date);
                }
                if (isset($request->executive_onboarding) && $request->executive_onboarding) {
                    $w->where('surveyor_id', $request->executive_onboarding);
                }
                if (isset($request->l1_validator) && $request->l1_validator) {
                    $w->where('apprv_reject_user_id', 'like', $request->l1_validator);
                }
                return $w;
            })
            ->latest()
            ->get();

       
        foreach ($farmers as $farmer) {
            $pipeCount = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
                ->count();
            if ($pipeCount > $this->maxPipeInstallations) {
                $this->maxPipeInstallations = $pipeCount;
            }
        }

        return $farmers;
    }

    public function map($farmers): array
    {
        $pipes = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $farmers->farmer_plot_uniqueid)
            ->get(['pipe_no', 'created_at']);

        $totalPipeInstallation = $pipes->count('pipe_no');

        
        $pipeInstallationDates = $pipes->pluck('created_at')->map(function ($date) {
            return $date->format('Y-m-d');  
        });

        
        $pipeInstallationDates = $pipeInstallationDates->pad($this->maxPipeInstallations,null);
//dd($this->maxPipeInstallations);
        
        $payload_fields = [
            $farmers->farmerapproved->organization->company ?? "-",
            $farmers->farmerapproved->farmer_name ?? "-",
            $farmers->surveyor->name ?? "-",
            $farmers->farmerapproved->farmer_uniqueId ?? "-",
            $farmers->farmerapproved->farmer_plot_uniqueid ?? "-",
            $farmers->farmerapproved->area_in_acers ?? "-",
            $farmers->polygon->plot_area ?? "-",
            $farmer->lat ?? "-",
            $farmer->lng ?? "-",
            $totalPipeInstallation ?? "-",
            ...$pipeInstallationDates->toArray(), 
            $farmers->farmerapproved->state->name ?? "-",
            $farmers->farmerapproved->district->district ?? "-",
            $farmers->farmerapproved->taluka->taluka ?? "-",
            $farmers->farmerapproved->panchayat->panchayat ?? "-",
            $farmers->farmerapproved->village->village ?? "-",
            
        ];

        return $payload_fields;
    }

    // public function headings(): array
    // {
    //     $header = [
    //         'Organization Name',
    //         'Farmer Name',
    //         'Surveyor Name',
    //         'Farmer UniqueId',
    //         'Onboarding Area',
    //         'Polygon Area',
    //         'Total Pipe Installation',
    //         'State',
    //         'District',
    //         'Taluka',
    //         'Village',
    //     ];

      
    //     for ($i = 1; $i <= $this->maxPipeInstallations; $i++) {
    //         $header[] = 'Pipe Installation Date ' . $i;
    //     }

    //     return $header;
    // }


    public function headings(): array
    {
        $header = [
            'Organization Name',
            'Farmer Name',
            'Surveyor Name',
            'Farmer UniqueId',
            'Farmer Plot uniqueId',
            'Onboarding Area',
            'Polygon Area',
            'Latitude',
            'Longitude',
            'Total Pipe Installation'
        ];
    
        // Append dynamic pipe installation date headings
        for ($i = 1; $i <= $this->maxPipeInstallations; $i++) {
            $header[] = 'Pipe Installation Date ' . $i;
        }
    
        // Append static location fields
        $header = array_merge($header, [
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
        ]);
    
        return $header;
    }
    

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:Z1'; 
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
         
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
