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
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex"></div>
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
                                              <th colspan="2" class="text-center">Farmer Info <button class="btn btn-success EditBasicform" style="margin: 0px 1px 1px 187px;padding: 4px 4px 4px 4px;cursor: pointer;">Edit</button></th>
                                            </tr>
                                        </thead>
                                        <tbody id="Basicform">
                                            <tr>
                                                <td>Farmer Unique Id</td><td>{{$plot->farmer->farmer_uniqueId}}</td>
                                            </tr>
                                            <tr>
                                              <td>Farmer Name</td><td><input type="text" class="form-control farmer_name" name="farmer_name" style="" value="{{$plot->farmer->farmer_name}}" readonly>
                                              <br><span class='d-none farmer_namereq' style="color:red;">Required</span>
                                            </td>
                                            </tr>
                                            <tr>
                                              <td>Mobile Access</td>
                                              <td>
                                              <select id="mobile_access" name="mobile_access" class="form-control select2 mobile_access" disabled>
                                                <option value="">Select Mobile Access</option>
                                                <option value="Own Number" {{ 'Own Number' == $plot->farmer->mobile_access ? 'Selected' :'' }}>Own Number</option>
                                                <option value="Relatives Number" {{ 'Relatives Number' == $plot->farmer->mobile_access ? 'Selected' :'' }}>Relatives Number</option>
                                              </select>
                                              <br><span class='d-none mobile_accessreq' style="color:red;">Required</span>
                                            </td>
                                            </tr>
                                            <tr>
                                              <td>Relationship owner</td>
                                              <td>
                                                <select id="mobile_reln_owner" name="mobile_reln_owner" class="form-control select2 mobile_reln_owner" disabled>
                                                    <option value="">Select Relationship</option>
                                                    @foreach($Relationshipowner as $relationshipowner)
                                                    <option value="{{$relationshipowner->name}}" {{ $relationshipowner->name == $plot->farmer->mobile_reln_owner ? 'Selected' :'' }}>{{$relationshipowner->name}}</option>
                                                @endforeach
                                                </select>
                                                <br><span class='d-none mobile_reln_ownerreq' style="color:red;">Required</span>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td>Mobile</td><td><input type="text" class="form-control mobile"  name="mobile" value="{{$plot->farmer->mobile}}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" readonly maxlength="10">
                                              <br><span class='d-none mobilereq' style="color:red;">Required</span>
                                            </td>
                                            </tr>
                                            <tr>
                                                <td>Plot No.</td><td>{{$plot->plot_no}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button style="padding: 4px 4px 4px 4px;" class="btn btn-success float-right UpdateBasicform d-none">Submit <i class="fa fa-spinner fa-spin Updatespinner d-none"></i></button>
                                    <button style="padding: 4px 4px 4px 4px;margin-right: 6px;" class="btn btn-danger float-right CancelBasicform d-none">Cancel</button>
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
                                              <th colspan="6" class="text-center">Plot Info <button class="btn btn-success EditPlot" style="margin: 0px 1px 1px 187px;padding: 4px 4px 4px 4px;cursor: pointer;">Edit</Button></th>
                                            </tr>
                                        </thead>
                                        <tbody id="EditPlot">
                                          <tr>
                                            <td>No of Plots</td><td colspan="3">{{$plot->farmer->no_of_plots}}</td><td>Area of Plots</td><td>{{$plot->farmer->total_plot_area}}</td>
                                          </tr>
                                          @if($farmerplots->count()>0)
                                              <tr><td>Plot No.</td><td>Area in Acres</td><td colspan="2">Actual Owner</td><td>Survey No.</td><td>Photos</td></tr>
                                              @foreach($farmerplots as $plot)
                                              <tr>
                                                  <td>{{$plot->plot_no}}</td>
                                                  <td><input type="text" style="width: 70px;"class="form-control area_in_acers" name="area_in_acers" data-plot="{{$plot->plot_no}}"  value="{{$plot->area_in_acers}}" readonly oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');">
                                                  <br><span class='d-none area_in_acersreq{{$plot->plot_no}}' style="color:red;">Required </span><span class='d-none' id="area_acers_base{{$plot->plot_no}}" style="color:red;"> Should be greater than {{$minimumvalues->value}}</span>

                                                  <td colspan="2"><input style="width: 111px;" type="text" class="form-control actual_owner_name" name="actual_owner_name" data-plot="{{$plot->plot_no}}"  value="{{$plot->actual_owner_name}}" readonly><br><span class='d-none actual_owner_namereq' style="color:red;">Required</span></td>
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
                                    <button style="padding: 4px 4px 4px 4px;" class="btn btn-success float-right UpdatePlot d-none" disabled>Submit <i class="fa fa-spinner fa-spin UpdatePlotspinner d-none"></i></button>
                                    <button style="padding: 4px 4px 4px 4px;margin-right: 6px;" class="btn btn-danger float-right CancelPlot d-none">Cancel</button>
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
                    <div class="col-md-12">
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
                                                  <select id="panchayats" name="panchayats" {{--onchange="FetchVillage(this.value)"--}} class="form-control select2" disabled>
                                                      <option value="">Panchayat</option>
                                                      @if($panchayats)
                                                      @foreach($panchayats as $panchayat)
                                                      <option value="{!! $panchayat->id !!}" {{ $plot->farmer->panchayat_id == $panchayat->id ?'Selected' :'' }}>{!! $panchayat->panchayat !!}</option>
                                                      @endforeach
                                                      @endif
                                                  </select>
                                                  <br><span class='d-none panchayatsreq' style="color:red;">Required</span>
                                            </tr>
                                            <tr>
                                              <td>Village</td>
                                              <td>
                                                  <select id="villages" name="villages" class="form-control select2" disabled>
                                                      <option value="">Village</option>
                                                      @if($villages)
                                                      @foreach($villages as $village)
                                                      <option value="{!! $village->id !!}" {{ $plot->farmer->village_id == $village->id ?'Selected' :'' }}>{!! $village->village !!}</option>
                                                      @endforeach
                                                      @endif
                                                  </select>
                                                  <br><span class='d-none villagesreq' style="color:red;">Required</span>
                                              </td>
                                            </tr>
                                            <tr>
                                                <td>Remarks</td>
                                                <td>{{$plot->farmer->remarks}}</td>
                                            </tr>
                                            <tr>
                                                <td>Latitude</td><td>{{$plot->farmer->latitude}}</td>
                                            </tr>
                                            <tr>
                                                <td>Logitude</td><td>{{$plot->farmer->longitude}}</td>
                                            </tr>
                                            <tr>
                                                <td class="align-top">Remarks</td><td>{{$plot->farmer->remarks}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button style="padding: 4px 4px 4px 4px;" class="btn btn-success float-right UpdateLocation d-none">Submit <i class="fa fa-spinner fa-spin UpdateLocationspinner d-none"></i></button>
                                    <button style="padding: 4px 4px 4px 4px;margin-right: 6px;" class="btn btn-danger float-right CancelLocation d-none">Cancel</button>
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
                                                <td>Name</td><td><a  target="_blank"  href="{{Route('admin.users.edit',$plot->farmer->surveyor_id)}}">{{$plot->farmer->surveyor_name}}</a></td>
                                            </tr>
                                            <tr>
                                                <td>Mobile No</td><td>{{$plot->farmer->surveyor_mobile}}</td>
                                            </tr>
                                            <tr>
                                                <td>Email ID</td><td>{{$plot->farmer->surveyor_email}}</td>
                                            </tr>
                                            <tr>
                                                <td>Date of Survey</td><td>{{$plot->farmer->date_survey}}</td>
                                            </tr>
                                            <tr>
                                                <td>Time of Survey</td><td>{{ $plot->farmer->time_survey }} </td>
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
                              <a style="width: 30%;" class="active btn btn-status{{$plot->farmer->onboarding_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>
                              <a style="width: 26%;" class="CropDataShow btn btn-status{{$plot->farmer->cropdata_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" class="btn btn-status{{$plot->farmer->pipes_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                            </div>
                            <div class="row mb-3">
                              <a style="width: 30%;" class="btn btn-status{{$plot->farmer->awd_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$plot->farmer->benefit_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$plot->farmer->other_form ? '-done' : ''}} m-b-0"><span class="btn-txt">Others</span></a>
                            </div>
                            <div class="row">
                                    {{--<a style="width: 30%;" href="{{url('l1/pending/plot/detail').'/'.$plot->id}}"
                                      class="btn btn-info m-b-0 mr-3"
                                    >Show</a>--}}
                            </div>
                          </div><!-- button end -->
                      </div>
                  </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                @if($plot->FarmerPlotImages()->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title bg-primary text-white p-3 text-center">Plot Photos</h5>
                            <!-- All plot images -->
                            <div id="PlotImg" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach($plot->FarmerPlotImages as $items)
                                    <li data-target="#PlotImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{$items->path}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($plot->FarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
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
                  @foreach($farmerplots as $plotdata)
                          <div class="row">
                            <div class="col">
                                <label for="plotno" style="margin-right: 11px;">Plot no {{$plotdata->plot_no}}</label>&nbsp;
                                  <input type="checkbox"  id="plotno{{$plot->plot_no}}"
                                    {{ $plotdata->status == 'Rejected' ? 'checked disabled' : ''}}
                                    {{ $plotdata->status == 'Approved' ? 'disabled' : ''}}
                                  name="plotno" value="{{$plot->plot_no}}" {{$plot->farmer->status_onboarding == 'Approved' ?' ':''}}>&nbsp;
                                  {{ $plotdata->status == 'Rejected' ? '(Rejected)' : ''}}{{ $plotdata->status == 'Approved' ? '(Approved)' : ''}}
                                  {{ $plotdata->check_update == '1' ? '(Validate)' : ''}}
                            </div>
                            <div class="col">
                                 <select {{ $plotdata->status == 'Rejected' ? 'disabled' : ''}} {{ $plotdata->status == 'Approved' ? 'disabled' : ''}} id="reasons{{$plotdata->plot_no}}"
                                            data-plot="{{$plotdata->plot_no}}"
                                            name="reasons" class="form-control select2">
                                     <option value="">Select Reasons</option>
                                      @foreach($reject_module as $list)
                                        <option value="{{$list->id}}" {{$plotdata->reason_id == $list->id ? 'Selected' :''}}>{{$list->reasons}}</option>
                                      @endforeach
                                 </select>
                            </div>
                            <div style="margin: 0px 0px 0px 17px;">
                                <label for="reject_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                                <textarea {{ $plotdata->status == 'Rejected' ? 'readonly' : ''}}  {{ $plotdata->status == 'Approved' ? 'disabled' : ''}}
                                          class="form-control" id="reject_comment{{$plot->plot_no}}"
                                           name="reject_comment" rows="3" cols="50">{{$plot->reject_comment}}</textarea>
                            </div><br>&nbsp;
                          </div>

                          <div class="row">
                                <div class="col mt-2">
                                  {{$plotdata->UserApprovedRejected->name??''}}<br>
                                  @if(Auth::user()->hasRole('L-1-Validator'))
                                    <a  target="_blank"  href="{{Route('admin.validator.edit',$plotdata->UserApprovedRejected->id??'')}}">{{$plotdata->UserApprovedRejected->email??""}}</a>
                                  @else
                                  {{$plotdata->UserApprovedRejected->email??''}}
                                  @endif

                                </div>
                                <div class="col">
                                  <button {{ $plotdata->status == 'Rejected' ? 'disabled' : ''}} {{ $plotdata->status == 'Approved' ? 'disabled' : ''}}
                                           type="button" class="btn btn-primary FarmerReject float-right"
                                           data-rejectplot="{{$plotdata->plot_no}}" style="margin-top: 5px;">Save plot no {{$plotdata->plot_no}}
                                           <i id="Rspinner{{$plotdata->plot_no}}" class="fa fa-spinner fa-spin Rspinner d-none"></i></button>
                                </div>
                              </div>



                          <hr style="margin-top: 51px;">
                  @endforeach
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
                     1. <strong>Farmer Onboarding</strong>
                    </div>
                    <div class="col">

                    </div>
                  </div>
                  @foreach($farmerplots as $plotlist)
                  <div class="row">
                    <div class="col mt-2">
                      <input type="checkbox"  id="onboarding"
                      {{ $plotlist->status == 'Rejected' ? 'disabled' : ''}}
                      {{ $plotlist->status == 'Approved' ? 'checked disabled' : ''}}
                      name="onboarding" value="{{$plotlist->plot_no}}" {{$plotlist->farmer->status_onboarding == 'Approved' ?' ':''}}>
                      <label  title="{{ $plotlist->status == 'Rejected' ? 'Rejected' : ''}}{{ $plotlist->status == 'Approved' ? 'Approved' : ''}}{{ $plotlist->status == 'Pending' ? 'Pending' : ''}}"
                          for="onboarding"
                          style="margin-right: 11px;">Plot no {{$plotlist->plot_no}}</label>&nbsp;
                    </div>
                    <div class="col">
                       {{$plotlist->UserApprovedRejected->name??''}}<br>
                          @if(Auth::user()->hasRole('L-1-Validator'))
                            <a  target="_blank"  href="{{Route('admin.validator.edit',$plotlist->UserApprovedRejected->id??'')}}">{{$plotlist->UserApprovedRejected->email??""}}</a>
                          @else
                          {{$plotlist->UserApprovedRejected->email??''}}
                          @endif
                    </div>
                  </div><hr>
                   @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" {{ $plotlist->status == 'Approved' ? 'disabled' : ''}}
                class="btn btn-primary SubmitApproval">Save <i class="fa fa-spinner fa-spin Aspinner d-none"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>

@stop
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js" integrity="sha512-E4rXB8fOORHVM/jZYNCX2rIY+FOvmTsWJ7OKZOG9x/0RmMAGyyzBqZG0OGKMpTyyuXVVoJsKKWYwbm7OU2klxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{asset('vendor/photoviewer/dist/photoviewer.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script>
(function(){
    'use strict';
    $('.plot-gallery a').click(function (e) {
        e.preventDefault();
        var items = [],
            options = {
                index: $(this).index()
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
                index: $(this).parents('.carousel-item').index()
            };
        $('#PlotImg').find('.plotImgclick').each(function(){
            let src = $(this).attr('href');
            items.push({
                src: src
            });
        });
        new PhotoViewer(items,options);
    });

})($);

$('.EditBasicform').click(function () {
  $('#Basicform input[type=text]').removeAttr('readonly');
  $('.UpdateBasicform').removeClass('d-none');
  $('.CancelBasicform').removeClass('d-none');
  $("#mobile_access").removeAttr('disabled');
  $("#mobile_reln_owner").removeAttr('disabled');
})
$('.CancelBasicform').click(function () {
  $('#Basicform input[type=text]').attr('readonly','readonly');
  $("#mobile_access").prop("disabled", true);
  $("#mobile_reln_owner").prop("disabled", true);
  $('.UpdateBasicform').addClass('d-none');
  $('.CancelBasicform').addClass('d-none');
})
$('.UpdateBasicform').click(function(){
    if(!$('input[name=farmer_name]').val()){
       $('.farmer_namereq').removeClass('d-none');
       return false;
    }
    if(!$('#mobile_access option:selected').val()){
       $('.mobile_accessreq').removeClass('d-none');
       return false;
    }
    if(!$('input[name=mobile]').val()){
       $('.mobilereq').removeClass('d-none');
       return false;
    }
    $('.Updatespinner').removeClass('d-none');
    $('.UpdateBasicform').prop('disabled', true);
    $.ajax({
      type:'post',
      url:"{{url('l1/plot/update/')}}/"+'{{$plot->farmer->id}}',
      data: {_token:'{{csrf_token()}}',method:'post',FarmerName:$('input[name=farmer_name]').val(),MobileAccess:$('#mobile_access option:selected').val(),
                                                     RelOwner:$('#mobile_reln_owner option:selected').val(),Mobile:$('input[name=mobile]').val(),type:'UpdateBasicform'},
      success:function(data){
        $('.Updatespinner').addClass('d-none');
        $(".UpdateBasicform").prop('disabled', false);
        $('.UpdateBasicform').addClass('d-none');
        $('.CancelBasicform').addClass('d-none');
        toastr.success("", data.message, {
              timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
              showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          });
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
          $(".UpdateBasicform").prop('disabled', false);
          $('.Updatespinner').addClass('d-none');
        var data = jqXHR.responseJSON;
        if(data.errors){
            jQuery.each( data.errors, function( i, val ) {
                  toastr.error("", val[0], {
                      positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
                      hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                  })
            });
            return false;
        }
        toastr.error("", data.message, {
              positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
              hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          })
      }
    });
});


$('.EditPlot').click(function () {
  $('#EditPlot input[type=text]').removeAttr('readonly');
  $('.UpdatePlot').removeClass('d-none');
  $('.CancelPlot').removeClass('d-none');
});
$('.CancelPlot').click(function () {
  $('#EditPlot input[type=text]').attr('readonly','readonly');
  $('.UpdatePlot').addClass('d-none');
  $('.CancelPlot').addClass('d-none');
});

// $( ".area_in_acers" ).keyup(function() {
//     if(!$(this).val()){
//       $('.area_in_acersreq').removeClass('d-none');$(".UpdatePlot").prop('disabled', true);
//     }else{
//         // has data
//         $('.area_in_acersreq').addClass('d-none');$(".UpdatePlot").prop('disabled', false);
//     }
// });

$( ".area_in_acers" ).keyup(function() {
    var basevalue = '{{$minimumvalues->value}}';
    if($(this).val() >= basevalue){
        var PlotNo = $(this).attr("data-plot");
        $('#area_acers_base'+PlotNo).addClass('d-none');
        if(!$(this).val()){
            $('.area_in_acersreq'+PlotNo).removeClass('d-none');  $(".UpdatePlot").prop('disabled', true);
        }else{
            // has data
            $('.area_in_acersreq'+PlotNo).addClass('d-none');   $(".UpdatePlot").prop('disabled', false);
        }
    }else{
        var PlotNo = $(this).attr("data-plot");
        $('#area_acers_base'+PlotNo).removeClass('d-none');
        $(".UpdatePlot").prop('disabled', true);
    }

});


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

$('.UpdatePlot').click(function(){
    $('.UpdatePlotspinner').removeClass('d-none');
    var area = [];
    $.each($("input[name='area_in_acers']"), function(){
        var data = {'area' : $(this).val(), PlotNo:$(this).data("plot")};
        area.push({'area' : $(this).val(), PlotNo:parseInt($(this).data("plot"))});
    });

    var ownername = [];
    $.each($("input[name='actual_owner_name']"), function(){
        ownername.push({'actual_owner_name' : $(this).val(), PlotNo:parseInt($(this).data("plot"))});
    });

    var survey = [];
    $.each($("input[name='survey_no']"), function(){
       var data = {'survey' : $(this).val(), PlotNo:$(this).data("plot")};
       survey.push(data);
    });
    $.ajax({
      type:'post',
      url:"{{url('l1/plot/update/')}}/"+'{{$plot->farmer->id}}',
      data: {_token:'{{csrf_token()}}',method:'post',area:area,ownername:ownername,survey:survey,unique:'{{$plot->farmer->farmer_uniqueId}}',type:'UpdatePlot'},
      success:function(data){
        $('.UpdatePlotspinner').addClass('d-none');
        $(".UpdatePlot").prop('disabled', false);
        $('.UpdatePlot').addClass('d-none');
        $('.CancelPlot').addClass('d-none');
        toastr.success("", data.message, {
              timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
              showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          });
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
          $(".UpdatePlot").prop('disabled', false);
          $('.UpdatePlotspinner').addClass('d-none');
        var data = jqXHR.responseJSON;
        if(data.errors){
            jQuery.each( data.errors, function( i, val ) {
                  toastr.error("", val[0], {
                      positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
                      hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                  })
            });
            return false;
        }
        toastr.error("", data.message, {
              positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
              hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          })
      }
    });
});

$('.EditLocation').click(function () {
  $("#states").removeAttr('disabled');
  $("#districts").removeAttr('disabled');
  $("#talukas").removeAttr('disabled');
  $("#panchayats").removeAttr('disabled');
  $("#villages").removeAttr('disabled');
  $('.UpdateLocation').removeClass('d-none');
  $('.CancelLocation').removeClass('d-none');
})
$('.CancelLocation').click(function () {
  $('#EditLocation input[type=text]').attr('readonly','readonly');
  $("#states").prop("disabled", true);
  $("#districts").prop("disabled", true);
  $("#talukas").prop("disabled", true);
  $("#panchayats").prop("disabled", true);
  $("#villages").prop("disabled", true);
  $('.UpdateLocation').addClass('d-none');
  $('.CancelLocation').addClass('d-none');
})

$('.UpdateLocation').click(function(){
    // if(!$('#states option:selected').val()){
    //   $('.statesreq').removeClass('d-none');
    //   return false;
    // }
    // if(!$('#districts option:selected').val()){
    //   $('.districtsreq').removeClass('d-none');
    //   return false;
    // }
    // if(!$('#talukas option:selected').val()){
    //   $('.talukasreq').removeClass('d-none');
    //   return false;
    // }
    if(!$('#panchayats option:selected').val()){
       $('.panchayatsreq').removeClass('d-none');
       return false;
    }
    if(!$('#villages option:selected').val()){
       $('.villagesreq').removeClass('d-none');
       return false;
    }
    $('.UpdateLocationspinner').removeClass('d-none');
    $.ajax({
      type:'post',
      url:"{{url('l1/plot/update/')}}/"+'{{$plot->farmer->id}}',
      data: {_token:'{{csrf_token()}}',method:'post',state:$('#states option:selected').val(),district:$('#districts option:selected').val(),taluka:$('#talukas option:selected').val(),
                                                    panchayat:$('#panchayats option:selected').val(),village:$('#villages option:selected').val(),type:'UpdateLocation'},
      success:function(data){
        $('.UpdateLocationspinner').addClass('d-none');
        $(".UpdateLocation").prop('disabled', false);
        $('.UpdateLocation').addClass('d-none');
        $('.CancelLocation').addClass('d-none');
        toastr.success("", data.message, {
              timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,positionClass: "toast-top-right",preventDuplicates: !0,onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",
              showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          });
        location.reload();
      },
      error: function (jqXHR, textStatus, errorThrown) {
          $(".UpdateLocation").prop('disabled', false);
          $('.UpdateLocationspinner').addClass('d-none');
        var data = jqXHR.responseJSON;
        if(data.errors){
            jQuery.each( data.errors, function( i, val ) {
                  toastr.error("", val[0], {
                      positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
                      hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
                  })
            });
            return false;
        }
        toastr.error("", data.message, {
              positionClass: "toast-top-right",timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
              hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",tapToDismiss: !1
          })
      }
    });
});

</script>
@stop
