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
                        $prev=\App\Models\FarmerPlot::with('farmer')->where('id','<',$plot->id)->orderBy('id','desc')->whereHas('farmer',function($q){
                            $q->where('onboarding_form','1');
                          return $q;
                        })->first();
                        $next=\App\Models\FarmerPlot::with('farmer')->where('id','>',$plot->id)->orderBy('id','asc')->whereHas('farmer',function($q){
                            $q->where('onboarding_form','1');
                          return $q;
                        })->first();
            @endphp
            @if($prev)
                <a style="color: red;" href="{{ url('l2/all-farmer/plot').'/'.$prev->id}}"  class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i>Previous</a>
            @endif
            @if($next)
                <a style="color: red;" href="{{ url('l2/all-farmer/plot').'/'.$next->id}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
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
                                                <td>Farmer Unique Id</td><td>{{$plot->farmer->farmer_uniqueId}}</td>
                                            </tr>
                                            <tr>
                                                <td>Farmer Name</td><td>{{$plot->farmer->farmer_name}}</td>
                                            </tr>
                                            <tr>
                                                <td>Mobile Access</td><td>{{$plot->farmer->mobile_access}}</td>
                                            </tr>
                                            <tr>
                                                <td>Relationship owner</td><td>{{$plot->farmer->mobile_reln_owner}}</td>
                                            </tr>
                                            <tr>
                                                <td>Mobile</td><td>{{$plot->farmer->mobile}}</td>
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
                                                <th colspan="{{$plot->farmer->state_id == 36 ? '6' : '5'}}" class="text-center">Plot Info</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <tr>
                                              <td>Total Plots</td>
                                              <td colspan="{{$plot->farmer->state_id == 36 ? '3' : '2'}}">
                                                 {{ $valicountplotapprv ? $valicountplotapprv : $plot->farmer->no_of_plots }} {{-- $plot->no_of_plots --}}
                                              </td>
                                              <td>Area of Plots (Acers)</td>
                                              <td>{{ $plot->total_area_acres_of_guntha ? $plot->total_area_acres_of_guntha : $plot->farmer->total_plot_area}}</td>
                                          </tr>
                                            <tr>
                                                <td>Plot No.</td>
                                                @if($plot->farmer->state_id == 36)
                                                    <td>Area in (A.G)</td>
                                                @else
                                                 <td>Area in Acres</td>
                                                @endif
                                                @if($plot->farmer->state_id == 36)
                                                    <td>Area in Acres</td>
                                                @endif
                                                <td>Plot Owner</td>
                                                <td>Survey No.</td>
                                                <td class="d-none">Documents</td>
                                                <td>Photos</td>
                                            </tr>
                                            <tr>
                                                @php $color=''; @endphp
                                                @if($plot->status == 'Pending')
                                                    @php $color = 'blue'; @endphp
                                                @elseif($plot->status == 'Approved')
                                                    @php $color = 'green'; @endphp
                                                @elseif($plot->status == 'Rejected')
                                                    @php $color = 'red'; @endphp
                                                @endif
                                                <td>{{$plot->plot_no}}&nbsp;<span class="dot{{$color}}"></span>&nbsp;{{$plot->land_ownership == 'Own' ? 'O' : 'L'}}</td>
                                                @if($plot->farmer->state_id == 36)
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
                                                    <a class="btn btn-sm p-0 popup-gallery" href="{{Storage::disk('s3')->url($items->path)}}"><img src="{{asset('icons/icons8-photos-100.png')}}" class="w-32"></a>
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
                                                <th colspan="2" class="text-center">Location Info</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>State</td><td>{{$plot->farmer->state}}</td>
                                            </tr>
                                            <tr>
                                                <td>District</td><td>{{$plot->farmer->district}}</td>
                                            </tr>
                                            <tr>
                                                <td>Taluka</td><td>{{$plot->farmer->taluka}}</td>
                                            </tr>
                                            <tr>
                                                <td>Panchayat</td><td>{{$plot->farmer->panchayat}}</td>
                                            </tr>
                                            <tr>
                                                <td>Village</td><td>{{$plot->farmer->village}}</td>
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
                                                    <a  target="_blank"  href="{{Route('admin.users.edit',$plot->farmer->surveyor_id)}}">{{$plot->farmer->surveyor_name}}</a>
                                                @else
                                                    {{$plot->farmer->surveyor_name}}
                                                @endif
                                                </td>
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
                            <div class="row mb-3">
                              {{--      <a style="width: 30%;" href="{{url('admin/farmers/plot/edit/'.$plot->id.'/'.$plot->farmer_uniqueId)}}"
                                      class="btn btn-info m-b-0 mr-3 EditBtn "
                                       @if($plot->farmer->status_onboarding == 'Approved')
                                        disabled
                                      @elseif($plot->farmer->status_onboarding == 'Rejected')
                                        disabled
                                      @else
                                      @endif
                                    >EDIT</a>
                                <!-- end button end -->
                              <button style="width: 26%;" disabled
                                  data-toggle="modal" data-target="#FinalApproveModal"
                                  class="btn btn-success FinalApproveBtn m-b-0 mr-3" 
                                    >
                                    {{ $plot->final_status == 'Approved'? 'Approved' :'Approve' }}
                              </button>
                              <!-- approve end -->
                              <button style="width: 30%;" data-toggle="modal" data-target="#Finalreject_remark" disabled
                                    class="btn btn-danger FinalRejectBtn m-b-0 mr-3">
                                    {{ $plot->final_status == 'Rejected'? 'Rejected' :'Reject' }}
                              </button>  --}}
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
                                    @foreach($plot->FarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
                                    <li data-target="#PlotImg" data-slide-to="{{$loop->index}}" class="{{$loop->first?'active':''}}"><img class="d-block w-100 img-fluid" src="{{Storage::disk('s3')->url($items->path)}}" alt=""></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner">
                                    @foreach($plot->FarmerPlotImages()->where('plot_no',$plot->plot_no)->get() as $items)
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
                @endif
                {{--<div class="col-12">
                    <div class="card">
                        <div class="card-body">
                              <div id="map" style="width: 100%; height:250px;"></div>
                        </div>
                    </div>
                </div> --}}

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
                                  {{$plotdata->UserApprovedRejected->name??''}}   /  {{ Carbon\Carbon::parse($plot->reject_timestamp)->toDayDateTimeString() }}<br>
                                  @if(Auth::user()->hasRole('SuperAdmin'))
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
                      <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                            <textarea {{ $plotlist->status == 'Approved' ? 'readonly' : ''}}  {{ $plotlist->status == 'Rejected' ? 'disabled' : ''}}
                                      class="form-control" id="approve_comment{{$plotlist->plot_no}}"
                                       name="approve_comment" rows="3" cols="50">{{$plotlist->approve_comment}}</textarea>
                        </div><br>&nbsp;
                    </div>
                    <div class="col">
                       {{$plotlist->UserApprovedRejected->name??''}}   / {{ Carbon\Carbon::parse($plot->appr_timestamp)->toDayDateTimeString() }} <br>
                          @if(Auth::user()->hasRole('SuperAdmin'))
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
                class="btn btn-primary SubmitApproval" disabled>Save <i class="fa fa-spinner fa-spin Aspinner d-none"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Final approve module start  -->
<div class="modal fade" id="FinalApproveModal">
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
                {{--  @php dd($plotlist); @endphp --}}
                  <div class="row">
                    <div class="col mt-2">
                      <input type="checkbox"  id="Finalonboarding"
                      {{ $plotlist->final_status == 'Rejected' ? 'disabled' : ''}}
                      {{ $plotlist->final_status == 'Approved' ? 'checked disabled' : ''}}
                      name="Finalonboarding" value="{{$plotlist->plot_no}}" {{$plotlist->farmer->final_status_onboarding == 'Approved' ?' ':''}}>
                      <label  title="{{ $plotlist->final_status == 'Rejected' ? 'Rejected' : ''}}{{ $plotlist->final_status == 'Approved' ? 'Approved' : ''}}{{ $plotlist->final_status == 'Pending' ? 'Pending' : ''}}"
                          for="onboarding"
                          style="margin-right: 11px;">Plot no {{$plotlist->plot_no}}</label>&nbsp;
                      <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Remark</label>&nbsp;
                            <textarea {{ $plotlist->final_status == 'Approved' ? 'readonly' : ''}}  {{ $plotlist->final_status == 'Rejected' ? 'disabled' : ''}}
                                      class="form-control" id="Finalapprove_comment{{$plotlist->plot_no}}"
                                       name="Finalapprove_comment" rows="3" cols="50">{{$plotlist->finalaprv_remark}}</textarea>
                        </div><br>&nbsp;
                    </div>
                    <div class="col">
                       {{$plotlist->FinalUserApprovedRejected->name ??' '}} {{ $plot->finalaprv_timestamp ? ' / '.Carbon\Carbon::parse($plot->finalaprv_timestamp)->toDayDateTimeString() :'' }} <br>
                          @if(Auth::user()->hasRole('SuperAdmin'))
                            <a  target="_blank"  href="{{Route('admin.validator.edit',$plotlist->FinalUserApprovedRejected->id??'')}}">{{$plotlist->FinalUserApprovedRejected->email??""}}</a>
                          @else
                          {{$plotlist->FinalUserApprovedRejected->email??''}}
                          @endif
                    </div>
                  </div><hr>
                   @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" {{ $plotlist->final_status == 'Approved' ? 'disabled' : ''}}
                class="btn btn-primary FinalSubmitApproval" disabled>Save <i class="fa fa-spinner fa-spin FAspinner d-none"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- final approve module end here -->
<!-- final reject module start here -->
<div class="modal fade" id="Finalreject_remark">
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
                                  <input type="checkbox"  id="Finalplotno{{$plot->plot_no}}"
                                    {{ $plotdata->final_status == 'Rejected' ? 'checked disabled' : ''}}
                                    {{ $plotdata->final_status == 'Approved' ? 'disabled' : ''}}
                                  name="Finalplotno" value="{{$plot->plot_no}}" {{$plot->farmer->final_status_onboarding == 'Approved' ?' ':''}}>&nbsp;
                                  {{ $plotdata->final_status == 'Rejected' ? '(Rejected)' : ''}}{{ $plotdata->final_status == 'Approved' ? '(Approved)' : ''}}
                                  {{ $plotdata->check_update == '1' ? '(Validate)' : ''}}
                            </div>
                            <div class="col">
                                 <select {{ $plotdata->final_status == 'Rejected' ? 'disabled' : ''}} {{ $plotdata->final_status == 'Approved' ? 'disabled' : ''}} id="Finalreasons{{$plotdata->plot_no}}"
                                            data-Finalplot="{{$plotdata->plot_no}}"
                                            name="Finalreasons" class="form-control select2">
                                     <option value="">Select Reasons</option>
                                      @foreach($reject_module as $list)
                                        <option value="{{$list->id}}" {{$plotdata->reason_id == $list->id ? 'Selected' :''}}>{{$list->reasons}}</option>
                                      @endforeach
                                 </select>
                            </div>
                            <div style="margin: 0px 0px 0px 17px;">
                                <label for="Finalreject_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                                <textarea {{ $plotdata->final_status == 'Rejected' ? 'readonly' : ''}}  {{ $plotdata->final_status == 'Approved' ? 'disabled' : ''}}
                                          class="form-control" id="Finalreject_comment{{$plot->plot_no}}"
                                           name="Finalreject_comment" rows="3" cols="50">{{$plot->reject_comment}}</textarea>
                            </div><br>&nbsp;
                          </div>

                          <div class="row">
                                <div class="col mt-2">
                                  {{$plotdata->FinalUserApprovedRejected->name??''}} {{ $plot->finalreject_timestamp ? ' / '.Carbon\Carbon::parse($plot->finalreject_timestamp)->toDayDateTimeString() : '' }}<br>
                                  @if(Auth::user()->hasRole('SuperAdmin'))
                                    <a  target="_blank"  href="{{Route('admin.validator.edit',$plotdata->FinalUserApprovedRejected->id??'')}}">{{$plotdata->FinalUserApprovedRejected->email??""}}</a>
                                  @else
                                  {{$plotdata->FinalUserApprovedRejected->email??''}}
                                  @endif

                                </div>
                                <div class="col">
                                  <button {{ $plotdata->final_status == 'Rejected' ? 'disabled' : ''}} {{ $plotdata->final_status == 'Approved' ? 'disabled' : ''}}
                                           type="button" class="btn btn-primary FinalFarmerReject float-right"
                                           data-Finalrejectplot="{{$plotdata->plot_no}}" style="margin-top: 5px;">Save plot no {{$plotdata->plot_no}}
                                           <i id="FRspinner{{$plotdata->plot_no}}" class="fa fa-spinner fa-spin FRspinner d-none"></i></button>
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
<!-- final reject module end here -->
@stop
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js" integrity="sha512-E4rXB8fOORHVM/jZYNCX2rIY+FOvmTsWJ7OKZOG9x/0RmMAGyyzBqZG0OGKMpTyyuXVVoJsKKWYwbm7OU2klxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{asset('vendor/photoviewer/dist/photoviewer.min.js') }}"></script>
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

})($);

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
                  url:"{{url('admin/farmers/status/')}}/"+'onboarding/{{$plot->farmer->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$plot->farmer->no_of_plots}}"},
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
		  }else{
		     $('.Aspinner').addClass('d-none');
             $(".SubmitApproval").prop('disabled', false);
		  }//if end of confirmation
		})//swal end
});

 $(".FarmerReject").click(function() {
    var plotno = $(this).attr("data-rejectplot");
    var reasons = $('#reasons'+plotno+' option:selected').val();
    var rejectcomment = $('#reject_comment'+plotno).val();
    $(".FarmerReject").prop('disabled', true);
    $('#Rspinner'+plotno).removeClass('d-none');
    if(!reasons.length > 0){
         console.log('to reasons');
        $('#Rspinner'+plotno).addClass('d-none');
        $(".FarmerReject").prop('disabled', false);
        return false;
    }
    if(!$('#plotno' + plotno).is(":checked")){
         console.log('to plotno');

        $('#Rspinner'+plotno).addClass('d-none');
        $(".FarmerReject").prop('disabled', false);
        return false;
    }

    console.log('to url');
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
                  url:"{{url('admin/farmers/status/')}}/"+'reject'+'/'+'{{$plot->farmer->farmer_uniqueId}}',
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
		  }else{
		      $(".FarmerReject").prop('disabled', false);
              $('#Rspinner'+plotno).addClass('d-none');
		  }//if end of confirmation
		})//swal end
});

// process for final approval js
$("#Finalonboarding").click(function() {
    $(".FinalSubmitApproval").prop('disabled', false);
});



$(".DeleteBtn").click(function() {
    console.log('DeleteBtn');
    // $(".DeleteBtn").prop('disabled', true);
    $('#DelSpin').removeClass('d-none');


    return false;
    Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, Delete it!'
		}).then((result) => {
		  if (result.value == 1) {
                $.ajax({
                  type:'post',
                  url:"{{url('admin/farmers/final/status/')}}/"+'finalonboarding/{{$plot->farmer->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$plot->farmer->no_of_plots}}"},
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
		  }else{//if end of confirmation
		      $('.FAspinner').addClass('d-none');
              $(".FinalSubmitApproval").prop('disabled', false);
		  }
		})//swal end
});



$(".FinalSubmitApproval").click(function() {
    $(".FinalSubmitApproval").prop('disabled', true);
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
                  url:"{{url('admin/farmers/final/status/')}}/"+'finalonboarding/{{$plot->farmer->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$plot->farmer->no_of_plots}}"},
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
		  }else{//if end of confirmation
		      $('.FAspinner').addClass('d-none');
              $(".FinalSubmitApproval").prop('disabled', false);
		  }
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
                 url:"{{url('admin/farmers/final/status/')}}/"+'finalreject'+'/'+'{{$plot->farmer->farmer_uniqueId}}',
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
         $(".FinalFarmerReject").prop('disabled', false);
         $('#FRspinner'+plotno).addClass('d-none');
     }
   })//swal end
});

</script>
@stop
