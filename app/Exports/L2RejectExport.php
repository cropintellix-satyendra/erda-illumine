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
use App\Models\ViewerLocation;

class L2RejectExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
{
    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
        //   $this->status  = $request->status;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        
        // $request = json_decode($this->request);//whil development use this
        $request = $this->request; //in production use this
        
        $farmer = FarmerPlot::where('status', 'Rejected')->where('final_status', 'Rejected')->whereHas('final_farmers', function ($q) use ($request) {
            $q->where('onboarding_form', 1);

            if (isset($request->rolename) && $request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } //end of viewer

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
            ->when('filter', function ($w) use ($request) {
                if (isset($request->rolename)  && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer' && $request->status != 'Pending') {
                    //mainly this will be used by own user which he/she approved or reject
                    $w->where('finalreject_userid', $request->userid);
                }
                if (isset($request->l2_validator) && $request->l2_validator) {
                    //mainly this will be used when admin, viewer are downloading data
                    $w->where('finalreject_userid', $request->l2_validator);
                }
            })
            ->with('final_farmers')->get();
            
        $farmer = $farmer->map(function ($q) {
            $q->PlotImgUrl = url('download/') . '/' . 'PlotImg' . '/' . $q->farmer_id . '/' . $q->farmer_uniqueId . '/' . $q->plot_no;
            return $q;
        });
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
        $request = $this->request;
        // $payload_fields = [
        //     $farmer->farmer->farmer_uniqueId ?? "-", $farmer->farmer->farmer_name ?? "-", $farmer->farmer->mobile_access ?? "-",
        //     $farmer->farmer->mobile_reln_owner ?? "-", $farmer->farmer->mobile ?? "-",
        //     $farmer->farmer->no_of_plots ?? "-"
        // ];

        // if ($farmer->farmer->state_id == 36) {
        //     //need to add plot area for telangana of converted data
        //     $farmerplots =  FarmerPlot::where('farmer_uniqueId', $farmer->farmer->farmer_uniqueId)->get();
        //     $guntha = 0.025000;
        //     $total_area_acres  = 0;
        //     foreach ($farmerplots as $plotsarea) {
        //         $area = number_format((float)$plotsarea->area_in_acers, 2, '.', '');
        //         $split = explode('.', $area); //spliting area
        //         $valueafterdecimal = (isset($split[1]) && $split[1]) ? $split[1] : 0; //take array of index 1 value after decimal point
        //         $result = $valueafterdecimal * $guntha; // multiplying value with defined base value
        //         $conversion = explode('.', $result); // split result
        //         $conversion = $conversion[1] ?? 0;
        //         $acers = $split[0] . '.' . $conversion; // concat the obtained result with firstly split data
        //         $total_area_acres += $acers;
        //     }
        //     //

        //     array_push(
        //         $payload_fields,
        //         $total_area_acres ?? "-",
        //         $farmer->farmer->country ?? "-",
        //         $farmer->farmer->state ?? "-",
        //         $farmer->farmer->district ?? "-",
        //         $farmer->farmer->taluka ?? "-",
        //         $farmer->farmer->panchayat ?? "-",
        //         $farmer->farmer->village ?? "-",
        //         $farmer->farmer->latitude ?? "-",
        //         $farmer->farmer->longitude ?? "-",
        //         $farmer->farmer->date_survey ?? "-",
        //         $farmer->farmer->time_survey ?? "-",
        //         $farmer->farmer->remarks ?? "-",
        //         $farmer->plot_no ?? "-",
        //         $farmer->PlotImgUrl ?? "-",
        //         $farmer->farmer_plot_uniqueid ?? "-",
        //         $farmer->plot_no ?? "-"
        //     );

        //     //second part
        //     $guntha = 0.025000;
        //     $area = number_format((float)$farmer->area_in_acers, 2, '.', '');
        //     $split = explode('.', $area); //spliting area
        //     $valueafterdecimal = (isset($split[1]) && $split[1]) ? $split[1] : 0; //take array of index 1 value after decimal point
        //     $result = $valueafterdecimal * $guntha; // multiplying value with defined base value
        //     $conversion = explode('.', $result); // split result
        //     $conversion = $conversion[1] ?? 0;
        //     $acers = $split[0] . '.' . $conversion; // concat the obtained result with firstly split data

        //     array_push($payload_fields, $farmer->area_in_acers ?? "-", $acers, $farmer->land_ownership ?? "-", $farmer->actual_owner_name ?? "-", $farmer->survey_no ?? "-");




        //     array_push(
        //         $payload_fields,
        //         //l2detail
        //         $farmer->final_status ?? "-",
        //         $farmer->finalreject_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->finalreject_timestamp)->format('d-m-Y') : "-",
        //         $farmer->finalreject_timestamp ?  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->finalreject_timestamp)->format('H:i:s') : "-",
        //         $farmer->FinalUserApprovedRejected->name ?? "-",
        //         $farmer->farmer->surveyor_name ?? "-",
        //         $farmer->farmer->surveyor_mobile ?? "-"
        //     );
        // } else {

        //     if (isset($request->state)  && $request->state == 29) {
        //         array_push(
        //             $payload_fields,
        //             $farmer->farmer->total_plot_area ?? "-",
        //             $farmer->farmer->country ?? "-",
        //             $farmer->farmer->state ?? "-",
        //             $farmer->farmer->district ?? "-",
        //             $farmer->farmer->taluka ?? "-",
        //             $farmer->farmer->panchayat ?? "-",
        //             $farmer->farmer->village ?? "-",
        //             $farmer->farmer->latitude ?? "-",
        //             $farmer->farmer->longitude ?? "-",
        //             $farmer->farmer->date_survey ?? "-",
        //             $farmer->farmer->time_survey ?? "-",
        //             $farmer->farmer->remarks ?? "-",
        //             $farmer->plot_no ?? "-",
        //             $farmer->PlotImgUrl ?? "-",
        //             $farmer->farmer_plot_uniqueid ?? "-",
        //             $farmer->plot_no ?? "-"
        //         );
        //         array_push(
        //             $payload_fields,
        //             $farmer->area_in_acers ?? "-",
        //             $farmer->land_ownership ?? "-",
        //             $farmer->actual_owner_name ?? "-",
        //             $farmer->survey_no ?? "-",
        //             $farmer->status ?? "-",
        //             $farmer->farmer->surveyor_name ?? "-",
        //             $farmer->farmer->surveyor_mobile ?? "-"
        //         );
        //     } else {
        //         array_push(
        //             $payload_fields,
        //             $farmer->farmer->total_plot_area ?? "-",
        //             $farmer->farmer->country ?? "-",
        //             $farmer->farmer->state ?? "-",
        //             $farmer->farmer->district ?? "-",
        //             $farmer->farmer->taluka ?? "-",
        //             $farmer->farmer->panchayat ?? "-",
        //             $farmer->farmer->village ?? "-",
        //             $farmer->farmer->latitude ?? "-",
        //             $farmer->farmer->longitude ?? "-",
        //             $farmer->farmer->date_survey ?? "-",
        //             $farmer->farmer->time_survey ?? "-",
        //             $farmer->farmer->remarks ?? "-",
        //             $farmer->plot_no ?? "-",
        //             $farmer->PlotImgUrl ?? "-",
        //             $farmer->farmer_plot_uniqueid ?? "-",
        //             $farmer->plot_no ?? "-"
        //         );

        //         array_push($payload_fields, "-", $farmer->area_in_acers ?? "-", $farmer->land_ownership ?? "-", $farmer->actual_owner_name ?? "-", $farmer->survey_no ?? "-");

        //         array_push(
        //             $payload_fields,
        //             //l2detail
        //             $farmer->final_status ?? "-",
        //             $farmer->finalreject_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->finalreject_timestamp)->format('d-m-Y') : "-",
        //             $farmer->finalreject_timestamp ?  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->finalreject_timestamp)->format('H:i:s') : "-",
        //             $farmer->FinalUserApprovedRejected->name ?? "-",
        //             $farmer->farmer->surveyor_name ?? "-",
        //             $farmer->farmer->surveyor_mobile ?? "-"
        //         );
        //     }
        // } //end of state
        $payload_fields = [
            $farmer->final_farmers->organization->company ??"_",
            $farmer->final_farmers->farmer_uniqueId ?? "-",
            $farmer->final_farmers->farmer_name ?? "-",
            $farmer->final_farmers->gender ?? "-",
            $farmer->final_farmers->guardian_name ?? "-",
            $farmer->final_farmers->mobile ?? "-",
            $farmer->final_farmers->mobile_access ?? "-",
            $farmer->final_farmers->mobile_reln_owner ?? "-",
            $farmer->final_farmers->aadhaar?? "-",
            $farmer->final_farmers->own_area_in_acres??"-",
            $farmer->final_farmers->lease_area_in_acres??"-",
            $farmer->final_farmers->actual_owner_name??"-",
            $farmer->area_in_acers??$farmer->final_farmers->area_in_acers??"-",
            $farmer->area_in_acers/0.330578512396694??$farmer->final_farmers->area_in_acers/0.330578512396694??"-",
            $farmer->khatian_number??"-",
            $farmer->final_farmers->country??"-",
            $farmer->final_farmers->state??"-",
            $farmer->final_farmers->district??"-",
            $farmer->final_farmers->taluka??"-",
            $farmer->final_farmers->panchayat??"-",
            $farmer->final_farmers->village??"-",
            $farmer->final_farmers->remarks??"-",
            $farmer->final_farmers->created_at??"-",
            $farmer->final_farmers->date_survey??"-",
            $farmer->final_farmers->time_survey??"-",
            $farmer->final_farmers->surveyor_name??"-",
            $farmer->final_farmers->surveyor_mobile??"-",
            $farmer->final_farmer_plot_image->path??"-",
            $farmer->final_farmers->signature??"-",
            $farmer->final_farmers->plotowner_sign??"-",
            $farmer->final_farmers->farmer_photo??"-",
            $farmer->final_farmers->aadhaar_photo??"-",
            $farmer->final_farmers->others_photo??"-",
            $farmer->final_status??"-",
            $farmer->final_farmers->FinalUserApprovedRejected->name??"-"
        ];

        return $payload_fields;
    }

    public function headings(): array
    {
        $request = $this->request;
        // $header = [
        //     'Farmer UniqueID', 'Farmer Name', 'Mobile Access', 'Mobile Relation Owner', 'Mobile', 'No. of PLots', 'Total Plot Area (Acres)', 'Country',
        //     'State', 'District', 'Taluka', 'Panchayat', 'Village', 'Latitude', 'Longitude', 'Date Survey', 'Time Survey',
        //     'Remarks',
        //     'PlotData', 'Plot Images', 'Plot Unique ID', 'Plot No'
        // ];


        // if (isset($request->state)  && $request->state == 29) {
        //     array_push($header, 'Area in Acers', 'Land Ownership', 'Actual Owner Name', 'Survey No', 'L2 PlotStatus', 'L2 Plotstatus update Date', 'L2 Plotstatus update Time', 'L2 Final Validator Name', 'Surveyor Name', 'Surveyor Mobile');
        // } else {
        //     array_push($header, 'Area in (A.G)', 'Area in Acres', 'Land Ownership', 'Actual Owner Name', 'Survey No', 'L2 PlotStatus', 'L2 Plotstatus update Date', 'L2 Plotstatus update Time', 'L2 Final Validator Name', 'Surveyor Name', 'Surveyor Mobile');
        // }

        $header = [
            'Organization Name',
            'Farmer UniqueID', 
            'Farmer Name', 
            'Gender',
            'Guardian Name',
            'Mobile Number',
            'Mobile Access', 
            'Mobile Relation Owner', 
            'Aadhar Number',
            'Own Land area in Acres',
            'Lease Land area in Acres',
            'Lease Land Owner Name',
            'Total Area in acres',
            'Total Area in bigha',
            'Khatian no/Plot No',
            'Country',
            'State', 
            'District', 
            'Block', 
            'Panchayat', 
            'Village', 
            'Village Remarks', 
            'Date & Time of Onboarding', 
            'Date Form Submitted', 
            'Time Form Submitted', 
            'Surveyor Name',
            'Surveyor Mobile Number',
            'Land record photo',
            'Farmer Signature',
            'Lease Land Owner Signature',
            'Farmer Photo',
            'Aadhar Photo',
            'Others Photo',
            'Status',
            'Validator name'
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
