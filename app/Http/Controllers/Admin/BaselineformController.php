<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaselineAditionalQuiz;
use App\Models\BaselineCropDetail;
use App\Models\BaselineFarmerDetail;
use App\Models\BaselineFertilizerDetail;
use App\Models\BaselineForm;
use App\Models\BaselineFormDetails;
use App\Models\BaselineManureQuiz;
use App\Models\BaselinePersonalQuiz;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class BaselineformController extends Controller
{
    public function index(){
        $farmer_details =BaselineFarmerDetail::with('surveyor')->get();
        $page_title = 'Baseline Form';
        $page_description = 'Baseline Form';
        $action = 'table_landownerships';
        return view('admin.baseline.index',compact('farmer_details','page_title','page_description','action'));
    }

    public function show($id,$form_number){
        $seasons = ['Kharif', 'Rabi'];
        $farmer_details =BaselineFarmerDetail::with('surveyor')->where('form_number',$form_number)->first();


        $crop_details = BaselineCropDetail::where('form_number',$form_number)->whereIn('season', ['Kharif', 'Rabi'])->get();
        $years = BaselineCropDetail::where('form_number',$form_number)->select('year')->distinct()->orderBy('year', 'desc') ->get()->pluck('year');
        $cropDetailsByYear = BaselineCropDetail::whereIn('year', $years)->get()->groupBy('year');
        // dd($cropDetailsByYear);
        $cropDetailsByYearAndSeason = $crop_details->groupBy(function ($item) {
            return $item->year . '-' . $item->season;
        });
        $filtered_years_by_season_cropdetails = [];
        foreach ($seasons as $season) {
            $filtered_years_by_season_cropdetails[$season] = $crop_details
                ->where('season', $season)
                ->pluck('year')
                ->unique()
                ->sortDesc();
        }


        $fertilizer_details = BaselineFertilizerDetail::where('form_number',$form_number) ->whereIn('season', ['Kharif', 'Rabi'])->get();
        $fertilizer_years = BaselineFertilizerDetail::where('form_number',$form_number)->select('year')->distinct()->orderBy('year', 'desc') ->get()->pluck('year');
        $fertilizerByYear = BaselineFertilizerDetail::whereIn('year', $fertilizer_years)->get()->groupBy('year');
        // Fetch and filter fertilizer details by form number and seasons
        $fertilizer_details = BaselineFertilizerDetail::where('form_number', $form_number)->whereIn('season', ['Kharif', 'Rabi'])->get();
        // Group fertilizer details by year and season
        $fertilizerByYearAndSeason = $fertilizer_details->groupBy(function ($item) {
            return $item->year . '-' . $item->season;
        });
        // Filter years based on seasons
        $filtered_years_by_season = [];
        foreach ($seasons as $season) {
            $filtered_years_by_season[$season] = $fertilizer_details
                ->where('season', $season)
                ->pluck('year')
                ->unique()
                ->sortDesc();
        }



        $manure_quiz = BaselineManureQuiz::where('form_number',$form_number)->first();



        $personal_quiz = BaselinePersonalQuiz::where('form_number',$form_number)->first();
        $personal_quiz_season = BaselinePersonalQuiz::where('form_number',$form_number)->select('season')->distinct()->orderBy('season', 'desc') ->get()->pluck('season');
        $sortingBySeason = BaselinePersonalQuiz::whereIn('season', $personal_quiz_season)->get()->groupBy('season');



        $additional_quiz = BaselineAditionalQuiz::where('form_number',$form_number)->first();
        $additional_quiz_season = BaselineAditionalQuiz::where('form_number',$form_number)->select('season')->distinct()->orderBy('season', 'desc') ->get()->pluck('season');
        $sortBySeason = BaselineAditionalQuiz::whereIn('season', $additional_quiz_season)->get()->groupBy('season');


        $action = 'table_farmer';
        $page_title = 'Baseline Form Show';
        $page_description = 'Baseline Form Show';
        // dd($crop_details);

        return view('admin.baseline.show',compact('farmer_details','crop_details','fertilizer_details','manure_quiz','personal_quiz','additional_quiz','page_description','page_title','action','years','cropDetailsByYear','fertilizerByYear','fertilizer_years','personal_quiz_season','sortingBySeason','additional_quiz_season','sortBySeason','fertilizerByYearAndSeason','filtered_years_by_season','cropDetailsByYearAndSeason','filtered_years_by_season_cropdetails'));




    }


    public function stake_holder_index(){
        $farmer_details =BaselineForm::with('surveyor')->get();
        // dd($farmer_details);
        $page_title = 'Stake Holder Form';
        $page_description = 'Stake Holder Form';
        $action = 'table_landownerships';
        return view('admin.baseline.stakeholder.index',compact('farmer_details','page_title','page_description','action'));

}

public function stake_holder_show($id,$form_number){

    try{
        $farmer_details = BaselineForm::where('form_number',$form_number)->first();
        $form_details = BaselineFormDetails::where('form_number',$form_number)->first();
        // dd($farmer_details);
        $action = 'table_landownerships';
        return view('admin.baseline.stakeholder.show',compact('farmer_details','action','form_details'));

    }  catch (Exception $e) {
        Log::error('Error storing data: ' . $e->getMessage());
        return response()->json(['error' => true, 'message' => 'Something went wrong'], 500);
    }
}
}
