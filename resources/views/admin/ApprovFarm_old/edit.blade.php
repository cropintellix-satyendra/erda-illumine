@extends('layout.default')


@section('content')
<!--Import PhotoSwipe Styles -->
<!-- Import PhotoSwipe Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/photoswipe.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/default-skin/default-skin.css">
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Farmer Edit</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            {{-- <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Farmers</a></li>
            </ol> --}}
            <a style="color: red;" href="{{url()->previous()}}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
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
                                            <th colspan="2" class="text-center">Farmer Info <button class="btn btn-success EditBasicform" style="margin: 0px 1px 1px 187px;padding: 4px 4px 4px 4px;cursor: pointer;">Edit</button></th>
                                        </tr>
                                    </thead>
                                    <tbody id="Basicform">
                                        <tr>
                                            <td>Farmer Unique Id</td>
                                            <td>{{$Farmer->farmer_uniqueId}}</td>
                                        </tr>
                                        <tr>
                                            <td>Farmer Name</td>
                                            <td><input type="text" class="form-control" name="farmer_name" style="" value="{{$Farmer->farmer_name}}" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Mobile Access</td>
                                            <td>
                                                <select id="mobile_access" name="mobile_access" class="form-control select2" disabled>
                                                    <option value="">Select Mobile Access</option>
                                                    <option value="Own Number" {{ 'Own Number' == $Farmer->mobile_access ? 'Selected' :'' }}>Own Number</option>
                                                    <option value="Relatives Number" {{ 'Relatives Number' == $Farmer->mobile_access ? 'Selected' :'' }}>Relatives Number</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Relationship owner</td>
                                            <td>
                                                <select id="mobile_reln_owner" name="mobile_reln_owner" class="form-control select2" disabled>
                                                    <option value="">Select Relationship</option>
                                                    @foreach($Relationshipowner as $relationshipowner)
                                                    <option value="{{$relationshipowner->name}}" {{ $relationshipowner->name == $Farmer->mobile_reln_owner ? 'Selected' :'' }}>{{$relationshipowner->name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mobile</td>
                                            <td><input type="text" class="form-control" name="mobile" value="{{$Farmer->mobile}}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" readonly maxlength="10"></td>
                                        </tr>
                                        <tr>
                                            <td>No. of plots</td>
                                            <td>{{$Farmer->no_of_plots}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button style="padding: 4px 4px 4px 4px;" class="btn btn-success float-right UpdateBasicform d-none">Submit <i class="fa fa-spinner fa-spin Updatespinner d-none"></i></button>
                                <button style="padding: 4px 4px 4px 4px;margin-right: 6px;" class="btn btn-danger float-right CancelBasicform d-none">Cancel</button>
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
                                            <th colspan="6" class="text-center">Plotddd Info <button class="btn btn-success EditPlot" style="margin: 0px 1px 1px 187px;padding: 4px 4px 4px 4px;cursor: pointer;">Edit</Button></th>
                                        </tr>
                                    </thead>
                                    <tbody id="EditPlot">
                                        <tr>
                                            <td>No of Plots</td>
                                            <td colspan="3">{{$Farmer->no_of_plots}}</td>
                                            <td>Area of Plots</td>
                                            <td>{{$Farmer->total_plot_area}}</td>
                                        </tr>
                                        @if($farmerplots->count()>0)
                                        <tr>
                                            <td>Plot No.</td>
                                            <td>Area in Acres</td>
                                            <td colspan="2">Actual Owner</td>
                                            <td>Survey No.</td>
                                            <td>Photos</td>
                                        </tr>
                                        @foreach($farmerplots as $plot)
                                        <tr>
                                            <td>{{$plot->plot_no}}</td>
                                            <td><input type="text" style="width: 70px;" class="form-control area_in_acers" id="area_in_acers{{$plot->plot_no}}" name="area_in_acers" data-plot="{{$plot->plot_no}}" value="{{$plot->area_in_acers}}" readonly oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');">
                                                <br><span class='d-none'id='area_in_acersreq{{$plot->plot_no}}' style="color:red;">Required </span><br><span class='d-none' id="area_acers_base{{$plot->plot_no}}" style="color:red;"> Should be greater than {{$minimumvalues->value}}</span>
                                            </td>
                                            <td colspan="2"><input style="width: 111px;" type="text" class="form-control actual_owner_name" name="actual_owner_name" data-plot="{{$plot->plot_no}}" value="{{$plot->actual_owner_name}}" readonly><br><span class='d-none actual_owner_namereq' style="color:red;">Required</span></td>
                                            <td><input type="text" style="width: 80px;" class="form-control survey_no" name="survey_no" data-plot="{{$plot->plot_no}}" value="{{$plot->survey_no}}" readonly><br><span class='d-none survey_noreq' style="color:red;">Required</span></td>
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
                                <button style="padding: 4px 4px 4px 4px;" class="btn btn-success float-right UpdatePlot d-none">Submit <i class="fa fa-spinner fa-spin UpdatePlotspinner d-none"></i></button>
                                <button style="padding: 4px 4px 4px 4px;margin-right: 6px;" class="btn btn-danger float-right CancelPlot d-none">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- download -->
                <div class="col-12">
                    <div class="row">
                        @can('carbon_download')
                        <div class="col-6">
                            <div class="card">
                                <div class="card-body">
                                    <center><a style="color: red;" href="{{url('admin/farmers/download/'.$plot->farmer_uniqueId.'/'.'CARBON'.'/'.'0')}}"><i class="fa fa-download" aria-hidden="true"></i> Carbon Consent</a></center>
                                </div>
                            </div>
                        </div>
                        @endcan
                        @can('Download Excel')
                        <div class="col-6">
                            <div class="card">
                                <div class="card-body">
                                    <center><a style="color: red;" href="{{url('admin/download/file'.'/?type=onboarding&file=excel&unique='.$plot->farmer_uniqueId)}}"><i class="fa fa-download" aria-hidden="true"></i> Download Excel</a></center>
                                </div>
                            </div>
                        </div>
                        @endcan
                    </div>
                </div>
                <!-- end download -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Location Info <button class="btn btn-success EditLocation" style="margin: 0px 1px 1px 187px;padding: 4px 4px 4px 4px;cursor: pointer;">Edit</button></th>
                                        </tr>
                                    </thead>
                                    <tbody id="EditLocation">
                                        <tr>
                                              <td>State</td>
                                              <td>
                                                  {{$plot->farmer->state}}
                                              <!--<select id="states" name="states" onchange="FetchDistrict(this.value)" class="form-control select2" disabled>-->
                                              <!--  <option value="">States</option>-->
                                              <!--    @if($states)-->
                                              <!--    @foreach($states as $state)-->
                                              <!--    <option value="{!! $state->id !!}" {{ $plot->farmer->state_id == $state->id ?'Selected' :'' }}>{!! $state->name !!}</option>-->
                                              <!--    @endforeach-->
                                              <!--    @endif-->
                                              <!--</select>-->
                                              <!--<br><span class='d-none statesreq' style="color:red;">Required</span>-->
  				                                   </td>
                                            </tr>
                                            <tr>
                                              <td>District</td>
                                              <td>
                                                  {{$plot->farmer->district}}
                                                  <!--<select id="districts" onchange="FetchBlock(this.value)" name="districts" class="form-control select2" disabled>-->
                                                  <!--    <option value="">Districts</option>-->
                                                  <!--    @if($districts)-->
                                                  <!--    @foreach($districts as $district)-->
                                                  <!--    <option value="{!! $district->id !!}" {{ $plot->farmer->district_id == $district->id ?'Selected' :'' }}>{!! $district->district !!}</option>-->
                                                  <!--    @endforeach-->
                                                  <!--    @endif-->
                                                  <!--</select>-->
                                                  <!--<br><span class='d-none districtsreq' style="color:red;">Required</span>-->
                                            </tr>
                                            <tr>
                                              <td>Taluka</td>
                                              <td>
                                                  {{$plot->farmer->taluka}}
                                                  <!--<select id="talukas" onchange="FetchPanchayat(this.value)" name="talukas" class="form-control select2" disabled>-->
                                                  <!--    <option value="">Taluka</option>-->
                                                  <!--    @if($talukas)-->
                                                  <!--    @foreach($talukas as $taluka)-->
                                                  <!--    <option value="{!! $taluka->id !!}" {{ $plot->farmer->taluka_id == $taluka->id ?'Selected' :'' }}>{!! $taluka->taluka !!}</option>-->
                                                  <!--    @endforeach-->
                                                  <!--    @endif-->
                                                  <!--</select>-->
                                                  <!--<br><span class='d-none talukasreq' style="color:red;">Required</span>-->
                                            </tr>
                                        <tr>
                                            <td>Panchayat</td>
                                            <td>
                                                <select id="panchayats" name="panchayats" onchange="FetchVillage(this.value)" class="form-control select2" disabled>
                                                    <option value="">Panchayat</option>
                                                    @if($panchayats)
                                                    @foreach($panchayats as $panchayat)
                                                    <option value="{!! $panchayat->id !!}" {{ $Farmer->panchayat_id == $panchayat->id ?'Selected' :'' }}>{!! $panchayat->panchayat !!}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                <!--<input type="text" name="state" data-plot="{{$plot->plot_no}}" style="background-color:#efeeee;" value="{{$Farmer->panchayat}}" readonly>-->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Village</td>
                                            <td>
                                                <select id="villages" name="villages" class="form-control select2" disabled>
                                                    <option value="">Village</option>
                                                    @if($villages)
                                                    @foreach($villages as $village)
                                                    <option value="{!! $village->id !!}" {{ $Farmer->village_id == $village->id ?'Selected' :'' }}>{!! $village->village !!}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                <!--<input type="text" name="state" data-plot="{{$plot->plot_no}}" style="background-color:#efeeee;" value="{{$Farmer->village}}" readonly>-->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Remarks</td>
                                            <td>{{$Farmer->remarks}}</td>
                                        </tr>
                                        <tr>
                                            <td>Latitude</td>
                                            <td>{{$Farmer->latitude}}</td>
                                        </tr>
                                        <tr>
                                            <td>Logitude</td>
                                            <td>{{$Farmer->longitude}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button style="padding: 4px 4px 4px 4px;" class="btn btn-success float-right UpdateLocation d-none">Submit <i class="fa fa-spinner fa-spin UpdateLocationspinner d-none"></i></button>
                                <button style="padding: 4px 4px 4px 4px;margin-right: 6px;" class="btn btn-danger float-right CancelLocation d-none">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--location end-->
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
                                            <td><a target="_blank" href="{{Route('admin.users.edit',$Farmer->surveyor_id)}}">{{$Farmer->surveyor_name}}</a></td>
                                        </tr>
                                        <tr>
                                            <td>Mobile No</td>
                                            <td>{{$Farmer->surveyor_mobile}}</td>
                                        </tr>
                                        <tr>
                                            <td>Email ID</td>
                                            <td>{{$Farmer->surveyor_email}}</td>
                                        </tr>
                                        <tr>
                                            <td>Date of Survey</td>
                                            <td>{{$Farmer->date_survey}}</td>
                                        </tr>
                                        <tr>
                                            <td>Time of Survey</td>
                                            <td>{{ $Farmer->time_survey }} </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--executive detail-->
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
                                    <a style="width: 30%;" href="{{url('admin/farmers/show').'/'.$Farmer->id.'/'.$Farmer->farmer_uniqueId}}" class="btn btn-info m-b-0 mr-3 EditBtn"
                                        >Show</a>
                                    <!-- end button end -->
                                    {{-- onclick="submitApprove('{{$Farmer->farmer_uniqueId}}')" --}}
                                    <button style="width: 26%;" data-toggle="modal" data-target="#ApproveModal" class="btn btn-success ApproveBtn m-b-0 mr-3" {{-- below code is to disable button if --}} @if(Auth::user()->hasRole('L-1-Validator'))
                                        disabled
                                        @endif
                                        @if($Farmer->farmer_status == 'Approved')

                                        @elseif($Farmer->farmer_status == 'Rejected')

                                        @else
                                        @endif
                                        disabled
                                        > {{-- button tag --}}
                                        @if($Farmer->farmer_status == 'Approved')
                                        Approved
                                        @elseif($Farmer->farmer_status == 'Rejected')
                                        Approve
                                        @else
                                        Approve
                                        @endif
                                        <i class="fa fa-spinner fa-spin Aspinner d-none"></i>
                                    </button>
                                    <!-- approve end -->
                                    <button style="width: 30%;" data-toggle="modal" data-target="#reject_remark" class="btn btn-danger RejectBtn m-b-0 mr-3" @if(Auth::user()->hasRole('L-1-Validator'))
                                        disabled
                                        @endif
                                        @if($Farmer->farmer_status == 'Approved')
                                        disabled
                                        @elseif($Farmer->farmer_status == 'Rejected')
                                        disabled
                                        @else
                                        @endif
                                        disabled
                                        > {{-- button tag --}}
                                        @if($Farmer->farmer_status == 'Approved')
                                        Reject
                                        @elseif($Farmer->farmer_status == 'Rejected')
                                        Rejected
                                        @else
                                        Reject
                                        @endif
                                    </button>
                                </div>
                            </div><!-- button end -->
                        </div>
                    </div>
                    {{-- <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th colspan="2" class="text-center">Current Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="{{ $Farmer->onboarding_form ? 'Form-Done' : '' }}"><a class="active btn btn-status{{$Farmer->status_onboarding ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a></td>
                    <td class="{{ $Farmer->cropdata_form ? 'Form-Done' : '' }}">Crop Data</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="{{ $Farmer->pipes_form ? 'Form-Done' : '' }}">Pipes Installations</td>
                        <td class="{{ $Farmer->awd_form ? 'Form-Done' : '' }}">AWD Events Captured</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="{{ $Farmer->benefit_form ? 'Form-Done' : '' }}">Farmer Benefits</td>
                        <td class="{{ $Farmer->other_form ? 'Form-Done' : '' }}">Reports</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div> --}}
    </div>
    @if(!empty($Farmer->reject_remark))
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
                            <tr>
                                <td>Remark</td>
                                <td>{{$Farmer->reject_remark}}</td>
                            </tr>
                            <tr>
                                <td>Remark Date/Time</td>
                                <td>{{$Farmer->reject_timestamp}}</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($Farmer->CropData->count()>0)
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
                                    <tr>
                                        <td>Plot Area</td>
                                        <td>{{$plot->area_in_acers}}</td>
                                    </tr>
                                    <tr>
                                        <td>Crop Season</td>
                                        <td>{{$plot->season}}</td>
                                    </tr>
                                    <tr>
                                        <td>Crop Variety</td>
                                        <td>{{$plot->crop_variety}}</td>
                                    </tr>
                                    <tr>
                                        <td>Date of Irrigation last Season</td>
                                        <td>{{\Carbon\Carbon::parse($plot->dt_irrigation_last)->format('d/m/Y')??''}}</td>
                                    </tr>
                                    <tr>
                                        <td>Date of Land Preparation</td>
                                        <td>{{\Carbon\Carbon::parse($plot->dt_ploughing)->format('d/m/Y')??''}}</td>
                                    </tr>
                                    <tr>
                                        <td>Date of Transplanting</td>
                                        <td>{{\Carbon\Carbon::parse($plot->dt_transplanting)->format('d/m/Y')??''}}</td>
                                    </tr>
                                    <tr>
                                        <td>Surveyor name</td>
                                        <td><a target="_blank" href="{{Route('admin.users.edit',$plot->surveyor_id)}}">{{$plot->surveyor_name}}</a></td>
                                    </tr>
                                    <tr>
                                        <td>Survey Date/Time</td>
                                        <td>{{ $plot->created_at->toDayDateTimeString() }}</td>
                                    </tr>
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
                                    <tr>
                                        <td>Farmer Unique ID</td>
                                        <td>{{$items->farmer_uniqueId}}</td>
                                    </tr>
                                    <tr>
                                        <td>Total Area in Acres</td>
                                        <td>{{$items->total_plot_area}}</td>
                                    </tr>
                                    <tr>
                                        <td>Season</td>
                                        <td>{{$items->seasons}}</td>
                                    </tr>
                                    <tr>
                                        <td>Type of Benefit</td>
                                        <td>{{$items->benefit}}</td>
                                    </tr>
                                    <tr>
                                        <td>Surveyor name</td>
                                        <td><a target="_blank" href="{{Route('admin.users.edit',$items->surveyor_id)}}">{{$items->surveyor_name}}</a></td>
                                    </tr>
                                    <tr>
                                        <td>Survey Date/Time</td>
                                        <td>{{ $items->created_at->toDayDateTimeString() }}</td>
                                    </tr>
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
                            <li data-target="#PlotImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{ asset('public/storage/'.$items->path)}}" alt=""></li>
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
                                {{-- <img class="d-block w-100" height="350" src="{{$items->path}}"> --}}
                                <!--<img class="d-block w-100" height="350" src="{{ asset('public/storage/'.$items->path)}}" alt="">-->
                            </div>
                            @endforeach
                        </div>
                        <a class="carousel-control-prev" href="#PlotImg" data-slide="prev"><span class="carousel-control-prev-icon"></span> <span class="sr-only">Previous</span> </a><a class="carousel-control-next" href="#PlotImg" data-slide="next"><span class="carousel-control-next-icon"></span>
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
                                {{-- <img class="d-block w-100"  height="350" src="{{ $items->path }}" alt=""> --}}
                                <!--<img class="d-block w-100"  height="350" src="{{ asset('public/storage/'.$items->path)}}" alt="">-->
                            </div>
                            @endforeach
                        </div>
                        <a class="carousel-control-prev" href="#BenefitImg" data-slide="prev"><span class="carousel-control-prev-icon"></span> <span class="sr-only">Previous</span> </a><a class="carousel-control-next" href="#BenefitImg" data-slide="next"><span class="carousel-control-next-icon"></span>
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
       {{-- <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="map" style="width: 100%; height:250px;"></div>
                    <!--<div id="map"> -->

                    <!--</div>-->
                </div>
            </div>
        </div>
    </div>--}}
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
                <form class="" method="post">
                    @csrf
                    @method('POST')
                    <label>Remarks <span class="text-danger">*</span></label>
                    <textarea name="name" id="rejectData" class="form-control" rows="8" cols="50"></textarea>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary FarmerReject" onclick="submitReject('{{$Farmer->farmer_uniqueId}}')">Save <i class="fa fa-spinner fa-spin Rspinner d-none"></i></button>
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
                <button type="button" class="close" data-dismiss="modal">X</button>
            </div>
            <div class="modal-body" style="margin-top: -53px;">
                <div class="container">
                    <div class="row">
                        <div class="col" style="font-size: 15px;">
                            1. <strong>Farmer Onboarding</strong>
                        </div>
                        <div class="col">

                        </div>
                    </div>
                    @foreach($farmerplots as $plot) {{-- onchange="SubmitApproval(this.value)" --}}
                    <input type="checkbox" id="onboarding" @if(in_array($plot->plot_no, $onboarding_plot))
                    checked disabled
                    @endif
                    name="onboarding" value="{{$plot->plot_no}}" {{$Farmer->status_onboarding == 'Approved' ?' ':''}}>
                    <label for="onboarding" style="margin-right: 11px;">Plot no {{$plot->plot_no}}</label>&nbsp;
                    @endforeach
                </div>
                <hr><br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary SubmitApproval">Save <i class="fa fa-spinner fa-spin Aspinner d-none"></i></button> {{--onclick="SubmitApproval('{{$Farmer->farmer_uniqueId}}')" --}}
            </div>
            </form>
        </div>
    </div>
</div>

@stop
@section('scripts')




<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCDicyDETOv0vvynhDBPPMgAMGFFkC-TOU&libraries=geometry,places&amp;ext=.js"></script>
<script type="text/javascript" src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>

<!-- Import jQuery and PhotoSwipe Scripts -->
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/photoswipe.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.0/photoswipe-ui-default.min.js"></script>

<script>
    'use strict';
    /* global jQuery, PhotoSwipe, PhotoSwipeUI_Default, console */
    (function($) {
        // Init empty gallery array
        var container = [];
        // Loop over gallery items and push it to the array
        //   $('#gallery').find('figure').each(function(){
        $('#PlotImg .plotImg').find('figure').each(function() {
            var $link = $(this).find('a'),
                item = {
                    src: $link.attr('href'),
                    w: $link.data('width'),
                    h: $link.data('height'),
                    title: $link.data('caption')
                };
            container.push(item);
        });

        // Define click event on gallery item
        $('.plotImg .plotImgclick').click(function(event) {
            // Prevent location change
            event.preventDefault();
            // Define object and gallery options
            var $pswp = $('.pswp')[0],
                options = {
                    index: $(this).parent('figure').index(),
                    bgOpacity: 0.85,
                    showHideOpacity: true
                };
            // Initialize PhotoSwipe
            var gallery = new PhotoSwipe($pswp, PhotoSwipeUI_Default, container, options);
            gallery.init();
        });


        $('#BenefitImg .benefitsimg').find('figure').each(function() {
            var $link = $(this).find('a'),
                item = {
                    src: $link.attr('href'),
                    w: $link.data('width'),
                    h: $link.data('height'),
                    title: $link.data('caption')
                };
            container.push(item);
        });

        // Define click event on gallery item
        $('.benefitsimg .benefitImgclick').click(function(event) {
            // Prevent location change
            event.preventDefault();
            // Define object and gallery options
            var $pswp = $('.pswp')[0],
                options = {
                    index: $(this).parent('figure').index(),
                    bgOpacity: 0.85,
                    showHideOpacity: true
                };
            // Initialize PhotoSwipe
            var benefitgallery = new PhotoSwipe($pswp, PhotoSwipeUI_Default, container, options);
            benefitgallery.init();
        });

    }(jQuery));
</script>
<script>
    $('.EditBasicform').click(function() {
        $('#Basicform input[type=text]').removeAttr('readonly');
        $('.UpdateBasicform').removeClass('d-none');
        $('.CancelBasicform').removeClass('d-none');
        $("#mobile_access").removeAttr('disabled');
        $("#mobile_reln_owner").removeAttr('disabled');
    })
    $('.CancelBasicform').click(function() {
        $('#Basicform input[type=text]').attr('readonly', 'readonly');
        $("#mobile_access").prop("disabled", true);
        $("#mobile_reln_owner").prop("disabled", true);
        $('.UpdateBasicform').addClass('d-none');
        $('.CancelBasicform').addClass('d-none');
    })
    $('.UpdateBasicform').click(function() {
        $('.Updatespinner').removeClass('d-none');
        $('.UpdateBasicform').prop('disabled', true);
        // $('input[name=farmer_name]').val()
        $.ajax({
            type: 'post',
            url: "{{url('admin/farmers/update/')}}/" + '{{$Farmer->id}}',
            data: {
                _token: '{{csrf_token()}}',
                method: 'post',
                FarmerName: $('input[name=farmer_name]').val(),
                MobileAccess: $('#mobile_access option:selected').val(),
                RelOwner: $('#mobile_reln_owner option:selected').val(),
                Mobile: $('input[name=mobile]').val(),
                type: 'UpdateBasicform'
            },
            success: function(data) {
                $('.Updatespinner').addClass('d-none');
                $(".UpdateBasicform").prop('disabled', false);
                $('.UpdateBasicform').addClass('d-none');
                $('.CancelBasicform').addClass('d-none');
                toastr.success("", data.message, {
                    timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                    progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,
                    onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
                    hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                });
                location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $(".UpdateBasicform").prop('disabled', false);
                $('.Updatespinner').addClass('d-none');
                var data = jqXHR.responseJSON;
                if (data.errors) {
                    jQuery.each(data.errors, function(i, val) {
                        toastr.error("", val[0], {
                            positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,
                            newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,
                            showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                            showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",
                            hideMethod: "fadeOut",tapToDismiss: !1
                        })
                    });
                    return false;
                }
                toastr.error("", data.message, {
                    positionClass: "toast-top-right",
                    timeOut: 5000,
                    closeButton: !0,
                    debug: !1,
                    newestOnTop: !0,
                    progressBar: !0,
                    preventDuplicates: !0,
                    onclick: null,
                    showDuration: "300",
                    hideDuration: "1000",
                    extendedTimeOut: "1000",
                    showEasing: "swing",
                    hideEasing: "linear",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                    tapToDismiss: !1
                })
            }
        });
    });

    $('.EditPlot').click(function() {
        $('#EditPlot input[type=text]').removeAttr('readonly');
        $('.UpdatePlot').removeClass('d-none');
        $('.CancelPlot').removeClass('d-none');
    });
    $('.CancelPlot').click(function() {
        $('#EditPlot input[type=text]').attr('readonly', 'readonly');
        $('.UpdatePlot').addClass('d-none');
        $('.CancelPlot').addClass('d-none');
    });
    
    $( ".area_in_acers" ).keyup(function() {
        var basevalue = '{{$minimumvalues->value}}';
        if($(this).val() >= basevalue){
            var PlotNo = $(this).attr("data-plot");
            $('#area_acers_base'+PlotNo).addClass('d-none');
            if(!$(this).val()){
                $('#area_in_acersreq'+PlotNo).removeClass('d-none');  $(".UpdatePlot").prop('disabled', true);
            }else{
                // has data
                $('#area_in_acersreq'+PlotNo).addClass('d-none');   $(".UpdatePlot").prop('disabled', false);
            }
        }else{
            var PlotNo = $(this).attr("data-plot");
            $('#area_acers_base'+PlotNo).removeClass('d-none');
            $(".UpdatePlot").prop('disabled', true);
        }
        
    });
    
    
    // id="area_in_acers{{$plot->plot_no}}" 
    
    $( ".actual_owner_name" ).keyup(function() {
        if(!$(this).val()){
            $('.actual_owner_namereq').removeClass('d-none');$(".UpdatePlot").prop('disabled', true);
        }else{
            // has data
            $('.actual_owner_namereq').addClass('d-none');$(".UpdatePlot").prop('disabled', false);
        }
    });
    
    $( ".survey_no" ).keyup(function() {
        if(!$(this).val()){
            $('.survey_noreq').removeClass('d-none');$(".UpdatePlot").prop('disabled', true);
        }else{
            // has data
            $('.survey_noreq').addClass('d-none');$(".UpdatePlot").prop('disabled', false);
        }
    });
                                    
    $('.UpdatePlot').click(function() {
        $('.UpdatePlotspinner').removeClass('d-none');
        var area = [];
        $.each($("input[name='area_in_acers']"), function() {
            var data = {
                'area': $(this).val(),
                PlotNo: $(this).data("plot")
            };
            area.push({
                'area': $(this).val(),
                PlotNo: parseInt($(this).data("plot"))
            });
        });

        var ownername = [];
        $.each($("input[name='actual_owner_name']"), function() {
            ownername.push({
                'actual_owner_name': $(this).val(),
                PlotNo: parseInt($(this).data("plot"))
            });
        });

        var survey = [];
        $.each($("input[name='survey_no']"), function() {
            var data = {
                'survey': $(this).val(),
                PlotNo: $(this).data("plot")
            };
            survey.push(data);
        });
        $.ajax({
            type: 'post',
            url: "{{url('admin/farmers/update/')}}/" + '{{$Farmer->id}}',
            data: {
                _token: '{{csrf_token()}}',
                method: 'post',
                area: area,
                ownername: ownername,
                survey: survey,
                unique:'{{$Farmer->farmer_uniqueId}}',
                type: 'UpdatePlot'
            },
            success: function(data) {
                $('.UpdatePlotspinner').addClass('d-none');
                $(".UpdatePlot").prop('disabled', false);
                $('.UpdatePlot').addClass('d-none');
                $('.CancelPlot').addClass('d-none');
                toastr.success("", data.message, {
                    timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                    progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,onclick: null,
                    showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",
                    hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                });
                location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $(".UpdatePlot").prop('disabled', false);
                $('.UpdatePlotspinner').addClass('d-none');
                var data = jqXHR.responseJSON;
                if (data.errors) {
                    jQuery.each(data.errors, function(i, val) {
                        toastr.error("", val[0], {
                            positionClass: "toast-top-right",
                            timeOut: 5000,
                            closeButton: !0,
                            debug: !1,
                            newestOnTop: !0,
                            progressBar: !0,
                            preventDuplicates: !0,
                            onclick: null,
                            showDuration: "300",
                            hideDuration: "1000",
                            extendedTimeOut: "1000",
                            showEasing: "swing",
                            hideEasing: "linear",
                            showMethod: "fadeIn",
                            hideMethod: "fadeOut",
                            tapToDismiss: !1
                        })
                    });
                    return false;
                }
                toastr.error("", data.message, {
                    positionClass: "toast-top-right",
                    timeOut: 5000,
                    closeButton: !0,
                    debug: !1,
                    newestOnTop: !0,
                    progressBar: !0,
                    preventDuplicates: !0,
                    onclick: null,
                    showDuration: "300",
                    hideDuration: "1000",
                    extendedTimeOut: "1000",
                    showEasing: "swing",
                    hideEasing: "linear",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                    tapToDismiss: !1
                })
            }
        });
    });

    $('.EditLocation').click(function() {
        $("#states").removeAttr('disabled');
        $("#districts").removeAttr('disabled');
        $("#talukas").removeAttr('disabled');
        $("#panchayats").removeAttr('disabled');
        $("#villages").removeAttr('disabled');
        $('.UpdateLocation').removeClass('d-none');
        $('.CancelLocation').removeClass('d-none');
    })
    $('.CancelLocation').click(function() {
        $('#EditLocation input[type=text]').attr('readonly', 'readonly');
        $("#states").prop("disabled", true);
        $("#districts").prop("disabled", true);
        $("#talukas").prop("disabled", true);
        $("#panchayats").prop("disabled", true);
        $("#villages").prop("disabled", true);
        $('.UpdateLocation').addClass('d-none');
        $('.CancelLocation').addClass('d-none');
    })

    $('.UpdateLocation').click(function() {

        $('.UpdateLocationspinner').removeClass('d-none');

        $.ajax({
            type: 'post',
            url: "{{url('admin/farmers/update/')}}/" + '{{$Farmer->id}}',
            data: {
                _token: '{{csrf_token()}}',
                method: 'post',
                state: $('#states option:selected').val(),
                district: $('#districts option:selected').val(),
                taluka: $('#talukas option:selected').val(),
                panchayat: $('#panchayats option:selected').val(),
                village: $('#villages option:selected').val(),
                type: 'UpdateLocation'
            },
            success: function(data) {
                $('.UpdateLocationspinner').addClass('d-none');
                $(".UpdateLocation").prop('disabled', false);
                $('.UpdateLocation').addClass('d-none');
                $('.CancelLocation').addClass('d-none');
                toastr.success("", data.message, {
                    timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,
                    progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,
                    onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
                    showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                    tapToDismiss: !1
                });
                location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $(".UpdateLocation").prop('disabled', false);
                $('.UpdateLocationspinner').addClass('d-none');
                var data = jqXHR.responseJSON;
                if (data.errors) {
                    jQuery.each(data.errors, function(i, val) {
                        toastr.error("", val[0], {
                            positionClass: "toast-top-right",
                            timeOut: 5000,
                            closeButton: !0,
                            debug: !1,
                            newestOnTop: !0,
                            progressBar: !0,
                            preventDuplicates: !0,
                            onclick: null,
                            showDuration: "300",
                            hideDuration: "1000",
                            extendedTimeOut: "1000",
                            showEasing: "swing",
                            hideEasing: "linear",
                            showMethod: "fadeIn",
                            hideMethod: "fadeOut",
                            tapToDismiss: !1
                        })
                    });
                    return false;
                }
                toastr.error("", data.message, {
                    positionClass: "toast-top-right",
                    timeOut: 5000,
                    closeButton: !0,
                    debug: !1,
                    newestOnTop: !0,
                    progressBar: !0,
                    preventDuplicates: !0,
                    onclick: null,
                    showDuration: "300",
                    hideDuration: "1000",
                    extendedTimeOut: "1000",
                    showEasing: "swing",
                    hideEasing: "linear",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                    tapToDismiss: !1
                })
            }
        });
    });

    function FetchDistrict(Id) {
        var stateID = Id; //$(this).val();
        if (stateID) {
            $.ajax({
                type: 'post',
                url: "{{url('admin/fetch/district')}}/" + stateID,
                dataType: 'Json',
                data: {
                    _token: '{{csrf_token()}}',
                    'id': stateID
                },
                success: function(data) {
                    $('select[name="districts"]').empty();
                    $('select[name="districts"]').append('<option value="">Select District</option>');
                    $.each(data.district, function(key, value) {
                        console.log(value);
                        $('select[name="districts"]').append('<option value="' + value.id + '">' + value.district + '</option>');
                    });
                }
            });
        } else {
            $('select[name="districts"]').empty();
        }
    }

    function FetchBlock(Id) {
        var districtID = Id; //$(this).val();
        console.log(districtID);
        if (districtID) {
            $.ajax({
                type: 'post',
                url: "{{url('admin/fetch/block')}}/" + districtID,
                dataType: 'Json',
                data: {
                    _token: '{{csrf_token()}}',
                    'id': districtID
                },
                success: function(data) {
                    $('select[name="talukas"]').empty();
                    $('select[name="talukas"]').append('<option value="">Select Taluka</option>');
                    $.each(data.Taluka, function(i, v) {
                        $('select[name="talukas"]').append('<option value="' + v.id + '">' + v.taluka + '</option>');
                    });
                }
            });
        } else {
            $('select[name="talukas"]').empty();
        }
    }

    function FetchPanchayat(Id) {
        var blockID = Id; //$(this).val();
        console.log(blockID);
        if (blockID) {
            $.ajax({
                type: 'post',
                url: "{{url('admin/fetch/panchayat')}}/" + blockID,
                dataType: 'Json',
                data: {
                    _token: '{{csrf_token()}}',
                    'id': blockID
                },
                success: function(data) {
                    $('select[name="panchayats"]').empty();
                    $('select[name="panchayats"]').append('<option value="">Select Panchayat</option>');
                    $.each(data.panchayat, function(i, v) {
                        $('select[name="panchayats"]').append('<option value="' + v.id + '">' + v.panchayat + '</option>');
                    });
                }
            });
        } else {
            $('select[name="panchayats"]').empty();
        }
    }

    function FetchVillage(Id) {
        var PanchayatID = Id; //$(this).val();
        if (PanchayatID) {
            $.ajax({
                type: 'post',
                url: "{{url('admin/fetch/village')}}/" + PanchayatID,
                dataType: 'Json',
                data: {
                    _token: '{{csrf_token()}}',
                    'id': PanchayatID
                },
                success: function(data) {
                    $('select[name="villages"]').empty();
                    $('select[name="villages"]').append('<option value="">Select Village</option>');
                    $.each(data.Village, function(i, v) {
                        console.log(v);
                        $('select[name="villages"]').append('<option value="' + v.id + '">' + v.village + '</option>');
                    });
                }
            });
        } else {
            $('select[name="villages"]').empty();
        }
    }

   function initMap() {
      const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        center: { lat: {!!$Farmer->latitude!!}, lng: {!! $Farmer->longitude!!} },
        mapTypeId: "hybrid",
        scrollwheel: true,
      });

      new google.maps.Marker({
        position: { lat: {!!$Farmer->latitude!!}, lng: {!! $Farmer->longitude!!} },
        map,
        title: "Farmer",
      });
}
initMap();
</script>
@stop
