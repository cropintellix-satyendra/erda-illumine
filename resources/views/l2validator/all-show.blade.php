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
                <h4>Farmer Details</h4>
            </div>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search...">
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            @php

          $prev=\App\Models\FarmerPlot::with('farmer')->where('farmer_uniqueId','<',$Farmer->farmer_uniqueId)->orderBy('id','desc')->whereHas('farmer',function($q){
              $q->where('onboarding_form','1');

            return $q;
          })->first();

          $next=\App\Models\FarmerPlot::with('farmer')->where('farmer_uniqueId','>',$Farmer->farmer_uniqueId)->orderBy('id','asc')->whereHas('farmer',function($q){
              $q->where('onboarding_form','1');

            return $q;
          })->first();


            @endphp
            @if($prev)
            <a style="color: red;" href="{{ url('l2/all-farmer/show',[$prev->id,$prev->farmer_uniqueId])}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a>
            @endif
            @if($next)
            <a style="color: red;" href="{{ url('l2/all-farmer/show',[$next->id,$next->farmer_uniqueId])}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-xl-4">
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
                                            <td>Farmer Unique Id</td><td>{{$Farmer->farmer_uniqueId}}</td>
                                        </tr>
                                        <tr>
                                            <td>Farmer Name</td><td>{{$Farmer->farmer_name}}</td>
                                        </tr>
                                        <tr>
                                            <td>Mobile Access</td><td>{{$Farmer->mobile_access}}</td>
                                        </tr>
                                        <tr>
                                            <td>Relationship owner</td><td>{{$Farmer->mobile_reln_owner}}</td>
                                        </tr>
                                        <tr>
                                            <td>Mobile</td><td>{{$Farmer->mobile}}</td>
                                        </tr>
                                        <tr>
                                            <td>No. of plots</td><td>{{$Farmer->no_of_plots}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th colspan="{{$Farmer->state_id == 36 ? '8' : '7'}}" class="text-center">Plot Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3">No of Plots</td><td colspan="{{$Farmer->state_id == 36 ? '3' : '2'}}">{{$Farmer->no_of_plots}}</td><td>Area of Plots</td><td>{{ $Farmer->total_area_acres_of_guntha ? $Farmer->total_area_acres_of_guntha : $Farmer->total_plot_area }}</td>
                                        </tr>
                                        @if($farmerplots->count()>0)
                                            <tr><td colspan="3">Plot No.</td>
                                            @if($Farmer->state_id == 36)
                                                <td>Area in (A.G)</td>
                                            @else
                                             <td>Area in Acres</td>
                                            @endif
                                            @if($Farmer->state_id == 36)
                                                <td>Area in Acres</td>
                                            @endif
                                            <td>Plot Owner</td>
                                            <td>Survey No.</td>
                                                <td>Photos</td>
                                            </tr>
                                            @php $color=''; @endphp
                                            @foreach($farmerplots as $plot)
                                                @if($plot->status == 'Pending')
                                                    @php $color = 'blue'; @endphp
                                                @elseif($plot->status == 'Approved')
                                                    @php $color = 'green'; @endphp
                                                @elseif($plot->status == 'Rejected')
                                                    @php $color = 'red'; @endphp
                                                @endif
                                                    <tr>
                                                        <td colspan="3">{{$plot->plot_no}}&nbsp;<span class="dot{{$color}}"></span>&nbsp;{{$plot->land_ownership == 'Own' ? 'O' : 'L'}}</td>
                                                        @if($Farmer->state_id == 36)
                                                            <td>{{$plot->area_in_acers}}</td>
                                                            <td>{{$plot->convertedacres}}</td>
                                                       @else
                                                          <td>{{$plot->area_in_acers}}</td>
                                                       @endif
                                                    <td>{{$plot->actual_owner_name}}</td>
                                                    <td>{{$plot->survey_no}}</td>
                                                    <td class="d-none">@if($plot->affidavit_tnc) <a href="{{url('admin/farmers/download/'.$plot->farmer_uniqueId.'/'.'LEASED'.'/'.$plot->plot_no)}}" style="color: red;">Download</a> @else OWN @endif</td>
                                                    <td>
                                                        <div class="plot-gallery d-flex">
                                                        @forelse($plot->FarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                                            <a class="btn btn-sm p-0 popup-gallery" href="{{$items->path}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                        @empty
                                                        @endforelse
                                                        </div>
                                                    </td>
                                                    </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer p-0">
                            <div class="row">
                                @can('carbon download')
                                <div class="col-6">
                                    <a class="btn text-success btn-sm" href="{{url('admin/farmers/download/'.$plot->farmer_uniqueId.'/'.'CARBON'.'/'.'0')}}"><i class="fa fa-download" aria-hidden="true"></i> Carbon Consent</a>
                                </div>
                                @endcan
                                @can('Download Excel')
                                <div class="col-6">
                                    <a class="btn text-success btn-sm" href="{{url('admin/download/file'.'/?type=onboarding&file=excel&unique='.$plot->farmer_uniqueId)}}"><i class="fa fa-download" aria-hidden="true"></i> Download Excel</a>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
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
                                            <td>State</td><td>{{$Farmer->state}}</td>
                                        </tr>
                                        <tr>
                                            <td>District</td><td>{{$Farmer->district}}</td>
                                        </tr>
                                        <tr>
                                            <td>Taluka</td><td>{{$Farmer->taluka}}</td>
                                        </tr>
                                        <tr>
                                            <td>Panchayat</td><td>{{$Farmer->panchayat}}</td>
                                        </tr>
                                        <tr>
                                            <td>Village</td><td>{{$Farmer->village}}</td>
                                        </tr>
                                        <tr>
                                            <td>Remarks</td><td>{{$Farmer->remarks}}</td>
                                        </tr>
                                        <tr>
                                            <td>Latitude</td><td>{{$Farmer->latitude}}</td>
                                        </tr>
                                        <tr>
                                            <td>Logitude</td><td>{{$Farmer->longitude}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!--location end-->
                <div class="col-12">
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
                                                    <a  target="_blank"  href="{{Route('admin.users.edit',$Farmer->surveyor_id)}}">{{$Farmer->surveyor_name}}</a>
                                                @else
                                                    {{$Farmer->surveyor_name}}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mobile No</td><td>{{$Farmer->surveyor_mobile}}</td>
                                        </tr>
                                        <tr>
                                            <td>Email ID</td><td>{{$Farmer->surveyor_email}}</td>
                                        </tr>
                                        <tr>
                                            <td>Date of Survey</td><td>{{$Farmer->date_survey}}</td>
                                        </tr>
                                        <tr>
                                            <td>Time of Survey</td><td>{{ $Farmer->time_survey }} </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!--executive detail-->
            </div>
        </div>
        <div class="col-xl-4">
            <div class="row">
                <div class="col-12">
                  <div class="card" style="margin-right: -24px;margin-left: -24px;">
                      <div class="card-body" style="padding-left: 23px;padding-top: 11px;padding-right: 2px;">
                        <div class="mb-2 mt-1" style="background-color: #450b5a;width: 107%;margin-left: -21px;height: 43px;">
                          <p class="text-center text-white pt-2"><b>CURRENT STATUS</b></p>
                        </div>
                        <div class="mb-1">
                            <div class="row mb-3">
                              <a style="width: 30%;" class="active btn btn-status{{$Farmer->onboarding_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>
                              <a style="width: 26%;" class="CropDataShow btn btn-status{{$Farmer->cropdata_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" class="btn btn-status{{$Farmer->pipes_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                            </div>
                            <div class="row mb-3">
                              <a style="width: 30%;" class="btn btn-status{{$Farmer->awd_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$Farmer->benefit_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$Farmer->other_form ? '-done' : ''}} m-b-0"><span class="btn-txt">Others</span></a>
                            </div>

                            <div class="row">
                                {{--    <a style="width: 30%;" href="{{url('admin/farmers/edit/'.$Farmer->id.'/'.$Farmer->farmer_uniqueId)}}"
                                      class="btn btn-info m-b-0 mr-3 EditBtn @if($allrejected) disabled @endif @if($allapproved) disabled @endif"

                                       @if($allrejected)
                                         Disabled
                                       @endif
                                       @if($allapproved)
                                         Disabled
                                       @endif
                                      @if($Farmer->status_onboarding == 'Approved')
                                        disabled
                                      @elseif($Farmer->status_onboarding == 'Rejected')
                                        disabled
                                      @else
                                      @endif
                                    >EDIT</a>
                                <!-- end button end -->

                              <button style="width: 26%;"
                                  data-toggle="modal" data-target="#ApproveModal"
                                  class="btn btn-success ApproveBtn m-b-0 mr-3"

                                    @if($allrejected)
                                      Disabled
                                    @endif
                                    >
                                    @if($allrejected)
                                      Approve
                                    @elseif($allapproved)
                                      Approved
                                    @else
                                     Approve
                                    @endif
                                    <i class="fa fa-spinner fa-spin Aspinner d-none"></i>
                              </button>
                              <!-- approve end -->
                              <button style="width: 30%;" data-toggle="modal" data-target="#reject_remark"
                                    class="btn btn-danger RejectBtn m-b-0 mr-3"

                                        @if($allapproved)
                                          Disabled
                                        @endif
                                    >
                                    @if($allrejected)
                                      Rejected
                                    @elseif($allapproved)
                                      Reject
                                    @else
                                      Reject
                                    @endif
                              </button>
                              --}}
                            </div>

                          </div><!-- button end -->
                      </div>
                  </div>

                </div>
                @if($anyrejected > 0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">reject Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      @foreach($farmerplots as $plot)
                                        @if($plot->reason_id)
                                          <tr>
                                              <td>Plot no {{$plot->plot_no}}</td><td>{{$plot->Reasons->reasons}}</td>
                                          </tr>
                                        @endif
                                      @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($Farmer->CropData->count()>800)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Crop Data</h5>
                            <ul class="nav nav-pills">
                            @foreach($Farmer->CropData as $plot)
                                <li class="nav-item"><a href="#plot-{{$plot->plot_no}}" class="nav-link {{$plot->plot_no == '1' ?'active':''}}" data-toggle="tab" aria-expanded="false">Plot {{$plot->plot_no}}</a></li>
                            @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($Farmer->CropData as $plot)
                                <div id="plot-{{$plot->plot_no}}" class="tab-pane {{$plot->plot_no == '1' ?'active':''}} pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Plot Area</td><td>{{$plot->area_in_acers}}</td></tr>
                                                <tr><td>Crop Season</td><td>{{$plot->season}}</td></tr>
                                                <tr><td>Crop Variety</td><td>{{$plot->crop_variety}}</td></tr>
                                                <tr><td>Date of Irrigation last Season</td><td>{{\Carbon\Carbon::parse($plot->dt_irrigation_last)->format('d/m/Y')??''}}</td></tr>
                                                <tr><td>Date of Land Preparation</td><td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $plot->dt_ploughing)->format('d-m-Y')}}</td></tr>
                                                <tr><td>Date of Transplanting</td><td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $plot->dt_transplanting)->format('d-m-Y')}}</td></tr>
                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Surveyor name</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$plot->surveyor_id)}}">{{$plot->surveyor_name}}</a></td></tr>
                                                @else
                                                {{$plot->surveyor_name}}
                                                @endif
                                                <tr><td>Survey Date/Time</td><td>{{ $plot->created_at->toDayDateTimeString() }}</td></tr>
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
                <!--cropdata end-->
                @if($Farmer->BenefitsData->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Farmer Benefits</h5>
                            <ul class="nav nav-pills">
                            @foreach($Farmer->BenefitsData as $data)
                                <li class="nav-item"><a href="#benefit-{{$loop->index+1}}" class="nav-link {{$loop->first?'active':''}}" data-toggle="tab" aria-expanded="false">Benefit {{$loop->index+1}}</a></li>
                            @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($Farmer->BenefitsData as $items)
                                <div id="benefit-{{$loop->index+1}}" class="tab-pane {{$loop->first?'active':''}} pt-2">
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
                                                {{$items->surveyor_name}}
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
            </div>
        </div>
        <div class="col-xl-4">
            <div class="row">
                @if($Farmerplotsimages->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Plot Photos</h5>
                            <!-- All plot images -->
                            <div id="PlotImg" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach($Farmerplotsimages as $items)
                                    <li data-target="#PlotImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{$items->path}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($Farmerplotsimages as $items)
                                    <div class="carousel-item plotImg  {{$loop->first?'active':''}}">
                                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                            <a href="{{$items->path}}" class="plotImgclick" data-caption="Plot no. {{$items->plot_no}}<br><em class='text-muted'>Plot Image</em>" data-width="1200" data-height="900" itemprop="contentUrl">
                                              <img class="d-block w-100" height="350" src="{{$items->path}}" itemprop="thumbnail" alt="plot image">
                                            </a>
                                          </figure>
                                        {{-- <img class="d-block w-100" height="350" src="{{$items->path}}">  --}}
                                        <!--<img class="d-block w-100" height="350" src="{{ asset('public/storage/'.$items->path)}}" alt="">-->
                                    </div>
                                    @endforeach
                                </div>
                                <a class="carousel-control-prev" href="#PlotImg" data-slide="prev"><span class="carousel-control-prev-icon"></span> <span
                                        class="sr-only">Previous</span> </a><a class="carousel-control-next" href="#PlotImg" data-slide="next"><span
                                        class="carousel-control-next-icon"></span>
                                    <span class="sr-only">Next</span></a>
                            </div>
                            <!-- plot images end-->
                        </div>
                    </div>
                </div>
                @endif
                <br>
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
                             <!-- Some spacing ðŸ˜‰ -->
                              <div class="spacer"></div>
                              <!-- Root element of PhotoSwipe. Must have class pswp. -->
                              <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
                                  <!-- Background of PhotoSwipe.
                                       It's a separate element as animating opacity is faster than rgba(). -->
                                  <div class="pswp__bg"></div>
                                  <!-- Slides wrapper with overflow:hidden. -->
                                  <div class="pswp__scroll-wrap">
                                      <!-- Container that holds slides.
                                          PhotoSwipe keeps only 3 of them in the DOM to save memory.
                                          Don't modify these 3 pswp__item elements, data is added later on. -->
                                      <div class="pswp__container">
                                          <div class="pswp__item"></div>
                                          <div class="pswp__item"></div>
                                          <div class="pswp__item"></div>
                                      </div>
                                      <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
                                      <div class="pswp__ui pswp__ui--hidden">
                                          <div class="pswp__top-bar">
                                              <!--  Controls are self-explanatory. Order can be changed. -->
                                              <div class="pswp__counter"></div>
                                              <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                                              <button class="pswp__button pswp__button--share" title="Share"></button>
                                              <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                                              <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                                              <!-- Preloader demo https://codepen.io/dimsemenov/pen/yyBWoR -->
                                              <!-- element will get class pswp__preloader--active when preloader is running -->
                                              <div class="pswp__preloader">
                                                  <div class="pswp__preloader__icn">
                                                    <div class="pswp__preloader__cut">
                                                      <div class="pswp__preloader__donut"></div>
                                                    </div>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                                              <div class="pswp__share-tooltip"></div>
                                          </div>
                                          <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
                                          </button>
                                          <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
                                          </button>
                                          <div class="pswp__caption">
                                              <div class="pswp__caption__center"></div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                <!--image end-->
                {{--<div class="col-12">
                    <div class="card">
                        <div class="card-body">
                              <div id="map" style="width: 100%; height:250px;"></div>
                        </div>
                    </div>
                </div>--}}
            </div>
        </div>
    </div>

</div>
<div class="modal fade" id="reject_remark">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Remark</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
              <div class="container">
                <div class="row">
                  <div class="col" style="font-size: 15px;">
                   1. <strong>Farmer Onboarding</strong>
                  </div>
                  <div class="col">
                  </div>
                </div>
                 @php $color=''; @endphp
                  @foreach($farmerplots as $plot)
                   @if($plot->status == 'Pending')
                        @php $color = 'blue'; @endphp
                    @elseif($plot->status == 'Approved')
                        @php $color = 'green'; @endphp
                    @elseif($plot->status == 'Rejected')
                        @php $color = 'red'; @endphp
                    @endif
                          <div class="row">
                            <div class="col">
                                <label for="plotno" style="margin-right: 11px;">Plot no {{$plot->plot_no}}</label>&nbsp;
                                  <input type="checkbox"  id="plotno{{$plot->plot_no}}"
                                    {{ $plot->status == 'Rejected' ? 'checked disabled' : ''}}
                                    {{ $plot->status == 'Approved' ? 'disabled' : ''}}
                                  name="plotno" value="{{$plot->plot_no}}" {{$Farmer->status_onboarding == 'Approved' ?' ':''}}> &nbsp;<span class="dot{{$color}}"></span>
                                  {{-- $plot->status == 'Rejected' ? '(Rejected)' : ''}}{{ $plot->status == 'Approved' ? '(Approved)' : ''}}
                                  {{ $plot->check_update == '1' ? '(Validate)' : '' --}}
                            </div>
                            <div class="col">
                                 <select {{ $plot->status == 'Rejected' ? 'disabled' : ''}} {{ $plot->status == 'Approved' ? 'disabled' : ''}} id="reasons{{$plot->plot_no}}"
                                            data-plot="{{$plot->plot_no}}"
                                            name="reasons" class="form-control select2">
                                     <option value="">Select Reasons</option>
                                      @foreach($reject_module as $list)
                                        <option value="{{$list->id}}" {{$plot->reason_id == $list->id ? 'Selected' :''}}>{{$list->reasons}}</option>
                                      @endforeach
                                 </select>
                            </div>
                            <div style="margin: 0px 0px 0px 17px;">
                                <label for="reject_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                                <textarea {{ $plot->status == 'Rejected' ? 'readonly' : ''}}  {{ $plot->status == 'Approved' ? 'disabled' : ''}}
                                          class="form-control" id="reject_comment{{$plot->plot_no}}"
                                           name="reject_comment" rows="3" cols="50">{{$plot->reject_comment}}</textarea>
                            </div><br>&nbsp;

                            <span style="color:red" class="Req{{$plot->plot_no}} d-none">Please Tick/Select reasons</span>

                          </div>
                          <div class="row">
                                <div class="col mt-2">
                                  {{$plot->UserApprovedRejected->name??''}}  {{ $plot->reject_timestamp ? '/ '. Carbon\Carbon::parse($plot->reject_timestamp)->toDayDateTimeString() : '' }}<br>
                                  @if(Auth::user()->hasRole('SuperAdmin'))
                                    <a  target="_blank"  href="{{Route('admin.validator.edit',$plot->UserApprovedRejected->id??'')}}">{{$plot->UserApprovedRejected->email??""}}</a>
                                  @else
                                  {{$plot->UserApprovedRejected->email??''}}
                                  @endif

                                </div>
                                <div class="col">
                                   <button {{ $plot->status == 'Rejected' ? 'disabled' : ''}} {{ $plot->status == 'Approved' ? 'disabled' : ''}}
                                    type="button" class="btn btn-primary FarmerReject float-right"
                                    data-rejectplot="{{$plot->plot_no}}" style="margin-top: 5px;">Save plot no {{$plot->plot_no}}
                                    <i id="Rspinner{{$plot->plot_no}}" class="fa fa-spinner fa-spin Rspinner d-none"></i></button>
                                </div>
                              </div>
                          <hr style="margin-top: 51px;">
                  @endforeach
              </div>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ApproveModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve</h5>
                <button type="button" class="close" data-dismiss="modal" style="z-index:99999;" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="margin-top: -53px;">
                <div class="container">
                  <div class="row">
                    <div class="col" style="font-size: 15px;">
                     1. <strong>Farmer Registration</strong>
                    </div>
                    <div class="col">

                    </div>
                  </div>
                  @php $color=''; @endphp
                  @foreach($farmerplots as $plot)
                    @if($plot->status == 'Pending')
                        @php $color = 'blue'; @endphp
                    @elseif($plot->status == 'Approved')
                        @php $color = 'green'; @endphp
                    @elseif($plot->status == 'Rejected')
                        @php $color = 'red'; @endphp
                    @endif
                  <div class="row">
                    <div class="col mt-2">
                        <input type="checkbox"  id="onboarding"
                        {{ $plot->status == 'Rejected' ? 'disabled' : ''}}
                        {{ $plot->status == 'Approved' ? 'checked disabled' : ''}}
                        name="onboarding" value="{{$plot->plot_no}}" {{$Farmer->status_onboarding == 'Approved' ?' ':''}}>
                        <label  title="{{ $plot->status == 'Rejected' ? 'Rejected' : ''}}{{ $plot->status == 'Approved' ? 'Approved' : ''}}{{ $plot->status == 'Pending' ? 'Pending' : ''}}"
                            for="onboarding"
                            style="margin-right: 11px;">Plot no {{$plot->plot_no}}</label>&nbsp;<span class="dot{{$color}}"></span>
                        <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                            <textarea {{ $plot->status == 'Approved' ? 'readonly' : ''}}  {{ $plot->status == 'Rejected' ? 'disabled' : ''}}
                                      class="form-control" id="approve_comment{{$plot->plot_no}}"
                                       name="approve_comment" rows="3" cols="50">{{$plot->approve_comment}}</textarea>
                        </div><br>&nbsp;
                    </div>
                    <div class="col">
                       {{$plot->UserApprovedRejected->name??''}}  {{ $plot->appr_timestamp ? '/  '. Carbon\Carbon::parse($plot->appr_timestamp)->toDayDateTimeString() : "" }} <br>
                          @if(Auth::user()->hasRole('SuperAdmin'))
                            <a  target="_blank"  href="{{Route('admin.validator.edit',$plot->UserApprovedRejected->id??'')}}">{{$plot->UserApprovedRejected->email??""}}</a>
                          @else
                          {{$plot->UserApprovedRejected->email??''}}
                          @endif
                    </div>
                  </div><hr>
                   @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button"
                class="btn btn-primary SubmitApproval">Save <i class="fa fa-spinner fa-spin Aspinner d-none"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('scripts')
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
      var container = [];
      // Loop over gallery items and push it to the array
    //   $('#gallery').find('figure').each(function(){
     $('#PlotImg .plotImg').find('figure').each(function(){
        var $link = $(this).find('a'),
            item = {
              src: $link.attr('href'),
              w: $link.data('width'),
              h: $link.data('height'),
              title: $link.data('caption')
            };
        container.push(item);
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

    }(jQuery));
  </script>
<script>

$("#onboarding").click(function() {
    console.log('ddsd');
    $(".SubmitApproval").prop('disabled', false);
});

$(".SubmitApproval").click(function() {
    // $(".SubmitApproval").prop('disabled', true);
    $('.Aspinner').removeClass('d-none');
    var plots = [];
    $.each($("input[name='onboarding']:checked"), function(){
        var ApproveComment  = $('#approve_comment'+$(this).val()).val();
      plots.push({'PlotNo' : $(this).val(), 'ApproveComment' :ApproveComment});
    });
    Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, Approve it!'
		}).then((result) => {
		  if (result.value == 1) {

                $.ajax({
                  type:'post',
                  url:"{{url('admin/farmers/status/')}}/"+'onboarding/{{$Farmer->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$Farmer->no_of_plots}}"},
                  success:function(data){

                      console.log(data);

                    $('.Aspinner').addClass('d-none');
                    $(".SubmitApproval").prop('disabled', false);
                    jQuery.noConflict(); //Furthermore, some plugins cause errors too, in this case add
                    $('#ApproveModal').modal('hide');
                    location.reload();
                    toastr.success("", data.message, {
                          timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                          progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,
                          onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                          showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",
                          hideMethod: "fadeOut",tapToDismiss: !1
                      })
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                      $('.Aspinner').addClass('d-none');
                    $(".SubmitApproval").prop('disabled', false);
                    var data = jqXHR.responseJSON.farmer;
                    toastr.error("", "Something went wrong", {
                          positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                          debug: !1,newestOnTop: !0,progressBar: !0,
                          preventDuplicates: !0,onclick: null,showDuration: "300",
                          hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
                          hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                          tapToDismiss: !1
                      })
                  }
              });//   ajax end
		  }else{//if end of confirmation
		     $('.Aspinner').addClass('d-none');
            $(".SubmitApproval").prop('disabled', false);
		  }
		})//swal end
});

 $(".FarmerReject").click(function() {
    var plotno = $(this).attr("data-rejectplot");
    var reasons = $('#reasons'+plotno+' option:selected').val();
    var rejectcomment = $('#reject_comment'+plotno).val();
    $(".FarmerReject").prop('disabled', true);
    $('#Rspinner'+plotno).removeClass('d-none');
    if(!reasons.length > 0){
        $('#Rspinner'+plotno).addClass('d-none');
        $(".FarmerReject").prop('disabled', false);
        $('.Req'+plotno).removeClass('d-none');
        return false;
    }
    if(!$('#plotno' + plotno).is(":checked")){
        $('#Rspinner'+plotno).addClass('d-none');
        $(".FarmerReject").prop('disabled', false);
        $('.Req'+plotno).removeClass('d-none');
        return false;
    }
    $('.Req'+plotno).addClass('d-none');
        Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, Reject it!'
		}).then((result) => {
		  if (result.value == 1) {

                $.ajax({
                  type:'post',
                  url:"{{url('admin/farmers/status/')}}/"+'reject'+'/'+'{{$Farmer->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plotno:plotno,reasons:reasons,rejectcomment:rejectcomment,},
                  success:function(data){
                      $('#Rspinner'+plotno).addClass('d-none');
                    location.reload();
                    toastr.success("", "Farmer rejected", {
                          timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                          progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,
                          onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                          showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",
                          hideMethod: "fadeOut",tapToDismiss: !1
                      })
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                      $(".FarmerReject").prop('disabled', false);
                      $('#Rspinner'+plotno).addClass('d-none');
                    var data = jqXHR.responseJSON.farmer;
                    toastr.error("", "Something went wrong", {
                          positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                          debug: !1,newestOnTop: !0,progressBar: !0,
                          preventDuplicates: !0,onclick: null,showDuration: "300",
                          hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
                          hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                          tapToDismiss: !1
                      })
                  }
              });//ajax end
		  }else{//if end of confirmation
		     $(".FarmerReject").prop('disabled', false);
             $('#Rspinner'+plotno).addClass('d-none');
		  }
		})//swal end
});

// function SubmitApproval(module){
//     Swal.fire({
// 			title: 'Are you sure?',
// 			text: "You won't be able to revert this!",
// 			type: 'warning',
// 			showCancelButton: true,
// 			confirmButtonColor: '#3085d6',
// 			cancelButtonColor: '#d33',
// 			confirmButtonText: 'Yes, Approve it!'
// 		}).then((result) => {
// 			if (result.value == 1) {
// 			    $.ajax({
//                       type:'post',
//                       url:"{{url('admin/farmers/status/')}}/"+module+'/{{$Farmer->farmer_uniqueId}}',
//                       data: {_token:'{{csrf_token()}}',method:'post'},
//                       success:function(data){
//                         $('.Aspinner').addClass('d-none');
//                         $(".SubmitApproval").prop('disabled', false);
//                         jQuery.noConflict(); //Furthermore, some plugins cause errors too, in this case add
//                         $('#ApproveModal').modal('hide');
//                         localStorage.removeItem("onboarding");
//                         toastr.success("", "Onboarding Approved", {
//                               timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
//                               progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,
//                               onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
//                               showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",
//                               hideMethod: "fadeOut",tapToDismiss: !1
//                           })
//                       },
//                       error: function (jqXHR, textStatus, errorThrown) {
//                         var data = jqXHR.responseJSON.farmer;
//                         toastr.error("", "Something went wrong", {
//                               positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,
//                               debug: !1,newestOnTop: !0,progressBar: !0,
//                               preventDuplicates: !0,onclick: null,showDuration: "300",
//                               hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
//                               hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
//                               tapToDismiss: !1
//                           })
//                       }
//                   });

// 			}
// 		})

// }
// href="{{url('admin/farmers/status/'.'approve'.'/'.$Farmer->farmer_uniqueId)}}"

// function submitReject(UniqueId){

</script>
@stop
