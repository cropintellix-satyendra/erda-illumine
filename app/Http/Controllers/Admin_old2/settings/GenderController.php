<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Gender;

class GenderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $genders = Gender::all();
      $page_title = 'Gender';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.gender.index',compact('genders','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create Gender';
      $page_description = 'Create Gender';
      return view('admin.settings.gender.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $season = new Gender;
      $season->name = $request->name;
      $season->status = $request->status;
      $season->save();
      if(!$season){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.gender.index')->with('success', 'Saved Successfully');
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
        $gender = Gender::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit Gender';
        return view('admin.settings.gender.edit',compact('action','gender','page_title'));
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
      $Gender = Gender::find($id);
      $Gender->name = $request->name;
      $Gender->status = $request->status;
      $Gender->save();
      if(!$Gender){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.gender.index')->with('success', 'Saved Successfully');
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
            $season =Gender::destroy($request->id);
            if(!$season){
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
     * get list of gender.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_gender()
    {
      $genders = Gender::select('id','name')->where('status',1)->get();
      if(!$genders){
        return response()->json(['error'=>true,'something went wrong'],500);
      }
      return response()->json(['success'=>true,'list'=>$genders],200);
    }
}
