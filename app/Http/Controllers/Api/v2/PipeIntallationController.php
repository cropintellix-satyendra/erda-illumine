<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PipeIntallationController extends Controller
{

   



    
    public function check_pipe_data(Request $request)
    {
       
        $plot = DB::table('pipe_installations')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no', $request->plot_no)->first();

        $poly_data = DB::table('polygons')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)->where('plot_no', $request->plot_no)->first();


        if ($plot) {

            if ($plot->l2_status == "Rejected") {
                $polygon_status = 0;
                return response()->json(['error' => true,  'data' => 'Rejected data'], 422);
            }
            $plot->ranges = json_decode($plot->ranges);

            if ($plot->ranges) {
                $polygon_status = 1;
            } else {
                $polygon_status = 0;
            }


            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                ->where('plot_no', $request->plot_no)
                ->where('status', 'Approved')
                ->where('trash', 0)
                ->get();
            // dd($pipe_data);

            if ($pipe_data->count() > 0) {
                $plot->pipes_location = $pipe_data;
                $status = 1;
                foreach ($pipe_data as $data) {
                    // dd($data);
                    if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                        // dd($status);
                        $status = 0;
                    } else {
                        // dd("in");
                        $status = 0;
                    }
                }
            } else {
                $status = 0;
            }

            if ($pipe_data->isNotEmpty() && isset($pipe_data[0]->financial_year) && isset($pipe_data[0]->season)) {
                foreach ($pipe_data as $data) {
                    if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                        return response()->json(['error' => true, 'message' => 'Data already submitted', 'status' => 0,], 423);
                    }
                }
            }

            return response()->json(['error' => true, 'message' => 'Data Available', 'data' => $plot, 'status' => $status, 'polygon_status' => $polygon_status], 200);
        } elseif ($poly_data) {

            if ($poly_data->final_status == "Rejected") {
                $polygon_status = 0;
                return response()->json(['error' => true,  'data' => 'Rejected data'], 422);
            }
            $poly_data->ranges = json_decode($poly_data->ranges);

            if ($poly_data->ranges) {
                $polygon_status = 1;
            } else {
                $polygon_status = 0;
            }


            $pipe_data = DB::table('pipe_installation_pipeimg')->where('farmer_uniqueId', $request->farmer_uniqueId)->where('farmer_plot_uniqueid', $request->farmer_plot_uniqueid)
                ->where('plot_no', $request->plot_no)
                ->where('status', 'Approved')
                ->where('trash', 0)
                ->get();
            // dd($pipe_data);

            if ($pipe_data->count() > 0) {
                // $plot->pipes_location = $pipe_data;
                $status = 1;
                foreach ($pipe_data as $data) {
                    // dd($data);
                    if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                        // dd($status);
                        $status = 0;
                    } else {
                        // dd("in");
                        $status = 0;
                    }
                }
            } else {
                $status = 0;
            }

            if ($pipe_data->isNotEmpty() && isset($pipe_data[0]->financial_year) && isset($pipe_data[0]->season)) {
                foreach ($pipe_data as $data) {
                    if ($data->financial_year == $request->financial_year && $data->season == $request->season) {
                        return response()->json(['error' => true, 'message' => 'Data already submitted', 'status' => 0,], 423);
                    }
                }
            }

            return response()->json(['error' => true, 'message' => 'Data Available', 'data' => $poly_data, 'status' => $status, 'polygon_status' => $polygon_status], 200);
        } else {
            return response()->json(['error' => true, 'data' => 'No Data'], 422);
        }
    }


}
