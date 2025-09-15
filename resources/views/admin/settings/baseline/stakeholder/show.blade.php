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
    <div class="row page-titles mx-0">
        <div class="col-sm-3 p-md-0">
            <div class="welcome-text">
                <h4>Stake Holder Form Details</h4>
            </div>
        </div>
        <div class="col-md-3">

        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
        </div>
    </div>
    <!-- row -->
    <div class="row mt-3">
        <div class="col-xl-12">
            <h4 class="thead-primary" >Farmer Details</h4>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th colspan="6" class="text-center">Farmer Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Form Number</td><td>{{$farmer_details->coordinator_name ?? 'N/A'}}</td>
                                            <td>Farmer Name</td><td>{{$farmer_details->profession ?? 'N/A'}}</td>
                                        </tr>
                                        <tr>
                                            <td>State</td><td>{{$farmer_details->state}}</td>
                                            <td>Districts</td><td>{{$farmer_details->district}}</td>

                                        </tr>
                                        <tr>
                                            <td>Taluka</td><td>{{$farmer_details->taluka}}</td>
                                            <td>Age</td><td>{{$farmer_details->taluka}}</td>

                                        </tr>
                                        <tr>
                                            <td>Panchayat</td><td>{{$farmer_details->panchayat}}</td>
                                            <td>Village</td><td>{{$farmer_details->village}}</td>

                                        </tr>
                                        <tr>
                                            <td>Average Farm size (in hectares) of a farmer  </td><td>{{$farmer_details->farmsizeframhect??'N/A'}}  <br> {{$farmer_details->farmsizeAcre??'N/A'}} in Acres , <br> {{$farmer_details->farmsizeGunta??'N/A' }} in Guntha , <br>{{$farmer_details->farmsizeHectares??'N/A'}} in Hectares </td>
                                            <td>How many years have you lived in this area? </td><td>{{$farmer_details->livedinthisarea??'N/A'}}  </td>

                                        </tr>
                                        <tr>
                                            <td>In how many seasons, farmers cultivate paddy? </td><td> Rabi {{$farmer_details->rabi_cultivation??'N/A'}}  , <br> Kharif {{$farmer_details->kharif_cultivation??'N/A'}} , <br> Summer {{$farmer_details->summer_cultivation??'N/A' }} </td>
                                            <td>Is agriculture Primary Profession in this village:</td><td>{{$farmer_details->primary_profession ?? 'N/A'}}</td>

                                        </tr>
                                        <tr>
                                            <td>Irrigation Source - Rainfed or Canal or Bore well or any other other source:</td> <td> {{$farmer_details->irrigation_source ??'N/A'}} </td>
                                            <td>Do you know AWD and any progressive farmers following?</td><td> {{$farmer_details->AWD_progressive_farmer}} </td>

                                        </tr>

                                        <tr>
                                            <td>If yes, how are they aware of? through Govt. projects or any Private Organisations or NGO?</td><td> {{$farmer_details->org_or_ngo??'N/A'}} </td>
                                            <td>Method of Sowing in this village and its Percentage?</td><td> {{$farmer_details->trasplantation??'N/A'}} Transplantation, <br> {{$farmer_details->broadcasting??'N/A'}} Broadcasting/DSR , <br> {{$farmer_details->drilling??'N/A'}} Drilling</td>

                                        </tr>
                                        <tr>
                                            <td>Is Proper drainage available in this village? YES/NO and for how much </td><td>{{$farmer_details->drainage_percentage??'N/A'}} %</td>
                                            <td>What is the farm situation related to irrigation?</td><td>{{$farmer_details->farm_situation??'N/A'}}</td>

                                        </tr>
                                        <tr>
                                            <td> No.of Irrigations used to provide in general in the entire cropping period/Season based on the soil structure
                                            in this Village: </td><td>Kharif - {{$farmer_details->kharif_irrigation??'N/A'}} ,<br> Rabi - {{$farmer_details->rabi_irrigation??'N/A'}} ,<br> Summer - {{$farmer_details->summer_irrigation??'N/A'}}</td>
                                            <td>What is the Crop duration in each season - </td><td>Rabi - {{$farmer_details->rabi_cropduration}} ,<br> Kharif - {{$farmer_details->kharif_cropduration}} ,<br> Summer{{$farmer_details->summer_cropduration}}</td>

                                        </tr>
                                        <tr>
                                            <td> What is the major variety have been cultivating for the past 5 years </td><td>{{$farmer_details->cultivating_years ??'N/A'}}</td>
                                            <td>Do farmers want to change the Variety?</td><td>{{$farmer_details->variety_change ??'N/A'}}</td>

                                        </tr>
                                        <tr>
                                            <td>Is there any Secondary Profession work in the off-season? (like alternate profession / MGNREGA etc.)</td><td>{{$farmer_details->secondary_profession ??'N/A'}}</td>
                                            <td>Work in MGNREGA how many days on average in a year</td><td>{{$farmer_details->mgnrega_in_work ??'N/A'}}</td>

                                        </tr>
                                        <tr>
                                             <td>What is the Labour cost per season, Approx
                                                 sowing / transplantation / harvesting / Intercultural operations etc?</td><td>Kharif :{{$farmer_details->kharif_inr ?? 'Not Available'}} INR ,<br> Rabi {{$farmer_details->rabi_inr ?? 'Not Available'}} INR <br>  Summer {{$farmer_details->summer_inr ?? 'Not Available'}} INR </td>
                                            <td>Do they have access to microfinance or loans? </td><td>{{$farmer_details->microfinance_or_loans??'N/A'}}</td>

                                        </tr>
                                        <tr>
                                            <td>{{$farmer_details->mention_name??'N/A'}}</td>
                                            <td>What are the natural risks you have observed in this area?</td><td>{{$farmer_details->natural_risks}}</td>

                                        </tr>
                                        <tr>
                                            <td>What do you feel is AWD suitable in this area/Village?</td><td>{{$farmer_details->awd_suitable}}</td>
                                            <td>Do you understand the benefits of AWD?</td><td>{{$farmer_details->awd_benefits}}</td>

                                        </tr>
                                        <tr>
                                            <td>Do you know how farmers get benefitted by participating in this project?</td><td>{{$farmer_details->farmer_benefits}}</td>
                                            <td>Do you think the project safeguards the farmers interests?</td><td>{{$farmer_details->farmer_interested}}</td>

                                        </tr>
                                        <tr>
                                            <td>Any Suggestions</td><td>{{$farmer_details->any_suggestions}}</td>
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
