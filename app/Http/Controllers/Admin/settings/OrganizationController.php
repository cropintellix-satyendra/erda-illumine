<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\State;
use App\Models\Company;
use App\Models\User;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $organizations = Organization::all();
      $page_title = 'organizations';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.organization.index',compact('organizations','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $States=State::all();
      $action = 'form_pickers';
      $page_title = 'Create organizations';
      $page_description = 'Create organizations';
      return view('admin.settings.organization.create',compact('action','page_title','States'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $season = new Organization;
      $season->name = $request->name;
      $season->state_id = $request->state_id;
      $season->status = $request->status;
      $season->save();
      if(!$season){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.organization.index')->with('success', 'Saved Successfully');
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
        $States=State::all();
        $organization = Organization::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit organizations';
        return view('admin.settings.organization.edit',compact('action','organization','page_title','States'));
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
      $season = Organization::find($id);
      $season->name = $request->name;
      $season->state_id = $request->state_id;
      $season->status = $request->status;
      $season->save();
      if(!$season){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.organization.index')->with('success', 'Saved Successfully');
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
            $Organization =Organization::destroy($request->id);
            if(!$Organization){
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['success'=>true,'Delete Successfully'],200);
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
              return response()->json(['error'=>true,'something went wrong'],500);
            }
            return response()->json(['error'=>true,'something went wrong'],500);
        }
    }

    /**
     * Get list of organization from state id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_organization(Request $request)
    {
      $user = User::find($request->user_id);
      $company = Company::where('company_code',$user->company_code)->get();
      return response()->json(['success'=>true,'list'=>$company],200);

    }
}
