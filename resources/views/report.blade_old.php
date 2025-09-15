{{-- Extends layout --}}
@extends('layout.default')
@push('head')
<style>
    .category-btn {
        width: 100%;
        padding: 10px 20px;
        text-align: center;
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .category-btn:hover {
        cursor: pointer;
    }

    .category-btn.active {
        background-color: #17a2b8;
        color: #fff;
        border-color: #17a2b8;
    }

    .add-category-btn {
        margin-bottom: 10px;
    }

    .subcategory-table {
        display: none; /* Hide subcategory table by default */
    }
</style>
@endpush
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
                            <li class="breadcrumb-item"><a href="javascript:void(0)">{{\Auth::user()->roles->first()->name}}</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Report</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="#" class="btn btn-info category-btn active" id="categoryBtn">Report Module wise</a>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <a href="{{route('reportcount')}}" class="btn btn-info category-btn" id="subcategoryBtn">Report Count wise</a>
                                </div>
                            </div>
                        </div>
                        
                          <div class="card-body form-validation">
                            <form class="form-filter" action="{{url('admin/app/settings/update/')}}" method="post" enctype="multipart/form-data">
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Organizations<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select id="organization" name="organization" class="form-control">
                                            <option value="">Organizations</option>
                                            @if($organizations)
                                            @foreach($organizations as $organization)
                                            <option value="{!! $organization->id !!}">{!! $organization->company !!}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                   <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Select Modules <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="modules" id="modules" onchange="changestatus(this.value)">
                                                <option value="" >Select Modules</option>
                                                <option value="Onboarding">Onboarding</option>
                                                <option value="CropData">CropData</option>
                                                <option value="Polygon">Polygon</option>
                                                <option value="PipeInstallation">PipeInstallation</option>
                                                <option value="Aeration">Aeration</option>
                                                {{-- <option value="Benefit">Benefit</option> --}}
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
                                                    <!--<option value="L1-Validator">L1-Validator</option>-->
                                                    <option value="L2-Validator">L2-Validator</option>
                                                </select>
                                          </div>
                                      </div>
                                      <div id="l2_validator_div" class="col-md-6 col-xl-3 col-xxl-6 mb-3 d-none">
                                            <label> L2 Validator </label>
                                            <div class="input-group">
                                                <select id="l2_validator" name="l2_validator" class="form-control select2">
                                                        <option  value="">Select L2 validator</option>
                                                        @if($l2_validators)
                                                        @foreach($l2_validators as $list)
                                                            <option value="{!! $list->id !!}">{!! $list->name !!}</option>
                                                        @endforeach
                                                        @endif
                                                </select>
                                            </div>
                                        </div>  
                                        <div id="l1_validator_div" class="col-md-6 col-xl-3 col-xxl-6 mb-3 d-none">
                                            <label> L1 Validator </label>
                                            <div class="input-group">
                                                <select id="l1_validator" name="l1_validator" class="form-control select2">
                                                        <option  value="">Select L1 validator</option>
                                                        @if($l1_validators)
                                                        @foreach($l1_validators as $list)
                                                            <option value="{!! $list->id !!}">{!! $list->name !!}</option>
                                                        @endforeach
                                                        @endif
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                  <div id="status_div" class="col-md-6 col-xl-3 col-xxl-6 mb-3 
{{auth()->user()->hasRole('L-2-Validator') ? '' : 'd-none'}}">
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
                                  {{-- <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 TypeDownload d-none">
                                      <label>Type </label>
                                      <div class="input-group">
                                            <select class="form-control" name="type_download" id="type_download"required>
                                                <option value="">Select type</option>
                                                <option value="Excel">Excel</option>
                                                <option value="Geojson">GEOJSON</option>
                                            </select>
                                      </div>
                                  </div> --}}
                                  <div id="report_type_div" class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Report Type </label>
                                    <div class="input-group">
                                        <select class="form-control" name="report_type" id="report_type">
                                            <option value="">Select type</option>
                                            <option value="Farmer_wise">Farmer Wise</option>
                                            <option value="Plot_wise">Plot Wise</option>
                                            <option value="Total_Date_Wise">Total Date Wise</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="aeration_no_container" class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Aeration no.</label>
                                    <div class="input-group">
                                        <select class="form-control" name="aeration_no" id="aeration_no">
                                            <option value="">Select number</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
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

                                  <!-- only for L2 -->
                                  @if(auth()->user()->hasRole('L-2-Validator'))
                                      <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                          <label>Panchayat </label>
                                          <div class="input-group">
                                                <select id="panchayats" name="panchayats" onchange="FetchVillage(this.value)" class="form-control select2">
                                                        <option value="">Select Panchayat</option>
                                                        @if($panchayats)
                                                        @foreach($panchayats as $panchayat)
                                                        <option value="{!! $panchayat->id !!}">{!! $panchayat->panchayat !!}</option>
                                                        @endforeach
                                                        @endif
                                                </select>
                                          </div>
                                      </div>
                                      <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                          <label>Village </label>
                                          <div class="input-group">
                                                <select id="villages" name="village" class="form-control select2">
                                                        <option value="">Village</option>
                                                        @if($villages)
                                                        @foreach($villages as $village)
                                                        <option value="{!! $village->id !!}">{!! $village->village !!}</option>
                                                        @endforeach
                                                        @endif
                                                </select>
                                          </div>
                                      </div>
                                  @endif
                                  <!-- end for L2 -->

                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Surveyor </label>
                                      <div class="input-group">
                                            <select id="executive_onboarding" name="executive_onboarding" class="form-control select2">
                                                    <option value="">Select Executive</option>
                                                    @if($onboarding_executive)
                                                    @foreach($onboarding_executive as $excutive)
                                                        <option value="{{$excutive->id}}">{{$excutive->name}}</option>
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
document.addEventListener('DOMContentLoaded', function () {
    const modulesSelect = document.getElementById('modules');
    const reportTypeSelect = document.getElementById('report_type');
    
    function updateReportTypeOptions() {
        const moduleType = modulesSelect.value;
        const totalDateWiseOption = reportTypeSelect.querySelector('option[value="Total_Date_Wise"]');

        console.log('Module Type:', moduleType);
        console.log('Level:', level);
        console.log('Option Display:', totalDateWiseOption.style.display);

        // Show or hide the 'Total Date Wise' option based on conditions
        if ((moduleType === 'Aeration' || moduleType === 'PipeInstallation')) {
            totalDateWiseOption.style.display = 'block';
        } else {
            totalDateWiseOption.style.display = 'none';
            // Reset selection if the current value is hidden
            if (reportTypeSelect.value === 'Total_Date_Wise') {
                reportTypeSelect.value = '';
            }
        }
    }

    modulesSelect.addEventListener('change', updateReportTypeOptions);
    levelSelect.addEventListener('change', updateReportTypeOptions);

    // Initial check
    updateReportTypeOptions();
});

</script>
<script>
    $(document).ready(function() {
        function changestatus() {
            $('#report_type_div, #aeration_no_container').removeClass('d-none');
            var modules = $('#modules').val();
            var level = $('#level').val();
            var reportType = $('#report_type').val();
            if (modules && level) {
                if (level === 'All') {
                    $('#report_type_div').removeClass('d-none');
                } else if (level === 'L2-Validator') {
                    $('#report_type_div').addClass('d-none');
                }

                if ((modules === 'CropData' || modules === 'Polygon') && level === 'All') {
                    $('#report_type_div').addClass('d-none');
                }

                if (modules === 'Aeration' && level === 'All' && reportType === 'Plot_wise') {
                    $('#aeration_no_container').removeClass('d-none');
                } else {
                    $('#aeration_no_container').addClass('d-none');
                }
            } else {
                $('#report_type_div, #aeration_no_container').addClass('d-none');
            }
        }
        changestatus();
        $('#modules, #level, #report_type').change(function() {
            changestatus();
        });
    });
</script>


<script>
    $('.start_date').change(function() {
        var date = $(this).val();
        var start_date = new Date(date);
        document.getElementById("end_date").setAttribute("min",moment(start_date).format('YYYY-MM-DD'));   
    });

        function clean(obj){
            for (var propName in obj) {
                if (!obj[propName]) {
                delete obj[propName];
                }
            }
            return obj;
        }

        $('.download-excel').on('click', function(e) {
            e.preventDefault();
            $(".Aspinner").removeClass('d-none');
            $(".download-excel").prop("disabled", true);

            var modules = $('select[name="modules"]').val();
            var status = $("#status").val();
            var level = $("#level").val();
            var rolename = '{{\Auth::user()->roles->first()->name}}';
            var userid = '{{\Auth::user()->id}}';
            var url = "";

            // Determine URL based on user role
            if (rolename == 'SuperAdmin') {
                url = "{{url('admin/report/download')}}";
            } else if (rolename == 'L-2-Validator') {
                url = "{{url('l2/report/download')}}";
            } else if (rolename == 'Viewer') {
                url = "{{url('admin/report/download')}}";
            }

            var l2_validator = $('select[name="l2_validator"]').val();
            var l1_validator = $('select[name="l1_validator"]').val();
            var start_date = $('input[name="start"]').val();
            var end_date = $('input[name="end"]').val();
            var state = $('select[name="state"]').val();
            var district = $('select[name="district"]').val();
            var taluka = $('select[name="taluka"]').val();
            var panchayats = $('select[name="panchayats"]').val();
            var village = $('select[name="village"]').val();
            var executive_onboarding = $('select[name="executive_onboarding"]').val();
            var type_download = $('select[name="type_download"]').val();
            var organization = $('select[name="organization"]').val();
            var aeration_no = $('select[name="aeration_no"]').val();
            var report_type = $('select[name="report_type"]').val();
            var report = $('select[name="report"]').val();
            var seasons = $('select[name="seasons"]').val();

            var parms = $.param(clean({
                modules,
                level,
                rolename,
                userid,
                l1_validator,
                l2_validator,
                executive_onboarding,
                status,
                start_date,
                end_date,
                state,
                district,
                taluka,
                panchayats,
                village,
                type_download,
                organization,
                aeration_no,
                report_type,
                report,
                seasons
            }));

            url += (parms) ? '?' + parms : '';

            console.log('Generated URL:', url);

            Swal.fire({
                type: 'warning',
                title: 'Please wait...',
                html: '<i class="fa fa-spinner fa-spin"></i> Your file is being downloaded. Please wait.',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
            });

            $.ajax({
                url: url,
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    $(".Aspinner").addClass('d-none');
                    $(".download-excel").prop("disabled", false);

                    var downloadUrl = window.URL.createObjectURL(data);
                    var link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = `${modules}.xlsx`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(downloadUrl);

                    Swal.fire('Request Submitted!', 'Your file has been downloaded.', 'success');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $(".Aspinner").addClass('d-none');
                    $(".download-excel").prop("disabled", false);

                    var data = jqXHR.responseJSON;
                    Swal.fire('Error!', data ? data.message : 'Something went wrong!', 'error');
                }
            });
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

function FetchVillage(Id){
    var PanchayatID = Id;//$(this).val();
    if(PanchayatID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/village')}}/"+PanchayatID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':PanchayatID},
            success: function(data) {
                $('select[name="village"]').empty();
                                $('select[name="village"]').append('<option value="">Select Village</option>');
                $.each(data.Village, function(i, v) {
                    $('select[name="village"]').append('<option value="'+ v.id +'">'+ v.village +'</option>');
                });
            }
        });
    }else{
        $('select[name="village"]').empty();
    }
}


//below code will switch between dropdown of l1 and l2 for superadmin and viewers
$('#level').change(function(){
    if(this.value == 'All'){
        $("#status_div").addClass('d-none')
        // $("#l1_validator_div").addClass('d-none')
        $("#l2_validator_div").addClass('d-none')
    }else if(this.value == 'L1-Validator'){
        $("#status_div").removeClass('d-none')
        // $("#l1_validator_div").removeClass('d-none')
        $("#l2_validator_div").addClass('d-none')
    }else if(this.value == 'L2-Validator'){
        $("#status_div").removeClass('d-none')
        $("#l2_validator_div").removeClass('d-none')
        // $("#l1_validator_div").addClass('d-none')
    }else{
        // $("#l1_validator_div").addClass('d-none')
        $("#l2_validator_div").addClass('d-none')
    }    
});

function changestatus(value){
    if(value == 'Onboarding'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('select[name="status"]').append('<option value="Rejected">Rejected</option>');
        $('.TypeDownload').addClass('d-none')
    }
    if(value == 'CropData'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('.TypeDownload').addClass('d-none')
    }
    if(value == 'Polygon'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('select[name="status"]').append('<option value="Rejected">Rejected</option>');
        $('.TypeDownload').removeClass('d-none')
    }
    if(value == 'PipeInstallation'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('select[name="status"]').append('<option value="Rejected">Rejected</option>');
        $('.TypeDownload').removeClass('d-none')
    }
    if(value == 'Aeration'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('select[name="status"]').append('<option value="Rejected">Rejected</option>');
        $('.TypeDownload').addClass('d-none')
    }
    if(value == 'Benefit'){
        $('select[name="status"]').empty();
        $('select[name="status"]').append('<option value="">Select Status</option>');                                                
        $('select[name="status"]').append('<option value="Pending">Pending</option>');
        $('select[name="status"]').append('<option value="Approved">Approved</option>');
        $('.TypeDownload').addClass('d-none')
    }   
}

    $('.filter-remove').on('click',function(e){
        e.preventDefault();
        $(".form-filter select").val("").trigger('change');
        $('#districts').append('<option value="">Districts</option>');
        $('#talukas').append('<option value="">Taluka</option>');
        $('#panchayats').append('<option value="">Select Panchayat</option>');
        $('#villages').append('<option value="">Village</option>');
        $(".start_date").val('').trigger('change');
        $(".end_date").val('').trigger('change');
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
    $('#panchayats').select2({
        selectOnClose: true
    });
    $('#villages').select2({
        selectOnClose: true
    });
    $('#l2_validator').select2({
        selectOnClose: true
    });
    $('#l1_validator').select2({
        selectOnClose: true
    });
    $('#type_download').select2({
        selectOnClose: true
    });

</script>
@stop
