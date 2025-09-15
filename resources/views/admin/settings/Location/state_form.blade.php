{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
    @php
        $editing = isset($State);
        $Method = isset($method);
    @endphp
    <style>
        .select2-drop-active {
            margin-top: -25px;
        }
    </style>
    <div class="container-fluid">
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>{{ $editing ? 'Update' : 'Create' }} State</h4>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">State</a></li>
                </ol>
            </div>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">State</h4>
                    </div>
                    <div class="card-body form-validation">
                        <form class="form-valide"
                            action="{{ $editing ? url('admin/state/update/' . $State->id) : url('admin/state/store') }}"
                            method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="state" name="state"
                                            id="state" value="{{ old('state', $editing ? $State->name : '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">Minimum Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="min_base_value" value="{{old('min_base_value' ,$editing ? $State->min_base_value : '')}}" name="min_base_value"
                                            id="state">
                                    </div>
                                </div>
                                {{-- <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 district">
                                    <label>Minimum Value </label>
                                    <div class="input-group">
                                            <select class="form-control select2" multiple id="land_unit_id" name="land_unit_id[]">
                                            <option value="" disabled>Select Minimum Value</option>
                                            @foreach($minimum as $district)
                                                <option value="{{$district->id}}">{{$district->value}}</option>
                                            @endforeach
                                            </select>
                                    </div>
                                </div> --}}
                                {{-- <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">Units <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="units" value="{{old('units' ,$editing ? $State->units : '')}}" name="units"
                                            id="state">
                                    </div>
                                </div> --}}

                               
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 district">
                                    <label>Lenght Measurement Unit </label>
                                    <div class="input-group">
                                          {{-- <select class="form-control select2" multiple id="land_unit_id" name="land_unit_id">
                                            <option value="" disabled>Select District</option>
                                            @foreach($landunit as $district)
                                                <option value="{{$district->id}}">{{$district->unit}}</option>
                                            @endforeach
                                          </select> --}}

                                          <input type="text" class="form-control" id="lm_units" value="{{old('lm_units' ,$editing ? $State->lm_units : '')}}" name="lm_units"
                                            id="lm_units" readonly style="background: #bbb4b4;">


                                          {{-- <select class="form-control select2" multiple id="land_unit_id" name="land_unit_id">
                                            <option value="" disabled>Select District</option>
                                            @foreach($all_state as $district)
                                                <option value="{{$district->lm_units}}">{{$district->lm_units}}</option>
                                            @endforeach
                                          </select> --}}
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="base_value" value="{{old('base_value' ,$editing ? $State->base_value : '')}}" name="base_value"
                                            id="base_value">
                                    </div>
                                </div>
                                {{-- <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">Max Base Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="max_base_value" value="{{old('max_base_value' ,$editing ? $State->max_base_value : '')}}" name="max_base_value"
                                            id="max_base_value">
                                    </div>
                                </div> --}}

                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">In Hectare<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lm_units" value="{{old('lm_units' ,$editing ? $State->lm_units : '')}}" name="lm_units"
                                            id="lm_units" readonly style="background: #bbb4b4;">
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control" name="status" required>
                                            <option value="1"
                                                {{ $editing ? ($State->status == 1 ? 'selected' : '') : '' }}>Enable</option>
                                            <option value="0"
                                                {{ $editing ? ($State->status == 0 ? 'selected' : '') : '' }}>Disable</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <a href="{!! route('admin.location') . '#district' !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
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
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
     <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script> -->
    <script src="{{ asset('vendor/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script>
        jQuery(".form-valide").validate({
            rules: {
                "State": {
                    required: !0,
                    minlength: 3
                },
            },
            messages: {
                "district": {
                    required: "Please enter a State",
                    minlength: "Your State must consist of at least 3 characters"
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
