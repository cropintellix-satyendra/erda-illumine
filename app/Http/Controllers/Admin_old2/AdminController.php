<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorLocation;
use App\Models\State;

class AdminController extends Controller
{
    /**
     * Admin profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_profile()
    {
      $page_title = 'Profile';
      $page_description = 'Profile';
      $action = 'form_pickers';
      $admin = User::with('state')->where('id',auth()->user()->id)->first();
      $vendor_location = VendorLocation::where('user_id',auth()->user()->id)->first();  
      $States = State::whereIn('id',explode(',',$vendor_location->state??''))->get();
      return view('admin.profile', compact('admin','page_title', 'page_description','action','vendor_location','States'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_profile_update(Request $request, $id)
    {
         $validatedData = $request->validate([
                        'email' => 'required',
                        'mobile' => 'required',
                        ]);
          $admin = User::find($id);
          $admin->name = $request->name;
          $admin->email = $request->email;
          $admin->mobile = $request->mobile;
          if($request->has('password') && !empty($request->password)){
            $admin->password = bcrypt($request->password);
          }
          $admin->save();
      if(!$admin){
        return redirect()->back()->withErrors(['Something went wrongs']);
      }
      return redirect()->back()->with('success', 'Saved Successfully!');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
