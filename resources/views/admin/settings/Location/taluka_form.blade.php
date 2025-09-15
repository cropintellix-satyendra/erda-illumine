{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
@php
$editing = isset($taluka);
$Method  = isset($method);
@endphp
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>{{ $editing ? 'Update' : 'Create'}} Taluka/Tehsil</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Taluka/Tehsil</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Taluka/Tehsil</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide"
                                  action="{{$editing ? url('admin/taluka/edit/'.$taluka->id) : url('admin/taluka/talukastore')}}"
                                  method="post">
                              @csrf
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>State <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" id="state_id" onchange="FetchDistrict(this.value)" name="state_id" required>
                                               <option value="">Select State</option>
                                            @foreach($States as $state)
                                                <option value="{{$state->id}}" {{ $editing ? $taluka->state_id == $state->id ? 'selected' :'' : ''}}>{{$state->name}}</option>
                                            @endforeach
                                          </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Districts <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" onchange="FetchBlock(this.value)" id="district_id" name="district_id" required>
                                              <option value="">Select District</option>
                                            @foreach($Districts as $district)
                                                <option value="{{$district->id}}" {{ $editing ? $taluka->district_id == $district->id ? 'selected' :'' : ''}}>{{$district->district}}</option>
                                            @endforeach
                                          </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="taluka">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="taluka" name="taluka"
                                               value="{{ old('price', ($editing ? $taluka->taluka : ''))}}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control" name="status" required>
                                              <option value="1" {{$editing ? $taluka->status==1 ? 'selected' : '' : ''}}>Enable</option>
                                              <option value="0" {{$editing ? $taluka->status==0 ? 'selected' : '' : ''}}>Disable</option>
                                          </select>
                                    </div>
                                </div>
                              </div>
                              <div class="col-12">
                                  <a href="{!! route('admin.taluka').'#taluka' !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
                                  <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button> &nbsp;
                              </div>
                              </form>
                          </div>
                      </div>
                  </div>
                </div>
          </div>
@endsection
@section('scripts')
<script src="{{ asset('vendor/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
<script>
$('#state_id').select2({
  selectOnClose: true
});

$('#district_id').select2({
  selectOnClose: true
});
jQuery(".form-valide").validate({
    rules: {
        "taluka": {
            required: !0,
            minlength: 3
        },
    },
    messages: {
        "taluka": {
            required: "Please enter a taluka",
            minlength: "Your taluka must consist of at least 3 characters"
        },
    },
    ignore: [],
    errorClass: "invalid-feedback animated fadeInUp",
    errorElement: "div",
    errorPlacement: function(e, a) {
        jQuery(a).parents(".form-group > div").append(e)
    },
    highlight: function(e) {
        jQuery(e).closest(".form-group").removeClass("is-invalid").addClass("is-invalid")
    },
    success: function(e) {
        jQuery(e).closest(".form-group").removeClass("is-invalid"), jQuery(e).remove()
    },
});
</script>
<script>
$(function(){
  //for edit purpose only
  FetchDistrict({{ $editing ? $taluka->state_id : ''}});//fetch district
});
function FetchDistrict(Id){
    var stateID = Id;
    if(stateID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/district')}}/"+stateID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':stateID},
            success: function(data) {
                $('select[name="district_id"]').empty();
                $.each(data.district, function(key, value) {
                    var districtId = '{{ $editing ? $taluka->district_id : ' ' }}';
                        if(districtId == value.id){
                            var isSelected = 'Selected';
                        }else{
                            var isSelected = ' ';
                        }
                    $('select[name="district_id"]').append('<option value="'+ value.id +'" '+isSelected+'>'+ value.district +'</option>');
                });
            }
        });
    }else{
        $('select[name="district_id"]').append('<option value="">Select District</option>');
    }
}
</script>
@stop
