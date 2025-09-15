{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>User Edit</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Edit Users</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">User</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{route('admin.users.update',$user->id)}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" name="name" value="{{ old('name', $user->name ) }}" id="name" required>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Mobile <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                          name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{$user->mobile}}" id="mobile" required maxlength="10">
                                            @error('mobile')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Company <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" name="company_code" value="{{$user->company_code}}" class="form-control" id="company_code" readonly required maxlength="6">
                                      </div>
                                  </div>
                                  
                                  {{--<div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status</label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" required>
                                                <option value="1" {{$user->status ==1 ? 'selected' : ''}}>Enable</option>
                                                <option value="0" {{$user->status ==0 ? 'selected' : ''}}>Disable</option>
                                            </select>
                                      </div>
                                  </div>--}}
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label for="device_id">Device ID <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" name="device_id" value="{{$user->device->device_id??""}}" class="form-control" id="device_id" readonly>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Role Assigned :</label>
                                      <div class="input-group">
                                        <p><strong>{{ $user->roles->count() > 0 ? $user->roles->first()->name : '-' }}</strong></p>
                                       </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <div class="row">
                                        <div class="col-sm">
                                          <div class="input-group">
                                            <label><b>Manufacture:</b></label>
                                            <p>{{ $user->device->device_manufacturer??'-' }}</p>
                                           </div>
                                        </div>
                                        <div class="col-sm">
                                          <div class="input-group">
                                            <label><b>Devices name:</b></label>
                                            <p>{{ $user->device->devicename??'-' }}</p>
                                           </div>
                                        </div>
                                        <div class="col-sm">
                                          <div class="input-group">
                                            <label><b>Release </b>:</label>
                                            <p>{{ $user->device->released??'-' }}</p>
                                           </div>
                                        </div>
                                        <div class="col-sm">
                                          <div class="input-group">
                                            <label><b>Version code </b>:</label>
                                            <p>{{ $user->device->versioncode??'-' }}</p>
                                           </div>
                                        </div>
                                        <div class="col-sm">
                                          <div class="input-group">
                                            <label><b>Version name </b>:</label>
                                            <p>{{ $user->device->versionname??'-' }}</p>
                                           </div>
                                        </div>
                                        <div class="col-sm">
                                          <div class="input-group">
                                            <label><b>IP </b>:</label>
                                            <p>{{ $user->device->ip??'-' }}</p>
                                           </div>
                                        </div>
                                      </div>
                                  </div>
                                   <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>States</label>
                                      <div class="input-group">
                                          <input type="text" name="state_id" value="{{$user->state->name??"NA"}}" class="form-control" id="state_id" readonly required>
                                          {{-- 
                                                <select class="form-control" name="state_id"  id="state_id">
                                                    <option value="">Select State</option>
                                                    @foreach($states as $state)
                                                        <option value="{{$state->id}}" {{$state->id == $user->state_id ? 'Selected' : ''}}>{{$state->name}}</option>
                                                    @endforeach
                                                </select> --}}
                                          </div>
                                   </div>
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.users.index')}}" class="btn btn-danger mb-2 float-right">Cancel</a>
                                 <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                              </div>
                              </form>
                          </div>
                      </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Change password</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{url('admin/user/change/password',$user->id)}}" method="post">
                                  @csrf
                                  @method('PUT')
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
                                 <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>

                                  </div>

                              </div>
                            </form>
                         </div>
                      </div>
                      </div>
                  </div>


                <div class="row">
                  <div class="col-12">
                      <div class="card">
                      <div class="row">
                        @if($user->status == '1')
                          <a href="{{url('admin/user/disable',$user->id)}}" class="btn btn-danger col-2 mb-2">Disable</a> &nbsp;
                        @elseif($user->status == '0')
                         <a href="{{url('admin/user/enable',$user->id)}}" class="btn btn-success col-2 mb-2">Enable</a> &nbsp;
                        @endif
                        <a href="{{url('admin/user/remove/deviceid',$user->id)}}" class="btn btn-info col-2 mb-2">Clear DeviceId</a> &nbsp;
                      </div>
                      </div>
                  </div>
                </div>


          </div>
@endsection
@section('scripts')
<script>
// $('#state_id').select2({
//   selectOnClose: true
// });


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
