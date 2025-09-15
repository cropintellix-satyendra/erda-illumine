<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cropvariety;
use App\Models\State;
use App\Models\Season;
class CropvarietyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $crop_variety = Cropvariety::with('states','seasons')->orderBy('id','desc')->get();
      $page_title = 'Crop Varietys';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.cropvariety.index',compact('crop_variety','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create cropvarietys';
      $page_description = 'Create Crop Variety';
      $states = State::all();
      $Seasons = Season::where('status','1')->get();
      return view('admin.settings.cropvariety.create',compact('action','states','Seasons','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $check_data = Cropvariety::where('name','like','%'.$request->name.'%')->first();
      if($check_data){
        return redirect()->back()->with('error', $check_data->name.' already exists for state and seasons');
      }
      $crop_variety = new Cropvariety;
      $crop_variety->name = $request->name;
      $crop_variety->state_id = $request->state_id;
      $state = State::find($request->state_id);
      $crop_variety->state = $state->name;
      $crop_variety->season_id= $request->season_id;
      $crop_variety->status= $request->status;
      $crop_variety->save();
      if(!$crop_variety){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.cropvariety.index')->with('success', 'Saved Successfully');
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
        $crop_variety = Cropvariety::find($id);
        $states = State::all();
        $Seasons = Season::where('status','1')->get();
        $action = 'form_pickers';
        $page_title = 'Edit cropvarietys';
        $page_description = 'Edit crop vareity';
        return view('admin.settings.cropvariety.edit',compact('action','crop_variety','states','Seasons','page_title'));
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
      $crop_variety = Cropvariety::find($id);
      $crop_variety->name = $request->name;
      $crop_variety->state_id = $request->state_id;
      $state = State::find($request->state_id);
      $crop_variety->state = $state->name;
      $crop_variety->season_id= $request->season_id;
      $crop_variety->status= $request->status;
      $crop_variety->save();
      if(!$crop_variety){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.cropvariety.index')->with('success', 'Saved Successfully');
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
            $crop_variety =Cropvariety::destroy($request->id);
            if(!$crop_variety){
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
