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
              $prev=\App\Models\FarmerCropdata::select('id','farmer_plot_uniqueid','plot_no')->where('status','Approved')->where('l2_status','Pending')->where('id','<',$crop_data_detail->id)->orderBy('id','desc')
              ->whereHas('farmerapproved',function($q){
                if(auth()->user()->hasRole('L-2-Validator')){
                    $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$VendorLocation->state));
                    if(!empty($VendorLocation->district)){
                        $q->whereIn('district_id',explode(',',$VendorLocation->district));
                    }
                    if(!empty($VendorLocation->taluka)){
                        $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                    }
                }
            return $q;
            })
              ->first()??'';
              $next=\App\Models\FarmerCropdata::select('id','farmer_plot_uniqueid','plot_no')->where('status','Approved')->where('l2_status','Pending')->where('id','>',$crop_data_detail->id)->orderBy('id','asc')
              ->whereHas('farmerapproved',function($q){
                if(auth()->user()->hasRole('L-2-Validator')){
                    $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                    $q->whereIn('state_id',explode(',',$VendorLocation->state));
                    if(!empty($VendorLocation->district)){
                        $q->whereIn('district_id',explode(',',$VendorLocation->district));
                    }
                    if(!empty($VendorLocation->taluka)){
                        $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                    }
                }
            return $q;
            })
              ->first()??'';
          @endphp
          @if($prev)
          <a style="color: red;" href="{{ url('l2/pending/cropdata/plot').'/'.$prev->farmer_plot_uniqueid.'/'.$prev->plot_no}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a>
          @endif
          @if($next)
          <a style="color: red;" href="{{ url('l2/pending/cropdata/plot').'/'.$next->farmer_plot_uniqueid.'/'.$next->plot_no}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
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
                                    @include('farmer_detail_extends.farmer_info')
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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                @include('farmer_detail_extends.plot_info')
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                @include('farmer_detail_extends.location_info')
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                @include('farmer_detail_extends.executive_details')
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
                                       @if($plot->l2_status_onboarding == 'Approved')
                                        disabled
                                      @elseif($plot->l2_status_onboarding == 'Rejected')
                                        disabled
                                      @else
                                      @endif
                                    >EDIT</a>
                                <!-- end button end -->
                                <button style="width: 26%;"
                                  data-toggle="modal" data-target="#ApproveModal" 
                                  class="btn btn-success ApproveBtn m-b-0 mr-3 {{ $crop_data_detail->l2_status  == 'Approved' ? 'disabled' : ''}}"
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
                        <h5 class="card-title bg-primary text-white p-3 text-center">Crop Data @can('L2-CropData-Edit')<button class="btn btn-success float-right EditCropData {{$crop_data_detail->l2_status == 'Approved' ? 'd-none' : ''}}" style="margin: -16px -14px 0px 0px;width: 84px;height: 50px;">Edit</button>@endcan</h5>
                            <div class="row mb-3">
                                <div class="col">
                                    <ul class="nav nav-pills" style="display: flex; align-items: center;">
                                        @foreach($plot->PlotCropData as $Croplotbtn)
                                        <li class="nav-item"><a href="#plot-{{$Croplotbtn->plot_no}}" class="nav-link active" data-toggle="tab" aria-expanded="false">Plot {{$Croplotbtn->plot_no}}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col">
                                </div>
                                <div class="col-4">
                                    <a href=""><span class="btn btn-warning" style="font-size: 11px; padding: 13px 0px;">Area Of all Plots(Acres)</span></a>
                                </div>

                                <div class="col-2" style="margin-top: 1px;margin-left: -34px;">
                                    <a href=""><span class="btn btn-warning" style="font-size: 17px;padding: 8px 11px;">{{$plot->total_plot_area??'0'}}</span></a>
                                </div>

                            </div>
                            <div class="tab-content">
                                @foreach($cropdata as $Croplot)
                                <div id="plot-{{$Croplot->plot_no}}" class="tab-pane active pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Plot Id</th>
                                                    <th colspan="2">{{$Croplot->farmer_plot_uniqueid}}</th>
                                                </tr>
                                                <tr>
                                                    <th>Plot Area In Bigha</th>
                                                    <th colspan="2">{{$Croplot->farmerplot_details->area_in_other}}</th>

                                                </tr>
                                                <tr>
                                                    <th>Plot Area In Acres</th>
                                                    <th colspan="2">{{$Croplot->farmerplot_details->area_in_acers}}</th>

                                                </tr>
                                                <tr>
                                                    <th>Dates of Transplanting</th>
                                                    <th colspan="2">
                                                        <input type="date" class="datepicker d-none dt_transplanting" id="dt_transplanting">
                                                        <span id="dt_transplanting_span">{{  \Carbon\Carbon::createFromFormat('d/m/Y', $Croplot->dt_transplanting)->format('d-m-Y') }}</span>
                                                        <!-- {{ \Carbon\Carbon::createFromFormat('d/m/Y', $Croplot->dt_transplanting)->format('d-m-Y') }} -->
                                                    </th>

                                                </tr>
                                                <tr>
                                                    <th>Dates of Nursery</th>
                                                    <th colspan="2">
                                                        <input type="date" class="datepicker d-none nursery"  id="nursery">
                                                        
                                                        <span id="nursery_span">{{  \Carbon\Carbon::createFromFormat('d/m/Y', $Croplot->PlotCropDetails->nursery)->format('d-m-Y') }}</span>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th>Date of Land Preparation</th>
                                                    <th colspan="2">
                                                        <input type="date" class="datepicker d-none dt_ploughing" id="dt_ploughing" >
                                                        <span id="dt_ploughing_span">{{ $Croplot->dt_ploughing}}</span>
                                                        <!-- {{ \Carbon\Carbon::createFromFormat('d/m/Y', $Croplot->dt_ploughing)->format('d-m-Y')}} -->
                                                    </th>

                                                </tr>
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <td>Season & Variety</td>
                                                    <td>last Year</td>
                                                    <td>Current Year</td>
                                                </tr>

                                                <tr>
                                                    <td>Crop Season</td>
                                                    <td>
                                                        <input type="text" class="d-none crop_season_lastyrs" value="{{$Croplot->PlotCropDetails->crop_season_lastyrs}}" id="crop_season_lastyrs" >
                                                        <span id="crop_season_lastyrs_span">{{$Croplot->PlotCropDetails->crop_season_lastyrs}}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none crop_season_currentyrs" value="{{$Croplot->PlotCropDetails->crop_season_currentyrs}}" id="crop_season_currentyrs" >
                                                        <span id="crop_season_currentyrs_span">{{$Croplot->PlotCropDetails->crop_season_lastyrs}}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Crop Variety</td>
                                                    <td>
                                                        <input type="text" class="d-none crop_variety_lastyrs" value="{{$Croplot->PlotCropDetails->crop_variety_lastyrs}}" id="crop_variety_lastyrs" >
                                                        <span id="crop_variety_lastyrs_span">{{$Croplot->PlotCropDetails->crop_variety_lastyrs}}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none crop_variety_currentyrs" value="{{$Croplot->PlotCropDetails->crop_variety_currentyrs}}" id="crop_variety_currentyrs" >
                                                        <span id="crop_variety_currentyrs_span">{{$Croplot->PlotCropDetails->crop_variety_currentyrs}}</span>
                                                    </td>
                                                </tr> 
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <th>Fertilizer Management</td>
                                                    <th>Last Year</td>
                                                    <th>Current Year</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_1_name" value="{{$Croplot->PlotCropDetails->fertilizer_1_name}}" id="fertilizer_1_name" >
                                                        <span id="fertilizer_1_name_span">{{$Croplot->PlotCropDetails->fertilizer_1_name}} (Kg/Ha)</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_1_lastyrs" value="{{$Croplot->PlotCropDetails->fertilizer_1_lastyrs}}" id="fertilizer_1_lastyrs" >
                                                        <span id="fertilizer_1_lastyrs_span">{{$Croplot->PlotCropDetails->fertilizer_1_lastyrs}}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_1_currentyrs" value="{{$Croplot->PlotCropDetails->fertilizer_1_currentyrs}}" id="fertilizer_1_currentyrs" >
                                                        <span id="fertilizer_1_currentyrs_span">{{$Croplot->PlotCropDetails->fertilizer_1_currentyrs}}</span>
                                                    </td>


                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_2_name" value="{{$Croplot->PlotCropDetails->fertilizer_2_name}}" id="fertilizer_2_name" >
                                                        <span id="fertilizer_2_name_span">{{$Croplot->PlotCropDetails->fertilizer_2_name}}(Kg/Ha)</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_2_lastyrs" value="{{$Croplot->PlotCropDetails->fertilizer_2_lastyrs}}" id="fertilizer_2_lastyrs" >
                                                        <span id="fertilizer_2_lastyrs_span">{{$Croplot->PlotCropDetails->fertilizer_2_lastyrs}}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_2_currentyrs" value="{{$Croplot->PlotCropDetails->fertilizer_2_currentyrs}}" id="fertilizer_2_currentyrs" >
                                                        <span id="fertilizer_2_currentyrs_span">{{$Croplot->PlotCropDetails->fertilizer_2_currentyrs}}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_3_name" value="{{$Croplot->PlotCropDetails->fertilizer_3_name}}" id="fertilizer_3_name" >
                                                        <span id="fertilizer_3_name_span">{{$Croplot->PlotCropDetails->fertilizer_3_name}}(Kg/Ha)</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_3_lastyrs" value="{{$Croplot->PlotCropDetails->fertilizer_3_lastyrs}}" id="fertilizer_3_lastyrs" >
                                                        <span id="fertilizer_3_lastyrs_span">{{$Croplot->PlotCropDetails->fertilizer_3_lastyrs}}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none fertilizer_3_currentyrs" value="{{$Croplot->PlotCropDetails->fertilizer_3_currentyrs}}" id="fertilizer_3_currentyrs" >
                                                        <span id="fertilizer_3_currentyrs_span">{{$Croplot->PlotCropDetails->fertilizer_3_currentyrs}}</span>
                                                    </td>
                                                </tr>
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <td>Water Management</td>
                                                    <td>Last Year</td>
                                                    <td>Current Year</td>
                                                </tr>
                                                <tr>
                                                    <td>Irrigation</td>
                                                    <td>
                                                        <input type="text" class="d-none water_mng_lastyrs" value="{{$Croplot->PlotCropDetails->water_mng_lastyrs}}" id="water_mng_lastyrs" >
                                                        <span id="water_mng_lastyrs_span">{{$Croplot->PlotCropDetails->water_mng_lastyrs}}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="d-none water_mng_currentyrs" value="{{$Croplot->PlotCropDetails->water_mng_currentyrs}}" id="water_mng_currentyrs" >
                                                        <span id="water_mng_currentyrs_span">{{$Croplot->PlotCropDetails->water_mng_currentyrs}}</span>
                                                    </td>
                                                </tr>
                                                <tr style="background-color: rgb(17, 17, 87); color: white;">
                                                    <td>Yield Information</td>
                                                    <td>Last Year</td>
                                                    <td>Current Year</td>
                                                </tr>
                                                <tr>
                                                    <td>Yield(Kg/Ha)</td>
                                                    <td>
                                                        <input type="text" class="d-none yeild_lastyrs" value="{{$Croplot->PlotCropDetails->yeild_lastyrs}}" id="yeild_lastyrs" >
                                                        <span id="yeild_lastyrs_span">{{$Croplot->PlotCropDetails->yeild_lastyrs}}</span>
                                                    </td>
                                                    <td>
                                                    <input type="text" class="d-none yeild_currentyrs" value="{{$Croplot->PlotCropDetails->yeild_currentyrs}}" id="yeild_currentyrs" >
                                                        <span id="yeild_currentyrs_span">{{$Croplot->PlotCropDetails->yeild_currentyrs}}</span>
                                                    </td>
                                                </tr>

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
                            @include('farmer_detail_extends.farmer_benefits')
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
                      {{ $crop_data_detail->l2_status == 'Rejected' ? 'disabled' : ''}}
                      {{ $crop_data_detail->l2_status == 'Approved' ? 'checked disabled' : ''}}
                      name="onboarding" value="{{$crop_data_detail->plot_no}}" {{$crop_data_detail->l2_status == 'Approved' ?' ':''}}>
                      <label title="{{ $crop_data_detail->l2_status == 'Rejected' ? 'Rejected' : ''}}{{ $crop_data_detail->l2_status == 'Approved' ? 'Approved' : ''}}{{ $crop_data_detail->l2_status == 'Pending' ? 'Pending' : ''}}"
                          for="onboarding"
                          style="margin-right: 11px;">Plot no {{$crop_data_detail->plot_no}}</label>&nbsp;
                      <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                            <textarea {{ $crop_data_detail->l2_status == 'Approved' ? 'readonly' : ''}}  {{ $crop_data_detail->l2_status == 'Rejected' ? 'disabled' : ''}}
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
                <button type="button" {{ $crop_data_detail->l2_status == 'Approved' ? 'disabled' : ''}}
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
                return $.get('{!! url('l2/cropdata/search/Pending') !!}', { query: query }, function (data) {
                    var matches = [];
                    $.each(data, function(i, str) {
                        matches.push({
                            id:str.id,
                            farmer_uniqueId:str.farmer_plot_uniqueid,
                            farmer_plot_uniqueid:str.farmer_plot_uniqueid,
                            value: str.surveyor_name,
                            status:str.l2_status,
                            plot_no:str.plot_no
                        });
                    });
                    return process(matches);
                });
            },
            templates: {
                suggestion: function(data) {
                    return '<div><a href="{{ url('l2/pending/cropdata/plot')}}/'+data.farmer_plot_uniqueid+'/'+data.plot_no+'"><strong>' + data.farmer_plot_uniqueid + '</strong> - ' + data.status + '</a></div>';
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
      $('#nursery').val('{{$crop_data_detail->PlotCropDetails->nursery}}');



})($);

$('.EditCropData').click(function () {
  $('#CropData input[type=text]').removeAttr('readonly');
  $('.UpdateCropData').removeClass('d-none');
  $('.CancelCropData').removeClass('d-none');
  $("#season").removeAttr('disabled');

    $("#dt_transplanting_span").addClass('d-none');$(".dt_transplanting").removeClass('d-none');

    $("#nursery_span").addClass('d-none');$(".nursery").removeClass('d-none');

    $("#dt_ploughing_span").addClass('d-none');$(".dt_ploughing").removeClass('d-none');

    $("#crop_season_lastyrs_span").addClass('d-none');$(".crop_season_lastyrs").removeClass('d-none');

    $("#crop_season_currentyrs_span").addClass('d-none');$(".crop_season_currentyrs").removeClass('d-none');
    $("#crop_variety_lastyrs_span").addClass('d-none');$(".crop_variety_lastyrs").removeClass('d-none');
    $("#crop_variety_currentyrs_span").addClass('d-none');$(".crop_variety_currentyrs").removeClass('d-none');
    $("#fertilizer_1_name_span").addClass('d-none');$(".fertilizer_1_name").removeClass('d-none');
    $("#fertilizer_1_lastyrs_span").addClass('d-none');$(".fertilizer_1_lastyrs").removeClass('d-none');
    $("#fertilizer_1_currentyrs_span").addClass('d-none');$(".fertilizer_1_currentyrs").removeClass('d-none');
    $("#fertilizer_2_name_span").addClass('d-none');$(".fertilizer_2_name").removeClass('d-none');
    $("#fertilizer_2_lastyrs_span").addClass('d-none');$(".fertilizer_2_lastyrs").removeClass('d-none');
    $("#fertilizer_2_currentyrs_span").addClass('d-none');$(".fertilizer_2_currentyrs").removeClass('d-none');
    $("#fertilizer_3_name_span").addClass('d-none');$(".fertilizer_3_name").removeClass('d-none');
    $("#fertilizer_3_lastyrs_span").addClass('d-none');$(".fertilizer_3_lastyrs").removeClass('d-none');
    $("#fertilizer_3_currentyrs_span").addClass('d-none');$(".fertilizer_3_currentyrs").removeClass('d-none');
    $("#water_mng_lastyrs_span").addClass('d-none');$(".water_mng_lastyrs").removeClass('d-none');
    $("#water_mng_currentyrs_span").addClass('d-none');$(".water_mng_currentyrs").removeClass('d-none');
    $("#yeild_lastyrs_span").addClass('d-none');$(".yeild_lastyrs").removeClass('d-none');
    $("#yeild_currentyrs_span").addClass('d-none');$(".yeild_currentyrs").removeClass('d-none');
    
    
    
})

$('.CancelCropData').click(function () {
  $('#CropData input[type=text]').attr('readonly','readonly');
  $("#season").prop("disabled", true);
  $("#crop_variety").prop("disabled", true);
  $('.UpdateCropData').addClass('d-none');
  $('.CancelCropData').addClass('d-none');
  $("#dt_ploughing_span").removeClass('d-none');
  $(".dt_ploughing").addClass('d-none');

  $("#nursery_span").removeClass('d-none');$(".nursery").addClass('d-none');
  
  $("#dt_transplanting_span").removeClass('d-none');$(".dt_transplanting").addClass('d-none');

  $("#crop_season_lastyrs_span").removeClass('d-none');$(".crop_season_lastyrs").addClass('d-none');
  $("#crop_season_currentyrs_span").removeClass('d-none');$(".crop_season_currentyrs").addClass('d-none');
  $("#crop_variety_lastyrs_span").removeClass('d-none');$(".crop_variety_lastyrs").addClass('d-none');
  $("#crop_variety_currentyrs_span").removeClass('d-none');$(".crop_variety_currentyrs").addClass('d-none');
  $("#fertilizer_1_name_span").removeClass('d-none');$(".fertilizer_1_name").addClass('d-none');
  $("#fertilizer_1_lastyrs_span").removeClass('d-none');$(".fertilizer_1_lastyrs").addClass('d-none');
  $("#fertilizer_1_currentyrs_span").removeClass('d-none');$(".fertilizer_1_currentyrs").addClass('d-none');
  $("#fertilizer_2_name_span").removeClass('d-none');$(".fertilizer_2_name").addClass('d-none');
  $("#fertilizer_2_lastyrs_span").removeClass('d-none');$(".fertilizer_2_lastyrs").addClass('d-none');
  $("#fertilizer_2_currentyrs_span").removeClass('d-none');$(".fertilizer_2_currentyrs").addClass('d-none');
  $("#fertilizer_3_name_span").removeClass('d-none');$(".fertilizer_3_name").addClass('d-none');
  $("#fertilizer_3_lastyrs_span").removeClass('d-none');$(".fertilizer_3_lastyrs").addClass('d-none');
  $("#fertilizer_3_currentyrs_span").removeClass('d-none');$(".fertilizer_3_currentyrs").addClass('d-none');
  $("#water_mng_lastyrs_span").removeClass('d-none');$(".water_mng_lastyrs").addClass('d-none');
  $("#water_mng_currentyrs_span").removeClass('d-none');$(".water_mng_currentyrs").addClass('d-none');
  $("#yeild_lastyrs_span").removeClass('d-none');$(".yeild_lastyrs").addClass('d-none');
  $("#yeild_currentyrs_span").removeClass('d-none');$(".yeild_currentyrs").addClass('d-none');
  
  
  
})

$('.UpdateCropData').click(function(){
    // if(!$('#crop_variety option:selected').val()){
    //    $('.crop_variety_req').removeClass('d-none');
    //    return false;
    // }
    $('.Updatespinner').removeClass('d-none');
    $.ajax({
      type:'post',
      url:"{{url('l2/pending/cropdata/update/')}}/"+'{{$plot->farmer_plot_uniqueid}}',
      data: {_token:'{{csrf_token()}}',method:'post',crop_variety:$('#crop_variety option:selected').val(),
                                                     season:$('#season option:selected').val(),
                                                      dt_irrigation_last:$('#dt_irrigation_last').val(),
                                                      dt_ploughing:$('#dt_ploughing').val(),
                                                      dt_transplanting:$('#dt_transplanting').val(),
                                                      crop_season_lastyrs:$('#crop_season_lastyrs').val(),
                                                      crop_season_currentyrs:$('#crop_season_currentyrs').val(),
                                                      crop_variety_lastyrs:$('#crop_variety_lastyrs').val(),
                                                      crop_variety_currentyrs:$('#crop_variety_currentyrs').val(),
                                                      fertilizer_1_name:$('#fertilizer_1_name').val(),
                                                      fertilizer_1_lastyrs:$('#fertilizer_1_lastyrs').val(),
                                                      fertilizer_1_currentyrs:$('#fertilizer_1_currentyrs').val(),
                                                      fertilizer_2_name:$('#fertilizer_2_name').val(),
                                                      fertilizer_2_lastyrs:$('#fertilizer_2_lastyrs').val(),
                                                      fertilizer_2_currentyrs:$('#fertilizer_2_currentyrs').val(),
                                                      fertilizer_3_name:$('#fertilizer_3_name').val(),
                                                      fertilizer_3_lastyrs:$('#fertilizer_3_lastyrs').val(),
                                                      fertilizer_3_currentyrs:$('#fertilizer_3_currentyrs').val(),
                                                      water_mng_lastyrs:$('#water_mng_lastyrs').val(),
                                                      water_mng_currentyrs:$('#water_mng_currentyrs').val(),
                                                      yeild_lastyrs:$('#yeild_lastyrs').val(),
                                                      yeild_currentyrs:$('#yeild_currentyrs').val(),
                                                      nursery:$('#nursery').val(),
                                                    },
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
    });
});

$('.dt_irrigation_last').change(function() {
    var date = $(this).val();
    var irrigation_date = new Date(date);
    var preparation_date_interval = '{{$date_setting->preparation_date_interval}}';
    var transplantation_date_interval = '{{$date_setting->transplantation_date_interval}}';
    var date_preparation = moment(irrigation_date, "DD-MM-YYYY").add(preparation_date_interval, 'days');
    // console.log(date, preparation_date_interval,  moment(date_preparation).format('YYYY/MM/DD'));
    document.getElementById("dt_ploughing").setAttribute("min", moment(date_preparation).format('YYYY-MM-DD'));

});
$('.dt_ploughing').change(function() {
    var date = $(this).val();
    var land_prep_date = new Date(date);
    var preparation_date_interval = '{{$date_setting->preparation_date_interval}}';
    var transplantation_date_interval = '{{$date_setting->transplantation_date_interval}}';
    var date_transplanting = moment(land_prep_date, "DD-MM-YYYY").add(transplantation_date_interval, 'days');
    document.getElementById("dt_transplanting").setAttribute("min", moment(date_transplanting).format('YYYY-MM-DD'));

    // document.getElementById("dt_transplanting").setAttribute("min", moment(date_transplanting).format('YYYY-MM-DD'));

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
                  url:"{{url('l2/cropdata/status/')}}/"+'{{$crop_data_detail->farmer_plot_uniqueid}}/{{$crop_data_detail->plot_no}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$plot->no_of_plots}}"},
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
