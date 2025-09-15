<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Relationshipowner;
class RelationshipownerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $RelationshipOwner = relationshipowner::orderBy('id','desc')->get();
      $page_title = 'Relationshipowners';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.relationshipowner.index',compact('RelationshipOwner','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create Relationship Owners';
      $page_description = 'Create Relationship owner';
      return view('admin.settings.relationshipowner.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $relationshipowner = new Relationshipowner;
      $relationshipowner->name = $request->name;
      $relationshipowner->status = $request->status;
      $relationshipowner->save();
      if(!$relationshipowner){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.relationshipowner.index')->with('success', 'Saved Successfully');
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
        $relationshipowner = Relationshipowner::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit relationshipowners';
        return view('admin.settings.relationshipowner.edit',compact('action','relationshipowner','page_title'));
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
      $relationshipowner = Relationshipowner::find($id);
      $relationshipowner->name = $request->name;
      $relationshipowner->status = $request->status;
      $relationshipowner->save();
      if(!$relationshipowner){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.relationshipowner.index')->with('success', 'Saved Successfully');
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
            $relationshipowner =Relationshipowner::destroy($request->id);
            if(!$relationshipowner){
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
       * Display a listing of the resource through api response.
       *
       * @return \Illuminate\Http\Response
       */
      public function relationshipowner(){

        try{
          $Relationshipowner = Relationshipowner::orderBy('id','asc')->where('status',1)->get();
          return response()->json(['success'=>true,'relationshipowner'=>$Relationshipowner],200);
        }catch(Exception $e){
          return response()->json(['error'=>true,'message'=>'Something Went wrong'],500);
        }
      }
}
