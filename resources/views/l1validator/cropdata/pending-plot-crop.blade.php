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
              $prev=\App\Models\FarmerCropdata::select('id','farmer_plot_uniqueid','plot_no')->where('status','Pending')->where('id','<',$crop_data_detail->id)->orderBy('id','desc')->when(request(),function($q){

              return $q;
              })->first()??'';
              $next=\App\Models\FarmerCropdata::select('id','farmer_plot_uniqueid','plot_no')->where('status','Pending')->where('id','>',$crop_data_detail->id)->orderBy('id','asc')->when(request(),function($q){

              return $q;
              })->first()??'';
          @endphp
          @if($prev)
          <a style="color: red;" href="{{ url('l1/pending/cropdata/plot').'/'.$prev->farmer_plot_uniqueid.'/'.$prev->plot_no}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a>          
          @endif
          @if($next)
          <a style="color: red;" href="{{ url('l1/pending/cropdata/plot').'/'.$next->farmer_plot_uniqueid.'/'.$next->plot_no}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a> 
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
                                                <td class="d-none">@if($plot->affidavit_tnc) <a href="{{url('admin/farmers/download/'.$plot->farmer_plot_uniqueid.'/'.'LEASED'.'/'.$plot->plot_no)}}" style="color: red;">Download</a> @else OWN @endif</td>
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
                                        <a class="btn text-success btn-sm" href="{{url('l1/download/cropdata/'.'Individuals/'.$plot->farmer_plot_uniqueid.'/'.$plot->plot_no.'/'.'Pending')}}"><i class="fa fa-download" aria-hidden="true"></i> Download Excel</a>
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
                              <a style="width: 30%;" href="{{url('l2/approved/plot/detail/'.$plot->farmer_plot_uniqueid)}}" target="_blank" class="active btn btn-status{{$plot->onboarding_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>
                              <a style="width: 26%;" href="{{url('l2/approved/plot/detail/'.$plot->farmer_plot_uniqueid)}}" target="_blank" class="CropDataShow btn btn-status{{$plot->cropdata_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" href="{{url('l2/pipeinstallation/plot/'.$plot->farmer_plot_uniqueid)}}" target="_blank" class="btn btn-status{{$check_pipedata ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                            </div>
                            <div class="row mb-3">
                              <a style="width: 30%;" href="{{url('l2/awd-captured/plot/'.$plot->farmer_plot_uniqueid)}}" target="_blank" class="btn btn-status{{$plot->awd_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$plot->benefit_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$plot->other_form ? '-done' : ' disabled'}} m-b-0"><span class="btn-txt">Others</span></a>
                            </div>
                            @if(!Auth::user()->hasRole('Viewer'))
                            <div class="row">
                                    <a style="width: 30%;" href="{{url('admin/farmers/approved/plot/edit/'.$plot->id.'/'.$plot->farmer_plot_uniqueid)}}"
                                      class="btn btn-info m-b-0 mr-3 EditBtn d-none"
                                       @if($plot->status_onboarding == 'Approved')
                                        disabled
                                      @elseif($plot->status_onboarding == 'Rejected')
                                        disabled
                                      @else
                                      @endif
                                    >EDIT</a>
                                <!-- end button end -->
                              <button style="width: 26%;"
                                  data-toggle="modal" data-target="#ApproveModal" 
                                  class="btn btn-success ApproveBtn m-b-0 mr-3 {{ $crop_data_detail->status == 'Approved' ? 'disabled' : ''}}" {{-- below code is to disable button if --}}
                                    >
                                    Approve
                              </button>
                              <!-- approve end -->
                              <button style="width: 30%;" data-toggle="modal" data-target="#reject_remark"
                                    class="btn btn-danger RejectBtn m-b-0 mr-3 d-none">
                                    Reject
                              </button>
                            </div>
                            @endif
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
                                    <div  class="tab-pane active pt-2">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <tbody>
                                                    <tr><td>level</td> <td>plot no</td>  <td>Status</td>  <td>Comment</td></tr>
                                                    @foreach($validation_list  as $list)                                                      
                                                       <tr><td>{{$list->level}}</td> <td>{{ $list->plot_no}}</td>  <td>{{ $list->status}}</td>  <td>{{ $list->comment}}</td></tr>
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
                @if($plot->PlotCropData->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Crop Data @can('L1-CropData-Edit')<button class="btn btn-success float-right EditCropData {{$crop_data_detail->status == 'Approved' ? 'd-none' : ''}}" style="margin: -16px -14px 0px 0px;width: 84px;height: 50px;">Edit</button>@endcan</h5>
                            <ul class="nav nav-pills">
                            @foreach($plot->PlotCropData as $Croplotbtn)
                                <li class="nav-item"><a href="#plot-{{$Croplotbtn->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">Plot {{$Croplotbtn->plot_no}}</a></li>
                            @endforeach                            
                            <!-- style="margin: 0px 1px 1px 421px;padding: 4px 4px 4px 4px;cursor: pointer;" -->
                            
                            </ul>
                            <div class="tab-content">
                                @foreach($plot->PlotCropData as $Croplot)
                                <div id="plot-{{$Croplot->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody id="CropData">
                                                <tr><td>Plot Id</td><td>{{$Croplot->farmer_plot_uniqueid}}</td></tr>
                                                <tr><td>Plot Area</td><td>{{$Croplot->area_in_acers}}</td></tr>
                                                <tr>
                                                    <td>Crop Season</td>
                                                    <td>
                                                    {{$Croplot->season}}
                                                        <!-- <select id="season" name="season" class="form-control select2 season" disabled>
                                                            <option value="">Select Season</option>
                                                            @foreach($seasons as $season)
                                                                <option value="{{$season->name}}" {{ $season->name == $Croplot->season ? 'Selected' :'' }}>{{$season->name}}</option>
                                                            @endforeach
                                                        </select>
                                                       <br><span class='d-none season_req' style="color:red;">Required</span> -->
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Crop Variety</td>
                                                    <td>
                                                        <!-- <input type="text" class="form-control crop_variety" name="crop_variety" style="" value="{{$Croplot->crop_variety}}" readonly> -->
                                                        <select id="crop_variety" name="crop_variety" class="form-control select2 crop_variety" disabled>
                                                            <option value="">Select Cropvariety</option>
                                                            @foreach($CropvarietyList as $list)
                                                                <option value="{{$list->name}}" {{ $list->name == $Croplot->crop_variety ? 'Selected' :'' }}>{{$list->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        <br><span class='d-none crop_variety_req' style="color:red;">Required</span>
                                                     </td>
                                                </tr>
                                                <tr>
                                                    <td>Date of Irrigation last Season</td>
                                                    <td>
                                                        <input type="date" class="datepicker d-none dt_irrigation_last" id="dt_irrigation_last">
                                                        <span id="dt_irrigation_last_span">{{ $Croplot->dt_irrigation_last}}</span>
                                                    </td>
                                                </tr>                                               
                                                <tr>
                                                    <td>Date of Land Preparation</td>
                                                    <td>
                                                        <input type="date" class="datepicker d-none dt_ploughing" id="dt_ploughing" >
                                                        <span id="dt_ploughing_span">{{ $Croplot->dt_ploughing}}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Date of Transplanting</td>
                                                    <td>
                                                        <input type="date" class="datepicker d-none dt_transplanting" id="dt_transplanting">
                                                        <span id="dt_transplanting_span">{{ $Croplot->dt_transplanting}}</span>
                                                    </td>
                                                </tr>
                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Surveyor name</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$Croplot->surveyor_id)}}">{{$Croplot->surveyor_name}}</a></td></tr>
                                                @else
                                                <tr><td>Surveyor name</td><td>{{$Croplot->surveyor_name}}</td></tr>
                                                @endif
                                                <tr><td>Survey Date/Time</td><td>{{ $Croplot->created_at->toDayDateTimeString() }}</td></tr>
                                            </tbody>
                                        </table>
                                        <button style="padding: 4px 4px 4px 4px;" class="btn btn-success float-right UpdateCropData d-none">Submit <i class="fa fa-spinner fa-spin Updatespinner d-none"></i></button>
                                        <button style="padding: 4px 4px 4px 4px;margin-right: 6px;" class="btn btn-danger float-right CancelCropData d-none">Cancel</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!--cropdata end-->
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
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                @if($plot->ApprvFarmerPlotImages()->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Plot Photos</h5>
                            <!-- All plot images -->
                            <div id="PlotImg" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach($plot->ApprvFarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                    <li data-target="#PlotImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{$items->path}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($plot->ApprvFarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                    <div class="carousel-item plotImg  {{$loop->first?'active':''}}">
                                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                            <a href="{{$items->path}}" class="plotImgclick" data-caption="Plot no. {{$items->plot_no}}<br><em class='text-muted'>Plot Image</em>" data-width="1200" data-height="900" itemprop="contentUrl">
                                              <img class="d-block w-100" height="350" src="{{$items->path}}" itemprop="thumbnail" alt="plot image">
                                            </a>
                                          </figure>
                                    </div>
                                    @endforeach
                                </div>
                                <a class="carousel-control-prev" href="#PlotImg" data-slide="prev"><span class="carousel-control-prev-icon"></span> <span
                                        class="sr-only">Previous</span> </a>
                                <a class="carousel-control-next" href="#PlotImg" data-slide="next"><span
                                        class="carousel-control-next-icon"></span>
                                    <span class="sr-only">Next</span></a>
                            </div>
                            <!-- plot images end-->
                        </div>
                    </div>
                </div>
                @endif
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
                   1. <strong>Farmer Registration</strong>
                  </div>
                  <div class="col">
                  </div>
                </div>
                          <div class="row">
                            <div class="col">
                                <label for="plotno" style="margin-right: 11px;">Plot no {{$plot->plot_no}}</label>&nbsp;
                                  <input type="checkbox"  id="plotno{{$plot->plot_no}}"
                                    {{ $plot->status == 'Rejected' ? 'checked disabled' : ''}}
                                    {{ $plot->status == 'Approved' ? 'disabled' : ''}}
                                  name="plotno" value="{{$plot->plot_no}}" {{$plot->status_onboarding == 'Approved' ?' ':''}}>&nbsp;
                                  {{ $plot->status == 'Rejected' ? '(Rejected)' : ''}}{{ $plot->status == 'Approved' ? '(Approved)' : ''}}
                                  {{ $plot->check_update == '1' ? '(Validate)' : ''}}
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
                          </div>

                          <div class="row">
                                <div class="col mt-2">
                                  {{$plot->FinalUserApprovedRejected->name??''}}   /  {{ Carbon\Carbon::parse($plot->reject_timestamp)->toDayDateTimeString() }}<br>
                                  @if(Auth::user()->hasRole('SuperAdmin'))
                                    <a  target="_blank"  href="{{Route('admin.validator.edit',$plot->FinalUserApprovedRejected->id??'')}}">{{$plot->FinalUserApprovedRejected->email??""}}</a>
                                  @else
                                  {{$plot->FinalUserApprovedRejected->email??''}}
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
              </div>
            </div>
            {{-- <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary FarmerReject">Save <i class="fa fa-spinner fa-spin  d-none"></i></button>
            </div> --}}
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
                     1. <strong>Crop Data</strong>
                    </div>
                    <div class="col">

                    </div>
                  </div>
                  <div class="row">
                    <div class="col mt-2">
                      <input type="checkbox"  id="onboarding"
                      {{ $crop_data_detail->status == 'Rejected' ? 'disabled' : ''}}
                      {{ $crop_data_detail->status == 'Approved' ? 'checked disabled' : ''}}
                      name="onboarding" value="{{$crop_data_detail->plot_no}}" {{$crop_data_detail->status == 'Approved' ?' ':''}}>
                      <label title="{{ $crop_data_detail->status == 'Rejected' ? 'Rejected' : ''}}{{ $crop_data_detail->status == 'Approved' ? 'Approved' : ''}}{{ $crop_data_detail->status == 'Pending' ? 'Pending' : ''}}"
                          for="onboarding"
                          style="margin-right: 11px;">Plot no {{$crop_data_detail->plot_no}}</label>&nbsp;
                      <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                            <textarea {{ $crop_data_detail->status == 'Approved' ? 'readonly' : ''}}  {{ $crop_data_detail->status == 'Rejected' ? 'disabled' : ''}}
                                      class="form-control" id="approve_comment{{$crop_data_detail->plot_no}}"
                                       name="approve_comment" rows="3" cols="50">{{$crop_data_detail->approve_comment}}</textarea>
                        </div><br>&nbsp;
                    </div>
                    <div class="col">
                      
                    </div>
                  </div><hr>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" {{ $crop_data_detail->status == 'Approved' ? 'disabled' : ''}}
                class="btn btn-primary SubmitApproval" disabled>Save <i class="fa fa-spinner fa-spin Aspinner d-none"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>

@stop
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js" integrity="sha512-E4rXB8fOORHVM/jZYNCX2rIY+FOvmTsWJ7OKZOG9x/0RmMAGyyzBqZG0OGKMpTyyuXVVoJsKKWYwbm7OU2klxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{asset('vendor/photoviewer/dist/photoviewer.min.js') }}"></script>

<script src = "https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>  
<script src = "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/js/bootstrap-datetimepicker.min.js"></script>  


<script>
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
                return $.get('{!! url('l1/cropdata/search/Pending') !!}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_plot_uniqueid,
                            farmer_plot_uniqueid:str.farmer_plot_uniqueid,
                            value: str.surveyor_name,
                            status:str.status,
                            plot_no:str.plot_no
                        });
                    });
                    return process(matches);
                });
            },
            templates: {
                suggestion: function(data) {
                    return '<div><a href="{{ url('l1/pending/cropdata/plot')}}/'+data.farmer_plot_uniqueid+'/'+data.plot_no+'"><strong>' + data.farmer_plot_uniqueid + '</strong> - ' + data.status + '</a></div>';
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

      $('#dt_irrigation_last').val('{{$crop_data_detail->dt_irrigation_last}}');
      $('#dt_ploughing').val('{{$crop_data_detail->dt_ploughing}}');
      $('#dt_transplanting').val('{{$crop_data_detail->dt_transplanting}}');
})($);


$('.dt_irrigation_last').change(function() {
    var date = $(this).val();
    var irrigation_date = new Date(date);
    var preparation_date_interval = '{{$date_setting->preparation_date_interval}}';
    var transplantation_date_interval = '{{$date_setting->transplantation_date_interval}}';
    var date_preparation = moment(irrigation_date, "DD-MM-YYYY").add(preparation_date_interval, 'days');
    console.log(date, preparation_date_interval,  moment(date_preparation).format('YYYY/MM/DD'));
    document.getElementById("dt_ploughing").setAttribute("min", moment(date_preparation).format('YYYY-MM-DD'));
   
});
$('.dt_ploughing').change(function() {
    var date = $(this).val();
    var land_prep_date = new Date(date);
    var preparation_date_interval = '{{$date_setting->preparation_date_interval}}';
    var transplantation_date_interval = '{{$date_setting->transplantation_date_interval}}';
    var date_transplanting = moment(land_prep_date, "DD-MM-YYYY").add(transplantation_date_interval, 'days');
    console.log(date, preparation_date_interval,  moment(date_transplanting).format('YYYY/MM/DD'));
    document.getElementById("dt_transplanting").setAttribute("min", moment(date_transplanting).format('YYYY-MM-DD'));
   
});

$('.EditCropData').click(function () {
  $('#CropData input[type=text]').removeAttr('readonly');
  $('.UpdateCropData').removeClass('d-none');
  $('.CancelCropData').removeClass('d-none');
  $("#season").removeAttr('disabled');
  $("#crop_variety").removeAttr('disabled');
  $("#dt_irrigation_last").removeClass('d-none'); 
  $("#dt_ploughing").removeClass('d-none');
  $("#dt_transplanting").removeClass('d-none'); 


  $("#dt_irrigation_last_span").addClass('d-none');
  $("#dt_ploughing_span").addClass('d-none');
  $("#dt_transplanting_span").addClass('d-none');
})
$('.CancelCropData').click(function () {
  $('#CropData input[type=text]').attr('readonly','readonly');
  $("#season").prop("disabled", true);
  $("#crop_variety").prop("disabled", true);
  $('.UpdateCropData').addClass('d-none');
  $('.CancelCropData').addClass('d-none');
  $("#dt_irrigation_last").addClass('d-none'); 
  $("#dt_ploughing").addClass('d-none');
  $("#dt_transplanting").addClass('d-none'); 

  $("#dt_irrigation_last_span").removeClass('d-none');
  $("#dt_ploughing_span").removeClass('d-none');
  $("#dt_transplanting_span").removeClass('d-none');
})
$('.UpdateCropData').click(function(){
    if(!$('#crop_variety option:selected').val()){
       $('.crop_variety_req').removeClass('d-none');
       return false;
    }
    $('.Updatespinner').removeClass('d-none');
    $.ajax({
      type:'post',
      url:"{{url('l1/pending/cropdata/update/')}}/"+'{{$crop_data_detail->farmer_plot_uniqueid}}',
      data: {_token:'{{csrf_token()}}',method:'post',crop_variety:$('#crop_variety option:selected').val(),
                                                     season:$('#season option:selected').val(), 
                                                      dt_irrigation_last:$('#dt_irrigation_last').val(),
                                                      dt_ploughing:$('#dt_ploughing').val(),
                                                      dt_transplanting:$('#dt_transplanting').val()},
      success:function(data){
        $('.Updatespinner').addClass('d-none');
        $(".UpdateCropData").prop('disabled', false);
        $('.UpdateCropData').addClass('d-none');
        $('.CancelCropData').addClass('d-none');
        toastr.success("", 'Updated Successfully', {
              timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
              showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          });
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
          $(".UpdateCropData").prop('disabled', false);
          $('.Updatespinner').addClass('d-none');
        var data = jqXHR.responseJSON;
     
        toastr.error("", data.message, {
              positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
              hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          })
      }
    });//ajax end
});



$("#onboarding").click(function() {
    $(".SubmitApproval").prop('disabled', false);
});

$(".SubmitApproval").click(function() {
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
                  url:"{{url('l1/cropdata/status/')}}/"+'{{$crop_data_detail->farmer_plot_uniqueid}}/{{$crop_data_detail->plot_no}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots},
                  success:function(data){
                    $('.Aspinner').addClass('d-none');
                    $(".SubmitApproval").prop('disabled', false);
                    //jQuery.noConflict(); //Furthermore, some plugins cause errors too, in this case add
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
        return false;
    }
    if(!$('#plotno' + plotno).is(":checked")){
        $('#Rspinner'+plotno).addClass('d-none');
        $(".FarmerReject").prop('disabled', false);
        return false;
    }
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
                  url:"{{url('admin/farmers/status/')}}/"+'reject'+'/'+'{{$plot->farmer_uniqueId}}',
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



// process for final approval js
$("#Finalonboarding").click(function() {
    $(".FinalSubmitApproval").prop('disabled', false);
});

$(".FinalSubmitApproval").click(function() {
    $('.FAspinner').removeClass('d-none');
    var plots = [];
    $.each($("input[name='Finalonboarding']:checked"), function(){
        var FinalApproveComment  = $('#Finalapprove_comment'+$(this).val()).val();
      plots.push({'PlotNo' : $(this).val(), 'FinalApproveComment' :FinalApproveComment});
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
                  url:"{{url('admin/farmers/final/status/')}}/"+'finalonboarding/{{$plot->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$plot->no_of_plots}}"},
                  success:function(data){
                    $('.FAspinner').addClass('d-none');
                    $(".FinalSubmitApproval").prop('disabled', false);
                    //jQuery.noConflict(); //Furthermore, some plugins cause errors too, in this case add
                    $('#FinalApproveModal').modal('hide');
                    location.reload();
                    toastr.success("", data.message, {
                          timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                          progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                          showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                      })
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                      $('.FAspinner').addClass('d-none');
                    $(".FinalSubmitApproval").prop('disabled', false);
                    var data = jqXHR.responseJSON.farmer;
                    toastr.error("", "Something went wrong", {
                          positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                          debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
                          hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                          tapToDismiss: !1
                      })
                  }
              });//   ajax end
		  }//if end of confirmation
		  $('.FAspinner').addClass('d-none');
          $(".FinalSubmitApproval").prop('disabled', false);
		})//swal end
});

$(".FinalFarmerReject").click(function() {
   var plotno = $(this).attr("data-Finalrejectplot");
   var reasons = $('#Finalreasons'+plotno+' option:selected').val();
   var rejectcomment = $('#Finalreject_comment'+plotno).val();

   $(".FinalFarmerReject").prop('disabled', true);
   $('#FRspinner'+plotno).removeClass('d-none');
   if(!reasons.length > 0){
       $('#FRspinner'+plotno).addClass('d-none');
       $(".FinalFarmerReject").prop('disabled', false);
       return false;
   }
   if(!$('#Finalplotno' + plotno).is(":checked")){
       $('#FRspinner'+plotno).addClass('d-none');
       $(".FinalFarmerReject").prop('disabled', false);
       return false;
   }
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
                 url:"{{url('admin/farmers/final/status/')}}/"+'finalreject'+'/'+'{{$plot->farmer_uniqueId}}',
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
     $(".FinalFarmerReject").prop('disabled', false);
         $('#FRspinner'+plotno).addClass('d-none');
   })//swal end
});

    $('#season').select2({
		selectOnClose: true
	});
    $('#crop_variety').select2({
		selectOnClose: true
	});

    
</script>
@stop
