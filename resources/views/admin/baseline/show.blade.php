@extends('layout.default')
@section('content')
  <!--Import PhotoSwipe Styles -->
  <!-- Import PhotoSwipe Styles -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/photoswipe.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/default-skin/default-skin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css" integrity="sha512-+EoPw+Fiwh6eSeRK7zwIKG2MA8i3rV/DGa3tdttQGgWyatG/SkncT53KHQaS5Jh9MNOT3dmFL0FjTY08And/Cw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-typeahead/2.11.2/jquery.typeahead.min.css" integrity="sha512-UKvJ8GWN7HSI41K3GUfcJInghVOhKi/w0pLNV/5lYluLW1IZPuXu0ANCFibdfp5SAY2CL0cZt6uYos8YqvV1/w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Core CSS file -->
<link href="{{asset('vendor/photoviewer/dist/photoviewer.min.css') }}" rel="stylesheet">
  <style>

  </style>
<div class="container-fluid">
    <div class="row page-titles mx-0 mb-5">
        <div class="col-sm-3 p-md-0">
            <div class="welcome-text">
                <h4>Baseline Survey Form Details</h4>
            </div>
        </div>
        <div class="col-md-3">

        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
        </div>
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-xl-4">
            <h4 class="thead-primary" >Farmer Details</h4>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Farmer Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Form Number</td><td>{{$farmer_details->form_number}}</td>
                                        </tr>
                                        <tr>
                                            <td>Farmer Name</td><td>{{$farmer_details->farmer_name}}</td>
                                        </tr>
                                        <tr>
                                            <td>Mobile No</td><td>{{$farmer_details->mob_no}}</td>
                                        </tr>
                                        <tr>
                                            <td>State</td><td>{{$farmer_details->state}}</td>
                                        </tr>
                                        <tr>
                                            <td>Districts</td><td>{{$farmer_details->district}}</td>
                                        </tr>
                                        <tr>
                                            <td>Taluka</td><td>{{$farmer_details->taluka}}</td>
                                        </tr>
                                        <tr>
                                            <td>Panchayat</td><td>{{$farmer_details->panchayat}}</td>
                                        </tr>
                                        <tr>
                                            <td>Village</td><td>{{$farmer_details->village}}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Land</td><td>{{$farmer_details->total_land}}</td>
                                        </tr>
                                        <tr>
                                            <td>Land Ownership</td><td>{{$farmer_details->land_ownership}}</td>
                                        </tr>
                                        <tr>
                                            <td>Date Of Survey</td><td>{{$farmer_details->date_of_survey}}</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <h4 class="thead-primary" >Crop Details </h4>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                @foreach (['Kharif'] as $season)
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-primary">
                                            <tr>
                                                <th>  {{ $season }} Season</th>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <th>{{ $year }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Name of the variety</td>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('variety')->first() ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Area of nursery sown</td>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('area_nursery')->first() ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Main field area</td>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('field_area')->first() ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Name of the manure applied</td>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('manure_applied')->first() ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>When (Days before sowing)</td>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('before_sowing')->first() ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Quantity applied</td>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('quantity_applied')->first() ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td>Final grain yield (kg)</td>
                                                @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                    <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('final_grain')->first() ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="col-xl-4">
        {{-- <h4 class="thead-primary" >Crop Details </h4> --}}
        <div class="row">
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            @foreach (['Rabi'] as $season)
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th> {{ $season }} Season</th>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <th>{{ $year }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Name of the variety</td>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('variety')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Area of nursery sown</td>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('area_nursery')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Main field area</td>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('field_area')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Name of the manure applied</td>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('manure_applied')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>When (Days before sowing)</td>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('before_sowing')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Quantity applied</td>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('quantity_applied')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>Final grain yield (kg)</td>
                                            @foreach($filtered_years_by_season_cropdetails[$season] as $year)
                                                <td>{{ $cropDetailsByYearAndSeason->get($year . '-' . $season)->pluck('final_grain')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="col-xl-4">
        <h4 class="thead-primary" >Manure Management</h4>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-primary">
                                    <tr>
                                        <th colspan="2"  class="text-center">Name </th>
                                        {{-- <th  class="text-center">Number Of Animals</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Name Of Animals</td><td>{{$manure_quiz->animal_no}}</td>
                                    </tr>
                                    <tr>
                                        <td>where do you normally keep your animals?</td><td>{{$manure_quiz->keep_animal}}</td>
                                    </tr>
                                    <tr>
                                        <td>Total amount of manure produced by animals? </td><td>{{$manure_quiz->produce_animal}}</td>
                                    </tr>
                                    <tr>
                                        <td></td><td>{{$manure_quiz->amount_manure}}</td>
                                    </tr>
                                    <tr>
                                        <td>what happens to the manure from the animals when they are in
                                            the fields? </td><td>{{$manure_quiz->animals_fields}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <h4 class="thead-primary" >Fertilizer application</h4>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            @foreach (['Kharif'] as $season)
                                <h5>{{ $season }} Season</h5>
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            @foreach($filtered_years_by_season[$season] as $year)
                                                <th colspan="3" class="text-center">{{ $year }}</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($filtered_years_by_season[$season] as $year)
                                                <td>DAT</td>
                                                <td>Fertilizer</td>
                                                <td>Quantity (Kg)</td>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @foreach($filtered_years_by_season[$season] as $year)
                                                @php
                                                    $details = $fertilizerByYearAndSeason->get($year . '-' . $season, collect());
                                                @endphp
                                                <td>{{ $details->pluck('date')->first() ?? '-' }}</td>
                                                <td>{{ $details->pluck('fertiliser')->first() ?? '-' }}</td>
                                                <td>{{ $details->pluck('quantity')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-xl-4">
        {{-- <h4 class="thead-primary" >Fertilizer applicationt</h4> --}}
        <div class="row">
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            @foreach ([ 'Rabi'] as $season)
                                <h5>{{ $season }} Season</h5>
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            @foreach($filtered_years_by_season[$season] as $year)
                                                <th colspan="3" class="text-center">{{ $year }}</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($filtered_years_by_season[$season] as $year)
                                                <td>DAT</td>
                                                <td>Fertilizer</td>
                                                <td>Quantity (Kg)</td>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @foreach($filtered_years_by_season[$season] as $year)
                                                @php
                                                    $details = $fertilizerByYearAndSeason->get($year . '-' . $season, collect());
                                                @endphp
                                                <td>{{ $details->pluck('date')->first() ?? '-' }}</td>
                                                <td>{{ $details->pluck('fertiliser')->first() ?? '-' }}</td>
                                                <td>{{ $details->pluck('quantity')->first() ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="col-xl-12">
       <center><h3 class="thead-primary" >Additional Details</h3></center>
        <div class="">

        <div class="row">
            <div class="col-6" style="padding:0px 0px 0px 0px!important">
                <div class="card">
                    <div class="card-body" >
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-primary">
                                    <tr>
                                        <th></th>
                                        @foreach($additional_quiz_season as $year)
                                        <th>{{ $year }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Source of irrigation</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('source_of_irrigation')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Name the crop growing?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('crop_growwing')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>The total duration of main field crops (in days)</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('duration_of_main_field')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>The total duration of nursery crops(in days)</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('duration_of_nursery')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>How many times do you drain the plot?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('drain_no')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>The number of weeding events</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('no_of_weeding')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>How many days does the plot remain drained?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('plot_drain_days')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>What is the average duration of the drainage period?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('avg_drain_period')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Do you have water control over irrigation?</td>
                                    </tr>
                                    <tr>
                                        <td>Do you follow AWD practice before?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('awd_practice')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>From how many seasons you are following AWD?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('seasons_awd')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Are you Burning Stubbles?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('stubbles_burn')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Method of Sowing?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('method_of_sowing')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Do you have access to Micro Finance?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('micro_finance')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Do you want to change variety?</td>
                                        @foreach($additional_quiz_season as $year)
                                        <td>{{ $sortBySeason->get($year)->pluck('change_variety')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6" style="padding:0px 0px 0px 0px!important">
                <div class="card">
                    <div class="card-body" >
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-primary">
                                    <tr>
                                        <th></th>
                                        @foreach($personal_quiz_season as $year)
                                        <th>{{ $year }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Highest Education</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('education')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>How many members in the household</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('member')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Since how many years, you have lived in this area?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('live_in_area')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Is agriculture Primary Profession</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('profession')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>From how many years you are cultivating paddy?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('cultivating_paddy')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Are you interested to change the profession?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('change_profession')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Is there any Secondary Profession you work in off-season?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('off_season')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>If you work for MGNREGA how many days on average in a year?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('avg_in_year')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Do you work on your own land/have external labors?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('external_labour')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>If you use laborers, how much it costs per season during sowing / transplantation / harvesting / etc?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('amount')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>What is the total household income per annum (INR)?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('per_cost')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>What are the natural risks you have observed in this area?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('area')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>What benefits do you see with AWD?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('awd')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Do you know how you would be benefitted by participating in this project?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('participation')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Do you think the project safeguards your interests?</td>
                                        @foreach($personal_quiz_season as $year)
                                        <td>{{ $sortingBySeason->get($year)->pluck('interest')->first() ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>

    </div>



</div>
@stop
@section('scripts')
<script src="{{asset('vendor/photoviewer/dist/photoviewer.min.js') }}"></script>
 <!-- Import jQuery and PhotoSwipe Scripts -->
 <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/photoswipe.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/photoswipe-ui-default.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js" integrity="sha512-IsNh5E3eYy3tr/JiX2Yx4vsCujtkhwl7SLqgnwLNgf04Hrt9BT9SXlLlZlWx+OK4ndzAoALhsMNcCmkggjZB1w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js" integrity="sha512-E4rXB8fOORHVM/jZYNCX2rIY+FOvmTsWJ7OKZOG9x/0RmMAGyyzBqZG0OGKMpTyyuXVVoJsKKWYwbm7OU2klxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="{{asset('vendor/photoviewer/dist/photoviewer.min.js') }}"></script>
   <script>
    /* global jQuery, PhotoSwipe, PhotoSwipeUI_Default, console */
    (function($){
        'use strict';
    //search

        $('input[name="search"]').typeahead({
            hint: true,
            highlight: true,
            minLength: 1,
            limit:10
        },{
            display: 'farmer_uniqueId',
            source:function (query, process) {
                return $.get('{!! route('admin.farmers.index') !!}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_uniqueId,
                            value: str.surveyor_name,
                            status:str.status_onboarding
                        });
                    });
                    return process(matches);
                });
            },
            templates: {
                suggestion: function(data) {
                    return '<div><a href="{{ url('admin/farmers/show')}}/'+data.id+'/'+data.farmer_uniqueId+'"><strong>' + data.farmer_uniqueId + '</strong> - ' + data.status + '</a></div>';
                }
            }
        });
      // Init empty gallery array


      $('.plot-gallery a').click(function (e) {
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).index(),
                initModalPos:{right:1,top:0}
            };
        $(this).parent().find('a').each(function(){
            let src = $(this).attr('href');
            items.push({
                src: src
            });
        });
        new PhotoViewer(items,options);
      });
      $('.plotImg .plotImgclick').click(function(e){
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).parents('.carousel-item').index(),
                initModalPos:{right:1,top:0}
            };
        $('#PlotImg').find('.plotImgclick').each(function(){
            let src = $(this).attr('href');
            items.push({
                src: src
            });
        });
        new PhotoViewer(items,options);
      });
      $('.benefitsimg .benefitImgclick').click(function(e){
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).parents('.carousel-item').index(),
                initModalPos:{right:1,top:0}
            };
        $('.benefitsimg').find('.benefitImgclick').each(function(){
            let src = $(this).attr('href');
            items.push({
                src: src
            });
        });
        new PhotoViewer(items,options);
      });

      $('.pipeImage a').click(function(e){
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).parents('.carousel-item').index(),
                initModalPos:{right:1,top:0}
            };
            $(this).parent().find('a').each(function(){
                let src = $(this).attr('href');
                items.push({
                    src: src
                });
            });
            new PhotoViewer(items,options);
      });

    }(jQuery));
  </script>

@stop
