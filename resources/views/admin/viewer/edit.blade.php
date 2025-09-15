{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Viewer Edit</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Create Viewer</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Viewer</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{route('admin.viewer.update',$viewer->id)}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" name="name" value="{{ old('name', $viewer->name ) }}" id="name" required>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Email <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{$viewer->email}}" name="email" id="email">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Mobile <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                          name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{$viewer->mobile}}" id="mobile" required maxlength="10">
                                            @error('mobile')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Password </label>
                                      <div class="input-group">
                                          <input type="text" name="password" class="form-control" id="password" minlength="6">
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status</label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" required>
                                                <option value="1" {{$viewer->status ==1 ? 'selected' : ''}}>Enable</option>
                                                <option value="0" {{$viewer->status ==0 ? 'selected' : ''}}>Disable</option>
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>State <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select multiple class="form-control select2  @error('state') is-invalid @enderror" id="state_id" {{--onchange="FetchDistrict(this.value)" --}}  name="state[]">
                                          <option value="" disabled>--State--</option>
                                          @foreach($States as $state)
                                                <option value="{{$state->id}}" {{ (in_array($state->id,explode(',',$viewer_location->state ??'')))?'selected':'' }}>{{$state->name}}</option>
                                            @endforeach
                                          </select>
                                            @error('state')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                    </div>
                                </div>

                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Role Assigned :</label>
                                      <div class="input-group">
                                        <p><strong>{{ $viewer->roles->count() > 0 ? $viewer->roles->first()->name : '-' }}</strong></p>
                                       </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.viewer.index')}}" class="btn btn-danger mb-2 float-right">Cancel</a>
                                 <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                              </div>
                            </form>
                          </div>
                      </div>
                  </div>
                </div>
          </div>
@endsection
@section('scripts')
<script>
function viewPassword(){
  var passwordInput = document.getElementById('password');
  var passStatus = document.getElementById('pass-status');
  if (passwordInput.type == 'password'){
    passwordInput.type='text';
    passStatus.className='fa fa-eye';
  }
  else{
    passwordInput.type='password';
    passStatus.className='fa fa-eye-slash';
  }
}

function viewCnfPassword(){
  var passwordInput = document.getElementById('password_confirmation');
  var passStatus = document.getElementById('cnfpass-status');
  if (passwordInput.type == 'password'){
    passwordInput.type='text';
    passStatus.className='fa fa-eye';
  }
  else{
    passwordInput.type='password';
    passStatus.className='fa fa-eye-slash';
  }
}

function validatePassword(){
    var password = document.getElementById("password").value;
    var confirm_password = document.getElementById("password_confirmation").value;
    if(confirm_password){
        if(password != confirm_password) {
          $(".Submitform").attr("disabled", true);
          $(".FDcnfpassword").removeClass('d-none');
          $("#password_confirmation").addClass('is-invalid');

      } else {
            $(".Submitform").attr("disabled", false);
            $(".FDcnfpassword").addClass('d-none');
            $("#password_confirmation").removeClass('is-invalid');
      }
    }
}

$('#roles').select2({
  selectOnClose: false
});
$('#state_id').select2({
   allowClear: true
});

</script>
@stop
