<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
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
            // dd('siss');
           if(auth()->user()->roles->first()->name == 'SuperAdmin'){
               return redirect('admin/dashboard');
            
            }elseif(auth()->user()->hasRole('L-1-Validator')){
               return redirect('l1/dashboard');
            }elseif(auth()->user()->roles->first()->name == 'Viewer'){
               return redirect('admin/dashboard');
            }elseif(auth()->user()->roles->first()->name == 'L-2-Validator'){
               return redirect('l2/dashboard');
            }elseif(auth()->user()->roles->first()->name == 'SuperVerifier'){
                return redirect('admin/dashboard');
            }elseif(auth()->user()->roles->first()->name == 'Company'){
                dd('uhdus');
                return redirect('company/dashboard');
            }
        }
        $page_title = 'Home';
        $page_description = 'Kosher';
        $action = 'page_login';
        return view('page.login', compact('page_title', 'page_description','action'));
    }

    public function login(Request $request){
        $validatedData = $request->validate([
          'email' => 'required',
          'password' => 'required',
        ]);
        $User = DB::table('users')->where('email',$request->email)->where('status','1')->first();
        // dd($User);
        if(!$User){
            // dd('ddddd');
          return redirect()->back()->withErrors(['Access Denied']);
        }
        $credentials = $this->credentials(request());
        if(!Auth::attempt($credentials)){
               DB::table('login_activity')->insert(['user_id'=>NULL,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>NULL,
                                                   'type'=>'LOGIN ATTEMPT FAILED','created_at'=> Carbon::now(),'updated_at'=>Carbon::now(),'log'=>json_encode($credentials['email'])
                                                   ]);
            return redirect()->back()->withErrors(['login credential wrong']);
         }
         if(auth()->user()->roles->first()->name == 'SuperAdmin'){
               DB::table('users')->where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
               DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,
                                                   'type'=>'LOGIN','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()
                                                   ]);
            return redirect('admin/dashboard');
         }elseif(auth()->user()->hasRole('L-1-Validator')){
            DB::table('users')->where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
            DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,
                                                   'type'=>'LOGIN','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()]);
            return redirect('l1/dashboard');
         }elseif(auth()->user()->roles->first()->name == 'Viewer'){
            DB::table('users')->where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
            DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,
                                                   'type'=>'LOGIN','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()]);
            return redirect('admin/dashboard');
         }elseif(auth()->user()->roles->first()->name == 'SuperValidator'){
            DB::table('users')->where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
            DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,
                                                   'type'=>'LOGIN','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()]);
            return redirect('admin/dashboard');
            }elseif(auth()->user()->hasRole('L-2-Validator')){
               DB::table('users')->where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
               DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,
                                                   'type'=>'LOGIN','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()]);
               return redirect('l2/dashboard');
            } elseif(auth()->user()->hasRole('Company')){
            DB::table('users')->where('id',auth()->user()->id)->update(['last_login' => Carbon::now(),'ip'=>$_SERVER['REMOTE_ADDR']??NULL]);
            DB::table('login_activity')->insert(['user_id'=>auth()->user()->id,'ip'=>$_SERVER['REMOTE_ADDR']??NULL,'rolename'=>auth()->user()->roles->first()->name,
                                                'type'=>'LOGIN','created_at'=> Carbon::now(),'updated_at'=>Carbon::now()]);
            return redirect('company/dashboard');
         }else{
               Auth::logout();
               Auth::guard('web')->logout();
               return redirect()->back()->withErrors(['Access Denied']);
         }
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
