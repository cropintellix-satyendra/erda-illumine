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
              $prev=\App\Models\FarmerBenefit::select('id','farmer_uniqueId')->where('status','Approved')->where('l2_status','Pending')->where('id','<',$benefit_data_detail->id)->orderBy('id','desc')
              ->whereHas('farmerapproved',function($q){
                if(auth()->user()->hasRole('Viewer')){
                                $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                                $q->whereIn('state_id',explode(',',$VendorLocation->state));
                                if(!empty($VendorLocation->district)){
                                   $q->whereIn('district_id',explode(',',$VendorLocation->district));
                                }
                                if(!empty($VendorLocation->taluka)){
                                   $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                                }
                                return $q;
                            }

              return $q;
              })->first()??'';
              $next=\App\Models\FarmerBenefit::select('id','farmer_uniqueId')->where('status','Approved')->where('l2_status','Pending')->where('id','>',$benefit_data_detail->id)->orderBy('id','asc')
              ->whereHas('farmerapproved',function($q){
                if(auth()->user()->hasRole('Viewer')){
                        $VendorLocation = DB::table('viewer_locations')->where('user_id',auth()->user()->id)->first();
                        $q->whereIn('state_id',explode(',',$VendorLocation->state));
                        if(!empty($VendorLocation->district)){
                            $q->whereIn('district_id',explode(',',$VendorLocation->district));
                        }
                        if(!empty($VendorLocation->taluka)){
                            $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                        }
                        return $q;
                    }

              return $q;
              })->first()??'';
          @endphp
          @if($prev)
          <a style="color: red;" href="{{ url('').'/'.$rolename.'/view/l2/pending/benefit/plot/'.$prev->farmer_uniqueId}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a>          
          @endif
          @if($next)
          <a style="color: red;" href="{{ url('').'/'.$rolename.'/view/l2/pending/benefit/plot/'.$next->farmer_uniqueId}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a> 
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
                                                <td>Farmer Unique Id</td><td>{{$plot->farmer_uniqueId	}}</td>
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
                                    @include('farmer_detail_extends.farmer_details')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            {{-- <div class="card-body">
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
                                                    <a class="btn btn-sm p-0 popup-gallery" href="{{Storage::disk('s3')->url($items->path)}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
                                                    @empty
                                                    @endforelse
                                                    </div>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>  --}}
                            <div class="card-footer p-0">
                                <div class="row">
                                    @can('carbon download')
                                    <div class="col-6">
                                        <a class="btn text-success btn-sm" href="{{url('admin/farmers/approved/download/'.$plot->farmer_uniqueId.'/'.'CARBON'.'/'.$plot->plot_no)}}"><i class="fa fa-download" aria-hidden="true"></i> Carbon Consent</a>
                                    </div>
                                    @endcan
                                    @can('Download Excel')
                                    <div class="col-6">
                                        <a class="btn text-success btn-sm" href="{{url('l2/download/benefit/'.'Individuals/'.$plot->farmer_uniqueId.'/'.'Pending')}}"><i class="fa fa-download" aria-hidden="true"></i> Download Excel</a>
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
                              <a style="width: 30%;" class="active btn btn-status{{$plot->onboarding_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>
                              <a style="width: 26%;" class="CropDataShow btn btn-status{{$plot->cropdata_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" class="btn btn-status{{$check_pipedata ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Polygon</span></a>
                            </div>
                            <div class="row mb-3">
                                <a style="width: 31%;" class="btn btn-status{{$check_pipedata ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$plot->awd_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$plot->benefit_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              {{-- <a style="width: 30%;" class="btn btn-status{{$plot->other_form ? '-done' : ' disabled'}} m-b-0"><span class="btn-txt">Others</span></a> --}}
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
                                                    <tr><td>level</td>   <td>Status</td>  <td>Comment</td></tr>
                                                    @foreach($validation_list  as $list)                                                      
                                                       <tr><td>{{$list->level}}</td>  <td>{{ $list->status}}</td>  <td>{{ $list->comment}}</td></tr>
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
                <!--cropdata end-->
                @if($benefit_data_detail->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Farmer Benefits</h5>
                            <div class="tab-content">
                                <div id="benefit" class="">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr><td>Farmer Unique ID</td><td>{{$benefit_data_detail->farmer_uniqueId}}</td></tr>
                                                <tr><td>Total Area in Acres</td><td>{{$benefit_data_detail->total_plot_area}}</td></tr>
                                                <tr><td>Season</td><td>{{$benefit_data_detail->seasons}}</td></tr>
                                                <tr><td>Type of Benefit</td><td>{{$benefit_data_detail->benefit}}</td></tr>

                                                @if(Auth::user()->hasRole('SuperAdmin'))
                                                <tr><td>Surveyor name</td><td><a target="_blank" href="{{Route('admin.users.edit',$benefit_data_detail->surveyor_id)}}">{{$benefit_data_detail->surveyor_name}}</a></td></tr>
                                                @else
                                                 <tr><td>Surveyor name</td><td>{{$benefit_data_detail->surveyor_name}}</td></tr>
                                                @endif
                                                <tr><td>Survey Date/Time</td><td>{{ $benefit_data_detail->created_at->toDayDateTimeString() }}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                {{--@if($plot->ApprvFarmerPlotImages()->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Plot Photos</h5>
                            <!-- All plot images -->
                            <div id="PlotImg" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach($plot->ApprvFarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                    <li data-target="#PlotImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{Storage::disk('s3')->url($items->path)}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($plot->ApprvFarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                    <div class="carousel-item plotImg  {{$loop->first?'active':''}}">
                                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                            <a href="{{Storage::disk('s3')->url($items->path)}}" class="plotImgclick" data-caption="Plot no. {{$items->plot_no}}<br><em class='text-muted'>Plot Image</em>" data-width="1200" data-height="900" itemprop="contentUrl">
                                              <img class="d-block w-100" height="350" src="{{Storage::disk('s3')->url($items->path)}}" itemprop="thumbnail" alt="plot image">
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
                @endif --}}
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
            </div>
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
                      {{ $benefit_data_detail->l2_status == 'Rejected' ? 'disabled' : ''}}
                      {{ $benefit_data_detail->l2_status == 'Approved' ? 'checked disabled' : ''}}
                      name="onboarding" value="{{$benefit_data_detail->farmer_uniqueId}}" {{$benefit_data_detail->l2_status == 'Approved' ?' ':''}}>
                      <label title="{{ $benefit_data_detail->l2_status == 'Rejected' ? 'Rejected' : ''}}{{ $benefit_data_detail->l2_status == 'Approved' ? 'Approved' : ''}}{{ $benefit_data_detail->l2_status == 'Pending' ? 'Pending' : ''}}"
                          for="onboarding"
                          style="margin-right: 11px;">Unique Id:.{{$benefit_data_detail->farmer_uniqueId}}</label>&nbsp;
                      <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                            <textarea {{ $benefit_data_detail->l2_status == 'Approved' ? 'readonly' : ''}}  {{ $benefit_data_detail->l2_status == 'Rejected' ? 'disabled' : ''}}
                                      class="form-control" id="approve_comment{{$benefit_data_detail->farmer_uniqueId}}"
                                       name="approve_comment" rows="3" cols="50">{{$benefit_data_detail->approve_comment}}</textarea>
                        </div><br>&nbsp;
                    </div>
                    <div class="col">
                      
                    </div>
                  </div><hr>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" {{ $benefit_data_detail->l2_status == 'Approved' ? 'disabled' : ''}}
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
                return $.get("{!! url('') !!}"+'/{{$rolename}}/view/l2/benefit/search/'+'{{$benefit_data_detail->l2_status}}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_uniqueId	,
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
                    var rolename = '{{$rolename}}';
                    return '<div><a href="{{ url("/")}}/'+rolename+'/view/l2/pending/benefit/plot/'+data.farmer_uniqueId+'"><strong>' + data.farmer_uniqueId	 + '</strong> - ' + data.status + '</a></div>';
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

      $('#dt_irrigation_last').val('{{$benefit_data_detail->dt_irrigation_last}}');
      $('#dt_ploughing').val('{{$benefit_data_detail->dt_ploughing}}');
      $('#dt_transplanting').val('{{$benefit_data_detail->dt_transplanting}}');
})($);



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
      url:"{{url('l1/pending/cropdata/update/')}}/"+'{{$benefit_data_detail->farmer_plot_uniqueid}}',
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
    var uniqueids = [];
    $.each($("input[name='onboarding']:checked"), function(){
        var ApproveComment  = $('#approve_comment'+$(this).val()).val();
      uniqueids.push({'uniqueid' : $(this).val(), 'ApproveComment' :ApproveComment});
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
                  url:"{{url('l2/benefit/status/')}}/"+'{{$benefit_data_detail->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',uniqueids:uniqueids},
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
