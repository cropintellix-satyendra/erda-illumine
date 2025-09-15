<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Benefit;

class BenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $benefits = Benefit::orderBy('id','desc')->get();
      $page_title = 'Benefits';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.benefit.index',compact('benefits','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = ' Create benefits';
      return view('admin.settings.benefit.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $benefits = new Benefit;
      $benefits->name = $request->name;
      $benefits->status = $request->status;
      $benefits->save();
      if(!$benefits){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.benefit.index')->with('success', 'Saved Successfully');
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
        $benefit = Benefit::find($id);
        $action = 'form_pickers';
        $page_title = ' Edit benefits';
        return view('admin.settings.benefit.edit',compact('action','benefit','page_title'));
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
      $benefit = Benefit::find($id);
      $benefit->name = $request->name;
      $benefit->status = $request->status;
      $benefit->save();
      if(!$benefit){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.benefit.index')->with('success', 'Saved Successfully');
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
            $benefits =Benefit::destroy($request->id);
            if(!$benefits){
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
