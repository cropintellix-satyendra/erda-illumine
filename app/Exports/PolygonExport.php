<?php

namespace App\Exports;


use App\Models\PipeImgValidation;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\PipeInstallation;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\ViewerLocation;

class PolygonExport implements FromCollection,WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
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
        //in production use this
        // dd($request->all());
        $farmer = PipeInstallation::whereHas('farmerapproved', function ($q) use ($request) {
            
            $q->where('onboarding_form', 1);
            if (isset($request->rolename) && $request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } //end of viewer


            if (isset($request->rolename)  && $request->rolename == 'L-1-Validator') {
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
            $q->where('final_status_onboarding', 'Approved');

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
        
            ->with('farmerapproved')
            ->whereHas('pipe_image', function ($im) use ($request) {
                $im->when('filter', function ($c) use ($request) {

                    if (isset($request->status)  && $request->status == 'Pending') {
                        $c->where('status', 'Pending');
                    }
                    if (isset($request->status)  && $request->status == 'Rejected') {
                        $c->where('status', 'Rejected');
                        $c->where('trash', 0);
                    }
                    if (isset($request->status)  && $request->status == 'Approved') {
                        $c->where('status', 'Approved');
                    }
                    return $c;
                });
                return $im;
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

                if (isset($request->status)  && $request->status) {
                    // $w->where('status',$request->status);
                }
                if (isset($request->status)  && $request->status != 'Pending') {
                    if (isset($request->rolename)  && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer') {
                        $w->where('apprv_reject_user_id', $request->userid);
                    }
                }
                if (isset($request->l1_validator) && $request->l1_validator) {
                    $w->where('apprv_reject_user_id', $request->l1_validator);
                }

                return $w;
            })
            ->limit(100)->get();
            // dd($farmer[0]);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
        
        // dd($farmer->l2_apprv_reject_user_id->name);
        $payload_fields = [
            $farmer->farmerapproved->organization->company??"-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->area_in_acers ?? "-",
            $farmer->farmerplot_details->area_acre_awd ?? "_",
            $farmer->farmer_plot_uniqueid ?? "-",
            $farmer->farmerapproved->no_of_plots ?? "-",
            $farmer->farmerapproved->total_plot_area ?? "-",//
            $farmer->farmerapproved->country ?? "-",
            $farmer->state ?? "-",
            $farmer->district ?? "-",
            $farmer->taluka ?? "-",
            $farmer->farmerapproved->panchayat ?? "-",
            $farmer->village ?? "-",
            $farmer->ranges ?? "-", 
            $farmer->polygon_date_time ?? "-",
            $farmer->farmerapproved->date_survey??"_",
            $farmer->farmerapproved->time_survey??"_",
            $farmer->surveyor_name??"_",
            $farmer->status??"_",
            $farmer->l2_apprv_reject_user_id->name??"-",

    



            
           
        ];
        


        return $payload_fields;
    }

    public function headings(): array
    {
        $header = [
            'Organisation Name',
            'Farmer UniqueID', 
            'Farmer Name', 
            'Mobile number',
            'Total Area in Acres',
            'Total AWD Area in Acres',
            'Plot  Id',
            'No. of Plots', 
            'Plot Area (Acres)', 
            'Country',
            'State',
            'District',
            'Block',
            'Panchayat',
            'Village', 
            'Polygon',
            'Date & Time of Polygon',
            'Date Form Submitted', 
            'Time Form Submitted',
            'Surveyor Name',
            'status',
            'Validator name',


            // 'Date & Time of Pipe installation',
            // 'Date Form Submitted', 
            // 'Time Form Submitted',
            // 'Surveyor Name', 
            // 'Surveyor Mobile',
            // 'Status',
            // 'validator',
            // 'Country', 
            // 'State', 
            // 'District', 
            // 'Taluka', 
            // 'Panchayat',
            // 'Village', 
             
            // 'Area in Acres', 
            // 'Area From Google map', 
            // 'Polygon', 
            // 'Date & Time of Polygon', 
             
            
            // 'No of pipes installed',         //----------------            
            // 'Pipe 1 Distance', 
            // 'Pipe 1 Location', 
            // 'Date & Time', 
            // 'Surveyor Name', 
            // 'Surveyor Mobile',
            // 'Pipe 2 Distance', 
            // 'Pipe 2 Location', 
            // 'Date & Time', 
            
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
