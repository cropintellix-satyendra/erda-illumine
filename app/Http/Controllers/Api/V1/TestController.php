<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Farmer;
use App\Models\FarmerPlot;

use App\Models\PlotStatusRecord;

use App\Models\Uniqueid;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\UserDevices;
use Illuminate\Support\Facades\Hash;
use Storage;
use DB;
use App\Models\Village;
use App\Models\Panchayat;
use App\Models\FarmerCropdata;
use App\Models\PipeInstallation;
use App\Models\AerationImage;
use App\Models\PipeInstallationPipeImg;
use App\Models\Aeration;
use App\Models\FinalFarmer;
use App\Models\FarmerBenefit;
use Exception ;
use App\Models\FarmerFarmDetails;
use App\Models\Taluka;
use App\Models\District;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Http\Message\ResponseInterface;

class TestController extends Controller
{
    // public function upload_delete(Request $request){
    //     // dd($request->all(), 'csc');
    //     //with daag and patta for own land and leased
    //     $data = Excel::toCollection(null,$request->file);
    //     // Loop through the data and store it in the database
    //     $unique = 28226;
    //     $plot_no_sr = 1;

    //     foreach ($data[0] as $row) {
    //         $data = DB::table('final_farmers')->select('id','farmer_survey_id','farmer_plot_uniqueid','farmer_uniqueId')
    //             ->where('farmer_survey_id', $row[0]) 
    //             ->get();

    //         // dd($data);

    //         foreach($data as $item){  
    //             // dd($item, 'cs');              
    //             $crop = FinalFarmer::where('id',$item->id)->forceDelete();
    //             // dd($crop);
    //             // forceDelete
    //             $crop = FarmerPlot::where('farmer_id',$item->id)->forceDelete();
    //             // dd()
                
    //         }
    //     }
    //     return response()->json('done');

            
    // }
    // ->forceDelete();


    public function delete_data(Request $request)
    {
        

    // Get duplicate farmer_plot_uniqueid values
    $duplicates = FarmerPlot::select('farmer_plot_uniqueid')
        ->groupBy('farmer_plot_uniqueid')
        ->havingRaw('COUNT(*) > 1')
        ->pluck('farmer_plot_uniqueid');
        // dd($duplicates);
    // Delete duplicate records, keeping the one with the smallest id
    foreach ($duplicates as $duplicate) {
        // dd( $duplicate);
        // Get IDs of duplicates for the current farmer_plot_uniqueid
        $duplicateIds = FarmerPlot::where('farmer_plot_uniqueid', $duplicate)
            ->orderBy('id', 'desc') // Order by id descending to keep the first record
            ->pluck('id')
            ->slice(1); // Exclude the first (smallest) id
        // dd($duplicateIds);
        // Delete the duplicates
        FarmerPlot::whereIn('id', $duplicateIds)->delete();
        // dd("stop");
    }

    return response()->json(['message' => 'Duplicates removed successfully']);
}


    public function check_plot2(Request $request){
        //with daag and patta for own land and leased
        $data = Excel::toCollection(null,$request->file);
        // Loop through the data and store it in the database
        $unique = 28226;
        $plot_no_sr = 1;

        foreach ($data[0] as $row) {

            $crop = FarmerCropdata::select('id','farmer_uniqueId')->where('farmer_uniqueId',$row[1])->first();
            // if($crop){
            //     $values = array('farmer_uniqueId' => $crop->farmer_uniqueId, 'plot_no'=> $row[4],'crop' => '1');
            //     DB::table('test')->insert($values);
            // }


            $pipe = PipeInstallation::select('id','farmer_uniqueId')->where('farmer_uniqueId',$row[1])->first();
            // if($pipe){
            //     $values = array('farmer_uniqueId' => $crop->farmer_uniqueId, 'plot_no'=> $row[4],'pipe' => '1');
            //     DB::table('test')->insert($values);
            // }

            $aeration = Aeration::select('id','farmer_uniqueId')->where('farmer_uniqueId',$row[1])->first();
            // if($aeration){
            //     $values = array('farmer_uniqueId' => $crop->farmer_uniqueId, 'plot_no'=> $row[4],'aeration' => '1');
            //     DB::table('test')->insert($values);
            // }

            $benfit = FarmerBenefit::select('id','farmer_uniqueId')->where('farmer_uniqueId',$row[1])->first();
            // if($benfit){
            //     $values = array('farmer_uniqueId' => $crop->farmer_uniqueId, 'plot_no'=> $row[4],'benfit' => '1');
            //     DB::table('test')->insert($values);
            // }

                $values = array('farmer_uniqueId' => $row[1]??NULL, 'plot_no'=> $row[4],'crop' => $crop->farmer_uniqueId??NULL
                                                ,'pipe' => $pipe->farmer_uniqueId??NULL,'aeration' => $aeration->farmer_uniqueId??NULL,'benfit' => $benfit->farmer_uniqueId??NULL);
                DB::table('test')->insert($values);

            // dd($crop, $row);
          

            
        }
        return response()->json('done');
    }


    public function change_to_zero(Request $request){
        $plotDetails = DB::table('farmer_plot_detail')
                        ->join('final_farmers', 'farmer_plot_detail.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                        // ->where('final_farmers.state_id', 29)//assam
                        ->where('final_farmers.state_id', 37)//westbengal
                        ->select('farmer_plot_detail.*')
                        ->groupBy('farmer_plot_detail.farmer_uniqueId')
                        ->havingRaw('COUNT(farmer_plot_detail.farmer_uniqueId) > 1')
                        ->get();

                // dd( $plotDetails);

        foreach($plotDetails as $item){ 
            FarmerPlot::where('farmer_uniqueId', $item->farmer_uniqueId)->update([
                'area_in_acers'  =>     "0.0",
                'area_in_other'  =>     "0.0",
                'area_acre_awd'  =>     "0.0",
                'area_other_awd'  =>     "0.0",
            ]);            
        }
        return response()->json('done');
                dd( $plotDetails);
    }
        

    public function change_data_westbengal(){
        // $cropdata = DB::table('farmer_cropdata')
        //     ->join('final_farmers', 'farmer_cropdata.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
        //     ->where('final_farmers.state_id', 37) //for west bengal
        //     ->select('farmer_cropdata.farmer_uniqueId','farmer_cropdata.farmer_plot_uniqueid','farmer_cropdata.id')
        //     ->get();

        // dd($cropdata);
        // foreach($cropdata as $item){                
        //     $crop = FarmerCropdata::where('farmer_plot_uniqueid',$item->farmer_plot_uniqueid)->forceDelete();
        // }


        // $pipedetail = DB::table('pipe_installations')
        //     ->join('final_farmers', 'pipe_installations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
        //     ->where('final_farmers.state_id', 37) //for west bengal
        //     ->select('pipe_installations.farmer_uniqueId','pipe_installations.farmer_plot_uniqueid','pipe_installations.id')
        //     ->get();
        // //  dd($pipedetail);
        // foreach($pipedetail as $item){              
        //     $piep = PipeInstallation::where('farmer_plot_uniqueid',$item->farmer_plot_uniqueid)->forceDelete();
        //     $image = PipeInstallationPipeImg::where('farmer_plot_uniqueid',$item->farmer_plot_uniqueid)->forceDelete();          
        // }


        // $aerationdetail = DB::table('aerations')
        //     ->join('final_farmers', 'aerations.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
        //     ->where('final_farmers.state_id', 37) //for west bengal
        //     ->select('aerations.farmer_uniqueId','aerations.farmer_plot_uniqueid','aerations.id')
        //     ->get();
        //  dd($aerationdetail);
        // foreach($aerationdetail as $item){              
        //     $aera = Aeration::where('farmer_plot_uniqueid',$item->farmer_plot_uniqueid)->forceDelete();
        //     $image = AerationImage::where('farmer_plot_uniqueid',$item->farmer_plot_uniqueid)->forceDelete();      
        // }

        // $benefitdetail = DB::table('farmer_benefits')
        //     ->join('final_farmers', 'farmer_benefits.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
        //     ->where('final_farmers.state_id', 37) //for west bengal
        //     ->select('farmer_benefits.farmer_uniqueId','farmer_benefits.id')
        //     ->get();

            return response()->json('done');


    }

    // public function newupload_survey_assam(Request $request){
    //     //with daag and patta for own land and leased
    //     $data = Excel::toCollection(null,$request->file);
    //     // Loop through the data and store it in the database
    //     $unique = 28226;
    //     $plot_no_sr = 1;

    //     // dd($data[0]);
    //     foreach ($data[0] as $row) {
    //         // dd($row);
    //         $district = DB::table('districts')->Where('district',$row[8])->select('id','district')->first();
    //         $taluka = DB::table('talukas')->where('taluka', 'like', '%'.$row[6])->select('id','taluka')->first();
    //         $panchayat=NULL;
    //         if($district && $taluka ){
    //             $panchayat = DB::table('panchayats')->where('state_id',29)->where('id',$district->id)->orWhere('taluka_id',$taluka->id)->select('id','panchayat')->first();
    //         }
    //         $village = DB::table('villages')->where('village',$row[8])->select('id','village')->first();

    //         $plot_no_serial = 1;
    //         foreach(explode(',',$row[9]) as $plots){
    //                 if($row[9]){ 
    //                     $final = new FinalFarmer;
    //                     $final->surveyor_id  = 1;
    //                     $final->surveyor_name  = 'ADMIN';
    //                     $final->surveyor_email  = 'superadmin@crop.com';
    //                     $final->surveyor_mobile  = '1245657895';

    //                     $final->status_onboarding=  'Approved';  
    //                     $final->final_status_onboarding=  'Approved';  
    //                     $final->onboarding_form=  1;  
    //                     $final->final_status=  'Approved';  
    //                     $final->L2_aprv_timestamp   =  Carbon::now();  
    //                     $final->L1_appr_timestamp   =  Carbon::now();  
    //                     $final->mobile_access   =  'Own Number';
    //                     $final->mobile_reln_owner   =  'NA';   
    //                     $final->country_id=  '101';  
    //                     $final->country=  'India';  
    //                     $final->state_id=  '29';  
    //                     $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
    //                     $final->check_carbon_credit=  '1';  
    //                     $final->affidavit_tnc=  '0';                      
    //                     $final->L2_appr_userid      =  '1';  
    //                     $final->L1_aprv_recj_userid =  '1';  


    //                     $final->district_id     =   $district->id??NULL;
    //                     $final->district        =   $district->district??$row[8];
    //                     $final->taluka_id       =   $taluka->id??NULL;
    //                     $final->taluka          =   $taluka->taluka??$row[6];
    //                     if($panchayat){
    //                         $final->panchayat_id    =   $panchayat->id??NULL;
    //                         $final->panchayat       =   $panchayat->panchayat??NULl;  
    //                     }else{
    //                         $final->panchayat_id    =   NULL;
    //                         $final->panchayat       =   NULl;  
    //                     }
    //                     $final->village_id      =   $village->id ??NULL;
    //                     $final->village         =   $village->village ??$row[7];
                    
    //                     $final->farmer_survey_id=   $row[0]??NULL;
    //                     $final->farmer_name     =   $row[1]??NULL;
    //                     if($row[2]){
    //                         if($row[2] == 'M'){
    //                             $final->gender=   'MALE';
    //                         }else{
    //                             $final->gender=   'FEMALE';
    //                         }
    //                     }
    //                     $final->guardian_name   =   $row[3]??NULL;
    //                     $final->aadhaar=   $row[4]??NULL;
    //                     $final->mobile   =   $row[5]??NULL;
    //                     $final->mobile_verified=   1;
    //                     //for owner plot

                        
    //                     // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
    //                     $final->no_of_plots=   count(explode(',',$row[9]))??NULL;//1??NULL; //assam
    //                     $final->area_in_acers   =  '0.0';// 0.3305785123 * floatval($row[11]) ??0; 

    //                     $final->leased_area     =   '0.0';// 0.3305785123 * floatval($row[12])??0;

    //                     $final->total_plot_area=  '0.0';// 0.3305785123 * (floatval($row[11])??0 + floatval($row[12])??0)??NULL; 
                        

    //                     $final->plot_no   =   $plot_no_serial;
    //                     //   dd( $row[11], $row[12], $final);

    //                     $final->farmer_uniqueId   =  $unique; 
    //                     $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
    //                     // $final->leased_land_plot =   $row[14]??NULL; 

    //                     $final->land_ownership=  'Own';
                        
    //                     $final->date_survey=  '2022-01-01';  
    //                     $final->time_survey=  '12:20:47';  
    //                     $final->save();
    //                     //end end end end end nend
    //                     //for owner plot

    //                     //new plot
    //                     $FarmerPlot = new FarmerPlot;
    //                     $FarmerPlot->farmer_id          =   $final->id;
    //                     $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
    //                     $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

    //                     $FarmerPlot->plot_no           =   $final->plot_no;
    //                     $FarmerPlot->area_in_acers      =   $final->area_in_acers;


    //                     $FarmerPlot->land_ownership     =   $final->land_ownership;

    //                     $FarmerPlot->final_status       = 'Approved';
    //                     $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
    //                     $FarmerPlot->finalappr_userid =  '1';  
    //                     $FarmerPlot->status             = 'Approved';
    //                     //for owner plot

    //                     $FarmerPlot->appr_timestamp=  Carbon::now();  
    //                     $FarmerPlot->aprv_recj_userid=  '1'; 

    //                     $FarmerPlot->daag_number=  $plots??NULL;                               
    //                     $FarmerPlot->patta_number= NULL;  

                    
                        
    //                     $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[11]) ??0; 
    //                     $FarmerPlot->area_in_other=  '0.0';// $row[11]??"0.0";  
    //                     $FarmerPlot->area_in_other_unit= "Bigha";

                        
    //                     $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[11]) ??0; //'0.0';// 0.3305785123 * floatval($row[11]) ??0; 
    //                     $FarmerPlot->area_other_awd= $row[11];// $row[11]??"0.0";  
    //                     $FarmerPlot->area_other_awd_unit= "Bigha";                        
    //                     $FarmerPlot->save();
    //                 }//if own land has data

    //                 $plot_no_serial++;
    //             }//foreach end for daag

    //             // $plot_no_serial++;

    //             foreach(explode(',',$row[10]) as $plots_patt){
    //                 if($row[10]){
    //                 // if($row[12]){
    //                     //for leased  plot
    //                     $final = new FinalFarmer;
    //                     $final->surveyor_id  = 1;
    //                     $final->surveyor_name  = 'ADMIN';
    //                     $final->surveyor_email  = 'superadmin@crop.com';
    //                     $final->surveyor_mobile  = '1245657895';

    //                     $final->status_onboarding=  'Approved';  
    //                     $final->final_status_onboarding=  'Approved';  
    //                     $final->onboarding_form=  1;  
    //                     $final->final_status=  'Approved';  
    //                     $final->L2_aprv_timestamp   =  Carbon::now();  
    //                     $final->L1_appr_timestamp   =  Carbon::now();  
    //                     $final->mobile_access   =  'Own Number';
    //                     $final->mobile_reln_owner   =  'NA';   
    //                     $final->country_id=  '101';  
    //                     $final->country=  'India';  
    //                     $final->state_id=  '29';  
    //                     $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
    //                     $final->check_carbon_credit=  '1';  
    //                     $final->affidavit_tnc=  '0';                      
    //                     $final->L2_appr_userid      =  '1';  
    //                     $final->L1_aprv_recj_userid =  '1';  
    //                     //for leased  plot


    //                     $final->district_id     =   $district->id??NULL;
    //                     $final->district        =   $district->district??$row[8];
    //                     $final->taluka_id       =   $taluka->id??NULL;
    //                     $final->taluka          =   $taluka->taluka??$row[6];
    //                     if($panchayat){
    //                         $final->panchayat_id    =   $panchayat->id??NULL;
    //                         $final->panchayat       =   $panchayat->panchayat??NULl;  
    //                     }
    //                     $final->village_id      =   $village->id ??NULL;
    //                     $final->village         =   $village->village ??$row[7];
                    
    //                     $final->farmer_survey_id=   $row[0]??NULL;
    //                     $final->farmer_name     =   $row[1]??NULL;
    //                     if($row[2]){
    //                         if($row[2] == 'M'){
    //                             $final->gender=   'MALE';
    //                         }else{
    //                             $final->gender=   'FEMALE';
    //                         }
    //                     }
    //                     //for leased  plot

    //                     $final->guardian_name   =   $row[3]??NULL;
    //                     $final->aadhaar=   $row[4]??NULL;
    //                     $final->mobile   =   $row[5]??NULL;
    //                     $final->mobile_verified=   1;

                        
    //                     // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
    //                     $final->no_of_plots=  count(explode(',',$row[10]))??NULL;
    //                     $final->area_in_acers   = '0.0';//  0.3305785123 * floatval($row[12]) ??0; 

    //                     // $final->leased_area     =   0.3305785123 * floatval($row[12])??0;

    //                     $final->total_plot_area=  '0.0';//  0.3305785123 * (floatval($row[12])??0)??NULL; 
    //                     //for leased  plot
    //                     $final->plot_no   =     $plot_no_serial;              

    //                     $final->farmer_uniqueId   =  $unique; 
    //                     $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
    //                     // $final->leased_land_plot =   $row[14]??NULL; 
    //                     $final->land_ownership=  'Own';  
    //                     $final->date_survey=  '2022-01-01';  
    //                     $final->time_survey=  '12:20:47';  

    //                     $final->save();
    //                     //end end end end end nend

    //                     //new plot
    //                     $FarmerPlot = new FarmerPlot;
    //                     $FarmerPlot->farmer_id          =   $final->id;
    //                     $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
    //                     $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

    //                     $FarmerPlot->plot_no           =   $final->plot_no;
    //                     $FarmerPlot->area_in_acers      =   $final->area_in_acers;

    //                     //for leased  plot

    //                     $FarmerPlot->land_ownership     =   'OWN';

    //                     $FarmerPlot->final_status       = 'Approved';
    //                     $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
    //                     $FarmerPlot->finalappr_userid =  '1';  
    //                     $FarmerPlot->status             = 'Approved';

    //                     $FarmerPlot->appr_timestamp=  Carbon::now();  
    //                     $FarmerPlot->aprv_recj_userid=  '1'; 

    //                     // $FarmerPlot->daag_number=  $row[9]??NULL;                               
    //                     $FarmerPlot->patta_number=  $plots_patt; 
                        
    //                     $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[12]) ??0; 


    //                     $FarmerPlot->area_in_other=  '0.0';// $row[12]??"0.0";  
    //                     $FarmerPlot->area_in_other_unit= "Bigha";

    //                     $FarmerPlot->area_acre_awd= 0.3305785123 * floatval($row[11]) ??0;//'0.0';// 
    //                     $FarmerPlot->area_other_awd= $row[11];// $row[12]??"0.0";  
    //                     $FarmerPlot->area_other_awd_unit= "Bigha";

    //                     $FarmerPlot->save();
    //                 } 

    //                 $plot_no_serial ++;
    //             }//foreacgh end patta
    //             //

    //             // $plot_no_serial++;
    //             if($row[12]){
    //                         $final = new FinalFarmer;
    //                         $final->surveyor_id  = 1;
    //                         $final->surveyor_name  = 'ADMIN';
    //                         $final->surveyor_email  = 'superadmin@crop.com';
    //                         $final->surveyor_mobile  = '1245657895';

    //                         $final->status_onboarding=  'Approved';  
    //                         $final->final_status_onboarding=  'Approved';  
    //                         $final->onboarding_form=  1;  
    //                         $final->final_status=  'Approved';  
    //                         $final->L2_aprv_timestamp   =  Carbon::now();  
    //                         $final->L1_appr_timestamp   =  Carbon::now();  
    //                         $final->mobile_access   =  'Own Number';
    //                         $final->mobile_reln_owner   =  'NA';   
    //                         $final->country_id=  '101';  
    //                         $final->country=  'India';  
    //                         $final->state_id=  '29';  
    //                         $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
    //                         $final->check_carbon_credit=  '1';  
    //                         $final->affidavit_tnc=  '0';                      
    //                         $final->L2_appr_userid      =  '1';  
    //                         $final->L1_aprv_recj_userid =  '1';  
    //                         //for leased  plot


    //                         $final->district_id     =   $district->id??NULL;
    //                         $final->district        =   $district->district??$row[8];
    //                         $final->taluka_id       =   $taluka->id??NULL;
    //                         $final->taluka          =   $taluka->taluka??$row[6];
    //                         if($panchayat){
    //                             $final->panchayat_id    =   $panchayat->id??NULL;
    //                             $final->panchayat       =   $panchayat->panchayat??NULl;  
    //                         }
    //                         $final->village_id      =   $village->id ??NULL;
    //                         $final->village         =   $village->village ??$row[7];
                        
    //                         $final->farmer_survey_id=   $row[0]??NULL;
    //                         $final->farmer_name     =   $row[1]??NULL;
    //                         if($row[2]){
    //                             if($row[2] == 'M'){
    //                                 $final->gender=   'MALE';
    //                             }else{
    //                                 $final->gender=   'FEMALE';
    //                             }
    //                         }
    //                         //for leased  plot

    //                         $final->guardian_name   =   $row[3]??NULL;
    //                         $final->aadhaar=   $row[4]??NULL;
    //                         $final->mobile   =   $row[5]??NULL;
    //                         $final->mobile_verified=   1;

                            
    //                         // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
    //                         $final->no_of_plots=  count(explode(',',$row[10]))??NULL;
    //                         $final->area_in_acers   = '0.0';//  0.3305785123 * floatval($row[12]) ??0; 

    //                         // $final->leased_area     =   0.3305785123 * floatval($row[12])??0;

    //                         $final->total_plot_area=  '0.0';//  0.3305785123 * (floatval($row[12])??0)??NULL; 
    //                         //for leased  plot
    //                         $final->plot_no   =     $plot_no_serial;              

    //                         $final->farmer_uniqueId   =  $unique; 
    //                         $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
    //                         // $final->leased_land_plot =   $row[14]??NULL; 
    //                         $final->land_ownership=  'Leased';  
    //                         $final->date_survey=  '2022-01-01';  
    //                         $final->time_survey=  '12:20:47';  

    //                         $final->save();
    //                         //end end end end end nend

    //                         //new plot
    //                         $FarmerPlot = new FarmerPlot;
    //                         $FarmerPlot->farmer_id          =   $final->id;
    //                         $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
    //                         $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

    //                         $FarmerPlot->plot_no           =   $final->plot_no;
    //                         $FarmerPlot->area_in_acers      =   $final->area_in_acers;

    //                         //for leased  plot

    //                         $FarmerPlot->land_ownership     =   'Leased';

    //                         $FarmerPlot->final_status       = 'Approved';
    //                         $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
    //                         $FarmerPlot->finalappr_userid =  '1';  
    //                         $FarmerPlot->status             = 'Approved';

    //                         $FarmerPlot->appr_timestamp=  Carbon::now();  
    //                         $FarmerPlot->aprv_recj_userid=  '1'; 

    //                         // $FarmerPlot->daag_number=  $row[9]??NULL;                               
    //                         // $FarmerPlot->patta_number=  $plots_patt; 
                            
    //                         $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[12]) ??0; 


    //                         $FarmerPlot->area_in_other=  '0.0';// $row[12]??"0.0";  
    //                         $FarmerPlot->area_in_other_unit= "Bigha";

    //                         $FarmerPlot->area_acre_awd= 0.3305785123 * floatval($row[12]) ??0;//'0.0';// 
    //                         $FarmerPlot->area_other_awd= $row[12];// $row[12]??"0.0";  
    //                         $FarmerPlot->area_other_awd_unit= "Bigha";
    //                         $FarmerPlot->save();
    //                     } 

                  
    //                 $unique++;  
    //                         // dd('stop');
    //     }//first forechend

    // }



    //This function used for the farmer onboarding of Assam  
    // Condition 1 : when uploading daag then open the daag forach loop and comment the patta foreach loop
    //Condition 2 : when uploading patta then open the patta forach loop and comment the patta forach 
    // Condition 3 : When uploading patta and daag both then open the both forach and in 3rd forach open the condition for patta and daag.
    public function newupload_survey_assam(Request $request){
        //with daag and patta for own land and leased
        $data = Excel::toCollection(null,$request->file);
        // Loop through the data and store it in the database
        $unique = 102270;
        $plot_no_sr = 1;

        // dd($data[0]);
        foreach ($data[0] as $row) {
            // dd($row);
            $district = DB::table('districts')->Where('district',$row[8])->select('id','district')->first();
            $taluka = DB::table('talukas')->where('taluka', 'like', '%'.$row[7])->select('id','taluka')->first();
            $panchayat=NULL;
            if($district && $taluka ){
                $panchayat = DB::table('panchayats')->where('state_id',29)->where('id',$district->id)->orWhere('taluka_id',$taluka->id)->select('id','panchayat')->first();
            }
            $village = DB::table('villages')->where('village',$row[6])->select('id','village')->first();

            $plot_no_serial = 1;
            foreach(explode('/',$row[9]) as $plots){
               
                    // for daag
                    if($row[9]){ 
                        // 21,34,35
                        // dd($row[9]);
                        $final = new FinalFarmer;
                        $final->surveyor_id  = 1;
                        $final->surveyor_name  = 'ADMIN';
                        $final->surveyor_email  = 'superadmin@crop.com';
                        $final->surveyor_mobile  = '1245657895';
                        $final->status_onboarding=  'Pending';  
                        $final->final_status_onboarding=  'Pending';  
                        $final->onboarding_form=  1;  
                        $final->final_status=  'Pending';  
                        $final->L2_aprv_timestamp   =  Carbon::now();  
                        $final->L1_appr_timestamp   =  Carbon::now();  
                        $final->mobile_access   =  'Own Number';
                        $final->mobile_reln_owner   =  'NA';   
                        $final->country_id=  '101';  
                        $final->country=  'India';  
                        $final->state_id=  '29';  
                        $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
                        $final->check_carbon_credit=  '1';  
                        $final->affidavit_tnc=  '0';                      
                        $final->L2_appr_userid      =  '1';  
                        $final->L1_aprv_recj_userid =  '1';  
                        $final->district_id     =   $district->id??NULL;
                        $final->district        =   $district->district??$row[8];
                        $final->taluka_id       =   $taluka->id??NULL;
                        $final->taluka          =   $taluka->taluka??$row[6];
                        if($panchayat){
                            $final->panchayat_id    =   $panchayat->id??NULL;
                            $final->panchayat       =   $panchayat->panchayat??NULl;  
                        }else{
                            $final->panchayat_id    =   NULL;
                            $final->panchayat       =   NULl;  
                        }
                        $final->village_id      =   $village->id ??NULL;
                        $final->village         =   $village->village ??$row[7];

                        $final->farmer_survey_id=   $row[0]??NULL;
                        $final->farmer_name     =   $row[1]??NULL;
                        if($row[2]){
                            if($row[2] == 'Male'){
                                $final->gender=   'MALE';
                            }else{
                                $final->gender=   'FEMALE';
                            }
                        }
                        $final->guardian_name   =   $row[3]??NULL;
                        $final->aadhaar=   str_replace(' ', '', $row[4])??NULL;
                        $final->mobile  =   $row[5]??NULL;
                        $final->mobile_verified=   1;
                        //for owner plot
                        // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
                        $final->no_of_plots=   count(explode('/',$row[9]))??NULL;//1??NULL; //assam
                        $final->area_in_acers   =  0.3305785123 * floatval($row[11]) ??0;  //'0.0';
                        $final->available_area =   $final->area_in_acers;
                        $final->leased_area     =   '0.0';// 0.3305785123 * floatval($row[12])??0;
                        $final->own_area_in_acres = 0.3305785123 * floatval($row[11]) ??0;
                        $final->lease_area_in_acres = 0.3305785123 * floatval($row[12]) ??0;
                        $final->total_plot_area=  '0.0';// 0.3305785123 * (floatval($row[11])??0 + floatval($row[12])??0)??NULL; 
                        $final->plot_no   =   $plot_no_serial;
                        //   dd( $row[11], $row[12], $final);
                        $final->farmer_uniqueId   =  $unique; 
                        $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
                        // $final->leased_land_plot =   $row[14]??NULL; 
                        $final->land_ownership=  'Own';
                        
                        $final->date_survey=  '2023-12-19';  
                        $final->time_survey=  '15:50:47';  
                        $final->save();
                        //end end end end end nend
                        //for owner plot

                        //new plot
                        $FarmerPlot = new FarmerPlot;
                        $FarmerPlot->farmer_id          =   $final->id;
                        $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
                        $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

                        $FarmerPlot->plot_no           =   $final->plot_no;
                        $FarmerPlot->area_in_acers      =   $final->area_in_acers;
                        $FarmerPlot->land_ownership     =   $final->land_ownership;

                        $FarmerPlot->final_status       = 'Pending';
                        $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
                        $FarmerPlot->finalappr_userid =  '1';  
                        $FarmerPlot->status             = 'Pending';
                        //for owner plot
                        $FarmerPlot->appr_timestamp=  Carbon::now();  
                        $FarmerPlot->aprv_recj_userid=  '1'; 
                        $FarmerPlot->daag_number=  $plots??NULL;                               
                        $FarmerPlot->patta_number= NULL;  
                        $FarmerPlot->area_in_acers= 0.3305785123 * floatval($row[11]) ??0 ;  //'0.0' ; 
                        $FarmerPlot->area_in_other=  '0.0';// $row[11]??"0.0";  
                        $FarmerPlot->area_in_other_unit= "Bigha";
                        // $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[11]) ??0; //'0.0';// 0.3305785123 * floatval($row[11]) ??0;

                        if($row[11])
                        {
                            if(count(explode('/',$row[9])) > 1){
                                $FarmerPlot->area_acre_awd=  '0.0';
                                $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";
                                 
                            }else{
                                $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[11]) ??0;
                                $FarmerPlot->area_other_awd= $row[11]??'0.0';// $row[11]??"0.0";  
                            }

                        }else{
                            $FarmerPlot->area_acre_awd=  '0.0';
                            $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";  
                        }

                        // $FarmerPlot->area_other_awd= $row[11]??'0.0';// $row[11]??"0.0";  

                        $FarmerPlot->area_other_awd_unit = "Bigha";                        
                        $FarmerPlot->save();
                    }//if own land has data

                    $plot_no_serial++;
                }//foreach end for daag


                foreach(explode('/',$row[10]) as $plots_patt){
                    if($row[10]){
                        //for leased  plot
                        $final = new FinalFarmer;
                        $final->surveyor_id  = 1;
                        $final->surveyor_name  = 'ADMIN';
                        $final->surveyor_email  = 'superadmin@crop.com';
                        $final->surveyor_mobile  = '1245657895';

                        $final->status_onboarding=  'Pending';  
                        $final->final_status_onboarding=  'Pending';  
                        $final->onboarding_form=  1;  
                        $final->final_status=  'Pending';  
                        $final->L2_aprv_timestamp   =  Carbon::now();  
                        $final->L1_appr_timestamp   =  Carbon::now();  
                        $final->mobile_access   =  'Own Number';
                        $final->mobile_reln_owner   =  'NA';   
                        $final->country_id=  '101';  
                        $final->country=  'India';  
                        $final->state_id=  '29';  
                        $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
                        $final->check_carbon_credit=  '1';  
                        $final->affidavit_tnc=  '0';                      
                        $final->L2_appr_userid      =  '1';  
                        $final->L1_aprv_recj_userid =  '1';  
                        //for leased  plot


                        $final->district_id     =   $district->id??NULL;
                        $final->district        =   $district->district??$row[8];
                        $final->taluka_id       =   $taluka->id??NULL;
                        $final->taluka          =   $taluka->taluka??$row[6];
                        if($panchayat){
                            $final->panchayat_id    =   $panchayat->id??NULL;
                            $final->panchayat       =   $panchayat->panchayat??NULl;  
                        }
                        $final->village_id      =   $village->id ??NULL;
                        $final->village         =   $village->village ??$row[7];
                    
                        $final->farmer_survey_id=   $row[0]??NULL;
                        $final->farmer_name     =   $row[1]??NULL;
                        if($row[2]){
                            if($row[2] == 'Male'){
                                $final->gender=   'MALE';
                            }else{
                                $final->gender=   'FEMALE';
                            }
                        }
                        //for leased  plot

                        $final->guardian_name   =   $row[3]??NULL;
                        $final->aadhaar=   str_replace(' ', '', $row[4])??NULL;
                        $final->mobile   =   $row[5]??NULL;
                        $final->mobile_verified=   1;

                        
                        // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
                        $final->no_of_plots=  count(explode('/',$row[10]))??NULL;
                        $final->area_in_acers   = 0.3305785123 * floatval($row[11]) ??0; //'0.0';
                        $final->available_area = $final->area_in_acers;
                        $final->leased_area     =   '0.0';// 0.3305785123 * floatval($row[12])??0;
                        $final->own_area_in_acres = 0.3305785123 * floatval($row[11]) ??0;
                        $final->lease_area_in_acres = 0.3305785123 * floatval($row[12]) ??0;
                        $final->leased_area     =   0.3305785123 * floatval($row[12])??0;
                        $final->total_plot_area=  '0.0';//  0.3305785123 * (floatval($row[12])??0)??NULL; 
                        //for leased  plot
                        $final->plot_no   =     $plot_no_serial;              

                        $final->farmer_uniqueId   =  $unique; 
                        $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
                        // $final->leased_land_plot =   $row[14]??NULL; 
                        $final->land_ownership=  'Own';  
                        $final->date_survey=  '2023-12-19';  
                        $final->time_survey=  '15:50:47';  
                        $final->save();
                        //end end end end end nend
                        //new plot
                        $FarmerPlot = new FarmerPlot;
                        $FarmerPlot->farmer_id          =   $final->id;
                        $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
                        $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

                        $FarmerPlot->plot_no           =   $final->plot_no;
                        $FarmerPlot->area_in_acers      =   $final->area_in_acers;
                        //for leased  plot
                        $FarmerPlot->land_ownership     =   'OWN';
                        $FarmerPlot->final_status       = 'Pending';
                        $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
                        $FarmerPlot->finalappr_userid =  '1';  
                        $FarmerPlot->status             = 'Pending';

                        $FarmerPlot->appr_timestamp=  Carbon::now();  
                        $FarmerPlot->aprv_recj_userid=  '1'; 

                        // $FarmerPlot->daag_number=  $row[9]??NULL;                               
                        $FarmerPlot->patta_number=  $plots_patt; 
                        
                        $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[12]) ??0; 


                        $FarmerPlot->area_in_other=  '0.0';// $row[12]??"0.0";  
                        $FarmerPlot->area_in_other_unit= "Bigha";

                        // $FarmerPlot->area_acre_awd= 0.3305785123 * floatval($row[11]) ??0;//'0.0';// 
                        // $FarmerPlot->area_other_awd= $row[11]??"0.0";// $row[12]??"0.0";  

                        if($row[11]){
                            if(count(explode('/',$row[10])) > 1){
                                $FarmerPlot->area_acre_awd=  '0.0';
                                $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0"; 
                            }else{
                                $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[11]) ??0;
                                $FarmerPlot->area_other_awd= $row[11]??'0.0';// $row[11]??"0.0";  
                            }
                        }else{
                            $FarmerPlot->area_acre_awd=  '0.0';
                            $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";  
                        }
                        $FarmerPlot->area_other_awd_unit= "Bigha";

                        $FarmerPlot->save();
                    } 

                    $plot_no_serial ++;
                }//foreacgh end patta
                
                //for leased record
                if($row[12]){
                            $final = new FinalFarmer;
                            $final->surveyor_id  = 1;
                            $final->surveyor_name  = 'ADMIN';
                            $final->surveyor_email  = 'superadmin@crop.com';
                            $final->surveyor_mobile  = '1245657895';

                            $final->status_onboarding=  'Pending';  
                            $final->final_status_onboarding=  'Pending';  
                            $final->onboarding_form=  1;  
                            $final->final_status=  'Pending';  
                            $final->L2_aprv_timestamp   =  Carbon::now();  
                            $final->L1_appr_timestamp   =  Carbon::now();  
                            $final->mobile_access   =  'Own Number';
                            $final->mobile_reln_owner   =  'NA';   
                            $final->country_id=  '101';  
                            $final->country=  'India';  
                            $final->state_id=  '29';  
                            $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
                            $final->check_carbon_credit=  '1';  
                            $final->affidavit_tnc=  '0';                      
                            $final->L2_appr_userid      =  '1';  
                            $final->L1_aprv_recj_userid =  '1';  
                            //for leased  plot
                            $final->district_id     =   $district->id??NULL;
                            $final->district        =   $district->district??$row[8];
                            $final->taluka_id       =   $taluka->id??NULL;
                            $final->taluka          =   $taluka->taluka??$row[6];
                            if($panchayat){
                                $final->panchayat_id    =   $panchayat->id??NULL;
                                $final->panchayat       =   $panchayat->panchayat??NULl;  
                            }
                            $final->village_id      =   $village->id ??NULL;
                            $final->village         =   $village->village ??$row[7];
                        
                            $final->farmer_survey_id=   $row[0]??NULL;
                            $final->farmer_name     =   $row[1]??NULL;
                            if($row[2]){
                                if($row[2] == 'Male'){
                                    $final->gender=   'MALE';
                                }else{
                                    $final->gender=   'FEMALE';
                                }
                            }
                            //for leased  plot
                            $final->guardian_name   =   $row[3]??NULL;
                            $final->aadhaar=   str_replace(' ', '', $row[4])??NULL;
                            $final->mobile  =   $row[5]??NULL;
                            $final->mobile_verified=   1;

                            
                            // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
                            $final->no_of_plots=  count(explode('/',$row[10]))??NULL;
                            $final->area_in_acers   = '0.0';//  0.3305785123 * floatval($row[12]) ??0; 

                            // $final->leased_area     =   0.3305785123 * floatval($row[12])??0;

                            $final->total_plot_area=  '0.0';//  0.3305785123 * (floatval($row[12])??0)??NULL; 
                            //for leased  plot
                            $final->plot_no   =     $plot_no_serial;              

                            $final->farmer_uniqueId   =  $unique; 
                            $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
                            // $final->leased_land_plot =   $row[14]??NULL; 
                            $final->land_ownership=  'Leased';  
                            $final->date_survey=  '2023-12-20';  
                            $final->time_survey=  '15:50:47';  

                            $final->save();
                            //end end end end end nend

                            //new plot
                            $FarmerPlot = new FarmerPlot;
                            $FarmerPlot->farmer_id          =   $final->id;
                            $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
                            $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

                            $FarmerPlot->plot_no           =   $final->plot_no;
                            $FarmerPlot->area_in_acers      =   $final->area_in_acers;

                            //for leased  plot
                            $FarmerPlot->land_ownership     =   'Leased';

                            $FarmerPlot->final_status       = 'Pending';
                            $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
                            $FarmerPlot->finalappr_userid =  '1';  
                            $FarmerPlot->status             = 'Pending';

                            $FarmerPlot->appr_timestamp=  Carbon::now();  
                            $FarmerPlot->aprv_recj_userid=  '1'; 

                             //uncomment Code when both daag and patta
                            $FarmerPlot->daag_number=  $row[9]??NULL;                               
                            $FarmerPlot->patta_number=  $plots_patt; 
                            
                            $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[12]) ??0; 
                            $FarmerPlot->area_in_other=  '0.0';// $row[12]??"0.0";  
                            $FarmerPlot->area_in_other_unit= "Bigha";
                            // //uncomment Code when both daag and patta
                            $FarmerPlot->area_acre_awd= 0.3305785123 * floatval($row[12]) ??0;//'0.0';// 
                            $FarmerPlot->area_other_awd= $row[12]??'0.0';// $row[12]??"0.0";  
                            $FarmerPlot->area_other_awd_unit= "Bigha";
                            if($row[12]){
                                if(count(explode(',',$row[10])) > 1){  // need to change when patta comming
                                    $FarmerPlot->area_acre_awd=  '0.0';
                                    $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0"; 
                                }else{
                                    $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[12]) ??0;
                                    $FarmerPlot->area_other_awd= $row[12]??'0.0';// $row[11]??"0.0";  
                                }
                            }else{
                                $FarmerPlot->area_acre_awd=  '0.0';
                                $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";  
                            } 
                            //uncomment Code when both daag and patta
                            $FarmerPlot->area_other_awd_unit= "Bigha";
                            $FarmerPlot->save();
                        } 
                    $unique++;  
                            // dd('stop');
        }//first forechend

            return response()->json(['message'=>"done"],200);
    }

    // public function newupload_survey_assam(Request $request){
    //     //with daag and patta for own land and leased
    //     $data = Excel::toCollection(null,$request->file);
    //     // Loop through the data and store it in the database
    //     $unique = 35056;
    //     $plot_no_sr = 1;

    //     // dd($data[0]);
    //     foreach ($data[0] as $row) {
    //         // dd($row);
    //         $district = DB::table('districts')->Where('district',$row[8])->select('id','district')->first();
    //         $taluka = DB::table('talukas')->where('taluka', 'like', '%'.$row[6])->select('id','taluka')->first();
    //         $panchayat=NULL;
    //         if($district && $taluka ){
    //             $panchayat = DB::table('panchayats')->where('state_id',29)->where('id',$district->id)->orWhere('taluka_id',$taluka->id)->select('id','panchayat')->first();
    //         }
    //         $village = DB::table('villages')->where('village',$row[8])->select('id','village')->first();

    //         $plot_no_serial = 1;
    //         foreach(explode(',',$row[9]) as $plots){
    //                 // for daag
    //                 if($row[9]){ 
    //                     // 21,34,35

    //                     $final = new FinalFarmer;
    //                     $final->surveyor_id  = 1;
    //                     $final->surveyor_name  = 'ADMIN';
    //                     $final->surveyor_email  = 'superadmin@crop.com';
    //                     $final->surveyor_mobile  = '1245657895';

    //                     $final->status_onboarding=  'Approved';  
    //                     $final->final_status_onboarding=  'Approved';  
    //                     $final->onboarding_form=  1;  
    //                     $final->final_status=  'Approved';  
    //                     $final->L2_aprv_timestamp   =  Carbon::now();  
    //                     $final->L1_appr_timestamp   =  Carbon::now();  
    //                     $final->mobile_access   =  'Own Number';
    //                     $final->mobile_reln_owner   =  'NA';   
    //                     $final->country_id=  '101';  
    //                     $final->country=  'India';  
    //                     $final->state_id=  '29';  
    //                     $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
    //                     $final->check_carbon_credit=  '1';  
    //                     $final->affidavit_tnc=  '0';                      
    //                     $final->L2_appr_userid      =  '1';  
    //                     $final->L1_aprv_recj_userid =  '1';  


    //                     $final->district_id     =   $district->id??NULL;
    //                     $final->district        =   $district->district??$row[8];
    //                     $final->taluka_id       =   $taluka->id??NULL;
    //                     $final->taluka          =   $taluka->taluka??$row[6];
    //                     if($panchayat){
    //                         $final->panchayat_id    =   $panchayat->id??NULL;
    //                         $final->panchayat       =   $panchayat->panchayat??NULl;  
    //                     }else{
    //                         $final->panchayat_id    =   NULL;
    //                         $final->panchayat       =   NULl;  
    //                     }
    //                     $final->village_id      =   $village->id ??NULL;
    //                     $final->village         =   $village->village ??$row[7];
                    
    //                     $final->farmer_survey_id=   $row[0]??NULL;
    //                     $final->farmer_name     =   $row[1]??NULL;
    //                     if($row[2]){
    //                         if($row[2] == 'Male'){
    //                             $final->gender=   'MALE';
    //                         }else{
    //                             $final->gender=   'FEMALE';
    //                         }
    //                     }
    //                     $final->guardian_name   =   $row[3]??NULL;
    //                     $final->aadhaar=   str_replace(' ', '', $row[4])??NULL;
    //                     $final->mobile  =   $row[5]??NULL;
    //                     $final->mobile_verified=   1;
    //                     //for owner plot

                        
    //                     // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
    //                     $final->no_of_plots=   count(explode(',',$row[9]))??NULL;//1??NULL; //assam
    //                     $final->area_in_acers   =  '0.0';// 0.3305785123 * floatval($row[11]) ??0; 

    //                     $final->leased_area     =   '0.0';// 0.3305785123 * floatval($row[12])??0;

    //                     $final->total_plot_area=  '0.0';// 0.3305785123 * (floatval($row[11])??0 + floatval($row[12])??0)??NULL; 
                        

    //                     $final->plot_no   =   $plot_no_serial;
    //                     //   dd( $row[11], $row[12], $final);

    //                     $final->farmer_uniqueId   =  $unique; 
    //                     $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
    //                     // $final->leased_land_plot =   $row[14]??NULL; 

    //                     $final->land_ownership=  'Own';
                        
    //                     $final->date_survey=  '2023-07-01';  
    //                     $final->time_survey=  '12:20:47';  
    //                     $final->save();
    //                     //end end end end end nend
    //                     //for owner plot

    //                     //new plot
    //                     $FarmerPlot = new FarmerPlot;
    //                     $FarmerPlot->farmer_id          =   $final->id;
    //                     $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
    //                     $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

    //                     $FarmerPlot->plot_no           =   $final->plot_no;
    //                     $FarmerPlot->area_in_acers      =   $final->area_in_acers;


    //                     $FarmerPlot->land_ownership     =   $final->land_ownership;

    //                     $FarmerPlot->final_status       = 'Approved';
    //                     $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
    //                     $FarmerPlot->finalappr_userid =  '1';  
    //                     $FarmerPlot->status             = 'Approved';
    //                     //for owner plot

    //                     $FarmerPlot->appr_timestamp=  Carbon::now();  
    //                     $FarmerPlot->aprv_recj_userid=  '1'; 

    //                     $FarmerPlot->daag_number=  $plots??NULL;                               
    //                     $FarmerPlot->patta_number= NULL;  

                    
                        
    //                     $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[11]) ??0; 
    //                     $FarmerPlot->area_in_other=  '0.0';// $row[11]??"0.0";  
    //                     $FarmerPlot->area_in_other_unit= "Bigha";

                        
    //                     // $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[11]) ??0; //'0.0';// 0.3305785123 * floatval($row[11]) ??0;

    //                     if($row[11]){
    //                         if(count(explode(',',$row[9])) > 1){
    //                             $FarmerPlot->area_acre_awd=  '0.0';
    //                             $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0"; 
    //                         }else{
    //                             $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[11]) ??0;
    //                             $FarmerPlot->area_other_awd= $row[11]??'0.0';// $row[11]??"0.0";  
    //                         }
    //                     }else{
    //                         $FarmerPlot->area_acre_awd=  '0.0';
    //                         $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";  
    //                     }
                        
    //                     // $FarmerPlot->area_other_awd= $row[11]??'0.0';// $row[11]??"0.0";  

    //                     $FarmerPlot->area_other_awd_unit = "Bigha";                        
    //                     $FarmerPlot->save();
    //                 }//if own land has data

    //                 $plot_no_serial++;
    //             }//foreach end for daag

              

    //             foreach(explode(',',$row[10]) as $plots_patt){
    //                 if($row[10]){
    //                     //for leased  plot
    //                     $final = new FinalFarmer;
    //                     $final->surveyor_id  = 1;
    //                     $final->surveyor_name  = 'ADMIN';
    //                     $final->surveyor_email  = 'superadmin@crop.com';
    //                     $final->surveyor_mobile  = '1245657895';

    //                     $final->status_onboarding=  'Approved';  
    //                     $final->final_status_onboarding=  'Approved';  
    //                     $final->onboarding_form=  1;  
    //                     $final->final_status=  'Approved';  
    //                     $final->L2_aprv_timestamp   =  Carbon::now();  
    //                     $final->L1_appr_timestamp   =  Carbon::now();  
    //                     $final->mobile_access   =  'Own Number';
    //                     $final->mobile_reln_owner   =  'NA';   
    //                     $final->country_id=  '101';  
    //                     $final->country=  'India';  
    //                     $final->state_id=  '29';  
    //                     $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
    //                     $final->check_carbon_credit=  '1';  
    //                     $final->affidavit_tnc=  '0';                      
    //                     $final->L2_appr_userid      =  '1';  
    //                     $final->L1_aprv_recj_userid =  '1';  
    //                     //for leased  plot


    //                     $final->district_id     =   $district->id??NULL;
    //                     $final->district        =   $district->district??$row[8];
    //                     $final->taluka_id       =   $taluka->id??NULL;
    //                     $final->taluka          =   $taluka->taluka??$row[6];
    //                     if($panchayat){
    //                         $final->panchayat_id    =   $panchayat->id??NULL;
    //                         $final->panchayat       =   $panchayat->panchayat??NULl;  
    //                     }
    //                     $final->village_id      =   $village->id ??NULL;
    //                     $final->village         =   $village->village ??$row[7];
                    
    //                     $final->farmer_survey_id=   $row[0]??NULL;
    //                     $final->farmer_name     =   $row[1]??NULL;
    //                     if($row[2]){
    //                         if($row[2] == 'Male'){
    //                             $final->gender=   'MALE';
    //                         }else{
    //                             $final->gender=   'FEMALE';
    //                         }
    //                     }
    //                     //for leased  plot

    //                     $final->guardian_name   =   $row[3]??NULL;
    //                     $final->aadhaar=   str_replace(' ', '', $row[4])??NULL;
    //                     $final->mobile   =   $row[5]??NULL;
    //                     $final->mobile_verified=   1;

                        
    //                     // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
    //                     $final->no_of_plots=  count(explode(',',$row[10]))??NULL;
    //                     $final->area_in_acers   = '0.0';//  0.3305785123 * floatval($row[12]) ??0; 

    //                     // $final->leased_area     =   0.3305785123 * floatval($row[12])??0;

    //                     $final->total_plot_area=  '0.0';//  0.3305785123 * (floatval($row[12])??0)??NULL; 
    //                     //for leased  plot
    //                     $final->plot_no   =     $plot_no_serial;              

    //                     $final->farmer_uniqueId   =  $unique; 
    //                     $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
    //                     // $final->leased_land_plot =   $row[14]??NULL; 
    //                     $final->land_ownership=  'Own';  
    //                     $final->date_survey=  '2023-07-01';  
    //                     $final->time_survey=  '12:20:47';  

    //                     $final->save();
    //                     //end end end end end nend

    //                     //new plot
    //                     $FarmerPlot = new FarmerPlot;
    //                     $FarmerPlot->farmer_id          =   $final->id;
    //                     $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
    //                     $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

    //                     $FarmerPlot->plot_no           =   $final->plot_no;
    //                     $FarmerPlot->area_in_acers      =   $final->area_in_acers;

    //                     //for leased  plot

    //                     $FarmerPlot->land_ownership     =   'OWN';

    //                     $FarmerPlot->final_status       = 'Approved';
    //                     $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
    //                     $FarmerPlot->finalappr_userid =  '1';  
    //                     $FarmerPlot->status             = 'Approved';

    //                     $FarmerPlot->appr_timestamp=  Carbon::now();  
    //                     $FarmerPlot->aprv_recj_userid=  '1'; 

    //                     // $FarmerPlot->daag_number=  $row[9]??NULL;                               
    //                     $FarmerPlot->patta_number=  $plots_patt; 
                        
    //                     $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[12]) ??0; 


    //                     $FarmerPlot->area_in_other=  '0.0';// $row[12]??"0.0";  
    //                     $FarmerPlot->area_in_other_unit= "Bigha";

    //                     // $FarmerPlot->area_acre_awd= 0.3305785123 * floatval($row[11]) ??0;//'0.0';// 
    //                     // $FarmerPlot->area_other_awd= $row[11]??"0.0";// $row[12]??"0.0";  

    //                     if($row[11]){
    //                         if(count(explode(',',$row[10])) > 1){
    //                             $FarmerPlot->area_acre_awd=  '0.0';
    //                             $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0"; 
    //                         }else{
    //                             $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[11]) ??0;
    //                             $FarmerPlot->area_other_awd= $row[11]??'0.0';// $row[11]??"0.0";  
    //                         }
    //                     }else{
    //                         $FarmerPlot->area_acre_awd=  '0.0';
    //                         $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";  
    //                     }
    //                     $FarmerPlot->area_other_awd_unit= "Bigha";

    //                     $FarmerPlot->save();
    //                 } 

    //                 $plot_no_serial ++;
    //             }//foreacgh end patta
                

    //             //for leased record
    //             if($row[12]){
    //                         $final = new FinalFarmer;
    //                         $final->surveyor_id  = 1;
    //                         $final->surveyor_name  = 'ADMIN';
    //                         $final->surveyor_email  = 'superadmin@crop.com';
    //                         $final->surveyor_mobile  = '1245657895';

    //                         $final->status_onboarding=  'Approved';  
    //                         $final->final_status_onboarding=  'Approved';  
    //                         $final->onboarding_form=  1;  
    //                         $final->final_status=  'Approved';  
    //                         $final->L2_aprv_timestamp   =  Carbon::now();  
    //                         $final->L1_appr_timestamp   =  Carbon::now();  
    //                         $final->mobile_access   =  'Own Number';
    //                         $final->mobile_reln_owner   =  'NA';   
    //                         $final->country_id=  '101';  
    //                         $final->country=  'India';  
    //                         $final->state_id=  '29';  
    //                         $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
    //                         $final->check_carbon_credit=  '1';  
    //                         $final->affidavit_tnc=  '0';                      
    //                         $final->L2_appr_userid      =  '1';  
    //                         $final->L1_aprv_recj_userid =  '1';  
    //                         //for leased  plot


    //                         $final->district_id     =   $district->id??NULL;
    //                         $final->district        =   $district->district??$row[8];
    //                         $final->taluka_id       =   $taluka->id??NULL;
    //                         $final->taluka          =   $taluka->taluka??$row[6];
    //                         if($panchayat){
    //                             $final->panchayat_id    =   $panchayat->id??NULL;
    //                             $final->panchayat       =   $panchayat->panchayat??NULl;  
    //                         }
    //                         $final->village_id      =   $village->id ??NULL;
    //                         $final->village         =   $village->village ??$row[7];
                        
    //                         $final->farmer_survey_id=   $row[0]??NULL;
    //                         $final->farmer_name     =   $row[1]??NULL;
    //                         if($row[2]){
    //                             if($row[2] == 'Male'){
    //                                 $final->gender=   'MALE';
    //                             }else{
    //                                 $final->gender=   'FEMALE';
    //                             }
    //                         }
    //                         //for leased  plot

    //                         $final->guardian_name   =   $row[3]??NULL;
    //                         $final->aadhaar=   str_replace(' ', '', $row[4])??NULL;
    //                         $final->mobile  =   $row[5]??NULL;
    //                         $final->mobile_verified=   1;

                            
    //                         // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
    //                         $final->no_of_plots=  count(explode(',',$row[10]))??NULL;
    //                         $final->area_in_acers   = '0.0';//  0.3305785123 * floatval($row[12]) ??0; 

    //                         // $final->leased_area     =   0.3305785123 * floatval($row[12])??0;

    //                         $final->total_plot_area=  '0.0';//  0.3305785123 * (floatval($row[12])??0)??NULL; 
    //                         //for leased  plot
    //                         $final->plot_no   =     $plot_no_serial;              

    //                         $final->farmer_uniqueId   =  $unique; 
    //                         $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
    //                         // $final->leased_land_plot =   $row[14]??NULL; 
    //                         $final->land_ownership=  'Leased';  
    //                         $final->date_survey=  '2023-07-01';  
    //                         $final->time_survey=  '12:20:47';  

    //                         $final->save();
    //                         //end end end end end nend

    //                         //new plot
    //                         $FarmerPlot = new FarmerPlot;
    //                         $FarmerPlot->farmer_id          =   $final->id;
    //                         $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
    //                         $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

    //                         $FarmerPlot->plot_no           =   $final->plot_no;
    //                         $FarmerPlot->area_in_acers      =   $final->area_in_acers;

    //                         //for leased  plot

    //                         $FarmerPlot->land_ownership     =   'Leased';

    //                         $FarmerPlot->final_status       = 'Approved';
    //                         $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
    //                         $FarmerPlot->finalappr_userid =  '1';  
    //                         $FarmerPlot->status             = 'Approved';

    //                         $FarmerPlot->appr_timestamp=  Carbon::now();  
    //                         $FarmerPlot->aprv_recj_userid=  '1'; 

    //                         // $FarmerPlot->daag_number=  $row[9]??NULL;                               
    //                         // $FarmerPlot->patta_number=  $plots_patt; 
                            
    //                         $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[12]) ??0; 
    //                         $FarmerPlot->area_in_other=  '0.0';// $row[12]??"0.0";  
    //                         $FarmerPlot->area_in_other_unit= "Bigha";

    //                         $FarmerPlot->area_acre_awd= 0.3305785123 * floatval($row[12]) ??0;//'0.0';// 
    //                         $FarmerPlot->area_other_awd= $row[12]??'0.0';// $row[12]??"0.0";  
    //                         $FarmerPlot->area_other_awd_unit= "Bigha";
    //                         // if($row[12]){
    //                         //     if(count(explode(',',$row[10])) > 1){  // need to change when patta comming
    //                         //         $FarmerPlot->area_acre_awd=  '0.0';
    //                         //         $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0"; 
    //                         //     }else{
    //                         //         $FarmerPlot->area_acre_awd=  0.3305785123 * floatval($row[12]) ??0;
    //                         //         $FarmerPlot->area_other_awd= $row[12]??'0.0';// $row[11]??"0.0";  
    //                         //     }
    //                         // }else{
    //                         //     $FarmerPlot->area_acre_awd=  '0.0';
    //                         //     $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";  

    //                         // }
    //                         $FarmerPlot->area_other_awd_unit= "Bigha";
    //                         $FarmerPlot->save();
    //                     } 

                  
    //                 $unique++;  
    //                         // dd('stop');
    //     }//first forechend

    //         return response()->json('done');
    // }



    public function upload_survey_assam(Request $request){
        // dd('stop');
        $data = Excel::toCollection(null,$request->file);
        // Loop through the data and store it in the database
        $unique = 28226;
        $plot_no_sr = 1;
        foreach ($data[0] as $row) {
          
            // if($row[1]){

                // if($row[10]){
                //     dd('paath');
                // }elseif($row[9]){
                //     dd('cc has daag ');
                // }
   
                $district = DB::table('districts')->Where('district',$row[8])->select('id','district')->first();
                $taluka = DB::table('talukas')->where('taluka', 'like', '%'.$row[6])->select('id','taluka')->first();
                $panchayat=NULL;
                if($district && $taluka ){
                    $panchayat = DB::table('panchayats')->where('state_id',29)->where('id',$district->id)->orWhere('taluka_id',$taluka->id)->select('id','panchayat')->first();
                }
                $village = DB::table('villages')->where('village',$row[8])->select('id','village')->first();
                // $unique = $unique + 1;
                // $plot_count = explode(',',$row[9]);
            // dd($district,  $taluka,  $panchayat, $village );

            // dd($row);
            $plot_no_serial = 1;
                //for owner plot
                foreach(explode(',',$row[9]) as $plots){

                
                if($row[9]){ 
                        $final = new FinalFarmer;
                        $final->surveyor_id  = 1;
                        $final->surveyor_name  = 'ADMIN';
                        $final->surveyor_email  = 'superadmin@crop.com';
                        $final->surveyor_mobile  = '1245657895';

                        $final->status_onboarding=  'Approved';  
                        $final->final_status_onboarding=  'Approved';  
                        $final->onboarding_form=  1;  
                        $final->final_status=  'Approved';  
                        $final->L2_aprv_timestamp   =  Carbon::now();  
                        $final->L1_appr_timestamp   =  Carbon::now();  
                        $final->mobile_access   =  'Own Number';
                        $final->mobile_reln_owner   =  'NA';   
                        $final->country_id=  '101';  
                        $final->country=  'India';  
                        $final->state_id=  '29';  
                        $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
                        $final->check_carbon_credit=  '1';  
                        $final->affidavit_tnc=  '0';                      
                        $final->L2_appr_userid      =  '1';  
                        $final->L1_aprv_recj_userid =  '1';  


                        $final->district_id     =   $district->id??NULL;
                        $final->district        =   $district->district??$row[8];
                        $final->taluka_id       =   $taluka->id??NULL;
                        $final->taluka          =   $taluka->taluka??$row[6];
                        if($panchayat){
                            $final->panchayat_id    =   $panchayat->id??NULL;
                            $final->panchayat       =   $panchayat->panchayat??NULl;  
                        }else{
                            $final->panchayat_id    =   NULL;
                            $final->panchayat       =   NULl;  
                        }
                        $final->village_id      =   $village->id ??NULL;
                        $final->village         =   $village->village ??$row[7];
                    
                        $final->farmer_survey_id=   $row[0]??NULL;
                        $final->farmer_name     =   $row[1]??NULL;
                        if($row[2]){
                            if($row[2] == 'M'){
                                $final->gender=   'MALE';
                            }else{
                                $final->gender=   'FEMALE';
                            }
                        }
                        $final->guardian_name   =   $row[3]??NULL;
                        $final->aadhaar=   $row[4]??NULL;
                        $final->mobile  =   $row[5]??NULL;
                        $final->mobile_verified=   1;
                        //for owner plot

                        
                        // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
                        $final->no_of_plots=   count(explode(',',$row[9]))??NULL;//1??NULL; //assam
                        $final->area_in_acers   =  '0.0';// 0.3305785123 * floatval($row[11]) ??0; 

                        $final->leased_area     =   '0.0';// 0.3305785123 * floatval($row[12])??0;

                        $final->total_plot_area=  '0.0';// 0.3305785123 * (floatval($row[11])??0 + floatval($row[12])??0)??NULL; 
                        

                        $final->plot_no   =   $plot_no_serial;
                        //   dd( $row[11], $row[12], $final);

                        $final->farmer_uniqueId   =  $unique; 
                        $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
                        // $final->leased_land_plot =   $row[14]??NULL; 

                        $final->land_ownership=  'Own';
                        
                        $final->date_survey=  '2022-01-01';  
                        $final->time_survey=  '12:20:47';  
                        

                        $final->save();
                        //end end end end end nend
                        //for owner plot

                        //new plot
                        $FarmerPlot = new FarmerPlot;
                        $FarmerPlot->farmer_id          =   $final->id;
                        $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
                        $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

                        $FarmerPlot->plot_no           =   $final->plot_no;
                        $FarmerPlot->area_in_acers      =   $final->area_in_acers;


                        $FarmerPlot->land_ownership     =   $final->land_ownership;

                        $FarmerPlot->final_status       = 'Approved';
                        $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
                        $FarmerPlot->finalappr_userid =  '1';  
                        $FarmerPlot->status             = 'Approved';
                        //for owner plot

                        $FarmerPlot->appr_timestamp=  Carbon::now();  
                        $FarmerPlot->aprv_recj_userid=  '1'; 

                        $FarmerPlot->daag_number=  $plots??NULL;                               
                        $FarmerPlot->patta_number= NULL;  

                       
                        
                        $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[11]) ??0; 
                        $FarmerPlot->area_in_other=  '0.0';// $row[11]??"0.0";  
                        $FarmerPlot->area_in_other_unit= "Bigha";

                        
                        $FarmerPlot->area_acre_awd=  '0.0';// 0.3305785123 * floatval($row[11]) ??0; 
                        $FarmerPlot->area_other_awd= '0.0';// $row[11]??"0.0";  
                        $FarmerPlot->area_other_awd_unit= "Bigha";
                        
                        $FarmerPlot->save();
                    }//if own land has data

                    $plot_no_serial++;
                }//foreach end for daag
                // 
                
                foreach(explode(',',$row[10]) as $plots_patt){
                    if($row[10]){
                    // if($row[12]){
                        //for leased  plot
                        $final = new FinalFarmer;
                        $final->surveyor_id  = 1;
                        $final->surveyor_name  = 'ADMIN';
                        $final->surveyor_email  = 'superadmin@crop.com';
                        $final->surveyor_mobile  = '1245657895';

                        $final->status_onboarding=  'Approved';  
                        $final->final_status_onboarding=  'Approved';  
                        $final->onboarding_form=  1;  
                        $final->final_status=  'Approved';  
                        $final->L2_aprv_timestamp   =  Carbon::now();  
                        $final->L1_appr_timestamp   =  Carbon::now();  
                        $final->mobile_access   =  'Own Number';
                        $final->mobile_reln_owner   =  'NA';   
                        $final->country_id=  '101';  
                        $final->country=  'India';  
                        $final->state_id=  '29';  
                        $final->state=  'Assam';  //Assam 29, Telangana 36, West Bengal 37
                        $final->check_carbon_credit=  '1';  
                        $final->affidavit_tnc=  '0';                      
                        $final->L2_appr_userid      =  '1';  
                        $final->L1_aprv_recj_userid =  '1';  
                        //for leased  plot


                        $final->district_id     =   $district->id??NULL;
                        $final->district        =   $district->district??$row[8];
                        $final->taluka_id       =   $taluka->id??NULL;
                        $final->taluka          =   $taluka->taluka??$row[6];
                        if($panchayat){
                            $final->panchayat_id    =   $panchayat->id??NULL;
                            $final->panchayat       =   $panchayat->panchayat??NULl;  
                        }
                        $final->village_id      =   $village->id ??NULL;
                        $final->village         =   $village->village ??$row[7];
                    
                        $final->farmer_survey_id=   $row[0]??NULL;
                        $final->farmer_name     =   $row[1]??NULL;
                        if($row[2]){
                            if($row[2] == 'M'){
                                $final->gender=   'MALE';
                            }else{
                                $final->gender=   'FEMALE';
                            }
                        }
                        //for leased  plot

                        $final->guardian_name   =   $row[3]??NULL;
                        $final->aadhaar=   $row[4]??NULL;
                        $final->mobile  =   $row[5]??NULL;
                        $final->mobile_verified=   1;

                        
                        // $final->no_of_plots=   count(explode(',',$row[9]))??NULL;
                        $final->no_of_plots=  count(explode(',',$row[10]))??NULL;
                        $final->area_in_acers   = '0.0';//  0.3305785123 * floatval($row[12]) ??0; 

                        // $final->leased_area     =   0.3305785123 * floatval($row[12])??0;

                        $final->total_plot_area=  '0.0';//  0.3305785123 * (floatval($row[12])??0)??NULL; 
                        //for leased  plot
                        $final->plot_no   =     $plot_no_serial;              

                        $final->farmer_uniqueId   =  $unique; 
                        $final->farmer_plot_uniqueid   =  $unique.'P'. $plot_no_serial;//$plot_no_sr;  
                        // $final->leased_land_plot =   $row[14]??NULL; 
                        $final->land_ownership=  'Own';  
                        $final->date_survey=  '2022-01-01';  
                        $final->time_survey=  '12:20:47';  

                        $final->save();
                        //end end end end end nend

                        //new plot
                        $FarmerPlot = new FarmerPlot;
                        $FarmerPlot->farmer_id          =   $final->id;
                        $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
                        $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

                        $FarmerPlot->plot_no           =   $final->plot_no;
                        $FarmerPlot->area_in_acers      =   $final->area_in_acers;

                        //for leased  plot

                        $FarmerPlot->land_ownership     =   'Leased';

                        $FarmerPlot->final_status       = 'Approved';
                        $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
                        $FarmerPlot->finalappr_userid =  '1';  
                        $FarmerPlot->status             = 'Approved';

                        $FarmerPlot->appr_timestamp=  Carbon::now();  
                        $FarmerPlot->aprv_recj_userid=  '1'; 

                        // $FarmerPlot->daag_number=  $row[9]??NULL;                               
                        $FarmerPlot->patta_number=  $plots_patt; 
                        
                        $FarmerPlot->area_in_acers=  '0.0';// 0.3305785123 * floatval($row[12]) ??0; 


                        $FarmerPlot->area_in_other=  '0.0';// $row[12]??"0.0";  
                        $FarmerPlot->area_in_other_unit= "Bigha";

                        $FarmerPlot->area_acre_awd= '0.0';// 0.3305785123 * floatval($row[12]) ??0;
                        $FarmerPlot->area_other_awd= '0.0';// $row[12]??"0.0";  
                        $FarmerPlot->area_other_awd_unit= "Bigha";

                        $FarmerPlot->save();
                    } 

                    $plot_no_serial ++;
                }//foreacgh end patta
                //
                
            //  }//secondforeach end

                    $unique++;  
                     
                    // dd('stop');
        }//foreach end

                // 
            // }
          

            
        return response()->json('done');
    }

    

    public function delete_assam(Request $request)
{
    // Step 1: Retrieve the data
    $plotDetails = DB::table('final_farmers')
        ->join('farmer_plot_detail', 'final_farmers.farmer_plot_uniqueid', '=', 'farmer_plot_detail.farmer_plot_uniqueid')
        ->where('final_farmers.state_id', 29)
        ->select('final_farmers.farmer_uniqueId','final_farmers.farmer_plot_uniqueid','final_farmers.plot_no','final_farmers.state_id')
        ->get();

        foreach( $plotDetails as $item){
            // dd($item);
                 DB::table('farmer_plot_detail')->where('farmer_plot_uniqueid',$item->farmer_uniqueId)->delete();
                        // ->where('farmer_plot_uniqueid', function ($query) {
                        //     $query->from('final_farmers')
                        //         ->select('farmer_plot_uniqueid')
                        //         ->where('state_id', 29);
                        // })
                        // ->delete();
                        // dd('ccc');

        }

        return response()->json('done');

    // // Step 2: Delete the data
    // DB::beginTransaction();

    // try {
    //     // Delete from "farmer_plot_detail" table
    //     DB::table('farmer_plot_detail')
    //         ->where('farmer_plot_uniqueid', function ($query) {
    //             $query->from('final_farmers')
    //                 ->select('farmer_plot_uniqueid')
    //                 ->where('state_id', 29);
    //         })
    //         ->delete();

    //     // Delete from "final_farmers" table
    //     DB::table('final_farmers')
    //         ->where('state_id', 29)
    //         ->delete();

    //     // If the deletion was successful, commit the transaction
    //     DB::commit();

    //     // Return the deleted data (optional)
    //     return $plotDetails;
    // } catch (\Exception $e) {
    //     // If an exception occurred during the deletion, rollback the transaction
    //     DB::rollback();

    //     dd($e);
    //     // Handle the exception (optional)
    //     // ...

    //     // Return an error response (optional)
    //     return response()->json(['message' => 'Error occurred while deleting data'], 500);
    // }
}


    public function check_swappingassam(Request $request){

        $plotDetails = DB::table('farmer_plot_detail')
        ->join('final_farmers', 'farmer_plot_detail.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
        ->where('final_farmers.state_id', 29)
        // ->where('final_farmers.state_id', 37)//west bengal
        // ->where('final_farmers.farmer_survey_id', 'MK/KAL/BAR/056')//assam
        // ->where([['final_farmers.state_id',  37],
        //         ['farmer_plot_detail.farmer_uniqueId',  13923]])
        ->select('farmer_plot_detail.*')
        ->get();

        dd( $plotDetails );


        $data = Excel::toCollection(null,$request->file);
        
        foreach ($data[0] as $row) {
            // $plotDetails = DB::table('final_farmers')->where('farmer_survey_id', $row[7])->select('farmer_survey_id','farmer_uniqueId','id')
            //                     ->first();

            // dd($row[11]);
            $value_inacreas = 0.3305785123 * floatval($row[11]);
            $plotDetails = DB::table('farmer_plot_detail')
            ->join('final_farmers', 'farmer_plot_detail.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
            ->where('final_farmers.state_id', 29)
            ->where('final_farmers.farmer_survey_id', $row[0])
            // ->where('farmer_plot_detail.area_acre_awd',  '!=', '0.0')
            // ->where('farmer_plot_detail.area_other_awd',  '!=', '0.0')
            // ->where('farmer_plot_detail.area_acre_awd', $row[11])
            ->where('farmer_plot_detail.area_other_awd', $value_inacreas)  
            // ->where([['farmer_plot_detail.area_acre_awd', $row[11]] , ['farmer_plot_detail.area_acre_awd', '!=', '0.0'] , ['farmer_plot_detail.area_other_awd', '!=', '0.0'] ])
            ->select('farmer_plot_detail.farmer_uniqueId','farmer_plot_detail.id','farmer_plot_detail.area_acre_awd','farmer_plot_detail.area_other_awd')
            ->first();

            // $plotDetails = DB::table('farmer_plot_detail')->where('area_acre_awd', $row[11])->select('farmer_uniqueId','id','area_acre_awd','area_other_awd')
            // ->first();


                                // dd( $plotDetails);
            if($plotDetails){
                $values = array('area_acre_awd' => $plotDetails->area_acre_awd, 'famerplot_id'=> $plotDetails->id,'state_id' => 'ASSAm');
                    DB::table('test')->insert($values);
            }
            // dd($plotDetails);
        }

        return response()->json('done');

    }

    
    public function check_farmersurveyid(Request $request){
        // $plotDetails = DB::table('final_farmers')
        //         ->select('farmer_survey_id','farmer_uniqueId')
        //         ->get();

        //         foreach($plotDetails as $data){
        //             dd($data);
        //         }

        //         return response()->json('done');
        //         dd($plotDetails);

        $data = Excel::toCollection(null,$request->file);
        
        foreach ($data[7] as $row) {
            $plotDetails = DB::table('final_farmers')->where('farmer_survey_id', $row[7])->select('farmer_survey_id','farmer_uniqueId','id')
                                ->first();

                                dd( $plotDetails);
            if(!$plotDetails){
                $values = array('farmer_survey_id' => $row[7],'state_id' => 'WEST BENGAL');
                    DB::table('test')->insert($values);
            }
            // dd($plotDetails);
        }

        return response()->json('done');



    }


//     M1$^bpti1$YY
//     User: cropintellix_location
// Database: cropintellix_location

    // UPDATE final_farmers SET aadhaar = REPLACE(aadhaar, ' ', '') ;

    public function update_col_westbengal(){

        $plotDetails = DB::table('farmer_plot_detail')
                        ->join('final_farmers', 'farmer_plot_detail.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                        ->where('final_farmers.state_id', 37)//west bengal
                        // ->where('final_farmers.farmer_survey_id', 'MK/KAL/BAR/056')//assam
                        // ->where([['final_farmers.state_id',  37],
                        //         ['farmer_plot_detail.farmer_uniqueId',  13923]])
                        ->select('farmer_plot_detail.*')
                        ->get();

                        
            // dd($plotDetails);
            // MK/KAL/BAR/056
             // for daag number only
        foreach($plotDetails as $item){ 
            if($item->khatian_number){
                $khatian_number = explode(',',$item->khatian_number);
                if(count($khatian_number) > 1){
                    //forloop
                    for ($x = 0; $x < count($khatian_number); $x++) {
                        $plot_no = $x+1;
                            FarmerPlot::where([['farmer_plot_uniqueid', $item->farmer_plot_uniqueid], ['plot_no', $plot_no]])->update([
                                'khatian_number' => $khatian_number[$x],
                            ]);
                      }
                    // dd('in ', count($khatian_number), $khatian_number);
                }// end of patta
            }// end of if 
        //   dd('stop forech');
        }//foreach end


        // // for patta only
        // foreach($plotDetails as $item){ 
        //     if($item->patta_number){
        //         $patta_number = explode(',',$item->patta_number);
        //         if(count($patta_number) > 1){
        //             //forloop
        //             for ($x = 0; $x < count($patta_number); $x++) {
        //                 $plot_no = $x+1;
        //                     FarmerPlot::where([['farmer_plot_uniqueid', $item->farmer_plot_uniqueid], ['plot_no', $plot_no]])->update([
        //                         'patta_number' => $patta_number[$x],
        //                     ]);
        //               }
        //             // dd('in ', count($patta_number), $patta_number);
        //         }// end of patta
        //     }// end of if 
        // //   dd('stop forech');
        // }//foreach end

        return response()->json('done');
            dd($plotDetails);
    }


    public function update_col_assam(){

        $plotDetails = DB::table('farmer_plot_detail')
                        ->join('final_farmers', 'farmer_plot_detail.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
                        ->where('final_farmers.state_id', 29)//assam
                        // ->where('final_farmers.farmer_survey_id', 'MK/KAL/BAR/056')//assam
                        // ->where([['final_farmers.state_id',  29],
                        //         ['farmer_plot_detail.farmer_uniqueId',  17833]])
                        ->select('farmer_plot_detail.*')
                        ->get();

                        
            // dd($plotDetails);
            // MK/KAL/BAR/056
             // for daag number only
        foreach($plotDetails as $item){ 
            if($item->daag_number){
                $daag_number = explode(',',$item->daag_number);
                if(count($daag_number) > 1){
                    //forloop
                    for ($x = 0; $x < count($daag_number); $x++) {
                        $plot_no = $x+1;
                            FarmerPlot::where([['farmer_plot_uniqueid', $item->farmer_plot_uniqueid], ['plot_no', $plot_no]])->update([
                                'daag_number' => $daag_number[$x],
                            ]);
                      }
                    // dd('in ', count($daag_number), $daag_number);
                }// end of patta
            }// end of if 
        //   dd('stop forech');
        }//foreach end


        // // for patta only
        // foreach($plotDetails as $item){ 
        //     if($item->patta_number){
        //         $patta_number = explode(',',$item->patta_number);
        //         if(count($patta_number) > 1){
        //             //forloop
        //             for ($x = 0; $x < count($patta_number); $x++) {
        //                 $plot_no = $x+1;
        //                     FarmerPlot::where([['farmer_plot_uniqueid', $item->farmer_plot_uniqueid], ['plot_no', $plot_no]])->update([
        //                         'patta_number' => $patta_number[$x],
        //                     ]);
        //               }
        //             // dd('in ', count($patta_number), $patta_number);
        //         }// end of patta
        //     }// end of if 
        // //   dd('stop forech');
        // }//foreach end

        return response()->json('done');
            dd($plotDetails);
    }




    
        public function changes_value(Request $request){
        // $plotDetails = DB::table('farmer_plot_detail')
        //                 ->join('final_farmers', 'farmer_plot_detail.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
        //                 ->where('final_farmers.state_id', 29)
        //                 ->where('farmer_plot_detail.area_acre_awd' ,'!=', '0.0')
        //                 // ->where('farmer_plot_detail.area_acre_awd', '>', 0.0)
        //                 ->select('farmer_plot_detail.*')
        //                 ->get();

        $plotDetails = DB::table('farmer_plot_detail')
    ->join('final_farmers', 'farmer_plot_detail.farmer_plot_uniqueid', '=', 'final_farmers.farmer_plot_uniqueid')
    ->where('final_farmers.state_id', 29)
    ->where('farmer_plot_detail.area_other_awd' ,'!=', '0.0')
    ->whereRaw('area_other_awd REGEXP \'^[0-9]+(\.[0-9]+)$\'')
    ->select('farmer_plot_detail.*')
    ->get();
        dd( $plotDetails);
                        // Access the plot details
            foreach ($plotDetails as $plotDetail) {
                // Access individual plot detail properties, e.g., $plotDetail->id, $plotDetail->area_in_other_unit, etc.
                // Process the data as needed
                dd($plotDetail);

                // 
                // 
                FarmerPlot::where('farmer_plot_uniqueid', $plotDetail->farmer_plot_uniqueid)->update([
                                                                                                // 'area_acre_awd' => $plotDetail->area_in_acers,
                                                                                                'area_other_awd'  =>     $plotDetail->area_acre_awd 
                                                                                            ]);
                // dd('stop');



            }
            return response()->json('done');
        dd($plotDetails );
    }



    public function upload_surveybengal(Request $request){
        $data = Excel::toCollection(null,$request->file);
        // Loop through the data and store it in the database
        $unique = 25838; 
        // $plot_no_sr = 1;
        // dd($data[0]);
        foreach ($data[0] as $row) {
            // dd($row);
            // if($row[1]){
                    $district = DB::table('districts')->where('state_id',37)->Where('district', 'like', '%'.$row[1])->select('id','district')->first();
                    $taluka = DB::table('talukas')->where('state_id',37)->where('taluka', 'like', '%'.$row[2])->select('id','taluka')->first();

                   
                    $panchayat = DB::table('panchayats')->where('state_id',37)->where('panchayat', 'like', '%'.$row[3])->select('id','panchayat')->first();
                   
                    $village = DB::table('villages')->where('state_id',37)->where('village', 'like', '%'.$row[4])->select('id','village')->first();
                       // dd($row[10],$row ,   $district , $taluka, $panchayat,$village );
                  
                        //this is for leased land
                         // in database this data start after 9936 for plotdetail for own land
                        // in database this data start after 9936 for final
                //         $plot_no_serial = 1;
                // foreach(explode(',',$row[11]) as $plots){
                //     $final = new FinalFarmer;
                //     $final->surveyor_id  = 1;
                //     $final->surveyor_name  = 'ADMIN';
                //     $final->surveyor_email  = 'superadmin@crop.com';
                //     $final->surveyor_mobile  = '1245657895';

                //     $final->status_onboarding=  'Approved';  
                //     $final->final_status_onboarding=  'Approved';  
                //     $final->onboarding_form=  1;  
                //     $final->final_status=  'Approved';  
                //     $final->L2_aprv_timestamp   =  Carbon::now();  
                //     $final->L1_appr_timestamp   =  Carbon::now();  
                //     $final->mobile_access   =  'Own Number';
                //     $final->mobile_reln_owner   =  'NA';   
                //     $final->country_id=  '101';  
                //     $final->country=  'India';  
                //     $final->state_id=  '37';  
                //     $final->state=  'West Bengal';  //Assam 29, Telangana 36, West Bengal 37
                //     $final->check_carbon_credit=  '1';  
                //     $final->land_ownership=  'Leased';  
                //     $final->affidavit_tnc=  '0';                      
                //     $final->L2_appr_userid      =  '1';  
                //     $final->L1_aprv_recj_userid =  '1';  


                //     $final->district_id     =   $district->id??NULL;
                //     $final->district        =   $district->district??$row[1];
                //     $final->taluka_id       =   $taluka->id??NULL;
                //     $final->taluka          =   $taluka->taluka??$row[2];
                //     if($panchayat){
                //         $final->panchayat_id    =   $panchayat->id??NULL;
                //         $final->panchayat       =   $panchayat->panchayat??NULL;  
                //     }else{
                //         $final->panchayat_id    =   NULL;
                //         $final->panchayat       =   $row[3]??NULL;  
                //     }
                //     $final->village_id      =   $village->id ??NULL;
                //     $final->village         =   $village->village ??$row[4];

                //     $final->farmer_name     =   $row[5]??NULL;
                //     $final->guardian_name   =   $row[6]??NULL;
                //     $final->farmer_survey_id=   $row[7]??NULL;
                //     $final->aadhaar=   $row[8]??NULL;
                //     $final->mobile   =   $row[9]??NULL;
                //     $final->mobile_verified=   1;

                //     // if($row[9]){
                //     //     if($row[9] == 'M'){
                //     //         $final->gender=   'MALE';
                //     //     }else{
                //     //         $final->gender=   'FEMALE';
                //     //     }
                //     // }
                //     $final->no_of_plots=   count(explode(',',$row[11]))??NULL;
                //     $final->area_in_acers   =  $row[15] ??"0.0"; 

                //     $final->leased_area     =  $row[14]??"0.0";

                //     $final->total_plot_area=   $row[15]??"0.0"; 
                    

                //     $final->plot_no   =  $plot_no_serial;
                //     $final->date_survey=  '2022-01-01';  
                //     $final->time_survey=  '12:20:47';  

                //     $final->farmer_uniqueId   =  $unique; 
                //     $final->farmer_plot_uniqueid   =  $unique.'P'.$plot_no_serial;//$plot_no_sr;  
                //     // $final->leased_land_plot =   $row[14]??NULL; 
                //     $final->save();

                //     //new plot
                //     $FarmerPlot = new FarmerPlot;
                //     $FarmerPlot->farmer_id          =   $final->id;
                //     $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
                //     $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;

                //     $FarmerPlot->plot_no           =   $final->plot_no;
                //     $FarmerPlot->area_in_acers      =   $final->area_in_acers;
                //     $FarmerPlot->land_ownership     =   $final->land_ownership;

                //     $FarmerPlot->final_status       = 'Approved';
                //     $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
                //     $FarmerPlot->finalappr_userid =  '1';  
                //     $FarmerPlot->status             = 'Approved';
                
                //     $FarmerPlot->appr_timestamp=  Carbon::now();  
                //     $FarmerPlot->aprv_recj_userid=  '1'; 


                //     $FarmerPlot->area_acre_awd=  $row[14]??"0.0"; 


                //     $FarmerPlot->area_in_other=  "0.0";  
                //     $FarmerPlot->area_in_other_unit= "Acres";
                    
                //     $FarmerPlot->area_other_awd= "0.0";  
                //     $FarmerPlot->area_other_awd_unit= "Acres";                      
                //     $FarmerPlot->khatian_number=  $row[11]??NULL; 
                //     // 2463
                //     $FarmerPlot->save();
                //     $plot_no_serial++;
                // }//second foreach
                // $unique++;
                                    // dd('stop');
            //         $plot_count = explode(',',$row[10]);                
            //         // dd(explode(',',$row[10]) ,$row[10],$row ,   $district , $taluka, $panchayat,$village );
                    $plot_no_serial = 1;
                    foreach(explode(',',$row[10]) as $plots){
                        //this is for owned land
                        // in database this data end at 9936 for plotdetail for own land
                        // in database this data end at 9936 for final
                            $final = new FinalFarmer;
                            $final->surveyor_id  = 1;
                            $final->surveyor_name  = 'ADMIN';
                            $final->surveyor_email  = 'superadmin@crop.com';
                            $final->surveyor_mobile  = '1245657895';

                            $final->status_onboarding=  'Approved';  
                            $final->final_status_onboarding=  'Approved';  
                            $final->onboarding_form=  1;  
                            $final->final_status=  'Approved';  
                            $final->L2_aprv_timestamp   =  Carbon::now();  
                            $final->L1_appr_timestamp   =  Carbon::now();  
                            $final->mobile_access   =  'Own Number';
                            $final->mobile_reln_owner   =  'NA';   
                            $final->country_id=  '101';  
                            $final->country=  'India';  
                            $final->state_id=  '37';  
                            $final->state=  'West Bengal';  //Assam 29, Telangana 36, West Bengal 37
                            $final->check_carbon_credit=  '1';  
                            $final->land_ownership=  'Own';  
                            $final->affidavit_tnc=  '0';                      
                            $final->L2_appr_userid      =  '1';  
                            $final->L1_aprv_recj_userid =  '1';  


                            $final->district_id     =   $district->id??NULL;
                            $final->district        =   $district->district??$row[1];
                            $final->taluka_id       =   $taluka->id??NULL;
                            $final->taluka          =   $taluka->taluka??$row[2];
                            if($panchayat){
                                $final->panchayat_id    =   $panchayat->id??NULL;
                                $final->panchayat       =   $panchayat->panchayat??NULL;  
                            }else{
                                $final->panchayat_id    =   NULL;
                                $final->panchayat       =   $row[3]??NULL;  
                            }
                            $final->village_id      =   $village->id ??NULL;
                            $final->village         =   $village->village ??$row[4];

                            $final->farmer_name     =   $row[5]??NULL;
                            $final->guardian_name   =   $row[6]??NULL;
                            $final->farmer_survey_id=   $row[7]??NULL;
                            $final->aadhaar=   $row[8]??NULL;
                            $final->mobile  =   $row[9]??NULL;
                            $final->mobile_verified=   1;

                            // if($row[9]){
                            //     if($row[9] == 'M'){
                            //         $final->gender=   'MALE';
                            //     }else{
                            //         $final->gender=   'FEMALE';
                            //     }
                            // }
                            $final->no_of_plots=   count(explode(',',$row[10]))??NULL;
                            $final->area_in_acers   =  $row[15] ??0; 

                            // $final->leased_area     =  $row[11]??0;

                            $final->total_plot_area=   $row[15]??NULL; 
                            
                            // dd($plots);
                            $final->plot_no   =  $plot_no_serial;//'1';//$plot_no_sr;
                            $final->date_survey=  '2022-01-01';  
                            $final->time_survey=  '12:20:47';  

                            $final->farmer_uniqueId   =  $unique; 
                            $final->farmer_plot_uniqueid   =  $unique.'P'.$plot_no_serial;//$plot_no_sr;  
                            // $final->leased_land_plot =   $row[14]??NULL; 
                            $final->save();
        
                            //new plot
                            $FarmerPlot = new FarmerPlot;
                            $FarmerPlot->farmer_id          =   $final->id;
                            $FarmerPlot->farmer_uniqueId    =   $final->farmer_uniqueId;
                            $FarmerPlot->farmer_plot_uniqueid   =   $final->farmer_plot_uniqueid;
        
                            $FarmerPlot->plot_no           =   $final->plot_no;
                            $FarmerPlot->area_in_acers      =   $final->area_in_acers;
                            $FarmerPlot->land_ownership     =   $final->land_ownership;
        
                            $FarmerPlot->final_status       = 'Approved';
                            $FarmerPlot->finalaprv_timestamp =  Carbon::now();  
                            $FarmerPlot->finalappr_userid =  '1';  
                            $FarmerPlot->status             = 'Approved';
                        
                            $FarmerPlot->appr_timestamp=  Carbon::now();  
                            $FarmerPlot->aprv_recj_userid=  '1'; 


                            $FarmerPlot->area_acre_awd=  $row[13]??"0.0"; 

                            $FarmerPlot->area_in_other=  "0.0";  
                            $FarmerPlot->area_in_other_unit= "Acres";
                            
                            $FarmerPlot->area_other_awd= "0.0";  
                            $FarmerPlot->area_other_awd_unit= "Acres";                            
                            $FarmerPlot->khatian_number=  $plots??NULL; 

                            $FarmerPlot->save();
                            $plot_no_serial++;
                    }//secod foreach end
                    // dd('sdone');   
                    $unique++;   
                    // $plot_no_sr++;      
                // }

            //     // dd('stop');
            }//foreach end
          

            // 6157 final far
            // plot detail 6157
        return response()->json('done');
    }


    


    // public function upload_village(Request $request){

    //     $data = Excel::toCollection(null,$request->excel);
       
    //     // Loop through the data and store it in the database
    //     foreach ($data[0] as $row) {
    //         // dd( $row);
    //         $panchat = Panchayat::where('panchayat',$row[2])->select('id','panchayat')->first();
    //         $taluka = Taluka::where('id',$row[3])->orWhere('taluka',$row[3])->select('id','taluka')->first();

    //         // dd($panchat,  $taluka ,$row);

    //         $Village = new Village;
    //         $Village->village           =    $row[1]??NULL;
           
    //         $Village->panchayat_id           =    $panchat->id??$row[2];

    //         $Village->taluka_id           =     $taluka->id??$row[3];
    //         $Village->district_id           =    $row[4]??NULL;
    //         $Village->state_id           =    $row[5]??NULL;
    //         $Village->save();
    //         // Village::create([

    //         //     'village' => $row[1]??NULL,
    //         //     'panchayat_id' => $row[2]??NULL,
    //         //     'taluka_id' => $row[3]??NULL,
    //         //     'district_id' => $row[4]??NULL,
    //         //     'state_id' => $row[5]??NULL,
    //         //     // Add more columns as needed
    //         // ]);
    //         // dd($row);
    //         // dd('c');
    //     }
    //     return response()->json('done');

    // }



    public function move_plotcqc_to_plot(){
      /* $plots = DB::table('farmer_plot_detail')
                        ->join('farmers', 'farmer_plot_detail.farmer_uniqueId', '=', 'farmers.farmer_uniqueId')
                        // ->where('farmer_plot_detail.farmer_uniqueId','122451')
                        ->where('farmers.onboarding_form','1')
                        // ->where('farmer_plot_detail.status','Approved')   //first case move all apprve plot of l1
                        ->get();
         dd($plots); */

//where('farmer_uniqueId','122954')->
// 123174
// where('farmer_uniqueId' ,'123409')->
         $farmer = FarmerPlot::whereHas('farmer', function($q) {
                         $q->where('onboarding_form',1);
                         $q->where('created_at','>=','2022-12-17 07:00:00');
                        return $q;
                            })
                        ->with('FarmerPlotImages')->get();

// dd('csc',$farmer);

    // foreach($farmer as $item){
    //     foreach($item->FarmerPlotImages as $img){
    //         $img_explode="";
    //         $img_explode = explode('/',$img->path);
    //         if($img_explode[0] == 'plotcqc'){
    //             $img_explode[0] = 'plot';            
    //             FarmerPlotImage::where('id', $img->id)->update([                            
    //                 'path'  => implode('/', $img_explode),
    //             ]);
    //             $img_explode[0] = 'plot';            
    //             FarmerPlotImage::where('id', $img->id)->update([                            
    //                     'path'  => implode('/', $img_explode),
    //                 ]); 
    //         }     
    //     }
    // }
    foreach($farmer as $item){
        // dd($item->farmer);
        $sign = explode('/',$item->farmer->signature);
        $sign[0] = 'plot'; 
        // dd($sign, implode('/', $sign));
        
        Farmer::where('id', $item->farmer->id)->update([
            'signature'  => implode('/', $sign),
            ]);
    }
        
dd('done');
// 122954
// 103395


    }

 public function move_status_to_new_table(){
      /* $plots = DB::table('farmer_plot_detail')
                        ->join('farmers', 'farmer_plot_detail.farmer_uniqueId', '=', 'farmers.farmer_uniqueId')
                        // ->where('farmer_plot_detail.farmer_uniqueId','122451')
                        ->where('farmers.onboarding_form','1')
                        // ->where('farmer_plot_detail.status','Approved')   //first case move all apprve plot of l1
                        ->get();
         dd($plots); */

//where('farmer_uniqueId','122954')->
// 123174
         $farmer = FarmerPlot::whereIn('status',['Approved','Rejected'])->whereHas('farmer', function($q) {
                         $q->where('onboarding_form',1);

                        return $q;
                            })
                        ->get();

// dd($farmer);
    foreach($farmer as $items){
            if($items->status == 'Approved'){
          //for L1 validator
              $record =  PlotStatusRecord::create([
                     'farmer_uniqueId'           => $items->farmer_uniqueId,
                     'plot_no'                   => $items->plot_no,
                     'farmer_plot_uniqueid'      => $items->farmer_uniqueId.'P'.$items->plot_no,
                     'level'                     => 'L-1-Validator',
                     'status'                    => 'Approved',
                     'comment'                   => $items->approve_comment??NULL,
                     'timestamp'                 => $items->appr_timestamp,
                     'user_id'                   => $items->aprv_recj_userid,
                     // 'approve_comment'           => $items->approve_comment??NULL,
                     // 'appr_timestamp'            => $items->appr_timestamp,
                     // 'reject_comment'        => NULL,
                     // 'reject_timestamp'      => NULL,
                     // 'aprvd_recj_userid'      => $items->aprv_recj_userid,

                 ]);
            }
      if($items->status == 'Rejected'  && $items->final_status == 'Pending'){
        //this condition is for when l1 Rejected.
        //when both status is rejected then we have to take data from l2 rejected record columns
        //for L1 validator
        // dd('2 e');
                 $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           => $items->farmer_uniqueId,
                        'plot_no'                   => $items->plot_no,
                        'farmer_plot_uniqueid'      => $items->farmer_uniqueId.'P'.$items->plot_no,
                        'level'                     => 'L-1-Validator',
                        'status'                    => 'Rejected',
                        'comment'                   => $items->reject_comment??NULL,
                        'timestamp'                 => $items->reject_timestamp,
                        'user_id'                   => $items->aprv_recj_userid,
                        'reject_reason_id'          => $items->reason_id,
                        // 'approve_comment'           => NULL,
                        // 'appr_timestamp'            => NULL,
                        // 'reject_comment'        => $items->reject_comment,
                        // 'reject_timestamp'      => $items->reject_timestamp,
                        // 'aprvd_recj_userid'      => $items->aprv_recj_userid,
                        // 'reject_reason_id'     => $items->reason_id,

                    ]);

      }
      if($items->final_status == 'Approved'){
          //for L2 validator
          $record =  PlotStatusRecord::create([
                       'farmer_uniqueId'           => $items->farmer_uniqueId,
                       'plot_no'                   => $items->plot_no,
                       'farmer_plot_uniqueid'      => $items->farmer_uniqueId.'P'.$items->plot_no,
                       'level'                     => 'L-2-Validator',
                       'status'                    => 'Approved',
                       'comment'                   => $items->finalaprv_remark??NULL,
                       'timestamp'                 => $items->finalaprv_timestamp,
                       'user_id'                   => $items->finalappr_userid,
                       // 'approve_comment'           => $items->finalaprv_remark,
                       // 'appr_timestamp'            => $items->finalaprv_timestamp,
                       // 'reject_comment'        => NULL,
                       // 'reject_timestamp'      => NULL,
                       // 'aprvd_recj_userid'      => $items->finalappr_userid,

                   ]);
      }
      if($items->final_status == 'Rejected'){
        // dd('csc');
              $record =  PlotStatusRecord::create([
                        'farmer_uniqueId'           =>  $items->farmer_uniqueId,
                        'plot_no'                   => $items->plot_no,
                        'farmer_plot_uniqueid'      => $items->farmer_uniqueId.'P'.$items->plot_no,
                        'level'                     => 'L-2-Validator',
                        'status'                    => 'Rejected',
                        'comment'                   => $items->reject_comment??NULL,
                        'timestamp'                 => $items->finalreject_timestamp,
                        'user_id'                   => $items->finalreject_userid,
                        'reject_reason_id'          => $items->reason_id,
                        // 'approve_comment'           => NULL,
                        // 'appr_timestamp'            => NULL,
                        // 'reject_comment'        => $items->reject_comment,
                        // 'reject_timestamp'      => $items->finalreject_timestamp,
                        // 'aprvd_recj_userid'      => $items->finalreject_userid,
                        // 'reject_reason_id'     => $items->reason_id,

                    ]);
      }
// 122954
// 103395

// 105874
    }//foreach end
          dd('done');

    }
  public function updte_unique_plot(){
     try {
         // $farmerplots =FarmerPlot::select('id','farmer_uniqueId','farmer_plot_uniqueid','plot_no')->with('farmer')->whereHas('farmer',function($q){
         //     $q->where('onboarding_form','1');
         //     return $q;
         // })->get();

// dd($farmerplots);
// ->whereNull('farmer_plot_uniqueid')
         // foreach($farmerplots as $farmerplot){
         //     $farmerplot->farmer_plot_uniqueid = $farmerplot->farmer_uniqueId.'P'.$farmerplot->plot_no;
         //     $farmerplot->save();
         // }


         DB::table('farmer_plot_detail')->orderBy('id')->whereNull('farmer_plot_uniqueid')->chunk(100, function ($plots) {
          dd($plots);
             foreach ($plots as $plot) {
  FarmerPlot::where('farmer_uniqueId', $plot->farmer_uniqueId)->where('plot_no',$plot->plot_no)->update(['farmer_plot_uniqueid'=>$plot->farmer_uniqueId.'P'.$plot->plot_no]);

             }
         });

         return response()->json(['success'=>true],200);
     } catch(\Illuminate\Database\QueryException $e) {
         if($e->getCode() == 23000)
         {
           return response()->json(['error'=>true,'something went wrong'],500);
         }
         return response()->json(['error'=>true,'something went wrong'],500);
     }
  }


    // public function add_newupqueplotid(){
    //     $farmer = Farmer::where('onboarding_form','1')->with('FarmerPlot')->get();

    //      dd($farmer);
    // }



     /**
     * It was just to assign role to all user.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignrole(){
        //  when roles and permissin was newly creating after all user were registered

        // $users = User::where('role','employee')->get();
        // foreach($users as $user){
        //     // $user->syncRoles(3);
        // }
        // return response()->json('no way');
         $users = User::whereHas('roles', function($q){
                $q->where('name', 'User');
            }
            )->get();
            dd($users);
            foreach($users as $user){
                User::where('mobile',$user->mobile)->where('id',$user->id)->update(['password' => bcrypt($user->mobile)]);
            }
        return response()->json('done');
    }

    /**
     * Change all password of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function changes_password(){
         $users = User::whereHas('roles', function($q){
                $q->where('name', 'User');
            }
            )->get();
            dd($users);
            foreach($users as $user){
                User::where('mobile',$user->mobile)->where('id',$user->id)->update(['password' => bcrypt($user->mobile)]);
            }
        return response()->json('done');
    }

    /**
     * Change all password of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function remove_email_users(){
         $users = User::whereHas('roles', function($q){
                $q->where('name', 'User');
            }
            )->get();
            foreach($users as $user){
                User::where('mobile',$user->mobile)->where('id',$user->id)->update(['email' => ' ']);
            }
        return response()->json('done');
    }

    /**
     * Change all password of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_role(){
         $users = User::whereHas('roles', function($q){
                $q->where('name', 'Vendor');
            }
            )->get();
            foreach($users as $user){
                User::where('id',$user->id)->update(['role' => 'Vendor']);
            }
        return response()->json('done');
    }





    
    public function upload_pnp_data(Request $request) {
        try {
            // dd('in');
            $data = Excel::toCollection(null, $request->file);
            $unique = 15778;
            $plot_no_sr = 1;

            foreach ($data[0] as $row) {
                $district = DB::table('districts')->where('district', 'like', $row[9])->select('id', 'district')->first();
                $states = DB::table('states')->where('name', 'like', '%' . $row[8])->select('id', 'name')->first();

                if (!$district) {
                    $district = new District();
                    $district->district = $row[9];
                    $district->state_id = $states->id;
                    $district->status = 1;
                    $district->save();
                }

                $taluka = DB::table('talukas')->where('taluka', 'like', '%' . $row[10])->select('id', 'taluka')->first();

                if (!$taluka) {
                    $taluka = new Taluka();
                    $taluka->taluka = $row[10];
                    $taluka->state_id = $states->id;
                    $taluka->district_id = $district->id;
                    $taluka->status = 1;
                    $taluka->save();
                }

                // $panchayat = DB::table('panchayats')
                //     ->where('panchayat', 'like', '%' . $row[11])
                //     ->where(function($query) use ($district, $taluka) {
                //         $query->where('district_id', $district->id)
                //               ->orWhere('taluka_id', $taluka->id);
                //     })
                //     ->select('id', 'panchayat')
                //     ->first();

                // if (!$panchayat) {
                //     $panchayat = new Panchayat();
                //     $panchayat->panchayat = $row[11];
                //     $panchayat->state_id = $states->id;
                //     $panchayat->district_id = $district->id;
                //     $panchayat->taluka_id = $taluka->id;
                //     $panchayat->status = 1;
                //     $panchayat->save();
                // }

                  $panchayatName = !empty($row[11]) ? $row[11] : $row[12]; // Use panchayat name if available, otherwise use village name
                  
                  $panchayat = DB::table('panchayats')
                      ->where('panchayat', 'like', '%' . $panchayatName)
                      ->where(function($query) use ($district, $taluka) {
                          $query->where('district_id', $district->id)
                                ->orWhere('taluka_id', $taluka->id);
                      })
                      ->select('id', 'panchayat')
                      ->first();
                  
                  if (!$panchayat) {
                      $panchayat = new Panchayat();
                      $panchayat->panchayat = $panchayatName;
                      $panchayat->state_id = $states->id;
                      $panchayat->district_id = $district->id;
                      $panchayat->taluka_id = $taluka->id;
                      $panchayat->status = 1;
                      $panchayat->save();
                  }

                $village = DB::table('villages')->where('village', 'like', '%' . $row[12])->select('id', 'village')->first();

                if (!$village) {
                    $village = new Village();
                    $village->village = $row[12];
                    $village->panchayat_id = $panchayat->id;
                    $village->taluka_id = $taluka->id;
                    $village->district_id = $district->id;
                    $village->state_id = $states->id;
                    $village->save();
                }

                // dd('Test');
                $final = new FinalFarmer();
                $final->surveyor_id = 1;
                $final->document_no = NULL;
                $final->document_id = NULL;
                $final->farmer_name = $row[1] ?? NULL;
                $final->organization_id = 2;
                $final->farmer_uniqueId = $unique;
                $final->farmer_survey_id = substr($district->district, 0, 2) . '/' . substr($taluka->taluka, 0, 3) . '/' . substr($village->village, 0, 3) . '/' . $final->farmer_uniqueId;
                $final->land_ownership = $row[2] ?? NULL;
                $final->guardian_name = $row[3] ?? NULL;
                $final->mobile = $row[4] ?? NULL;
                $final->mobile_verified = 1;
                $final->gender = $row[7] ?? NULL;
                $final->state_id = $states->id ?? NULL;
                $final->district_id = $district->id ?? NULL;
                $final->taluka_id = $taluka->id ?? NULL;
                $final->panchayat_id = $panchayat->id ?? NULL;
                $final->village_id = $village->id;
                $final->pincode = $row[13] ?? NULL;
                $final->no_of_plots = $row[15] ?? NULL;
                $final->status_onboarding = 'Approved';
                $final->final_status_onboarding = 'Approved';
                $final->onboarding_form = 1;
                $final->onboard_completed = "Approved";
                $final->final_status = 'Approved';
                $final->L2_aprv_timestamp = Carbon::now();
                $final->mobile_access = 'Own Number';
                $final->mobile_reln_owner = 'NA';
                $final->country_id = '101';
                $final->check_carbon_credit = '1';
                $final->affidavit_tnc = '0';
                $final->L2_appr_userid = '1';
                $final->mobile_verified = 1;
                $final->area_in_acers = $row[19] ?? NULL;

                if ($final->land_ownership == 'Own') {
                    $final->own_area_in_acres = $row[19] ?? NULL;
                }

                if ($final->land_ownership == 'Leased') {
                    $final->lease_area_in_acres = $row[19] ?? NULL;
                }

                $final->total_plot_area = '0.0';
                $final->season = $row[25] ?? NULL;
                $final->financial_year = $row[24] ?? NULL;
                $final->plot_no = 1;
                $final->farmer_plot_uniqueid = $unique . 'P1';
                $final->date_survey = $row[22] ?? Carbon::now();
                $final->time_survey = $row[23] ?? Carbon::now();
                $final->save();

                $FarmerPlot = new FarmerPlot();
                $FarmerPlot->farmer_id = $final->id;
                $FarmerPlot->farmer_uniqueId = $final->farmer_uniqueId;
                $FarmerPlot->farmer_plot_uniqueid = $final->farmer_plot_uniqueid;
                $FarmerPlot->plot_no = $final->plot_no;
                $FarmerPlot->area_in_acers = $final->area_in_acers;
                $FarmerPlot->land_ownership = $row[2] ?? NULL;
                $FarmerPlot->patta_number = $row[16] ?? NULL;
                $FarmerPlot->daag_number = $row[17] ?? NULL;
                $FarmerPlot->survey_no = 123;
                $FarmerPlot->final_status = 'Approved';
                $FarmerPlot->finalaprv_timestamp = Carbon::now();
                $FarmerPlot->finalappr_userid = '1';
                $FarmerPlot->status = 'Approved';
                $FarmerPlot->appr_timestamp = Carbon::now();
                $FarmerPlot->aprv_recj_userid = '1';
                $FarmerPlot->daag_number = NULL;
                $FarmerPlot->patta_number = NULL;
                $FarmerPlot->area_in_other = '0.0';
                $FarmerPlot->area_in_other_unit = "0.0";
                $FarmerPlot->area_acre_awd = '0.0';
                $FarmerPlot->area_other_awd = '0.0';
                $FarmerPlot->area_other_awd_unit = "0.0";
                $FarmerPlot->save();

                $farmer_farm_details = new FarmerFarmDetails();
                $farmer_farm_details->farmer_uniqueId = $final->farmer_uniqueId;
                $farmer_farm_details->irigation_source = "Bore";
                $farmer_farm_details->struble_burning = "No";
                $farmer_farm_details->double_paddy_status = "Rabi";
                $farmer_farm_details->soil_type = "Alluvial";
                $farmer_farm_details->variety = "19";
                $farmer_farm_details->flooding_type = "Continues";
                $farmer_farm_details->proper_drainage = "Yes";
                $farmer_farm_details->awd_previous = "No";
                $farmer_farm_details->awd_previous_no = "0";
                $farmer_farm_details->community_benefit = "Yes";
                $farmer_farm_details->final_status = "Approved";
                $farmer_farm_details->save();

                $unique++;

                // dd($FarmerPlot , $farmer_farm_details , $final);
            }

            return response()->json('done');
        } catch (Exception $e) {
            dd($e);
            return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
        }
    }


    // public function update_excel_data_using_api(Request $request){
    //     try {
    //         $data = Excel::toCollection(null, $request->file);

    //         foreach ($data[0] as $row) {
    //             $farmerUniqueId = $row[0];
    //             $farmerPlotUniqueId = $row[1];

    //             // Find existing farmer record
    //             $farmer = FinalFarmer::where('farmer_uniqueId', $farmerUniqueId)
    //                 ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
    //                 ->first();

    //             if ($farmer) {
    //                 // Update fields as necessary
    //                 $farmer->available_area = round($row[2] - $row[3], 3);
    //                 $farmer->save();
    //             }
    //         }

    //         return response()->json('done');
    //     } catch (Exception $e) {
    //         return response()->json(['error' => true, 'message' => 'Something went wrong', 'exception' => $e->getMessage()], 500);
    //     }
    // }


    public function update_excel_data_using_api(Request $request){
        try {
            $data = Excel::toCollection(null, $request->file);
    
            foreach ($data[0] as $row) {
                $farmerUniqueId = $row[0];
                $farmerPlotUniqueId = $row[1];
    
                // Find existing farmer record
                $farmer = FinalFarmer::where('farmer_uniqueId', $farmerUniqueId)
                    ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
                    ->first();
    
                if ($farmer) {
                    // Update fields
                    $farmer->area_in_hectares = round($row[2], 3);
                    $farmer->available_area = round($row[2] * 0.404686, 3); // Convert to hectares
                    $farmer->area_in_acers = round($row[2] * 0.404686, 3);
    
                    // Update based on land ownership type
                    if ($farmer->land_ownership == 'Own') {
                        $farmer->own_area_in_acres = round($row[2] * 0.404686, 3) ?? null;
                    } elseif ($farmer->land_ownership == 'Leased') {
                        $farmer->lease_area_in_acres = round($row[2] * 0.404686, 3) ?? null;
                    }
    
                    // Save updated farmer data
                    $farmer->save();
                }
            }
    
            return response()->json('done');
        } catch (Exception $e) {
            dd($e);
            return response()->json(['error' => true, 'message' => 'Something went wrong', 'exception' => $e->getMessage()], 500);
        }
    }
    

}
