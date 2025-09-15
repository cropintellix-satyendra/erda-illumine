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
            $prev=\App\Models\PipeInstallation::select('id','farmer_plot_uniqueid')->where('id','<',$PipeInstallation->id)->orderBy('id','desc')->when(request(),function($q){

            return $q;
            })
            ->whereHas('pipe_image',function($im){
                $im->when('filter',function($c){
                    $c->where('status','Rejected');
                    return $c;
                });
                return $im;
           })
            ->first()??'';


           $next=\App\Models\PipeInstallation::select('id','farmer_plot_uniqueid')->where('id','>',$PipeInstallation->id)->orderBy('id','desc')->when(request(),function($q){

            return $q;
            })
            ->whereHas('pipe_image',function($im){
                $im->when('filter',function($c){
                    $c->where('status','Rejected');
                    return $c;
                });
                return $im;
            })
            ->first()??'';
          @endphp
          @if($prev)
          <a style="color: red;" href="{{ url('l1/rejected/pipeinstallation/plot').'/'.$prev->farmer_plot_uniqueid}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a> 
          @endif                      
          @if($next)
          <a style="color: red;" href="{{ url('l1/rejected/pipeinstallation/plot').'/'.$next->farmer_plot_uniqueid}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a> 
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
                                        <a class="btn text-success btn-sm" href="{{url('admin/download/approved/file'.'/?type=onboarding&file=excel&unique='.$plot->farmer_uniqueId)}}"><i class="fa fa-download" aria-hidden="true"></i> Download Excel</a>
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
                              <a style="width: 26%;" href="#"  class="CropDataShow btn btn-status{{$plot->cropdata_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" href="#"  class="btn btn-status{{$plot->pipe_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                            </div>
                            <div class="row mb-3">
                              <a style="width: 30%;" href="#"  class="btn btn-status{{$plot->awd_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$plot->benefit_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$plot->other_form ? '-done' : ' disabled'}} m-b-0"><span class="btn-txt">Others</span></a>
                            </div>
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
                                  data-toggle="modal" data-target="#PipeApprove" 
                                  class="btn btn-success ApproveBtn m-b-0 mr-3 d-none" {{-- below code is to disable button if --}}
                                    >
                                    Approve
                                    <i class="fa fa-spinner fa-spin Aspinner d-none"></i>
                              </button>
                              <!-- approve end -->
                              <button style="width: 30%;" data-toggle="modal" data-target="#Finalreject_remark" 
                                    class="btn btn-danger RejectBtn m-b-0 mr-3 d-none">
                                    Reject
                              </button>
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
                                                    <tr><td>Sr.No</td> <td>pipe no</td>  <td>Status</td>  <td>Comment</td></tr>
                                                    @foreach($validation_list  as $list)                                                      
                                                       <tr><td>{{$loop->index+1}}</td> <td>{{ $list->pipe_no}}</td>  <td>{{ $list->status}}</td>  <td>{{ $list->comment}}</td></tr>
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


                <!-- pipe installtion start -->
                @if($PipeInstallation->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Pipe Installations</h5>
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a href="#plot-{{$PipeInstallation->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">{{$PipeInstallation->farmer_plot_uniqueid}}</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="plot-{{$PipeInstallation->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Plot unique Id</td><td>{{$PipeInstallation->farmer_plot_uniqueid}}</td></tr>
                                                <tr><td>Plot Area(Onboarding)</td><td>{{$PipeInstallation->area_in_acers}}</td></tr>
                                                <tr><td>Plot Area(Google map)</td><td>{{$PipeInstallation->plot_area}}</td></tr>
                                                <tr><td>Percentage Error(%)</td><td>{{ round($percent_error, 2) }} %</td></tr>
                                                <tr><td>No. of pipes Installed</td><td>{{$PipeInstallation->installed_pipe}}</td></tr>
                                                @if($PipesLocation)
                                                    @foreach($PipesLocation as $pipe)
                                                      <tr><td>Pipe 1 Distance</td>
                                                        <td>
                                                          <div class="plot-gallery d-flex float-left">
                                                              <a class="btn btn-sm p-0 popup-gallery" href="{{$pipe->images}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                          </div>
                                                          <div class="d-inline float-right mt-2">{{$pipe->distance }}M</div>
                                                        </td>
                                                      </tr>
                                                      <tr>
                                                          <td>Date & Time of Installations</td>
                                                          <td>{{$pipe->date }}, {{$pipe->time }}</td>
                                                      </tr>
                                                    @endforeach
                                                @endif
                                                {{--<tr><td>Pipe 2 Distance</td><td>{{$PipeInstallation->no_pipe_req}} M</td></tr>
                                                <tr><td>Date & Time of Installations</td><td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $PipeInstallation->date_time)->format('d-m-Y')}}</td></tr> --}}
                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Name of Surveyor</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$PipeInstallation->surveyor_id)}}">{{$PipeInstallation->surveyor_name}}</a></td></tr>
                                                @else
                                                <tr><td>Name of Surveyor</td><td>{{$PipeInstallation->surveyor_name}}</td></tr>
                                                @endif
                                            </tbody>
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
              <!-- pipe image -->
                @if($PipesLocation)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Pipe Photos</h5>
                            <!-- All plot pipe images -->
                            <div id="plotPipeImg" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach($PipesLocation as $pipe)
                                    <li data-target="#plotPipeImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{$pipe->images}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($PipesLocation as $pipeImage)
                                    <div class="carousel-item plotPipeImg  {{$loop->first?'active':''}}">
                                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                            <a href="{{$pipeImage->images}}" class="pipeImgclick" data-caption="Plot no. {{$pipeImage->pipe_no}}<br><em class='text-muted'>Pipe Image</em>" data-width="1200" data-height="900" itemprop="contentUrl">
                                              <img class="d-block w-100" height="350" src="{{$pipeImage->images}}" itemprop="thumbnail" alt="plot image">
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


<!-- pipe approve module -->
<div class="modal fade" id="PipeApprove">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PipeInstallation Approve</h5>
                <button type="button" class="close" data-dismiss="modal" style="z-index:99999;" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>

            </div>
            <div class="modal-body" style="margin-top: -53px;">
                <div class="container">
                  <div class="row">
                    <div class="col">

                    </div>
                  </div>
                  @foreach($PipesLocation as $pipeImage)
                  <div class="row">
                    <div class="col mt-2">
                      <input type="checkbox"  id="approvepipe_no"
                      name="approvepipe_no" value="" >
                      <label  title="{{ $pipeImage->status == 'Rejected' ? 'Rejected' : ''}}{{ $pipeImage->status == 'Approved' ? 'Approved' : ''}}{{ $pipeImage->status == 'Pending' ? 'Pending' : ''}}"
                          for="pipe_no"
                          style="margin-right: 11px;">Plot no {{$pipeImage->pipe_no}}</label>&nbsp;
                      <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                            <textarea 
                                      class="form-control" id="approve_comment{{$pipeImage->pipe_no}}"
                                       name="approve_comment" rows="3" cols="50"></textarea>
                        </div><br>&nbsp;
                    </div>
                    <div class="col">
                      {{--@if($PipeInstallation->status == 'Approved')
                        <h5>L-1 validator</h5>
                         {{$pipeImage->UserApprovedRejected->name??''}}   / {{ Carbon\Carbon::parse($plot->appr_timestamp)->toDayDateTimeString() }} <br>
                            @if(Auth::user()->hasRole('SuperAdmin'))
                              <a  target="_blank"  href="{{Route('admin.validator.edit',$pipeImage->UserApprovedRejected->id??'')}}">{{$pipeImage->UserApprovedRejected->email??""}}</a>
                            @else
                            {{$pipeImage->UserApprovedRejected->email??''}}
                            @endif
                        @endif --}}
                    </div>
                  </div><hr>
                   @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button"  {{ $PipeInstallation->status =='Rejected' ? 'Disabled' :'' }}
                class="btn btn-primary SubmitApproval" Disabled>Save <i class="fa fa-spinner fa-spin Aspinner d-none"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- end pipe approve module -->
<!-- reject module start here -->
<div class="modal fade" id="Finalreject_remark">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pipeinstallation Reject</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
              <div class="container">
                <div class="row">
                  <div class="col">
                  </div>
                </div>
                  @foreach($PipesLocation as $pipeImage)
                          <div class="row">
                            <div class="col">
                                <label for="pipeno" style="margin-right: 11px;">Pipe no {{$pipeImage->pipe_no}}</label>&nbsp;
                                  <input type="checkbox"  id="pipeno{{$pipeImage->pipe_no}}" 
                                  name="pipeno" value="{{$pipeImage->pipe_no }}">&nbsp;
                                  
                            </div>
                            <div class="col">
                                 <select id="reasons{{$pipeImage->pipe_no}}" {{$pipeImage->status == 'Rejected' ? 'Disabled' : ""}}
                                            data-pipe_no="{{$pipeImage->pipe_no}}"
                                            name="reasons" class="form-control select2">
                                     <option value="">Select Reasons</option>
                                      @foreach($reject_module as $list)
                                        <option value="{{$list->id}}" >{{$list->reasons}}</option>
                                      @endforeach
                                      {{-- $pipeImage->reject_validation_detail->reject_reason_id??"" == $list->id ? 'Selected' :'' --}}
                                 </select>
                            </div>
                            <div style="margin: 0px 0px 0px 17px;">
                                <label for="reject_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                                <textarea class="form-control" id="reject_comment{{$pipeImage->pipe_no}}"
                                           name="reject_comment" rows="3" cols="50" {{$pipeImage->status == 'Rejected' ? 'Disabled' : ""}}></textarea>
                                           {{-- {{$pipeImage->reject_validation_detail->comment??""}} --}}
                            </div><br>&nbsp;
                          </div>

                          <div class="row">
                                <div class="col mt-2">
                                @if($pipeImage->reject_validation_detail)    
                                 @if($pipeImage->status == 'Rejected')
                                        <h5>L-1 validator</h5>
                                      {{$pipeImage->reject_validation_detail->ValidatorUserDetail->name??''}} {{ $pipeImage->reject_validation_detail->timestamp ? ' / '.Carbon\Carbon::parse($plot->finalreject_timestamp)->toDayDateTimeString() : '' }}<br>
                                      @if(Auth::user()->hasRole('SuperAdmin'))
                                        <a  target="_blank"  href="{{Route('admin.validator.edit',$pipeImage->reject_validation_detail->ValidatorUserDetail->id??'')}}">{{$pipeImage->reject_validation_detail->ValidatorUserDetail->email??""}}</a>
                                      @else
                                      {{$pipeImage->reject_validation_detail->ValidatorUserDetail->email??''}}
                                      @endif
                                  @endif
                                @endif
                                </div>
                                <div class="col">
                                  <button {{ $pipeImage->status == 'Rejected' ? 'disabled' : ''}} {{ $PipeInstallation->status =='Approved' ? 'Disabled' :'' }}
                                           type="button" class="btn btn-primary PipeReject float-right"
                                           data-reject_pipe_no="{{$pipeImage->pipe_no}}" style="margin-top: 5px;">Reject pipe no {{$pipeImage->pipe_no}}
                                           <i id="Rspinner{{$pipeImage->pipe_no}}" class="fa fa-spinner fa-spin Rspinner d-none"></i></button>
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
<!-- reject module end here -->

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

     /* Below code is for Updating new lat & long from a surveys*/
        //google.maps.event.addListener(polygon, "dragend", getPolygonCoords);
        google.maps.event.addListener(path.getPath(), "insert_at", getPolygonCoords);
        //google.maps.event.addListener(polygon.getPath(), "remove_at", getPolygonCoords);
        google.maps.event.addListener(path.getPath(), "set_at", getPolygonCoords);

        function getPolygonCoords(){
            var coordinates_poly = path.getPath().getArray();
            var newCoordinates_poly = [];
            var sum=0;
            for (var i = 0; i < coordinates_poly.length; i++){
                lat_poly = coordinates_poly[i].lat();
                lng_poly = coordinates_poly[i].lng();
                latlng_poly = [lat_poly, lng_poly];
                newCoordinates_poly.push(latlng_poly);
            }
            
            // for(var i=0,l=newCoordinates_poly.length-1;i<l;i++){
            //     sum+=(newCoordinates_poly[i][0]*newCoordinates_poly[i+1][1]-newCoordinates_poly[i+1][0]*newCoordinates_poly[i][1]);
            // }

     

            console.log(newCoordinates_poly,'b4');

            // console.log("Area is", google.maps.geometry.spherical.computeArea(newCoordinates_poly));

            var str_coordinates_poly = JSON.stringify(newCoordinates_poly);

            console.log(str_coordinates_poly, 'after');

            if (str_coordinates_poly !== null) {
                    var farmer_plot_uniqueid =    "{{$plot->farmer_plot_uniqueid}}";
                    $('.UpdtePoly').click(function(){
                    event.preventDefault();
                    Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, Update polygon it!'
                            }).then((result) => {
                            if (result.value == 1) {
                                      $.ajax({
                                        type:"post",
                                        url:"{{ url('l1/pipe/polygon/update')}}/" + farmer_plot_uniqueid,
                                        data:{_method:'post',_token:'{!! csrf_token() !!}', updatedpolygon:str_coordinates_poly, farmer_plot_uniqueid:farmer_plot_uniqueid},
                                        success:function(data){                                            
                                            toastr.success("", data.message, {
                                                timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                                                progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                                                showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                                            });
                                            location.reload();
                                        },
                                        error:function(xhr, status, error) {
                            				var data = jqXHR.responseJSON.farmer;
                                            toastr.error("", "Something went wrong", {
                                                positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                                                debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
                                                hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                                                tapToDismiss: !1
                                            })
                            			}
                           });
                    		
                        }else{//if end of confirmation
                    }
                    });//swal end
                });
            }
        }//getPolygonCoords end
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
                return $.get('{!! url('l1/pipeinstallation/search/Rejected') !!}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_plot_uniqueid,
                            value: str.surveyor_name,
                            status:str.status
                        });
                    });
                    return process(matches);
                });
            },
            templates: {
                suggestion: function(data) {
                    return '<div><a href="{{ url('l1/rejected/pipeinstallation/plot')}}/'+data.farmer_uniqueId+'"><strong>' + data.farmer_uniqueId + '</strong> - ' + data.status + '</a></div>';
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



$("#approvepipe_no").click(function() {
    console.log('csc');
    $(".SubmitApproval").prop('disabled', false);
});

$(".SubmitApproval").click(function() {

    // $('.Aspinner').removeClass('d-none');
    var pipes = [];
    $.each($("input[name='approvepipe_no']:checked"), function(){
        var ApproveComment  = $('#approve_comment'+$(this).val()).val();
        pipes.push({'pipe_no' : $(this).val(), 'ApproveComment' :ApproveComment});
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
                  url:"{{url('l1/pipeinstallation/status/')}}/"+'approve/{{$plot->farmer_plot_uniqueid}}',
                  data: {_token:'{{csrf_token()}}',method:'post',pipes:pipes},
                  success:function(data){
                    $('.Aspinner').addClass('d-none');
                    $(".SubmitApproval").prop('disabled', false);
                    $('.EditBtn').addClass('d-none');
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
          }else{
             $('.Aspinner').addClass('d-none');
             $(".SubmitApproval").prop('disabled', false);
          }//if end of confirmation
        })//swal end
});



$(".PipeReject").click(function() {

    var pipeno = $(this).attr("data-reject_pipe_no");
    var reasons = $('#reasons'+pipeno+' option:selected').val();
    var rejectcomment = $('#reject_comment'+pipeno).val();


    


    $(".PipeReject").prop('disablesd', true);


    $('#Rspinner'+pipeno).removeClass('d-none');


    if(!reasons.length > 0){
        $('#Rspinner'+plotno).addClass('d-none');
        $(".PipeReject").prop('disabled', false);
        return false;
    }
    if(!$('#pipeno' + pipeno).is(":checked")){

        $('#Rspinner'+pipeno).addClass('d-none');
        $(".PipeReject").prop('disabled', false);
        return false;
    }

    // console.log(pipeno, reasons, rejectcomment,'dsddfdfd');
    // return false;

    
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
                  url:"{{url('l1/pipeinstallation/status/')}}/"+'reject'+'/'+'{{$plot->farmer_plot_uniqueid}}',
                  data: {_token:'{{csrf_token()}}',method:'post',pipeno:pipeno,reasons:reasons,rejectcomment:rejectcomment,},
                  success:function(data){
                      $('#Rspinner'+pipeno).addClass('d-none');
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
                      $(".PipeReject").prop('disabled', false);
                      $('#Rspinner'+pipeno).addClass('d-none');
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
          }else{
              $(".PipeReject").prop('disabled', false);
              $('#Rspinner'+plotno).addClass('d-none');
          }//if end of confirmation
        })//swal end
});

</script>
@stop
