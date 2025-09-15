{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>User Create</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Create Users</a></li>
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
                            <form action="{{route('admin.users.store')}}" method="post">
                              @csrf
                              @method('POST')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" name="name" value="{{ old('name')}}" id="name" required>

                                      </div>
                                  </div>
                                  {{--<div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Email <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email')}}" name="email" id="email">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div> --}}
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Mobile <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                  name="mobile" value="{{ old('mobile')}}" class="form-control" id="mobile" required maxlength="10">
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Company <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" name="company_code" value="{{ old('company_code')}}" class="form-control" id="company_code" required minlength="6" maxlength="6">
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
                                          <select class="form-control select2" onchange="displayLocation(this.value)" id="roles" name="roles" required>
                                              <option value="">Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{$role->id}}">{{$role->name}}</option>
                                                @endforeach
                                          </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 StateClass d-none">
                                    <label>State <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" id="state_id" onchange="FetchDistrict(this.value)" name="state_id[]">
                                               <option value="">Select State</option>
                                            @foreach($States as $state)
                                                <option value="{{$state->id}}">{{$state->name}}</option>
                                            @endforeach
                                          </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 DistrictClass d-none">
                                    <label>Districts <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" id="district_id" name="district_id[]">
                                              <option value="">Select District</option>
                                            @foreach($Districts as $district)
                                                <option value="{{$district->id}}">{{$district->district}}</option>
                                            @endforeach
                                          </select>
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
          </div>
@endsection
@section('scripts')
<script>
$('#roles').select2({
  selectOnClose: true
});

$('#state_id').select2({
  selectOnClose: true
});

$('#district_id').select2({
  selectOnClose: true
});

function displayLocation(Id){
    if(Id == 2){
        $('.StateClass').removeClass('d-none');
        $('.DistrictClass').removeClass('d-none');
    }else{
        $('.StateClass').addClass('d-none');
        $('.DistrictClass').addClass('d-none');
    }
}

function FetchDistrict(Id){
    var stateID = Id;
    if(stateID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/district')}}/"+stateID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':stateID},
            success: function(data) {
                $('select[id="district_id"]').empty();
                $.each(data.district, function(key, value) {
                    $('select[id="district_id"]').append('<option value="'+ value.id +'">'+ value.district +'</option>');
                });
            }
        });
    }else{
        $('select[name="district_id"]').append('<option value="">Select District</option>');
    }
}

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
