<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Year;


class SeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $seasons = Season::all();
      $page_title = 'Seasons';
      $page_description = 'Some description for the page';
 		  $action = 'table_landownerships';
      return view('admin.settings.season.index',compact('seasons','page_title', 'page_description','action'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $action = 'form_pickers';
      $page_title = 'Create seasons';
      $page_description = 'Create seasons';
      return view('admin.settings.season.create',compact('action','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $year = Year::where('id', $request->year)->first();
      $season = new Season;
      $season->name = $request->name;
      $season->status = $request->status;
      $season->month_1 = $request->month1;
      $season->month_2 = $request->month2;
      $season->month = $request->month1 . '-' . $request->month2;
      $season->monthword1 = date('F', strtotime($season->month_1));
      $season->monthword2 = date('F', strtotime($season->month_2));
      $season->month_number_1 = date('m', strtotime($request->month1));
      $season->month_number_2 = date('m', strtotime($request->month2));
      
      // Create an array to hold the month numbers
      $monthNumbers = [];
      
      // Loop through the months between month_number_1 and month_number_2
      for ($i = $season->month_number_1; $i != $season->month_number_2; $i++) {
          // Add leading zero if month number is less than 10
          $monthNumbers[] = str_pad($i, 2, '0', STR_PAD_LEFT);
  
          // Reset month number to 1 if it reaches 12
          if ($i == 12) {
              $i = 0; // Reset to 0 because it will be incremented by 1 in the loop
          }
      }
      
      // Add the last month (month_number_2)
      $monthNumbers[] = str_pad($season->month_number_2, 2, '0', STR_PAD_LEFT);
      
      // Implode the month numbers array to create the month range string
      $season->month_range = implode(',', $monthNumbers);
      
      $season->year = $year->year;
      $season->save();
  
      if (!$season) {
          return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->route('admin.season.index')->with('success', 'Saved Successfully');
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
        $season = Season::find($id);
        $action = 'form_pickers';
        $page_title = 'Edit seasons';
        return view('admin.settings.season.edit',compact('action','season','page_title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //   $year=Year::where('id',$request->year)->first();
    //   $season = Season::find($id);
    //   $season->name = $request->name;
    //   $season->status = $request->status;
    //   $season->month_1 = $request->month1;
    //   $season->month_2 = $request->month2;
    //   $season->month=$request->month1.'-'.$request->month2;
    //   $season->monthword1= date('F', strtotime($season->month_1));
    //   $season->monthword2= date('F', strtotime($season->month_2));
    //   $season->month_number_1 = date('m', strtotime($request->month1));
    //   $season->month_number_2 = date('m', strtotime($request->month2));
    //   $season->year=$year->year;
    //   $month_range=
    //   $season->save();
    //   if(!$season){
    //     return redirect()->back()->with('error', 'Something went wrong');
    //   }
    //   return redirect()->route('admin.season.index')->with('success', 'Saved Successfully');
    // }
    public function updagte(Request $request, $id)
{
    $year = Year::where('id', $request->year)->first();
    $season = Season::find($id);
    $season->name = $request->name;
    $season->status = $request->status;
    $season->month_1 = $request->month1;
    $season->month_2 = $request->month2;
    $season->month = $request->month1 . '-' . $request->month2;
    $season->monthword1 = date('F', strtotime($season->month_1));
    $season->monthword2 = date('F', strtotime($season->month_2));
    $season->month_number_1 = date('m', strtotime($request->month1));
    $season->month_number_2 = date('m', strtotime($request->month2));
    
    // Create an array to hold the month numbers
    $monthNumbers = [];
    
    // Loop through the months between month_number_1 and month_number_2
    for ($i = $season->month_number_1; $i <= $season->month_number_2; $i++) {
        // Add leading zero if month number is less than 10
        $monthNumbers[] = str_pad($i, 2, '0', STR_PAD_LEFT);
    }
    
    // Implode the month numbers array to create the month range string
    $season->month_range = implode(',', $monthNumbers);
    
    $season->year = $year->year;
    $season->save();

    if (!$season) {
        return redirect()->back()->with('error', 'Something went wrong');
    }
    return redirect()->route('admin.season.index')->with('success', 'Saved Successfully');
}

public function update(Request $request, $id)
{
    $year = Year::where('id', $request->year)->first();
    $season = Season::find($id);
    $season->name = $request->name;
    $season->status = $request->status;
    $season->month_1 = $request->month1;
    $season->month_2 = $request->month2;
    $season->month = $request->month1 . '-' . $request->month2;
    $season->monthword1 = date('F', strtotime($season->month_1));
    $season->monthword2 = date('F', strtotime($season->month_2));
    $season->month_number_1 = date('m', strtotime($request->month1));
    $season->month_number_2 = date('m', strtotime($request->month2));
    
    // Create an array to hold the month numbers
    $monthNumbers = [];
    
    // Loop through the months between month_number_1 and month_number_2
    for ($i = $season->month_number_1; $i != $season->month_number_2; $i++) {
        // Add leading zero if month number is less than 10
        $monthNumbers[] = str_pad($i, 2, '0', STR_PAD_LEFT);

        // Reset month number to 1 if it reaches 12
        if ($i == 12) {
            $i = 0; // Reset to 0 because it will be incremented by 1 in the loop
        }
    }
    
    // Add the last month (month_number_2)
    $monthNumbers[] = str_pad($season->month_number_2, 2, '0', STR_PAD_LEFT);
    
    // Implode the month numbers array to create the month range string
    $season->month_range = implode(',', $monthNumbers);
    
    $season->year = $year->year;
    $season->save();

    if (!$season) {
        return redirect()->back()->with('error', 'Something went wrong');
    }
    return redirect()->route('admin.season.index')->with('success', 'Saved Successfully');
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
            $season =Season::destroy($request->id);
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
