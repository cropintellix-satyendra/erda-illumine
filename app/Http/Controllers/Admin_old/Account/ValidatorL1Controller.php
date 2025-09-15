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

class ValidatorL1Controller extends Controller
{
  /**
   * Show the user list.
   *
   * @param  int  $id
   * @return \Illuminate\View\View
   */
   public function index(){
    if(auth()->user()->cannot('L-1-Validator')) abort(403, 'User does not have the right roles.');
     $Validators = User::whereHas('roles', function($q){
                $q->whereIn('name',['L-1-Validator','SuperValidator']);//fetch user from users table hasrole SuperValidator  L-1-Validator
            })->orderBy('created_at','desc')->get();
     $page_title = 'Validators';
     $page_description = 'Validator list';
     $action = 'table_landownerships';
     return view('admin.validator.index', compact('Validators','page_title', 'page_description','action'));
   }

   /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      if(auth()->user()->cannot('add L-1-Validator')) abort(403, 'User does not have the right roles.');
      $States = State::all();
      $Districts = District::all();
      $Talukas = Taluka::orderBy('id','desc')->get();
      $Panchayats = Panchayat::orderBy('id','desc')->get();
      $Villages = Village::orderBy('id','desc')->get();
      $roles = Role::whereIn('name',['L-1-Validator','SuperValidator'])->get();
      $action = 'form_pickers';
      $page_title = 'Create Validator';
      $page_description = 'Create Validator';
      return view('admin.validator.create',compact('action','page_title','roles','States','Districts','Talukas','Panchayats','Villages'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      if(auth()->user()->cannot('add L-1-Validator')) abort(403, 'User does not have the right roles.');
        $request->mobile = (int) $request->mobile;
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'mobile' => 'required| min:6',
            'state' => 'required',
            'password' => 'required| min:6 |confirmed',
            'password_confirmation' => 'required| min:6',
            'roles' => 'required',
        ]);
        $vendor = new User;
        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->mobile = $request->mobile;
        $vendor->status = '0';
        $vendor->password = bcrypt($request->password);
        $roles = Role::where('id',$request->roles)->first();
        $vendor->role = $roles->name;
        $vendor->save();
        $vendorId = $vendor->id;
        // assign new role to the user
        $vendor->syncRoles($request->roles);
        //store vendor location for filter
        $vendorlocation = new VendorLocation;
        $vendorlocation->user_id   =  $vendorId;
        // dd($request->all());
        $vendorlocation->state   =  implode(',',$request->state);
        if($request->district){
            $vendorlocation->district   =  implode(',',$request->district);
        }
        if($request->block){
            $vendorlocation->taluka   =  implode(',',$request->block);
        }
        $vendorlocation->save();
        if(!$vendor){
          return redirect()->back()->with('error', 'Something went wrongs');
        }
        return redirect()->route('admin.validator.index')->with('success', 'Saved Successfully');
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
      if(auth()->user()->cannot('edit L-1-Validator')) abort(403, 'User does not have the right roles.');
        $States = State::all();
        $Districts = District::all();
        $Talukas = Taluka::orderBy('id','desc')->get();
        $Panchayats = Panchayat::orderBy('id','desc')->get();
        $Villages = Village::orderBy('id','desc')->get();
        $vendor_location = VendorLocation::where('user_id',$id)->first();
        $vendor = User::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit Validator';
        $page_description = 'Edit Validator';
        return view('admin.validator.edit',compact('vendor','action','States','Districts','Talukas','Panchayats','Villages','vendor_location'));
    }

   /**
     * Edit the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function update(Request $request,$id)
    {
      if(auth()->user()->cannot('edit L-1-Validator')) abort(403, 'User does not have the right roles.');
        $validatedData = $request->validate([
                      'name' => 'required',
                      'mobile' => 'required',
                    ]);
        $vendor = User::find($id);
        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->mobile = $request->mobile;
        $vendor->status = $request->status;
        if($request->has('password') && !empty($request->password)){
          $vendor->password = bcrypt($request->password);
        }
        $vendor->save();
         //store vendor location for filter
         $vendorlocation = VendorLocation::where('user_id',$id)->first();
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
         if($request->block){
             $vendorlocation->taluka   =  implode(',',$request->block);
         }
         else{
          $vendorlocation->taluka   = null;
         }
         $vendorlocation->save();

        if(!$vendor){
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
      if(auth()->user()->cannot('delete validator')) abort(403, 'User does not have the right roles.');
      try {
            $vendor = User::where('status',0)->find($request->id);
            if($vendor){
                $vendor = User::destroy($request->id);
                if(!$vendor){
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
}
