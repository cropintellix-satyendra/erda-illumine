<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyTarget;
use App\Models\UserTarget;
use Illuminate\Http\Request;
use App\Models\Aeration;
use App\Models\FarmerCropdata;
use App\Models\FinalFarmer;
use App\Models\PipeInstallationPipeImg;
use App\Models\Polygon;
use Validator;
use Exception;
use Illuminate\Support\Facades\Log;

class UserTargetController extends Controller
{
    public function daily_target(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'module_id' => 'required|integer'
        ]);
        $target = DailyTarget::select('id', 'module_name', 'daily_target')->where('id', $request->module_id)->first();
        // dd($target);
        if (!$target) {
            return response()->json(['error' => true, 'message' => 'Target not found'], 404);
        }
        return response()->json(['success' => true, 'message' => 'Successful', 'data' => $target], 200);
    }


//     public function user_target(Request $request)
//     {

//      $request->validate([

//                 'date' => 'required|date',

//             ]);
//      $UserId = auth()->user()->id;
//      $userTarget = UserTarget::with('daily_target:id,daily_target')->select('id','module_id','module_name','count')->where('user_id', $UserId)->where('date', $request->date) ->get();
//      dd($userTarget);
//      if($userTarget->isEmpty()){
//          $count = []
    
//          foreach($count as $counting){
//             $nullCount = [
//                 'id' => $counting->id,
//                 'module_id' => $counting->module_id,
//                 'module_name' => $counting->module_name,
//                 'count' => 0,
//                 "daily_target": {
//                     "id": 1,
//                     "daily_target": "50"
//                 }
    
//             ];
//          }



//          return response()->json(['success' => true, 'count' => '0','message'], 200);

//      if ($userTarget) {
//                 return response()->json([ 'success' => true,'count' => $userTarget ], 200);
//      } else {
//                 return response()->json(['error' => true, 'message' => 'No matching record found' ], 404);
//           }
//     }


// }



public function user_target(Request $request)
{
    $request->validate([
        'date' => 'required|date',
    ]);

    // Fetch daily targets
    $daily_targets = DailyTarget::select('id','module_name','daily_target')->get();

    $userId = auth()->user()->id;

    $customTarget = [];

    // Iterate over each unique module name
    foreach ($daily_targets as $daily_target) {
        // Fetch user targets for the current module name
        $userTargets = UserTarget::with(['daily_target:id,daily_target'])
            ->select('id', 'module_id', 'user_id', 'module_name', 'count', 'date')
            ->where('user_id', $userId)
            ->where('date', $request->date)
            ->where('module_name', $daily_target->module_name)
            ->get(); // Fetch all records for the module name

        // If user targets don't exist for this module, create a new entry with count 0
        if ($userTargets->isEmpty()) {
            $customTarget[] = [
                'module_id' => $daily_target->id,
                'module_name' => $daily_target->module_name,
                'user_id' => $userId,
                'count' => 0,
                'daily_target' => [
                    'id' => $daily_target->id,
                    'daily_target' => $daily_target->daily_target,
                ]
            ];
        } else {
            // If user targets exist, include them in the response
            foreach ($userTargets as $userTarget) {
                $customTarget[] = [
                    'id' => $userTarget->id, // Accessing id from a single model instance
                    'module_id' => $userTarget->module_id,
                    'module_name' => $userTarget->module_name,
                    'user_id' => $userId,
                    'count' => $userTarget->count,
                    'daily_target' => [
                        'id' => $userTarget->daily_target->id,
                        'daily_target' => $userTarget->daily_target->daily_target,
                    ]
                ];

            }
        }

    }

    return response()->json(['success' => true, 'data' => $customTarget], 200);
}
// public function user_target(Request $request)
// {
//     $request->validate([
//         'date' => 'required|date',
//     ]);

//     $userId = auth()->user()->id;
//     $userTarget = UserTarget::with(['daily_target:id,daily_target'])->select('id','module_id','user_id','module_name','count','date')->where('user_id', $userId) ->where('date', $request->date) ->get();

//     if ($userTarget->isEmpty()) {
//         $customResponse = [];

//         // Assuming you have a predefined array of all modules
//         $allModules = DailyTarget::all(); // Fetch all modules or use your method to get them

//         // Iterate through all modules and create custom response with count 0
//         foreach ($allModules as $module) {
//             $customResponse[] = [
//                 'module_id' => $module->id,
//                 'module_name' => $module->module_name,
//                 'user_id' => auth()->user()->id,
//                 'count' => 0,
//                 'daily_target' => [
//                     'id' => $module->id,
//                     'daily_target' => $module->daily_target,
//                 ]
//             ];
//         }

//         return response()->json(['success' => true, 'count' => 0, 'data' => $customResponse], 200);
//     }

//     return response()->json(['success' => true, 'count' => $userTarget->count(), 'data' => $userTarget], 200);
// }




public function modulewise_count(Request $request){
    try {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ]);

    $userId = auth()->user()->id;

    $startDate = $request->start_date;
    $endDate = $request->end_date;
    if ($request->module_name == 'onboarding'){
        $onboard = UserTarget::where('user_id',$userId)->where('module_name','onboarding')->whereBetween('date', [$startDate, $endDate])->sum('count');
        // dd($onboard);
        $onboard = (int) $onboard;
        return response()->json(['success' => true, 'message' => 'Onboarding Data Available','count' => $onboard], 200);
    }

    if ($request->module_name == 'polygon'){
        $polygon = UserTarget::where('user_id',$userId)->where('module_name','polygon')->whereBetween('date', [$startDate, $endDate])->sum('count');
        $polygon = (int) $polygon;
        return response()->json(['success' => true, 'message' => 'Polygon Data Available','count' => $polygon], 200);
    }

    if ($request->module_name == 'cropdata'){
        $cropdata = UserTarget::where('user_id',$userId)->where('module_name','cropdata')->whereBetween('date', [$startDate, $endDate])->sum('count');
        $cropdata = (int) $cropdata;
        return response()->json(['success' => true, 'message' => ' Cropdata Data Available','count' => $cropdata], 200);
    }

    if ($request->module_name == 'pipeinstallation'){
        $pipeinstallation = UserTarget::where('user_id',$userId)->where('module_name','pipeinstallation')->whereBetween('date', [$startDate, $endDate])->sum('count');
        $pipeinstallation = (int) $pipeinstallation;
        return response()->json(['success' => true, 'message' => 'Pipeinstallation Data Available','count' => $pipeinstallation], 200);
    }

    if ($request->module_name == 'aeration'){
        $aeration = UserTarget::where('user_id',$userId)->where('module_name','aeration')->whereBetween('date', [$startDate, $endDate])->sum('count');
        $aeration = (int) $aeration;
        return response()->json(['success' => true, 'message' => ' Aeration Data Available','count' => $aeration], 200);
    }
    if ($request->module_name == 'farmer_benefit'){
        $benefit = UserTarget::where('user_id',$userId)->where('module_name','farmer_benefit')->whereBetween('date', [$startDate, $endDate])->sum('count');
        $benefit = (int) $benefit;
        return response()->json(['success' => true, 'message' => ' Farmer Benefit Data Available','count' => $benefit], 200);
    }
    if ($request->start_date == $startDate && $request->end_date == $endDate){
                 $aeration = UserTarget::where('user_id',$userId)->where('module_name','aeration')->whereBetween('date', [$startDate, $endDate])->sum('count');
                 $aeration = (int) $aeration;
                 $onboard = UserTarget::where('user_id',$userId)->where('module_name','onboarding')->whereBetween('date', [$startDate, $endDate])->sum('count');
                 $onboard = (int) $onboard;
                 $polygon = UserTarget::where('user_id',$userId)->where('module_name','polygon')->whereBetween('date', [$startDate, $endDate])->sum('count');
                 $polygon = (int) $polygon;
                 $cropdata = UserTarget::where('user_id',$userId)->where('module_name','cropdata')->whereBetween('date', [$startDate, $endDate])->sum('count');
                 $cropdata = (int) $cropdata;
                 $pipeinstallation = UserTarget::where('user_id',$userId)->where('module_name','pipeinstallation')->whereBetween('date', [$startDate, $endDate])->sum('count');
                 $pipeinstallation = (int) $pipeinstallation;
                 $benifit = UserTarget::where('user_id',$userId)->where('module_name','farmer_benefit')->whereBetween('date', [$startDate, $endDate])->sum('count');
                 $benifit = (int) $benifit;
                // Response
                $response = [
                     'success' => true ,
                     'message' => 'Data Available',
                     'onboard_count' =>  $onboard,
                     'polygon_count' => $polygon ,
                     'cropdata_count' => $cropdata ,
                     'pipeinstallation_count' => $pipeinstallation,
                     'aeration' => $aeration,
                     'farmer_benefit' => $benifit
                ];
                 return response()->json($response, 200);
             }
    // else{
    //     return response()->json(['error' => true, 'message' => 'Something Went Wrong' ], 422);
    // }
}  catch (Exception $e) {
    // dd($e);
    Log::error('Error storing data: ' . $e->getMessage());
    return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
}

}
}