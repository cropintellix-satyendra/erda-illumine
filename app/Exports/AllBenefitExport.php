<?php

namespace App\Exports;

use App\Models\BenefitDataValidation;
use App\Models\FarmerBenefit;
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

class AllBenefitExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles, WithChunkReading
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
        // dd('in');
        $request = json_decode($this->request);//whil development use this
        $request = $this->request; //in production use this
        $farmer = FarmerBenefit::whereHas('farmerapproved', function ($q) use ($request) {
            $q->where('onboarding_form', 1);
            $q->where('final_status_onboarding', 'Approved');
            if (isset($request->rolename) && $request->rolename == 'L-1-Validator') {
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
            return $q;
        })
            ->with('farmerapproved')
            ->when($request, function ($w) use ($request) {
                if (isset($request->executive_onboarding)  && $request->executive_onboarding) {
                    $w->where('surveyor_id', $request->executive_onboarding);
                }
                if (isset($request->start_date)  && $request->start_date) {
                    $w->whereDate('created_at', '>=', $request->start_date);
                }
                if (isset($request->end_date)  && $request->end_date) {
                    $w->whereDate('created_at', '<=', $request->end_date);
                }
                return $w;
            })->get();
            // dd($farmer[0]);
        return $farmer;
    }

    // here you select the row that you want in the file
    public function map($farmer): array
    {
        // dd($farmer->userid);
        $request = $this->request;
        $payload_fields = [
            $farmer->farmerapproved->organization->company??"-",
            $farmer->farmerapproved->farmer_uniqueId ?? "-",
            $farmer->farmerapproved->farmer_name ?? "-",
            $farmer->farmerapproved->mobile ?? "-",
            $farmer->farmerapproved->area_in_acers ?? "-",
            $farmer->farmerapproved->area_in_acers/0.330578512396694 ?? "-",
            $farmer->farmerplot_details->area_acre_awd ?? "_",
            $farmer->farmerapproved->mobile_access ?? "-",
            $farmer->farmerapproved->mobile_reln_owner ?? "-",
            $farmer->farmerapproved->country ?? "-",
            $farmer->farmerapproved->state ?? "-",
            $farmer->farmerapproved->district ?? "-",
            $farmer->farmerapproved->taluka ?? "-",
            $farmer->farmerapproved->panchayat ?? "-",
            $farmer->farmerapproved->village ?? "-",
            $farmer->benefit ?? "-",
            $farmer->seasons ?? "-",
            $farmer->surveyor_name ?? "-",
            $farmer->surveyor_mobile ?? "-",
            $farmer->status ?? "-",
            $farmer->farmer_benefits_img->path ?? "-",
            $farmer->farmerapproved->path ?? "-",
            $farmer->userid->name ?? "-",





            // $farmer->farmerapproved->farmer_uniqueId ?? "-", 
            // $farmer->farmerapproved->farmer_name ?? "-", 
            // $farmer->farmerapproved->mobile_access ?? "-",
            // $farmer->farmerapproved->mobile_reln_owner ?? "-", 
            // $farmer->farmerapproved->mobile ?? "-", 
            // $farmer->farmerapproved->state ?? "-",
            // $farmer->benefit ?? "-", 
            // $farmer->seasons ?? "-", 
            // $farmer->date_survey ?? "-", 
            // $farmer->surveyor_name ?? "-", 
            // $farmer->surveyor_mobile ?? "-",
        ];
        // $validation = BenefitDataValidation::where('farmer_uniqueId', $farmer->farmer_uniqueId)->where('level', 'L-1-Validator')->latest()->first();

        // array_push(
        //     $payload_fields, 
        //     $validation->status ?? "Pending", 
        //     $validation->timestamp ?? "-", 
        //     $validation->ValidatorUserDetail->name ?? "-"
        // );
        return $payload_fields;
    }

    public function headings(): array
    {
        $header = [
            'Organisation Name',
            'Farmer UniqueID',
            'Farmer Name',
            'Mobile',
            'Total Area in Acres',
            'Total Area in Bigha',
            'Total AWD Area in Acres',
            'Mobile Access',
            'Mobile Relation Owner',
            'country',
            'State', 
            'District',
            'Block',
            'Panchayat',
            'Village',
            'Benefit',
            'Seasons',
            'Surveyor Name',
            'Surveyor Mobile',
            'Status',
            'Image 1',
            'Image 2',
            'Validator name'



            // 'Farmer UniqueID', 
            // 'Farmer Name', 
            // 'Mobile Access', 
            // 'Mobile Relation Owner', 
            // 'Mobile', 
            // 'State', 
            // 'Benefit',
            // 'Seasons', 
            // 'DateSurvey', 
            // 'Surveyor Name', 
            // 'Surveyor Mobile', 
            // 'L1 Plotstatus', 
            // 'L1 Plotstatus DateTime', 
            // 'L1 Validator Name'
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
