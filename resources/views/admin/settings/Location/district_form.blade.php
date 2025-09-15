{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
@php
$editing = isset($District);
$Method  = isset($method);
@endphp
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>{{ $editing ? 'Update' : 'Create'}} District</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">District</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">District</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide"
                                  action="{{$editing ? url('admin/district/edit/'.$District->id) : url('admin/district/districtstore')}}"
                                  method="post">
                              @csrf
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                      <label for="district">Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" id="district" name="district"
                                                id="district" value="{{ old('price', ($editing ? $District->district : ''))}}">
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>State <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control select2" id="state_id" name="state_id" required>
                                              @foreach($States as $state)
                                                <option value="{{$state->id}}" {{ $editing ? $District->state_id == $state->id ? 'selected' :'' : ''}}>{{$state->name}}</option>
                                              @endforeach
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" required>
                                                <option value="1" {{$editing ? $District->status==1 ? 'selected' : '' : ''}}>Enable</option>
                                                <option value="0" {{$editing ? $District->status==0 ? 'selected' : '' : ''}}>Disable</option>
                                            </select>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                  <a href="{!! route('admin.settings.Location.district_list').'#district' !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
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
$('#state').select2({
  selectOnClose: true
});
jQuery(".form-valide").validate({
    rules: {
        "district": {
            required: !0,
            minlength: 3
        },
    },
    messages: {
        "district": {
            required: "Please enter a district",
            minlength: "Your district must consist of at least 3 characters"
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
@stop
