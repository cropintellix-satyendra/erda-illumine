<?php

namespace App\Http\Controllers\Admin\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
    * Show the Admin user list.
    *
    * @param  int  $id
    * @return \Illuminate\View\View
    */
    public function index(){
        // if(auth()->user()->cannot('user')) abort(403, 'User does not have the right roles.');
        $users = User::whereHas('roles', function($q){
                $q->where('name', 'SuperAdmin'); //fetch user from users table hasrole SuperAdmin
            }
            )->select('id','name','email','mobile','status','last_login')->orderBy('created_at','desc')->get();
        $page_title = 'Admin Users';
        $page_description = 'Admin List';
        $action = 'table_landownerships';
        return view('admin.Adminuser.index', compact('users','page_title', 'page_description','action'));
    }

   /**
     * Show the form for creating a new admin user.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $roles = Role::where('name','SuperAdmin')->get();//fetch user from users table hasrole SuperAdmin
      $action = 'form_pickers';
      $page_title = 'Create admin users';
      $page_description = 'Admin Create User';
      return view('admin.Adminuser.create',compact('action','page_title','roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->mobile = (int) $request->mobile;
        $request->validate([
            'name' => 'required',
            'mobile' => 'required',
        ]);
        $mobile = User::where('mobile',$request->mobile)->first();
        if($mobile){
          return redirect()->back()->with('error', 'Mobile already register');
        }
        $user = new User;
        $user->name = $request->name;
        $user->mobile = $request->mobile;
        $user->email = $request->email;
        $user->status = '0';
        $user->role = 'SuperAdmin';
        $user->password = bcrypt($request->password);
        $user->save();
        // assign new role to the user
        $user->syncRoles($request->roles);
        if(!$user){
          return redirect()->back()->with('error', 'Something went wrongs');
        }
        return redirect()->route('admin.adminlist.index')->with('success', 'Saved Successfully');
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
        $roles = Role::all();
        $user = User::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit admin users';
        $page_description = 'Admin Edit User';
        return view('admin.Adminuser.edit',compact('user','roles','action'));
    }

   /**
     * Edit the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function update(Request $request,$id)
    {
        $validatedData = $request->validate([
                          'name' => 'required',
                          'mobile' => 'required',
                        ]);
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->status = $request->status;
            if($request->has('password') && !empty($request->password)){
                $user->password = bcrypt($request->password);
            }
            $user->save();
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
}
