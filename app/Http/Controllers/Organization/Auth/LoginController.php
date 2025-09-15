<?php

namespace App\Http\Controllers\Organization\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class LoginController extends Controller
{
     public function destroy_session(){
        //  Session::flush();
        Auth::logout();
        Auth::guard('web')->logout();
        return response()->json(['success'=>true]);

     }

    public function index(){
        if(auth()->user()){
            dd('siss');
            return redirect('company/dashboard');
        }
        // dd('siss');
        $page_title = 'Home';
        $page_description = 'KOSHER';
        $action = 'page_login';
        return view('organization.login', compact('page_title', 'page_description','action'));
    }

    public function login(Request $request){
        $validatedData = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = Company::where('email', $request->email)->where('status', '1')->first();

        if (!$user) {
            return redirect()->back()->withErrors(['Access Denied']);
        }

        if (Hash::check($request->password, $user->password)) {
            // dd('if');
            Auth::login($user);
            // dd($user, auth()->user());
            return redirect('company/dashboard');
        } else {
            return redirect()->back()->withErrors(['Login credentials are incorrect']);
        }

        // dd($credentials );

        // if(!Auth::attempt($credentials)){
        //     dd('if');
        //        DB::table('login_activity')->insert(['user_id'=>NULL,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>NULL,'companies'=> 1,
        //                                            'type'=>'LOGIN ATTEMPT FAILED','created_at'=> Carbon::now(),'updated_at'=>Carbon::now(),'log'=>json_encode($credentials['email'])
        //                                            ]);
        //     return redirect()->back()->withErrors(['login credential wrong']);
        // }
        // if(auth()->user()){
        //     DB::table('companies')->where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
        //     DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,'companies'=> 1,
        //                                         'type'=>'LOGIN','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()
        //                                         ]);
        // return redirect('organization/dashboard');
        // }else{
        //     dd('stop');
        //        Auth::logout();
        //        Auth::guard('web')->logout();
        //        return redirect()->back()->withErrors(['Access Denied']);
        // }
    }

    public function logout(){
         DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,
                                                 'type'=>'LOGOUT','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()]);
        Auth::logout();
        Auth::guard('web')->logout();
        return redirect('/login');
    }

    /**
       * Get the needed authorization credentials from the request.
       *
       * @param  \Illuminate\Http\Request  $request
       * @return array
       */
      protected function credentials(Request $request)
      {
        return ['email' => $request->get('email'), 'password'=>$request->get('password')];
      }
}
