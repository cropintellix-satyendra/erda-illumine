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
                        <form class="form-valide" action="{{ route('assign_update',$State->id) }}" method="post">
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
                                    <label for="district">Patta Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="patta_number" placeholder="eg.Patta Number" required >
                                    </div>
                                    <input type="checkbox" class="dynamic-checkbox" name="patta_status" value="1"> Enable
                                </div>

                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">Daag Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="state" name="daag_number" placeholder="eg.Daag Number" required >
                                    </div>
                                    <input type="checkbox" class="dynamic-checkbox" name="daag_status" value="1"> Enable
                                    {{-- <label for="dynamicStatusCheckbox">Enable</label> --}}
                                </div>
                                

                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="district">Khatia Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="state" name="khatian_number"
                                            id="state" placeholder="eg.Khatian Number" required>
                                        </div>
                                        <input type="checkbox" class="dynamic-checkbox" name="khatian_status"  value="1"> Enable
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
