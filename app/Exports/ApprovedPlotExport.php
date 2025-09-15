<?php

namespace App\Exports;

use App\Models\FinalFarmer;
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
use Carbon\Carbon;
use App\Models\ViewerLocation;
use App\Models\FarmerPlot;

class ApprovedPlotExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
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
         
        // $request = json_decode($this->request);//while development use this
        $request = $this->request;  // while devlopment close this because we get data in json and this is used for Cron
     
        $farmer = FarmerPlot::where('final_status', 'approved')->whereHas('final_farmers', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
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
            return $q;
        })->when('filter', function ($w) use ($request) {
               
                if (isset($request->l1_validator) && $request->l1_validator) {
                    //mainly this will be used when admin, viewer are downloading data
                    $w->where('aprv_recj_userid', $request->l1_validator);
                }
            })->where('plot_no',1)
            ->with('final_farmers')
            ->groupBy('farmer_uniqueId')
            // ->limit(100)
            ->latest()
            ->get();
            
        $farmer = $farmer->map(function ($q) {
            $q->PlotImgUrl = url('download/') . '/' . 'PlotImg' . '/' . $q->farmer_id . '/' . $q->farmer_uniqueId . '/' . $q->plot_no;
            return $q;
        });
    // {
    //     // dd('in');
    //     // dd($request->all());
    //     //  $request = json_decode($this->request);//whil development use this
    //     $request = $this->request; //in production use this
    //     //  dd($request);
    //     $farmer = FinalFarmer::when('filter', function ($q) use ($request) {
    //         // dd('in');

    //         $q->where('onboarding_form', 1);
    //         // dd('in');
    //         if (isset($request->rolename) && $request->rolename == 'Viewer') {
    //             // dd('in');
    //             $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
    //             $q->whereIn('state_id', explode(',', $viewerlocation->state));
    //         } //end of viewer
    //         if (isset($request->rolename)  && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer') {
    //             $q->where('L2_appr_userid', $request->userid);
    //         }
    //         if (isset($request->modules)  && $request->modules == 'CropData') { //when plot has cropdata
    //             $q->whereHas('PlotCropData');
    //         }
    //         if (isset($request->modules)  && $request->modules == 'Benefit') { //when plot has benefit
    //             $q->whereHas('BenefitsData');
    //         }
    //         if (isset($request->modules)  && $request->modules == 'PipeInstalltion') { //when plot has pipeinstalltion
    //             $q->whereHas('PlotPipeData');
    //         }
    //         if (isset($request->modules)  && $request->modules == 'Aeration') { //when plot has aeration
    //             $q->whereHas('AerationData');
    //         }

    //         if (isset($request->l2_validator)  && $request->l2_validator) {
    //             $q->where('L2_appr_userid', 'like', $request->l2_validator);
    //         }

    //         if (isset($request->state)  && $request->state) {

    //             $q->where('state_id', 'like', $request->state);
    //         }
    //         if (isset($request->district)  && $request->district) {
    //             $q->where('district_id', 'like', $request->district);
    //         }
    //         if (isset($request->taluka)  && $request->taluka) {
    //             $q->where('taluka_id', 'like', $request->taluka);
    //         }
    //         if (isset($request->panchayats)  && $request->panchayats) {
    //             $q->where('panchayat_id', 'like', $request->panchayats);
    //         }
    //         if (isset($request->village)  && $request->village) {
    //             $q->where('village_id', 'like', $request->village);
    //         }

    //         if (isset($request->executive_onboarding)  && $request->executive_onboarding) {
    //             $q->where('surveyor_id', $request->executive_onboarding);
    //         }
    //         if (isset($request->start_date)  && $request->start_date) {
    //             $q->whereDate('date_survey', '>=', $request->start_date);
    //         }
    //         if (isset($request->end_date)  && $request->end_date) {
    //             $q->whereDate('date_survey', '<=', $request->end_date);
    //         }
    //         return $q;
    //     })

    //         ->with('ApprvFarmerPlotImages', 'ApprvFarmerPlot')
    //         ->limit(10)
    //         ->get();

    //     $farmer = $farmer->map(function ($q) {
    //         $q->PlotImgUrl = url('download/') . '/' . 'Apprvplot' . '/' . 'PlotImg' . '/' . $q->id . '/' . $q->farmer_uniqueId . '/' . $q->plot_no;
    //         return $q;
    //         // dd($q->PlotImgUrl);
    //     });
        // dd($farmer);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {

        // $request = $this->request;

        // $payload_fields = [
        //     $farmer->farmer_uniqueId ?? "-", $farmer->farmer_name ?? "-", $farmer->mobile_access ?? "-",
        //     $farmer->mobile_reln_owner ?? "-", $farmer->mobile ?? "-",
        //     $farmer->no_of_plots ?? "-"
        // ];

        // if ($farmer->state_id == 36) {
        //     //need to add plot area for telangana of converted data
        //     $farmerplots =  FinalFarmer::where('farmer_uniqueId', $farmer->farmer_uniqueId)->get();
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
        //         $farmer->country ?? "-",
        //         $farmer->state ?? "-",
        //         $farmer->district ?? "-",
        //         $farmer->taluka ?? "-",
        //         $farmer->panchayat ?? "-",
        //         $farmer->village ?? "-",
        //         $farmer->latitude ?? "-",
        //         $farmer->longitude ?? "-",
        //         $farmer->date_survey ?? "-",
        //         $farmer->time_survey ?? "-",
        //         $farmer->remarks ?? "-",
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

        //     array_push(
        //         $payload_fields,
        //         $farmer->area_in_acers ?? "-",
        //         $acers,
        //         $farmer->land_ownership ?? "-",
        //         $farmer->actual_owner_name ?? "-",
        //         $farmer->survey_no ?? "-",
        //         $farmer->final_status,
        //         $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('d-m-Y') : "-",
        //         $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('H:i:s') : "-",
        //         $farmer->UserApprovedRejected->name ?? "-",
        //         $farmer->final_status_onboarding ?? "-",
        //         $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('d-m-Y') : "-",
        //         $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('H:i:s') : "-",
        //         $farmer->FinalUserApprovedRejected->name ?? "-",
        //         $farmer->surveyor_name ?? "-",
        //         $farmer->surveyor_mobile ?? "-"
        //     );
        // } else {
        //     if (isset($request->state)  && $request->state == 29) {
        //         array_push(
        //             $payload_fields,
        //             $farmer->total_plot_area ?? "-",
        //             $farmer->country ?? "-",
        //             $farmer->state ?? "-",
        //             $farmer->district ?? "-",
        //             $farmer->taluka ?? "-",
        //             $farmer->panchayat ?? "-",
        //             $farmer->village ?? "-",
        //             $farmer->latitude ?? "-",
        //             $farmer->longitude ?? "-",
        //             $farmer->date_survey ?? "-",
        //             $farmer->time_survey ?? "-",
        //             $farmer->remarks ?? "-",
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
        //             $farmer->final_status,
        //             $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('d-m-Y') : "-",
        //             $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('H:i:s') : "-",
        //             $farmer->UserApprovedRejected->name ?? "-",
        //             $farmer->final_status_onboarding ?? "-",
        //             $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('d-m-Y') : "-",
        //             $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('H:i:s') : "-",
        //             $farmer->FinalUserApprovedRejected->name ?? "-",
        //             $farmer->surveyor_name ?? "-",
        //             $farmer->surveyor_mobile ?? "-"
        //         );
        //     } else {
        //         array_push(
        //             $payload_fields,
        //             $farmer->total_plot_area ?? "-",
        //             $farmer->country ?? "-",
        //             $farmer->state ?? "-",
        //             $farmer->district ?? "-",
        //             $farmer->taluka ?? "-",
        //             $farmer->panchayat ?? "-",
        //             $farmer->village ?? "-",
        //             $farmer->latitude ?? "-",
        //             $farmer->longitude ?? "-",
        //             $farmer->date_survey ?? "-",
        //             $farmer->time_survey ?? "-",
        //             $farmer->remarks ?? "-",
        //             $farmer->plot_no ?? "-",
        //             $farmer->PlotImgUrl ?? "-",
        //             $farmer->farmer_plot_uniqueid ?? "-",
        //             $farmer->plot_no ?? "-"
        //         );

        //         array_push(
        //             $payload_fields,
        //             "-",
        //             $farmer->area_in_acers ?? "-",
        //             $farmer->land_ownership ?? "-",
        //             $farmer->actual_owner_name ?? "-",
        //             $farmer->survey_no ?? "-",
        //             $farmer->final_status,
        //             $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('d-m-Y') : "-",
        //             $farmer->L1_appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L1_appr_timestamp)->format('H:i:s') : "-",
        //             $farmer->UserApprovedRejected->name ?? "-",
        //             $farmer->final_status_onboarding ?? "-",
        //             $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('d-m-Y') : "-",
        //             $farmer->L2_aprv_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->L2_aprv_timestamp)->format('H:i:s') : "-",
        //             $farmer->FinalUserApprovedRejected->name ?? "-",
        //             $farmer->surveyor_name ?? "-",
        //             $farmer->surveyor_mobile ?? "-"
        //         );
        //     }
        // }
        
            $request = $this->request;
            
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

        // $header = [
        //     'Organisation Name', 'Farmer UniqueID', 'Farmer Name', 'Gender', 'Guardian Name', 'Mobile Number', 'Mobile Access', 'Relationship with Owner', 'Aadhar Number',
        //     'Own Land area in Acres', 'Lease Land area in Acres', 'Lease Land Owner Name', 'Total Area in Acres',
        //     'Khatian no/Plot No', 'Mobile Relation Owner', 'Mobile', 'No. of PLots', 'Total Plot Area (Acres)', 'Country',
        //     'State', 'District', 'Block', 'Taluka', 'Panchayat', 'Village', 'Village Remarks', 'Date & Time of Onboarding', 'Date Form Submitted',
        //     'Time Form Submitted', 'Surveyor Name', 'Surveyor Mobile Number', 'Land Record Photo',
        //     'Farmer Signature', 'Lease Land Owner Signature', 'Farmer Photo', 'Aadhar Photo', 'Others Photo', 'Status',
        //     'Validator name', 
            
        // ];


        // if (isset($request->state)  && $request->state == 29) {
        //     array_push($header, 'Area in Acers', 'Land Ownership', 'Actual Owner Name', 'Survey No', 'L1PlotStatus', 'L1 Plotstatus update Date', 'L1 Plotstatus update Time', 'L1 Validator Name', 'L2 PlotStatus', 'L2 Plotstatus update Date', 'L2 Plotstatus update Time', 'L2 Final Validator Name', 'Surveyor Name', 'Surveyor Mobile');
        // } else {
        //     array_push($header, 'Area in (A.G)', 'Area in Acres', 'Land Ownership', 'Actual Owner Name', 'Survey No', 'L1PlotStatus', 'L1 Plotstatus update Date', 'L1 Plotstatus update Time', 'L1 Validator Name', 'L2 PlotStatus', 'L2 Plotstatus update Date', 'L2 Plotstatus update Time', 'L2 Final Validator Name', 'Surveyor Name', 'Surveyor Mobile');
        // }
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
