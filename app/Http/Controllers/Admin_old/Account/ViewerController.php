<?php

namespace App\Http\Controllers\Admin\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\State;
use App\Models\ViewerLocation;

class ViewerController extends Controller
{
  /**
   * Show the user list.
   *
   * @param  int  $id
   * @return \Illuminate\View\View
   */
   public function index(){
     $viewers = User::whereHas('roles', function($q){
                $q->where('name','Viewer');//fetch user from users table hasrole Viewer
            })->orderBy('created_at','desc')->get();
     $page_title = 'Viewers';
     $page_description = 'Viewer list';
     $action = 'table_landownerships';
     return view('admin.viewer.index', compact('viewers','page_title', 'page_description','action'));
   }

   /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $roles = Role::where('name','Viewer')->get();
      $States = State::all();
      $action = 'form_pickers';
      $page_title = 'Viewers';
      $page_description = 'Create Viewer';
      return view('admin.viewer.create',compact('action','page_title','roles','States'));
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
            'email' => 'required|unique:users',
            'mobile' => 'required|min:6|unique:users',
            'password' => 'required| min:6 |confirmed',
            'password_confirmation' => 'required| min:6',
            'roles' => 'required',
        ]);
        $viewer = new User;
        $viewer->name = $request->name;
        $viewer->email = $request->email;
        $viewer->mobile = $request->mobile;
        $viewer->status = '0';
        $viewer->password = bcrypt($request->password);
        $viewer->role = 'Viewer';
        $viewer->save();
        $viewerId = $viewer->id;
        // assign new role to the user
        $viewer->syncRoles($request->roles);

        //store vendor location for filter
        $viewerlocation = new ViewerLocation;
        $viewerlocation->user_id   =  $viewerId;
        $viewerlocation->state   =  implode(',',$request->state);
        $viewerlocation->save();

        if(!$viewer){
          return redirect()->back()->with('error', 'Something went wrongs');
        }
        return redirect()->route('admin.viewer.index')->with('success', 'Saved Successfully');
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
        $States = State::all();
        $viewer = User::find($id);
        $viewer_location = ViewerLocation::where('user_id',$id)->first();
        $action = 'form_pickers';
        $page_title = 'Edit';
        $page_description = 'Edit Viewer';
        return view('admin.viewer.edit',compact('viewer','action','States','viewer_location'));
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
        $viewer = User::find($id);
        $viewer->name = $request->name;
        $viewer->email = $request->email;
        $viewer->mobile = $request->mobile;
        $viewer->status = $request->status;
        if($request->has('password') && !empty($request->password)){
          $viewer->password = bcrypt($request->password);
        }
        $viewer->save();
         //update vendor location for filter
         $viewerlocation = ViewerLocation::where('user_id',$id)->first();
         if($request->state){
              $viewerlocation->state   =  implode(',',$request->state);
            }
            else{
              $viewerlocation->state =null;
            }
         $viewerlocation->save();

        if(!$viewer){
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
            $viewer = User::where('status',0)->find($request->id);
            if($viewer){
                $viewer = User::destroy($request->id);
                if(!$viewer){
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
