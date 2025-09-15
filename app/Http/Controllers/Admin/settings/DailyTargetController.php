<?php

namespace App\Http\Controllers\Admin\settings;

use App\Http\Controllers\Controller;
use App\Models\DailyTarget;
use Illuminate\Http\Request;

class DailyTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $target = DailyTarget::all();
      $page_title = 'Year';
      $page_description = 'Some description for the page';
      $action = 'table_landownerships';
      return view('admin.settings.daily_target.index',compact('target','page_title', 'page_description','action'));
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
      return view('admin.settings.daily_target.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'module_name' => 'required',
            'daily_target' => 'required',
          ]);
        $check=DailyTarget::where('module_name',$request->module_name)->where('daily_target',$request->daily_target)->first();

        if($check){
         return redirect()->back()->with('error', 'Already Exist this year');
        }
      $years = new DailyTarget;
      $years->module_name = $request->module_name;
      $years->daily_target = $request->daily_target;
    //   $years->year=$request->year_1.'-'.$request->year_2;

      $years->save();
      if(!$years){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.daily_target.index')->with('success', 'Saved Successfully');
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
        $target = DailyTarget::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit Years';
        return view('admin.settings.daily_target.edit',compact('action','target','page_title'));
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
        $check=DailyTarget::where('module_name',$request->module_name)->where('daily_target',$request->daily_target)->first();

       if($check){
        return redirect()->back()->with('error', 'Already Exist this year');
       }

      $target = DailyTarget::find($id);

      $target->module_name = $request->module_name;
      $target->daily_target = $request->daily_target;
      $target->save();
      if(!$target){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.daily_target.index')->with('success', 'Saved Successfully');
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
            $target =DailyTarget::destroy($request->id);
            if(!$target){
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
