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
    {        // $request = json_decode($this->request);//whil development use this
        $request = $this->request;
        // dd($request); 
        $farmer = FarmerPlot::whereHas('final_farmers', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
            if (isset($request->rolename) && $request->rolename == 'Viewer') {
                $viewerlocation = ViewerLocation::where('user_id', $request->userid)->first();
                $q->whereIn('state_id', explode(',', $viewerlocation->state));
            } //end of viewer
            if (isset($request->state) && $request->state) {
                $q->where('state_id', 'like', $request->state);
                // dd($request->state);
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
        })
            ->when('filter', function ($w) use ($request) {
                if (isset($request->l1_validator) && $request->l1_validator) {
                    //mainly this will be used when admin, viewer are downloading data
                    $w->where('aprv_recj_userid', $request->l1_validator);
                }
            })
            ->with('farmer')->limit(100)->get();
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
        
        $payload_fields = [
            $farmer->final_farmers->organization->company ??"_",
            $farmer->final_farmers->farmer_uniqueId ?? "-",
            $farmer->final_farmers->farmer_name ?? "-",
            $farmer->final_farmers->gender ?? "-",
            $farmer->final_farmers->guardian_name ?? "-",
            $farmer->final_farmers->mobile ?? "-",
            $farmer->final_farmers->mobile_access ?? "-",
            $farmer->final_farmers->mobile_reln_owner ?? "-",
            $farmer->final_farmers->aadhaar ?? "-",
            $farmer->final_farmers->lease_area_in_acres??"-",
            $farmer->final_farmers->actual_owner_name??"-",
            $farmer->final_farmers->plot_no??"-",
           




           
        ];


        if ($farmer->final_farmers->state_id == 36) {
            // dd('36');
            //need to add plot area for telangana of converted data
            $farmerplots = FarmerPlot::where('final_farmers', $farmer->final_farmers->farmer_uniqueId??"-")->get();
            //  FinalFarmer::where('farmer_uniqueId',$farmer->farmer_uniqueId)->get();
            $guntha = 0.025000;
            $total_area_acres  = 0;
            foreach ($farmerplots as $plotsarea) {
                $area = number_format((float)$plotsarea->area_in_acers, 2, '.', '');
                $split = explode('.', $area); //spliting area
                $valueafterdecimal = (isset($split[1]) && $split[1]) ? $split[1] : 0; //take array of index 1 value after decimal point
                $result = $valueafterdecimal * $guntha; // multiplying value with defined base value
                $conversion = explode('.', $result); // split result
                $conversion = $conversion[1] ?? 0;
                $acers = $split[0] . '.' . $conversion; // concat the obtained result with firstly split data
                $total_area_acres += $acers;
            }
            //

            array_push(
                
                $payload_fields,
                $total_area_acres ?? "-",
                $farmer->final_farmers->country ?? "-",
                $farmer->final_farmers->state ?? "-",
                $farmer->final_farmers->district ?? "-",
                $farmer->final_farmers->taluka ?? "-",
                $farmer->final_farmers->panchayat ?? "-",
                $farmer->final_farmers->village ?? "-",
                $farmer->final_farmers->latitude ?? "-",
                $farmer->final_farmers->longitude ?? "-",
                $farmer->final_farmers->date_survey ?? "-",
                $farmer->final_farmers->time_survey ?? "-",
                $farmer->final_farmers->remarks ?? "-",
                $farmer->PlotImgUrl ?? "-",
                
            );

            //second part
            $guntha = 0.025000;
            $area = number_format((float)$farmer->area_in_acers, 2, '.', '');
            $split = explode('.', $area); //spliting area
            $valueafterdecimal = (isset($split[1]) && $split[1]) ? $split[1] : 0; //take array of index 1 value after decimal point
            $result = $valueafterdecimal * $guntha; // multiplying value with defined base value
            $conversion = explode('.', $result); // split result
            $conversion = $conversion[1] ?? 0;
            $acers = $split[0] . '.' . $conversion; // concat the obtained result with firstly split data

            array_push($payload_fields, 
            $farmer->area_in_acers ?? "-", 
            $farmer->actual_owner_name ?? "-", 
            $farmer->survey_no ?? "-");



            if (isset($request->status) && $request->status == 'Rejected') {
                array_push(
                    $payload_fields,
                    $farmer->status ?? "-",
                    $farmer->reject_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->reject_timestamp)->format('d-m-Y') : "-",
                    $farmer->reject_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->reject_timestamp)->format('H:i:s') : "-",
                    $farmer->UserApprovedRejected->name ?? "-",
                    $farmer->farmer->surveyor_name ?? "-",
                    $farmer->farmer->surveyor_mobile ?? "-"
                );
            } elseif (isset($request->status) && $request->status == 'Approved') {
                array_push(
                    $payload_fields,
                    $farmer->status ?? "-",
                    $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp ?? "")->format('d-m-Y') : "-",
                    $farmer->appr_timestamp ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $farmer->appr_timestamp ?? "")->format('H:i:s') : "-",
                    $farmer->UserApprovedRejected->name ?? "-",
                    $farmer->final_farmers->surveyor_name ?? "-",
                    $farmer->final_farmers->surveyor_mobile ?? "-"
                );
            } else {
                array_push(
                    $payload_fields,
                    $farmer->status ?? "-",
                    "-",
                    "-",
                    "-",
                    $farmer->farmer->surveyor_name ?? "-",
                    $farmer->farmer->surveyor_mobile ?? "-"
                );
            }
        } else {

            // if (isset($request->state) && $request->state == 29)
            if($farmer->final_farmers->state_id == 29) {
                dd('29');
                array_push(
                    $payload_fields,
                    $farmer->final_farmers->area_in_acers ?? "-",
                    $farmer->final_farmers->area_in_acers/0.330578512396694 ?? "-",
                    $farmer->final_farmers->country ?? "-",
                    $farmer->final_farmers->state ?? "-",
                    $farmer->final_farmers->district ?? "-",
                    $farmer->final_farmers->taluka ?? "-",
                    $farmer->final_farmers->panchayat ?? "-",
                    $farmer->final_farmers->village ?? "-",
                    $farmer->final_farmers->latitude ?? "-",
                    $farmer->final_farmers->longitude ?? "-",
                    $farmer->final_farmers->date_survey ?? "-",
                    $farmer->final_farmers->time_survey ?? "-",
                    $farmer->final_farmers->remarks ?? "-",
                    $farmer->PlotImgUrl ?? "-",
                    
                );
                array_push(
                    $payload_fields,
                    $farmer->final_farmers->area_in_acers ?? "-",
                    $farmer->actual_owner_name ?? "-",
                    $farmer->survey_no ?? "-",
                    $farmer->status ?? "-",
                    $farmer->final_farmers->surveyor_name ?? "-",
                    $farmer->final_farmers->surveyor_mobile ?? "-"
                );
            } else {
                array_push(
                    $payload_fields,
                    $farmer->final_farmers->total_plot_area ?? "-",
                    $farmer->final_farmers->country ?? "-",
                    $farmer->final_farmers->state ?? "-",
                    $farmer->final_farmers->district ?? "-",
                    $farmer->final_farmers->taluka ?? "-",
                    $farmer->final_farmers->panchayat ?? "-",
                    $farmer->final_farmers->village ?? "-",
                    $farmer->final_farmers->latitude ?? "-",
                    $farmer->final_farmers->longitude ?? "-",
                    $farmer->final_farmers->date_survey ?? "-",
                    $farmer->final_farmers->time_survey ?? "-",
                    $farmer->final_farmers->remarks ?? "-",
                    $farmer->PlotImgUrl ?? "-",
                    
                );

                array_push($payload_fields, "-", 
                $farmer->final_farmers->area_in_acers ?? "-",  
                $farmer->actual_owner_name ?? "-", 
                $farmer->survey_no ?? "-"
            );
                // dd('ddsf');
                if (isset($request->status) && $request->status  == 'Rejected') {
                    // dd('ddsf');
                    array_push(
                        $payload_fields,
                        $farmer->final_farmers->photo??"-",
                        $farmer->final_farmers->farmer_sign??"-",
                        $farmer->final_farmers->plotowner_sign??"-",
                        $farmer->final_farmers->farmer_photo??"-",
                        $farmer->final_farmers->aadhaar_photo??"-",
                        $farmer->final_farmers->others_photo??"-",
                        $farmer->final_farmers->surveyor_name ?? "-",
                        $farmer->final_farmers->surveyor_mobile ?? "-"
                    );
                } else {


                    array_push(
                        $payload_fields,
                        $farmer->final_farmers->photo??"-",
                        $farmer->final_farmers->farmer_sign??"-",
                        $farmer->final_farmers->plotowner_sign??"-",
                        $farmer->final_farmers->farmer_photo??"-",
                        $farmer->final_farmers->aadhaar_photo??"-",
                        $farmer->final_farmers->others_photo??"-",
                        $farmer->final_farmers->surveyor_name ?? "-",
                        $farmer->final_farmers->surveyor_mobile ?? "-",
                        $farmer->final_farmers->final_status ?? "-",
                        $farmer->final_farmers->UserApprovedRejected->name??"-"
                    );
                }
            }
        } //end of state

        return $payload_fields;
    }

    public function headings(): array
    {
        $request = $this->request;
        $header = [
            'Organisation Name',
            'Farmer UniqueID', 
            'Farmer Name', 
            'Gender',
            'Guardian Name',
            'Mobile Number',
            'Mobile Access', 
            'Mobile Relation Owner', 
            'Aadhar Number',
            'Lease Land area in Acres',
            'Lease Land Owner Name',
            'Khatian no/Plot No',
            'Total Plot Area (Acres)', 
            'Country',
            'State', 
            'District', 
            'Taluka', 
            'Panchayat', 
            'Village', 
            'Latitude', 
            'Longitude', 
            'Date Form Submitted', 
            'Time Form Submitted',
            'Village Remarks',
            'Plot Images',
            // 'Land Record Photo',
            // 'Farmer Signature',
            // 'Lease Land Owner Signature',
            // 'Farmer Photo',
            // 'Aadhar Photo',
            // 'Others Photo',
            // 'Date Form Submitted',
            // 'Time Form Submitted',
            
        ];

        //  if(is_numeric($this->farmeruniqueid)){ // w'll be used when request come from farmer detail page
        //     $farmerstate =  DB::table('farmers')->where('farmer_uniqueId',$this->farmeruniqueid)->first();
        //     if($farmerstate->state_id == 36){
        //         array_push($header, 'Area in (A.G)','Area in Acres','Land Ownership','Actual Owner Name','Survey No','PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','Surveyor Name', 'Surveyor Mobile');
        //     }else{
        //         array_push($header,'Area in Acers','Land Ownership','Actual Owner Name','Survey No','PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','Surveyor Name', 'Surveyor Mobile');
        //     }
        //  }else{
        //      if(isset($request->state) && $request->state == 29){
        //         array_push($header,'Area in Acers','Land Ownership','Actual Owner Name','Survey No','PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','Surveyor Name', 'Surveyor Mobile');
        //      }else{
        //         array_push($header,'Area in (A.G)','Area in Acres','Land Ownership','Actual Owner Name','Survey No','PlotStatus','L1 Plotstatus update Date','L1 Plotstatus update Time','L1 Validator Name','Surveyor Name', 'Surveyor Mobile');
        //      }
        //  }

        if (isset($request->state) && $request->state == 29) {
            array_push($header, 
            'Total Area in Acres',
            'Total Area in Bigha',
            'Country',
            'State', 
            'District', 
            'Block',
            'Panchayat',
            'Village', 
            'Actual Owner Name', 
            'Survey No', 
            'Land Record Photo',
            'Farmer Signature',
            'Lease Land Owner Signature',
            'Farmer Photo',
            'Aadhar Photo',
            'Others Photo',
            'Surveyor Name', 
            'Surveyor Mobile',
            'Status',
            'Validator name'
        );
        } else {
            array_push($header, 
            'Area in (A.G)', 
            'Total Area in Acres', 
            'Actual Owner Name', 
            'Survey No',
            'Land Record Photo',
            'Farmer Signature',
            'Lease Land Owner Signature',
            'Farmer Photo',
            'Aadhar Photo',
            'Others Photo', 
            'Surveyor Name', 
            'Surveyor Mobile',
            'Status',
            'Validator name'
        );
        }


        //   if(is_numeric($this->farmeruniqueid)){ // w'll be used when request come from farmer detail page
        //     $farmer = $this->collection()->first();//get the record from collection
        //     // 1. below procedure is to make header based on no of cropdata record and benefits record for particular farmer.
        //     // 2. based on count of cropdata and benefit record stored in DB now making adding header in Excel same goes for benfits also
        //     //e.x. if this farmer has only two plot that header of cropdata added two times
        //     //doing this just to take count of plot
        //     $CropData =  DB::table('farmer_plot_detail')->where('farmer_uniqueId',$farmer->farmer_uniqueId)->count();
        //     if($CropData > 0){
        //       for($x = 1; $x <= $CropData; $x++){
        //             array_push($header, 'CROPDATA', 'Area of plots', 'Seasons', 'Crop Variety','Lastirrigation date', 'Date of Ploughing', 'Date of Transplanting',
        //             'landOwnership', 'Survey No','Surveyor Name', 'Surveyor Mobile');
        //       }//end foreach
        //     }//cropdata end if
        //     //for benefits
        //     $benefits = DB::table('benefits')->where('status',1)->select('name')->get();
        //     if($benefits->count() > 0){
        //       foreach($benefits as $q){
        //             array_push($header, $q->name, 'Total Plot Area', 'Seasons', 'Benefits','Surveyor Name', 'Surveyor Mobile');
        //       }//end foreach
        //     }//benefit if end
        //   }else{
        //     $farmer = $this->collection();
        //     $benefits = DB::table('benefits')->where('status',1)->select('name')->get();
        //     $plotdata = DB::table('farmers')->max('no_of_plots');
        //     // for ($x = 1; $x <= $plotdata; $x++) {
        //     //   array_push($header, 'Plot Images','PlotData', 'Plot ID', 'Area in Acers', 'Land Ownership','Actual Owner Name','Survey No');
        //     // }
        //   }//elseif
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
