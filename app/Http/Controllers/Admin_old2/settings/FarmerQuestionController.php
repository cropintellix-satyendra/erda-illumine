<?php

namespace App\Http\Controllers\Admin\settings;

use App\Http\Controllers\Controller;
use App\Models\FarmerQuestion;
use App\Models\FarmerQuestionValue;
use Illuminate\Http\Request;

class FarmerQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seasons = FarmerQuestion::all();
        $page_title = 'Questions';
        $page_description = 'Some description for the page';
             $action = 'table_landownerships';
        return view('admin.settings.questions.index',compact('seasons','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        $season = FarmerQuestion::find($id);
        $values = FarmerQuestionValue::where('farmer_question_id', $season->id)->get();
        $action = 'form_pickers';
        $page_title = 'Edit Questions';
        return view('admin.settings.questions.edit',compact('action','season','page_title','values'));
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
    try {
        $farmerQuestion = FarmerQuestion::findOrFail($id);
        $farmerQuestion->question_text = $request->input('question_text');
        $farmerQuestion->status = $request->input('status');
        $farmerQuestion->save();

        $values = $request->input('values', []);
        foreach ($values as $value) {
            $farmerQuestion->values()->updateOrCreate(
                ['question_value' => $value],
                ['status' => 1] // Set status to 1 (enabled)
            );
        }

        // $farmerQuestion->values()->whereNotIn('question_value', $values)->update(['status' => 0]);

        return redirect()->route('admin.questions.index')->with('success', 'Saved Successfully');

        
    } catch (\Exception $e) {

        // dd($e);
        return redirect()->back()->with('error', 'Something went wrong');
    }
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
            $season = FarmerQuestion::destroy($request->id);
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
}
