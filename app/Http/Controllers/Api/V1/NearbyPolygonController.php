<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Polygon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NearbyPolygonController extends Controller
{
    /**
     * Find nearby polygons based on coordinates and radius
     * Enhanced version with farmer and plot details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function nearby(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:0.001|max:1', // radius in kilometers (like original)
                'limit' => 'nullable|integer|min:1|max:100',
                'final_status' => 'nullable|string',
                'season' => 'nullable|string',
                'financial_year' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->filled('radius') ? $request->radius : 1;//0.05; // default 0.05 km (50 meters)
            $limit = $request->filled('limit') ? $request->limit : 10;

            // Get all polygons with ranges data
            $allPolygons = Polygon::whereNotNull('ranges')
                ->where('delete_polygon', '0')
                ->with([
                    'farmerapproved',
                    'FormSubmitBy:id,name,email',
                    'seasons:id,name',
                    'surveyor:id,name,email'
                ])
                ->get();

            $nearbyPolygons = collect();

            foreach ($allPolygons as $polygon) {
                $coordinates = json_decode($polygon->ranges, true);
                
                if (!$coordinates || !is_array($coordinates)) {
                    continue;
                }

                $isNearby = false;
                $minDistance = PHP_FLOAT_MAX;

                // Check each coordinate in the polygon
                foreach ($coordinates as $coord) {
                    if (isset($coord['lat']) && isset($coord['lng'])) {
                        $distance = $this->haversine($lat, $lng, $coord['lat'], $coord['lng']);
                        $minDistance = min($minDistance, $distance);
                        
                        // If any coordinate within the polygon is within the radius, consider the entire polygon
                        if ($distance <= $radius) {
                            $isNearby = true;
                            break;
                        }
                    }
                }

                if ($isNearby) {
                    // Apply additional filters - only if values are provided and not empty
                    if ($request->filled('final_status') && $polygon->final_status !== $request->final_status) {
                        continue;
                    }
                    if ($request->filled('season') && $polygon->season !== $request->season) {
                        continue;
                    }
                    if ($request->filled('financial_year') && $polygon->financial_year !== $request->financial_year) {
                        continue;
                    }

                    // Add distance to polygon data
                    $polygon->distance = $minDistance;
                    $nearbyPolygons->push($polygon);
                }
            }

            // Sort by distance and limit results
            $nearbyPolygons = $nearbyPolygons->sortBy('distance')->take($limit);

            // Format response (also include pipe_location from pipe_installation_pipeimg)
            $formattedResponse = $nearbyPolygons->map(function ($polygon) use ($request) {
                // Build query to fetch pipe locations for this polygon
                $pipeQuery = DB::table('pipe_installation_pipeimg')
                    ->select('lat', 'lng', 'pipe_no')
                    ->where('farmer_plot_uniqueid',$polygon->farmer_plot_uniqueid)
                    ->where('plot_no',$polygon->plot_no);

                if ($request->filled('financial_year')) {
                    $pipeQuery->where('financial_year', $request->financial_year);
                }
                if ($request->filled('season')) {
                    $pipeQuery->where('season', $request->season);
                }
                // dd($polygon->farmer_plot_uniqueid,$polygon->plot_no, $pipeQuery->get());
                $pipeLocation = $pipeQuery->get()->map(function ($row) {
                    return [
                        'lat' => (float) $row->lat,
                        'lng' => (float) $row->lng,
                        'pipe_no' => (int) $row->pipe_no,
                    ];
                })->values();


                

                    // ------------------------ New Pipe Data ------------------------
                    $pipeNewData = DB::table('Pipe')
                    ->select('lat', 'lng', 'plot_no','pipe_count as pipe_no')
                    ->where('farmerPlotUniqueid',$polygon->farmer_plot_uniqueid)
                    ->where('plot_no',$polygon->plot_no);

                    if ($request->filled('financial_year')) {
                        $pipeNewData->where('select_season', $request->financial_year);
                    }
                    if ($request->filled('season')) {
                        $pipeNewData->where('select_year', $request->season);
                    }
                    
                    $pipeNewLocation = $pipeNewData->get()->map(function ($row) {
                        return [
                            'lat' => (float) $row->lat,
                            'lng' => (float) $row->lng,
                            'pipe_no' => (int) $row->pipe_no,
                        ];
                    })->values();

                    // dd($pipeNewLocation);

                    $pipeLocation = array_merge($pipeLocation->toArray(), $pipeNewLocation->toArray());
                    // ------------------------ New Pipe Data ------------------------
                

                return [
                    'polygon_id' => $polygon->id,
                    'farmer_plot_uniqueid' => $polygon->farmer_plot_uniqueid,
                    'plot_no' => $polygon->plot_no,
                    'latitude' => $polygon->latitude,
                    'longitude' => $polygon->longitude,
                    'plot_area' => $polygon->plot_area,
                    'area_units' => $polygon->area_units,
                    'ranges' => json_decode($polygon->ranges, true),
                    'distance' => round($polygon->distance, 4), // distance in km
                    'pipe_location' => $pipeLocation,
                ];
            });

            if ($formattedResponse->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No nearby polygons found',
                    'data' => [],
                    'meta' => [
                        'total_found' => 0,
                        'search_radius' => $radius . ' km',
                        'center_coordinates' => [
                            'latitude' => $lat,
                            'longitude' => $lng
                        ]
                    ]
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $formattedResponse->values(),
                'message' => 'Nearby polygons retrieved successfully',
                'meta' => [
                    'total_found' => $formattedResponse->count(),
                    'search_radius' => $radius . ' km',
                    'center_coordinates' => [
                        'latitude' => $lat,
                        'longitude' => $lng
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for nearby polygons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Haversine formula to calculate distance between two coordinates
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in kilometers
     */
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $radius = 6371; // Earth's radius in kilometers

        return $radius * $c;
    }

    /**
     * Check if coordinates are within any existing polygon
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkCoordinates(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'tolerance' => 'nullable|numeric|min:0.0001|max:1' // tolerance in degrees
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $tolerance = $request->filled('tolerance') ? $request->tolerance : 0.001; // default tolerance

            // Find polygons within tolerance
            $polygons = Polygon::whereBetween(DB::raw('CAST(latitude AS DECIMAL(10,8))'), [
                $latitude - $tolerance,
                $latitude + $tolerance
            ])
            ->whereBetween(DB::raw('CAST(longitude AS DECIMAL(11,8))'), [
                $longitude - $tolerance,
                $longitude + $tolerance
            ])
            ->where('delete_polygon', '0')
            ->with([
                'farmerapproved',
                'FormSubmitBy:id,name,email',
                'seasons:id,name'
            ])
            ->get();

            $isWithinPolygon = $polygons->count() > 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'is_within_polygon' => $isWithinPolygon,
                    'polygons_found' => $polygons,
                    'coordinates' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ],
                    'tolerance_used' => $tolerance
                ],
                'message' => $isWithinPolygon 
                    ? 'Coordinates are within existing polygon(s)' 
                    : 'Coordinates are not within any existing polygon'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking coordinates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get polygon statistics within a radius
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:1|max:10000' // radius in meters
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->filled('radius') ? $request->radius : 15; // in meters, default 15 meters

            // Get statistics
            $stats = Polygon::select([
                DB::raw('COUNT(*) as total_polygons'),
                DB::raw('COUNT(CASE WHEN final_status = "approved" THEN 1 END) as approved_count'),
                DB::raw('COUNT(CASE WHEN final_status = "pending" THEN 1 END) as pending_count'),
                DB::raw('COUNT(CASE WHEN final_status = "rejected" THEN 1 END) as rejected_count'),
                DB::raw('SUM(plot_area) as total_area'),
                DB::raw('AVG(plot_area) as average_area'),
                DB::raw('MIN(plot_area) as min_area'),
                DB::raw('MAX(plot_area) as max_area')
            ])
            ->whereRaw("(
                6371 * acos(
                    cos(radians(?)) * cos(radians(CAST(latitude AS DECIMAL(10,8)))) * 
                    cos(radians(CAST(longitude AS DECIMAL(11,8))) - radians(?)) + 
                    sin(radians(?)) * sin(radians(CAST(latitude AS DECIMAL(10,8))))
                )
            ) <= ?", [$latitude, $longitude, $latitude, $radius / 1000]) // convert meters to kilometers
            ->where('delete_polygon', '0')
            ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats,
                    'search_radius' => $radius . ' meters',
                    'center_coordinates' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]
                ],
                'message' => 'Statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get polygons within a bounding box
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWithinBounds(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'north' => 'required|numeric|between:-90,90',
                'south' => 'required|numeric|between:-90,90',
                'east' => 'required|numeric|between:-180,180',
                'west' => 'required|numeric|between:-180,180',
                'limit' => 'nullable|integer|min:1|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $north = $request->north;
            $south = $request->south;
            $east = $request->east;
            $west = $request->west;
            $limit = $request->filled('limit') ? $request->limit : 100;

            // Validate bounding box
            if ($north <= $south || $east <= $west) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid bounding box coordinates'
                ], 422);
            }

            $polygons = Polygon::whereBetween(DB::raw('CAST(latitude AS DECIMAL(10,8))'), [$south, $north])
                ->whereBetween(DB::raw('CAST(longitude AS DECIMAL(11,8))'), [$west, $east])
                ->where('delete_polygon', '0')
                ->with([
                    'farmerapproved',
                    'FormSubmitBy:id,name,email',
                    'seasons:id,name',
                    'surveyor:id,name,email'
                ])
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $polygons,
                'message' => 'Polygons within bounds retrieved successfully',
                'meta' => [
                    'total_found' => $polygons->count(),
                    'bounding_box' => [
                        'north' => $north,
                        'south' => $south,
                        'east' => $east,
                        'west' => $west
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving polygons within bounds',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search polygons by farmer details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchByFarmer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'farmer_uniqueId' => 'nullable|string',
                'farmer_plot_uniqueid' => 'nullable|string',
                'surveyor_id' => 'nullable|integer',
                'final_status' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Polygon::query();

            // Apply filters - only if values are provided and not empty
            if ($request->filled('farmer_uniqueId')) {
                $query->where('farmer_uniqueId', 'like', '%' . $request->farmer_uniqueId . '%');
            }

            if ($request->filled('farmer_plot_uniqueid')) {
                $query->where('farmer_plot_uniqueid', 'like', '%' . $request->farmer_plot_uniqueid . '%');
            }

            if ($request->filled('surveyor_id')) {
                $query->where('surveyor_id', $request->surveyor_id);
            }

            if ($request->filled('final_status')) {
                $query->where('final_status', $request->final_status);
            }

            // Exclude soft deleted polygons
            $query->where('delete_polygon', '0');

            $limit = $request->filled('limit') ? $request->limit : 20;
            $polygons = $query->with([
                'farmerapproved',
                'FormSubmitBy:id,name,email',
                'seasons:id,name',
                'surveyor:id,name,email'
            ])
            ->limit($limit)
            ->get();

            return response()->json([
                'success' => true,
                'data' => $polygons,
                'message' => 'Polygons found successfully',
                'meta' => [
                    'total_found' => $polygons->count(),
                    'filters_applied' => $request->only([
                        'farmer_uniqueId', 'farmer_plot_uniqueid', 
                        'surveyor_id', 'final_status'
                    ])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching polygons',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
