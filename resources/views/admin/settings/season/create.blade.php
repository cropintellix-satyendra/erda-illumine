{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Create Seasons</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Seasons</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Seasons</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide" action="{{route('admin.season.store')}}" method="post">
                              @csrf
                              @method('POST')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                      <label for="name">Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" id="name" name="name" id="name">
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="month1">Month 1<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="month" class="form-control" id="month1" name="month1">
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="month2">Month 2<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="month" class="form-control" id="month2" name="month2">
                                    </div>
                                </div>
                            @php
                                $years=\App\Models\Year::all();

                            @endphp
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Year Range <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control" name="year" required>
                                            <option selected disabled>----select----</option>
                                            @foreach ($years as $year)
                                            <option value="{{$year->id}}" >{{$year->year}}</option>
                                            @endforeach
                                            
                                          </select>
                                    </div>
                                </div>
                         
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" required>
                                                <option value="1" >Enable</option>
                                                <option value="0" >Disable</option>
                                            </select>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                  <a href="{!! route('admin.season.index'); !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
                                  <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button> &nbsp;
                              </div>
                              </form>
                          </div>
                      </div>
                  </div>
                </div>
          </div>
          {{-- @if(session()->has('success'))
               notyf.success("{{ session()->get('success') }}");
          @endif
          --}}
@endsection
@section('scripts')
<script src="{{ asset('vendor/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
<script>
jQuery(".form-valide").validate({
    rules: {
        "name": {
            required: !0,
            minlength: 3
        },
    },
    messages: {
        "name": {
            required: "Please enter a name",
            minlength: "Your username must consist of at least 3 characters"
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
