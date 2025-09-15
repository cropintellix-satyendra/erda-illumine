<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Landownership;

class LandownershipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $landownerships = Landownership::orderby('id','desc')->get();
      $page_title = 'Landownerships';
      $page_description = 'Some description for the page';
 	    $action = 'table_landownerships';
      return view('admin.settings.landownership.index',compact('landownerships','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create landownerships';
      $page_description = 'Create Landownership';
      return view('admin.settings.landownership.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $landownership = new Landownership;
      $landownership->name = $request->name;
      $landownership->status = $request->status;
      $landownership->save();
      if(!$landownership){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.landownership.index')->with('success', 'Saved Successfully');
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
        $landownership = Landownership::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit landownerships';
        $page_description = 'Edit Landownership';
        return view('admin.settings.landownership.edit',compact('action','landownership'));
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
      $landownership = Landownership::find($id);
      $landownership->name = $request->name;
      $landownership->status = $request->status;
      $landownership->save();
      if(!$landownership){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.landownership.index')->with('success', 'Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
      try {
            $landownership =Landownership::destroy($request->id);
            if(!$landownership){
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
}
