<?php

namespace App\Exports;

use App\Models\PipeImgValidation;
use App\Models\PipeInstallation;
use App\Models\PipeInstallationPipeImg;
use App\Models\Polygon;
use App\Models\User;
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
use Carbon\Carbon;

class AllPipeInstallationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
    protected $farmeruniqueid;
    protected $request;
    protected $organization;
    protected $maxPipeno;
    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
        $this->organization = $request->organization ?? null;
        $this->maxPipeno = PipeInstallationPipeImg::max('pipe_no'); 
       // dd($request);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        // $request = json_decode($this->request);//whil development use this
         $request = $this->request; //in production use this
         //dd($request);
        $farmer = PipeInstallationPipeImg::with('farmerapproved')->where('pipe_no','1')->whereHas('farmerapproved', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
            if (isset($request->rolename) && $request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } //end of viewer
       

            if (isset($request->state)  && $request->state) {
                $q->where('state_id',  $request->state);
            }
            if (isset($request->district)  && $request->district) {
                $q->where('district_id', $request->district);
            }
            if (isset($request->taluka)  && $request->taluka) {
                $q->where('taluka_id', $request->taluka);
            }
            if (isset($request->panchayats)  && $request->panchayats) {
                $q->where('panchayat_id',  $request->panchayats);
            }
            if (isset($request->village)  && $request->village) {
                $q->where('village_id',  $request->village);
            }
            if (isset($request->organization) && $request->organization) {
                $q->where('organization_id', $request->organization);
            }
            return $q;
        })
            ->when($request, function ($w) use ($request) {
                if (isset($request->start_date)  && $request->start_date) {
                    $w->whereDate('created_at', '>=', $request->start_date);
                }
                if (isset($request->end_date)  && $request->end_date) {
                    $w->whereDate('created_at', '<=', $request->end_date);
                }
                if (isset($request->executive_onboarding)  && $request->executive_onboarding) {
                    $w->where('surveyor_id', $request->executive_onboarding);
                }
                if (isset($request->l1_validator)  && $request->l1_validator) {
                    $w->where('apprv_reject_user_id', 'like', $request->l1_validator);
                }
                return $w;
            })
            ->latest()
            ->get();
            // dd($farmer[0]);
        return $farmer;
    }


    

    // here you select the row that you want in the file
    public function map($farmer): array
    {
        // $plotAreas = Polygon::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
        //     ->pluck('plot_area');
        
        // $totalPolygonArea = $plotAreas->sum();
        
        // $pipes = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
        //     ->get(['pipe_no', 'created_at']);
        
        // $totalPipeInstalation = $pipes->count('pipe_no');
        
        // // Get creation dates for up to maxPipeno pipes
        // $pipeCreationDates = $pipes->pluck('created_at')->map(function($date) {
        //     return $date ? $date->format('Y-m-d') : "-";
        // });
        
        // $pipeInstallationDates = $pipeCreationDates->pad($this->maxPipeno, null);
    
            // Step 1: Fetch plot areas for the given farmer plot unique ID
             $plotAreas = Polygon::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
             ->pluck('plot_area');
         
         $totalPolygonArea = $plotAreas->sum();
         
         // Step 2: Fetch pipes for the given farmer plot unique ID
         $pipes = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
             ->get(['pipe_no', 'created_at']);
         
         $totalPipeInstalation = $pipes->count('pipe_no');
         
         // Get creation dates for up to maxPipeno pipes
         $pipeCreationDates = $pipes->pluck('created_at')->map(function($date) {
             return $date ? $date->format('Y-m-d') : "-";
         });
         
         $pipeInstallationDates = $pipeCreationDates->pad($this->maxPipeno, null);
         
         // Step 3: Fetch pipes by organization and count maximum pipes per plot
         $organizationId = $farmer->farmerapproved->organization->id;
         $maxPipes = PipeInstallationPipeImg::whereHas('farmerapproved', function ($query) use ($organizationId) {
                 $query->where('organization_id', $organizationId);
             })
             ->groupBy('farmer_plot_uniqueid')
             ->selectRaw('count(pipe_no) as pipe_count')
             ->orderBy('pipe_count', 'desc')
             ->first();
         
         $maxPipesCount = $maxPipes ? $maxPipes->pipe_count : 0;
         
        $validatorDate = "-";
        if ($farmer->l2status == "Rejected") {
            $validatorDate = $farmer->farmerapproved->L2_reject_timestamp ? Carbon::parse($farmer->farmerapproved->L2_reject_timestamp)->toDateString() : "-";
        } elseif ($farmer->l2status == "Approved") {
            $validatorDate = $farmer->farmerapproved->L2_aprv_timestamp ? Carbon::parse($farmer->farmerapproved->L2_aprv_timestamp)->toDateString() : "-";
        }
    
        $validatorName = "-";
        if ($farmer->l2status == "Approved" || $farmer->l2status == "Rejected") {
            $validatorName = $farmer->Pipe_validations->users->name ?? "-";
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
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->area_in_acers ?? "-",
            $totalPolygonArea ?? "-",
            $farmer->lat ?? "-",
            $farmer->lng ?? "-",
            $totalPipeInstalation ?? "-"
        ];
    
        // Add dynamic pipe creation dates to the payload
        foreach ($pipeInstallationDates as $date) {
            $payload_fields[] = $date ??"-";
        }
    
        $payload_fields = array_merge($payload_fields, [
            $farmer->farmerapproved->state->name ?? "-",
            $farmer->farmerapproved->district->district ?? "-",
            $farmer->farmerapproved->taluka->taluka ?? "-",
            $farmer->farmerapproved->panchayat->panchayat ?? "-",
            $farmer->farmerapproved->village->village ?? "-",
            $farmer->surveyor->name ?? "-",
            $farmer->surveyor->mobile ?? "-",
            $farmer->date ? $farmer->date->format('Y-m-d') : "-",
            $farmer->l2status ?? "-",
            $validatorName,
            $validatorDate
        ]);
    
        return $payload_fields;
    }
    

    public function headings(): array
    {
        $header = [
            'Year',
            'Season',
            'Organisation Name',
            'Onboarding Year',
            'Farmer UniqueID', 
            'Farmer PlotId',
            'No of Plots',
            'Farmer Name', 
            'Mobile number',
            'Onboarding Area Acres',
            'Total Polygon Area',
            'Latitude',
            'Longitude',
            'Total Pipe Installation'
        ];
    
        // Add dynamic pipe headings
        for ($i = 1; $i <= $this->maxPipeno; $i++) {
            $header[] = 'Pipe No ' . $i;
        }
    
        $header = array_merge($header, [
            'State', 
            'District', 
            'Taluka', 
            'Panchayat', 
            'Village',
            'Surveyor Name', 
            'Surveyor Mobile no.',
            'Date of Survey',
            'Status',
            'Validator Name',
            'Validation Date'
        ]);
    
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
