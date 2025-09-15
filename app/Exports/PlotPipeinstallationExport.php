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

class PlotPipeinstallationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
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

        // $request = json_decode($this->request);//whil development use this
         $request = $this->request; //in production use this
         //dd($request);
        $farmer = PipeInstallationPipeImg::whereHas('farmerapproved', function ($q) use ($request) {
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
                return $q;
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
            if (isset($request->organization) && $request->organization) {
                $q->where('organization_id', '<=', $request->organization);
            }
            return $q;
        })
            ->with('farmerapproved')
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
            // ->get();
            ->cursor();
         // dd($farmer);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
            // $plotAreas = PipeInstallation::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
            //     ->pluck('plot_area');
            $plotAreas = Polygon::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
            ->pluck('plot_area');
        
            $totalPolygonArea = $plotAreas->sum();
        
            $pipes = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)
                ->get(['pipe_no', 'created_at']);
        
            $totalPipeInstalation = $pipes->sum('pipe_no');
        
            // Get creation dates for up to 3 pipes
            $pipeCreationDates = $pipes->take(3)->pluck('created_at')->map(function($date) {
                return $date ? $date->format('Y-m-d') : "-";
            });
        
           
        
            $validatorDate = "-";
            if ($farmer->l2status == "Rejected") {
                $validatorDate = $farmer->farmerapproved->L2_reject_timestamp ? Carbon::parse($farmer->farmerapproved->L2_reject_timestamp)->toDateString() : "-";
            } elseif ($farmer->l2status == "Approved") {
                $validatorDate = $farmer->farmerapproved->L2_aprv_timestamp ? Carbon::parse($farmer->farmerapproved->L2_aprv_timestamp)->toDateString() : "-";
            }


            $validatorName = "-";
              if ($farmer->l2status == "Approved") {
                $validatorName = $farmer->farmerapproved->FinalUserApprovedRejected->name ?? null;
              } elseif ($farmer->l2status == "Rejected") {
                $validatorName = $farmer->farmerapproved->FinalUserApprovedRejected->name?? null;
             }


            $payload_fields = [
                $farmer->financial_year ?? "-",
                $farmer->seasons->name ?? "-",
                $farmer->farmerapproved->organization->company ?? "-",
                $farmer->financial_year ?? "-",
                $farmer->farmerapproved->farmer_uniqueId ?? "-",
                $farmer->farmerapproved->farmer_plot_uniqueid ?? "-",
                $farmer->pipe_no ??"-",
                $farmer->farmerapproved->farmer_name ?? "-",
                $farmer->farmerapproved->mobile ?? "-",
                $farmer->farmerapproved->area_in_acers ?? "-",
                $farmer->polygon->plot_area ?? "-",
                $farmer->lat ?? "-",
                $farmer->lng ?? "-",
                $farmer->date->format('Y-m-d') ?? "-",
                $farmer->farmerapproved->state->name ?? "-",
                $farmer->farmerapproved->district->district ?? "-",
                $farmer->farmerapproved->taluka->taluka ?? "-",
                $farmer->farmerapproved->panchayat->panchayat ?? "-",
                $farmer->farmerapproved->village->village ?? "-",
                $farmer->lat??"-",
                $farmer->lng??"-",
                $farmer->surveyor->name ?? "-",
                $farmer->surveyor->mobile ?? "-",
                $farmer->created_at->format('Y-m-d') ?? "-",
                $farmer->l2status ?? "-",
                $validatorName,
                $validatorDate
            ];
        
        
        
      

        $validator = PipeImgValidation::with('ValidatorUserDetail')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('plot_no', $farmer->plot_no)->latest()->first();



        if ($farmer->pipes_location) {
            $pipe_data = (array)json_decode($farmer->pipes_location); 
            foreach ($pipe_data as $data) {
                array_push(
                    $payload_fields,
                  
                );
            }
        } else {


            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('plot_no', $farmer->plot_no)->get();


            foreach ($pipe_data as $data) {
                array_push(
                    $payload_fields,
                  
                );
                if ($validator) {
                    array_push(
                        $payload_fields,
                     
                    );
                }
            }
        }



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
            'farmer PlotId',
            'Pipe No',
            'Farmer Name', 
            'Mobile number',
            'Onboarding Area Acres',
            'Total Polygon Area',
            'Latitude',
            'Longitude',
            'Pipe Installation Date',
            'State', 
            'District', 
            'Taluka', 
            'Panchayat', 
            'Village',
            'Latitue',
            'Longitude',
            'Surveyor Name',
            'Surveyor Mobile', 
            'Date of Survey',
            'Status',
            'Validator Name',
            'Validation Date'

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
