{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>L-2 Validator</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">L-2 Validator</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">L-2 Validator</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{route('admin.verifier.store')}}" method="post">
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
                                          <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email')}}" name="email" id="email" autocomplete="off" required>
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
                                                  name="mobile" value="{{ old('mobile')}}" class="form-control" id="mobile" required maxlength="10" required>
                                      </div>
                                  </div>
                                   <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Roles <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2 @error('roles') is-invalid @enderror" {{-- onchange="displayLocation(this.value)" --}}  id="roles" name="roles" required>
                                              <option value="">Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{$role->id}}">{{$role->name}}</option>
                                                @endforeach
                                          </select>
                                        @error('roles')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
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
                                    <label class="text-label">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group transparent-append">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
                                        </div>
                                        <input type="password" class="form-control" id="password_confirmation" onkeyup="validatePassword()" minlength="6" name="password_confirmation" minlength="6" placeholder="Choose a safe one..">
                                        <div class="input-group-append show-pass">
                                            <span class="input-group-text"> <i id="cnfpass-status" class="fa fa-eye-slash" aria-hidden="true" onClick="viewCnfPassword()"></i>
                                            </span>
                                        </div>
                                        <span class="invalid-feedback FDcnfpassword d-none" role="alert">
                                            <strong>Password don't match</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>State <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select multiple class="form-control select2  @error('state') is-invalid @enderror"  id="state_id" onchange="FetchDistrict(this.value)" name="state[]">
                                                <option value="" disabled>Select State</option>
                                            @foreach($States as $state)
                                                <option value="{{$state->id}}">{{$state->name}}</option>
                                            @endforeach
                                          </select>
                                            @error('state')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 districtdiv">
                                    <label>Districts </label>
                                    <div class="input-group">
                                          <select class="form-control select2" multiple {{--onchange="FetchBlock(this.value)"  --}} id="district_id" name="district[]">
                                            @foreach($Districts as $district)
                                                <option value="{{$district->id}}">{{$district->district}}</option>
                                            @endforeach
                                          </select>
                                    </div>
                                </div>
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.verifier.index')}}" class="btn btn-danger mb-2 float-right">Cancel</a>
                                 <button type="submit" class="btn btn-primary mb-2 mr-2 float-right Submitform">Submit</button>
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

$('#state_id').change(function(){
   var role = $("#roles option:selected").val();
   var rolename = $("#role"+role).data("rolename");
    var result = $("#state_id option:selected").map(function() {
      return this.value;
    }).get();
    if(rolename == 'SuperValidator'){
        if(result.length >= 2){
            $('.districtdiv').addClass('d-none');
            $('.blockdiv').addClass('d-none');
        }else{
            $('.districtdiv').removeClass('d-none');
            $('.blockdiv').removeClass('d-none');
        }
    }
});

$('#roles').select2({
  selectOnClose: false
});

$('#state_id').select2({
  selectOnClose: false
});

$('#district_id').select2({
  selectOnClose: false
});

</script>
@stop
