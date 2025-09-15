<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fertilizer;

class FertilizerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $fertilizers = Fertilizer::all();
      $page_title = 'Fertilizer';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.fertilizer.index',compact('fertilizers','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create Fertilizer';
      $page_description = 'Create Fertilizer';
      return view('admin.settings.fertilizer.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $season = new Fertilizer;
      $season->name = $request->name;
      $season->status = $request->status;
      $season->save();
      if(!$season){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.fertilizer.index')->with('success', 'Saved Successfully');
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
        $fertilizer = Fertilizer::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit Fertilizer';
        return view('admin.settings.fertilizer.edit',compact('action','fertilizer','page_title'));
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
      $fertilizer = Fertilizer::find($id);
      $fertilizer->name = $request->name;
      $fertilizer->status = $request->status;
      $fertilizer->save();
      if(!$fertilizer){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.fertilizer.index')->with('success', 'Saved Successfully');
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
            $season =Fertilizer::destroy($request->id);
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
    public function get_fertilizer()
    {
      $Fertilizer = Fertilizer::select('id','name')->where('status',1)->get();
      if(!$Fertilizer){
        return response()->json(['error'=>true,'something went wrong'],500);
      }
      return response()->json(['success'=>true,'list'=>$Fertilizer],200);
    }
}
