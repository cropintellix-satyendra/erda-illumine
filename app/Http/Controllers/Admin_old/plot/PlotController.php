<?php

namespace App\Http\Controllers\Admin\plot;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Landownership;
use App\Models\FarmerPlot; 

class PlotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $plots = FarmerPlot::groupBy('farmer_id')->get();
      $page_title = 'Table table_landownerships Basic';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.plots.index',compact('plots','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      return view('admin.settings.landownership.create',compact('action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // $validatedData = $request->validate([
      //   'name' => 'required',
      //   'status' => 'required',
      // ]);
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
      // $validatedData = $request->validate([
      //   'name' => 'required',
      //   'status' => 'required',
      // ]);
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
    public function destroy(Request $request)
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
