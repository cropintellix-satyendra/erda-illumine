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
              $prev=\App\Models\FarmerCropdata::select('id','farmer_plot_uniqueid','plot_no')->where('status','Approved')->where('l2_status','Approved')->where('id','<',$crop_data_detail->id)->orderBy('id','desc')
              ->when(request(),function($q){
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
              $next=\App\Models\FarmerCropdata::select('id','farmer_plot_uniqueid','plot_no')->where('status','Approved')->where('l2_status','Approved')->where('id','>',$crop_data_detail->id)->orderBy('id','asc')
              ->when(request(),function($q){
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
          <a style="color: red;" href="{{ url('').'/'.$rolename.'/view/l2/approved/cropdata/plot/'.$prev->farmer_uniqueId}}" class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Previous</a>          
          @endif
          @if($next)
          <a style="color: red;" href="{{ url('').'/'.$rolename.'/view/l2/approved/cropdata/plot/'.$next->farmer_uniqueId}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a> 
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
                                <a style="width: 31%;" href="" target="_blank" class="btn btn-status m-b-0 mr-3"><span class="btn-txt">Polygon</span></a>
                              </div>
                              <div class="row mb-3">
                                  <a style="width: 31%;" href="{{url('l2/pipeinstallation/plot/'.$plot->farmer_plot_uniqueid)}}" target="_blank" class="btn btn-status{{$check_pipedata ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                                <a style="width: 30%;" href="{{url('l2/awd-captured/plot/'.$plot->farmer_plot_uniqueid)}}" target="_blank" class="btn btn-status{{$plot->awd_form ? '-done' : ' disabled'}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
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
                              <button style="width: 26%;"
                                  data-toggle="modal" data-target="#ApproveModal"
                                  class="btn btn-success ApproveBtn m-b-0 mr-3 d-none" {{-- below code is to disable button if --}}
                                    >
                                    Approve
                                    <i class="fa fa-spinner fa-spin Aspinner"></i>
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
                @if($cropdata->count()>0)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            @include('farmer_detail_extends.crop_data')
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
                     1. <strong>Farmer Onboarding</strong>
                    </div>
                    <div class="col">

                    </div>
                  </div>
                  <div class="row">
                    <div class="col mt-2">
                      <input type="checkbox"  id="onboarding"
                      {{ $plot->final_status_onboarding == 'Rejected' ? 'disabled' : ''}}
                      {{ $plot->final_status_onboarding == 'Approved' ? 'checked disabled' : ''}}
                      name="onboarding" value="{{$plot->plot_no}}" {{$plot->final_status_onboarding == 'Approved' ?' ':''}}>
                      <label  title="{{ $plot->final_status_onboarding == 'Rejected' ? 'Rejected' : ''}}{{ $plot->final_status_onboarding == 'Approved' ? 'Approved' : ''}}{{ $plot->final_status_onboarding == 'Pending' ? 'Pending' : ''}}"
                          for="onboarding"
                          style="margin-right: 11px;">Plot no {{$plot->plot_no}}</label>&nbsp;
                      <div style="margin: 0px 0px 0px 17px;">
                            <label for="approve_comment" style="margin-right: 11px;">Comment</label>&nbsp;
                            <textarea {{ $plot->final_status_onboarding == 'Approved' ? 'readonly' : ''}}  {{ $plot->final_status_onboarding == 'Rejected' ? 'disabled' : ''}}
                                      class="form-control" id="approve_comment{{$plot->plot_no}}"
                                       name="approve_comment" rows="3" cols="50">{{$plot->approve_comment}}</textarea>
                        </div><br>&nbsp;
                    </div>
                    <div class="col">
                       {{$plot->FinalUserApprovedRejected->name??''}}   / {{ Carbon\Carbon::parse($plot->appr_timestamp)->toDayDateTimeString() }} <br>
                          @if(Auth::user()->hasRole('SuperAdmin'))
                            <a  target="_blank"  href="{{Route('admin.validator.edit',$plot->FinalUserApprovedRejected->id??'')}}">{{$plot->FinalUserApprovedRejected->email??""}}</a>
                          @else
                          {{$plot->FinalUserApprovedRejected->email??''}}
                          @endif
                    </div>
                  </div><hr>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-dismiss="modal">Close</button>
                <button type="button" {{ $plot->final_status_onboarding == 'Approved' ? 'disabled' : ''}}
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
                return $.get("{!! url('') !!}"+'/{{$rolename}}/view/l2/cropdata/search/'+'{{$crop_data_detail->l2_status}}', { query: query }, function (data) {
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
                    var rolename = '{{$rolename}}';
                    return '<div><a href="{{ url("/")}}/'+rolename+'/view/l2/approved/cropdata/plot/'+data.farmer_plot_uniqueid+'/'+data.plot_no+'"><strong>' + data.farmer_plot_uniqueid + '</strong> - ' + data.status + '</a></div>';
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

})($);

$('.EditCropData').click(function () {
  $('#CropData input[type=text]').removeAttr('readonly');
  $('.UpdateCropData').removeClass('d-none');
  $('.CancelCropData').removeClass('d-none');
  $("#season").removeAttr('disabled');
  $("#crop_variety").removeAttr('disabled');
})
$('.CancelCropData').click(function () {
  $('#CropData input[type=text]').attr('readonly','readonly');
  $("#season").prop("disabled", true);
  $("#crop_variety").prop("disabled", true);
  $('.UpdateCropData').addClass('d-none');
  $('.CancelCropData').addClass('d-none');
})

$('.UpdateCropData').click(function(){

    if(!$('#season option:selected').val()){
       $('.season_req').removeClass('d-none');
       return false;
    }


    if(!$('#crop_variety option:selected').val()){
       $('.crop_variety_req').removeClass('d-none');
       return false;
    }

    // if(!$('input[name=mobile]').val()){
    //    $('.mobilereq').removeClass('d-none');
    //    return false;
    // }

    $('.Updatespinner').removeClass('d-none');
    // $('.UpdateCropData').prop('disabled', true);

    

    $.ajax({
      type:'post',
      url:"{{url('l1/pending/cropdata/update/')}}/"+'{{$plot->farmer_plot_uniqueid}}',
      data: {_token:'{{csrf_token()}}',method:'post',crop_variety:$('#crop_variety option:selected').val(),
                                                     season:$('#season option:selected').val()},
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
                  url:"{{url('admin/farmers/status/')}}/"+'onboarding/{{$plot->farmer_uniqueId}}',
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
