<?php

namespace App\Http\Controllers\Admin\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use App\Models\State;
use App\Models\District;
use App\Models\UserDevices;
use DB;

class UserController extends Controller
{
    /**
    * Show the user list.
    *
    * @param  int  $id
    * @return \Illuminate\View\View
    */
    public function index(){
        if(auth()->user()->cannot('user')) abort(403, 'User does not have the right roles.');
        $users = User::with('company_name')->whereHas('roles', function($q){
                $q->where('name', 'AppUser');//fetch user from users table hasrole User
            }
            )->select('id','name','mobile','company_code','status','last_login','state_id')->with('device')->orderBy('created_at','desc')->get();

        $page_title = 'Users';
        $page_description = 'Some description for the page';
        $action = 'table_landownerships';
        return view('admin.user.index', compact('users','page_title', 'page_description','action'));
    }

   /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      if(auth()->user()->cannot('add user')) abort(403, 'User does not have the right roles.');
      $States = State::all();
      $Districts = District::all();
      $roles = Role::where('name','AppUser')->get();
      $action = 'form_pickers';
      $page_title = 'Create users';
      return view('admin.user.create',compact('action','page_title','roles','States','Districts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(auth()->user()->cannot('add user')) abort(403, 'User does not have the right roles.');
        $request->mobile = (int) $request->mobile;
        $request->validate([
            'name' => 'required',
            'mobile' => 'required',
        ]);
        $mobile = User::where('mobile',$request->mobile)->first();
        if($mobile){
          return redirect()->back()->with('error', 'Mobile already register');
        }
        $company = Company::where('company_code',$request->company_code)->first();
        if(!$company){
           return redirect()->back()->with('error', 'Wrong Company Code');
        }
        $user = new User;
        $user->name = $request->name;
        $user->mobile = $request->mobile;
        $user->company_code = $request->company_code;
        $user->status = '0';
        $user->role = 'User';
        $user->password = bcrypt($request->password);
        $user->save();
        // assign new role to the user
        $user->syncRoles($request->roles);
        if(!$user){
          return redirect()->back()->with('error', 'Something went wrongs');
        }
        return redirect()->route('admin.users.index')->with('success', 'Saved Successfully');
    }


   /**
     * Edit the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
    }

    /**
     * Edit the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if(auth()->user()->cannot('edit user')) abort(403, 'User does not have the right roles.');
        $roles = Role::all();
        $states = DB::table('states')->get();
        $user = User::find($id);
        $action = 'form_pickers';
        return view('admin.user.edit',compact('user','roles','action','states'));
    }

   /**
     * Edit the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function update(Request $request,$id)
    {
      if(auth()->user()->cannot('edit user')) abort(403, 'User does not have the right roles.');
        $validatedData = $request->validate([
                          'name' => 'required',
                          'mobile' => 'required',
                        ]);
            $user = User::find($id);
            $user->name = $request->name;
            $user->mobile = $request->mobile;
            // $user->status = $request->status;
            
            $user->state_id = $request->state_id;
            $user->save();
            $UserDevices = UserDevices::where('user_id',$id)->update([
                                    'device_id' => null,
                                ]);
        if(!$user){
          return redirect()->back()->withErrors(['Something went wrongs']);
        }
        return redirect()->back()->with('success', 'Saved Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
      if(auth()->user()->cannot('delete user')) abort(403, 'User does not have the right roles.');
      try {
            $farmer = Farmer::where('surveyor_id',$request->id)->first();
            $farmer_cropdata = FarmerCropdata::where('surveyor_id',$request->id)->first();
            $User = User::where('status',0)->find($request->id);
            if(!$farmer && !$farmer_cropdata && $User){
                $User = User::destroy($request->id);
                if(!$User){
                  return response()->json(['error'=>true,'something went wrong'],500);
                }
                return response()->json(['success'=>true,'Delete Successfully'],200);
            }
            return response()->json(['error'=>true,'message'=>'Please disable user/user has some data'],500);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Change status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function disable($user_id)
    {      
      $User = User::where('id',$user_id)->first();
      $User->status = 0;
      $User->save();
      if(!$User){
        return redirect()->back()->with('error', 'Something Went wrong');
      }
      return redirect()->back()->with('success', 'Disable Sucessfully');

    }

    /**
     * Change status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enable($user_id)
    {      
      $User = User::where('id',$user_id)->first();
      $User->status = 1;
      $User->save();
      if(!$User){
        return redirect()->back()->with('error', 'Something Went wrong');
      }
      return redirect()->back()->with('success', 'Enable Sucessfully');

    }

    /**
     * remove device id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function remove_device_id($user_id)
    {      
      $User = UserDevices::where('user_id',$user_id)->first();
      if($User){
        $User->device_id = NULL;
        $User->save();
      }else{
        return redirect()->back()->with('error', 'No Device id');        
      }
      if(!$User){
        return redirect()->back()->with('error', 'Something Went wrong');
      }
      return redirect()->back()->with('success', 'Deleted Sucessfully');
    }

    /**
     * remove device id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function all_remove_device_id()
    {      
      $User = UserDevices::get();
      foreach($User as $item){
        if($item){
          UserDevices::where('id',$item->id)->update(['device_id'=>NULL]);
        }
      }
      if(!$User){
        return redirect()->back()->with('error', 'Something Went wrong');
      }
      return redirect()->back()->with('success', 'Deleted Sucessfully');
    }

    

    /**
     * Change status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function change_password(Request $request, $user_id)
    {      
      $User = User::where('id',$user_id)->first();
      if($request->has('password') && !empty($request->password)){
          $User->password = bcrypt($request->password);
      }
      $User->save();
      if(!$User){
        return redirect()->back()->with('error', 'Something Went wrong');
      }
      return redirect()->back()->with('success', 'Sucessfully');

    }
}
