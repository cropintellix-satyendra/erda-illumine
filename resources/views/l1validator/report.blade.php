{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Report</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Report</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Report</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-filter" action="{{url('admin/app/settings/update/')}}" method="post" enctype="multipart/form-data">
                              <div class="row">
                                   <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Select Modules <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="modules" id="modules" onchange="changestatus(this.value)" required>
                                                <option value="" >Select Modules</option>
                                                <option value="Onboarding">Onboarding</option>
                                                <option value="CropData">CropData</option>
                                                <option value="PipeInstallation">PipeInstallation</option>
                                                <option value="Aeration">Aeration</option>
                                                <option value="Benefit">Benefit</option>
                                            </select>
                                      </div>
                                  </div>
                                  @if(!auth()->user()->hasRole('L-1-Validator') && !auth()->user()->hasRole('L-2-Validator'))
                                      <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                          <label>Select level <span class="text-danger">*</span></label>
                                          <div class="input-group">
                                                <select class="form-control" name="level" id="level" required>
                                                    <option value="">Select Level</option>
                                                    <option value="All">All</option>
                                                    <option value="L1-Validator">L1-Validator</option>
                                                    <option value="L2-Validator">L2-Validator</option>
                                                </select>
                                          </div>
                                      </div>
                                  @endif
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status </label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" id="status"required>
                                                <option value="">Select Status</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Rejected">Rejected</option>
                                            </select>
                                      </div>
                                  </div>
                            </div>
                            <hr>
                            <h4>Filter</h4>
                            <div class="row">                                
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Date Range </label>
                                      <div class="input-group">
                                            <!-- <div class="input-daterange input-group" data-date-format="dd M, yyyy"  data-date-autoclose="true"  data-provide="datepickers"> -->
                                                <input type="date" class="datepicker form-control start_date" name="start" placeholder="From"/>
                                                <input type="date" class="datepicker form-control end_date" id="end_date" name="end" placeholder="To"/>
                                            <!-- </div> -->
                                      </div>
                                  </div>
                                 
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>States </label>
                                      <div class="input-group">
                                            <select id="states" onchange="FetchDistrict(this.value)" name="state" class="form-control select2">
                                                    <option value="">States</option>
                                                    @if($states)
                                                    @foreach($states as $state)
                                                        <option value="{!! $state->id !!}">{!! $state->name !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                      </div>
                                  </div>

                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Districts </label>
                                      <div class="input-group">
                                           <select id="districts" onchange="FetchBlock(this.value)"  name="district" class="form-control select2">
                                                    <option value="">Districts</option>
                                                    @if($districts)
                                                    @foreach($districts as $district)
                                                    <option value="{!! $district->id !!}">{!! $district->district !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                      </div>
                                  </div>

                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Taluka </label>
                                      <div class="input-group">
                                            <select id="talukas" onchange="FetchPanchayat(this.value)" name="taluka" class="form-control select2">
                                                    <option value="">Taluka</option>
                                                    @if($talukas)
                                                    @foreach($talukas as $taluka)
                                                    <option value="{!! $taluka->id !!}">{!! $taluka->taluka !!}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                      </div>
                                  </div>

                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Surveyor </label>
                                      <div class="input-group">
                                            <select id="executive_onboarding" name="executive_onboarding" class="form-control select2">
                                                    <option value="">Select Executive</option>
                                                    @if($onboarding_executive)
                                                    @foreach($onboarding_executive as $excutive)
                                                        <option value="{{$excutive->surveyor_id}}">{{$excutive->surveyor_name}}</option>
                                                    @endforeach
                                                    @endif
                                            </select>
                                      </div>
                                  </div> 
                              </div>
                              <hr>
                               <div class="col-12">
                                <button type="button" class="btn btn-rounded btn-danger filter-remove"><span class="btn-icon-start text-dangers"><i class="fa fa-filter color-danger"></i> </span>Clear</button>
                                    <button class="btn btn-primary mb-2 mr-2 download-excel">Download <i class="fa fa-spinner fa-spin Aspinner d-none"></i></button>                                             
                              </div> 
                              </form>
                          </div>
                      </div>
                  </div>
                </div>
          </div>         
@endsection
@section('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js')}}"></script>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script> 
<script src = "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/js/bootstrap-datetimepicker.min.js"></script>  

<script>
    $('.start_date').change(function() {
        //this function will set min date for end_date date field.
        var date = $(this).val();
        var start_date = new Date(date);
        document.getElementById("end_date").setAttribute("min",moment(start_date).format('YYYY-MM-DD'));   
    });

        function clean(obj){
            //used for cleaning input field data to be used in url for filter purpose.
            for (var propName in obj) {
                if (!obj[propName]) {
                delete obj[propName];
                }
            }
            return obj;
        }

        $('.download-excel').on('click',function(e){
            $(".Aspinner").removeClass('d-none');
             $(".download-excel").prop("disabled",true);
            //when onClick download btn then, function pick input data and first clean it, and then prepare url.
              e.preventDefault();
              var modules=$('select[name="modules"]').val(); 
              var status=$("#status").val();
              var level=$("#level").val();
              if(!modules){
                    Swal.fire('Please select required field!','Error',  'warning')
                    return false;
              }
              var url="{{url('l1/report/download')}}";
              var start_date=$('input[name="start"]').val();
              var end_date=$('input[name="end"]').val();
            //   var season=$('select[name="seasons"]').val();
              var state=$('select[name="state"]').val();
              var district=$('select[name="district"]').val();
              var taluka=$('select[name="taluka"]').val();
              var panchayats=$('select[name="panchayats"]').val();
            //   var village=$('select[name="village"]').val();
              var executive_onboarding =$('select[name="executive_onboarding"]').val();
              var rolename = '{{\Auth::user()->roles->first()->name}}';
              var userid = '{{\Auth::user()->id}}';              
              var parms=$.param(clean({level,modules,status,rolename,userid,executive_onboarding,start_date,end_date,state,district,taluka,panchayats}));
             

            url+=(parms)?'?'+parms:'';
            
            // console.log(url);
        

            // window.open(url, '_blank');
            // return false;
          
                $.ajax({
					url:url,
					success:function(data){
                        $(".Aspinner").addClass('d-none');
                        $(".download-excel").prop("disabled",false);
						Swal.fire('Request Submitted!', data.message, 'success')
					},
                    error: function (jqXHR, textStatus, errorThrown) {
                        $(".Aspinner").addClass('d-none');
                        $(".download-excel").prop("disabled",false);
                        var data = jqXHR.responseJSON;
                        Swal.fire('Error!', data.message, 'warning')
                    }
				});
              
          });

    $('#modules').select2({
        selectOnClose: true
	});
    $('#status').select2({
		selectOnClose: true
	});
    $('#level').select2({
		selectOnClose: true
	});
    $('#seasons').select2({
	  selectOnClose: true
	});
	$('#states').select2({
	  selectOnClose: true
	});
	$('#districts').select2({
	  selectOnClose: true
	});
	$('#talukas').select2({
	  selectOnClose: true
	});
	$('#panchayats').select2({
	  selectOnClose: true
	});
	$('#villages').select2({
	  selectOnClose: true
	});
	$('#farmer_status').select2({
		selectOnClose: true
	});
	$('#executive_onboarding').select2({
		selectOnClose: true
	});

    function FetchDistrict(Id){
        var stateID = Id;//$(this).val();
        if(stateID) {
            $.ajax({
                type:'post',
                url: "{{url('admin/fetch/district')}}/"+stateID,
                dataType: 'Json',
                data: {_token:'{{csrf_token()}}','id':stateID},
                success: function(data) {
                    $('select[name="district"]').empty();
    								$('select[name="district"]').append('<option value="">Select District</option>');
                    $.each(data.district, function(key, value) {
                        $('select[name="district"]').append('<option value="'+ value.id +'">'+ value.district +'</option>');
                    });
                }
            });
        }else{
            $('select[name="district"]').empty();
        }
    }

function FetchBlock(Id){
    var districtID = Id;//$(this).val();
    if(districtID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/block')}}/"+districtID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':districtID},
            success: function(data) {
                $('select[name="taluka"]').empty();
                                $('select[name="taluka"]').append('<option value="">Select Taluka</option>');
                $.each(data.Taluka, function(i, v) {
                    $('select[name="taluka"]').append('<option value="'+ v.id +'">'+ v.taluka +'</option>');
                });
            }
        });
    }else{
        $('select[name="taluka"]').empty();
    }
}

function FetchPanchayat(Id){
    var blockID = Id;//$(this).val();
    if(blockID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/panchayat')}}/"+blockID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':blockID},
            success: function(data) {
                $('select[name="panchayats"]').empty();
                $('select[name="panchayats"]').append('<option value="">Select Panchayat</option>');
                $.each(data.panchayat, function(i, v) {
                    $('select[name="panchayats"]').append('<option value="'+ v.id +'">'+ v.panchayat +'</option>');
                });
            }
        });
    }else{
        $('select[name="panchayats"]').empty();
    }
}



function changestatus(value){
    if(value == 'Onboarding'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('select[name="status"]').append('<option value="Rejected">Rejected</option>');
    }
    if(value == 'CropData'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
    }
    if(value == 'PipeInstallation'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('select[name="status"]').append('<option value="Rejected">Rejected</option>');

    }
    if(value == 'Aeration'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('select[name="status"]').append('<option value="Rejected">Rejected</option>');

    }
    if(value == 'Benefit'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
    }   
}

$('.filter-remove').on('click',function(e){
        e.preventDefault();
        //$('.form-filter select').prop('selectedIndex',0);
        //$(".form-filter select").val('').trigger('change');
        //$(".form-filter select").val('').trigger('change.select2');
        $(".form-filter select").val("").trigger('change');
        $('#districts').append('<option value="">Districts</option>');
        $('#talukas').append('<option value="">Taluka</option>');
        $('#panchayats').append('<option value="">Select Panchayat</option>');
        $('#villages').append('<option value="">Village</option>');
        $(".start_date").val('').trigger('change');
        $(".end_date").val('').trigger('change');

});

</script>
@stop
