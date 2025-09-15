<?php

namespace App\Exports;

use App\Models\Farmer;
use App\Models\FarmerPlot;
use App\Models\FinalFarmer;
use App\Models\FarmerBenefit;
use App\Models\FarmerBenefitImage;
use App\Models\FarmerCropcdata;
use App\Models\Benefit;
use App\Exports\FarmerExport;
use App\Models\Company;
use App\Models\State;
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


class AllOnboardingExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading, ShouldQueue
{
    use Exportable;


    protected $farmeruniqueid;
    protected $farmer_id;
    function __construct($farmeruniqueid, $request)
    {
        $this->farmeruniqueid = $farmeruniqueid;
        $this->request = $request;
        //$this->status  = $request->status;

    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $request = json_decode($this->request);//while development use this
        // $request = $this->request;  // while devlopment close this because we get data in json and this is used for Cron

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
                $q->where('organization_id', 'like', $request->organization);
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
            // dd($farmer[0]);
        $farmer = $farmer->map(function ($q) {
            $q->PlotImgUrl = url('download/') . '/' . 'PlotImg' . '/' . $q->farmer_id . '/' . $q->farmer_uniqueId . '/' . $q->plot_no;
            return $q;
        });
        // dd($farmer[0]);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
        // dd( $farmer->final_farmer_plot_image);
        $payload_fields = [
            $farmer->final_farmers->organization->company ??"-",
            $farmer->final_farmers->farmer_uniqueId??"-",
            $farmer->final_farmers->farmer_name??"-",
            $farmer->final_farmers->mobile??"-",
            $farmer->final_farmers->gender??"-",
            $farmer->final_farmers->guardian_name??"-",
            $farmer->final_farmers->state->name??"-",
            $farmer->final_farmers->district->district??"-",
            $farmer->final_farmers->taluka->taluka??"-",
            $farmer->final_farmers->panchayat->panchayat??"-",
            $farmer->final_farmers->village->village??"-",
            $farmer->final_farmers->date_survey??"-",
            $farmer->final_farmers->own_area_in_acres??"-",
            $farmer->final_farmers->lease_area_in_acres??"-",
            $farmer->area_in_acers??$farmer->final_farmers->area_in_acers??"-",
            $farmer->final_farmers->financial_year??"-",
            $farmer->final_farmers->seasons->name??"-",
            $farmer->final_farmers->users->name??"-",
            $farmer->final_farmers->users->mobile ?? "-",
            $farmer->final_farmers->final_status??"-",
            $farmer->final_farmers->validator->name??"-",
            // $farmer->final_farmers->organization->company ??"_",
            // $farmer->final_farmers->farmer_name ?? "-",
            // $farmer->final_farmers->farmer_uniqueId ?? "-",
            // $farmer->final_farmers->mobile_access ?? "-",
            // $farmer->final_farmers->gender ?? "-",
            // $farmer->final_farmers->guardian_name ?? "-",
            // $farmer->final_farmers->state->name??"-",
            // $farmer->final_farmers->district->district??"-",
            // $farmer->final_farmers->taluka->taluka??"-",
            // $farmer->final_farmers->panchayat->panchayat??"-",
            // $farmer->final_farmers->village->village??"-",
            // $farmer->final_farmers->date_survey??"-",
            // $farmer->final_farmers->own_area_in_acres??"-",
            // $farmer->final_farmers->lease_area_in_acres??"-",
            // // $farmer->final_farmers->mobile ?? "-",
            // // $farmer->final_farmers->mobile_reln_owner ?? "-",
            // // $farmer->final_farmers->aadhaar?? "-",
            // $farmer->final_farmers->actual_owner_name??"-",
            // $farmer->area_in_acers??$farmer->final_farmers->area_in_acers??"-",
            // $farmer->area_in_acers/0.330578512396694??$farmer->final_farmers->area_in_acers/0.330578512396694??"-",
            // $farmer->final_farmers->financial_year??"-",
            // // dd($farmer->final_farmers->season->id),
            // $farmer->final_farmers->season,
            // $farmer->final_farmers->surveyor->name??"-",
            // $farmer->final_farmers->surveyor->mobile??"-",
            // $farmer->final_farmers->final_status??"-",
            // $farmer->final_farmers->FinalUserApprovedRejected->name??"-"
            // $farmer->khatian_number??"-",
            // $farmer->final_farmers->country??"-",
            // $farmer->final_farmers->remarks??"-",
            // $farmer->final_farmers->created_at??"-",
            // $farmer->final_farmers->time_survey??"-",
            // $farmer->final_farmer_plot_image->path??"-",
            // $farmer->final_farmers->signature??"-",
            // $farmer->final_farmers->plotowner_sign??"-", //Leased Land Owner signature
            // $farmer->final_farmers->farmer_photo??"-",
            // $farmer->final_farmers->aadhaar_photo??"-",
            // $farmer->final_farmers->others_photo??"-",
        ];




        // if ($farmer->final_farmers->state_id == 36) {
        //     // dd('36');
        //     //need to add plot area for telangana of converted data
        //     $farmerplots = FarmerPlot::where('final_farmers', $farmer->final_farmers->farmer_uniqueId??"-")->get();
        //     //  FinalFarmer::where('farmer_uniqueId',$farmer->farmer_uniqueId)->get();
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
        //         $farmer->final_farmers->country ?? "-",
        //         $farmer->final_farmers->state ?? "-",
        //         $farmer->final_farmers->district ?? "-",
        //         $farmer->final_farmers->taluka ?? "-",
        //         $farmer->final_farmers->panchayat ?? "-",
        //         $farmer->final_farmers->village ?? "-",
        //         $farmer->final_farmers->latitude ?? "-",
        //         $farmer->final_farmers->longitude ?? "-",
        //         $farmer->final_farmers->date_survey ?? "-",
        //         $farmer->final_farmers->time_survey ?? "-",
        //         $farmer->final_farmers->remarks ?? "-",
        //         $farmer->PlotImgUrl ?? "-",
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

        //     array_push($payload_fields,
        //     $farmer->area_in_acers ?? "-",
        //     $farmer->actual_owner_name ?? "-",
        //     $farmer->survey_no ?? "-"
        // );

        //     if (isset($request->status) && $request->status == 'Rejected') {
        //         array_push(
        //             $payload_fields,
        //             $farmer->status ?? "-",
        //             $farmer->reject_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->reject_timestamp)->format('d-m-Y') : "-",
        //             $farmer->reject_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->reject_timestamp)->format('H:i:s') : "-",
        //             $farmer->UserApprovedRejected->name ?? "-",
        //             $farmer->farmer->surveyor_name ?? "-",
        //             $farmer->farmer->surveyor_mobile ?? "-"
        //         );

        //     } elseif (isset($request->status) && $request->status == 'Approved') {
        //         array_push(
        //             $payload_fields,
        //             $farmer->status ?? "-",
        //             $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp ?? "")->format('d-m-Y') : "-",
        //             $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp ?? "")->format('H:i:s') : "-",
        //             $farmer->UserApprovedRejected->name ?? "-",
        //             $farmer->final_farmers->surveyor_name ?? "-",
        //             $farmer->final_farmers->surveyor_mobile ?? "-"
        //         );
        //     } else {
        //         array_push(
        //             $payload_fields,
        //             $farmer->status ?? "-",
        //             "-",
        //             "-",
        //             "-",
        //             $farmer->farmer->surveyor_name ?? "-",
        //             $farmer->farmer->surveyor_mobile ?? "-"
        //         );
        //     }
        // } else {

        //     // if (isset($request->state) && $request->state == 29)
        //     if($farmer->final_farmers->state_id == 29) {
        //         // dd('29');
        //         array_push(
        //             $payload_fields,
        //             $farmer->final_farmers->area_in_acers ?? "-",
        //             $farmer->final_farmers->area_in_acers/0.330578512396694 ?? "-",
        //             $farmer->final_farmers->country ?? "-",
        //             $farmer->final_farmers->state ?? "-",
        //             $farmer->final_farmers->district ?? "-",
        //             $farmer->final_farmers->taluka ?? "-",
        //             $farmer->final_farmers->panchayat ?? "-",
        //             $farmer->final_farmers->village ?? "-",
        //             $farmer->final_farmers->latitude ?? "-",
        //             $farmer->final_farmers->longitude ?? "-",
        //             $farmer->final_farmers->date_survey ?? "-",
        //             $farmer->final_farmers->time_survey ?? "-",
        //             $farmer->final_farmers->remarks ?? "-",
        //             $farmer->PlotImgUrl ?? "-",

        //         );
        //         array_push(
        //             $payload_fields,
        //             $farmer->final_farmers->area_in_acers ?? "-",
        //             $farmer->actual_owner_name ?? "-",
        //             $farmer->survey_no ?? "-",
        //             $farmer->status ?? "-",
        //             $farmer->final_farmers->surveyor_name ?? "-",
        //             $farmer->final_farmers->surveyor_mobile ?? "-"
        //         );
        //     } else {
        //         array_push(
        //             $payload_fields,
        //             $farmer->final_farmers->total_plot_area ?? "-",
        //             $farmer->final_farmers->country ?? "-",
        //             $farmer->final_farmers->state ?? "-",
        //             $farmer->final_farmers->district ?? "-",
        //             $farmer->final_farmers->taluka ?? "-",
        //             $farmer->final_farmers->panchayat ?? "-",
        //             $farmer->final_farmers->village ?? "-",
        //             $farmer->final_farmers->latitude ?? "-",
        //             $farmer->final_farmers->longitude ?? "-",
        //             $farmer->final_farmers->date_survey ?? "-",
        //             $farmer->final_farmers->time_survey ?? "-",
        //             $farmer->final_farmers->remarks ?? "-",
        //             $farmer->PlotImgUrl ?? "-",

        //         );

        //         array_push($payload_fields, "-",
        //         $farmer->final_farmers->area_in_acers ?? "-",
        //         $farmer->actual_owner_name ?? "-",
        //         $farmer->survey_no ?? "-"
        //     );
        //         // dd('ddsf');
        //         if (isset($request->status) && $request->status  == 'Rejected') {
        //             // dd('ddsf');
        //             array_push(
        //                 $payload_fields,
        //                 $farmer->final_farmers->photo??"-",
        //                 $farmer->final_farmers->farmer_sign??"-",
        //                 $farmer->final_farmers->plotowner_sign??"-",
        //                 $farmer->final_farmers->farmer_photo??"-",
        //                 $farmer->final_farmers->aadhaar_photo??"-",
        //                 $farmer->final_farmers->others_photo??"-",
        //                 $farmer->final_farmers->surveyor_name ?? "-",
        //                 $farmer->final_farmers->surveyor_mobile ?? "-"
        //             );
        //         } else {


        //             array_push(
        //                 $payload_fields,
        //                 $farmer->final_farmers->photo??"-",
        //                 $farmer->final_farmers->farmer_sign??"-",
        //                 $farmer->final_farmers->plotowner_sign??"-",
        //                 $farmer->final_farmers->farmer_photo??"-",
        //                 $farmer->final_farmers->aadhaar_photo??"-",
        //                 $farmer->final_farmers->others_photo??"-",
        //                 $farmer->final_farmers->surveyor_name ?? "-",
        //                 $farmer->final_farmers->surveyor_mobile ?? "-",
        //                 $farmer->final_farmers->final_status ?? "-",
        //                 $farmer->final_farmers->UserApprovedRejected->name??"-"
        //             );
        //         }
        //          if (isset($request->status) && $request->status  == 'Rejected') {
        //             // dd('ddsf');
        //             array_push(
        //                 $payload_fields,
        //                 $farmer->final_farmers->photo??"-",
        //                 $farmer->final_farmers->farmer_sign??"-",
        //                 $farmer->final_farmers->plotowner_sign??"-",
        //                 $farmer->final_farmers->farmer_photo??"-",
        //                 $farmer->final_farmers->aadhaar_photo??"-",
        //                 $farmer->final_farmers->others_photo??"-",
        //                 $farmer->final_farmers->surveyor_name ?? "-",
        //                 $farmer->final_farmers->surveyor_mobile ?? "-"
        //             );
        //         } else {


        //             array_push(
        //                 $payload_fields,
        //                 $farmer->final_farmers->photo??"-",
        //                 $farmer->final_farmers->farmer_sign??"-",
        //                 $farmer->final_farmers->plotowner_sign??"-",
        //                 $farmer->final_farmers->farmer_photo??"-",
        //                 $farmer->final_farmers->aadhaar_photo??"-",
        //                 $farmer->final_farmers->others_photo??"-",
        //                 $farmer->final_farmers->surveyor_name ?? "-",
        //                 $farmer->final_farmers->surveyor_mobile ?? "-",
        //                 $farmer->final_farmers->final_status ?? "-",
        //                 $farmer->final_farmers->UserApprovedRejected->name??"-"
        //             );
        //         }
        //     }
        // } //end of state

        return $payload_fields;
    }

    public function headings(): array
    {
        // $request = $this->request;
        // dd($request);
        $request = json_decode($this->request);
        // if($request->organization ){
            //     $organization = Company::where('id',$request->organization)->first();
        //     $state = State::where('id',$organization->state_id)->select('id','name','im_units')->get();
        //     $unit = $state->lm_units;
        // }



        //use this you want to change the heading dynamic like did here 
        if (isset($request->organization)) {
            // dd($request->organization);
            $organization = Company::where('id', $request->organization)->first();
            // dd($organization);
            if ($organization) {
                $state = State::where('id', $organization->state_id)->first();
                // dd($state);
                if ($state) {
                    $unit = $state->lm_units; // Assuming this is the correct property name
                    // Update headers with dynamic unit values
                    $own_area = 'Own area in ' . $unit;
                    $lease_area = 'Lease area in ' . $unit;
                    $total_area = 'Total area in ' . $unit;
                    // dd($own_area,$lease_area,$total_area);
                }
            }
        }

        $header = [

            'Organization Name',
            'Farmer uniqueID',
            'Farmer Name',
            'Mobile',
            'Gender',
            'Guardian name',
            'State',
            'District',
            'Taluka',
            'Panchayat',
            'Village',
            'Date survey',
            'Own area in Hectares',
            'Lease area in Hectares',
            'Total area in Hectares',
            'Financial Year',
            'Season',
            'Surveyor Name',
            'Surveyor Mobile',
            'Status',
            'Validator Name',



            // 'Organization Name',
            // 'Farmer Name',
            // 'Farmer UniqueID',
            // 'Mobile Access',
            // 'Gender',
            // 'Guardian Name',
            // 'State',
            // 'District',
            // 'Block',
            // 'Panchayat',
            // 'Village',
            // 'Date Form Submitted',
            // 'Own Land area in Acres',
            // 'Lease Land area in Acres',
            // // 'Mobile Number',
            // // 'Mobile Relation Owner',
            // // 'Aadhar Number',
            // 'Lease Land Owner Name',
            // 'Total Area in acres',
            // 'Total Area in bigha',
            // 'Financial Year' ,
            // 'Season' ,
            // 'Surveyor Name',
            // 'Surveyor Mobile Number',
            // 'Status',
            // 'Validator name'
            // // 'Khatian no/Plot No',
            // // 'Country',
            // // 'Village Remarks',
            // // 'Date & Time of Onboarding',
            // // 'Time Form Submitted',
            // // 'Land record photo',
            // // 'Farmer Signature',
            // // 'Lease Land Owner Signature',
            // // 'Farmer Photo',
            // // 'Aadhar Photo',
            // // 'Others Photo',
        ];





        // if (isset($request->state) && $request->state == 29) {
        //     array_push($header,
        //     'Total Area in Acres',
        //     'Total Area in Bigha',
        //     'Country',
        //     'State',
        //     'District',
        //     'Block',
        //     'Panchayat',
        //     'Village',
        //     'Actual Owner Name',
        //     'Survey No',
        //     'Land Record Photo',
        //     'Farmer Signature',
        //     'Lease Land Owner Signature',
        //     'Farmer Photo',
        //     'Aadhar Photo',
        //     'Others Photo',
        //     'Surveyor Name',
        //     'Surveyor Mobile',
        //     'Status',
        //     'Validator name'
        // );
        // } else {
        //     array_push($header,
        //     'Area in (A.G)',
        //     'Total Area in Acres',
        //     'Actual Owner Name',
        //     'Survey No',
        //     'Land Record Photo',
        //     'Farmer Signature',
        //     'Lease Land Owner Signature',
        //     'Farmer Photo',
        //     'Aadhar Photo',
        //     'Others Photo',
        //     'Surveyor Name',
        //     'Surveyor Mobile',
        //     'Status',
        //     'Validator name'
        // );
        // }

        // dd($header);
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
