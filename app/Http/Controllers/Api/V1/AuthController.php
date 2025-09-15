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
use Laravel\Sanctum\PersonalAccessToken;
use Storage;

class AuthController extends Controller
{
    /**
     * Check app version.
     *
     * @return \Illuminate\Http\Response
     */
    public function check_version(){
        $versions = DB::table('app_versions')->select('version','path')->where('version',request('version'))->where('status',1)->first();
        if($versions){
             return response()->json(['success'=>true, 'message'=>'Version Matched','version'=>$versions->version],200);
        }
        $versionsURL = DB::table('app_versions')->select('version','path')->orderBy('id', 'DESC')->where('status',1)->first();

        //Now bypass the app.
        // return response()->json(['success'=>true, 'message'=>'Version Matched','version'=>$versions->version??"1.00"],200);

        return response()->json(['error'=>'Something went wrong','message'=>'Please download new app','url'=>$versionsURL->path],500);
    }

    // public function generate_uniqueId()
    // {
    //   try{
    //     $versions = DB::table('app_versions')->where('status',1)->get();
    //     foreach($versions as $version){
    //       if($version->version == request('version')){
    //           $last_unique_id = Uniqueid::select('unique_id')->orderby('id','desc')->first();
    //           if(empty($last_unique_id)){
    //               $new_unique_id = '1000000';
    //               Uniqueid::create(['unique_id'=>$new_unique_id,'status'=>'INITIATE']);
    //           }else{
    //               $new_unique_id  = $last_unique_id->unique_id + 1;
    //               Uniqueid::create(['unique_id'=>$new_unique_id,'status'=>'INITIATE']);
    //           }
    //           return response()->json(['success'=>true, 'UniqueId'=>$new_unique_id],200);
    //       }
    //     }
    //     $versionsURL = DB::table('app_versions')->select('version','path')->orderBy('id', 'DESC')->where('status',1)->first();
    //     return response()->json(['error'=>'Something went wrong','message'=>'Please download new app','url'=>asset('public/storage/'.$versionsURL->path)],500);
    //   }catch(Exception $e){
    //     return response()->json(['error'=>'Something went wrong'],500);
    //   }
    // }

    //|| $version->version !== request('version')
    public function generate_uniqueId()
    {
      try{
        $versions = DB::table('app_versions')->where('status',1)->get();
        foreach($versions as $version){
          if($version->version == request('version') )
          {
              $last_unique_id = Uniqueid::select('unique_id')->orderby('id','desc')->first();
              if(empty($last_unique_id)){
                  $new_unique_id = '1000000';
                  Uniqueid::create(['unique_id'=>$new_unique_id,'status'=>'INITIATE']);
              }else{
                  $new_unique_id  = $last_unique_id->unique_id + 1;
                  Uniqueid::create(['unique_id'=>$new_unique_id,'status'=>'INITIATE']);
              }
              return response()->json(['success'=>true, 'UniqueId'=>$new_unique_id],200);
          }
        }
        $versionsURL = DB::table('app_versions')->select('version','path')->orderBy('id', 'DESC')->where('status',1)->first();

        return response()->json(['error'=>'Something went wrong','message'=>'Please download new app','url'=>asset('public/storage/'.$versionsURL->path)],500);
      }catch(Exception $e){
        return response()->json(['error'=>'Something went wrong'],500);
      }
    }

    /**
     * Check wheather email, mobile exists or not.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkUserValidation(Request $request)
    {
      try{
          if($request->has('email')){
                $email = User::where('email',$request->email)->first();
                if($email){
                   return response()->json(['error'=>true,'message'=> 'Email already register'],422);
                }else{
                   return response()->json(['success'=>true,'email'=> 'No data'],200);
                }
          }
           if($request->has('mobile')){
                $mobile = User::where('mobile',$request->mobile)->first();
                if($mobile){
                   return response()->json(['error'=>true,'message'=> 'Mobile already register'],422);
                }else{
                   return response()->json(['success'=>true,'mobile'=> 'No data'],200);
                }
          }
      }catch(Exception $e){
        return response()->json(['error'=>'Something went wrong'],500);
      }
    }

   /**
   * User register from api
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
    public function register(Request $request)
    {
      //validate company code given by user
        $company = Company::where('company_code',$request->company_code)->first();
        if(!$company){
           return response()->json(['error'=>true,'message'=>'Wrong Company Code'],403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'mobile' => 'required|unique:users',
            'password' => 'required|string|min:6|max:50',
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(collect(['error'=>true,'message'=>'Please fill all details!'])->merge(collect($validator->messages())->map(function($items){ return $items[0]; })), 422);
        }
        //Request is valid, create new user
        $user = User::create([
            'name' =>$request->name,
            'mobile'=>$request->mobile??'',
            'status'=> '0',
            'role' => 'User',
            'password' => bcrypt($request->password),
            'company_code' => $request->company_code,
            'company_id'    =>$company->id,
            'state_id'   => $company->state_id,//$request->state_id??,
        ]);
        // assign new role to the user
        $user->syncRoles(3);
        //collect device type and token
        $UserDevices = UserDevices::create([
            'user_id' =>$user->id,
            'versioncode'  =>  $request->versionCode,
            'versionname'  =>  $request->versionName,
            'released'     =>  $request->release,
            'devicename'   =>  $request->deviceName,
            'device_manufacturer'  =>  $request->deviceManufacturer,
            'device_id' => $request->device_id,
            'fcm_token'  =>  $request->fcm_token??NULL, 
        ]);
        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required'
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(collect(['error'=>true,'message'=>'Please fill all details!'])->merge(collect($validator->messages())->map(function($items){ return $items[0]; })), 422);
        }
        $User = User::with('state:id,name')->where('mobile',$request->mobile)->where('status','1')->first();
        if(!$User){
            DB::table('login_activity')->insert(['user_id'=>NULL,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>'AppUser',
                                                 'type'=>'LOGIN ATTEMPT FAILED','created_at'=> Carbon::now(),'updated_at'=>Carbon::now(),'log'=>json_encode($request->mobile)
                                                ]);
          return response()->json(['error' => 'Access Denied',],422);
        }
        //Request is validated
        //Create token
        // if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ old way with email
         if(Hash::check($request->password, $User->password)){
            // $user = Auth::user();
            \Auth::login($User);
            // $User->token =  $User->createToken('api')->plainTextToken;
            User::where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
            DB::table('login_activity')->insert(['user_id'=>auth()->user()->id??NULL,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,
            'rolename'=>auth()->user()->roles->first()->name??NULL,'type'=>'LOGIN',
            'created_at'=> Carbon::now()??NULL,'updated_at'=>Carbon::now()??NULL]);
            //check role
            if(auth()->user()->roles->first()->name != 'AppUser'){
               return response()->json(['error' => 'Access Denied',],422);
            }
            if($User->device){
                if(!$User->device->device_id){
                    //collect device type and token
                     $UserDevices = UserDevices::where('user_id',auth()->user()->id)->update([
                                                    'user_id' =>auth()->user()->id,
                                                    'versioncode'  =>  $request->versionCode,
                                                    'versionname'  =>  $request->versionName,
                                                    'released'  =>  $request->release,
                                                    'devicename'  =>  $request->deviceName,
                                                    'device_manufacturer'  =>  $request->deviceManufacturer,
                                                    'device_id' => $request->device_id,
                                                    'ip'=>$_SERVER['REMOTE_ADDR']??NULL,
                                                    'fcm_token'  =>  $request->fcm_token??NULL, 
                                                ]);
                }else{
                    //collect device type and token
                    $UserDevices = UserDevices::where('user_id',auth()->user()->id)->update([
                                    'user_id' =>auth()->user()->id,
                                    'versioncode'  =>  $request->versionCode,
                                    'versionname'  =>  $request->versionName,
                                    'released'  =>  $request->release,
                                    'devicename'  =>  $request->deviceName,
                                    'device_manufacturer'  =>  $request->deviceManufacturer,
                                    'ip'=>$_SERVER['REMOTE_ADDR']??NULL,
                                    'fcm_token'  =>  $request->fcm_token??NULL, 
                                ]);
                }
            }else{
                //collect device type and token
                $UserDevices = UserDevices::create([
                    'user_id' =>auth()->user()->id,
                    'versioncode'  =>  $request->versionCode,
                    'versionname'  =>  $request->versionName,
                    'released'     =>  $request->release,
                    'devicename'   =>  $request->deviceName,
                    'device_manufacturer'  =>  $request->deviceManufacturer,
                    'device_id' => $request->device_id,
                    'ip'=>$_SERVER['REMOTE_ADDR']??NULL,
                    'fcm_token'  =>  $request->fcm_token??NULL, 
                ]);
            }
            // if($request->mobile != '9820098200'){
            //     $User=User::with('state:id,name')->where('mobile',$request->mobile)->where('status','1')->first();
            //     if($User->device->device_id != $request->device_id){
            //           // return response()->json(['error' => 'You are not authorised to login on this device',],422);
            //         return response()->json(['success' => 'Login successfully',],200);
            //     }
            // }
            $User->token =  $User->createToken('api')->plainTextToken;
        }
        else{
            DB::table('login_activity')->insert(['user_id'=>NULL,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>'AppUser',
            'type'=>'LOGIN ATTEMPT FAILED','created_at'=> Carbon::now(),'updated_at'=>Carbon::now(),'log'=>json_encode($request->mobile)
           ]);
            return response()->json([
                'error' => 'Wrong Credentials',
            ],422);
        }
        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'user'  => $User,
        ]);
    }

    // public function logout(Request $request)
    // {
    //     //valid credential
    //     $validator = Validator::make($request->only('token'), [
    //         'token' => 'required'
    //     ]);

    //     //Send failed response if request is not valid
    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->messages()], 200);
    //     }

    //     //Request is validated, do logout
    //     try {
    //         JWTAuth::invalidate($request->token);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User has been logged out'
    //         ]);
    //     } catch (JWTException $exception) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Sorry, user cannot be logged out'
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }


    // public function logout(Request $request)
    // {
    //     try {
    //         \Log::info($request->all());
    //         $user = Auth::user();
        
    //         // Check if the user exists and the status is 0
    //         if ($user && $user->status == 1) {
    //             \Log::info('Authentication Loop');
    //             // Revoke all tokens for the user (assuming Sanctum)
    //             $user->tokens()->delete();
        
    //             // Clear the session
    //             $request->session()->invalidate();
    //             $request->session()->regenerateToken();
        
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Successfully logged out'
    //             ], 200);
    //         }
        
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Authentication Failed'
    //         ], 422);

    //     }    catch (\Exception $e) {
    //         dd($e);
    //         return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
    //     }
    //     // Get the user who is currently authenticated
    // }
    


    public function logout()
    {
        try {
            // $user = auth()->user();
            // if ($user) {
            //     $admin = User::where('id', $user->id)->first();
            //     // Revoke the user's token
            //     $admin->tokens()->delete();
            //     Auth::logout();
    
            //     return response()->json(['success' => true, 'message' => 'Logout Successfully!'], 200);
            // }
            // return response()->json(['error' => true, 'message' => 'Failed Logout'], 422);

                      $accessToken = request()->bearerToken();
                      $token = PersonalAccessToken::findToken($accessToken);
                      if(!$token){
                          return response()->json([
                              'error'=>true,
                              'message'=>'Unknown Error!'
                          ],422);
                      }
                      $user=$token->tokenable_type::find($token->tokenable_id);
                      $revoke=$user->tokens()->delete();
                      if(!$revoke){
                          return response()->json([
                              'error'=>true,
                              'message'=>'Unknown Error!'
                          ],422);
                      }
                      return response()->json([
                          'success'=>true,
                          'message'=>'Logout successfully'
                      ] ,200);
                }    catch (\Exception $e) {
                    // dd($e);
                    return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
                }

            }


            public function logfhhgout(Request $request)
            {
                try {
                    $accessToken = $request->bearerToken();
                    $token = PersonalAccessToken::findToken($accessToken);
            
                    if (!$token) {
                        return response()->json([
                            'error' => true,
                            'message' => 'Unknown Error!'
                        ], 422);
                    }
            
                    $user = $token->tokenable_type::find($token->tokenable_id);
            
                    if ($user && $user->status == 1) {
                        \Log::info('Authentication Loop');

                        $revoke=$user->tokens()->delete();
                        if(!$revoke){
                            return response()->json([
                                'error'=>true,
                                'message'=>'Unknown Error!'
                            ],422);
                        }
                        // Revoke all tokens for the user (assuming Sanctum)
                        $user->tokens()->delete();
            
                        // Clear the session
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
            
                        return response()->json([
                            'success' => true,
                            'message' => 'Successfully logged out'
                        ], 200);
                    }
            
                    return response()->json([
                        'error' => true,
                        'message' => 'User is inactive and cannot log out!'
                    ], 403);
                } catch (\Exception $e) {
                    \Log::error('Logout Error: ' . $e->getMessage());
                    return response()->json(['error' => true, 'message' => 'An error occurred'], 500);
                }
            }
}
