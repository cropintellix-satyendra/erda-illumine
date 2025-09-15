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
                <h4>Farmer Plot </h4>
            </div>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search...">
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            @php
              if(auth()->user()->hasRole('L-1-Validator')){
                      $prev=\App\Models\FarmerPlot::with('farmer')->where('id','<',$plot->id)->orderBy('id','desc')->whereHas('farmer',function($q){
                          $q->where('onboarding_form','1');
                              $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                              $q->whereIn('state_id',explode(',',$VendorLocation->state));
                              if(!empty($VendorLocation->district)){
                                 $q->whereIn('district_id',explode(',',$VendorLocation->district));
                              }
                              if(!empty($VendorLocation->taluka)){
                                 $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                              }
                        return $q;
                      })->first();
                      $next=\App\Models\FarmerPlot::with('farmer')->where('id','>',$plot->id)->orderBy('id','asc')->whereHas('farmer',function($q){
                          $q->where('onboarding_form','1');
                              $VendorLocation = DB::table('vendor_locations')->where('user_id',auth()->user()->id)->first();
                              $q->whereIn('state_id',explode(',',$VendorLocation->state));
                              if(!empty($VendorLocation->district)){
                                 $q->whereIn('district_id',explode(',',$VendorLocation->district));
                              }
                              if(!empty($VendorLocation->taluka)){
                                 $q->whereIn('taluka_id',explode(',',$VendorLocation->taluka));
                              }
                        return $q;
                      })->first();
              }elseif(auth()->user()->hasRole('L-2-Validator')){
                        $prev=\App\Models\FarmerPlot::with('farmer')->where('id','<',$plot->id)->orderBy('id','desc')->whereHas('farmer',function($q){
                            $q->where('onboarding_form','1');
                          return $q;
                        })->first();
                        $next=\App\Models\FarmerPlot::with('farmer')->where('id','>',$plot->id)->where('final_status',$status)->where('status','Approved')->orderBy('id','asc')->whereHas('farmer',function($q){
                            $q->where('onboarding_form','1');
                          return $q;
                        })->first();
              }else{
                $prev=\App\Models\FarmerPlot::with('farmer')->where('id','<',$plot->id)->orderBy('id','desc')->whereHas('farmer',function($q){
                            $q->where('onboarding_form','1');
                          return $q;
                        })->first();
                $next=\App\Models\FarmerPlot::with('farmer')->where('id','>',$plot->id)->orderBy('id','asc')->whereHas('farmer',function($q){
                    $q->where('onboarding_form','1');
                  return $q;
                })->first();
              }
            @endphp
            @if($prev)
                <a style="color: red;" href="{{ url('admin/farmer/all-farmer/plot').'/'.$prev->id}}"  class="btn btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i>Previous</a>
            @endif
            @if($next)
                <a style="color: red;" href="{{ url('admin/farmer/all-farmer/plot').'/'.$next->id}}" class="btn btn-sm">Next <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-md-4">
            <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                @include('farmer_detail_extends.farmer_info')
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                @include('farmer_detail_extends.farmer_details')
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
                              <a style="width: 30%;" class="active btn btn-status{{$finalfarmers->onboarding_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Farmer Onboarding</span></a>
                              <a style="width: 26%;" class="CropDataShow btn btn-status{{$finalfarmers->cropdata_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Crop data</span></a>
                              <a style="width: 31%;" class="btn btn-status{{$finalfarmers->pipes_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Pipes Installations</span></a>
                            </div>
                            <div class="row mb-3">
                              <a style="width: 30%;" class="btn btn-status{{$finalfarmers->awd_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">AWD Captured</span></a>
                              <a style="width: 26%;" class="FarmerBenefits btn btn-status{{$finalfarmers->benefit_form ? '-done' : ''}} m-b-0 mr-3"><span class="btn-txt">Benefits</span></a>
                              <a style="width: 30%;" class="btn btn-status{{$finalfarmers->other_form ? '-done' : ''}} m-b-0"><span class="btn-txt">Others</span></a>
                            </div>
                            @if(!Auth::user()->hasRole('Viewer') && !Auth::user()->hasRole('L-2-Validator'))
                            <div class="row mb-3">
                                    <a style="width: 30%;" href="{{url('admin/farmers/plot/edit/'.$plot->id.'/'.$plot->farmer_uniqueId)}}"
                                      class="btn btn-info m-b-0 mr-3 EditBtn d-none"
                                       @if($finalfarmers->status_onboarding == 'Approved')
                                        disabled
                                      @elseif($finalfarmers->status_onboarding == 'Rejected')
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
                                    <i class="fa fa-spinner fa-spin Aspinner d-none"></i>
                              </button>
                              <!-- approve end -->
                              <button style="width: 30%;" data-toggle="modal" data-target="#reject_remark"
                                    class="btn btn-danger RejectBtn m-b-0 mr-3 d-none">
                                    Reject
                              </button>
                            </div>
                            @endif
                            <!-- end final approve module -->
                            @if(Auth::user()->hasRole('L-2-Validator'))
                            <div class="row mb-3">
                                    <a style="width: 30%;" href="{{url('admin/farmers/plot/edit/'.$plot->id.'/'.$plot->farmer_uniqueId)}}"
                                      class="btn btn-info m-b-0 mr-3 EditBtn "
                                       @if($finalfarmers->status_onboarding == 'Approved')
                                        disabled
                                      @elseif($finalfarmers->status_onboarding == 'Rejected')
                                        disabled
                                      @else
                                      @endif
                                    >EDIT</a>
                                <!-- end button end -->
                              <button style="width: 26%;"
                                  data-toggle="modal" data-target="#FinalApproveModal"
                                  class="btn btn-success FinalApproveBtn m-b-0 mr-3" {{-- below code is to disable button if --}}
                                    >
                                    {{ $plot->final_status == 'Approved'? 'Approved' :'Approve' }}
                              </button>
                              <!-- approve end -->
                              <button style="width: 30%;" data-toggle="modal" data-target="#Finalreject_remark"
                                    class="btn btn-danger FinalRejectBtn m-b-0 mr-3">
                                    {{ $plot->final_status == 'Rejected'? 'Rejected' :'Reject' }}
                              </button>
                            </div>
                            @endif
                            {{-- <div class="row mb-3">
                              <button style="width: 30%;" href="{{url('admin/farmers/plot/edit/'.$plot->id.'/'.$plot->farmer_uniqueId)}}" class="btn btn-danger m-b-0 mr-3 DeleteBtn">
                                        Delete Plot <i id="DelSpin" class="fa fa-spinner fa-spin d-none"></i></button>
                            </div>--}}
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
                return $.get('{!! url('admin/plot/search') !!}', { query: query }, function (data) {
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
                  url:"{{url('admin/farmers/status/')}}/"+'onboarding/{{$finalfarmers->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$finalfarmers->no_of_plots}}"},
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
                  url:"{{url('admin/farmers/status/')}}/"+'reject'+'/'+'{{$finalfarmers->farmer_uniqueId}}',
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
                  url:"{{url('admin/farmers/final/status/')}}/"+'finalonboarding/{{$finalfarmers->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$finalfarmers->no_of_plots}}"},
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
                  url:"{{url('admin/farmers/final/status/')}}/"+'finalonboarding/{{$finalfarmers->farmer_uniqueId}}',
                  data: {_token:'{{csrf_token()}}',method:'post',plots:plots,TotalPlot:"{{$finalfarmers->no_of_plots}}"},
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
                 url:"{{url('admin/farmers/final/status/')}}/"+'finalreject'+'/'+'{{$finalfarmers->farmer_uniqueId}}',
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
