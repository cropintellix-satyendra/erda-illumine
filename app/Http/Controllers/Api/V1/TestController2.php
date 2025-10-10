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
use Illuminate\Support\Facades\Log;
// use App\Models\Panchayat;
// use App\Models\Village;
// use App\Models\FarmerPlot;


use Illuminate\Support\Facades\Storage as FacadesStorage;
// use Maatwebsite\Excel\Excel as ExcelExcel;

// use Maatwebsite\Excel\Facades\Excel;

class TestController2 extends Controller
{


    public function genrate_geojson(Request $request){
        try {
            $polygons = Polygon::select('id', 'ranges')->get();
        
        $analysis = [
            'total_polygons' => $polygons->count(),
            'valid_polygons' => 0,
            'invalid_polygons' => 0,
            'empty_ranges' => 0,
            'invalid_coordinates' => 0,
            'swapped_coordinates' => 0,
            'invalid_format' => 0,
            'coordinate_format_issues' => 0,
            'bounds_issues' => 0,
            'polygon_details' => [],
            'coordinate_analysis' => [],
            'sample_data' => []
        ];
        
        $sampleCount = 0;
        $maxSampleSize = 10; // Show only first 10 samples for detailed analysis
        
        foreach ($polygons as $polygon) {
            $rawRanges = $polygon->ranges;
            $decodedRanges = $this->decodeRanges($rawRanges);
            $validation = $this->validateRanges($decodedRanges);
            
            // Detailed coordinate analysis
            $coordinateAnalysis = [
                'polygon_id' => $polygon->id,
                'raw_ranges_type' => gettype($rawRanges),
                'raw_ranges_length' => is_string($rawRanges) ? strlen($rawRanges) : 'N/A',
                'decoded_type' => gettype($decodedRanges),
                'decoded_count' => is_array($decodedRanges) ? count($decodedRanges) : 0,
                'coordinate_format' => 'unknown',
                'bounds' => null,
                'issues' => [],
                'sample_coordinates' => []
            ];
            
            if (is_array($decodedRanges) && !empty($decodedRanges)) {
                // Check coordinate format
                $firstCoord = $decodedRanges[0];
                if (is_array($firstCoord)) {
                    if (count($firstCoord) >= 2) {
                        $coordinateAnalysis['coordinate_format'] = 'array_format';
                        $coordinateAnalysis['sample_coordinates'] = array_slice($decodedRanges, 0, 3); // First 3 coordinates
                        
                        // Check bounds - safely extract coordinates
                        $lats = [];
                        $lngs = [];
                        
                        foreach ($decodedRanges as $coord) {
                            if (is_array($coord) && count($coord) >= 2) {
                                $lngs[] = floatval($coord[0]);
                                $lats[] = floatval($coord[1]);
                            }
                        }
                        
                        if (!empty($lats) && !empty($lngs)) {
                            $coordinateAnalysis['bounds'] = [
                                'min_lat' => min($lats),
                                'max_lat' => max($lats),
                                'min_lng' => min($lngs),
                                'max_lng' => max($lngs),
                                'lat_range' => max($lats) - min($lats),
                                'lng_range' => max($lngs) - min($lngs)
                            ];
                            
                            // Check India bounds
                            if (min($lats) < 6 || max($lats) > 37) {
                                $coordinateAnalysis['issues'][] = 'Latitude out of India bounds (6-37)';
                                $analysis['bounds_issues']++;
                            }
                            if (min($lngs) < 68 || max($lngs) > 97) {
                                $coordinateAnalysis['issues'][] = 'Longitude out of India bounds (68-97)';
                                $analysis['bounds_issues']++;
                            }
                            
                            // Check for potential coordinate swap
                            if (min($lats) > 68 && max($lats) < 97 && min($lngs) > 6 && max($lngs) < 37) {
                                $coordinateAnalysis['issues'][] = 'Potential coordinate swap detected';
                                $analysis['coordinate_format_issues']++;
                            }
                        } else {
                            $coordinateAnalysis['issues'][] = 'No valid coordinates found for bounds calculation';
                            $coordinateAnalysis['bounds'] = null;
                        }
                        
                    } else {
                        $coordinateAnalysis['issues'][] = 'Insufficient coordinate points';
                        $analysis['coordinate_format_issues']++;
                    }
                } else {
                    $coordinateAnalysis['issues'][] = 'Invalid coordinate structure';
                    $analysis['coordinate_format_issues']++;
                }
            } else {
                $coordinateAnalysis['issues'][] = 'Empty or invalid ranges';
            }
            
            $polygonDetail = [
                'polygon_id' => $polygon->id,
                'is_valid' => $validation['valid'],
                'reason' => $validation['reason'],
                'ranges_count' => is_array($decodedRanges) ? count($decodedRanges) : 0,
                'coordinate_analysis' => $coordinateAnalysis
            ];
            
            if ($validation['valid']) {
                $analysis['valid_polygons']++;
            } else {
                $analysis['invalid_polygons']++;
                
                // Categorize invalid polygons
                switch ($validation['reason']) {
                    case 'Ranges is empty or not an array':
                        $analysis['empty_ranges']++;
                        break;
                    case 'lat/lng values are swapped - lat should be 6-37, lng should be 68-97 for India':
                        $analysis['swapped_coordinates']++;
                        break;
                    case 'lat/lng values must be numeric':
                    case 'Coordinate point is not an array':
                    case 'Missing lat/lng keys in coordinate point':
                        $analysis['invalid_coordinates']++;
                        break;
                    default:
                        $analysis['invalid_format']++;
                        break;
                }
            }
            
            $analysis['polygon_details'][] = $polygonDetail;
            
            // Add sample data for first few polygons
            if ($sampleCount < $maxSampleSize) {
                $analysis['sample_data'][] = [
                    'polygon_id' => $polygon->id,
                    'raw_ranges' => $rawRanges,
                    'decoded_ranges' => $decodedRanges,
                    'validation' => $validation,
                    'coordinate_analysis' => $coordinateAnalysis
                ];
                $sampleCount++;
            }
        }
        
        // Calculate additional statistics
        $analysis['statistics'] = [
            'total_polygons' => $analysis['total_polygons'],
            'valid_percentage' => $analysis['total_polygons'] > 0 ? 
                round(($analysis['valid_polygons'] / $analysis['total_polygons']) * 100, 2) : 0,
            'invalid_percentage' => $analysis['total_polygons'] > 0 ? 
                round(($analysis['invalid_polygons'] / $analysis['total_polygons']) * 100, 2) : 0,
            'empty_ranges_percentage' => $analysis['total_polygons'] > 0 ? 
                round(($analysis['empty_ranges'] / $analysis['total_polygons']) * 100, 2) : 0,
            'swapped_coordinates_percentage' => $analysis['total_polygons'] > 0 ? 
                round(($analysis['swapped_coordinates'] / $analysis['total_polygons']) * 100, 2) : 0,
            'bounds_issues_percentage' => $analysis['total_polygons'] > 0 ? 
                round(($analysis['bounds_issues'] / $analysis['total_polygons']) * 100, 2) : 0,
        ];
        
        // Recommendations
        $analysis['recommendations'] = [];
        if ($analysis['swapped_coordinates'] > 0) {
            $analysis['recommendations'][] = 'Consider running coordinate swap fix for ' . $analysis['swapped_coordinates'] . ' polygons';
        }
        if ($analysis['bounds_issues'] > 0) {
            $analysis['recommendations'][] = 'Check coordinate bounds for ' . $analysis['bounds_issues'] . ' polygons - they may be outside India';
        }
        if ($analysis['empty_ranges'] > 0) {
            $analysis['recommendations'][] = 'Review and fix ' . $analysis['empty_ranges'] . ' polygons with empty ranges';
        }
        if ($analysis['coordinate_format_issues'] > 0) {
            $analysis['recommendations'][] = 'Investigate coordinate format issues in ' . $analysis['coordinate_format_issues'] . ' polygons';
        }
        
        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'summary' => $analysis['statistics'],
            'recommendations' => $analysis['recommendations'],
            'debug_info' => [
                'sample_data_count' => count($analysis['sample_data']),
                'total_analysis_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
                'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
            ]
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing polygons: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    // Detailed analysis for specific polygon
    public function analyze_specific_polygon(Request $request)
    {
        $polygonId = $request->input('polygon_id');
        
        if (!$polygonId) {
            return response()->json([
                'success' => false,
                'message' => 'Polygon ID is required'
            ], 400);
        }
        
        $polygon = Polygon::select('id', 'ranges')->where('id', $polygonId)->first();
        
        if (!$polygon) {
            return response()->json([
                'success' => false,
                'message' => 'Polygon not found'
            ], 404);
        }
        
        $rawRanges = $polygon->ranges;
        $decodedRanges = $this->decodeRanges($rawRanges);
        $validation = $this->validateRanges($decodedRanges);
        
        // Detailed analysis
        $analysis = [
            'polygon_id' => $polygon->id,
            'raw_data' => [
                'raw_ranges' => $rawRanges,
                'raw_type' => gettype($rawRanges),
                'raw_length' => is_string($rawRanges) ? strlen($rawRanges) : 'N/A',
                'is_json_valid' => json_last_error() === JSON_ERROR_NONE
            ],
            'decoded_data' => [
                'decoded_ranges' => $decodedRanges,
                'decoded_type' => gettype($decodedRanges),
                'decoded_count' => is_array($decodedRanges) ? count($decodedRanges) : 0,
                'is_array' => is_array($decodedRanges),
                'is_empty' => empty($decodedRanges)
            ],
            'validation' => $validation,
            'coordinate_analysis' => [],
            'geojson_preview' => null,
            'issues' => [],
            'recommendations' => []
        ];
        
        if (is_array($decodedRanges) && !empty($decodedRanges)) {
            // Analyze each coordinate
            $coordinates = [];
            $lats = [];
            $lngs = [];
            
            foreach ($decodedRanges as $index => $point) {
                if (is_array($point) && count($point) >= 2) {
                    $lng = floatval($point[0]);
                    $lat = floatval($point[1]);
                    
                    $coordinates[] = [
                        'index' => $index,
                        'lng' => $lng,
                        'lat' => $lat,
                        'is_valid_lng' => ($lng >= 68 && $lng <= 97),
                        'is_valid_lat' => ($lat >= 6 && $lat <= 37),
                        'is_swapped' => ($lng >= 6 && $lng <= 37 && $lat >= 68 && $lat <= 97)
                    ];
                    
                    $lats[] = $lat;
                    $lngs[] = $lng;
                }
            }
            
            $bounds = null;
            if (!empty($lats) && !empty($lngs)) {
                $bounds = [
                    'min_lat' => min($lats),
                    'max_lat' => max($lats),
                    'min_lng' => min($lngs),
                    'max_lng' => max($lngs),
                    'lat_range' => max($lats) - min($lats),
                    'lng_range' => max($lngs) - min($lngs)
                ];
            }
            
            $analysis['coordinate_analysis'] = [
                'total_coordinates' => count($coordinates),
                'coordinates' => $coordinates,
                'bounds' => $bounds,
                'statistics' => [
                    'valid_lat_count' => count(array_filter($coordinates, function($c) { return $c['is_valid_lat']; })),
                    'valid_lng_count' => count(array_filter($coordinates, function($c) { return $c['is_valid_lng']; })),
                    'swapped_count' => count(array_filter($coordinates, function($c) { return $c['is_swapped']; }))
                ]
            ];
            
            // Generate GeoJSON preview
            if (count($coordinates) >= 3) {
                $analysis['geojson_preview'] = [
                    'type' => 'Feature',
                    'properties' => [
                        'polygon_id' => $polygon->id,
                        'coordinate_count' => count($coordinates)
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [array_map(function($c) {
                            return [$c['lng'], $c['lat']];
                        }, $coordinates)]
                    ]
                ];
            }
            
            // Identify issues
            if (count($coordinates) < 3) {
                $analysis['issues'][] = 'Insufficient coordinates for polygon (minimum 3 required)';
            }
            
            $swappedCount = $analysis['coordinate_analysis']['statistics']['swapped_count'];
            if ($swappedCount > 0) {
                $analysis['issues'][] = "Potential coordinate swap detected in $swappedCount coordinates";
                $analysis['recommendations'][] = 'Consider swapping lat/lng values';
            }
            
            $validLatCount = $analysis['coordinate_analysis']['statistics']['valid_lat_count'];
            $validLngCount = $analysis['coordinate_analysis']['statistics']['valid_lng_count'];
            
            if ($validLatCount < count($coordinates)) {
                $analysis['issues'][] = 'Some latitude values are outside India bounds (6-37)';
            }
            
            if ($validLngCount < count($coordinates)) {
                $analysis['issues'][] = 'Some longitude values are outside India bounds (68-97)';
            }
            
            if (empty($analysis['issues'])) {
                $analysis['recommendations'][] = 'Polygon appears to be valid for map display';
            }
        } else {
            $analysis['issues'][] = 'Empty or invalid coordinate data';
            $analysis['recommendations'][] = 'Check polygon data source and format';
        }
        
        return response()->json([
            'success' => true,
            'analysis' => $analysis
        ]);
    }

    // Debug function to check polygon visibility issues
    public function debug_polygon_visibility(Request $request)
    {
        $polygons = Polygon::select('id', 'ranges')->limit(5)->get();
        
        $debugData = [
            'sample_polygons' => [],
            'coordinate_analysis' => [],
            'geojson_structure' => []
        ];
        
        foreach ($polygons as $polygon) {
            $rawRanges = $polygon->ranges;
            $decodedRanges = $this->decodeRanges($rawRanges);
            
            // Check coordinate format
            $coordinateAnalysis = [
                'polygon_id' => $polygon->id,
                'raw_ranges' => $rawRanges,
                'decoded_ranges' => $decodedRanges,
                'coordinate_format' => 'unknown',
                'bounds' => null,
                'issues' => []
            ];
            
            if (is_array($decodedRanges) && !empty($decodedRanges)) {
                // Check if coordinates are in [lng, lat] format (GeoJSON standard)
                $firstCoord = $decodedRanges[0];
                if (is_array($firstCoord) && count($firstCoord) >= 2) {
                    $coordinateAnalysis['coordinate_format'] = 'array_format';
                    
                    // Check bounds
                    $lats = array_column($decodedRanges, 1);
                    $lngs = array_column($decodedRanges, 0);
                    
                    $coordinateAnalysis['bounds'] = [
                        'min_lat' => min($lats),
                        'max_lat' => max($lats),
                        'min_lng' => min($lngs),
                        'max_lng' => max($lngs)
                    ];
                    
                    // Check if coordinates are within India bounds
                    if (min($lats) < 6 || max($lats) > 37) {
                        $coordinateAnalysis['issues'][] = 'Latitude out of India bounds (6-37)';
                    }
                    if (min($lngs) < 68 || max($lngs) > 97) {
                        $coordinateAnalysis['issues'][] = 'Longitude out of India bounds (68-97)';
                    }
                } else {
                    $coordinateAnalysis['issues'][] = 'Invalid coordinate structure';
                }
            } else {
                $coordinateAnalysis['issues'][] = 'Empty or invalid ranges';
            }
            
            $debugData['coordinate_analysis'][] = $coordinateAnalysis;
            
            // Generate GeoJSON structure for this polygon
            if (is_array($decodedRanges) && !empty($decodedRanges)) {
                $geojsonFeature = [
                    'type' => 'Feature',
                    'properties' => [
                        'polygon_id' => $polygon->id
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [$decodedRanges]
                    ]
                ];
                
                $debugData['geojson_structure'][] = $geojsonFeature;
            }
        }
        
        return response()->json($debugData);
    }

    // Generate proper GeoJSON for map display
    public function generate_map_geojson(Request $request)
    {
        $polygons = Polygon::select('id', 'ranges')->get();
        
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => []
        ];
        
        $validPolygons = 0;
        $invalidPolygons = 0;
        
        foreach ($polygons as $polygon) {
            $rawRanges = $polygon->ranges;
            $decodedRanges = $this->decodeRanges($rawRanges);
            
            if (is_array($decodedRanges) && !empty($decodedRanges)) {
                // Convert to proper GeoJSON format
                $coordinates = [];
                
                foreach ($decodedRanges as $point) {
                    if (is_array($point) && count($point) >= 2) {
                        // Ensure coordinates are in [lng, lat] format
                        $coordinates[] = [
                            floatval($point[0]), // longitude
                            floatval($point[1])  // latitude
                        ];
                    }
                }
                
                if (count($coordinates) >= 3) { // Minimum 3 points for polygon
                    $feature = [
                        'type' => 'Feature',
                        'properties' => [
                            'polygon_id' => $polygon->id,
                            'point_count' => count($coordinates)
                        ],
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [$coordinates]
                        ]
                    ];
                    
                    $geojson['features'][] = $feature;
                    $validPolygons++;
                } else {
                    $invalidPolygons++;
                }
            } else {
                $invalidPolygons++;
            }
        }
        
        return response()->json([
            'success' => true,
            'geojson' => $geojson,
            'statistics' => [
                'total_polygons' => $polygons->count(),
                'valid_polygons' => $validPolygons,
                'invalid_polygons' => $invalidPolygons,
                'features_count' => count($geojson['features'])
            ]
        ]);
    }

    public function genrate_geojson_update_polygon(Request $request){
        $polygons = Polygon::select('id', 'ranges')->get();

        $invalidPolygons = [];
        $limit = (int) $request->input('limit', 100);
        $limit = $limit > 0 ? min($limit, 1000) : 100;

        foreach ($polygons as $polygon) {
            $rawRanges = $polygon->ranges;
            $decodedRanges = $this->decodeRanges($rawRanges);
            $validation = $this->validateRanges($decodedRanges);

            if (!$validation['valid']) {
                $rangesToUpdate = [];
                $actualRanges = json_decode($rawRanges);
                foreach($actualRanges as $range){
                    $rangesToUpdate[] = [
                        'lat' => $range->lng,
                        'lng' => $range->lat,
                    ];
                }
                $polygon->ranges = json_encode($rangesToUpdate,true);
                $polygon->new_backup_data = $rawRanges;
                $polygon->save();
                // dd($polygon);
                $invalidPolygons[] = [
                    'polygon_id' => $polygon->id,
                    'raw_ranges' => $rawRanges,
                    'parsed_ranges' => $decodedRanges,
                    'reason' => $validation['reason'],
                ];

                if (count($invalidPolygons) >= $limit) {
                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'total_polygons' => $polygons->count(),
            'invalid_count' => count($invalidPolygons),
            'invalid_polygons' => $invalidPolygons,
        ]);
    }

    private function decodeRanges($rawRanges)
    {
        if (is_null($rawRanges) || $rawRanges === '') {
            return null;
        }

        if (is_array($rawRanges)) {
            return $rawRanges;
        }

        if (is_string($rawRanges)) {
            $trimmed = trim($rawRanges);

            if ($trimmed === '') {
                return null;
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $rawRanges;
    }

    private function validateRanges($ranges)
    {
        if (!is_array($ranges) || empty($ranges)) {
            return [
                'valid' => false,
                'reason' => 'Ranges is empty or not an array',
            ];
        }

        foreach ($ranges as $point) {
            if (!is_array($point)) {
                return [
                    'valid' => false,
                    'reason' => 'Coordinate point is not an array',
                ];
            }

            $hasLatLngKeys = array_key_exists('lat', $point) && array_key_exists('lng', $point);

            if ($hasLatLngKeys) {
                if (!is_numeric($point['lat']) || !is_numeric($point['lng'])) {
                    return [
                        'valid' => false,
                        'reason' => 'lat/lng values must be numeric',
                    ];
                }

                // Check if lat/lng values are swapped (lat > 50 means it's actually longitude)
                $lat = (float) $point['lat'];
                $lng = (float) $point['lng'];
                
                if ($lat > 50 || $lng < 50) {
                    return [
                        'valid' => false,
                        'reason' => 'lat/lng values are swapped - lat should be 6-37, lng should be 68-97 for India',
                    ];
                }

                continue;
            }

            return [
                'valid' => false,
                'reason' => 'Missing lat/lng keys in coordinate point',
            ];
        }

        return [
            'valid' => true,
            'reason' => null,
        ];
    }

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
    
    public function genrate_geojson_cccddss_ss(Request $request){
        try {
            
            set_time_limit(0);
            ini_set('memory_limit', '72000M');
            ini_set('max_execution_time', 0);
            ini_set('max_input_time', -1);
            
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            
            if (!headers_sent()) {
                header('X-Accel-Buffering: no'); // Disable nginx buffering
            }

            // Load existing GeoJSON file instead of processing KML
            $filename = 'geojson_data_2025-09-30_10-56-59.json';
            $geojsonPath = storage_path('app/public/geojson/' . $filename);
            $processedDataPath = storage_path('app/public/geojson/processed_data_2025-09-30_10-56-59.json');

            if (!file_exists($geojsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GeoJSON file not found: ' . $filename,
                    'file_path' => $geojsonPath
                ], 404);
            }

            // Load GeoJSON data
            $geojsonContent = file_get_contents($geojsonPath);
            $geojsonData = json_decode($geojsonContent, true);

            if (!$geojsonData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid GeoJSON file format'
                ], 422);
            }

            // Load processed data if available
            $processedData = [];
            if (file_exists($processedDataPath)) {
                $processedContent = file_get_contents($processedDataPath);
                $processedData = json_decode($processedContent, true);
            }

            // Extract unique farmers from GeoJSON
            $uniqueFarmers = [];
            foreach ($geojsonData['features'] as $feature) {
                $properties = $feature['properties'];
                $clientId = $properties['client_id'] ?? null;
                $farmerName = $properties['farmer_name'] ?? null;
                
                if ($clientId) {
                    $uniqueFarmers[$clientId] = $farmerName;
                }
            }

            // Check existing polygons based on coordinates and process data
            $existingPolygonsCount = 0;
            $newPolygonsCount = 0;
            $polygonDetails = [];
            $processedResults = [];
            DB::beginTransaction();
            foreach ($geojsonData['features'] as $index => $feature) {
                $coordinates = $feature['geometry']['coordinates'][0] ?? [];
                $properties = $feature['properties'];
                
                if (!empty($coordinates)) {
                    // Get first coordinate for comparison
                    $firstCoord = $coordinates[0];
                    $lng = $firstCoord[0];
                    $lat = $firstCoord[1];
                    
                    // Check if polygon exists with similar coordinates (within small range)
                    $existingPolygon = DB::table('polygons')
                        ->whereRaw("JSON_EXTRACT(ranges, '$[0].lat') BETWEEN ? AND ?", [$lat - 0.0001, $lat + 0.0001])
                        ->whereRaw("JSON_EXTRACT(ranges, '$[0].lng') BETWEEN ? AND ?", [$lng - 0.0001, $lng + 0.0001])
                        ->first();
                    
                    $clientId = $properties['client_id'] ?? null;
                    $farmerName = $properties['farmer_name'] ?? null;
                    $plotArea = $properties['plot_area'] ?? 0;
                    $plotArea = round($plotArea, 2);
                    $totalAreaInAcres = $properties['total_area_inacres'] ?? 0;
                    $totalAreaInAcres = round($totalAreaInAcres, 2);
                    $surveyorName = $properties['surveyor_name'] ?? null;
                    
                    if ($existingPolygon) {
                        // Update existing data
                        $existingPolygonsCount++;
                        
                        // Update final_farmers table
                        DB::table('final_farmers')
                            ->where('id', $existingPolygon->farmer_id)
                            ->update([
                                'farmer_name' => $farmerName,
                                'plot_area' => $plotArea,
                                'area_in_acers' => $totalAreaInAcres,
                                'surveyor_name' => $surveyorName,
                                'updated_at' => now()
                            ]);
                        
                        // Update 	farmer_plot_detail table
                        DB::table('farmer_plot_detail')
                            ->where('farmer_id', $existingPolygon->farmer_id)
                            ->update([
                                'area_in_acers' => $totalAreaInAcres,
                                'updated_at' => now()
                            ]);
                        
                        // Update polygons table
                        DB::table('polygons')
                            ->where('id', $existingPolygon->id)
                            ->update([
                                'ranges' => json_encode($coordinates),
                                'latitude' => $lat,
                                'longitude' => $lng,
                                'plot_area' => $plotArea,
                                'updated_at' => now()
                            ]);
                        
                        $status = 'updated';
                        $action = 'Updated existing records';
                        
                    } else {
                        // Create new data
                        $newPolygonsCount++;
                        
                        // Get farmer count for plot number
                        $farmerCount = DB::table('final_farmers')->count();
                        $plotNo = $farmerCount > 0 ? $farmerCount + 1 : 1;
                        $surveyorId = User::where('name',$surveyorName)->first()->id;
                        // Create final_farmers record
                        $farmerId = Finalfarmer::where('farmer_uniqueId',$clientId)->first();
                        if(!$farmerId){
                            $farmerId = Finalfarmer();
                            $farmerId->surveyor_id = $surveyorId;
                            $farmerId->surveyor_name = $surveyorName;
                            $farmerId->surveyor_email = $surveyorEmail;
                            $farmerId->surveyor_mobile = $surveyorMobile;
                            $farmerId->farmer_uniqueId = $clientId;
                            $farmerId->farmer_plot_uniqueid = $clientId.'P'.$plotNo;
                            $farmerId->farmer_name = $farmerName;
                            $farmerId->plot_area = $plotArea;
                            $farmerId->plot_no = $plotNo;
                            $farmerId->area_in_acers = $totalAreaInAcres;
                            $farmerId->surveyor_name = $surveyorName;
                            $farmerId->latitude = $lat;
                            $farmerId->longitude = $lng;

                            $farmerId->status_onboarding = 'Approved';
                            $farmerId->final_status_onboarding = 'Approved';
                            $farmerId->onboarding_form = 1;
                            $farmerId->final_status = 'Approved';
                            $farmerId->mobile_access = 'Own Number';
                            $farmerId->mobile_reln_owner = 'NA';
                            $farmerId->country_id = '91';
                            $farmerId->country = 'India';
                            $farmerId->state_id = '1';
                            $farmerId->district_id = '2';
                            $farmerId->organization_id = '2';
                            $farmerId->save();
                        }else{
                            $farmerId = $farmerId->replicate();
                            $farmerId->plot_no = $plotNo;
                            $farmerId->plot_area = $plotArea;
                            $farmerId->area_in_acers = $totalAreaInAcres;
                            $farmerId->surveyor_name = $surveyorName;
                            $farmerId->latitude = $lat;
                            $farmerId->longitude = $lng;
                            $farmerId->save();
                        }
                        
                        $farmerPlotId = FarmerPlot::where('farmer_id',$farmerId->id)->first();
                        if(!$farmerPlotId){
                            $farmerPlotId = new FarmerPlot();
                            $farmerPlotId->farmer_id = $farmerId->id;
                            $farmerPlotId->farmer_uniqueId = $clientId;
                            $farmerPlotId->plot_no = $plotNo;
                            $farmerPlotId->area_in_acers = $totalAreaInAcres;
                            $farmerPlotId->save();
                        }else{
                            $farmerPlotId = $farmerPlotId->replicate();
                            $farmerPlotId->farmer_id = $farmerId->id;
                            $farmerPlotId->farmer_uniqueId = $clientId;
                            $farmerPlotId->plot_no = $plotNo;
                            $farmerPlotId->area_in_acers = $totalAreaInAcres;
                            $farmerPlotId->save();
                        }

                        $polygonId = Polygon::where('farmer_id',$farmerId->id)->first();
                        if(!$polygonId){
                            $polygonId = new Polygon();
                            $polygonId->farmer_id = $farmerId->id;
                            $polygonId->farmer_plot_uniqueid = $clientId.'P'.$plotNo;
                            $polygonId->ranges = json_encode($coordinates);
                            $polygonId->latitude = $lat;
                            $polygonId->longitude = $lng;
                            $polygonId->plot_area = $plotArea;
                            $polygonId->surveyor_id = $surveyorId;
                            $polygonId->final_status = 'Approved';
                            $polygonId->save();
                        }else{
                            $polygonId = $polygonId->replicate();
                            $polygonId->farmer_id = $farmerId->id;
                            $polygonId->farmer_plot_uniqueid = $clientId.'P'.$plotNo;
                            $polygonId->ranges = json_encode($coordinates);
                            $polygonId->latitude = $lat;
                            $polygonId->longitude = $lng;
                            $polygonId->plot_area = $plotArea;
                            $polygonId->surveyor_id = $surveyorId;
                            $polygonId->final_status = 'Approved';
                            $polygonId->save();
                        }

                        $status = 'created';
                        $action = 'Created new records';
                    }
                    
                    $polygonDetail = [
                        'feature_index' => $index,
                        'object_id' => $properties['object_id'] ?? null,
                        'client_id' => $clientId,
                        'farmer_name' => $farmerName,
                        'coordinates' => $coordinates,
                        'first_coordinate' => ['lat' => $lat, 'lng' => $lng],
                        'status' => $status,
                        'action' => $action,
                        'existing_polygon_id' => $existingPolygon ? $existingPolygon->id : null,
                        'farmer_id' => $farmerId ?? null,
                        'farmer_plot_id' => $farmerPlotId ?? null,
                        'polygon_id' => $polygonId ?? null
                    ];
                    
                    $polygonDetails[] = $polygonDetail;
                    $processedResults[] = $polygonDetail;
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Polygon coordinates analysis and database processing completed successfully',
                'analysis_report' => [
                    'summary' => [
                        'total_geojson_features' => count($geojsonData['features']),
                        'processed_features' => count($processedResults),
                        'updated_records' => $existingPolygonsCount,
                        'created_records' => $newPolygonsCount
                    ],
                    'database_analysis' => [
                        'polygons' => [
                            'by_coordinates' => [
                                'existing_count' => $existingPolygonsCount,
                                'new_count' => $newPolygonsCount,
                                'polygon_details' => $polygonDetails
                            ]
                        ]
                    ],
                    'processing_results' => [
                        'total_processed' => count($processedResults),
                        'updated_farmers' => $existingPolygonsCount,
                        'created_farmers' => $newPolygonsCount,
                        'details' => $processedResults
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'GeoJSON analysis failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get comprehensive analysis report (farmers, pipes, aerations, surveyors)
     */
    public function get_comprehensive_analysis()
    {
        try {
            // Load existing GeoJSON file
            $filename = 'geojson_data_2025-09-30_10-56-59.json';
            $geojsonPath = storage_path('app/public/geojson/' . $filename);
            $processedDataPath = storage_path('app/public/geojson/processed_data_2025-09-30_10-56-59.json');

            if (!file_exists($geojsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GeoJSON file not found: ' . $filename
                ], 404);
            }

            // Load GeoJSON data
            $geojsonContent = file_get_contents($geojsonPath);
            $geojsonData = json_decode($geojsonContent, true);

            if (!$geojsonData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid GeoJSON file format'
                ], 422);
            }

            // Load processed data if available
            $processedData = [];
            if (file_exists($processedDataPath)) {
                $processedContent = file_get_contents($processedDataPath);
                $processedData = json_decode($processedContent, true);
            }

            // Extract unique farmers from GeoJSON
            $uniqueFarmers = [];
            foreach ($geojsonData['features'] as $feature) {
                $properties = $feature['properties'];
                $clientId = $properties['client_id'] ?? null;
                $farmerName = $properties['farmer_name'] ?? null;
                
                if ($clientId) {
                    $uniqueFarmers[$clientId] = $farmerName;
                }
            }

            // Check existing farmers in database
            $existingFarmers = [];
            if (!empty($uniqueFarmers)) {
                $clientIds = array_keys($uniqueFarmers);
                $existingFarmers = DB::table('final_farmers')
                    ->whereIn('farmer_uniqueId', $clientIds)
                    ->select('farmer_uniqueId', 'farmer_name')
                    ->get()
                    ->pluck('farmer_name', 'farmer_uniqueId')
                    ->toArray();
            }

            // Check pipe_installation_pipeimg records
            $pipeInstallationCount = DB::table('pipe_installation_pipeimg')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $pipeInstallationByPlotCount = DB::table('pipe_installation_pipeimg')
                ->whereIn('farmer_plot_uniqueId', array_column($processedData, 'client_id'))
                ->count();
            
            // Check aerations records
            $aerationCount = DB::table('aerations')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $aerationByPlotCount = DB::table('aerations')
                ->whereIn('farmer_plot_uniqueId', array_column($processedData, 'client_id'))
                ->count();
            
            // Check existing polygons based on farmer_uniqueId
            $existingPolygonsByFarmerCount = DB::table('polygons')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $newPolygonsByFarmerCount = count($uniqueFarmers) - $existingPolygonsByFarmerCount;
            
            // Check surveyor names in users table
            $surveyorNames = [];
            foreach ($geojsonData['features'] as $feature) {
                $surveyorName = $feature['properties']['surveyor_name'] ?? '';
                if (!empty(trim($surveyorName))) {
                    $surveyorNames[] = trim($surveyorName);
                }
            }
            $surveyorNames = array_unique($surveyorNames);
            
            $foundSurveyors = 0;
            $notFoundSurveyors = 0;
            $createdSurveyors = [];
            $failedSurveyors = [];
            
            if (!empty($surveyorNames)) {
                $existingSurveyors = DB::table('users')
                    ->whereIn('name', $surveyorNames)
                    ->pluck('name')
                    ->toArray();
                
                $foundSurveyors = count($existingSurveyors);
                $notFoundSurveyors = count($surveyorNames) - $foundSurveyors;
                
                // Create missing surveyors
                foreach ($surveyorNames as $surveyorName) {
                    if (!in_array($surveyorName, $existingSurveyors)) {
                        try {
                            // Generate unique email for surveyor
                            $email = strtolower(str_replace(' ', '.', $surveyorName)) . '@surveyor.local';
                            
                            // Check if email already exists
                            $existingUser = DB::table('users')->where('email', $email)->first();
                            if ($existingUser) {
                                $email = strtolower(str_replace(' ', '.', $surveyorName)) . '_' . time() . '@surveyor.local';
                            }
                            
                            // Create surveyor user
                            $userId = DB::table('users')->insertGetId([
                                'name' => $surveyorName,
                                'email' => $email,
                                'password' => bcrypt('password123'), // Default password
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            
                            if ($userId) {
                                $createdSurveyors[] = [
                                    'id' => $userId,
                                    'name' => $surveyorName,
                                    'email' => $email,
                                    'status' => 'created'
                                ];
                                
                                // Update found surveyors count
                                $foundSurveyors++;
                                $notFoundSurveyors--;
                            }
                            
                        } catch (\Exception $e) {
                            $failedSurveyors[] = [
                                'name' => $surveyorName,
                                'error' => $e->getMessage()
                            ];
                        }
                    }
                }
            }

            // Generate comprehensive analysis report
            $analysisReport = [
                'file_info' => [
                    'loaded_file' => $filename,
                    'file_path' => $geojsonPath,
                    'file_size' => filesize($geojsonPath) . ' bytes',
                    'processed_data_available' => file_exists($processedDataPath),
                    'processed_data_path' => $processedDataPath,
                    'load_timestamp' => date('Y-m-d H:i:s')
                ],
                'summary' => [
                    'total_geojson_features' => count($geojsonData['features']),
                    'unique_farmers_in_file' => count($uniqueFarmers),
                    'existing_farmers_in_database' => count($existingFarmers),
                    'new_farmers_to_add' => count($uniqueFarmers) - count($existingFarmers)
                ],
                'database_analysis' => [
                    'farmers' => [
                        'existing_count' => count($existingFarmers),
                        'new_count' => count($uniqueFarmers) - count($existingFarmers),
                        'total_in_file' => count($uniqueFarmers),
                        'existing_farmers' => $existingFarmers
                    ],
                    'pipe_installations' => [
                        'by_farmer_uniqueId' => $pipeInstallationCount,
                        'by_farmer_plot_uniqueId' => $pipeInstallationByPlotCount
                    ],
                    'aerations' => [
                        'by_farmer_uniqueId' => $aerationCount,
                        'by_farmer_plot_uniqueId' => $aerationByPlotCount
                    ],
                    'polygons' => [
                        'by_farmer_uniqueId' => [
                            'existing_count' => $existingPolygonsByFarmerCount,
                            'new_count' => $newPolygonsByFarmerCount,
                            'total_farmers_checked' => count($uniqueFarmers)
                        ]
                    ],
                    'surveyors' => [
                        'found_in_users_table' => $foundSurveyors,
                        'not_found_in_users_table' => $notFoundSurveyors,
                        'total_in_file' => count($surveyorNames),
                        'created_surveyors' => $createdSurveyors,
                        'failed_surveyors' => $failedSurveyors
                    ]
                ],
                'recommendations' => [
                    'data_import' => count($uniqueFarmers) - count($existingFarmers) > 0 ? 
                        'Import ' . (count($uniqueFarmers) - count($existingFarmers)) . ' new farmers' : 
                        'No new farmers to import',
                    'surveyor_setup' => count($createdSurveyors) > 0 ? 
                        'Created ' . count($createdSurveyors) . ' new surveyor accounts' : 
                        'All surveyors are already in the system',
                    'surveyor_issues' => count($failedSurveyors) > 0 ? 
                        'Failed to create ' . count($failedSurveyors) . ' surveyor accounts - check logs' : 
                        'All surveyors created successfully'
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Comprehensive analysis report generated successfully',
                'analysis_report' => $analysisReport
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Comprehensive analysis failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get detailed surveyor analysis from GeoJSON file
     */
    public function get_surveyor_analysis()
    {
        try {
            // Load existing GeoJSON file
            $filename = 'geojson_data_2025-09-30_10-56-59.json';
            $geojsonPath = storage_path('app/public/geojson/' . $filename);

            if (!file_exists($geojsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GeoJSON file not found: ' . $filename
                ], 404);
            }

            // Load GeoJSON data
            $geojsonContent = file_get_contents($geojsonPath);
            $geojsonData = json_decode($geojsonContent, true);

            if (!$geojsonData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid GeoJSON file format'
                ], 422);
            }

            // Extract surveyor data from GeoJSON
            $surveyorData = [];
            $surveyorStats = [];
            $surveyorPolygons = [];

            foreach ($geojsonData['features'] as $index => $feature) {
                $properties = $feature['properties'];
                $surveyorName = trim($properties['surveyor_name'] ?? '');
                
                if (!empty($surveyorName)) {
                    // Initialize surveyor if not exists
                    if (!isset($surveyorStats[$surveyorName])) {
                        $surveyorStats[$surveyorName] = [
                            'name' => $surveyorName,
                            'total_polygons' => 0,
                            'total_farmers' => 0,
                            'total_area' => 0,
                            'polygon_ids' => [],
                            'farmer_ids' => [],
                            'areas' => []
                        ];
                    }

                    // Count polygons and farmers
                    $surveyorStats[$surveyorName]['total_polygons']++;
                    $surveyorStats[$surveyorName]['polygon_ids'][] = $properties['object_id'] ?? $index;
                    
                    if ($properties['client_id']) {
                        $surveyorStats[$surveyorName]['farmer_ids'][] = $properties['client_id'];
                        $surveyorStats[$surveyorName]['total_farmers'] = count(array_unique($surveyorStats[$surveyorName]['farmer_ids']));
                    }

                    // Calculate area
                    $plotArea = floatval($properties['plot_area'] ?? 0);
                    $totalArea = floatval($properties['total_area_inacres'] ?? 0);
                    $area = $totalArea > 0 ? $totalArea : $plotArea;
                    
                    if ($area > 0) {
                        $surveyorStats[$surveyorName]['total_area'] += $area;
                        $surveyorStats[$surveyorName]['areas'][] = $area;
                    }

                    // Store detailed polygon data
                    $surveyorPolygons[] = [
                        'surveyor_name' => $surveyorName,
                        'object_id' => $properties['object_id'],
                        'client_id' => $properties['client_id'],
                        'farmer_name' => $properties['farmer_name'],
                        'plot_area' => $properties['plot_area'],
                        'total_area_inacres' => $properties['total_area_inacres'],
                        'coordinates' => $feature['geometry']['coordinates'][0] ?? [],
                        'x' => $properties['x'],
                        'y' => $properties['y']
                    ];
                }
            }

            // Check which surveyors exist in users table
            $surveyorNames = array_keys($surveyorStats);
            $existingSurveyors = [];
            $missingSurveyors = [];

            if (!empty($surveyorNames)) {
                $existingSurveyors = DB::table('users')
                    ->whereIn('name', $surveyorNames)
                    ->select('id', 'name', 'email', 'status', 'created_at')
                    ->get()
                    ->toArray();

                $existingSurveyorNames = array_column($existingSurveyors, 'name');
                $missingSurveyors = array_diff($surveyorNames, $existingSurveyorNames);
            }

            // Calculate statistics
            $totalSurveyors = count($surveyorStats);
            $existingCount = count($existingSurveyors);
            $missingCount = count($missingSurveyors);
            $totalPolygons = array_sum(array_column($surveyorStats, 'total_polygons'));
            $totalFarmers = array_sum(array_column($surveyorStats, 'total_farmers'));
            $totalArea = array_sum(array_column($surveyorStats, 'total_area'));

            // Top performers
            $topByPolygons = collect($surveyorStats)
                ->sortByDesc('total_polygons')
                ->take(5)
                ->values()
                ->toArray();

            $topByArea = collect($surveyorStats)
                ->sortByDesc('total_area')
                ->take(5)
                ->values()
                ->toArray();

            $topByFarmers = collect($surveyorStats)
                ->sortByDesc('total_farmers')
                ->take(5)
                ->values()
                ->toArray();

            // Area distribution
            $areaRanges = [
                '0-1 acres' => 0,
                '1-5 acres' => 0,
                '5-10 acres' => 0,
                '10+ acres' => 0
            ];

            foreach ($surveyorStats as $surveyor) {
                $area = $surveyor['total_area'];
                if ($area <= 1) {
                    $areaRanges['0-1 acres']++;
                } elseif ($area <= 5) {
                    $areaRanges['1-5 acres']++;
                } elseif ($area <= 10) {
                    $areaRanges['5-10 acres']++;
                    } else {
                    $areaRanges['10+ acres']++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Surveyor analysis completed successfully',
                'analysis' => [
                    'summary' => [
                        'total_surveyors' => $totalSurveyors,
                        'existing_in_database' => $existingCount,
                        'missing_from_database' => $missingCount,
                        'total_polygons_surveyed' => $totalPolygons,
                        'total_farmers_surveyed' => $totalFarmers,
                        'total_area_surveyed' => round($totalArea, 2) . ' acres',
                        'average_polygons_per_surveyor' => $totalSurveyors > 0 ? round($totalPolygons / $totalSurveyors, 2) : 0,
                        'average_farmers_per_surveyor' => $totalSurveyors > 0 ? round($totalFarmers / $totalSurveyors, 2) : 0,
                        'average_area_per_surveyor' => $totalSurveyors > 0 ? round($totalArea / $totalSurveyors, 2) . ' acres' : '0 acres'
                    ],
                    'surveyor_details' => array_values($surveyorStats),
                    'top_performers' => [
                        'by_polygons' => $topByPolygons,
                        'by_area' => $topByArea,
                        'by_farmers' => $topByFarmers
                    ],
                    'area_distribution' => $areaRanges,
                    'database_status' => [
                        'existing_surveyors' => $existingSurveyors,
                        'missing_surveyors' => array_values($missingSurveyors)
                    ],
                    'detailed_polygons' => $surveyorPolygons
                ]
            ], 200);

                } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Surveyor analysis failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Load and analyze saved GeoJSON file
     */
    public function load_geojson_analysis(Request $request)
    {
        try {
            // Fixed file path - no need for filename input
            $filename = 'geojson_data_2025-09-30_10-56-59.json';
            $geojsonPath = storage_path('app/public/geojson/' . $filename);
            $processedDataPath = storage_path('app/public/geojson/processed_data_' . str_replace('geojson_data_', '', $filename));

            if (!file_exists($geojsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GeoJSON file not found: ' . $filename
                ], 404);
            }

            // Load GeoJSON data
            $geojsonContent = file_get_contents($geojsonPath);
            $geojsonData = json_decode($geojsonContent, true);

            if (!$geojsonData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid GeoJSON file format'
                ], 422);
            }

            // Load processed data if available
            $processedData = [];
            if (file_exists($processedDataPath)) {
                $processedContent = file_get_contents($processedDataPath);
                $processedData = json_decode($processedContent, true);
            }

            // Extract unique farmers from GeoJSON
            $uniqueFarmers = [];
            foreach ($geojsonData['features'] as $feature) {
                $properties = $feature['properties'];
                $clientId = $properties['client_id'] ?? null;
                $farmerName = $properties['farmer_name'] ?? null;
                
                if ($clientId) {
                    $uniqueFarmers[$clientId] = $farmerName;
                }
            }

            // Check existing farmers in database
            $existingFarmers = [];
            if (!empty($uniqueFarmers)) {
                $clientIds = array_keys($uniqueFarmers);
                $existingFarmers = DB::table('final_farmers')
                    ->whereIn('farmer_uniqueId', $clientIds)
                    ->select('farmer_uniqueId', 'farmer_name')
                    ->get()
                    ->pluck('farmer_name', 'farmer_uniqueId')
                    ->toArray();
            }

            // Check pipe_installation_pipeimg records
            $pipeInstallationCount = DB::table('pipe_installation_pipeimg')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $pipeInstallationByPlotCount = DB::table('pipe_installation_pipeimg')
                ->whereIn('farmer_plot_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            // Check aerations records
            $aerationCount = DB::table('aerations')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $aerationByPlotCount = DB::table('aerations')
                ->whereIn('farmer_plot_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            // Check existing polygons based on coordinates
            $existingPolygonsCount = 0;
            $newPolygonsCount = 0;
            
            foreach ($geojsonData['features'] as $feature) {
                $coordinates = $feature['geometry']['coordinates'][0] ?? [];
                if (!empty($coordinates)) {
                    // Get first coordinate for comparison
                    $firstCoord = $coordinates[0];
                    $lng = $firstCoord[0];
                    $lat = $firstCoord[1];
                    
                    // Check if polygon exists with similar coordinates (within small range)
                    $existingPolygon = DB::table('polygons')
                        ->whereRaw("JSON_EXTRACT(ranges, '$[0].lat') BETWEEN ? AND ?", [$lat - 0.0001, $lat + 0.0001])
                        ->whereRaw("JSON_EXTRACT(ranges, '$[0].lng') BETWEEN ? AND ?", [$lng - 0.0001, $lng + 0.0001])
                        ->first();
                    
                    if ($existingPolygon) {
                        $existingPolygonsCount++;
                    } else {
                        $newPolygonsCount++;
                    }
                }
            }
            
            // Check existing polygons based on farmer_uniqueId
            $existingPolygonsByFarmerCount = DB::table('polygons')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $newPolygonsByFarmerCount = count($uniqueFarmers) - $existingPolygonsByFarmerCount;
            
            // Check surveyor names in users table
            $surveyorNames = [];
            foreach ($geojsonData['features'] as $feature) {
                $surveyorName = $feature['properties']['surveyor_name'] ?? '';
                if (!empty(trim($surveyorName))) {
                    $surveyorNames[] = trim($surveyorName);
                }
            }
            $surveyorNames = array_unique($surveyorNames);
            
            $foundSurveyors = 0;
            $notFoundSurveyors = 0;
            
            if (!empty($surveyorNames)) {
                $existingSurveyors = DB::table('users')
                    ->whereIn('name', $surveyorNames)
                    ->pluck('name')
                    ->toArray();
                
                $foundSurveyors = count($existingSurveyors);
                $notFoundSurveyors = count($surveyorNames) - $foundSurveyors;
            }

            return response()->json([
                'success' => true,
                'message' => 'Fixed GeoJSON file (geojson_data_2025-09-30_10-56-59.json) loaded and analyzed successfully',
                'file_info' => [
                    'loaded_file' => $filename,
                    'file_path' => $geojsonPath,
                    'file_size' => filesize($geojsonPath) . ' bytes',
                    'processed_data_available' => file_exists($processedDataPath),
                    'processed_data_path' => $processedDataPath
                ],
                'data' => [
                    'geojson_analysis' => [
                        'total_features' => count($geojsonData['features']),
                        'unique_farmers_in_geojson' => count($uniqueFarmers),
                        'geojson_type' => $geojsonData['type'] ?? 'Unknown'
                    ],
                    'database_analysis' => [
                        'existing_farmers_count' => count($existingFarmers),
                        'new_farmers_count' => count($uniqueFarmers) - count($existingFarmers)
                    ],
                    'pipe_installation_analysis' => [
                        'by_farmer_uniqueId' => $pipeInstallationCount,
                        'by_farmer_plot_uniqueId' => $pipeInstallationByPlotCount
                    ],
                    'aeration_analysis' => [
                        'by_farmer_uniqueId' => $aerationCount,
                        'by_farmer_plot_uniqueId' => $aerationByPlotCount
                    ],
                    'polygon_analysis' => [
                        'by_coordinates' => [
                            'existing_polygons_count' => $existingPolygonsCount,
                            'new_polygons_count' => $newPolygonsCount,
                            'total_polygons_checked' => $existingPolygonsCount + $newPolygonsCount
                        ],
                        'by_farmer_uniqueId' => [
                            'existing_polygons_count' => $existingPolygonsByFarmerCount,
                            'new_polygons_count' => $newPolygonsByFarmerCount,
                            'total_farmers_checked' => count($uniqueFarmers)
                        ]
                    ],
                    'surveyor_analysis' => [
                        'found_in_users_table' => $foundSurveyors,
                        'not_found_in_users_table' => $notFoundSurveyors,
                        'total_surveyors_in_geojson' => count($surveyorNames)
                    ],
                    'existing_farmers_in_db' => $existingFarmers,
                    'geojson_features' => $geojsonData['features'], // All GeoJSON features
                    'processed_data' => $processedData // Processed data if available
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'GeoJSON loading failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Load specific GeoJSON file and return analysis report
     */
    public function load_specific_geojson_analysis()
    {
        try {
            // Fixed file path
            $filename = 'geojson_data_2025-09-30_10-56-59.json';
            $geojsonPath = storage_path('app/public/geojson/' . $filename);
            $processedDataPath = storage_path('app/public/geojson/processed_data_2025-09-30_10-56-59.json');

            if (!file_exists($geojsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'GeoJSON file not found: ' . $filename,
                    'file_path' => $geojsonPath
                ], 404);
            }

            // Load GeoJSON data
            $geojsonContent = file_get_contents($geojsonPath);
            $geojsonData = json_decode($geojsonContent, true);

            if (!$geojsonData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid GeoJSON file format'
                ], 422);
            }

            // Load processed data if available
            $processedData = [];
            if (file_exists($processedDataPath)) {
                $processedContent = file_get_contents($processedDataPath);
                $processedData = json_decode($processedContent, true);
            }

            // Extract unique farmers from GeoJSON
            $uniqueFarmers = [];
            foreach ($geojsonData['features'] as $feature) {
                $properties = $feature['properties'];
                $clientId = $properties['client_id'] ?? null;
                $farmerName = $properties['farmer_name'] ?? null;
                
                if ($clientId) {
                    $uniqueFarmers[$clientId] = $farmerName;
                }
            }

            // Check existing farmers in database
            $existingFarmers = [];
            if (!empty($uniqueFarmers)) {
                $clientIds = array_keys($uniqueFarmers);
                $existingFarmers = DB::table('final_farmers')
                    ->whereIn('farmer_uniqueId', $clientIds)
                    ->select('farmer_uniqueId', 'farmer_name')
                    ->get()
                    ->pluck('farmer_name', 'farmer_uniqueId')
                    ->toArray();
            }

            // Check pipe_installation_pipeimg records
            $pipeInstallationCount = DB::table('pipe_installation_pipeimg')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $pipeInstallationByPlotCount = DB::table('pipe_installation_pipeimg')
                ->whereIn('farmer_plot_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            // Check aerations records
            $aerationCount = DB::table('aerations')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $aerationByPlotCount = DB::table('aerations')
                ->whereIn('farmer_plot_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            // Check existing polygons based on coordinates
            $existingPolygonsCount = 0;
            $newPolygonsCount = 0;
            
            foreach ($geojsonData['features'] as $feature) {
                $coordinates = $feature['geometry']['coordinates'][0] ?? [];
                if (!empty($coordinates)) {
                    // Get first coordinate for comparison
                    $firstCoord = $coordinates[0];
                    $lng = $firstCoord[0];
                    $lat = $firstCoord[1];
                    
                    // Check if polygon exists with similar coordinates (within small range)
                    $existingPolygon = DB::table('polygons')
                        ->whereRaw("JSON_EXTRACT(ranges, '$[0].lat') BETWEEN ? AND ?", [$lat - 0.0001, $lat + 0.0001])
                        ->whereRaw("JSON_EXTRACT(ranges, '$[0].lng') BETWEEN ? AND ?", [$lng - 0.0001, $lng + 0.0001])
                        ->first();
                    
                    if ($existingPolygon) {
                        $existingPolygonsCount++;
                    } else {
                        $newPolygonsCount++;
                    }
                }
            }
            
            // Check existing polygons based on farmer_uniqueId
            $existingPolygonsByFarmerCount = DB::table('polygons')
                ->whereIn('farmer_uniqueId', array_keys($uniqueFarmers))
                ->count();
            
            $newPolygonsByFarmerCount = count($uniqueFarmers) - $existingPolygonsByFarmerCount;
            
            // Check surveyor names in users table
            $surveyorNames = [];
            foreach ($geojsonData['features'] as $feature) {
                $surveyorName = $feature['properties']['surveyor_name'] ?? '';
                if (!empty(trim($surveyorName))) {
                    $surveyorNames[] = trim($surveyorName);
                }
            }
            $surveyorNames = array_unique($surveyorNames);
            
            $foundSurveyors = 0;
            $notFoundSurveyors = 0;
            
            if (!empty($surveyorNames)) {
                $existingSurveyors = DB::table('users')
                    ->whereIn('name', $surveyorNames)
                    ->pluck('name')
                    ->toArray();
                
                $foundSurveyors = count($existingSurveyors);
                $notFoundSurveyors = count($surveyorNames) - $foundSurveyors;
            }

            // Generate comprehensive analysis report
            $analysisReport = [
                'file_info' => [
                    'loaded_file' => $filename,
                    'file_path' => $geojsonPath,
                    'file_size' => filesize($geojsonPath) . ' bytes',
                    'processed_data_available' => file_exists($processedDataPath),
                    'processed_data_path' => $processedDataPath,
                    'load_timestamp' => date('Y-m-d H:i:s')
                ],
                'summary' => [
                    'total_geojson_features' => count($geojsonData['features']),
                    'unique_farmers_in_file' => count($uniqueFarmers),
                    'existing_farmers_in_database' => count($existingFarmers),
                    'new_farmers_to_add' => count($uniqueFarmers) - count($existingFarmers),
                    'total_polygons_checked' => $existingPolygonsCount + $newPolygonsCount,
                    'existing_polygons' => $existingPolygonsCount,
                    'new_polygons' => $newPolygonsCount
                ],
                'database_analysis' => [
                    'farmers' => [
                        'existing_count' => count($existingFarmers),
                        'new_count' => count($uniqueFarmers) - count($existingFarmers),
                        'total_in_file' => count($uniqueFarmers)
                    ],
                    'pipe_installations' => [
                        'by_farmer_uniqueId' => $pipeInstallationCount,
                        'by_farmer_plot_uniqueId' => $pipeInstallationByPlotCount
                    ],
                    'aerations' => [
                        'by_farmer_uniqueId' => $aerationCount,
                        'by_farmer_plot_uniqueId' => $aerationByPlotCount
                    ],
                    'polygons' => [
                        'by_coordinates' => [
                            'existing_count' => $existingPolygonsCount,
                            'new_count' => $newPolygonsCount,
                            'total_checked' => $existingPolygonsCount + $newPolygonsCount
                        ],
                        'by_farmer_uniqueId' => [
                            'existing_count' => $existingPolygonsByFarmerCount,
                            'new_count' => $newPolygonsByFarmerCount,
                            'total_farmers_checked' => count($uniqueFarmers)
                        ]
                    ],
                    'surveyors' => [
                        'found_in_users_table' => $foundSurveyors,
                        'not_found_in_users_table' => $notFoundSurveyors,
                        'total_in_file' => count($surveyorNames),
                        'created_surveyors' => $createdSurveyors,
                        'failed_surveyors' => $failedSurveyors
                    ]
                ],
                'recommendations' => [
                    'data_import' => count($uniqueFarmers) - count($existingFarmers) > 0 ? 
                        'Import ' . (count($uniqueFarmers) - count($existingFarmers)) . ' new farmers' : 
                        'No new farmers to import',
                    'polygon_import' => $newPolygonsCount > 0 ? 
                        'Import ' . $newPolygonsCount . ' new polygons' : 
                        'No new polygons to import',
                    'surveyor_setup' => $notFoundSurveyors > 0 ? 
                        'Setup ' . $notFoundSurveyors . ' surveyor accounts' : 
                        'All surveyors are already in the system'
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Analysis report generated successfully for geojson_data_2025-09-30_10-56-59.json',
                'analysis_report' => $analysisReport
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis report generation failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * List all available GeoJSON files in storage
     */
    public function list_geojson_files()
    {
        try {
            $storageDir = storage_path('app/public/geojson/');
            
            if (!is_dir($storageDir)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No GeoJSON files found',
                    'files' => []
                ], 200);
            }

            $files = glob($storageDir . 'geojson_data_*.json');
            $fileList = [];

            foreach ($files as $file) {
                $filename = basename($file);
                $fileInfo = [
                    'filename' => $filename,
                    'path' => $file,
                    'size' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'processed_data_file' => 'processed_data_' . str_replace('geojson_data_', '', $filename),
                    'processed_data_exists' => file_exists($storageDir . 'processed_data_' . str_replace('geojson_data_', '', $filename))
                ];
                $fileList[] = $fileInfo;
            }

            // Sort by creation time (newest first)
            usort($fileList, function($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            return response()->json([
                'success' => true,
                'message' => 'GeoJSON files listed successfully',
                'total_files' => count($fileList),
                'files' => $fileList
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list GeoJSON files',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function genrate_geojson_old_upload(Request $request){

        try {
            // Set comprehensive timeout and memory settings
            set_time_limit(0); // 0 = no time limit
            ini_set('memory_limit', '72000M');
            ini_set('max_execution_time', 0);
            ini_set('max_input_time', -1);
            
            // Additional timeout settings
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            
            // Set HTTP timeout headers
            if (!headers_sent()) {
                header('X-Accel-Buffering: no'); // Disable nginx buffering
            }

            // Check if a KML file was uploaded
            if (!$request->hasFile('kml_file')) {
                return response()->json(['message' => 'No KML file uploaded. Please upload a KML file with key "kml_file"'], 400);
            }

            $file = $request->file('kml_file');
            
            // Validate file type
            if ($file->getClientOriginalExtension() !== 'kml') {
                return response()->json(['message' => 'Invalid file type. Please upload a KML file'], 400);
            }

            // Load and parse the KML file
            $kml = simplexml_load_file($file->getPathname());
            
            if ($kml === false) {
                return response()->json(['message' => 'Failed to parse KML file'], 400);
            }

            // Register the KML namespace
            $kml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
            
            // Find all Placemark elements
            $placemarks = $kml->xpath('//kml:Placemark');
            
            $processedData = [];
            $count = 0;
            $created = 0;
            $skippedNoFarmer = 0;
            $errors = [];
            
            // Start database transaction for entire processing
            DB::beginTransaction();
            
            try {
                foreach ($placemarks as $placemark) {
                try {
                    // Register namespace for this placemark
                    $placemark->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
                    
                    // Extract farmer ID from layer field in ExtendedData
                    $layerNodes = $placemark->xpath('.//kml:ExtendedData/kml:SchemaData/kml:SimpleData[@name="layer"]');
                    $rawLayer = isset($layerNodes[0]) ? (string)$layerNodes[0] : '';
                    
                    if ($rawLayer === '') {
                        $skippedNoFarmer++;
                        continue;
                    }
                    
                    // Extract farmer ID from layer field (e.g., "C813981484 — polygon list" -> "813981484")
                    $farmerId = preg_replace('/\D+/', '', $rawLayer);
                    
                    if (!$farmerId) {
                        $skippedNoFarmer++;
                        continue;
                    }
                    
                    // Extract area from ExtendedData if available
                    $areaNodes = $placemark->xpath('.//kml:ExtendedData/kml:SchemaData/kml:SimpleData[@name="area ha"]');
                    $areaHa = isset($areaNodes[0]) ? (float)$areaNodes[0] : null;
                    
                    // Extract polygon coordinates
                    $coordNodes = $placemark->xpath('.//kml:Polygon/kml:outerBoundaryIs/kml:LinearRing/kml:coordinates');
                    
                    if (empty($coordNodes)) {
                        continue; // Skip if no coordinates found
                    }
                    
                    // Collect all coordinate ranges for this placemark
                    $allRanges = [];
                    
                    foreach ($coordNodes as $coordNode) {
                        $coordsStr = trim((string)$coordNode);
                        if ($coordsStr === '') continue;
                        
                        // Parse coordinates
                        $points = preg_split('/\s+/', $coordsStr);
                        $latLngs = [];
                        foreach ($points as $pt) {
                            $parts = explode(',', trim($pt));
                            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                $latLngs[] = ['lat' => (float)$parts[1], 'lng' => (float)$parts[0]];
                            }
                        }
                        
                        if (!empty($latLngs)) {
                            $allRanges[] = $latLngs;
                        }
                    }
                    
                    // Skip if no valid coordinates found
                    if (empty($allRanges)) {
                        continue;
                    }
                    
                    // Get first two points from first range for latitude and longitude
                    $firstRange = $allRanges[0];
                    $lat = $firstRange[0]['lat'] ?? null;
                    $lng = $firstRange[0]['lng'] ?? null;
                    
                    // Skip if coordinates are null
                    if ($lat === null || $lng === null) {
                        continue;
                    }
                    
                    // Check if farmer exists
                    $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerId)->first();
                    
                    if ($finalFarmer) {
                        // Farmer exists - check if this is a second polygon
                        $existingPolygons = Polygon::where('farmer_uniqueId', $farmerId)->count();
                        
                        if ($existingPolygons > 0) {
                            // This is a second or subsequent polygon - create P2, P3, etc.
                            $plotNo = $existingPolygons + 1;
                            $plotUniqueId = $farmerId . 'P' . $plotNo;
                            
                            // Create new FinalFarmer record for additional polygon
                            $targetFarmer = $finalFarmer->replicate();
                            $targetFarmer->plot_no = (string)$plotNo;
                            $targetFarmer->farmer_plot_uniqueid = $plotUniqueId;
                            $targetFarmer->plot_area = $areaHa !== null ? $areaHa : 0;
                            $targetFarmer->area_in_acers = $areaHa !== null ? $areaHa : 0;
                            $targetFarmer->save();
                            
                            // Create corresponding FarmerPlot record
                            $farmerPlot = FarmerPlot::where('farmer_uniqueId', $farmerId)->where('plot_no', '1')->first();
                            if ($farmerPlot) {
                                $targetFarmerPlot = $farmerPlot->replicate();
                                $targetFarmerPlot->farmer_id = $targetFarmer->id;
                                $targetFarmerPlot->farmer_plot_uniqueid = $plotUniqueId;
                                $targetFarmerPlot->plot_no = (string)$plotNo;
                                $targetFarmerPlot->area_in_acers = $areaHa !== null ? $areaHa : 0;
                                $targetFarmerPlot->save();
                            }
                            
                            // Create polygon record with all ranges
                            $polygon = new Polygon;
                            $polygon->farmer_uniqueId = $farmerId;
                            $polygon->farmer_id = $targetFarmer->id;
                            $polygon->farmer_plot_uniqueid = $plotUniqueId;
                            $polygon->ranges = json_encode($allRanges);
                            $polygon->plot_no = (string)$plotNo;
                            $polygon->final_status = "Approved";
                            $polygon->area_units = "Hectare";
                            $polygon->latitude = $lat;
                            $polygon->longitude = $lng;
                            $polygon->plot_area = $areaHa !== null ? $areaHa : 0;
                            $polygon->save();
                            
                        } else {
                            // First polygon for existing farmer
                            $polygon = new Polygon;
                            $polygon->farmer_uniqueId = $farmerId;
                            $polygon->farmer_id = $finalFarmer->id;
                            $polygon->farmer_plot_uniqueid = $farmerId . "P1";
                            $polygon->ranges = json_encode($allRanges);
                            $polygon->plot_no = "1";
                            $polygon->final_status = "Approved";
                            $polygon->area_units = "Hectare";
                            $polygon->latitude = $lat;
                            $polygon->longitude = $lng;
                            $polygon->plot_area = $areaHa !== null ? $areaHa : 0;
                            $polygon->save();
                        }
                        
                    } else {
                        // Farmer doesn't exist - create new farmer and polygon
                        $finalFarmer = new FinalFarmer;
                        $finalFarmer->surveyor_id = 3783;
                        $finalFarmer->organization_id = 1;
                        $finalFarmer->farmer_survey_id = 3783;
                        $finalFarmer->farmer_name = 'Farmer ' . $farmerId;
                        $finalFarmer->farmer_uniqueId = $farmerId;
                        $finalFarmer->total_plot_area = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->available_area = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->area_in_acers = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->plot_no = "1";
                        $finalFarmer->own_area_in_acres = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->plot_area = $areaHa !== null ? $areaHa : 0;
                        $finalFarmer->land_ownership = "Own";
                        $finalFarmer->actual_owner_name = $finalFarmer->farmer_name;
                        $finalFarmer->final_status = "Approved";
                        $finalFarmer->onboard_completed = "Approved";
                        $finalFarmer->financial_year = "2025-2025";
                        $finalFarmer->season = "Kharif";
                        $finalFarmer->country_id = 101;
                        $finalFarmer->state_id = 1;
                        $finalFarmer->final_status_onboarding = "Completed";
                        $finalFarmer->status_onboarding = "Completed";
                        $finalFarmer->onboarding_form = "1";
                        $finalFarmer->farmer_plot_uniqueid = $farmerId . "P1";
                        $finalFarmer->save();
                        
                        // Create FarmerPlot record
                        $farmerPlot = new FarmerPlot;
                        $farmerPlot->farmer_id = $finalFarmer->id;
                        $farmerPlot->farmer_uniqueId = $farmerId;
                        $farmerPlot->farmer_plot_uniqueid = $farmerId . "P1";
                        $farmerPlot->plot_no = "1";
                        $farmerPlot->area_in_acers = $areaHa !== null ? $areaHa : 0;
                        $farmerPlot->land_ownership = "Own";
                        $farmerPlot->actual_owner_name = $finalFarmer->farmer_name;
                        $farmerPlot->final_status = "Approved";
                        $farmerPlot->status = "Approved";
                        $farmerPlot->save();
                        
                        // Create polygon record with all ranges
                        $polygon = new Polygon;
                        $polygon->farmer_uniqueId = $farmerId;
                        $polygon->farmer_id = $finalFarmer->id;
                        $polygon->farmer_plot_uniqueid = $farmerId . "P1";
                        $polygon->ranges = json_encode($allRanges);
                        $polygon->plot_no = "1";
                        $polygon->final_status = "Approved";
                        $polygon->area_units = "Hectare";
                        $polygon->latitude = $lat;
                        $polygon->longitude = $lng;
                        $polygon->plot_area = $areaHa !== null ? $areaHa : 0;
                        $polygon->save();
                    }
                    
                    $created++;
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'farmer_id' => $farmerId ?? 'unknown',
                        'error' => $e->getMessage(),
                        'line' => $e->getLine()
                    ];
                }
            }
            
            // Commit transaction if all processing completed successfully
            DB::commit();
            
            return response()->json([
                'message' => 'KML processing completed successfully',
                'created' => $created,
                'skipped_no_farmer' => $skippedNoFarmer,
                'errors' => $errors,
                'file_name' => $file->getClientOriginalName()
            ]);
            
            } catch (\Exception $e) {
                // Rollback transaction on any error
                DB::rollback();
                
                return response()->json([
                    'message' => 'KML processing failed - all changes rolled back',
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'created' => $created,
                    'skipped_no_farmer' => $skippedNoFarmer,
                    'errors' => $errors
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Processing failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function genrate_geojson_data_kml(Request $request)
    {
        try {
            // Set comprehensive timeout and memory settings
            set_time_limit(0); // 0 = no time limit
            ini_set('memory_limit', '72000M');
            ini_set('max_execution_time', 0);
            ini_set('max_input_time', -1);
            
            // Additional timeout settings
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            
            // Set HTTP timeout headers
            if (!headers_sent()) {
                header('X-Accel-Buffering: no'); // Disable nginx buffering
            }

            // Check if a KML file was uploaded
            if (!$request->hasFile('kml_file')) {
                return response()->json(['message' => 'No KML file uploaded. Please upload a KML file with key "kml_file"'], 400);
            }

            $file = $request->file('kml_file');
            
            // Validate file type
            if ($file->getClientOriginalExtension() !== 'kml') {
                return response()->json(['message' => 'Invalid file type. Please upload a KML file'], 400);
            }

            // Load and parse the KML file
            $kml = simplexml_load_file($file->getPathname());
            
            if ($kml === false) {
                return response()->json(['message' => 'Failed to parse KML file'], 400);
            }

            // Register the KML namespace
            $kml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
            
            // Find all Placemark elements
            $placemarks = $kml->xpath('//kml:Placemark');
            
            $processedData = [];
            $count = 0;
            
            foreach ($placemarks as $placemark) {
                
                // Extract basic information
                $name = (string) $placemark->name;
            
                // Extract ExtendedData
                $extendedData = [];
                if (isset($placemark->ExtendedData->SchemaData->SimpleData)) {
                    foreach ($placemark->ExtendedData->SchemaData->SimpleData as $simpleData) {
                        $extendedData[(string) $simpleData['name']] = (string) $simpleData;
                    }
                }
                
                // Extract coordinates and convert to [[lat, lng], [lat, lng]] format
                $coordinates = [];
                if (isset($placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates)) {
                    $coordsString = (string) $placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates;
                    
                    // Split coordinates by spaces and process each coordinate pair
                    $coordPairs = explode(' ', trim($coordsString));
                    
                    foreach ($coordPairs as $coordPair) {
                        if (!empty(trim($coordPair))) {
                            $parts = explode(',', trim($coordPair));
                            if (count($parts) >= 2) {
                                $lng = floatval($parts[0]); // Longitude (X)
                                $lat = floatval($parts[1]); // Latitude (Y)
                                $coordinates[] = ["lat"=>$lat, "lng"=>$lng]; // Format: [lat, lng]
                            }
                        }
                    }
                }
                
                // Extract farmer ID from Client_ID field
                $rawClientId = $extendedData['Client_ID'] ?? '';
                $farmerId = preg_replace('/[cC]/', '', (string) $rawClientId); // Remove 'c' and 'C'
                $farmerId = preg_replace('/\D+/', '', $farmerId); // Remove all non-digits
                $farmerId = ltrim($farmerId, '0'); // Remove leading zeros
                
                $count++; 
                
                if (!$farmerId) {
                    echo "Skipping polygon {$count}: No farmer ID found in Client_ID field\n";
                    continue; // Skip if no farmer ID found
                }
                
                // Extract area in hectares - Dag_Wise_l is total area, FINAL_Q_GI is plot area
                $totalArea = isset($extendedData['Dag_Wise_l']) && is_numeric($extendedData['Dag_Wise_l']) ? (float) $extendedData['Dag_Wise_l'] : null;
                $plotArea = isset($extendedData['FINAL_Q_GI']) && is_numeric($extendedData['FINAL_Q_GI']) ? (float) $extendedData['FINAL_Q_GI'] : null;
                
                // Extract farmer name from F_client_l field
                $farmerName = $rawClientId;
                
                echo "Processing farmer ID: {$farmerId}, Name: {$farmerName}, Area: {$totalArea} hectares\n";
                
                // Extract collector information
                $collectorFirstName = $extendedData['F_collecto'] ?? '';
                $collectorLastName = $extendedData['F_collec_1'] ?? '';
                $collectorName = trim($collectorFirstName . ' ' . $collectorLastName);
                
                // Get coordinates from X and Y fields
                $lat = isset($extendedData['Y']) && is_numeric($extendedData['Y']) ? (float) $extendedData['Y'] : null;
                $lng = isset($extendedData['X']) && is_numeric($extendedData['X']) ? (float) $extendedData['X'] : null;
                
                // If X,Y coordinates are not available, use first coordinate from polygon
                if (!$lat || !$lng) {
                    $lat = $coordinates[0]['lat'] ?? null;
                    $lng = $coordinates[0]['lng'] ?? null;
                }
                
                // Find or create FinalFarmer
                $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerId)->first();
                $polygon = polygon::where('farmer_plot_uniqueid', $farmerId . 'P1')->first();
                
                if ($finalFarmer && $polygon) {
                    // Create new FinalFarmer
                    $finalFarmer = finalFarmer->replicate();
                    $finalFarmer->total_plot_area = $totalArea !== null ? $totalArea : 0;
                    $finalFarmer->available_area = $totalArea !== null ? $totalArea : 0;
                    $finalFarmer->area_in_acers = $totalArea !== null ? $totalArea : 0;
                    $finalFarmer->plot_no = 2;
                    $finalFarmer->own_area_in_acres = $totalArea !== null ? $totalArea : 0;
                    $finalFarmer->plot_area = $plotArea !== null ? $plotArea : 0;
                    
    
                    $finalFarmer->farmer_plot_uniqueid = $farmerId . 'P2';
                    $finalFarmer->save();
                    
                    // Create P1 FarmerPlot
                    $farmerPlot = new FarmerPlot;
                    $farmerPlot->farmer_id = $finalFarmer->id;
                    $farmerPlot->farmer_uniqueId = $farmerId;
                    $farmerPlot->farmer_plot_uniqueid = $farmerId . 'P2';
                    $farmerPlot->plot_no = 2;
                    $farmerPlot->area_in_acers = $totalArea !== null ? $totalArea : 0;
                    $farmerPlot->area_in_other = $totalArea !== null ? $totalArea : 0;
                    $farmerPlot->area_in_other_unit = $totalArea !== null ? $totalArea : 0;
                    $farmerPlot->area_acre_awd = $totalArea !== null ? $totalArea : 0;
                    $farmerPlot->area_other_awd = $totalArea !== null ? $totalArea : 0;
                    $farmerPlot->area_other_awd_unit = $totalArea !== null ? $totalArea : 0;
                    $farmerPlot->land_ownership = $finalFarmer->land_ownership;
                    $farmerPlot->actual_owner_name = $finalFarmer->actual_owner_name;
                    $farmerPlot->final_status = $finalFarmer->final_status;
                    $farmerPlot->status = $finalFarmer->final_status;
                    $farmerPlot->save();
                    
                    $plotNo = '2';
                } else {
                    $plotNo = 1;
                }
                
                // Create Polygon
                $polygon = new Polygon;
                $polygon->farmer_uniqueId = $farmerId;
                $polygon->farmer_id = $finalFarmer->id;
                $polygon->farmer_plot_uniqueid = $farmerId . 'P' . $plotNo;
                $polygon->ranges = json_encode($coordinates);
                $polygon->plot_no = $plotNo;
                $polygon->final_status = "Approved";
                $polygon->area_units = "Hectare";
                $polygon->latitude = $lat;
                $polygon->longitude = $lng;
                if ($totalArea !== null || $plotArea !== null) $polygon->plot_area = $plotArea;
                $polygon->save();
            }
            
            echo "\nTotal polygons processed: {$count}\n";
            
            return response()->json([
                'message' => 'KML file processed successfully',
                'total_polygons' => $count,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Processing failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
    public function genrate_geojsonBanshihari_polygons(Request $request){
        try {
            // Set comprehensive timeout and memory settings
            set_time_limit(0);
            ini_set('memory_limit', '72000M');
            ini_set('max_execution_time', 0);
            ini_set('max_input_time', -1);
            
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            
            if (!headers_sent()) {
                header('X-Accel-Buffering: no');
            }

            // Check if a file was uploaded
            if (!$request->hasFile('file')) {
                return response()->json(['message' => 'No file uploaded'], 400);
            }

            $file = $request->file('file');
            $data = Excel::toArray([], $file);
            
            \DB::beginTransaction();
            $processedCount = 0;
            
            foreach ($data[0] as $rowIndex => $row) {
                if ($rowIndex === 0) {
                    // Skip the header row
                    continue;
                }

                $farmerUniqueId = str_ireplace('c', '', str_ireplace('C', '', $row[0])); // Remove 'c' from value, e.g., C825678482 -> 825678482
                $surveyorName = $row[1];
                $stateName = $row[2];
                $districtName = $row[3];
                $talukaName = $row[4];
                $pancayatName = $row[5];
                $villageName = $row[6];
                $Pincode = $row[7];
                $MobileNumber = $row[8];
                $TotalLand = $row[9];
                $AWDLandSize = $row[10];
                $PlotDagNo = $row[11];
                $KhatiyanNumber = $row[12];
                $ValidatedArea = $row[13];
                $GisArea = $row[14];
                
                $surveyour = User::where('name', trim($surveyorName))->first();
                if(!$surveyour){
                    $surveyour = new User();
                    $surveyour->name = $surveyorName;
                    $surveyour->save();
                }
                $surveyorId = $surveyour->id;

                $state = State::where('name', trim($stateName))->first();
                $district = District::where('district', trim($districtName))->where('state_id', $state->id??null)->first();
                $taluka = Taluka::where('taluka', trim($talukaName))->where('district_id', $district->id??null)->first();
                $pancayat = Panchayat::where('panchayat', trim($pancayatName))->where('taluka_id', $taluka->id??null)->first();
                $village = Village::where('village', trim($villageName))->where('panchayat_id', $pancayat->id??null)->first();
                
                $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerUniqueId)->latest()->first();
                if($finalFarmer){
                    $plotNo = $finalFarmer->plot_no + 1;
                }else{
                    $plotNo = 1;
                }

                // Create FinalFarmer record
                $farmer = new FinalFarmer();
                $farmer->farmer_uniqueId = $farmerUniqueId;
                $farmer->farmer_plot_uniqueid = $farmerUniqueId . 'P' . $plotNo;
                $farmer->surveyor_id = $surveyorId;
                $farmer->state_id = $state->id??null;
                $farmer->district_id = $district->id??null;
                $farmer->taluka_id = $taluka->id??null;
                $farmer->panchayat_id = $pancayat->id??null;
                $farmer->village_id = $village->id??null;
                $farmer->plot_no = $plotNo;
                $farmer->total_land = $TotalLand;
                $farmer->awd_land_size = $AWDLandSize;
                $farmer->plot_dag_no = $PlotDagNo;
                $farmer->khatiyan_number = $KhatiyanNumber;
                $farmer->validated_area = $ValidatedArea;
                $farmer->gis_area = $GisArea;
                $farmer->pincode = $Pincode;
                $farmer->mobile_number = $MobileNumber;
                $farmer->land_ownership = "Owner";
                $farmer->actual_owner_name = $row[0];
                $farmer->final_status = "Approved";
                $farmer->status = "Approved";
                $farmer->onboard_completed = "Approved";
                $farmer->final_status_onboarding = "Completed";
                $farmer->status_onboarding = "Completed";
                $farmer->onboarding_form = "1";
                $farmer->financial_year = "2025-2025";
                $farmer->season = "Kharif";
                $farmer->country_id = 101;
                $farmer->total_plot_area = $TotalLand;
                $farmer->available_area = $TotalLand;
                $farmer->area_in_acers = $TotalLand;
                $farmer->own_area_in_acres = $TotalLand;
                $farmer->plot_area = $AWDLandSize;
                $farmer->state_name = $stateName;
                $farmer->district_name = $districtName;
                $farmer->taluka_name = $talukaName;
                $farmer->panchayat_name = $pancayatName;
                $farmer->village_name = $villageName;
                $farmer->block_name = $talukaName;
                $farmer->save();

                // Create FarmerPlot record
                $farmerPlot = new FarmerPlot();
                $farmerPlot->farmer_id = $farmer->id;
                $farmerPlot->farmer_uniqueId = $farmerUniqueId;
                $farmerPlot->farmer_plot_uniqueid = $farmerUniqueId . 'P' . $plotNo;
                $farmerPlot->plot_no = $plotNo;
                $farmerPlot->area_in_acers = $AWDLandSize;
                $farmerPlot->area_in_other = $AWDLandSize;
                $farmerPlot->area_in_other_unit = $AWDLandSize;
                $farmerPlot->area_acre_awd = $AWDLandSize;
                $farmerPlot->area_other_awd = $AWDLandSize;
                $farmerPlot->area_other_awd_unit = $AWDLandSize;
                $farmerPlot->daag_number = $PlotDagNo;
                $farmerPlot->khatian_number = $KhatiyanNumber;
                $farmerPlot->land_ownership = "Owner";
                $farmerPlot->actual_owner_name = $row[0];
                $farmerPlot->final_status = "Approved";
                $farmerPlot->status = "Approved";
                $farmerPlot->save();

                $processedCount++;
            }

            \DB::commit();

            return response()->json([
                'message' => 'Data processed successfully',
                'total_processed' => $processedCount
            ], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Processing failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
// public function genrate_geojson(Request $request){
//     try {
//          // Set comprehensive timeout and memory settings
//         set_time_limit(0);
//         ini_set('memory_limit', '72000M');
//         ini_set('max_execution_time', 0);
//         ini_set('max_input_time', -1);
        
//         if (function_exists('ignore_user_abort')) {
//             ignore_user_abort(true);
//         }
        
//         if (!headers_sent()) {
//             header('X-Accel-Buffering: no');
//         }

        
//     // Check if a file was uploaded
//     if (!$request->hasFile('file')) {
//         return response()->json(['message' => 'No file uploaded'], 400);
//     }

//     $file = $request->file('file');
 
//     $data = Excel::toArray([], $file);
//     \DB::beginTransaction();
//     foreach ($data[0] as $rowIndex => $row) {
//         if ($rowIndex === 0) {
//             // Skip the header row
//             continue;
//         }

//         $farmerUniqueId = str_ireplace('c', '', str_ireplace('C', '', $row[0])); // Remove 'c' from value, e.g., C825678482 -> 825678482
//         $surveyorName = $row[1];
//         $stateName = $row[2];
//         $districtName = $row[3];
//         $talukaName = $row[4];
//         $pancayatName = $row[5];
//         $villageName = $row[6];
//         $Pincode = $row[7];
//         $MobileNumber = $row[8];
//         $TotalLand = $row[9];
//         $AWDLandSize = $row[10];
//         $PlotDagNo = $row[11];
//         $KhatiyanNumber = $row[12];
//         $ValidatedArea = $row[13];
//         $GisArea = $row[14];
        
//         $surveyour = User::where('name', trim($surveyorName))->first();
//         if(!$surveyour){
//             $surveyour = new User();
//             $surveyour->name = $surveyorName;
//             $surveyour->save();
//         }
//         $surveyorId = $surveyour->id;

//         $state = State::where('name', trim($stateName))->first();
//         $district = District::where('name', trim($districtName),'state_id' => $state->id??null)->first();
//         $taluka = Taluka::where('name', trim($talukaName),'district_id' => $district->id??null)->first();
//         $pancayat = Panchayat::where('name', trim($pancayatName),'taluka_id' => $taluka->id??null)->first();
//         $village = Village::where('name', trim($villageName),'panchayat_id' => $pancayat->id??null)->first();
        
//         $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerUniqueId)->latest()->first();
//         if($finalFarmer){
//             $plotNo = $finalFarmer->plot_no + 1;
//         }else{
//             $plotNo = 1;
//         }

//         $farmer = new FinalFarmer();
//         $farmer->farmer_uniqueId = $farmerUniqueId;
//         $farmer->surveyor_id = $surveyorId;
//         $farmer->state_id = $state->id??null;
//         $farmer->district_id = $district->id??null;
//         $farmer->taluka_id = $taluka->id??null;
//         $farmer->pancayat_id = $pancayat->id??null;
//         $farmer->village_id = $village->id??null;
//         $farmer->plot_no = $plotNo;
//         $farmer->total_land = $TotalLand;
//         $farmer->awd_land_size = $AWDLandSize;
//         $farmer->plot_dag_no = $PlotDagNo;
//         $farmer->khatiyan_number = $KhatiyanNumber;
//         $farmer->validated_area = $ValidatedArea;
//         $farmer->gis_area = $GisArea;
//         $farmer->pincode = $Pincode;
//         $farmer->mobile_number = $MobileNumber;
//         $farmer->land_ownership = "Owner";
//         $farmer->actual_owner_name = $row[0];
//         $farmer->final_status = "Approved";
//         $farmer->status = "Approved";
//         $farmer->save();

//         dd($row);
//     }

//     } catch (\Exception $e) {
//         return response()->json([
//             'message' => 'Processing failed',
//             'error' => $e->getMessage(),
//             'line' => $e->getLine(),
//         ], 500);
//     }
// }
// public function genrate_geojson_Banshihari_blockPolygon(Request $request)
// {
//     try {
//         // Set comprehensive timeout and memory settings
//         set_time_limit(0);
//         ini_set('memory_limit', '72000M');
//         ini_set('max_execution_time', 0);
//         ini_set('max_input_time', -1);
        
//         if (function_exists('ignore_user_abort')) {
//             ignore_user_abort(true);
//         }
        
//         if (!headers_sent()) {
//             header('X-Accel-Buffering: no');
//         }

        
//         // Check if a KML file was uploaded
//         if (!$request->hasFile('kml_file')) {
//             return response()->json(['message' => 'No KML file uploaded. Please upload a KML file with key "kml_file"'], 400);
//         }

//         $file = $request->file('kml_file');
        
//         // Validate file type
//         if ($file->getClientOriginalExtension() !== 'kml') {
//             return response()->json(['message' => 'Invalid file type. Please upload a KML file'], 400);
//         }

//         // Load and parse the KML file
//         $kml = simplexml_load_file($file->getPathname());
        
//         if ($kml === false) {
//             return response()->json(['message' => 'Failed to parse KML file'], 400);
//         }

//         // Register the KML namespace
//         $kml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
        
//         // Find all Placemark elements
//         $placemarks = $kml->xpath('//kml:Placemark');
        
//         $processedData = [];
//         $count = 0;
        
//         foreach ($placemarks as $placemark) {
//             dd($placemark);
//         }
//     }catch (\Exception $e) {
//         return response()->json([
//             'message' => 'Processing failed',
//             'error' => $e->getMessage(),
//             'line' => $e->getLine(),
//         ], 500);
//     }
// }
public function genrate_geojson_consolidated_report(Request $request)
{
    try {
        // Set comprehensive timeout and memory settings
        set_time_limit(0);
        ini_set('memory_limit', '72000M');
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);
        
        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }
        
        if (!headers_sent()) {
            header('X-Accel-Buffering: no');
        }

        $data = \DB::select("SELECT 
            f.farmer_uniqueId,
            f.farmer_name,
            f.farmer_plot_uniqueid,
            pipe_data.total_pipe_installation,
            MAX(CASE WHEN pipe_data.row_no = 1 THEN pipe_data.pipe_no END) AS pipe_no_1,
            MAX(CASE WHEN pipe_data.row_no = 1 THEN pipe_data.date END) AS pipe_date_1,
            MAX(CASE WHEN pipe_data.row_no = 1 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_1,
            MAX(CASE WHEN pipe_data.row_no = 2 THEN pipe_data.pipe_no END) AS pipe_no_2,
            MAX(CASE WHEN pipe_data.row_no = 2 THEN pipe_data.date END) AS pipe_date_2,
            MAX(CASE WHEN pipe_data.row_no = 2 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_2,
            MAX(CASE WHEN pipe_data.row_no = 3 THEN pipe_data.pipe_no END) AS pipe_no_3,
            MAX(CASE WHEN pipe_data.row_no = 3 THEN pipe_data.date END) AS pipe_date_3,
            MAX(CASE WHEN pipe_data.row_no = 3 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_3,
            MAX(CASE WHEN pipe_data.row_no = 4 THEN pipe_data.pipe_no END) AS pipe_no_4,
            MAX(CASE WHEN pipe_data.row_no = 4 THEN pipe_data.date END) AS pipe_date_4,
            MAX(CASE WHEN pipe_data.row_no = 4 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_4,
            MAX(CASE WHEN pipe_data.row_no = 5 THEN pipe_data.pipe_no END) AS pipe_no_5,
            MAX(CASE WHEN pipe_data.row_no = 5 THEN pipe_data.date END) AS pipe_date_5,
            MAX(CASE WHEN pipe_data.row_no = 5 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_5,
            MAX(CASE WHEN pipe_data.row_no = 6 THEN pipe_data.pipe_no END) AS pipe_no_6,
            MAX(CASE WHEN pipe_data.row_no = 6 THEN pipe_data.date END) AS pipe_date_6,
            MAX(CASE WHEN pipe_data.row_no = 6 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_6,
            MAX(CASE WHEN pipe_data.row_no = 7 THEN pipe_data.pipe_no END) AS pipe_no_7,
            MAX(CASE WHEN pipe_data.row_no = 7 THEN pipe_data.date END) AS pipe_date_7,
            MAX(CASE WHEN pipe_data.row_no = 7 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_7,
            MAX(CASE WHEN pipe_data.row_no = 8 THEN pipe_data.pipe_no END) AS pipe_no_8,    
            MAX(CASE WHEN pipe_data.row_no = 8 THEN pipe_data.date END) AS pipe_date_8,
            MAX(CASE WHEN pipe_data.row_no = 8 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_8,
            MAX(CASE WHEN pipe_data.row_no = 9 THEN pipe_data.pipe_no END) AS pipe_no_9,
            MAX(CASE WHEN pipe_data.row_no = 9 THEN pipe_data.date END) AS pipe_date_9,
            MAX(CASE WHEN pipe_data.row_no = 9 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_9,
            MAX(CASE WHEN pipe_data.row_no = 10 THEN pipe_data.pipe_no END) AS pipe_no_10,
            MAX(CASE WHEN pipe_data.row_no = 10 THEN pipe_data.date END) AS pipe_date_10,
            MAX(CASE WHEN pipe_data.row_no = 10 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_10,
            MAX(CASE WHEN pipe_data.row_no = 11 THEN pipe_data.pipe_no END) AS pipe_no_11,
            MAX(CASE WHEN pipe_data.row_no = 11 THEN pipe_data.date END) AS pipe_date_11,
            MAX(CASE WHEN pipe_data.row_no = 11 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_11,
            MAX(CASE WHEN pipe_data.row_no = 12 THEN pipe_data.pipe_no END) AS pipe_no_12,
            MAX(CASE WHEN pipe_data.row_no = 12 THEN pipe_data.date END) AS pipe_date_12,
            MAX(CASE WHEN pipe_data.row_no = 12 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_12,
            MAX(CASE WHEN pipe_data.row_no = 13 THEN pipe_data.pipe_no END) AS pipe_no_13,
            MAX(CASE WHEN pipe_data.row_no = 13 THEN pipe_data.date END) AS pipe_date_13,
            MAX(CASE WHEN pipe_data.row_no = 13 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_13,
            MAX(CASE WHEN pipe_data.row_no = 14 THEN pipe_data.pipe_no END) AS pipe_no_14,
            MAX(CASE WHEN pipe_data.row_no = 14 THEN pipe_data.date END) AS pipe_date_14,
            MAX(CASE WHEN pipe_data.row_no = 14 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_14,
            MAX(CASE WHEN pipe_data.row_no = 15 THEN pipe_data.pipe_no END) AS pipe_no_15,
            MAX(CASE WHEN pipe_data.row_no = 15 THEN pipe_data.date END) AS pipe_date_15,
            MAX(CASE WHEN pipe_data.row_no = 15 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_15,
            MAX(CASE WHEN pipe_data.row_no = 16 THEN pipe_data.pipe_no END) AS pipe_no_16,
            MAX(CASE WHEN pipe_data.row_no = 16 THEN pipe_data.date END) AS pipe_date_16,
            MAX(CASE WHEN pipe_data.row_no = 16 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_16,
            MAX(CASE WHEN pipe_data.row_no = 17 THEN pipe_data.pipe_no END) AS pipe_no_17,
            MAX(CASE WHEN pipe_data.row_no = 17 THEN pipe_data.date END) AS pipe_date_17,
            MAX(CASE WHEN pipe_data.row_no = 17 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_17,
            MAX(CASE WHEN pipe_data.row_no = 18 THEN pipe_data.pipe_no END) AS pipe_no_18,
            MAX(CASE WHEN pipe_data.row_no = 18 THEN pipe_data.date END) AS pipe_date_18,
            MAX(CASE WHEN pipe_data.row_no = 18 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_18,
            MAX(CASE WHEN pipe_data.row_no = 19 THEN pipe_data.pipe_no END) AS pipe_no_19,
            MAX(CASE WHEN pipe_data.row_no = 19 THEN pipe_data.date END) AS pipe_date_19,
            MAX(CASE WHEN pipe_data.row_no = 19 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_19,
            MAX(CASE WHEN pipe_data.row_no = 20 THEN pipe_data.pipe_no END) AS pipe_no_20,
            MAX(CASE WHEN pipe_data.row_no = 20 THEN pipe_data.date END) AS pipe_date_20,
            MAX(CASE WHEN pipe_data.row_no = 20 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_20,
            MAX(CASE WHEN pipe_data.row_no = 21 THEN pipe_data.pipe_no END) AS pipe_no_21,
            MAX(CASE WHEN pipe_data.row_no = 21 THEN pipe_data.date END) AS pipe_date_21,
            MAX(CASE WHEN pipe_data.row_no = 21 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_21,
            MAX(CASE WHEN pipe_data.row_no = 22 THEN pipe_data.pipe_no END) AS pipe_no_22,
            MAX(CASE WHEN pipe_data.row_no = 22 THEN pipe_data.date END) AS pipe_date_22,  
            MAX(CASE WHEN pipe_data.row_no = 22 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_22,
            MAX(CASE WHEN pipe_data.row_no = 23 THEN pipe_data.pipe_no END) AS pipe_no_23,
            MAX(CASE WHEN pipe_data.row_no = 23 THEN pipe_data.date END) AS pipe_date_23,
            MAX(CASE WHEN pipe_data.row_no = 23 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_23,
            MAX(CASE WHEN pipe_data.row_no = 24 THEN pipe_data.pipe_no END) AS pipe_no_24,
            MAX(CASE WHEN pipe_data.row_no = 24 THEN pipe_data.date END) AS pipe_date_24,
            MAX(CASE WHEN pipe_data.row_no = 24 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_24,
            MAX(CASE WHEN pipe_data.row_no = 25 THEN pipe_data.pipe_no END) AS pipe_no_25,
            MAX(CASE WHEN pipe_data.row_no = 25 THEN pipe_data.date END) AS pipe_date_25,
            MAX(CASE WHEN pipe_data.row_no = 25 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_25,
            MAX(CASE WHEN pipe_data.row_no = 26 THEN pipe_data.pipe_no END) AS pipe_no_26,
            MAX(CASE WHEN pipe_data.row_no = 26 THEN pipe_data.date END) AS pipe_date_26,
            MAX(CASE WHEN pipe_data.row_no = 26 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_26,
            MAX(CASE WHEN pipe_data.row_no = 27 THEN pipe_data.pipe_no END) AS pipe_no_27,
            MAX(CASE WHEN pipe_data.row_no = 27 THEN pipe_data.date END) AS pipe_date_27,
            MAX(CASE WHEN pipe_data.row_no = 27 THEN pipe_data.surveyor_name END) AS pipe_surveyor_name_27,
            MAX(CASE WHEN pipe_data.row_no = 28 THEN pipe_data.pipe_no END) AS pipe_no_28,
            MAX(CASE WHEN pipe_data.row_no = 28 THEN pipe_data.date END) AS pipe_date_28,
            MAX(CASE WHEN pipe_data.row_no = 28 THEN pipe_data.surveyor_name END) AS surveyor_name_28,
            MAX(CASE WHEN pipe_data.row_no = 29 THEN pipe_data.pipe_no END) AS pipe_no_29,
            MAX(CASE WHEN pipe_data.row_no = 29 THEN pipe_data.date END) AS pipe_date_29,
            MAX(CASE WHEN pipe_data.row_no = 29 THEN pipe_data.surveyor_name END) AS surveyor_name_29,
            MAX(CASE WHEN pipe_data.row_no = 30 THEN pipe_data.pipe_no END) AS pipe_no_30,
            MAX(CASE WHEN pipe_data.row_no = 30 THEN pipe_data.date END) AS pipe_date_30,
            MAX(CASE WHEN pipe_data.row_no = 30 THEN pipe_data.surveyor_name END) AS surveyor_name_30,
            MAX(CASE WHEN pipe_data.row_no = 31 THEN pipe_data.pipe_no END) AS pipe_no_31,
            MAX(CASE WHEN pipe_data.row_no = 31 THEN pipe_data.date END) AS pipe_date_31,
            MAX(CASE WHEN pipe_data.row_no = 31 THEN pipe_data.surveyor_name END) AS surveyor_name_31,
            MAX(CASE WHEN pipe_data.row_no = 32 THEN pipe_data.pipe_no END) AS pipe_no_32,
            MAX(CASE WHEN pipe_data.row_no = 32 THEN pipe_data.date END) AS pipe_date_32,
            MAX(CASE WHEN pipe_data.row_no = 32 THEN pipe_data.surveyor_name END) AS surveyor_name_32,
            MAX(CASE WHEN pipe_data.row_no = 33 THEN pipe_data.pipe_no END) AS pipe_no_33,
            MAX(CASE WHEN pipe_data.row_no = 33 THEN pipe_data.date END) AS pipe_date_33,
            MAX(CASE WHEN pipe_data.row_no = 33 THEN pipe_data.surveyor_name END) AS surveyor_name_33,
            MAX(CASE WHEN pipe_data.row_no = 34 THEN pipe_data.pipe_no END) AS pipe_no_34,
            MAX(CASE WHEN pipe_data.row_no = 34 THEN pipe_data.date END) AS pipe_date_34,
            MAX(CASE WHEN pipe_data.row_no = 34 THEN pipe_data.surveyor_name END) AS surveyor_name_34,
            MAX(CASE WHEN pipe_data.row_no = 35 THEN pipe_data.pipe_no END) AS pipe_no_35,
            MAX(CASE WHEN pipe_data.row_no = 35 THEN pipe_data.date END) AS pipe_date_35,
            MAX(CASE WHEN pipe_data.row_no = 35 THEN pipe_data.surveyor_name END) AS surveyor_name_35,
            MAX(CASE WHEN pipe_data.row_no = 36 THEN pipe_data.pipe_no END) AS pipe_no_36,
            MAX(CASE WHEN pipe_data.row_no = 36 THEN pipe_data.date END) AS pipe_date_36,
            MAX(CASE WHEN pipe_data.row_no = 36 THEN pipe_data.surveyor_name END) AS surveyor_name_36,
            MAX(CASE WHEN pipe_data.row_no = 37 THEN pipe_data.pipe_no END) AS pipe_no_37,
            MAX(CASE WHEN pipe_data.row_no = 37 THEN pipe_data.date END) AS pipe_date_37,
            MAX(CASE WHEN pipe_data.row_no = 37 THEN pipe_data.surveyor_name END) AS surveyor_name_37,
            MAX(CASE WHEN pipe_data.row_no = 38 THEN pipe_data.pipe_no END) AS pipe_no_38,
            MAX(CASE WHEN pipe_data.row_no = 38 THEN pipe_data.date END) AS pipe_date_38,
            MAX(CASE WHEN pipe_data.row_no = 38 THEN pipe_data.surveyor_name END) AS surveyor_name_38,
            MAX(CASE WHEN pipe_data.row_no = 39 THEN pipe_data.pipe_no END) AS pipe_no_39,
            MAX(CASE WHEN pipe_data.row_no = 39 THEN pipe_data.date END) AS pipe_date_39,
            MAX(CASE WHEN pipe_data.row_no = 39 THEN pipe_data.surveyor_name END) AS surveyor_name_39,
            MAX(CASE WHEN pipe_data.row_no = 40 THEN pipe_data.pipe_no END) AS pipe_no_40,
            MAX(CASE WHEN pipe_data.row_no = 40 THEN pipe_data.date END) AS pipe_date_40,
            MAX(CASE WHEN pipe_data.row_no = 40 THEN pipe_data.surveyor_name END) AS surveyor_name_40,
            MAX(CASE WHEN pipe_data.row_no = 41 THEN pipe_data.pipe_no END) AS pipe_no_41,
            MAX(CASE WHEN pipe_data.row_no = 41 THEN pipe_data.date END) AS pipe_date_41,
            MAX(CASE WHEN pipe_data.row_no = 41 THEN pipe_data.surveyor_name END) AS surveyor_name_41,
            MAX(CASE WHEN pipe_data.row_no = 42 THEN pipe_data.pipe_no END) AS pipe_no_42,
            MAX(CASE WHEN pipe_data.row_no = 42 THEN pipe_data.date END) AS pipe_date_42,
            MAX(CASE WHEN pipe_data.row_no = 42 THEN pipe_data.surveyor_name END) AS surveyor_name_42,
            MAX(CASE WHEN pipe_data.row_no = 43 THEN pipe_data.pipe_no END) AS pipe_no_43,
            MAX(CASE WHEN pipe_data.row_no = 43 THEN pipe_data.date END) AS pipe_date_43,
            MAX(CASE WHEN pipe_data.row_no = 43 THEN pipe_data.surveyor_name END) AS surveyor_name_43,
            MAX(CASE WHEN pipe_data.row_no = 44 THEN pipe_data.pipe_no END) AS pipe_no_44,
            MAX(CASE WHEN pipe_data.row_no = 44 THEN pipe_data.date END) AS pipe_date_44,
            MAX(CASE WHEN pipe_data.row_no = 44 THEN pipe_data.surveyor_name END) AS surveyor_name_44,
            MAX(CASE WHEN pipe_data.row_no = 45 THEN pipe_data.pipe_no END) AS pipe_no_45,
            MAX(CASE WHEN pipe_data.row_no = 45 THEN pipe_data.date END) AS pipe_date_45,
            MAX(CASE WHEN pipe_data.row_no = 45 THEN pipe_data.surveyor_name END) AS surveyor_name_45,
            MAX(CASE WHEN pipe_data.row_no = 46 THEN pipe_data.pipe_no END) AS pipe_no_46,
            MAX(CASE WHEN pipe_data.row_no = 46 THEN pipe_data.date END) AS pipe_date_46,
            MAX(CASE WHEN pipe_data.row_no = 46 THEN pipe_data.surveyor_name END) AS surveyor_name_46,
            MAX(CASE WHEN pipe_data.row_no = 47 THEN pipe_data.pipe_no END) AS pipe_no_47,
            MAX(CASE WHEN pipe_data.row_no = 47 THEN pipe_data.date END) AS pipe_date_47,
            MAX(CASE WHEN pipe_data.row_no = 47 THEN pipe_data.surveyor_name END) AS surveyor_name_47,
            MAX(CASE WHEN pipe_data.row_no = 48 THEN pipe_data.pipe_no END) AS pipe_no_48,
            MAX(CASE WHEN pipe_data.row_no = 48 THEN pipe_data.date END) AS pipe_date_48,
            MAX(CASE WHEN pipe_data.row_no = 48 THEN pipe_data.surveyor_name END) AS surveyor_name_48,
            MAX(CASE WHEN pipe_data.row_no = 49 THEN pipe_data.pipe_no END) AS pipe_no_49,
            MAX(CASE WHEN pipe_data.row_no = 49 THEN pipe_data.date END) AS pipe_date_49,
            MAX(CASE WHEN pipe_data.row_no = 49 THEN pipe_data.surveyor_name END) AS surveyor_name_49,
            MAX(CASE WHEN pipe_data.row_no = 50 THEN pipe_data.pipe_no END) AS pipe_no_50,
            MAX(CASE WHEN pipe_data.row_no = 50 THEN pipe_data.date END) AS pipe_date_50,
            MAX(CASE WHEN pipe_data.row_no = 50 THEN pipe_data.surveyor_name END) AS surveyor_name_50,
            MAX(CASE WHEN pipe_data.row_no = 51 THEN pipe_data.pipe_no END) AS pipe_no_51,
            MAX(CASE WHEN pipe_data.row_no = 51 THEN pipe_data.date END) AS pipe_date_51,
            MAX(CASE WHEN pipe_data.row_no = 51 THEN pipe_data.surveyor_name END) AS surveyor_name_51,

            aerations_data.total_aeration,
            MAX(CASE WHEN aerations_data.aeration_row_no = 1 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_1,
            MAX(CASE WHEN aerations_data.aeration_row_no = 1 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_1,
            MAX(CASE WHEN aerations_data.aeration_row_no = 1 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_1,
            MAX(CASE WHEN aerations_data.aeration_row_no = 1 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_1,
            MAX(CASE WHEN aerations_data.aeration_row_no = 2 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_2,
            MAX(CASE WHEN aerations_data.aeration_row_no = 2 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_2,
            MAX(CASE WHEN aerations_data.aeration_row_no = 2 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_2,
            MAX(CASE WHEN aerations_data.aeration_row_no = 2 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_2,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 3 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_3,
            MAX(CASE WHEN aerations_data.aeration_row_no = 3 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_3,
            MAX(CASE WHEN aerations_data.aeration_row_no = 3 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_3,
            MAX(CASE WHEN aerations_data.aeration_row_no = 3 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_3,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 4 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_4,
            MAX(CASE WHEN aerations_data.aeration_row_no = 4 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_4,
            MAX(CASE WHEN aerations_data.aeration_row_no = 4 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_4,
            MAX(CASE WHEN aerations_data.aeration_row_no = 4 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_4,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 5 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_5,
            MAX(CASE WHEN aerations_data.aeration_row_no = 5 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_5,
            MAX(CASE WHEN aerations_data.aeration_row_no = 5 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_5,
            MAX(CASE WHEN aerations_data.aeration_row_no = 5 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_5,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 6 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_6,
            MAX(CASE WHEN aerations_data.aeration_row_no = 6 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_6,
            MAX(CASE WHEN aerations_data.aeration_row_no = 6 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_6,
            MAX(CASE WHEN aerations_data.aeration_row_no = 6 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_6,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 7 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_7,
            MAX(CASE WHEN aerations_data.aeration_row_no = 7 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_7,
            MAX(CASE WHEN aerations_data.aeration_row_no = 7 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_7,
            MAX(CASE WHEN aerations_data.aeration_row_no = 7 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_7,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 8 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_8,
            MAX(CASE WHEN aerations_data.aeration_row_no = 8 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_8,
            MAX(CASE WHEN aerations_data.aeration_row_no = 8 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_8,
            MAX(CASE WHEN aerations_data.aeration_row_no = 8 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_8,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 9 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_9,
            MAX(CASE WHEN aerations_data.aeration_row_no = 9 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_9,
            MAX(CASE WHEN aerations_data.aeration_row_no = 9 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_9,
            MAX(CASE WHEN aerations_data.aeration_row_no = 9 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_9,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 10 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_10,
            MAX(CASE WHEN aerations_data.aeration_row_no = 10 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_10,
            MAX(CASE WHEN aerations_data.aeration_row_no = 10 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_10,
            MAX(CASE WHEN aerations_data.aeration_row_no = 10 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_10,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 11 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_11,
            MAX(CASE WHEN aerations_data.aeration_row_no = 11 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_11,
            MAX(CASE WHEN aerations_data.aeration_row_no = 11 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_11,
            MAX(CASE WHEN aerations_data.aeration_row_no = 11 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_11,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 12 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_12,
            MAX(CASE WHEN aerations_data.aeration_row_no = 12 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_12,
            MAX(CASE WHEN aerations_data.aeration_row_no = 12 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_12,
            MAX(CASE WHEN aerations_data.aeration_row_no = 12 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_12,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 13 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_13,
            MAX(CASE WHEN aerations_data.aeration_row_no = 13 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_13,
            MAX(CASE WHEN aerations_data.aeration_row_no = 13 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_13,
            MAX(CASE WHEN aerations_data.aeration_row_no = 13 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_13,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 14 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_14,
            MAX(CASE WHEN aerations_data.aeration_row_no = 14 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_14,
            MAX(CASE WHEN aerations_data.aeration_row_no = 14 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_14,
            MAX(CASE WHEN aerations_data.aeration_row_no = 14 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_14,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 15 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_15,
            MAX(CASE WHEN aerations_data.aeration_row_no = 15 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_15,
            MAX(CASE WHEN aerations_data.aeration_row_no = 15 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_15,
            MAX(CASE WHEN aerations_data.aeration_row_no = 15 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_15,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 16 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_16,
            MAX(CASE WHEN aerations_data.aeration_row_no = 16 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_16,
            MAX(CASE WHEN aerations_data.aeration_row_no = 16 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_16,
            MAX(CASE WHEN aerations_data.aeration_row_no = 16 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_16,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 17 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_17,
            MAX(CASE WHEN aerations_data.aeration_row_no = 17 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_17,
            MAX(CASE WHEN aerations_data.aeration_row_no = 17 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_17,
            MAX(CASE WHEN aerations_data.aeration_row_no = 17 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_17,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 18 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_18,
            MAX(CASE WHEN aerations_data.aeration_row_no = 18 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_18,
            MAX(CASE WHEN aerations_data.aeration_row_no = 18 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_18,
            MAX(CASE WHEN aerations_data.aeration_row_no = 18 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_18,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 19 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_19,
            MAX(CASE WHEN aerations_data.aeration_row_no = 19 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_19,
            MAX(CASE WHEN aerations_data.aeration_row_no = 19 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_19,
            MAX(CASE WHEN aerations_data.aeration_row_no = 19 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_19,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 20 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_20,
            MAX(CASE WHEN aerations_data.aeration_row_no = 20 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_20,
            MAX(CASE WHEN aerations_data.aeration_row_no = 20 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_20,
            MAX(CASE WHEN aerations_data.aeration_row_no = 20 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_20,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 21 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_21,
            MAX(CASE WHEN aerations_data.aeration_row_no = 21 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_21,
            MAX(CASE WHEN aerations_data.aeration_row_no = 21 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_21,
            MAX(CASE WHEN aerations_data.aeration_row_no = 21 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_21,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 22 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_22,
            MAX(CASE WHEN aerations_data.aeration_row_no = 22 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_22,
            MAX(CASE WHEN aerations_data.aeration_row_no = 22 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_22,
            MAX(CASE WHEN aerations_data.aeration_row_no = 22 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_22,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 23 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_23,
            MAX(CASE WHEN aerations_data.aeration_row_no = 23 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_23,
            MAX(CASE WHEN aerations_data.aeration_row_no = 23 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_23,
            MAX(CASE WHEN aerations_data.aeration_row_no = 23 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_23,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 24 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_24,
            MAX(CASE WHEN aerations_data.aeration_row_no = 24 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_24,
            MAX(CASE WHEN aerations_data.aeration_row_no = 24 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_24,
            MAX(CASE WHEN aerations_data.aeration_row_no = 24 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_24,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 25 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_25,
            MAX(CASE WHEN aerations_data.aeration_row_no = 25 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_25,
            MAX(CASE WHEN aerations_data.aeration_row_no = 25 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_25,
            MAX(CASE WHEN aerations_data.aeration_row_no = 25 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_25,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 26 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_26,
            MAX(CASE WHEN aerations_data.aeration_row_no = 26 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_26,
            MAX(CASE WHEN aerations_data.aeration_row_no = 26 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_26,
            MAX(CASE WHEN aerations_data.aeration_row_no = 26 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_26,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 27 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_27,
            MAX(CASE WHEN aerations_data.aeration_row_no = 27 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_27,
            MAX(CASE WHEN aerations_data.aeration_row_no = 27 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_27,
            MAX(CASE WHEN aerations_data.aeration_row_no = 27 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_27,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 28 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_28,
            MAX(CASE WHEN aerations_data.aeration_row_no = 28 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_28,
            MAX(CASE WHEN aerations_data.aeration_row_no = 28 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_28,
            MAX(CASE WHEN aerations_data.aeration_row_no = 28 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_28,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 29 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_29,
            MAX(CASE WHEN aerations_data.aeration_row_no = 29 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_29,
            MAX(CASE WHEN aerations_data.aeration_row_no = 29 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_29,
            MAX(CASE WHEN aerations_data.aeration_row_no = 29 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_29,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 30 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_30,
            MAX(CASE WHEN aerations_data.aeration_row_no = 30 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_30,
            MAX(CASE WHEN aerations_data.aeration_row_no = 30 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_30,
            MAX(CASE WHEN aerations_data.aeration_row_no = 30 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_30,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 31 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_31,
            MAX(CASE WHEN aerations_data.aeration_row_no = 31 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_31,
            MAX(CASE WHEN aerations_data.aeration_row_no = 31 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_31,
            MAX(CASE WHEN aerations_data.aeration_row_no = 31 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_31,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 32 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_32,
            MAX(CASE WHEN aerations_data.aeration_row_no = 32 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_32,
            MAX(CASE WHEN aerations_data.aeration_row_no = 32 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_32,
            MAX(CASE WHEN aerations_data.aeration_row_no = 32 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_32,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 33 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_33,
            MAX(CASE WHEN aerations_data.aeration_row_no = 33 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_33,
            MAX(CASE WHEN aerations_data.aeration_row_no = 33 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_33,
            MAX(CASE WHEN aerations_data.aeration_row_no = 33 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_33,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 34 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_34,
            MAX(CASE WHEN aerations_data.aeration_row_no = 34 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_34,
            MAX(CASE WHEN aerations_data.aeration_row_no = 34 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_34,
            MAX(CASE WHEN aerations_data.aeration_row_no = 34 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_34,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 35 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_35,
            MAX(CASE WHEN aerations_data.aeration_row_no = 35 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_35,
            MAX(CASE WHEN aerations_data.aeration_row_no = 35 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_35,
            MAX(CASE WHEN aerations_data.aeration_row_no = 35 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_35,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 36 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_36,
            MAX(CASE WHEN aerations_data.aeration_row_no = 36 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_36,
            MAX(CASE WHEN aerations_data.aeration_row_no = 36 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_36,
            MAX(CASE WHEN aerations_data.aeration_row_no = 36 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_36,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 37 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_37,
            MAX(CASE WHEN aerations_data.aeration_row_no = 37 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_37,
            MAX(CASE WHEN aerations_data.aeration_row_no = 37 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_37,
            MAX(CASE WHEN aerations_data.aeration_row_no = 37 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_37,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 38 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_38,
            MAX(CASE WHEN aerations_data.aeration_row_no = 38 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_38,
            MAX(CASE WHEN aerations_data.aeration_row_no = 38 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_38,
            MAX(CASE WHEN aerations_data.aeration_row_no = 38 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_38,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 39 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_39,
            MAX(CASE WHEN aerations_data.aeration_row_no = 39 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_39,
            MAX(CASE WHEN aerations_data.aeration_row_no = 39 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_39,
            MAX(CASE WHEN aerations_data.aeration_row_no = 39 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_39,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 40 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_40,
            MAX(CASE WHEN aerations_data.aeration_row_no = 40 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_40,
            MAX(CASE WHEN aerations_data.aeration_row_no = 40 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_40,
            MAX(CASE WHEN aerations_data.aeration_row_no = 40 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_40,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 41 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_41,
            MAX(CASE WHEN aerations_data.aeration_row_no = 41 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_41,
            MAX(CASE WHEN aerations_data.aeration_row_no = 41 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_41,
            MAX(CASE WHEN aerations_data.aeration_row_no = 41 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_41,
            
            MAX(CASE WHEN aerations_data.aeration_row_no = 42 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_42,
            MAX(CASE WHEN aerations_data.aeration_row_no = 42 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_42,
            MAX(CASE WHEN aerations_data.aeration_row_no = 42 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_42,
            MAX(CASE WHEN aerations_data.aeration_row_no = 42 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_42,

            MAX(CASE WHEN aerations_data.aeration_row_no = 43 THEN aerations_data.aerations_pipe_no END) AS aerations_pipe_no_43,
            MAX(CASE WHEN aerations_data.aeration_row_no = 43 THEN aerations_data.aerations_aeration_no END) AS aerations_aeration_no_43,
            MAX(CASE WHEN aerations_data.aeration_row_no = 43 THEN aerations_data.aerations_date_survey END) AS aerations_date_survey_43,
            MAX(CASE WHEN aerations_data.aeration_row_no = 43 THEN aerations_data.aerationsurveyor_name END) AS aerationsurveyor_name_43


        FROM 
            final_farmers f
        JOIN (
            SELECT 
                pipe_installation_pipeimg.pipe_no,
                pipe_installation_pipeimg.date,
                pipe_installation_pipeimg.farmer_plot_uniqueid,
                ROW_NUMBER() OVER (PARTITION BY pipe_installation_pipeimg.farmer_plot_uniqueid ORDER BY id) AS row_no,
                COUNT(*) OVER (PARTITION BY pipe_installation_pipeimg.farmer_plot_uniqueid) AS total_pipe_installation,
                u.name as surveyor_name
            FROM 
                pipe_installation_pipeimg
            left join users u on pipe_installation_pipeimg.surveyor_id = u.id
            ORDER BY pipe_installation_pipeimg.id DESC
        ) AS pipe_data ON f.farmer_plot_uniqueid = pipe_data.farmer_plot_uniqueid

        Left Join (
            SELECT 
                aerations.pipe_no as aerations_pipe_no,
                aerations.aeration_no as aerations_aeration_no,
                aerations.date_survey as aerations_date_survey,
                aerations.farmer_plot_uniqueid,
                ROW_NUMBER() OVER (PARTITION BY aerations.farmer_plot_uniqueid ORDER BY id) AS aeration_row_no,
                COUNT(*) OVER (PARTITION BY aerations.farmer_plot_uniqueid) AS total_aeration,
                u.name as aerationsurveyor_name
            FROM 
                aerations
            left join users u on aerations.surveyor_id = u.id
            ORDER BY aerations.id DESC
        ) AS aerations_data ON f.farmer_plot_uniqueid = aerations_data.farmer_plot_uniqueid

        GROUP BY  f.farmer_uniqueId
        order by aerations_data.total_aeration desc;
        ");

        // Convert data to CSV format
        if (empty($data)) {
            return response()->json(['message' => 'No data found'], 404);
        }

        // Create CSV content
        $csvContent = '';
        
        // Get headers from first row
        $headers = array_keys((array)$data[0]);
        $csvContent .= implode(',', $headers) . "\n";
        
        // Add data rows
        foreach ($data as $row) {
            $rowArray = (array)$row;
            $csvRow = [];
            foreach ($rowArray as $value) {
                // Escape CSV values
                $value = str_replace('"', '""', $value);
                $csvRow[] = '"' . $value . '"';
            }
            $csvContent .= implode(',', $csvRow) . "\n";
        }

        // Generate filename with timestamp
        $filename = 'farmer_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for CSV download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        return response($csvContent, 200, $headers);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error generating CSV',
            'error' => $e->getMessage(),
            'line' => $e->getLine()
        ], 500);
    }
}
public function genrate_geojson_data(Request $request)
{
    try {
        // Set comprehensive timeout and memory settings
    set_time_limit(0);
    ini_set('memory_limit', '72000M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    
    if (function_exists('ignore_user_abort')) {
        ignore_user_abort(true);
    }
    
    if (!headers_sent()) {
        header('X-Accel-Buffering: no');
    }

    // Check if a file was uploaded
    if (!$request->hasFile('file')) {
        return response()->json(['message' => 'No file uploaded'], 400);
    }

    $file = $request->file('file');

    // Extract Farmer Unique IDs from the uploaded Excel file
    $farmerUniqueIds = [];
    $data = Excel::toArray([], $file);
    \DB::beginTransaction();
    foreach ($data[0] as $rowIndex => $row) {
        if ($rowIndex === 0) {
            // Skip the header row
            continue;
        }

        //Farmers' name	ONBOARDING ID	New Id	Old Land (Ha)	Polygon area	Mobile No.	village	Volunteer Name	Block/ Taluka	Gram Panchayat	District	State
        $farmerName = trim($row[0]);
        $farmerId = trim($row[2]);
        $areainAcre = round(floatval($row[3] ?? 0), 2);
        $mobileNo = trim($row[5]);
        $villageName = trim($row[6]);
        $volunteerName = trim($row[7]);
        $blockName = trim($row[8]);
        $panchayatName = trim($row[9]);
        $district_name = trim($row[10]);
        $state_name = trim($row[11]);

        $state = State::where('name', $state_name)->first();
        $district = District::where('district', $district_name)->where('state_id', $state->id??null)->first();
        $block = Taluka::where('taluka', $blockName)->where('district_id', $district->id??null)->first();

        $panchayat = Panchayat::where('panchayat', $panchayatName)->where('taluka_id', $block->id??null)->first();

        $village = Village::where('village', $villageName)->where('panchayat_id', $panchayat->id??null)->first();
 
        $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerId)->latest()->first();
        if($finalFarmer){
            $plotNo = $finalFarmer->plot_no + 1;
        }else{
            $plotNo = 1;
        }
        $finalFarmer = new FinalFarmer;
        $finalFarmer->farmer_name = $farmerName;
        $finalFarmer->farmer_uniqueId = $farmerId;
        $finalFarmer->farmer_plot_uniqueid = $farmerId . 'P' . $plotNo;
        $finalFarmer->total_plot_area = $areainAcre;
        $finalFarmer->available_area = $areainAcre;
        $finalFarmer->area_in_acers = $areainAcre;
        $finalFarmer->plot_no = $plotNo;
        $finalFarmer->own_area_in_acres = $areainAcre;
        $finalFarmer->plot_area = $areainAcre;
        $finalFarmer->land_ownership = "Own";
        $finalFarmer->actual_owner_name = $farmerName;
        $finalFarmer->final_status = "Approved";
        $finalFarmer->onboard_completed = "Approved";
        $finalFarmer->financial_year = "2025-2025";
        $finalFarmer->season = "Kharif";
        $finalFarmer->country_id = 101;
        $finalFarmer->state_id = $state->id??null;
        $finalFarmer->final_status_onboarding = "Completed";
        $finalFarmer->status_onboarding = "Completed";
        $finalFarmer->onboarding_form = "1";
        $finalFarmer->district_id = $district->id??null;
        $finalFarmer->taluka_id = $block->id??null;
        $finalFarmer->panchayat_id = $panchayat->id??null;
        $finalFarmer->village_id = $village->id??null;
        $finalFarmer->block_name = $blockName;
        $finalFarmer->panchayat_name = $panchayatName;
        $finalFarmer->village_name = $villageName;
        $finalFarmer->surveyor_name = $volunteerName;
        $finalFarmer->save();
        $farmerPlot = new FarmerPlot;
        $farmerPlot->farmer_id = $finalFarmer->id;
        $farmerPlot->farmer_uniqueId = $farmerId;
        $farmerPlot->farmer_plot_uniqueid = $farmerId . 'P' . $plotNo;
        $farmerPlot->plot_no = $plotNo;
        $farmerPlot->area_in_acers = $areainAcre;
        $farmerPlot->area_in_other = $areainAcre;
        $farmerPlot->area_in_other_unit = $areainAcre;
        $farmerPlot->area_acre_awd = $areainAcre;
        $farmerPlot->area_other_awd = $areainAcre;
        $farmerPlot->area_other_awd_unit = $areainAcre;
        $farmerPlot->land_ownership = "Own";
        $farmerPlot->actual_owner_name = $farmerName;
        $farmerPlot->final_status = "Approved";
        $farmerPlot->status = "Approved";
        $farmerPlot->save();

    }

    \DB::commit();

    return response()->json(['message' => 'Farmer Uploaded Successfully',"total_farmers" => ($rowIndex+1)], 200);
    } catch (\Throwable $th) {
        \DB::rollBack();
        return response()->json(['message' => 'Farmer Uploaded Failed',"error" => $th->getMessage()], 500);
    }
    
}
public function genrate_geojson_used_for(Request $request)
{
    // Set comprehensive timeout and memory settings
    set_time_limit(0);
    ini_set('memory_limit', '72000M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    
    if (function_exists('ignore_user_abort')) {
        ignore_user_abort(true);
    }
    
    if (!headers_sent()) {
        header('X-Accel-Buffering: no');
    }

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
        
        $farmerId = preg_replace('/[cC]/', '', (string) $row[2]); // Remove 'c' and 'C'
        $farmerId = preg_replace('/\D+/', '', $farmerId); // Remove all non-digits
        $farmerId = ltrim($farmerId, '0'); // Remove leading zeros
        $finalFarmer = FinalFarmer::where('farmer_uniqueId', $farmerId)->get();
        if($finalFarmer->count() > 0){
            foreach($finalFarmer as $farmer){
                $blockName = $row[6];
                // if($blockName == 'Kushamndi'){
                //     $blockName = 'Kushmandi';
                // }
                try {
                    $block = Taluka::where('taluka', $blockName)->where('district_id', $farmer->district_id)->first();
                } catch (\Throwable $th) {
                   Log::info("Block ".$blockName);
                }
                
                $panchayatName = $row[9];
                // if($panchayatName == 'Berail'){
                //     $panchayatName = 'Beroil';
                // }
                
                try {
                    $panchayat = Panchayat::where('panchayat', $panchayatName)->where('taluka_id', $block->id)->first();
                } catch (\Throwable $th) {
                   Log::info("Block ".$blockName);
                }
                        
                $villageName = $row[10];

                try {
                    $village = Village::where('village', $villageName)->where('panchayat_id', $panchayat->id)->first();
                } catch (\Throwable $th) {
                   Log::info("Panchayat ".$panchayatName);
                }
                if(isset($village) && $village){

                }else{
                   Log::info("Village ".$villageName);
                }
                // $village = Village::where('village', $villageName)->where('panchayat_id', $panchayat->id)->first();
                // dd($block,$panchayat,$village);
            }
        }
        // $farmerUniqueIds[] = $row[3]; // Assuming column index 2 (3rd column)
    }
    return response()->json(['message' => 'Farmer Location Updated Successfully'], 200);
    die;
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
    // Set comprehensive timeout and memory settings
    set_time_limit(0);
    ini_set('memory_limit', '64000M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    
    if (function_exists('ignore_user_abort')) {
        ignore_user_abort(true);
    }
    
    if (!headers_sent()) {
        header('X-Accel-Buffering: no');
    }

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
    // Set comprehensive timeout and memory settings
    set_time_limit(0);
    ini_set('memory_limit', '64000M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    
    if (function_exists('ignore_user_abort')) {
        ignore_user_abort(true);
    }
    
    if (!headers_sent()) {
        header('X-Accel-Buffering: no');
    }

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
    // Set comprehensive timeout and memory settings
    set_time_limit(0);
    ini_set('memory_limit', '64000M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    
    if (function_exists('ignore_user_abort')) {
        ignore_user_abort(true);
    }
    
    if (!headers_sent()) {
        header('X-Accel-Buffering: no');
    }

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
    // Set comprehensive timeout and memory settings
    set_time_limit(0);
    ini_set('memory_limit', '250000M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    
    if (function_exists('ignore_user_abort')) {
        ignore_user_abort(true);
    }
    
    if (!headers_sent()) {
        header('X-Accel-Buffering: no');
    }

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
        // Set comprehensive timeout and memory settings
        set_time_limit(0);
        ini_set('memory_limit', '64000M');
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);
        
        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }
        
        if (!headers_sent()) {
            header('X-Accel-Buffering: no');
        }
 
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

    private function createLocationHierarchy($finalFarmer, $districtName, $blockName, $panchayatName, $villageName)
    {
        // Create District
        if (!empty($districtName)) {
            $district = District::whereRaw("CONVERT(`district` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$districtName])
                ->where('state_id', $finalFarmer->state_id)->first();
            if ($district) {
                $finalFarmer->district_id = $district->id ?? null;
            }
            
        }

        // Create Taluka (Block)
        if (!empty($blockName) && !empty($finalFarmer->district_id)) {
            $taluka = Taluka::whereRaw("CONVERT(`taluka` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$blockName])
                ->where('district_id', $finalFarmer->district_id)->where('state_id', $finalFarmer->state_id)->first();
            if ($taluka) {
                $finalFarmer->taluka_id = $taluka->id ?? null;
            }
            
        }

        // Create Panchayat
        if (!empty($panchayatName) && !empty($finalFarmer->taluka_id) && !empty($finalFarmer->district_id)) {
            $panchayat = Panchayat::whereRaw("CONVERT(`panchayat` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$panchayatName])
                ->where('taluka_id', $finalFarmer->taluka_id)
                ->where('district_id', $finalFarmer->district_id)
                ->where('state_id', $finalFarmer->state_id)->first();
            if ($panchayat) {
                $finalFarmer->panchayat_id = $panchayat->id ?? null;
            }
            
        }

        // Create Village
        if (!empty($villageName) && !empty($finalFarmer->panchayat_id) && !empty($finalFarmer->taluka_id) && !empty($finalFarmer->district_id)) {
            $village = Village::whereRaw("CONVERT(`village` USING utf8mb4) COLLATE utf8mb4_unicode_ci = ?", [$villageName])
                ->where('panchayat_id', $finalFarmer->panchayat_id)
                ->where('taluka_id', $finalFarmer->taluka_id)
                ->where('district_id', $finalFarmer->district_id)
                ->where('state_id', $finalFarmer->state_id)->first();
            if ($village) {
                $finalFarmer->village_id = $village->id ?? null;
            }
            $finalFarmer->village_id = $village->id ?? null;
        }

        $finalFarmer->save();
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