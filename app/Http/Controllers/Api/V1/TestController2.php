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
use App\Models\Polygon;
use App\Models\AerationImage;
use App\Models\PipeInstallationPipeImg;
use App\Models\Aeration;
use App\Models\Cordinate;
use App\Models\FinalFarmer;
use App\Models\FarmerAwd;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Http\Message\ResponseInterface;
use App\Exports\ExportAeration;
use App\Exports\ExportOnboarding;
use App\Exports\ExportPolygon;
use App\Exports\ExportPipe;

use App\Models\State;
use App\Models\District;
use App\Models\Taluka;
// use App\Models\Panchayat;
// use App\Models\Village;
// use App\Models\FarmerPlot;


use Illuminate\Support\Facades\Storage as FacadesStorage;
// use Maatwebsite\Excel\Excel as ExcelExcel;

// use Maatwebsite\Excel\Facades\Excel;

class TestController2 extends Controller
{

    public function geojson(Request $request){

        // dd($request->all());
        $file=$request->file;
        // $file="Dunigram_farm_bound.json";
        $contents=json_decode(file_get_contents($file),true);
        // dd($contents);
        $data=[];
      
        foreach($contents['features'] as $key=>$features){
            //dd($features);
            $cord=$features['properties'];
            foreach($features['geometry']['coordinates'] as $index=>$coordinates){
                //d($coordinates);
                foreach ($coordinates as $i => $coordinate) {
                    //dd($coordinate);
                    //$data[][$key][$i]=$coordinate;
                    $cord['coordinates'][]=collect([
                        'lat'=>"$coordinate[1]",
                        'lng'=>"$coordinate[0]"
                    ])->toArray();
                }
            }
            $data[]=$cord;
        }
        // dd(collect($data)->take(5));
        foreach ($data as $item) {
            $cordinate         = new Cordinate();
            $cordinate->gid    = $item['gid'];
            $cordinate->fid    = $item['fid'];
            $cordinate->ranges = json_encode($item['coordinates']); 
            $cordinate->status = "Pending";
            $cordinate->village = $request->village;
            // dd($cordinate);
            $cordinate->save();
        }

        return response()->json(['message' => 'Data saved successfully']);
       
        // return response()->json(collect($data)->take(5));
    }


    public function genrate_geojson_old(Request $request)
    {

       

        set_time_limit(-1);
        ini_set('memory_limit', '64000M');

        // Assuming $uniqueIds contains the list of unique IDs
        // $uniqueIds = ['33381P1'];
        // $farmers = PipeInstallation::where('district','COOCHBEHAR')
        // ->whereNULL('delete_polygon')
        // ->get();
        $farmers = [
          4254,575,787,1048,4049,2184,2755,146,788,15066,15080,
          15081,15093,15120,15132,15138,898,15016,15021,1045,15144,
          174,4588,399,769,803,16074,397,1193,4776,4186,4188,4189,
          4191,4207,4212,4991,3346,138,15110,15111,778,4247
          ,4781,148,15126,15161,145,1774,543,4782,4813,4843,
        ];
        
        
        // dd($farmers);

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];
        
        foreach ($farmers as $uniqueId) {
            // dd($uniqueId);
            $farmer = PipeInstallation::where('farmer_uniqueId', $uniqueId)->first(); 
            if ($farmer && $farmer->ranges) {
                $ranges = json_decode($farmer->ranges, true);
                
                // dd($ranges);
                $coordinates = [];
                // dd($coordinates);
                foreach ($ranges as $range) {
                    $coordinates[] = [
                        'latitude' => $range['lat'],
                        'longitude' => $range['lng'],
                    ];
                }
        
                // Construct GeoJSON for this unique ID
                $feature = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [array_map(function ($coord) {
                            return [$coord['longitude'], $coord['latitude']];
                        }, $coordinates)],
                    ],
                    'properties' => [
                        'farmer_uniqueId'=> $farmer->farmer_uniqueId,
                        'farmer_plot_uniqueid'=> $farmer->farmer_plot_uniqueid,
                        'farmer_name'=> $farmer->farmerapproved->farmer_name??"NA",
                        'plot_no'=> $farmer->plot_no,
                        'ranges'=> $farmer->ranges,
                        'polygon_date_time'=> $farmer->polygon_date_time,
                        'date_time'=> $farmer->date_time,
                        'date_survey'=> $farmer->date_survey,
                        'surveyor_name'=> $farmer->surveyor_name,
                        'status'=> $farmer->l2_status,
                        'surveyor_mobile'=> $farmer->surveyor_mobile,
                        'latitude'=> $farmer->latitude,
                        'longitude'=> $farmer->longitude,
                        'state' =>  $farmer->state,
                        'district'=> $farmer->district,
                        'taluka'=> $farmer->taluka,
                        'village' => $farmer->village,
                        'area_in_acers'=> $farmer->area_in_acers,
                        'plot_area' =>  $farmer->plot_area,
                        'validator_name'=> $farmer->validator->name??"NA",
                    ],
                ];
        
                $geojson['features'][] = $feature;
            }
        }
        
        // Serialize GeoJSON to JSON
        $json = json_encode($geojson);

      // Define the directory and file path to save the GeoJSON data
        $directory = public_path('geojson'); // Path to the directory where you want to save the file
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true); // Create the directory if it doesn't exist
        }
        $filename = 'farmers_polygon-assam.geojson';
        $file_path = $directory . '/' . $filename;

        // Save the GeoJSON data to the file
        file_put_contents($file_path, $json);

        // Check if the file was saved successfully
        if (file_exists($file_path)) {
            echo "GeoJSON data saved successfully to $file_path";
        } else {
            echo "Failed to save GeoJSON data";
        }

        return response()->json(['message'=>"genrted" ]);

        

    }


    // public function genrate_geojson(Request $request)
    // {



    //     set_time_limit(-1);
    //     ini_set('memory_limit', '72000M');

    //     // Assuming $uniqueIds contains the list of unique IDs
    //     // $uniqueIds = ['33381P1'];
    //     // $farmers = PipeInstallation::where('district','COOCHBEHAR')
    //     // ->whereNULL('delete_polygon')
    //     // ->get();
    //     $farmerUniqueIds = [
    //         4254,575,787,1048,4049,2184,2755,146,788,15066,15080,
    //         15081,15093,15120,15132,15138,898,15016,15021,1045,15144,
    //         174,4588,399,769,803,16074,397,1193,4776,4186,4188,4189,
    //         4191,4207,4212,4991,3346,138,15110,15111,778,4247
    //         ,4781,148,15126,15161,145,1774,543,4782,4813,4843,
    //     ];


    //     // dd($farmers);

    //     $geojson = [
    //         'type' => 'FeatureCollection',
    //         'features' => [],
    //     ];

    //     $chunkSize = 100;

    //     // foreach ($farmers as $uniqueId) {
    //         foreach (array_chunk($farmerUniqueIds, $chunkSize) as $chunk) {
    //             $farmers_data = Polygon::whereIn('farmer_uniqueId', $chunk)->get();
        
    //             foreach ($farmers_data as $farmer) {
    //                 if ($farmer && $farmer->ranges) {
    //                     $ranges = json_decode($farmer->ranges, true);
    //                     $coordinates = [];
                
    //                     foreach ($ranges as $range) {
    //                         $coordinates[] = [
    //                             'latitude' => $range['lat'],
    //                             'longitude' => $range['lng'],
    //                         ];
    //                     }
                
    //                     // Ensure the last coordinate matches the first coordinate
    //                     if (!empty($coordinates)) {
    //                         $firstCoordinate = $coordinates[0];
    //                         $lastCoordinate = end($coordinates);
                
    //                         if ($firstCoordinate['latitude'] !== $lastCoordinate['latitude'] ||
    //                             $firstCoordinate['longitude'] !== $lastCoordinate['longitude']) {
    //                             $coordinates[] = $firstCoordinate;
    //                         }
    //                     }
                
    //                     // Construct GeoJSON for this unique ID
    //                     $feature = [
    //                         'type' => 'Feature',
    //                         'geometry' => [
    //                             'type' => 'Polygon',
    //                             'coordinates' => [array_map(function ($coord) {
    //                                 return [$coord['longitude'], $coord['latitude']];
    //                             }, $coordinates)],
    //                         ],
    //                         'properties' => [
    //                             'farmer_uniqueId' => $farmer->farmer_uniqueId ?? "",
    //                             'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid ?? "",
    //                             'farmer_name' => $farmer->farmerapproved->farmer_name ?? "NA",
    //                             'plot_no' => $farmer->plot_no ?? "",
    //                             'ranges' => $farmer->ranges,
    //                             'polygon_date_time' => $farmer->polygon_date_time ?? "",
    //                             'date_time' => $farmer->date_time ?? "",
    //                             'date_survey' => $farmer->date_survey ?? "",
    //                             'surveyor_name' => $farmer->surveyor_name ?? "",
    //                             'status' => $farmer->l2_status ?? "",
    //                             'surveyor_mobile' => $farmer->surveyor_mobile ?? "",
    //                             'latitude' => $farmer->latitude ?? "",
    //                             'longitude' => $farmer->longitude ?? "",
    //                             'state' => $farmer->farmerapproved->state->name ?? "",
    //                             'district' => $farmer->farmerapproved->district->district ?? "",
    //                             'taluka' => $farmer->farmerapproved->taluka->taluka ?? "",
    //                             'village' => $farmer->farmerapproved->village->village ?? "",
    //                             'area_in_hectare' => $farmer->farmerapproved->area_in_acers ?? "",
    //                             'plot_area' => $farmer->plot_area ?? "",
    //                             'validator_name' => $farmer->PolygonValidation->users->name ?? "NA",
    //                             // dd($farmer),
    //                         ],
    //                     ];  
    //                     // dd($feature);
                
    //                     $geojson['features'][] = $feature;
    //                 }
    //             }
                
    //         }

    //     // Serialize GeoJSON to JSON
    //     $json = json_encode($geojson);

    //     // Define the directory and file path to save the GeoJSON data
    //     $directory = public_path('geojson'); // Path to the directory where you want to save the file
    //     if (!file_exists($directory)) {
    //         mkdir($directory, 0755, true); // Create the directory if it doesn't exist
    //     }
    //     $filename = 'polygon-geojson9.geojson';
    //     $file_path = $directory . '/' . $filename;

    //     // Save the GeoJSON data to the file
    //     file_put_contents($file_path, $json);

    //     // Check if the file was saved successfully
    //     if (file_exists($file_path)) {
    //         echo "GeoJSON data saved successfully to $file_path";
    //     } else {
    //         echo "Failed to save GeoJSON data";
    //     }

    //     return response()->json(['message' => "genrted"]);
    // }

//     public function genrate_geojson(Request $request)
// {
//     set_time_limit(-1);
//     ini_set('memory_limit', '72000M');

//     // Assuming $uniqueIds contains the list of unique IDs
//     // $uniqueIds = ['33381P1'];
//     // $farmers = PipeInstallation::where('district','COOCHBEHAR')
//     // ->whereNULL('delete_polygon')
//     // ->get();
//     $farmerUniqueIds = [
//         13975,11951,13713,14287,12265,12273,12538,10423,10673,14189,14396,14421,16590,13468,7420,7975,8247,9071,12142,12168,12708,13038,13040,13041,13050,7889,
//         8504,8944,8962,8970,8984,9022,9242,10177,10182,10185,10187,10192,10317,10319,10322,10323,10324,10332,10336,10339,10458,10461,10489,10671,10672,10677,
//         10680,10697,10699,10702,10810,10812,10814,11074,11648,12152,12387,12423,8788,9107,9117,9141,11780,14258,14490,9309,9353,11502,11582,16521,7640,7716,
//         7771,7781,7785,8217,8243,8257,8283,8290,8294,8374,8439,8441,8450,10465,10473,10491,10518,10588,10888,11098,11532,11535,11536,11537,11538,11539,11540,
//         11544,11546,11763,11766,11770,12368,12375,12385,12402,12436,12450,12463,13087,13127,13532,13547,13569,14671,7469,8037,8059,12431,12434,12435,12442,
//         12653,8282,8289,8293,8363,8478,8493,8507,8509,8629,8633,8640,8652,8656,8698,9254,10082,10093,10101,10719,10739,10746,10755,10780,10838,10845,11103,
//         11148,11156,11181,11190,11198,12197,12198,12206,12564,12996,13141,13147,13529,13531,13535,13539,16578,16591,16597,16680,7310,7320,7322,7325,7408,7441,
//         7457,7484,7524,7573,7589,7597,7615,7636,7658,7829,7830,7844,7848,7858,7861,7897,7901,7906,7915,7988,8028,8036,8045,8352,8358,8913,8921,8958,8967,8973,
//         8979,8982,9074,9240,9307,9311,9316,9322,9329,9334,9376,10140,10141,10156,10161,10175,10190,10340,10343,10422,10451,10462,11493,11579,11684,11686,11725,
//         11743,11746,11824,11833,11943,11956,11959,11965,11973,12321,12475,12479,12482,12863,12868,12870,13503,13515,13523,14140,14143,14144,14145,14226,14275,
//         14307,14685,14688,16531,16670,12201,12993,12994,12995,14478,7354,7404,7521,8268,11259,11550,11737,12059,12767,13100,13145,14648,7689,8452,12665,9075,
//         11967,7890,7932,8933,9592,10110,11498,11583,11828,12733,12735,8284,9220,9223,11049,11057,11059,12541,12563,13028,13032,13033,13035,13036,13042,13487,
//         13495,13170,13195,14738,14957,7350,7832,7925,8392,8468,9265,7561,11703,14722,14805,8700,8741,11710,11724,12041,12083,12284,12318,12693,14469,14868,7417,
//         7421,7433,7439,9228,9230,10691,13980,14171,14485,14752,14757,14942,14949,7888,8596,11610,11611,11614,11618,11621,11628,11630,11638,11642,11645,11647,11649,
//         11654,11900,11904,11907,12082,12094,12098,12104,12105,12107,12114,12116,12122,12128,12149,12389,12392,12395,12406,12408,12411,12416,12537,12539,13996,14165,
//         14302,16577,16583,16588,16595,11953,7824,8957,8628,11494,9327,14971,8476,11016,11038,11092,14185,14283,13976,13985,8948,9027,13973,14168,14169,14481,
//     ];


//     // dd($farmers);

//     $geojson = [
//         'type' => 'FeatureCollection',
//         'features' => [],
//     ];

//     $chunkSize = 100;
//     // foreach ($farmers as $uniqueId) {
//         foreach (array_chunk($farmerUniqueIds, $chunkSize) as $chunk) {
//             $farmers_data = Polygon::whereIn('farmer_uniqueId', $chunk)->get();
    
//             foreach ($farmers_data as $farmer) {
//                 if ($farmer && $farmer->ranges) {
//                     $ranges = json_decode($farmer->ranges, true);
//                     $coordinates = [];
            
//                     foreach ($ranges as $range) {
//                         $coordinates[] = [
//                             'latitude' => (float) $range['lat'],
//                             'longitude' =>  (float) $range['lng'],
//                         ];
//                     }
            
//                     // Ensure the last coordinate matches the first coordinate
//                     if (!empty($coordinates)) {
//                         $firstCoordinate = $coordinates[0];
//                         $lastCoordinate = end($coordinates);
            
//                         if ($firstCoordinate['latitude'] !== $lastCoordinate['latitude'] ||
//                             $firstCoordinate['longitude'] !== $lastCoordinate['longitude']) {
//                             $coordinates[] = $firstCoordinate;
//                         }
//                     }
            
//                     // Construct GeoJSON for this unique ID
//                     $feature = [
//                         'type' => 'Feature',
//                         'geometry' => [
//                             'type' => 'Polygon',
//                             'coordinates' => [array_map(function ($coord) {
//                                 return [(float) $coord['longitude'], (float) $coord['latitude']];
//                             }, $coordinates)],
//                         ],
//                         'properties' => [
//                             'farmer_uniqueId' => $farmer->farmer_uniqueId ?? "",
//                             'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid ?? "",
//                             'farmer_name' => $farmer->farmerapproved->farmer_name ?? "NA",
//                             'plot_no' => $farmer->plot_no ?? "",
//                             'ranges' => $farmer->ranges,
//                             'polygon_date_time' => $farmer->polygon_date_time ?? "",
//                             'date_time' => $farmer->date_time ?? "",
//                             'date_survey' => $farmer->date_survey ?? "",
//                             'surveyor_name' => $farmer->surveyor_name ?? "",
//                             'status' => $farmer->l2_status ?? "",
//                             'surveyor_mobile' => $farmer->surveyor_mobile ?? "",
//                             'latitude' => $farmer->latitude ?? "",
//                             'longitude' => $farmer->longitude ?? "",
//                             'state' => $farmer->farmerapproved->state->name ?? "",
//                             'district' => $farmer->farmerapproved->district->district ?? "",
//                             'taluka' => $farmer->farmerapproved->taluka->taluka ?? "",
//                             'village' => $farmer->farmerapproved->village->village ?? "",
//                             'area_in_hectare' => $farmer->farmerapproved->area_in_acers ?? "",
//                             'plot_area' => $farmer->plot_area ?? "",
//                             'validator_name' => $farmer->PolygonValidation->users->name ?? "NA",
//                             // dd($farmer),
//                         ],
//                     ];  
//                     // dd($feature);
            
//                     $geojson['features'][] = $feature;
//                 }
//             }
            
//         }

//     // Serialize GeoJSON to JSON
//     $json = json_encode($geojson);

//     // Define the directory and file path to save the GeoJSON data
//     $directory = public_path('geojson'); // Path to the directory where you want to save the file
//     if (!file_exists($directory)) {
//         mkdir($directory, 0755, true); // Create the directory if it doesn't exist
//     }
//     $filename = 'UK-polygon-geojson9.geojson';
//     $file_path = $directory . '/' . $filename;

//     // Save the GeoJSON data to the file
//     file_put_contents($file_path, $json);

//     // Check if the file was saved successfully
//     if (file_exists($file_path)) {
//         echo "GeoJSON data saved successfully to $file_path";
//     } else {
//         echo "Failed to save GeoJSON data";
//     }

//     return response()->json(['message' => "generated"]);
// }



public function genrate_geojson(Request $request)
{
    try {
        set_time_limit(-1);
        ini_set('memory_limit', '72000M');

        // Check if a file was uploaded
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');

        // Extract Farmer Unique IDs from the uploaded Excel file
        $farmerData = [];
        $data = Excel::toArray([], $file);
        foreach ($data[0] as $rowIndex => $row) {
            if ($rowIndex === 0) {
                continue;
            }
            $farmer = new FinalFarmer;
            $farmer->surveyor_id = 3783;
            $farmer->organization_id = 1;
            $farmer->farmer_survey_id = 3783;
            $farmer->farmer_name = $row[0];
            $farmer->total_plot_area = $row[1];
            $farmer->available_area = $row[1];
            $farmer->area_in_acers = $row[1];
            $farmer->plot_no = 1;
            $farmer->own_area_in_acres = $row[1];
            $farmer->plot_area = $row[8];
            $farmer->land_ownership = "Own";
            $farmer->actual_owner_name = $row[0];
            $farmer->final_status = "Approved";
            $farmer->onboard_completed = "Approved";
            $farmer->financial_year = "2025-2025";
            $farmer->season = "Kharif";
            $farmer->country_id = 101;

            $state = State::where('name', $row[2])->first();
            if (!$state) {
                $state = State::create(['name' => $row[2],"country_id"=>101,"status"=>1]);
            }
            $farmer->state_id = $state->id ?? null;

            $district = District::where('district', $row[3])->where('state_id', $farmer->state_id)->first();
            if (!$district) {
                $district = District::create(['district' => $row[3],"state_id"=>$farmer->state_id,"status"=>1]);
            }
            $farmer->district_id = $district->id ?? null;

            $taluka = Taluka::where('taluka', $row[4])->where('district_id', $farmer->district_id)->where('state_id', $farmer->state_id)->first();
            if (!$taluka) {
                $taluka = Taluka::create(['taluka' => $row[4],"district_id"=>$farmer->district_id,"state_id"=>$farmer->state_id,"status"=>1]);
            }
            $farmer->taluka_id = $taluka->id ?? null;

            $panchayat = Panchayat::where('panchayat', $row[5])->where('taluka_id', $farmer->taluka_id)->where('district_id', $farmer->district_id)->where('state_id', $farmer->state_id)->first();
            if (!$panchayat) {
                $panchayat = Panchayat::create(['panchayat' => $row[5],"taluka_id"=>$farmer->taluka_id,"district_id"=>$farmer->district_id,"state_id"=>$farmer->state_id,"status"=>1]);
            }
            $farmer->panchayat_id = $panchayat->id ?? null;

            $village = Village::where('village', $row[7])->where('panchayat_id', $farmer->panchayat_id)->where('taluka_id', $farmer->taluka_id)->where('district_id', $farmer->district_id)->where('state_id', $farmer->state_id)->first();
            if (!$village) {
                $village = Village::create(['village' => $row[7],"panchayat_id"=>$farmer->panchayat_id,"taluka_id"=>$farmer->taluka_id,"district_id"=>$farmer->district_id,"state_id"=>$farmer->state_id,"status"=>1]);
            }
            $farmer->village_id = $village->id ?? null;
            $farmer->save();
            $farmerId = $farmer->id+1000;
            $farmer->farmer_uniqueId = $farmerId;
            $farmer->farmer_plot_uniqueid = $farmerId."P1";
            $farmer->save();

            $FarmerPlot = new FarmerPlot;
            $FarmerPlot->farmer_id = $farmer->id;
            $FarmerPlot->farmer_uniqueId = $farmer->farmer_uniqueId;
            $FarmerPlot->farmer_plot_uniqueid = $farmer->farmer_plot_uniqueid;
            $FarmerPlot->plot_no = $farmer->plot_no;
            $FarmerPlot->area_in_acers = $farmer->area_in_acers;
            $FarmerPlot->daag_number = $row[9];
            $FarmerPlot->land_ownership = $farmer->land_ownership;
            $FarmerPlot->actual_owner_name = $farmer->actual_owner_name;
            $FarmerPlot->final_status = $farmer->final_status;
            $FarmerPlot->status = $farmer->final_status;
            $FarmerPlot->save();
        }
        echo "Success";
        // Code that might throw an exception
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Import failed',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }

}

public function genrate_geojson_data(Request $request)
{
    set_time_limit(-1);
    ini_set('memory_limit', '72000M');

    // Check if a file was uploaded
    if (!$request->hasFile('file')) {
        return response()->json(['message' => 'No file uploaded'], 400);
    }

    $file = $request->file('file');

    // Extract Farmer Unique IDs from the uploaded Excel file
    $farmerUniqueIds = [];
    $data = Excel::toArray([], $file);
    foreach ($data[0] as $rowIndex => $row) {
        if ($rowIndex === 0) {
            // Skip the header row
            continue;
        }
        $farmerUniqueIds[] = $row[3]; // Assuming column index 2 (3rd column)
    }

    // Initialize GeoJSON structure
    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [],
    ];
    $chunkSize = 100;

    foreach (array_chunk($farmerUniqueIds, $chunkSize) as $chunk) {
        $farmers_data = Polygon::whereIn('farmer_plot_uniqueid', $chunk)->get();

        // if ($farmers_data->isEmpty()) {
        //     $farmers_data = Polygon::whereIn('farmer_plot_uniqueid', $chunk)->get();
        // }

        foreach ($farmers_data as $farmer) {
            if ($farmer && $farmer->ranges) {
                $ranges = json_decode($farmer->ranges, true);
                $coordinates = [];
                foreach ($ranges as $range) {
                    $coordinates[] = [
                        'latitude' => $range['lat'],
                        'longitude' => $range['lng'],
                    ];
                }

                // Ensure the last coordinate matches the first coordinate
                if (!empty($coordinates)) {
                    $firstCoordinate = $coordinates[0];
                    $lastCoordinate = end($coordinates);
                    if ($firstCoordinate['latitude'] !== $lastCoordinate['latitude'] ||
                        $firstCoordinate['longitude'] !== $lastCoordinate['longitude']) {
                        $coordinates[] = $firstCoordinate;
                    }
                }

                // Construct GeoJSON feature
                $feature = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [array_map(function ($coord) {
                            return [$coord['longitude'], $coord['latitude']];
                        }, $coordinates)],
                    ],
                    'properties' => [
                        'farmer_uniqueId' => $farmer->farmer_uniqueId ?? "",
                        'farmer_plot_uniqueid' => $farmer->farmer_plot_uniqueid ?? "",
                        'farmer_name' => $farmer->farmerapproved->farmer_name ?? "NA",
                        'plot_no' => $farmer->plot_no ?? "",
                        'ranges' => $farmer->ranges,
                        'polygon_date_time' => $farmer->polygon_date_time ?? "",
                        'date_time' => $farmer->date_time ?? "",
                        'date_survey' => $farmer->date_survey ?? "",
                        'surveyor_name' => $farmer->surveyor_name ?? "",
                        'status' => $farmer->l2_status ?? "",
                        'surveyor_mobile' => $farmer->surveyor_mobile ?? "",
                        'latitude' => $farmer->latitude ?? "",
                        'longitude' => $farmer->longitude ?? "",
                        'state' => $farmer->state ?? "",
                        'district' => $farmer->district ?? "",
                        'taluka' => $farmer->taluka ?? "",
                        'village' => $farmer->village ?? "",
                        'area_in_acers' => $farmer->area_in_acers ?? "",
                        'plot_area' => $farmer->plot_area ?? "",
                        'validator_name' => $farmer->validator->name ?? "NA",
                    ],
                ];
                $geojson['features'][] = $feature;
            }
        }
    }

    // Save GeoJSON to file
    $directory = public_path('geojson');
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
    $filename = 'PNP-All-Organization-polygon-geojson.geojson';
    $file_path = $directory . '/' . $filename;
    file_put_contents($file_path, json_encode($geojson));

    // Check if saved successfully
    if (file_exists($file_path)) {
        return response()->json(['message' => "GeoJSON data saved successfully to $file_path"]);
    } else {
        return response()->json(['message' => "Failed to save GeoJSON data"], 500);
    }
}

    public function aeration_sheet(Request $request)
{
    set_time_limit(-1);
    ini_set('memory_limit', '64000M');

    $query = Aeration::with(['farmerapproved','PipeInstallation'])->where('aeration_no', $request->aeration_no)
                     ->whereHas('farmerapproved', function ($q) use ($request) {
                         $q->where('onboarding_form', 1);

                         if ($request->has('organization_id') && $request->organization_id) {
                             $q->where('organization_id', $request->organization_id);
                         }
                     });
                    //  dd($query);
                    

    if ($request->has('start_date') && $request->start_date) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $data = $query->get();
    // dd($data);
    // $sumPlotArea = [];
    // foreach ($data as $aeration) {
    //     $farmerUniqueId = $aeration->farmer_uniqueId;
    //     $plotArea = $aeration->PipeInstallation->sum('plot_area');
    //     $sumPlotArea[$farmerUniqueId] = $plotArea;
    // }

    // Export to Excel
    // return Excel::download(new ExportAeration($data), 'aeration_sheet.xlsx');

     // Specify the storage path
     $path = 'exports/aeration_sheet'.\Carbon\Carbon::now()->timestamp.'.xlsx';

     // Export and store the Excel file
     Excel::store(new ExportAeration($data), $path);
 
     // Get the URL of the stored file
     $url = Storage::url($path);
 
     // Return the URL
     return response()->json(['url' => $url]);
     
}


public function polygon_sheet(Request $request) 
{
    set_time_limit(-1);
    ini_set('memory_limit', '64000M');

    $query = Polygon::with(['farmerapproved','seasons','PolygonValidation'])->where('delete_polygon','!=','1')
                     ->whereHas('farmerapproved', function ($q) use ($request) {
                         $q->where('onboarding_form', 1);

                         if ($request->has('organization_id') && $request->organization_id) {
                             $q->where('organization_id', $request->organization_id);
                         }
                     });
                    //  dd($query);
                 
    if ($request->has('start_date') && $request->start_date) {
        $query->whereDate('polygon_date_time', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date) {
        $query->whereDate('polygon_date_time', '<=', $request->end_date);
    }
    $data = $query->get();

    // $data = [];
    // $query->chunk(1000, function ($chunkData) use (&$data) {
    //     foreach ($chunkData as $item) {
    //         $data[] = $item;
    //     }
    // });


     $path = 'exports/polygon_sheet'.\Carbon\Carbon::now()->timestamp.'.xlsx';
     Excel::store(new ExportPolygon($data), $path);
     $url = Storage::url($path);
     return response()->json(['url' => $url]);

}

public function pipe_sheet(Request $request) 
{
    // dd("in");
    set_time_limit(-1);
    ini_set('memory_limit', '64000M');

    $query = PipeInstallationPipeImg::with(['farmerapproved'])
                     ->whereHas('farmerapproved', function ($q) use ($request) {
                         $q->where('onboarding_form', 1);

                         if ($request->has('organization_id') && $request->organization_id) {
                             $q->where('organization_id', $request->organization_id);
                         }
                     });
                 
    if ($request->has('start_date') && $request->start_date) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $data = $query->get();

    // $sumPlotArea = [];
    // foreach ($data as $item) {
    //     $farmerUniqueId = $item->farmer_uniqueId;
    //     $plotArea = $item->sum('plot_area');
    //     $sumPlotArea[$farmerUniqueId] = $plotArea;
    // }

     $path = 'exports/pipe_sheet'.\Carbon\Carbon::now()->timestamp.'.xlsx';
     Excel::store(new ExportPipe($data), $path);
     $url = Storage::url($path);
     return response()->json(['url' => $url]);

}

public function onboarding_sheet(Request $request) 
{
    set_time_limit(-1);
    ini_set('memory_limit', '250000M');

    $query = FinalFarmer::with(['users','FinalUserApprovedRejected','validator'])
                            ->where('plot_no',1);
    if ($request->has('organization_id') && $request->organization_id) {
        $query->where('organization_id', $request->organization_id);
    }         
    if ($request->has('start_date') && $request->start_date) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->has('end_date') && $request->end_date) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $data = $query->latest()->get();

    // $sumPlotArea = [];
    // foreach ($data as $item) {
    //     $farmerUniqueId = $item->farmer_uniqueId;
    //     $plotArea = $item->sum('plot_area');
    //     $sumPlotArea[$farmerUniqueId] = $plotArea;
    // }

     $path = 'exports/onboarding_sheet'.\Carbon\Carbon::now()->timestamp.'.xlsx';
     Excel::store(new ExportOnboarding($data), $path);
     $url = Storage::url($path);
     return response()->json(['url' => $url]);

}


public function storeFarmerAwdData()
    {

        // $data = PipeInstallation::select('farmer_uniqueId', DB::raw('SUM(plot_area) as total_plot_area'))
        // // ->groupBy('farmer_uniqueId')
        // ->get();

        //     // Prepare data for insertion/update
        //     $values = [];
        //     foreach ($data as $entry) {
        //     $values[] = [
        //     'farmer_uniqueId' => $entry->farmer_uniqueId,
        //     'total_plot_area' => $entry->total_plot_area
        //     ];
        //     }

        //     // Insert data into FarmerAwd table using the "on duplicate key update" feature
        //     $sql = 'INSERT INTO farmer_awds (farmer_uniqueId, total_plot_area) VALUES ';
        //     $sql .= collect($values)->map(function ($value) {
        //     return '(' . $value['farmer_uniqueId'] . ', ' . $value['total_plot_area'] . ')';
        //     })->implode(', ');
        //     $sql .= ' ON DUPLICATE KEY UPDATE total_plot_area = VALUES(total_plot_area)';

        //     // Execute the SQL query
        //     DB::statement($sql);

        //     return response()->json(['message' => 'Data stored successfully']);

        
        // dd("in");
        PipeInstallation::select('farmer_uniqueId', 'plot_area')
        ->chunk(1000, function ($dataChunk) {
            // Process each chunk of data
            foreach ($dataChunk as $entry) {
                        $farmerUniqueId = $entry->farmer_uniqueId;
                        $plotArea = $entry->plot_area;

                        // Find existing record
                        $existingRecord = FarmerAwd::where('farmer_uniqueId', $farmerUniqueId)->first();

                        if ($existingRecord) {
                            $existingRecord->update(['total_plot_area' => $plotArea]);
                        } else {
                            // Create new record
                            FarmerAwd::create([
                                'farmer_uniqueId' => $farmerUniqueId,
                                'total_plot_area' => $plotArea
                            ]);
                        }
                    }
                });

        return response()->json(['message' => 'Data stored successfully']);

    }

    public function storeFarmerData(Request $request)
    {
        try {
            $data = Excel::toCollection(null, $request->file);

            $data[0]->chunk(1000, function ($rows) {
    
                foreach ($rows as $row) {
                    $company = Company::where('company', 'like', '%' . $row[0] . '%')->first();
        
                    if ($company) {
                        $farmer = FinalFarmer::where('farmer_uniqueId', $row[1])->first();
        
                        if ($farmer) {
                            $farmer->update(['organization_id' => $company->id]);
                        } else {
                            continue;
                        }
                    } else {
                    continue;
                    }
                }
            });
    
            return response()->json(['success' => true, 'message' => 'Data inserted successfully'], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
        }
    }
    
   
    public function upload_farmer(Request $request)
    {
        // Validate Excel file input
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid file. Allowed types: xlsx, xls, csv',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $sheets = Excel::toArray([], $file);
            foreach($sheets[0] as $rowIndex => $row){
                if ($rowIndex === 0) {
                    continue;
                }
                $farmer = new FinalFarmer;
                $farmer->surveyor_id = 3783;
                $farmer->organization_id = 1;
                $farmer->farmer_survey_id = 3783;
                $farmer->farmer_name = $row[0];
                $farmer->total_plot_area = $row[2];
                $farmer->available_area = (float)$row[2]-(float)$row[3];
                $farmer->area_in_acers = $row[2];
                $farmer->plot_no = 1;
                $farmer->own_area_in_acres = $row[2];
                $farmer->plot_area = $row[3];
                $farmer->land_ownership = "Own";
                $farmer->actual_owner_name = $row[0];
                $farmer->final_status = "Approved";
                $farmer->onboard_completed = "Approved";
                $farmer->financial_year = "2025-2025";
                $farmer->season = "Kharif";
                $farmer->country_id = 101;

                $farmer->state_id = 1;

                $district = District::whereRaw("CONVERT(`district` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$row[4]])->where('state_id', $farmer->state_id)->first();
                if (!$district) {
                    $district = District::create(['district' => $row[4],"state_id"=>$farmer->state_id,"status"=>1]);
                }
                $farmer->district_id = $district->id ?? null;

                $taluka = Taluka::whereRaw("CONVERT(`taluka` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$row[5]])->where('district_id', $farmer->district_id)->where('state_id', $farmer->state_id)->first();
                if (!$taluka) {
                    $taluka = Taluka::create(['taluka' => $row[5],"district_id"=>$farmer->district_id,"state_id"=>$farmer->state_id,"status"=>1]);
                }
                $farmer->taluka_id = $taluka->id ?? null;

                $panchayat = Panchayat::whereRaw("CONVERT(`panchayat` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$row[6]])->where('taluka_id', $farmer->taluka_id)->where('district_id', $farmer->district_id)->where('state_id', $farmer->state_id)->first();
                if (!$panchayat) {
                    $panchayat = Panchayat::create(['panchayat' => $row[6],"taluka_id"=>$farmer->taluka_id,"district_id"=>$farmer->district_id,"state_id"=>$farmer->state_id,"status"=>1]);
                }
                $farmer->panchayat_id = $panchayat->id ?? null;

                $village = Village::whereRaw("CONVERT(`village` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$row[7]])->where('panchayat_id', $farmer->panchayat_id)->where('taluka_id', $farmer->taluka_id)->where('district_id', $farmer->district_id)->where('state_id', $farmer->state_id)->first();
                if (!$village) {
                    $village = Village::create(['village' => $row[7],"panchayat_id"=>$farmer->panchayat_id,"taluka_id"=>$farmer->taluka_id,"district_id"=>$farmer->district_id,"state_id"=>$farmer->state_id,"status"=>1]);
                }
                $farmer->village_id = $village->id ?? null;
                $farmer->save();
                // Extract only digits from the cell value (e.g., C816544726 -> 816544726)
                $farmerId = preg_replace('/\D+/', '', (string) $row[1]);
                
                $farmer->farmer_uniqueId = $farmerId;
                $farmer->farmer_plot_uniqueid = $farmerId."P1";
                
                $farmer->final_status_onboarding = "Completed";
                $farmer->status_onboarding = "Completed";
                $farmer->onboarding_form = "1";
                $farmer->save();

                $FarmerPlot = new FarmerPlot;
                $FarmerPlot->farmer_id = $farmer->id;
                $FarmerPlot->farmer_uniqueId = $farmer->farmer_uniqueId;
                $FarmerPlot->farmer_plot_uniqueid = $farmer->farmer_plot_uniqueid;
                $FarmerPlot->plot_no = $farmer->plot_no;
                $FarmerPlot->area_in_acers = $row[3];
                $FarmerPlot->area_in_other = $row[3];
                $FarmerPlot->area_in_other_unit = $row[3];
                $FarmerPlot->area_acre_awd = $row[3];
                $FarmerPlot->area_other_awd = $row[3];
                $FarmerPlot->area_other_awd_unit = $row[3];
                // $FarmerPlot->daag_number = $row[9];
                $FarmerPlot->land_ownership = $farmer->land_ownership;
                $FarmerPlot->actual_owner_name = $farmer->actual_owner_name;
                $FarmerPlot->final_status = $farmer->final_status;
                $FarmerPlot->status = $farmer->final_status;

                $FarmerPlot->save();
                
            }




            return response()->json([
                'file_name'    => $file->getClientOriginalName(),
                // 'mime'         => $file->getMimeType(),
                // 'extension'    => $file->getClientOriginalExtension(),
                // 'sheets_count' => count($sheets),
                // // Preview first 10 rows of the first sheet
                // 'data_preview' => array_slice($sheets[0] ?? [], 0, 10),
            ]);
        } catch (\Throwable $e) {
            dd($e);
            return response()->json([
                'message' => 'Failed to read Excel file',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    public function upload_polygon(Request $request)
    {
        // Validate GeoJSON/JSON file input
        // $validator = Validator::make($request->all(), [
        //     'file' => 'required|file|mimes:json,geojson|mimetypes:application/json,application/geo+json,text/plain',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'message' => 'Invalid file. Allowed types: .geojson, .json',
        //         'errors'  => $validator->errors(),
        //     ], 422);
        // }

        try {
            $file = $request->file('file');
            $contents = file_get_contents($file->getRealPath());
            $json = json_decode($contents, true);

            if ($json === null) {
                return response()->json([
                    'message' => 'Invalid JSON/GeoJSON content',
                ], 400);
            }

            $featureCount = 0;
            if (isset($json['type']) && $json['type'] === 'FeatureCollection' && isset($json['features']) && is_array($json['features'])) {
                $featureCount = count($json['features']);
            }

            $extractedIds = [];
            $faranotfound = [];
            
            foreach (($json['features'] ?? []) as $index => $data) {
                $geometryType = $data['geometry']['type'] ?? null;
                $coordinates = $data['geometry']['coordinates'] ?? [];
                // Select the exterior ring depending on geometry type
                if ($geometryType === 'MultiPolygon') {
                    $ring = $coordinates[0][0] ?? [];
                } elseif ($geometryType === 'Polygon') {
                    $ring = $coordinates[0] ?? [];
                } else {
                    // Fallback: try to unwrap one extra nesting level if present
                    $ring = $coordinates;
                    if (isset($ring[0]) && is_array($ring[0]) && isset($ring[0][0]) && is_array($ring[0][0]) && isset($ring[0][0][0]) && is_numeric($ring[0][0][0])) {
                        $ring = $ring[0];
                    }
                }

                $latLngs = [];
                foreach ($ring as $point) {
                    if (is_array($point) && count($point) >= 2 && is_numeric($point[0]) && is_numeric($point[1])) {
                        $latLngs[] = ['lat' => (float) $point[1], 'lng' => (float) $point[0]];
                    }
                }
                $lat = $latLngs[0]['lat'] ?? null;
                $lng = $latLngs[0]['lng'] ?? null;
                // dd($lat,$lng,$latLngs,$ring);
                // \Log::info('First coordinate', ['lat' => $lat, 'lng' => $lng]);
                $rawLayer = $data['properties']['layer'] ?? '';
                if ($rawLayer !== '') {
                    // Keep only digits: e.g., "C887784347 — polygon list" -> "887784347"
                    $farmerId = preg_replace('/\D+/', '', (string) $rawLayer);
                    if ($farmerId !== '') {
                        $extractedIds[] = $farmerId;
                        $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerId)->first();
                        if($finalFarmer){
                            $farmerCount = FinalFarmer::where('farmer_uniqueId', $farmerId)->count();
                            
                            if($farmerCount > 1){
                                $farmerCount = $farmerCount+1;
                                
                                $plotNo = (string) $farmerCount;
                                $plotUniqueId = $farmerId . 'P' . $plotNo;

                                $targetFarmer = $finalFarmer->replicate();
                                $targetFarmer->plot_no = $plotNo;
                                $targetFarmer->farmer_plot_uniqueid = $plotUniqueId;
                                $targetFarmer->plot_area = $data["properties"]["Area_Ha"];
                                $targetFarmer->area_in_acers = $data["properties"]["Area_Ha"];
                                $targetFarmer->save();

                                $farmerPlot = FarmerPlot::where('farmer_uniqueId', $farmerId)->where('plot_no', 1)->first();
                                if($farmerPlot){
                                    $targetFarmerPlot = $farmerPlot->replicate();
                                    $targetFarmerPlot->farmer_id = $targetFarmer->id;
                                    $targetFarmerPlot->farmer_plot_uniqueid = $plotUniqueId;
                                    $targetFarmerPlot->plot_no = $plotNo;
                                    $targetFarmerPlot->area_in_acers = $data["properties"]["Area_Ha"];
                                    $targetFarmerPlot->save();
                                }
                                
                                $polygon = new Polygon;
                                $polygon->farmer_uniqueId = $farmerId;
                                $polygon->farmer_id = $finalFarmer->id;
                                $polygon->farmer_plot_uniqueid = $plotUniqueId;
                                $polygon->ranges = json_encode($latLngs);
                                $polygon->plot_no = $plotNo;
                                $polygon->final_status = "Approved";
                                $polygon->area_units = "Hectare";
                                $polygon->latitude = $lat;
                                $polygon->longitude = $lng;
                                $polygon->plot_area = $data["properties"]["Area_Ha"];
                                $polygon->save();
                            }else{
                                $polygon = new Polygon;
                                $polygon->farmer_uniqueId = $farmerId;
                                $polygon->farmer_id = $finalFarmer->id;
                                $polygon->farmer_plot_uniqueid = $farmerId."P1";
                                $polygon->ranges = json_encode($latLngs);
                                $polygon->plot_no = "1";
                                $polygon->final_status = "Approved";
                                $polygon->area_units = "Hectare";
                                $polygon->latitude = $lat;
                                $polygon->longitude = $lng;
                                $polygon->plot_area = $data["properties"]["Area_Ha"];
                                $polygon->save();
                            }
                        }
                    }
                }
            }

            return response()->json([
                'file_name'      => $file->getClientOriginalName()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to read GeoJSON file',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function upload_kml_polygons(Request $request)
    {
        set_time_limit(-1);
        ini_set('memory_limit', '64000M');

        // Validate KML upload
        // $validator = Validator::make($request->all(), [
        //     'file' => 'required|file|mimes:kml,xml|mimetypes:text/xml,application/xml,application/vnd.google-earth.kml+xml,text/plain',
        // ]);
        // if ($validator->fails()) {
        //     return response()->json([
        //         'message' => 'Invalid file. Allowed types: .kml, .xml',
        //         'errors'  => $validator->errors(),
        //     ], 422);
        // }

        try {
            $file = $request->file('file');
            $xml = @simplexml_load_file($file->getRealPath());
            if ($xml === false) {
                return response()->json(['message' => 'Unable to read KML/XML file'], 400);
            }

            // Namespace-safe XPath using local-name()
            $placemarks = $xml->xpath('//*[local-name()="Placemark"]') ?: [];

            $created = 0;
            $skippedNoFarmer = 0;
            $totalPolygons = 0;
            $errors = [];

            foreach ($placemarks as $placemark) {
                // 1) Extract farmer ID
                $nameNode = $placemark->xpath('.//*[local-name()="name"]');
                $name = isset($nameNode[0]) ? (string)$nameNode[0] : '';

                // Try ExtendedData Data/SimpleData fields that might hold id
                $idKeys = ['Client_ID','layer','farmer_uniqueId','FarmerId','Unique_Id','UniqueID','FID','id'];
                $candIds = [];
                foreach ($idKeys as $key) {
                    $v1 = $placemark->xpath('.//*[local-name()="ExtendedData"]//*[local-name()="Data"][@name="'.$key.'"]/*[local-name()="value"]');
                    if (isset($v1[0]) && trim((string)$v1[0]) !== '') $candIds[] = (string)$v1[0];

                    $v2 = $placemark->xpath('.//*[local-name()="ExtendedData"]//*[local-name()="SimpleData"][@name="'.$key.'"]');
                    if (isset($v2[0]) && trim((string)$v2[0]) !== '') $candIds[] = (string)$v2[0];
                }
                $rawId = $candIds[0] ?? $name;
                $farmerId = preg_replace('/\D+/', '', (string)$rawId);

                if (!$farmerId) {
                    $skippedNoFarmer++;
                    continue;
                }

                // 2) Extract area (hectares if present)
                $areaKeys = ['Area_Ha','AREA_HA','Area_ha','Area','HA','ha','Hectare','Hectares'];
                $areaHa = null;
                foreach ($areaKeys as $aKey) {
                    $a1 = $placemark->xpath('.//*[local-name()="ExtendedData"]//*[local-name()="Data"][@name="'.$aKey.'"]/*[local-name()="value"]');
                    if (isset($a1[0]) && is_numeric((string)$a1[0])) { $areaHa = (float)$a1[0]; break; }
                    $a2 = $placemark->xpath('.//*[local-name()="ExtendedData"]//*[local-name()="SimpleData"][@name="'.$aKey.'"]');
                    if (isset($a2[0]) && is_numeric((string)$a2[0])) { $areaHa = (float)$a2[0]; break; }
                }
                // Extract optional farmer and location metadata from KML ExtendedData
                $nameKeys = ['Farmer_nam','Farmer_name','FarmerName','Name'];
                $farmerName = null;
                foreach ($nameKeys as $nKey) {
                    $n1 = $placemark->xpath('.//*[local-name()="ExtendedData"]//*[local-name()="Data"][@name="'.$nKey.'"]/*[local-name()="value"]');
                    if (isset($n1[0]) && trim((string)$n1[0]) !== '') { $farmerName = trim((string)$n1[0]); break; }
                    $n2 = $placemark->xpath('.//*[local-name()="ExtendedData"]//*[local-name()="SimpleData"][@name="'.$nKey.'"]');
                    if (isset($n2[0]) && trim((string)$n2[0]) !== '') { $farmerName = trim((string)$n2[0]); break; }
                }

                $districtName = null; $blockName = null; $panchayatName = null; $villageName = null;
                $districtNodes = [
                    './/*[local-name()="ExtendedData"]//*[local-name()="Data"][@name="F_District"]/*[local-name()="value"]',
                    './/*[local-name()="ExtendedData"]//*[local-name()="SimpleData"][@name="F_District"]',
                ];
                foreach ($districtNodes as $xp) { $tmp = $placemark->xpath($xp); if (isset($tmp[0]) && trim((string)$tmp[0]) !== '') { $districtName = trim((string)$tmp[0]); break; } }
                $blockNodes = [
                    './/*[local-name()="ExtendedData"]//*[local-name()="Data"][@name="F_Block"]/*[local-name()="value"]',
                    './/*[local-name()="ExtendedData"]//*[local-name()="SimpleData"][@name="F_Block"]',
                ];
                foreach ($blockNodes as $xp) { $tmp = $placemark->xpath($xp); if (isset($tmp[0]) && trim((string)$tmp[0]) !== '') { $blockName = trim((string)$tmp[0]); break; } }
                $panchayatNodes = [
                    './/*[local-name()="ExtendedData"]//*[local-name()="Data"][@name="F_Gram_Pan"]/*[local-name()="value"]',
                    './/*[local-name()="ExtendedData"]//*[local-name()="SimpleData"][@name="F_Gram_Pan"]',
                ];
                foreach ($panchayatNodes as $xp) { $tmp = $placemark->xpath($xp); if (isset($tmp[0]) && trim((string)$tmp[0]) !== '') { $panchayatName = trim((string)$tmp[0]); break; } }
                $villageNodes = [
                    './/*[local-name()="ExtendedData"]//*[local-name()="Data"][@name="F_Village"]/*[local-name()="value"]',
                    './/*[local-name()="ExtendedData"]//*[local-name()="SimpleData"][@name="F_Village"]',
                ];
                foreach ($villageNodes as $xp) { $tmp = $placemark->xpath($xp); if (isset($tmp[0]) && trim((string)$tmp[0]) !== '') { $villageName = trim((string)$tmp[0]); break; } }

                // 3) Collect all polygons under this Placemark (MultiGeometry or single Polygon)
                $coordNodes = $placemark->xpath('.//*[local-name()="Polygon"]//*[local-name()="outerBoundaryIs"]//*[local-name()="LinearRing"]//*[local-name()="coordinates"]') ?: [];

                foreach ($coordNodes as $coordNode) {
                    
                    $coordsStr = trim((string)$coordNode);
                    if ($coordsStr === '') continue;

                    $points = preg_split('/\s+/', $coordsStr);
                    $latLngs = [];
                    foreach ($points as $pt) {
                        $parts = explode(',', trim($pt));
                        if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                            $latLngs[] = ['lat' => (float)$parts[1], 'lng' => (float)$parts[0]];
                        }
                    }
                    // Optional: print coordinates in [lon, lat] associative format if requested
                    if (!empty($latLngs) && $request->has('print_coords')) {
                        $lonLatPairs = array_map(function($p){ return ['lon' => $p['lng'], 'lat' => $p['lat']]; }, $latLngs);
                        return response()->json(['coordinates' => $lonLatPairs]);
                    }
                    if (empty($latLngs)) continue;

                    $lat = $latLngs[0]['lat'] ?? null;
                    $lng = $latLngs[0]['lng'] ?? null;

                    $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerId)->first();
                    if (!$finalFarmer) {
                        // Create default FinalFarmer and P1 plot if not found
                        $finalFarmer = new FinalFarmer;
                        $finalFarmer->surveyor_id = 3783;
                        $finalFarmer->organization_id = 1;
                        $finalFarmer->farmer_survey_id = 3783;
                        $finalFarmer->farmer_name = $farmerName ?: ('Farmer ' . $farmerId);
                        $finalFarmer->total_plot_area = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->available_area = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->area_in_acers = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->plot_no = 1;
                        $finalFarmer->own_area_in_acres = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->plot_area = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->land_ownership = "Own";
                        $finalFarmer->actual_owner_name = $finalFarmer->farmer_name;
                        $finalFarmer->final_status = "Approved";
                        $finalFarmer->onboard_completed = "Approved";
                        $finalFarmer->financial_year = "2025-2025";
                        $finalFarmer->season = "Kharif";
                        $finalFarmer->country_id = 101;
                        // State and location hierarchy similar to upload_farmer
                        $finalFarmer->state_id = 1;
                        if (!empty($districtName)) {
                            $district = District::whereRaw("CONVERT(`district` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$districtName])
                                ->where('state_id', $finalFarmer->state_id)->first();
                            if (!$district) {
                                $district = District::create(['district' => $districtName, 'state_id' => $finalFarmer->state_id, 'status' => 1]);
                            }
                            $finalFarmer->district_id = $district->id ?? null;
                        }
                        if (!empty($blockName) && !empty($finalFarmer->district_id)) {
                            $taluka = Taluka::whereRaw("CONVERT(`taluka` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$blockName])
                                ->where('district_id', $finalFarmer->district_id)->where('state_id', $finalFarmer->state_id)->first();
                            if (!$taluka) {
                                $taluka = Taluka::create(['taluka' => $blockName, 'district_id' => $finalFarmer->district_id, 'state_id' => $finalFarmer->state_id, 'status' => 1]);
                            }
                            $finalFarmer->taluka_id = $taluka->id ?? null;
                        }
                        if (!empty($panchayatName) && !empty($finalFarmer->taluka_id) && !empty($finalFarmer->district_id)) {
                            $panchayat = Panchayat::whereRaw("CONVERT(`panchayat` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$panchayatName])
                                ->where('taluka_id', $finalFarmer->taluka_id)
                                ->where('district_id', $finalFarmer->district_id)
                                ->where('state_id', $finalFarmer->state_id)->first();
                            if (!$panchayat) {
                                $panchayat = Panchayat::create([
                                    'panchayat' => $panchayatName,
                                    'taluka_id' => $finalFarmer->taluka_id,
                                    'district_id' => $finalFarmer->district_id,
                                    'state_id' => $finalFarmer->state_id,
                                    'status' => 1
                                ]);
                            }
                            $finalFarmer->panchayat_id = $panchayat->id ?? null;
                        }
                        if (!empty($villageName) && !empty($finalFarmer->panchayat_id) && !empty($finalFarmer->taluka_id) && !empty($finalFarmer->district_id)) {
                            $village = Village::whereRaw("CONVERT(`village` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$villageName])
                                ->where('panchayat_id', $finalFarmer->panchayat_id)
                                ->where('taluka_id', $finalFarmer->taluka_id)
                                ->where('district_id', $finalFarmer->district_id)
                                ->where('state_id', $finalFarmer->state_id)->first();
                            if (!$village) {
                                $village = Village::create([
                                    'village' => $villageName,
                                    'panchayat_id' => $finalFarmer->panchayat_id,
                                    'taluka_id' => $finalFarmer->taluka_id,
                                    'district_id' => $finalFarmer->district_id,
                                    'state_id' => $finalFarmer->state_id,
                                    'status' => 1
                                ]);
                            }
                            $finalFarmer->village_id = $village->id ?? null;
                        }
                        // Optional onboarding flags to align with existing flows
                        $finalFarmer->final_status_onboarding = "Completed";
                        $finalFarmer->status_onboarding = "Completed";
                        $finalFarmer->onboarding_form = "1";
                        $finalFarmer->save();

                        // Assign unique IDs
                        $finalFarmer->farmer_uniqueId = $farmerId;
                        $finalFarmer->farmer_plot_uniqueid = $farmerId . 'P1';
                        $finalFarmer->save();

                        // Create P1 FarmerPlot
                        $p1Plot = new FarmerPlot;
                        $p1Plot->farmer_id = $finalFarmer->id;
                        $p1Plot->farmer_uniqueId = $farmerId;
                        $p1Plot->farmer_plot_uniqueid = $farmerId . 'P1';
                        $p1Plot->plot_no = 1;
                        $p1Plot->area_in_acers = $areaHa !== null ? $areaHa : 0;
                        $p1Plot->area_in_other = $areaHa !== null ? $areaHa : 0;
                        $p1Plot->area_in_other_unit = $areaHa !== null ? $areaHa : 0;
                        $p1Plot->area_acre_awd = $areaHa !== null ? $areaHa : 0;
                        $p1Plot->area_other_awd = $areaHa !== null ? $areaHa : 0;
                        $p1Plot->area_other_awd_unit = $areaHa !== null ? $areaHa : 0;
                        $p1Plot->land_ownership = $finalFarmer->land_ownership;
                        $p1Plot->actual_owner_name = $finalFarmer->actual_owner_name;
                        $p1Plot->final_status = $finalFarmer->final_status;
                        $p1Plot->status = $finalFarmer->final_status;
                        $p1Plot->save();
                    }

                    // Determine whether to attach to P1 or create a new plot by replicating farmer & plot
                    $farmerCount = FinalFarmer::where('farmer_uniqueId', $farmerId)->count();

                    if ($farmerCount > 1) {
                        // Create new plot
                        $plotNo = (string)($farmerCount + 1);
                        $plotUniqueId = $farmerId . 'P' . $plotNo;

                        // Replicate FinalFarmer
                        $targetFarmer = $finalFarmer->replicate();
                        $targetFarmer->plot_no = $plotNo;
                        $targetFarmer->farmer_plot_uniqueid = $plotUniqueId;
                        if ($areaHa !== null) {
                            $targetFarmer->plot_area = $areaHa;
                            $targetFarmer->area_in_acers = $areaHa;
                        }
                        $targetFarmer->save();

                        // Replicate or create FarmerPlot
                        $basePlot = FarmerPlot::where('farmer_uniqueId', $farmerId)->where('plot_no', 1)->first();
                        if ($basePlot) {
                            $targetFarmerPlot = $basePlot->replicate();
                            $targetFarmerPlot->farmer_id = $targetFarmer->id;
                            $targetFarmerPlot->farmer_plot_uniqueid = $plotUniqueId;
                            $targetFarmerPlot->plot_no = $plotNo;
                            if ($areaHa !== null) {
                                $targetFarmerPlot->area_in_acers = $areaHa;
                                $targetFarmerPlot->area_in_other = $areaHa;
                                $targetFarmerPlot->area_in_other_unit = $areaHa;
                                $targetFarmerPlot->area_acre_awd = $areaHa;
                                $targetFarmerPlot->area_other_awd = $areaHa;
                                $targetFarmerPlot->area_other_awd_unit = $areaHa;
                            }
                            $targetFarmerPlot->save();
                        } else {
                            $newPlot = new FarmerPlot;
                            $newPlot->farmer_id = $targetFarmer->id;
                            $newPlot->farmer_uniqueId = $farmerId;
                            $newPlot->farmer_plot_uniqueid = $plotUniqueId;
                            $newPlot->plot_no = $plotNo;
                            if ($areaHa !== null) {
                                $newPlot->area_in_acers = $areaHa;
                                $newPlot->area_in_other = $areaHa;
                                $newPlot->area_in_other_unit = $areaHa;
                                $newPlot->area_acre_awd = $areaHa;
                                $newPlot->area_other_awd = $areaHa;
                                $newPlot->area_other_awd_unit = $areaHa;
                            }
                            $newPlot->land_ownership = $finalFarmer->land_ownership;
                            $newPlot->actual_owner_name = $finalFarmer->actual_owner_name;
                            $newPlot->final_status = $finalFarmer->final_status;
                            $newPlot->status = $finalFarmer->final_status;
                            $newPlot->save();
                        }

                        // Save Polygon
                        $polygon = new Polygon;
                        $polygon->farmer_uniqueId = $farmerId;
                        $polygon->farmer_id = $targetFarmer->id;
                        $polygon->farmer_plot_uniqueid = $plotUniqueId;
                        $polygon->ranges = json_encode($latLngs);
                        $polygon->plot_no = $plotNo;
                        $polygon->final_status = "Approved";
                        $polygon->area_units = "Hectare";
                        $polygon->latitude = $lat;
                        $polygon->longitude = $lng;
                        if ($areaHa !== null) $polygon->plot_area = $areaHa;
                        $polygon->save();
                    } else {
                        // Attach to P1
                        $polygon = new Polygon;
                        $polygon->farmer_uniqueId = $farmerId;
                        $polygon->farmer_id = $finalFarmer->id;
                        $polygon->farmer_plot_uniqueid = $farmerId . "P1";
                        $polygon->ranges = json_encode($latLngs);
                        $polygon->plot_no = "1";
                        $polygon->final_status = "Approved";
                        $polygon->area_units = "Hectare";
                        $polygon->latitude = $lat;
                        $polygon->longitude = $lng;
                        if ($areaHa !== null) $polygon->plot_area = $areaHa;
                        $polygon->save();

                        // Ensure FarmerPlot P1 exists and sync area if provided
                        $p1 = FarmerPlot::where('farmer_uniqueId', $farmerId)->where('plot_no', 1)->first();
                        if (!$p1) {
                            $p1 = new FarmerPlot;
                            $p1->farmer_id = $finalFarmer->id;
                            $p1->farmer_uniqueId = $farmerId;
                            $p1->farmer_plot_uniqueid = $farmerId . "P1";
                            $p1->plot_no = 1;
                            $p1->land_ownership = $finalFarmer->land_ownership;
                            $p1->actual_owner_name = $finalFarmer->actual_owner_name;
                            $p1->final_status = $finalFarmer->final_status;
                            $p1->status = $finalFarmer->final_status;
                        }
                        if ($areaHa !== null) {
                            $p1->area_in_acers = $areaHa;
                            $p1->area_in_other = $areaHa;
                            $p1->area_in_other_unit = $areaHa;
                            $p1->area_acre_awd = $areaHa;
                            $p1->area_other_awd = $areaHa;
                            $p1->area_other_awd_unit = $areaHa;
                        }
                        $p1->save();
                    }

                    $created++;
                    $totalPolygons++;
                }
            }

            return response()->json([
                'file_name' => $file->getClientOriginalName(),
                'polygons_imported' => $created,
                'skipped_no_farmer' => $skippedNoFarmer,
                'total_polygons_seen' => $totalPolygons,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to read KML file',
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }
    
    public function update_location_data(){
        $data = $this->update_location();
        $created = 0;
        $skippedNoFarmer = 0;
        $totalPolygons = 0;
        $errors = [];
        
        foreach($data['features'] as $feature){
            try {
                $farmer = $feature['properties']['extendedData'];
                // dd($farmer);
                // Extract farmer ID from Final_Kopa (remove 'C' prefix)
                $farmerId = str_replace('C', '', $farmer['Client_ID'] ?? '');
                if (!$farmerId) {
                    $skippedNoFarmer++;
                    continue;
                }
                
                // Extract area (convert to hectares if needed)
                $areaHa = isset($farmer['area']) && is_numeric($farmer['area']) ? (float)$farmer['area'] : null;
                $areaHaQgis = isset($farmer['Qgis_Area']) && is_numeric($farmer['Qgis_Area']) ? (float)$farmer['Qgis_Area'] : null;
                
                // Extract farmer name
                $farmerName = $farmer['F_client_l'] ?? ('Farmer ' . $farmerId);
                
                // Extract location data
                $districtName = $farmer['District'] ?? null;
                $blockName = $farmer['Block'] ?? null;
                $panchayatName = $farmer['Gram_Panch'] ?? null;
                $villageName = $farmer['Village'] ?? null;
                
                // Extract coordinates
                if (!isset($feature['geometry']['coordinates'])) {
                    $feature['geometry']['coordinates'] = [['lat' => 00.000000001, 'lng' => 00.000000001]];
                }
                
                $coordinates = $feature['geometry']['coordinates'] ?? null;
                $lat = $coordinates[0][0]["lat"] ?? null;
                $lng = $coordinates[0][0]["lng"] ?? null;
                
                // Convert coordinates to latLngs array format
                $latLngs = [];
                if (isset($coordinates[0]) && is_array($coordinates[0])) {
                    foreach ($coordinates[0] as $point) {
                        if (isset($point['lat']) && isset($point['lng'])) {
                            $latLngs[] = ['lat' => (float)$point['lat'], 'lng' => (float)$point['lng']];
                        }
                    }
                }
                
                if (empty($latLngs)) {
                    $errors[] = "No valid coordinates found for farmer ID: " . $farmerId;
                }
                // Find or create FinalFarmer
                $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerId)->latest()->first();
                if (!$finalFarmer) {
                    $plotNo = 1;
                    $finalFarmer = $this->create_farmer($farmerName, $areaHa, $areaHaQgis, 1, $farmerId,$feature);
                    
                    $this->create_farmer_plot($finalFarmer, $farmerId, $areaHa, $areaHaQgis, 1);
                    
                }else{
                    $plotNo = $finalFarmer->plot_no + 1;
                    $finalFarmer = $this->create_farmer($farmerName, $areaHa, $areaHaQgis, $plotNo, $farmerId,$feature);
                    
                    $this->create_farmer_plot($finalFarmer, $farmerId, $areaHa, $areaHaQgis, $plotNo);
                }
                $finalFarmer = $this->updateLocationData($finalFarmer,$feature);
                
                // Create Polygon
                $polygon = new Polygon;
                $polygon->farmer_uniqueId = $farmerId;
                $polygon->farmer_id = $finalFarmer->id;
                $polygon->farmer_plot_uniqueid = $farmerId . "P".$plotNo;
                $polygon->ranges = json_encode($latLngs);
                $polygon->plot_no = $plotNo;
                $polygon->final_status = "Approved";
                $polygon->area_units = "Hectare";
                $polygon->latitude = $lat;
                $polygon->longitude = $lng;
                if ($areaHa !== null) $polygon->plot_area = $areaHa;
                $polygon->save();
                
                $created++;
                $totalPolygons++;
                
            } catch (\Throwable $e) {
                $errors[] = "Error processing farmer ID " . ($farmerId ?? 'unknown') . ": " . $e->getMessage();
            }
        }
        
        return response()->json([
            'message' => 'Location data updated successfully',
            'polygons_imported' => $created,
            'skipped_no_farmer' => $skippedNoFarmer,
            'total_polygons_seen' => $totalPolygons,
            'errors' => $errors
        ]);
    }

    public function updateLocationData($finalFarmer,$feature){
        $districtName = $feature['properties']['extendedData']['District'] ?? null;
        $blockName = $feature['properties']['extendedData']['Block'] ?? null;
        $panchayatName = $feature['properties']['extendedData']['Gram_Panch'] ?? null;
        $villageName = $feature['properties']['extendedData']['Village'] ?? null;
        if (!empty($districtName)) {
            $district = District::whereRaw("CONVERT(`district` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$districtName])
                ->where('state_id', $finalFarmer->state_id)->first();
            if (!$district) {
                $district = District::create(['district' => $districtName, 'state_id' => $finalFarmer->state_id, 'status' => 1]);
            }
            $finalFarmer->district_id = $district->id ?? null;
        }
        if (!empty($blockName) && !empty($finalFarmer->district_id)) {
            $taluka = Taluka::whereRaw("CONVERT(`taluka` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$blockName])
                ->where('district_id', $finalFarmer->district_id)->where('state_id', $finalFarmer->state_id)->first();
            if (!$taluka) {
                $taluka = Taluka::create(['taluka' => $blockName, 'district_id' => $finalFarmer->district_id, 'state_id' => $finalFarmer->state_id, 'status' => 1]);
            }
            $finalFarmer->taluka_id = $taluka->id ?? null;
        }
        if (!empty($panchayatName) && !empty($finalFarmer->taluka_id) && !empty($finalFarmer->district_id)) {
            $panchayat = Panchayat::whereRaw("CONVERT(`panchayat` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$panchayatName])
                ->where('taluka_id', $finalFarmer->taluka_id)
                ->where('district_id', $finalFarmer->district_id)
                ->where('state_id', $finalFarmer->state_id)->first();
            if (!$panchayat) {
                $panchayat = Panchayat::create([
                    'panchayat' => $panchayatName,
                    'taluka_id' => $finalFarmer->taluka_id,
                    'district_id' => $finalFarmer->district_id,
                    'state_id' => $finalFarmer->state_id,
                    'status' => 1
                ]);
            }
            $finalFarmer->panchayat_id = $panchayat->id ?? null;
        }
        if (!empty($villageName) && !empty($finalFarmer->panchayat_id) && !empty($finalFarmer->taluka_id) && !empty($finalFarmer->district_id)) {
            $village = Village::whereRaw("CONVERT(`village` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$villageName])
                ->where('panchayat_id', $finalFarmer->panchayat_id)
                ->where('taluka_id', $finalFarmer->taluka_id)
                ->where('district_id', $finalFarmer->district_id)
                ->where('state_id', $finalFarmer->state_id)->first();
            if (!$village) {
                $village = Village::create([
                    'village' => $villageName,
                    'panchayat_id' => $finalFarmer->panchayat_id,
                    'taluka_id' => $finalFarmer->taluka_id,
                    'district_id' => $finalFarmer->district_id,
                    'state_id' => $finalFarmer->state_id,
                    'status' => 1
                ]);
            }
            $finalFarmer->village_id = $village->id ?? null;
        }
        $finalFarmer->save();
        return $finalFarmer;
    }

    public function create_farmer_plot($finalFarmer, $farmerId, $areaHa, $areaHaQgis, $plotNo){
        // Create P1 FarmerPlot
        $p1Plot = new FarmerPlot;
        $p1Plot->farmer_id = $finalFarmer->id;
        $p1Plot->farmer_uniqueId = $farmerId;
        $p1Plot->farmer_plot_uniqueid = $farmerId . 'P'.$plotNo;
        $p1Plot->plot_no = $plotNo;
        $p1Plot->area_in_acers = $areaHaQgis !== null ? $areaHaQgis : 0;
        $p1Plot->area_in_other = $areaHaQgis !== null ? $areaHaQgis : 0;
        $p1Plot->area_in_other_unit = $areaHaQgis !== null ? $areaHaQgis : 0;
        $p1Plot->area_acre_awd = $areaHaQgis !== null ? $areaHaQgis : 0;
        $p1Plot->area_other_awd = $areaHaQgis !== null ? $areaHaQgis : 0;
        $p1Plot->area_other_awd_unit = $areaHaQgis !== null ? $areaHaQgis : 0;
        $p1Plot->land_ownership = $finalFarmer->land_ownership;
        $p1Plot->actual_owner_name = $finalFarmer->actual_owner_name;
        $p1Plot->final_status = $finalFarmer->final_status;
        $p1Plot->status = $finalFarmer->final_status;
        $p1Plot->save();
    }

    function create_farmer($farmerName, $areaHa, $areaHaQgis, $plotNo, $farmerId,$feature){
        // Create new FinalFarmer with basic data
        $finalFarmer = new FinalFarmer;
        $finalFarmer->surveyor_id = 3783;
        $finalFarmer->organization_id = 2;
        $finalFarmer->farmer_survey_id = 3783;
        $finalFarmer->farmer_name = $farmerName;
        $finalFarmer->total_plot_area = $areaHa !== null ? $areaHa : 0;
        $finalFarmer->available_area = $areaHa !== null ? $areaHa : 0;
        $finalFarmer->area_in_acers = $areaHa !== null ? $areaHa : 0;
        $finalFarmer->plot_no = $plotNo;
        $finalFarmer->own_area_in_acres = $areaHa !== null ? $areaHa : 0;
        $finalFarmer->plot_area = $areaHaQgis !== null ? $areaHaQgis : 0;
        $finalFarmer->land_ownership = "Own";
        $finalFarmer->actual_owner_name = $farmerName;
        $finalFarmer->final_status = "Approved";
        $finalFarmer->onboard_completed = "Approved";
        $finalFarmer->financial_year = "2025-2025";
        $finalFarmer->season = "Kharif";
        $finalFarmer->country_id = 101;
        $finalFarmer->state_id = 1;
        $finalFarmer->final_status_onboarding = "Completed";
        $finalFarmer->status_onboarding = "Completed";
        $finalFarmer->onboarding_form = "1";
        $finalFarmer->extra_data = json_encode($feature);
        $finalFarmer->save();

        // Assign unique IDs
        $finalFarmer->farmer_uniqueId = $farmerId;
        $finalFarmer->farmer_plot_uniqueid = $farmerId . 'P'.$plotNo;
        $finalFarmer->save();
        return $finalFarmer;
    }
    public function update_location(){
    }

    public function upload_excel_data(Request $request){
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $excel_file = $request->file('excel_file');
        $excel_file_name = $excel_file->getClientOriginalName();
        $excel_file_extension = $excel_file->getClientOriginalExtension();
        $excel_file_size = $excel_file->getSize();

        try {
            // Read Excel file directly without storing
            $data = Excel::toArray([], $excel_file);
            
            $allData = [];
            $sheetNames = [];
            
            // Loop through all sheets (assuming 2 sheets)
            foreach ($data as $sheetIndex => $sheetData) {
                foreach ($sheetData as $index => $row) {
                    if($index == 0){
                        continue;
                    }
                    $State = $row[0];
                    $District = $row[1];
                    $Block = $row[2];
                    $Gram_Panchayat = $row[3];
                    $Village_Name = $row[4];
                    
                    $district = District::where(['district' => $District, 'state_id' => 1, 'status' => 1])->first();
                    if(empty($district)){
                        $district = District::create(['district' => $District, 'state_id' => 1, 'status' => 1]);
                    }

                    $taluka = Taluka::where(['taluka' => $Block, 'district_id' => $district->id, 'state_id' => 1, 'status' => 1])->first();
                    if(empty($taluka)){
                        $taluka = Taluka::create(['taluka' => $Block, 'district_id' => $district->id, 'state_id' => 1, 'status' => 1]);
                    }
                            
                       
                    $panchayat = Panchayat::where(['panchayat' => $Gram_Panchayat, 'taluka_id' => $taluka->id, 'district_id' => $district->id, 'state_id' => 1, 'status' => 1])->first();
                    if(empty($panchayat)){
                        $panchayat = Panchayat::create([
                            'panchayat' => $Gram_Panchayat,
                            'taluka_id' => $taluka->id,
                            'district_id' => $district->id,
                            'state_id' => 1,
                            'status' => 1
                        ]);
                    }
                    
                    $village = Village::where(['village' => $Village_Name, 'panchayat_id' => $panchayat->id, 'taluka_id' => $taluka->id, 'district_id' => $district->id, 'state_id' => 1])->first();
                    if(empty($village)){
                        $village = Village::create([
                            'village' => $Village_Name,
                            'panchayat_id' => $panchayat->id,
                            'taluka_id' => $taluka->id,
                            'district_id' => $district->id,
                            'state_id' => 1
                        ]);
                    }
                }
            }
             
            
            return response()->json([
                'success' => true
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing Excel file: ' . $e->getMessage()
            ], 500);
        }
    }

}