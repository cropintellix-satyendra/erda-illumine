<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Year;


class YearController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $years = Year::all();
      $page_title = 'Year';
      $page_description = 'Some description for the page';	  
      $action = 'table_landownerships';
      return view('admin.settings.year.index',compact('years','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create Year';
      $page_description = 'Create Years';
      return view('admin.settings.year.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $check=Year::where('year_1',$request->year_1)->where('year_2',$request->year_2)->first();

        if($check){
         return redirect()->back()->with('error', 'Already Exist this year'); 
        }
      $years = new Year;
      $years->year_1 = $request->year_1;
      $years->year_2 = $request->year_2;
      $years->year=$request->year_1.'-'.$request->year_2;

      $years->save();
      if(!$years){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.year.index')->with('success', 'Saved Successfully');
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
        $year = Year::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit Years';
        return view('admin.settings.year.edit',compact('action','year','page_title'));
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
       $check=Year::where('year_1',$request->year_1)->where('year_2',$request->year_2)->first();

       if($check){
        return redirect()->back()->with('error', 'Already Exist this year'); 
       }

      $years = Year::find($id);

      $years->year_1 = $request->year_1;
      $years->year_2 = $request->year_2;
      $years->year=$request->year_1.'-'.$request->year_2;
      $years->save();
      if(!$years){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.year.index')->with('success', 'Saved Successfully');
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
            $years =Year::destroy($request->id);
            if(!$years){
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
