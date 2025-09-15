{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Admin Create</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Create Admin</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Admin</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{url('admin/store-admin')}}" method="post">
                              @csrf
                              @method('POST')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" name="name" value="{{ old('name')}}" id="name" required>

                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Email <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email')}}" name="email" id="email">
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
                                                  name="mobile" value="{{ old('mobile')}}" class="form-control" id="mobile" required maxlength="10">
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label class="text-label">Password <span class="text-danger">*</span></span></label>
                                      <div class="input-group transparent-append">
                                          <div class="input-group-prepend">
                                              <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
                                          </div>
                                          <input type="password" class="form-control @error('password') is-invalid @enderror" onkeyup="validatePassword()" id="password" minlength="6" name="password" placeholder="Choose a safe one..">
                                          <div class="input-group-append show-pass">
                                              <span class="input-group-text"> <i class="fa fa-eye-slash" id="pass-status" aria-hidden="true" onClick="viewPassword()"></i>
                                              </span>
                                          </div>
                                          @error('password')
                                              <span class="invalid-feedback" role="alert">
                                                  <strong>{{ $message }}</strong>
                                              </span>
                                          @enderror
                                      </div>
                                  </div>
                                 <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Roles <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" id="roles" name="roles" required>
                                              <option value="">Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{$role->id}}">{{$role->name}}</option>
                                                @endforeach
                                          </select>
                                    </div>
                                </div>
                              </div>
                              <div class="col-12">
                                <a href="{!! url('admin/list/admin') !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
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
$('#roles').select2({
   allowClear: true
});

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

</script>
@stop
