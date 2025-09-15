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
                            <form action="{{route('admin.verifier.update',$verifier->id)}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" name="name" value="{{ old('name', $verifier->name ) }}" id="name" required>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Email <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{$verifier->email}}" name="email" id="email">
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
                                          name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{$verifier->mobile}}" id="mobile" required maxlength="10">
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
                                                <option value="1" {{$verifier->status ==1 ? 'selected' : ''}}>Enable</option>
                                                <option value="0" {{$verifier->status ==0 ? 'selected' : ''}}>Disable</option>
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Role Assigned :</label>
                                      <div class="input-group">
                                        <p><strong>{{ $verifier->roles->count() > 0 ? $verifier->roles->first()->name : '-' }}</strong></p>
                                       </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>State <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select multiple class="form-control select2  @error('state') is-invalid @enderror" id="state_id" onchange="FetchDistrict(this.value)"   name="state[]">
                                          <option value="" disabled>--State--</option>
                                          @foreach($States as $state)
                                                <option value="{{$state->id}}" {{ (in_array($state->id,explode(',',$vendor_location->state??'')))?'selected':'' }}>{{$state->name}}</option>
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
                                          <select class="form-control select2" multiple id="district_id" name="district[]">
                                          <option value="" disabled>--District--</option>
                                            @foreach($Districts as $district)
                                                <option value="{{$district->id}}" {{ (in_array($district->id,explode(',',$vendor_location->district??'')))?'selected':''}}>{{$district->district}}</option>
                                            @endforeach
                                          </select>
                                    </div>
                                </div>
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.verifier.index')}}" class="btn btn-danger mb-2 float-right">Cancel</a>
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
                            <form action="{{url('admin/validator/2/change/password',$verifier->id)}}" method="post">
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
                        @if($verifier->status == '1')
                          <a href="{{url('admin/user/disable',$verifier->id)}}" class="btn btn-danger col-2 mb-2">Disable</a> &nbsp;
                        @elseif($verifier->status == '0')
                         <a href="{{url('admin/user/enable',$verifier->id)}}" class="btn btn-success col-2 mb-2">Enable</a> &nbsp;
                        @endif
                       
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


function FetchDistrict(Id){
    var stateID = Id;
    if(stateID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/district')}}/"+stateID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':stateID},
            success: function(data) {
              $('select[name="district"]').empty();
              $('select[name="district"]').append('<option value="">Select District</option>');
              $.each(data.district, function(key, value) {
                  $('select[name="district"]').append('<option value="'+ value.id +'">'+ value.district +'</option>');
              });
            }
        });
    }else{
        $('select[id="district_id"]').append('<option value="">Select District</option>');
    }
}

</script>
@stop
