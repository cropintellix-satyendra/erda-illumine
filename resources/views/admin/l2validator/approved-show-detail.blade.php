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
$prev=\App\Models\FinalFarmer::where('farmer_uniqueId','<',$Farmer->farmer_uniqueId)->
    where('final_status_onboarding','Approved')->where('final_status','Approved')->orderBy('id','desc')->first();
$next=\App\Models\FinalFarmer::where('farmer_uniqueId','>',$Farmer->farmer_uniqueId)->
where('final_status_onboarding','Approved')->where('final_status','Approved')->orderBy('id','asc')->first();

            @endphp
            @if($prev)
            <a style="color: red;" href="{{ url('l2/approved/show/'.$prev->farmer_uniqueId)}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a>
            @endif
            @if($next)
            <a style="color: red;" href="{{ url('l2/approved/show/'.$next->farmer_uniqueId)}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
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
                                            <th colspan="{{$Farmer->state_id == 36 ? '6' : '5'}}" class="text-center">Plot Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>No of Plots</td><td colspan="{{$Farmer->state_id == 36 ? '3' : '2'}}">{{$Farmer->no_of_plots}}</td><td>Area of Plots</td><td>{{$Farmer->total_plot_area}}</td>
                                        </tr>
                                        @if($farmerplots->count()>0)
                                            <tr><td>Plot No.</td>
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
                                                        <td>{{$plot->plot_no}}&nbsp;<span class="dot{{$color}}"></span></td>
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
                                                        @forelse($plot->ApprvFarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                                            <a class="btn btn-sm p-0 popup-gallery" href="{{Storage::disk('s3')->url($items->path)}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
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
                              <a style="width: 30%;" class="active btn btn-status{{$Farmer->onboarding_form ? '-done' : 'disabled'}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>
                              <a style="width: 26%;" class="CropDataShow btn btn-status{{$Farmer->cropdata_form ? '-done' : 'disabled'}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" class="btn btn-status{{$Farmer->pipe_form ? '-done' : 'disabled'}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                            </div>
                            <div class="row mb-3">
                              <a style="width: 30%;" class="btn btn-status{{$Farmer->awd_form ? '-done' : 'disabled'}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$Farmer->benefit_form ? '-done' : 'disabled'}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$Farmer->other_form ? '-done' : 'disabled'}} m-b-0"><span class="btn-txt">Others</span></a>
                            </div>
                            @if(!Auth::user()->hasRole('Viewer'))
                            <div class="row">
                                    <a style="width: 30%;" href="{{url('admin/farmers/edit/'.$Farmer->id.'/'.$Farmer->farmer_uniqueId)}}"
                                      class="btn btn-info m-b-0 mr-3 EditBtn d-none"
                                    >EDIT</a>
                                <!-- end button end -->

                              <button style="width: 26%;"
                                  data-toggle="modal" data-target="#ApproveModal"
                                  class="btn btn-success ApproveBtn m-b-0 mr-3 d-none" {{-- below code is to disable button if --}}
                                    >
                                     Approve
                                    <i class="fa fa-spinner fa-spin Aspinner d-none"></i>
                              </button>
                              <!-- approve end -->
                              <button style="width: 30%;" data-toggle="modal" data-target="#reject_remark"
                                    class="btn btn-danger RejectBtn m-b-0 mr-3 d-none"
                                    > {{-- button tag --}}
                                      Reject
                              </button>
                            </div>
                            @endif
                          </div><!-- button end -->
                      </div>
                  </div>
                </div>

                @if($crop_data->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Crop Data</h5>
                            <ul class="nav nav-pills">
                            @foreach($crop_data as $plot)
                                <li class="nav-item"><a href="#plot-{{$loop->index+1}}" class="nav-link {{$loop->first?'active':''}}" data-toggle="tab" aria-expanded="false">Plot {{$loop->index+1}}</a></li>
                            @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($crop_data as $plot)
                                <div id="plot-{{$loop->index+1}}" class="tab-pane {{$loop->first?'active':''}} pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Plot Area</td><td>{{$plot->area_in_acers}}</td></tr>
                                                <tr><td>Crop Season</td><td>{{$plot->season}}</td></tr>
                                                <tr><td>Crop Variety</td><td>{{$plot->crop_variety}}</td></tr>
                                                <tr><td>Date of Irrigation last Season</td><td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $plot->dt_irrigation_last)->format('d-m-Y')}}</td></tr>
                                                <tr><td>Date of Land Preparation</td><td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $plot->dt_ploughing)->format('d-m-Y')}} </td></tr>
                                                <tr><td>Date of Transplanting</td><td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $plot->dt_transplanting)->format('d-m-Y')}}</td></tr>
                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Surveyor name</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$plot->surveyor_id)}}">{{$plot->surveyor_name}}</a></td></tr>
                                                @else
                                                <tr><td>Surveyor name</td><td>{{$plot->surveyor_name}}</td></tr>
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
                <!-- pipe installtion start -->
                @if($PipeInstallation->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Pipe Installations</h5>
                            <ul class="nav nav-pills">
                              @foreach($PipeInstallation as $plot)
                                <li class="nav-item"><a href="#pipe-{{$loop->index+1}}" class="nav-link {{$loop->first?'active':''}}" data-toggle="tab" aria-expanded="false">{{$plot->farmer_plot_uniqueid}}</a></li>
                              @endforeach
                            </ul>
                            <div class="tab-content">
                              @foreach($PipeInstallation as $pipe)
                                <div id="pipe-{{$loop->index+1}}" class="tab-pane {{$loop->first?'active':''}} pt-2" >
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Plot unique Id</td><td>{{$pipe->farmer_plot_uniqueid}}</td></tr>
                                                <tr><td>Plot Area(Oboarding)</td><td>{{$pipe->area_in_acers}}</td></tr>
                                                <tr><td>Plot Area(Google map)</td><td>{{$pipe->plot_area}}</td></tr>
                                                <tr><td>No. of pipes Installed</td><td>{{$pipe->installed_pipe}}</td></tr>
                                                 @if($pipe->pipes_location)
                                                    @foreach(json_decode($pipe->pipes_location) as $array_data)
                                                    <tr><td>Pipe 1 Distance</td>
                                                      <td>
                                                        <div class="plot-gallery d-flex float-left">
                                                            <a class="btn btn-sm p-0 popup-gallery" href="{{$array_data->images}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                        </div>
                                                        <div class="d-inline float-right mt-2">{{$array_data->distance }}M</div>
                                                      </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                                  <tr>
                                                      <td>Date & Time of Installations</td>
                                                      <td>{{$pipe->date }}, {{$pipe->time }}</td>
                                                  </tr>
                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Name of Surveyor</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$pipe->surveyor_id)}}">{{$pipe->surveyor_name}}</a></td></tr>
                                                @else
                                                <tr><td>Name of Surveyor</td><td>{{$pipe->surveyor_name}}</td></tr>
                                                @endif
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
                <!-- AWD -->
                @if($awd->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">AWD Events</h5>
                            <ul class="nav nav-pills">
                                @foreach($PipeInstallation as $plot)
                                  <li class="nav-item"><a href="#awd-{{$loop->index+1}}" class="nav-link {{$loop->first?'active':''}}" data-toggle="tab" aria-expanded="false">{{$plot->farmer_plot_uniqueid}}</a></li>
                                @endforeach
                            </ul>
                            <div class="tab-content">
                              @foreach($PipeInstallation as $awd_list)
                                <div id="awd-{{$loop->index+1}}" class="tab-pane {{$loop->first?'active':''}} pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tr>
                                                <td>Plot Unique Id</td>
                                                <td>{{$plot->farmer_plot_uniqueid}}</td>
                                                <td>Plot Area(Acres)</td>
                                                <td>{{$plot->plot_area}}</td>
                                            </tr>
                                            <tr>
                                                <td>Event No.</td>
                                                <td>Pipe No.</td>
                                                <td>Date & Time</td>
                                                <td colspan="2">Photos</td>
                                            </tr>
                                            @foreach($awd_list->AerationData as $items)
                                            <tr>
                                                <td>{{$items->aeration_no}}</td>
                                                <td>{{$items->pipe_no}}</td>
                                                <td>{{\Carbon\Carbon::parse(strtotime($items->date_survey))->format('d-m-Y')}}, {{\Carbon\Carbon::parse($items->time_survey)->format('h:i A')}}</td>
                                                <td colspan="2">
                                                    <div class="plot-gallery d-flex">
                                                        @forelse($items->AerationImages()->where('plot_no',$items->plot_no)->where('aeration_no',$items->aeration_no)->where('pipe_no',$items->pipe_no)->get() as $imgpath)
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
                                    <li data-target="#PlotImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{Storage::disk('s3')->url($items->path)}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($Farmerplotsimages as $items)
                                    <div class="carousel-item plotImg  {{$loop->first?'active':''}}">
                                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                            <a href="{{Storage::disk('s3')->url($items->path)}}" class="plotImgclick" data-caption="Plot no. {{$items->plot_no}}<br><em class='text-muted'>Plot Image</em>" data-width="1200" data-height="900" itemprop="contentUrl">
                                              <img class="d-block w-100" height="350" src="{{Storage::disk('s3')->url($items->path)}}" itemprop="thumbnail" alt="plot image">
                                            </a>
                                          </figure>
                                        {{-- <img class="d-block w-100" height="350" src="{{Storage::disk('s3')->url($items->path)}}">  --}}
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
                return $.get('{!! url('l2/approved/search') !!}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_uniqueId,
                            farmer_plot_uniqueid:str.farmer_plot_uniqueid,
                            value: str.surveyor_name,
                            status:str.final_status_onboarding
                        });
                    });
                    return process(matches);
                });
            },
            templates: {
                suggestion: function(data) {
                    return '<div><a href="{{ url('l2/approved/show')}}/'+data.farmer_uniqueId+'"><strong>' + data.farmer_plot_uniqueid + '</strong> - ' + data.status + '</a></div>';
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
		  }//if end of confirmation
		  $('.Aspinner').addClass('d-none');
          $(".SubmitApproval").prop('disabled', false);
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
		  }//if end of confirmation
		  $(".FarmerReject").prop('disabled', false);
          $('#Rspinner'+plotno).addClass('d-none');
		})//swal end
});
</script>
@stop
