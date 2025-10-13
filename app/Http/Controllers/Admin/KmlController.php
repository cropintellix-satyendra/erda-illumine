<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KmlController extends Controller
{
    /**
     * Display KML viewer page
     */
    public function viewer()
    {
        // Get all KML files from storage
        $kmlFiles = [];
        
        // Check if kml directory exists in storage/app/public
        if (Storage::disk('public')->exists('kml')) {
            $files = Storage::disk('public')->files('kml');
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'kml') {
                    $filename = basename($file);
                    $kmlFiles[] = [
                        'name' => $filename,
                        'path' => $file,
                        'url' => route('admin.kml.content', ['filename' => $filename]),
                        'size' => Storage::disk('public')->size($file),
                        'modified' => Storage::disk('public')->lastModified($file)
                    ];
                }
            }
        }
        
        $action = 'kml_viewer';
        return view('admin.kml.viewer', compact('kmlFiles', 'action'));
    }

    /**
     * Display KML upload page
     */
    public function upload()
    {
        $action = 'kml_upload';
        return view('admin.kml.upload', compact('action'));
    }

    /**
     * Display Analyze Polygon page
     */
    public function analyze()
    {
        // Get all KML files from storage
        $kmlFiles = [];
        
        if (Storage::disk('public')->exists('kml')) {
            $files = Storage::disk('public')->files('kml');
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'kml') {
                    $filename = basename($file);
                    $kmlFiles[] = [
                        'name' => $filename,
                        'path' => $file,
                        'url' => route('admin.kml.content', ['filename' => $filename]),
                        'size' => Storage::disk('public')->size($file),
                        'modified' => Storage::disk('public')->lastModified($file)
                    ];
                }
            }
        }
        
        $action = 'kml_analyze';
        return view('admin.kml.analyze', compact('kmlFiles', 'action'));
    }

    /**
     * Compare KML with Database Polygons
     */
    public function compareKml($filename)
    {
        try {
            \Log::info("=== START KML COMPARISON ===");
            \Log::info("KML Filename: " . $filename);
            
            $path = 'kml/' . $filename;
            
            if (!Storage::disk('public')->exists($path)) {
                \Log::error("KML file not found at path: " . $path);
                return response()->json(['error' => 'KML file not found'], 404);
            }
            
            \Log::info("KML file found, reading content...");
            
            // Read KML file content
            $kmlContent = Storage::disk('public')->get($path);
            \Log::info("KML content length: " . strlen($kmlContent));
            
            // Parse KML to get coordinates
            \Log::info("Parsing KML coordinates...");
            $kmlRanges = $this->parseKmlCoordinates($kmlContent);
            \Log::info("KML polygons parsed: " . count($kmlRanges));
            
            if (count($kmlRanges) > 0) {
                \Log::info("First KML polygon sample:");
                \Log::info(json_encode($kmlRanges[0]));
            }
            
            // Get polygon ranges from database
            \Log::info("Fetching polygons from database...");
            $dbPolygons = \App\Models\Polygon::whereNotNull('ranges')
                ->select('id', 'farmer_plot_uniqueid', 'ranges', 'plot_no')
                ->get();
            
            \Log::info("DB polygons fetched: " . $dbPolygons->count());
            
            $matched = [];
            $unmatched = [];
            $dbRanges = [];
            
            foreach ($dbPolygons as $polygon) {
                $ranges = json_decode($polygon->ranges, true);
                if ($ranges && is_array($ranges) && count($ranges) > 0) {
                    $dbRanges[] = [
                        'id' => $polygon->id,
                        'plot_uniqueid' => $polygon->farmer_plot_uniqueid,
                        'plot_no' => $polygon->plot_no,
                        'ranges' => $ranges
                    ];
                }
            }
            
            \Log::info("DB polygons with valid ranges: " . count($dbRanges));
            
            if (count($dbRanges) > 0) {
                \Log::info("First DB polygon sample:");
                \Log::info("Plot No: " . $dbRanges[0]['plot_no']);
                \Log::info("Ranges count: " . count($dbRanges[0]['ranges']));
                \Log::info("First range: " . json_encode($dbRanges[0]['ranges'][0]));
            }
            
            // Set unlimited time limit for complete processing
            set_time_limit(0); // Unlimited timeout
            ini_set('max_execution_time', 0); // Also set via ini
            
            $totalKml = count($kmlRanges);
            \Log::info("Starting comparison of ALL $totalKml KML polygons...");
            
            foreach ($kmlRanges as $kmlIndex => $kmlRange) {
                $matchFound = false;
                
                // Progress logging every 50 polygons
                if (($kmlIndex + 1) % 50 === 0) {
                    \Log::info("Progress: Checked " . ($kmlIndex + 1) . "/$totalKml KML polygons...");
                }
                
                foreach ($dbRanges as $dbIndex => $dbRange) {
                    if ($this->rangesMatch($kmlRange['ranges'], $dbRange['ranges'])) {
                        \Log::info("MATCH FOUND! KML polygon #" . ($kmlIndex + 1) . " matches DB polygon (Plot: " . $dbRange['plot_no'] . ")");
                        $matched[] = [
                            'kml_polygon' => $kmlRange,
                            'db_polygon' => $dbRange
                        ];
                        $matchFound = true;
                        break;
                    }
                }
                
                if (!$matchFound) {
                    $unmatched[] = $kmlRange;
                }
            }
            
            \Log::info("Comparison complete!");
            \Log::info("Matched: " . count($matched));
            \Log::info("Unmatched: " . count($unmatched));
            \Log::info("=== END KML COMPARISON ===");
            
            return response()->json([
                'success' => true,
                'total_kml_polygons' => count($kmlRanges),
                'total_db_polygons' => count($dbRanges),
                'matched_count' => count($matched),
                'unmatched_count' => count($unmatched),
                'matched' => $matched,
                'unmatched' => $unmatched
            ]);
            
        } catch (\Exception $e) {
            \Log::error("COMPARISON ERROR: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Parse KML coordinates
     */
    private function parseKmlCoordinates($kmlContent)
    {
        $polygons = [];
        
        try {
            \Log::info("parseKmlCoordinates: Starting XML parsing...");
            
            // Disable XML errors to handle them ourselves
            libxml_use_internal_errors(true);
            
            $xml = simplexml_load_string($kmlContent);
            
            if (!$xml) {
                \Log::error("parseKmlCoordinates: Failed to load XML");
                $errors = libxml_get_errors();
                foreach ($errors as $error) {
                    \Log::error("XML Error: " . $error->message);
                }
                libxml_clear_errors();
                return $polygons;
            }
            
            // Get the actual namespace from the XML
            $namespaces = $xml->getNamespaces(true);
            \Log::info("XML Namespaces: " . json_encode($namespaces));
            
            // Try with namespace first
            if (isset($namespaces[''])) {
                $xml->registerXPathNamespace('kml', $namespaces['']);
                $placemarks = $xml->xpath('//kml:Placemark');
            } else {
                // Try without namespace
                $placemarks = $xml->xpath('//Placemark');
            }
            
            if (!$placemarks) {
                \Log::warning("No placemarks found with default approach, trying alternative...");
                // Try direct children approach
                $placemarks = $xml->xpath('//*[local-name()="Placemark"]');
            }
            
            \Log::info("parseKmlCoordinates: Found " . count($placemarks) . " placemarks");
            
            foreach ($placemarks as $index => $placemark) {
                // Try multiple ways to get coordinates
                $coordinates = null;
                
                // Method 1: With registered namespace
                if (isset($namespaces[''])) {
                    $coordinates = $placemark->xpath('.//kml:coordinates');
                }
                
                // Method 2: Without namespace
                if (empty($coordinates)) {
                    $coordinates = $placemark->xpath('.//coordinates');
                }
                
                // Method 3: Using local-name
                if (empty($coordinates)) {
                    $coordinates = $placemark->xpath('.//*[local-name()="coordinates"]');
                }
                
                if (!empty($coordinates)) {
                    $coordsText = trim((string)$coordinates[0]);
                    
                    // Split by whitespace or newlines
                    $points = preg_split('/\s+/', $coordsText);
                    
                    $ranges = [];
                    foreach ($points as $pointIndex => $point) {
                        $point = trim($point);
                        if (empty($point)) continue;
                        
                        $coords = explode(',', $point);
                        if (count($coords) >= 2) {
                            $lng = (float)$coords[0];
                            $lat = (float)$coords[1];
                            
                            // Validate lat/lng ranges
                            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                                $ranges[] = [
                                    'lat' => $lat,
                                    'lng' => $lng
                                ];
                                
                                // Log only first polygon's first point
                                if ($index === 0 && $pointIndex === 0) {
                                    \Log::info("Sample first point: lat=$lat, lng=$lng");
                                }
                            }
                        }
                    }
                    
                    if (count($ranges) > 0) {
                        $polygons[] = [
                            'name' => 'Polygon ' . ($index + 1),
                            'ranges' => $ranges
                        ];
                        
                        // Log every 1000th polygon
                        if (($index + 1) % 1000 === 0) {
                            \Log::info("Parsed " . ($index + 1) . " polygons...");
                        }
                    }
                }
            }
            
            \Log::info("parseKmlCoordinates: Total polygons extracted: " . count($polygons));
            
        } catch (\Exception $e) {
            \Log::error("parseKmlCoordinates ERROR: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
        
        return $polygons;
    }

    /**
     * Check if two ranges match (with tolerance for floating point differences)
     */
    private function rangesMatch($range1, $range2, $tolerance = 0.001)
    {
        // Both should be arrays
        if (!is_array($range1) || !is_array($range2)) {
            return false;
        }
        
        $count1 = count($range1);
        $count2 = count($range2);
        
        // Quick reject: point count difference too large (more than 50%)
        $countDiff = abs($count1 - $count2);
        $avgCount = ($count1 + $count2) / 2;
        if ($countDiff / $avgCount > 0.5) {
            return false;
        }
        
        $matchCount = 0;
        $totalChecked = 0;
        
        // Use flexible matching: check if any point from range1 matches any nearby point in range2
        foreach ($range1 as $point1) {
            if (!isset($point1['lat']) || !isset($point1['lng'])) continue;
            
            $totalChecked++;
            $pointMatched = false;
            
            // Check against all points in range2 (for reordered points)
            foreach ($range2 as $point2) {
                if (!isset($point2['lat']) || !isset($point2['lng'])) continue;
                
                $latDiff = abs($point1['lat'] - $point2['lat']);
                $lngDiff = abs($point1['lng'] - $point2['lng']);
                
                // Exact match
                if ($latDiff < $tolerance && $lngDiff < $tolerance) {
                    $matchCount++;
                    $pointMatched = true;
                    break;
                }
            }
            
            // Early exit if too many mismatches
            if ($totalChecked > 5 && $matchCount === 0) {
                return false;
            }
        }
        
        if ($totalChecked === 0) return false;
        
        $matchPercentage = ($matchCount / $totalChecked);
        
        // Consider matched if at least 80% of points match
        return $matchPercentage >= 0.8;
    }

    /**
     * Store uploaded KML file
     */
    public function store(Request $request)
    {
        $request->validate([
            'kml_file' => 'required|file|mimes:kml,xml|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('kml_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Store in storage/app/public/kml
            $path = $file->storeAs('kml', $filename, 'public');
            
            return redirect()->back()->with('success', 'KML file uploaded successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload KML file: ' . $e->getMessage());
        }
    }

    /**
     * List all KML files
     */
    public function list()
    {
        $kmlFiles = [];
        
        if (Storage::disk('public')->exists('kml')) {
            $files = Storage::disk('public')->files('kml');
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'kml') {
                    $filename = basename($file);
                    $kmlFiles[] = [
                        'name' => $filename,
                        'path' => $file,
                        'url' => route('admin.kml.content', ['filename' => $filename]),
                        'size' => $this->formatBytes(Storage::disk('public')->size($file)),
                        'modified' => date('d M Y H:i', Storage::disk('public')->lastModified($file))
                    ];
                }
            }
        }
        
        $action = 'kml_list';
        return view('admin.kml.list', compact('kmlFiles', 'action'));
    }

    /**
     * Delete KML file
     */
    public function delete($filename)
    {
        try {
            $path = 'kml/' . $filename;
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return redirect()->back()->with('success', 'KML file deleted successfully!');
            }
            
            return redirect()->back()->with('error', 'KML file not found!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete KML file: ' . $e->getMessage());
        }
    }

    /**
     * Get KML file content
     */
    public function getKmlContent($filename)
    {
        try {
            $path = 'kml/' . $filename;
            
            if (Storage::disk('public')->exists($path)) {
                $content = Storage::disk('public')->get($path);
                return response($content, 200)->header('Content-Type', 'application/vnd.google-earth.kml+xml');
            }
            
            return response()->json(['error' => 'KML file not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

