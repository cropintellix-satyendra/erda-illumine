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
                                    <a href="{{ url('admin/report') }}" class="btn btn-info category-btn" id="categoryBtn">Report Module wise</a>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <a href="#" class="btn btn-info category-btn active" id="subcategoryBtn">Report count wise</a>
                                </div>
                            </div>
                        </div>
                        
                          <div class="card-body form-validation">
                            <form class="form-filter" action="{{url('admin/app/settings/update/')}}" method="post" enctype="multipart/form-data">
                            <div class="row">
    <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
        <label>Select Count Report <span class="text-danger">*</span></label>
        <div class="input-group">
            <select class="form-control" name="report2" id="report2">
                <option value="">Select Count Report</option>
                <option value="organization_wise">Organization wise</option>
                <option value="surveyor_wise">Surveyor wise</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 col-xxl-6 mb-3" id="organization_container">
        <label>Organizations<span class="text-danger">*</span></label>
        <div class="input-group">
            <select id="organization2" name="organization2" class="form-control select2">
                <option value="">Organizations</option>
                @if($organizations)
                    @foreach($organizations as $organization)
                        <option value="{{ $organization->id }}">{{ $organization->company }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 col-xxl-6 mb-3" id="year_container">
        <label>Years<span class="text-danger">*</span></label>
        <div class="input-group">
            <select id="years" name="years" class="form-control select2">
                <option value="">Years</option>
                @if($years)
                    @foreach($years as $year)
                        <option value="{{ $year->year }}">{{ $year->year }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 col-xxl-6 mb-3" id="seasons_container">
        <label>Seasons</label>
        <div class="input-group">
            <select id="seasons" name="seasons" class="form-control select2">
                <option value="">Seasons</option>
                @if($seasons)
                    @foreach($seasons as $season)
                        <option value="{{ $season->name }}">{{ $season->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 col-xxl-6 mb-3" id="surveyor_name_container">
        <label>Surveyor name</label>
        <div class="input-group">
            <input type="search" class="form-control" id="surveyor2" placeholder="Search for Surveyor" name="surveyor2">
            <input type="hidden" id="users_id" name="users_id">
        </div>
    </div>
    <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
        <label>Select Modules <span class="text-danger">*</span></label>
        <div class="input-group">
            <select class="form-control" name="modules2" id="modules2">
                <option value="">Select Modules</option>
                <option value="Onboarding">Onboarding</option>
                <option value="Polygon">Polygon</option>
                <option value="PipeInstallation">PipeInstallation</option>
                <option value="Aeration">Aeration</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
        <label>Date Range </label>
        <div class="input-group">
             
                  <input type="date" class="datepicker form-control start_date" name="start" placeholder="From"/>
                  <input type="date" class="datepicker form-control end_date" id="end_date" name="end" placeholder="To"/>
            
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
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
       
        $('#surveyor2').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('search_surveyor') }}",
                    dataType: 'json',
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.name,
                                value: item.name,
                                id: item.id
                            };
                        }));
                    }
                });
            },
            minLength: 1,
            select: function(event, ui) {
                $('#users_id').val(ui.item.id);
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
       const levelSelect = document.getElementById('level');
       const reportTypeField = document.getElementById('reportTypeField');

      
       toggleReportTypeField();

      
       levelSelect.addEventListener('change', toggleReportTypeField);

       function toggleReportTypeField() {
           const selectedValue = levelSelect.value;
           if (selectedValue === 'All') {
               reportTypeField.style.display = 'block';
           } else {
               reportTypeField.style.display = 'none';
           }
       }
   });
</script>
<script>
    $(document).ready(function() {
     
        toggleFields();

       
        $('#report2').change(function() {
            toggleFields();
        });

        function toggleFields() {
            var report = $('#report2').val();
            $('#organization_container').show();
            $('#surveyor_name_container').show();

            if (report === 'organization_wise') {
                $('#surveyor_name_container').hide();
            } else if (report === 'surveyor_wise') {
                $('#organization_container').hide();
            } else {
               
            }
        }
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
                var years = $('select[name="years"]').val();
                var report2 = $('select[name="report2"]').val();
                var modules2 = $('select[name="modules2"]').val();
                var organization2 = $('select[name="organization2"]').val();
                var surveyor2 = $('input[name="users_id"]').val();
            //console.log(surveyor2);
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
                    report_type,
                    report,
                    seasons,
                    years,
                    report2,
                    modules2,
                    organization2,
                    surveyor2

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
                        link.download = `${modules2}.xlsx`;
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
