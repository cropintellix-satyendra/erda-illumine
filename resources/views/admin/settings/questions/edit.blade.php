{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Edit Seasons</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Questions</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                {{-- <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Questions</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide" action="{{route('admin.season.update',$season->id)}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                      <label for="name">Question text <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" id="question_text" value="{{$season->question_text}}" name="question_text" id="question_text">
                                      </div>
                                  </div>
                                  
                               
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" required>
                                                <option value="1" {{$season->status==1 ? 'selected' : ''}}>Enable</option>
                                                <option value="0" {{$season->status==0 ? 'selected' : ''}}>Disable</option>
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
                </div> --}}


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Values</h4>
                            </div>
                            <div class="card-body form-validation">
                                <form class="form-valide" action="{{ route('admin.questions.update', $season->id) }}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                            <label for="name">Values<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="question_text" value="{{ $season->question_text }}" name="question_text" id="question_text">
                                            </div>
                                        </div>
                
                                        <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                            <label>Status <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select class="form-control" name="status" required>
                                                    <option value="1" {{ $season->status == 1 ? 'selected' : '' }}>Enable</option>
                                                    <option value="0" {{ $season->status == 0 ? 'selected' : '' }}>Disable</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="table" id="values_table">
                                                <thead>
                                                    <tr>
                                                        <th>Value</th>
                                                        {{-- <th>Status</th> --}}
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($values as $value)
                                                    <tr>
                                                        <td><input type="text" name="values[]" class="form-control" value="{{ $value->question_value }}" required></td>
                                                        {{-- <td><input type="text" name="values[]" class="form-control" value="{{ $value->question_value }}" required></td> --}}
                                                        <td><button type="button" class="btn btn-sm btn-danger remove-value">Remove</button></td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" id="add_value">Add Value</button>
                
                                    <div class="col-12">
                                        <a href="{!! route('admin.questions.index'); !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
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
            required: "Please enter a seasons",
            minlength: "Your seasons must consist of at least 3 characters"
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
    document.addEventListener('DOMContentLoaded', function () {
        const addValueBtn = document.getElementById('add_value');
        const valuesTable = document.getElementById('values_table').querySelector('tbody');

        addValueBtn.addEventListener('click', function () {
            const newRow = `
                <tr>
                    <td><input type="text" name="values[]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-value">Remove</button></td>
                </tr>
            `;
            valuesTable.insertAdjacentHTML('beforeend', newRow);
        });

        valuesTable.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-value')) {
                event.target.closest('tr').remove();
            }
        });
    });
</script>
@stop
