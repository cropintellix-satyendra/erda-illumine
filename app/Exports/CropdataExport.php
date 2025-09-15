<?php

namespace App\Exports;

use App\Models\FarmerCropdata;
use App\Models\CropDataValidation;
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
use App\Models\ViewerLocation;
use Illuminate\Cache\RateLimiting\Limit;

class CropdataExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
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
       // $request = json_decode($this->request);//wh/il development use this
        $request = $this->request; //in production use this

        $farmer = FarmerCropdata::whereHas('farmerapproved', function ($q) use ($request) {
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
                $q->where('organization_id',  $request->organization);
            }

            return $q;
        })

            ->when('filter', function ($w) use ($request) {
                if (isset($request->start_date) && !empty($request->start_date)) {
                    $w->whereDate('created_at', '>=', $request->start_date);
                }
                if (isset($request->end_date) && !empty($request->end_date)) {
                    $w->whereDate('created_at', '<=', $request->end_date);
                }
                if (isset($request->executive_onboarding) && !empty($request->executive_onboarding)) {
                    $w->where('surveyor_id', $request->executive_onboarding);
                }

                if (isset($request->rolename)  && $request->rolename != 'SuperAdmin' && $request->rolename != 'Viewer') {
                    if (isset($request->userid)  && !empty($request->userid) && $request->status != 'Pending') {
                        $w->where('apprv_reject_user_id', $request->userid);
                    }
                }
                if (isset($request->status) && !empty($request->status)) {
                    $w->where('status', $request->status);
                }
                if (isset($request->l1_validator)  && $request->l1_validator) {
                    $w->where('l2_apprv_reject_user_id', 'like', $request->l1_validator);
                }
                return $w;
            })
            ->with('farmerapproved')
            ->latest()
            ->get();
        // dd($farmer[0]);
        return $farmer;

            
        if ($farmer->aeration_data != null) {
            if($farmer->aeration_data->aeration_no == 1 )
            {
                $date1= $farmer->aeration_data->date_survey;
                return $date1;
            }
            else{

                $date1= $farmer->aeration_data->date_survey;
                return $date1;
            }
            
        }
    }



    // here you select the row that you want in the file
    public function map($farmer): array
    {    

        // dd($farmer->aeration_data);
        
        $payload_fields = [
            $farmer->farmerapproved->organization->company??"-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->own_area_in_acres ?? "-",
            $farmer->farmerapproved->lease_area_in_acres ?? "-",
            $farmer->farmerapproved->actual_owner_name ?? "-",
            $farmer->area_in_acers ?? "-",
            $farmer->area_in_acers/0.330578512396694 ?? "-",
            $farmer->farmerplot_details->khatian_number ?? "-",
            $farmer->farmerapproved->country ?? "-",
            $farmer->farmerapproved->state->name ?? "-",
            $farmer->farmerapproved->district->district ?? "-",
            $farmer->farmerapproved->taluka->taluka ?? "-",
            $farmer->farmerapproved->panchayat->panchayat ?? "-",
            $farmer->farmerapproved->village->village ?? "-",
            $farmer->PlotCropDetails->nursery ??"-",
            $farmer->PlotCropDetails->crop_season_currentyrs ??"_", 
            $farmer->PlotCropDetails->yeild_lastyrs ??"_",
            $farmer->pipe_images->date??"NA",
           
            $date1??"-",
            $date1??"-",
            \Carbon\Carbon::createFromFormat('d/m/Y', $farmer->dt_transplanting)->format('d-m-Y'),
            $farmer->farmerapproved->land_ownership ??"_", 
            $farmer->date_survey ??"-", 
            $farmer->date_time ??"_",
            $farmer->surveyor_name ??"_", 
            $farmer->surveyor_mobile ??"-", 
            $farmer->PlotCropDetails->crop_season_lastyrs ?? "-",
            $farmer->PlotCropDetails->crop_season_currentyrs ?? "", 
            $farmer->PlotCropDetails->crop_variety_lastyrs ?? "-",
            $farmer->PlotCropDetails->crop_variety_currentyrs ?? "-",
            $farmer->PlotCropDetails->fertilizer_1_currentyrs ?? "-",
            $farmer->PlotCropDetails->fertilizer_2_currentyrs ?? "-",
            $farmer->PlotCropDetails->fertilizer_3_currentyrs ?? "-",
            $farmer->status ?? "-",
            $farmer->usertag->name??"-" 
          
        ];
        $validator = CropDataValidation::where('farmer_plot_uniqueid', $farmer->farmer_plot_uniqueid)->where('level', 'L-1-Validator')->latest()->first();
        // dd($farmer->ValidatorUserDetail);
        array_push($payload_fields  );

        return $payload_fields;
    }

    public function headings(): array
    {
        $header = [
            'Organisation Name',
            'Farmer UniqueID', 
            'Farmer Name', 
           
            'Mobile',
            'Own Land area in Acres',
            'Lease Land area in Acres',
            'Lease Land Owner Name',
            'Total Area in Acres',
            'Total Area in Bigha',
            'Khatian no/Plot No',
            'country','State', 
            'District',
            'Block',
            'Panchayat',
            'Village',
            'Date of Nursery',
          
            'Crop season',
            'Yield kg/acre',
            'Pipe installation date',
            '1st drying date',
            '2st drying date',
            'Date of Transplanting',
            'landOwnership',
            'Date',
            'Time', 
            'Surveyor Name', 
            'Surveyor Mobile', 
            'Crop Season last Year',
            'Crop Season Current Year',
            'Crop Variety last Year', 
            'Crop Variety Current Year', 
            'Nitrogen current year kg/acre',
            'phosphorus current year kg/acre',
            'potassium  current year kg/acre',
            'Status',
            'Validator name',
           
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
