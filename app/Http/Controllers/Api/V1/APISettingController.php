<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Uniqueid;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use DB;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\UserDevices;
use Illuminate\Support\Facades\Hash;
use Storage;

class APISettingController extends Controller
{
    /**
     * Oboarding Screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function onboarding_screen(){
        $screen = DB::table('meta')->select('meta_key','meta_value')->get();
        if($screen){
             return response()->json(['success'=>true,'screen'=>$screen],200);
        }
        return response()->json(['error'=>'Something went wrong','screen'=>$screen],500);
    }

}
