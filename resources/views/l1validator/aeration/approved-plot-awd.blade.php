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
                <h4>Farmer Plot Details</h4>
            </div>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search..."> 
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
        @php
            $prev=\App\Models\Aeration::select('id','farmer_plot_uniqueid','aeration_no','pipe_no')->where('pipe_no',$awd_data->pipe_no)->where('status','Approved')->where('apprv_reject_user_id',auth()->user()->id)->where('id','<',$awd_data->id)->orderBy('id','desc')->when(request(),function($q){

            return $q;
            })->first()??'';

            $next=\App\Models\Aeration::select('id','farmer_plot_uniqueid','aeration_no','pipe_no')->where('pipe_no',$awd_data->pipe_no)->where('status','Approved')->where('apprv_reject_user_id',auth()->user()->id)->where('id','>',$awd_data->id)->orderBy('id','asc')->when(request(),function($q){

            return $q;
            })->first()??'';
        @endphp
        @if($prev)
          <a style="color: red;" href="{{ url('l1/approved/aeration/plot').'/'.$prev->farmer_plot_uniqueid.'/'.$prev->aeration_no.'/'.$prev->pipe_no}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a>
        @endif
        @if($next)
          <a style="color: red;" href="{{ url('l1/approved/aeration/plot').'/'.$next->farmer_plot_uniqueid.'/'.$next->aeration_no.'/'.$next->pipe_no}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a> 
        @endif                        
        </div>
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-md-4">
            <div class="row">
                    <div class="col-md-12">
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
                                                <td>Farmer Unique Id</td><td>{{$plot->farmer_plot_uniqueid}}</td>
                                            </tr>
                                            <tr>
                                                <td>Farmer Name</td><td>{{$plot->farmer_name}}</td>
                                            </tr>
                                            <tr>
                                                <td>Mobile Access</td><td>{{$plot->mobile_access}}</td>
                                            </tr>
                                            <tr>
                                                <td>Relationship owner</td><td>{{$plot->mobile_reln_owner}}</td>
                                            </tr>
                                            <tr>
                                                <td>Mobile</td><td>{{$plot->mobile}}</td>
                                            </tr>
                                            <tr>
                                                <td>Plot No.</td><td>{{$plot->plot_no}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-primary">
                                            <tr>
                                                <th colspan="{{$plot->state_id == 36 ? '6' : '5'}}" class="text-center">Plot Info</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <tr>
                                              <td>Total Plots</td><td colspan="{{$plot->state_id == 36 ? '3' : '2'}}">{{$plot->plot_no}}</td><td>Area of Plots (Acers)</td><td>{{ $plot->total_area_acres_of_guntha ? $plot->total_area_acres_of_guntha : $plot->total_plot_area}}</td>
                                          </tr>
                                            <tr>
                                                <td>Plot No.</td>
                                                @if($plot->state_id == 36)
                                                    <td>Area in (A.G)</td>
                                                @else
                                                 <td>Area in Acres</td>
                                                @endif
                                                @if($plot->state_id == 36)
                                                    <td>Area in Acres</td>
                                                @endif
                                                <td>Plot Owner</td>
                                                <td>Survey No.</td>
                                                <td class="d-none">Documents</td>
                                                <td>Photos</td>
                                            </tr>
                                            <tr>
                                                @php $color=''; @endphp
                                                @if($plot->final_status_onboarding == 'Pending')
                                                    @php $color = 'blue'; @endphp
                                                @elseif($plot->final_status_onboarding == 'Approved')
                                                    @php $color = 'green'; @endphp
                                                @elseif($plot->final_status_onboarding == 'Rejected')
                                                    @php $color = 'red'; @endphp
                                                @endif
                                                <td>{{$plot->plot_no}}&nbsp;<span class="dot{{$color}}"></span>&nbsp;{{$plot->land_ownership == 'Own' ? 'O' : 'L'}}</td>
                                                @if($plot->state_id == 36)
                                                        <td>{{$plot->area_in_acers}}</td>
                                                        <td>{{$plot->convertedacres}}</td>
                                                   @else
                                                      <td>{{$plot->area_in_acers}}</td>
                                                   @endif
                                                <td>{{$plot->actual_owner_name}}</td>
                                                <td>{{$plot->survey_no}}</td>
                                                <td>
                                                    <div class="plot-gallery d-flex">
                                                    @forelse($plot->ApprvFarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                                    <a class="btn btn-sm p-0 popup-gallery" href="{{$items->path}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                    @empty
                                                    @endforelse
                                                    </div>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer p-0">
                                <div class="row">
                                    @can('carbon download')
                                    <div class="col-6">
                                        <a class="btn text-success btn-sm" href="{{url('admin/farmers/approved/download/'.$plot->farmer_uniqueId.'/'.'CARBON'.'/'.$plot->plot_no)}}"><i class="fa fa-download" aria-hidden="true"></i> Carbon Consent</a>
                                    </div>
                                    @endcan
                                    @can('Download Excel')
                                    <div class="col-6">
                                        <a class="btn text-success btn-sm" href="{{url('l1/download/aeration/'.'aeration'.'/'.$plot->farmer_plot_uniqueid.'/'.$awd_data->plot_no.'/'.'Approved/'.$awd_data->aeration_no)}}"><i class="fa fa-download" aria-hidden="true"></i> Download Excel</a>
                                    </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-primary">
                                            <tr>
                                                <th colspan="2" class="text-center">Location Info</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>State</td><td>{{$plot->state}}</td>
                                            </tr>
                                            <tr>
                                                <td>District</td><td>{{$plot->district}}</td>
                                            </tr>
                                            <tr>
                                                <td>Taluka</td><td>{{$plot->taluka}}</td>
                                            </tr>
                                            <tr>
                                                <td>Panchayat</td><td>{{$plot->panchayat}}</td>
                                            </tr>
                                            <tr>
                                                <td>Village</td><td>{{$plot->village}}</td>
                                            </tr>
                                            <tr>
                                                <td>Latitude</td><td>{{$plot->latitude}}</td>
                                            </tr>
                                            <tr>
                                                <td>Logitude</td><td>{{$plot->longitude}}</td>
                                            </tr>
                                            <tr>
                                                <td class="align-top">Remarks</td><td>{{$plot->remarks}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-primary">
                                            <tr>
                                                <th colspan="2" class="text-center">Executive Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Name</td>
                                                <td>
                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                    <a  target="_blank"  href="{{Route('admin.users.edit',$plot->surveyor_id)}}">{{$plot->surveyor_name}}</a>
                                                @else
                                                    {{$plot->surveyor_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mobile No</td><td>{{$plot->surveyor_mobile}}</td>
                                            </tr>
                                            <tr>
                                                <td>Email ID</td><td>{{$plot->surveyor_email}}</td>
                                            </tr>
                                            <tr>
                                                <td>Date of Survey</td><td>{{$plot->date_survey}}</td>
                                            </tr>
                                            <tr>
                                                <td>Time of Survey</td><td>{{ $plot->time_survey }} </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div><!--executive detail-->
                </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-12">
                  <div class="card" style="margin-right: -24px;margin-left: -24px;">
                      <div class="card-body" style="padding-left: 23px;padding-top: 11px;padding-right: 2px;">
                        <div class="mb-2 mt-1" style="background-color: #450b5a;width: 107%;margin-left: -21px;height: 43px;">
                          <p class="text-center text-white pt-2"><b>CURRENT STATUS</b></p>
                        </div>
                        <div class="mb-1">
                            <div class="row mb-3">
                              <a style="width: 30%;" href="#"  class="active btn btn-status{{$plot->onboarding_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>
                              <a style="width: 26%;" href="#" class="CropDataShow btn btn-status{{$plot->cropdata_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" href="#"  class="btn btn-status{{$plot->pipe_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                            </div>
                            <div class="row mb-3">
                              <a style="width: 30%;" href="#" class="btn btn-status{{$plot->awd_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$plot->benefit_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$plot->other_form ? '-done' : ' disabled'}} m-b-0"><span class="btn-txt">Others</span></a>
                            </div>
                           
                            <!-- end final approve module -->
                          </div><!-- button end -->
                      </div>
                  </div>
                </div>

                <!-- validation data -->
                @if($validation_list->count()>0)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title bg-primary text-white p-3 text-center">Validation</h5>
                                <div class="tab-content">
                                    <div id="plot-{{$PipeInstallation->plot_no}}" class="tab-pane active pt-2">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <tbody>
                                                    <tr><td>Sr.No</td> <td>Aeration no</td> <td>Pipe no</td> <td>Status</td>  <td>Comment</td> <td>L1-Validate</td></tr>
                                                    @foreach($validation_list  as $list)                                                      
                                                       <tr><td>{{$loop->index+1}}</td> <td>{{ $list->aeration_no}}</td><td>{{ $list->pipe_no}}</td> 
                                                       <td>
                                                            <a class="{{ $list->status == 'Approved' ? 'text-success' :'text-danger' }}" >{{ $list->status }}</a> 
                                                        </td> 
                                                        <td>{{ $list->comment}}</td>  <td>{{ $list->ValidatorUserDetail->name??""}}</td></tr>
                                                    @endforeach                                              
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

               
                @if($awd->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">AWD Events</h5>
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a href="#plot-{{$PipeInstallation->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">{{$PipeInstallation->farmer_plot_uniqueid}}</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="plot-{{$PipeInstallation->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tr>
                                                <td>Plot Unique Id</td>
                                                <td>{{$PipeInstallation->farmer_plot_uniqueid}}</td>
                                                <td>Plot Area(Acres)</td>
                                                <td>{{$PipeInstallation->plot_area}}</td>
                                            </tr>
                                            <tr>
                                                <td>Event No.</td>
                                                <td>Pipe No.</td>
                                                <td>Date & Time</td>
                                                <td colspan="2">Photos</td>
                                            </tr>
                                            @foreach($awd as $items)
                                            <tr>
                                                <td>{{$items->aeration_no}}</td>
                                                <td>{{$items->pipe_no}}</td>
                                                <td>{{ $items->date_survey }}, {{\Carbon\Carbon::parse($items->time_survey)->format('h:i A')}}</td>
                                                <td colspan="2">
                                                    <div class="plot-gallery d-flex">
                                                        @forelse($items->AerationImages()->where('plot_no',$items->plot_no)->where('aeration_no',$items->aeration_no)->where('pipe_no',$items->pipe_no)->where('status','Approved')->get() as $imgpath)
                                                            <a class="btn btn-sm p-0 popup-gallery" href="{{$imgpath->path}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                        @empty
                                                        @endforelse
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!--benefit data-->
                @if($plot->BenefitsData->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Farmer Benefits</h5>
                            <ul class="nav nav-pills">
                            @foreach($plot->BenefitsData as $data)
                                <li class="nav-item"><a href="#benefit-{{$loop->index+1}}" class="nav-link {{$loop->first?'active':''}}" data-toggle="tab" aria-expanded="false">Benefit {{$loop->index+1}}</a></li>
                            @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($plot->BenefitsData as $items)
                                <div id="benefit-{{$loop->index+1}}" class="tab-pane pt-2 {{$loop->first?'active':''}}">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Farmer Unique ID</td><td>{{$items->farmer_uniqueId}}</td></tr>
                                                <tr><td>Total Area in Acres</td><td>{{$items->total_plot_area}}</td></tr>
                                                <tr><td>Season</td><td>{{$items->seasons}}</td></tr>
                                                <tr><td>Type of Benefit</td><td>{{$items->benefit}}</td></tr>

                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Surveyor name</td><td><a target="_blank" href="{{Route('admin.users.edit',$items->surveyor_id)}}">{{$items->surveyor_name}}</a></td></tr>
                                                @else
                                                 <tr><td>Surveyor name</td><td>{{$items->surveyor_name}}</td></tr>
                                                @endif
                                                <tr><td>Survey Date/Time</td><td>{{ $items->created_at->toDayDateTimeString() }}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!--benefit data end-->
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
             <!-- awd image -->
             @if($awd)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Aeration Photos</h5>
                            <!-- All plot pipe images -->
                            <div id="plotPipeImg" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach($AwdImage as $imgs)
                                    <li data-target="#plotPipeImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{$imgs->path}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($AwdImage as $img)
                                    <div class="carousel-item plotPipeImg  {{$loop->first?'active':''}}">
                                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                            <a href="{{$img->path}}" class="pipeImgclick" data-caption="Plot no. {{$img->pipe_no}}<br><em class='text-muted'>Pipe Image</em>" data-width="1200" data-height="900" itemprop="contentUrl">
                                              <img class="d-block w-100" height="350" src="{{$img->path}}" itemprop="thumbnail" alt="plot image">
                                            </a>
                                          </figure>
                                    </div>
                                    @endforeach
                                </div>
                                <a class="carousel-control-prev" href="#plotPipeImg" data-slide="prev"><span class="carousel-control-prev-icon"></span> <span
                                        class="sr-only">Previous</span> </a>
                                <a class="carousel-control-next" href="#plotPipeImg" data-slide="next"><span
                                        class="carousel-control-next-icon"></span>
                                    <span class="sr-only">Next</span></a>
                            </div>
                            <!-- plot pipe image end-->
                        </div>
                    </div>
                </div>
                @endif
              <!-- awd image -->
                <!--benfit image-->
                @if($farmerbenefitimg->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Benefits Photos</h5>
                            <!-- All plot images -->
                            <div id="BenefitImg" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach($farmerbenefitimg as $items)
                                    <li data-target="#BenefitImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{ asset('public/storage/'.$items->path)}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($farmerbenefitimg as $items)
                                    <div class="carousel-item benefitsimg {{$loop->first?'active':''}}">
                                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                            <a href="{{$items->path}}" class="benefitImgclick" data-caption="Benefit Image" data-width="1200" data-height="900" itemprop="contentUrl">
                                              <img class="d-block w-100" height="350" src="{{$items->path}}" itemprop="thumbnail" alt="plot image">
                                            </a>
                                        </figure>
                                       {{-- <img class="d-block w-100"  height="350" src="{{ $items->path }}" alt="">  --}}
                                        <!--<img class="d-block w-100"  height="350" src="{{ asset('public/storage/'.$items->path)}}" alt="">-->
                                    </div>
                                    @endforeach
                                </div>
                                <a class="carousel-control-prev" href="#BenefitImg" data-slide="prev"><span class="carousel-control-prev-icon"></span> <span
                                        class="sr-only">Previous</span> </a><a class="carousel-control-next" href="#BenefitImg" data-slide="next"><span
                                        class="carousel-control-next-icon"></span>
                                    <span class="sr-only">Next</span></a>
                            </div>
                            <!-- plot images end-->
                        </div>
                    </div>
                </div>
                @endif
                <!--benefit image end-->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">                            
                              <div id="map" style="width: 100%; height:300px;"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


@stop
@section('scripts')
<script type="text/javascript" src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js" integrity="sha512-E4rXB8fOORHVM/jZYNCX2rIY+FOvmTsWJ7OKZOG9x/0RmMAGyyzBqZG0OGKMpTyyuXVVoJsKKWYwbm7OU2klxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{asset('vendor/photoviewer/dist/photoviewer.min.js') }}"></script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script src="{{asset('js/yepnope.min.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{config('map.map_key')}}&libraries=geometry,places&amp;ext=.js"></script>

<script>
$(document).ready(function() {
  // var test = $('#map-container').hasClass('mapit');
  var test = window.google != undefined;
  $('.OpenMap').click(function() {
    //   $('.OpenMap').addClass('d-none');
    $gmap = true;
    $mapit = false;
    yepnope({  
		    test : test,
		    yep: {
		    	"alreadyLoaded":"timeout=1!"
		      //   "googleMap": "https://maps.googleapis.com/maps/api/js?key="+'{{config('map.map_key')}}'+"&libraries=geometry,places&amp;ext=.js"
		      //"googleMap": "https://maps.googleapis.com/maps/api/js?key=AIzaSyAqvsVxUyfv5KJl0cDoyhEUPtGm5YcVEuM&libraries=geometry,places&amp;ext=.js"
		    },
		    nope: {
		  //  	"googleMap": "https://maps.googleapis.com/maps/api/js?key="+'{{config('map.map_key')}}'+"&libraries=geometry,places&amp;ext=.js"
		  //"googleMap": "https://maps.googleapis.com/maps/api/js?key=AIzaSyAqvsVxUyfv5KJl0cDoyhEUPtGm5YcVEuM&libraries=geometry,places&amp;ext=.js"
		    },
		    callback: {
		    	"alreadyLoaded": function() {
		    		initMap();
		    	}
		    },			
			complete : function(url, result, key){
			    
		    }
		});
	});
    
});

function initMap() {
	var polygon={!!json_encode($Polygon)!!}||[];
	var pipe_location={!! json_encode($PipesLocation) !!}||[];
    var updated_polygon = {!! $updated_polygon !!}||[]; 
	if(polygon.length>0){
		polygon.map(function(v,i){
			v.lat=parseFloat(v.lat);
			v.lng=parseFloat(v.lng);
			return v;
		});
	}
	const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 16,
        center: polygon[0]||{ lat: {!!$plot->latitude!!}, lng: {!! $plot->longitude!!} },
        mapTypeId: "hybrid",
        scrollwheel: true,
      });

      const path = new google.maps.Polygon({
            paths: polygon,
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.35,
        });
        path.setMap(map);


      if(updated_polygon.length > 0){//if this has updated polygon
        var color = ['#FFF2CC', '#E1679C', '#FFD700', '#4885B4', '#BD91E4'];
            if(updated_polygon.length>0){
                $.each(updated_polygon,function(i,v){
                        $.each(v,function(z,poly){
                            var poly_color = color[i];
                            var updatepaths= JSON.parse(poly);
                            updatepath=updatepaths.map(function(n,l){
                                return {lat:parseFloat(n.lat),lng:parseFloat(n.lng)};
                            });                            
                            const update_polygon = new google.maps.Polygon({
                                paths: updatepath,
                                strokeColor: "#FF0000",
                                strokeOpacity: 0.8,
                                strokeWeight: 2,
                                fillColor: poly_color,//"#FF0000",
                                fillOpacity: 0.35,
                            });
                            update_polygon.setMap(map);
                        });
                });
            }        
      }

	 //pipe marker
	if(pipe_location.length>0){
		for (var i = 0; i < pipe_location.length; i++) {
			var marker = new google.maps.Marker({
				position: { lat: parseFloat(pipe_location[i].lat), lng: parseFloat(pipe_location[i].lng) },
				map,
				title: 'Pipe No: '+pipe_location[i].pipe_no,
        icon: {
          url: "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png"
        }
			});
		}
	}

     
}

initMap();

function GetArea(polygon) {
  const length = polygon.length;

  let sum = 0;

  for (let i = 0; i < length; i += 2) {
    sum +=
      polygon[i] * polygon[(i + 3) % length] -
      polygon[i + 1] * polygon[(i + 2) % length];
  }

  console.log(Math.abs(sum) * 0.5);
}

function latlontocart(latlon) {
  let latAnchor = latlon[0][0];
  let lonAnchor = latlon[0][1];
  let x = 0;
  let y = 0;
  let R = 6378137; //radius of earth

  let pos = [];

  for (let i = 0; i < latlon.length; i++) {
    let xPos =
      (latlon[i][1] - lonAnchor) * ConvertToRadian(R) * Math.cos(latAnchor);
    let yPos = (latlon[i][0] - latAnchor) * ConvertToRadian(R);

    pos.push(xPos, yPos);
  }
  return pos;
}

function ConvertToRadian(input) {
  return (input * Math.PI) / 180;
}



(function(){
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
                return $.get('{!! url('l1/aeration/search/Approved') !!}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_plot_uniqueid,
                            value: str.surveyor_name,
                            status:str.status,
                            aeration_no:str.aeration_no,
                            pipe_no:str.pipe_no
                        });
                    });
                    return process(matches);
                });
            },
            templates: {
                suggestion: function(data) {
                    return '<div><a href="{{ url('l1/approved/aeration/plot')}}/'+data.farmer_uniqueId+'/'+data.aeration_no+'/'+data.pipe_no+'"><strong>' + data.farmer_uniqueId + '</strong> - ' + data.status + '</a></div>';
                }                         
            }
        });


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
    $('.plotPipeImg .pipeImgclick').click(function(e){
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).parents('.carousel-item').index(),
                initModalPos:{right:1,top:0}
            };
        $('#plotPipeImg').find('.pipeImgclick').each(function(){
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

})($);

</script>
@stop
