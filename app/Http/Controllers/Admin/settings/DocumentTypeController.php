<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $documentType = DocumentType::all();
      $page_title = 'Document Type';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.document_types.index',compact('documentType','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create Document Type';
      $page_description = 'Create Document Type';
      return view('admin.settings.document_types.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $season = new DocumentType;
      $season->document_name = $request->name;
      $season->status = $request->status;
      $season->save();
      if(!$season){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.document_type.index')->with('success', 'Saved Successfully');
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
        $DocumentType = DocumentType::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit DocumentType';
        return view('admin.settings.document_types.edit',compact('action','DocumentType','page_title'));
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
      $DocumentType = DocumentType::find($id);
      $DocumentType->document_name = $request->name;
      $DocumentType->status = $request->status;
      $DocumentType->save();
      if(!$DocumentType){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.document_type.index')->with('success', 'Saved Successfully');
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
            $season =DocumentType::destroy($request->id);
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
      $genders = DocumentType::select('id','name')->where('status',1)->get();
      if(!$genders){
        return response()->json(['error'=>true,'something went wrong'],500);
      }
      return response()->json(['success'=>true,'list'=>$genders],200);
    }
}
