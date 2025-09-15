<?php

namespace App\Exports;

use App\Models\PipeImgValidation;
use App\Models\PipeInstallation;
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
use Illuminate\Cache\RateLimiting\Limit;

class PipeInstallationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
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
        // dd($farmer->pipe_image_latest->date ??"-" ."/".$farmer->pipe_image_latest->time??"-");
        // dd($farmer->validator->name);
        $payload_fields = [
            $farmer->farmerapproved->organization->company??"-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->plot_no ?? "-",
            $farmer->farmerplot_details->area_acre_awd ?? "_",
            $farmer->farmer_plot_uniqueid ?? "-",
            // $farmer->farmerapproved->no_of_plots ?? "-",
            $farmer->farmerapproved->total_plot_area ?? "-",//
            $farmer->pipe_image_latest->plot_no ?? "-",
            $farmer->pipe_image_latest->date  ."/".$farmer->pipe_image_latest->time,
            $farmer->date_survey ?? "-", 
            $farmer->date_time ?? "-",
            $farmer->FormSubmitBy->name ?? "-",
            $farmer->FormSubmitBy->mobile ?? "-",
            $farmer->status ?? "-",
            // $farmer->approve_validation_detail->ValidatorUserDetail->name ??"-",
            $farmer->validator->name??"-",
            $farmer->pipe_image_latest->images??"-",
            // $farmer->farmerapproved->country ?? "-",
            // $farmer->farmerapproved->state ?? "-",
            // $farmer->farmerapproved->district ?? "-",
            // $farmer->farmerapproved->taluka ?? "-",
            // $farmer->farmerapproved->panchayat ?? "-",
            // $farmer->farmerapproved->village ?? "-",
           
            // $farmer->area_in_acers ?? "-",//total area in acers
            // $farmer->plot_area ?? "-",
            // $farmer->ranges ?? "-", 
            // $farmer->polygon_date_time ?? "-",
            
            
            // $farmer->installed_pipe ?? "-"
        ];
        // dd($farmer);
        // $pipe_data = (Array)json_decode($farmer->pipes_location);//get installed pipe location

        $validator = PipeImgValidation::with('ValidatorUserDetail')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('plot_no', $farmer->plot_no)->latest()->first();
        // dd($validator->ValidatorUserDetail->name);
        


        if ($farmer->pipes_location) {
            $pipe_data = (array)json_decode($farmer->pipes_location); //get installed pipe location
            foreach ($pipe_data as $data) {
                array_push(
                    $payload_fields,
                    // $data->distance ?? "-",
                    // 'Lat: ' . $data->lat . ', ' . 'Lng: ' . $data->lng ?? "-",
                    // 'Date: ' . $data->date . ', ' . 'Time: ' . $data->time,
                    // $farmer->FormSubmitBy->name ?? "-",
                    // $farmer->FormSubmitBy->mobile ?? "-"
                );
            }
        } else {


            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('plot_no', $farmer->plot_no)->get();


            foreach ($pipe_data as $data) {
                array_push(
                    $payload_fields,
                    // $data->distance ?? "0",
                    // 'Lat: ' . $data->lat . ', ' . 'Lng: ' . $data->lng ?? "-",
                    // 'Date: ' . $data->date . ', ' . 'Time: ' . $data->time,
                    // $farmer->FormSubmitBy->name ?? "-",
                    // $farmer->FormSubmitBy->mobile ?? "-",
                );
                if ($validator) {
                    array_push(
                        $payload_fields,
                        // $farmer->status,
                        // $validator->ValidatorUserDetail->name,
                        // $validator->ValidatorUserDetail->mobile,
                        // $validator->timestamp ?  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $validator->timestamp)->format('Y-m-d H:i:s') : "-"
                    );
                }
            }
        }



        return $payload_fields;
    }

    public function headings(): array
    {
        $header = [
            'Organisation Name',
            'Farmer UniqueID', 
            'Farmer Name', 
            'Mobile number',
            'Plot No',
            'Total AWD Area in Acres',
            'Plot  Id',
            // 'No. of Plots', 
            'Plot Area (Acres)', 
            'Pipe Number',
            'Date & Time of Pipe installation',
            'Date Form Submitted', 
            'Time Form Submitted',
            'Surveyor Name', 
            'Surveyor Mobile',
            'Status',
            'validator',
            'pip Photo',
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
            // 'Surveyor Name', 
            // 'Surveyor Mobile',
            // 'Pipe 3 Distance', 
            // 'Pipe 3 Location', 
            // 'Date & Time', 
            // 'Surveyor Name', 
            // 'Surveyor Mobile'
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
