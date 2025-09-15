<?php

namespace App\Http\Controllers\Admin\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\State;
use App\Models\District;
use App\Models\Taluka;
use App\Models\Village;
use App\Models\Panchayat;
use App\Models\VendorLocation;

class ValidatorL2Controller extends Controller 
{
  /**
   * Show the user list.
   *
   * @param  int  $id
   * @return \Illuminate\View\View
   */
   public function index(){
    // if(auth()->user()->cannot('viewer')) abort(403, 'User does not have the right roles.');
     $verifiers = User::whereHas('roles', function($q){
                $q->where('name','L-2-Validator');
            })->orderBy('created_at','desc')->get();
     $page_title = 'Verifier';
     $page_description = 'Verifier list';
     $action = 'table_landownerships';
     return view('admin.verifier.index', compact('verifiers','page_title', 'page_description','action'));
   }

   /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      // if(auth()->user()->cannot('add vendor')) abort(403, 'User does not have the right roles.');
      $roles = Role::where('name','L-2-Validator')->get();
      $States = State::all();
      $Districts = District::all();
      $Talukas = Taluka::orderBy('id','desc')->get();
      $Panchayats = Panchayat::orderBy('id','desc')->get();
      $Villages = Village::orderBy('id','desc')->get();
      $action = 'form_pickers';
      $page_title = 'Verifier';
      $page_description = 'Create Verifier';
      return view('admin.verifier.create',compact('action','page_title','roles','States','Districts','Talukas','Villages','Panchayats'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // if(auth()->user()->cannot('add vendor')) abort(403, 'User does not have the right roles.');
        $request->mobile = (int) $request->mobile;
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'mobile' => 'required| min:6',
            'password' => 'required| min:6 |confirmed',
            'password_confirmation' => 'required| min:6',
            'roles' => 'required',
        ]);
        // $mobile = User::where('mobile',$request->mobile)->first();
        // if($mobile){
        //   return redirect()->back()->with('error', 'Mobile already register');
        // }
        $verifier = new User;
        $verifier->name = $request->name;
        $verifier->email = $request->email;
        $verifier->mobile = $request->mobile;
        $verifier->status = '0';
        $verifier->password = bcrypt($request->password);
        $verifier->role = 'L-2-Validator';
        $verifier->save();
        $verifierId = $verifier->id;
        // assign new role to the user
        $verifier->syncRoles($request->roles);


        //store vendor location for filter
        $vendorlocation = new VendorLocation;
        $vendorlocation->user_id   =  $verifierId;
        // dd($request->all());
        $vendorlocation->state   =  implode(',',$request->state);
        if($request->district){
            $vendorlocation->district   =  implode(',',$request->district);
        }
        $vendorlocation->save();


        if(!$verifier){
          return redirect()->back()->with('error', 'Something went wrongs');
        }
        return redirect()->route('admin.verifier.index')->with('success', 'Saved Successfully');
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
      // if(auth()->user()->cannot('edit vendor')) abort(403, 'User does not have the right roles.');
        $States = State::all();
        $roles = Role::where('name','L-2-Validator')->get();
        $verifier = User::find($id);
        $Districts = District::all();
        $Talukas = Taluka::orderBy('id','desc')->get();
        $Panchayats = Panchayat::orderBy('id','desc')->get();
        $Villages = Village::orderBy('id','desc')->get();
        $vendor_location = VendorLocation::where('user_id',$id)->first();
        $action = 'form_pickers';
        $page_title = 'Verifier';
        $page_description = 'Edit Verifier';
        return view('admin.verifier.edit',compact('verifier','roles','action','States','Talukas','Panchayats','Districts','vendor_location'));
    }

   /**
     * Edit the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function update(Request $request,$id)
    {
      // if(auth()->user()->cannot('edit vendor')) abort(403, 'User does not have the right roles.');
        $validatedData = $request->validate([
                      'name' => 'required',
                      'mobile' => 'required',
                    ]);
        $verifier = User::find($id);
        $verifier->name = $request->name;
        $verifier->email = $request->email;
        $verifier->mobile = $request->mobile;
        $verifier->status = $request->status;
        if($request->has('password') && !empty($request->password)){
          $verifier->password = bcrypt($request->password);
        }
        $verifier->save();

         //store vendor location for filter
         $vendorlocation = VendorLocation::where('user_id',$id)->first();
         if(!$vendorlocation){
            $vendorlocation = new VendorLocation;
            $vendorlocation->user_id   =  $id;
         }
          if($request->state){
            $vendorlocation->state   =  implode(',',$request->state);
          }
          else{
            $vendorlocation->state =null;
          }
         if($request->district){
             $vendorlocation->district   =  implode(',',$request->district);
         }
         else{
          $vendorlocation->district   = null;
         }
         $vendorlocation->save();


        if(!$verifier){
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
      if(auth()->user()->cannot('delete vendor')) abort(403, 'User does not have the right roles.');
      try {
            $verifier = User::where('status',0)->find($request->id);
            if($verifier){
                $verifier = User::destroy($request->id);
                if(!$verifier){
                  return response()->json(['error'=>true,'something went wrong'],500);
                }
                return response()->json(['success'=>true,'Delete Successfully'],200);
            }
            return response()->json(['error'=>true,'message'=>'Please disable vendor'],500);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    public function assignrole(){
      $users = User::where('id',157)->get();

      foreach($users as $user){
        dd($user->roles()->first());
           // $user->syncRoles(9);
      }
      return response()->json('no way');
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

}
