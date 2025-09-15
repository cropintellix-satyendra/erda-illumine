<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Season;
use App\Models\AreationDate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use PDF;
use Storage;
use Carbon\Carbon;
use DB;
use App\Models\PipeInstallation;
use App\Models\FinalFarmer;
use App\Models\Aeration;
use App\Models\AerationImage;
use App\Models\PlotStatusRecord;
use App\Models\PipeInstallationPipeImg;
use App\Models\Polygon;

class AerationController extends Controller
{

  /**
  * Get polygon for plot data
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */

//Now we checking farmer have only one pipe in any plot_id;

public function get_plot_pipe(Request $request)
{

    \Log::info('Request Parameters in Get Plot Pipe Number:', $request->all());

    $farmerUniqueId = $request->farmer_uniqueId;
    // Check in pipe_installations table
    // $pipeInstallation = DB::table('pipe_installations')
    //     ->select('id', 'farmer_uniqueId', 'plot_no', 'pipes_location')
    //     ->where('farmer_uniqueId', $farmerUniqueId)
    //     ->first();

    // where('financial_year',$request->financial_year)->where('season',$season)
    // If no data in pipe_installations, check in polygons table
    // if (!$pipeInstallation) {

    $season = Season::select('id','name')->where('name',$request->season)->first();
        $polygonData = DB::table('polygons')
            ->select('id', 'farmer_uniqueId', 'plot_no', 'ranges AS pipes_location')
            ->where('farmer_uniqueId', $farmerUniqueId)
            ->first();

        if (!$polygonData) {
            return response()->json(['error' => true, 'message' => 'No Data', 'status' => 0], 422);
        } else {
            $pipeInstallation = $polygonData;
        }
    // }


    // Fetch pipe data related to the plot
    $pipeData = DB::table('pipe_installation_pipeimg')
        ->where('farmer_uniqueId', $pipeInstallation->farmer_uniqueId)
        ->where('trash', 0)
        ->where('financial_year',$request->financial_year)->where('season',$season->id)
        ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'pipe_no', 'lat', 'lng', 'images', 'distance','financial_year','season')
        ->get();

        \Log::info('Fetched Plot Pipe Number: ' . json_encode($pipeData));
    // dd($pipeData , $request->season , $season , $request->financial_year);
    

    $isPipeInstalled = count($pipeData) > 0 ? 1 : 0;

    return response()->json([
        'success' => true,
        'farmer_uniqueid' => $farmerUniqueId,
        'pipe_installation_id' => $pipeInstallation->id,
        'plot_no' => $pipeInstallation->plot_no,
        'PipeList' => $pipeData,
        'status' => 1,
        'is_pipe_installed' => $isPipeInstalled
    ], 200);
}


//Previously we checked the only farmer uique id has installed anyone plot's pipe installed then he can fill aeration 
//now we checked the all plots

// public function get_plot_pipe_plotwise(Request $request)
// {
//     $farmerPlotUniqueId = $request->farmer_plot_uniqueid;
//     $farmerUniqueId = $request->farmer_uniqueId ;
//     $season = Season::select('id','name')->where('name',$request->season)->first();

//     // Check in pipe_installations table
//     // $pipeInstallation = DB::table('pipe_installations')
//     //     ->select('id', 'farmer_uniqueId', 'plot_no', 'pipes_location')
//     //     ->where('farmer_uniqueId', $farmerUniqueId)
//     //     ->first();

//     // If no data in pipe_installations, check in polygons table
//     // if (!$pipeInstallation) {
//         $polygonData = DB::table('polygons')
//             ->select('id', 'farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'ranges AS pipes_location')
//             ->where('farmer_uniqueId',$farmerUniqueId)
//             ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
//             ->first();

//         if (!$polygonData) {
//             return response()->json(['error' => true, 'message' => 'No Data', 'status' => 0], 422);
//         } else {
//             $pipeInstallation = $polygonData;
//         }
//     // }
//     $pipeData_uniqueId = DB::table('pipe_installation_pipeimg')
//     ->where('farmer_uniqueId', $pipeInstallation->farmer_uniqueId)
//     ->where('trash', 0)
//     ->where('financial_year',$request->financial_year)->where('season',$season->id)
//     ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'pipe_no', 'lat', 'lng', 'images', 'distance','financial_year','season')
//     ->get();

//     //Used for to check if the Pipe installed is the Related to that Farmer uniqueId 
//     $uniqueId_isPipeInstalled = count($pipeData_uniqueId) > 0 ? 1 : 0;

//     // Fetch pipe data related to the plot
//     $pipeData = DB::table('pipe_installation_pipeimg')
//         ->where('farmer_plot_uniqueid', $pipeInstallation->farmer_plot_uniqueid)
//         ->where('trash', 0)
//         ->where('financial_year',$request->financial_year)->where('season',$season->id)
//         ->orderBy('pipe_no', 'asc')
//         ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'pipe_no', 'lat', 'lng', 'images', 'distance','financial_year','season')
//         ->get();

//     //Used for to check if the Pipe installed is the Related to that Farmer plot uniqueId 
//     $isPipeInstalled = count($pipeData) > 0 ? 1 : 0;

//     $pipeData = $pipeData->map(function ($pipe) use ($isPipeInstalled) {
//         $pipe->is_pipe_installed = $isPipeInstalled;
//         return $pipe;
//     });

//     return response()->json([
//         'success' => true,
//         'farmer_uniqueid' => $pipeInstallation->farmer_uniqueId,
//         'farmer_plot_uniqueid' => $farmerPlotUniqueId,
//         'pipe_installation_id' => $pipeInstallation->id,
//         'plot_no' => $pipeInstallation->plot_no,
//         'PipeList' => $pipeData,
//         'status' => 1,
//         'is_pipe_installed' => $uniqueId_isPipeInstalled
//     ], 200);
// }

public function get_plot_pipe_plotwise(Request $request)
{
    $farmerPlotUniqueId = $request->farmer_plot_uniqueid;
    $farmerUniqueId = $request->farmer_uniqueId;
    
    // Fetch Season ID
    $season = Season::select('id', 'name')->where('name', $request->season)->first();
    
    if (!$season) {
        return response()->json(['error' => true, 'message' => 'Invalid season', 'status' => 0], 422);
    }

    // Check if polygon data exists for the given plot
    $polygonData = DB::table('polygons')
        ->select('id', 'farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'ranges AS pipes_location')
        ->where('farmer_uniqueId', $farmerUniqueId)
        ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
        ->first();

    if (!$polygonData) {
        return response()->json(['error' => true, 'message' => 'No Data', 'status' => 0], 422);
    }

    $pipeInstallation = $polygonData;  // Assign polygon data to pipeInstallation for consistency

    // Fetch pipe data related to the farmer (Unique ID Level)
    $pipeData_uniqueId = DB::table('pipe_installation_pipeimg')
        ->where('farmer_uniqueId', $pipeInstallation->farmer_uniqueId)
        ->where('trash', 0)
        ->where('financial_year', $request->financial_year)
        ->where('season', $season->id)
        ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'pipe_no', 'lat', 'lng', 'images', 'distance', 'financial_year', 'season')
        ->get();

    // Check if pipes are installed for the given unique ID
    $uniqueId_isPipeInstalled = $pipeData_uniqueId->isNotEmpty() ? 1 : 0;

    // Fetch pipe data related to the plot (Plot Unique ID Level)
    $pipeData = DB::table('pipe_installation_pipeimg')
        ->where('farmer_plot_uniqueid', $pipeInstallation->farmer_plot_uniqueid)
        ->where('trash', 0)
        ->where('financial_year',$request->financial_year)->where('season',$season->id)
        ->orderBy('pipe_no', 'asc')
        ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'pipe_no', 'lat', 'lng', 'images', 'distance', 'financial_year', 'season')
        ->get();
    // dd($pipeData);
    // Check if pipes are installed for the given plot
    $isPipeInstalled = $pipeData->isNotEmpty() ? 1 : 0;

    // Add is_pipe_installed flag inside each pipe object
    $pipeData = $pipeData->map(function ($pipe) use ($isPipeInstalled) {
        $pipe->is_pipe_installed = $isPipeInstalled;
        return $pipe;
    });

    return response()->json([
        'success' => true,
        'farmer_uniqueid' => $pipeInstallation->farmer_uniqueId,
        'farmer_plot_uniqueid' => $farmerPlotUniqueId,
        'pipe_installation_id' => $pipeInstallation->id ?? null, // Ensure id exists
        'plot_no' => $pipeInstallation->plot_no,
        'PipeList' => $pipeData,
        'status' => 1,
        'is_pipe_installed' => $uniqueId_isPipeInstalled
    ], 200);
}



public function get_polygon(Request $request)
{
    $farmerPlotUniqueId = $request->farmer_plot_uniqueid;
    $pipeNo = $request->pipe_no;

    // Fetch pipe polygon data
    // $pipePolygon = PipeInstallation::select('ranges', 'pipes_location', 'farmer_uniqueId')
    //     ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
    //     ->first();

    // If no data in pipe installations, fetch from polygons table
    // if (!$pipePolygon) {
        $pipePolygon = Polygon::select('ranges', 'farmer_uniqueId')
            ->where('farmer_plot_uniqueid', $farmerPlotUniqueId)
            ->first();

        if (!$pipePolygon) {
            return response()->json(['error' => true, 'message' => 'No Data'], 422);
        }
    // }

    $pipePolygon->ranges = json_decode($pipePolygon->ranges);

    // Fetch pipe data
    $pipeData = PipeInstallationPipeImg::where('farmer_plot_uniqueid', $farmerPlotUniqueId)
        ->where('pipe_no', $pipeNo)
        ->select('farmer_uniqueId', 'farmer_plot_uniqueid', 'plot_no', 'pipe_no', 'lat', 'lng', 'images', 'distance')
        ->first();

    // Extract numeric part from farmer_plot_uniqueid
    $numericPart = '';
    if (preg_match('/(\d+)P\d+$/', $farmerPlotUniqueId, $matches)) {
        $numericPart = $matches[1];
    }

    // Check if pipe is installed
    $isPipeInstalled = PipeInstallationPipeImg::where('farmer_uniqueId', $numericPart)
        ->where('pipe_no', $pipeNo)
        ->exists();

    return response()->json([
        'success' => true,
        'polygon' => $pipePolygon->ranges,
        'PipeLocation' => $pipeData,
        'is_pipe_installed' => $isPipeInstalled ? 1 : 0,
    ], 200);
}


}
