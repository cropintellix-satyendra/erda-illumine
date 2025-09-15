{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Organization Edit</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Organization</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Company</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{route('admin.company.update',$user->id)}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label> Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{$user->name}}" name="name" id="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="email" value="{{$user->email}}" id="email" required >
                                    </div>
                                </div>
                                <!-- <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password"  value="{{$user->password}}" id="password" required >
                                    </div>
                                </div> -->
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Organization</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="company" value="{{$company->company}}" id="company" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Organization Code</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="company_code" value="{{$company->company_code}}" id="name" required maxlength="6">
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>State <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" id="state_id" name="state_id" onchange="FetchDistrict(this.value)"  required>
                                               <option value="">Select State</option>
                                            @foreach($States as $state)
                                                <option value="{{$state->id}}" {{ $company->state_id == $state->id ? 'selected' :'' }}>{{$state->name}}</option>
                                            @endforeach
                                          </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 district">
                                    <label>Districts </label>
                                    <div class="input-group">
                                          <select class="form-control select2" multiple id="district_id" name="district[]">
                                            <option value="" disabled>Select District</option>
                                            @foreach($Districts as $district)
                                                @if(in_array($district->id,explode(',',$company->district_id)))
                                                    <option value="{{$district->id}}" {{ (in_array($district->id,explode(',',$company->district_id))) ? 'selected':'' }}>{{$district->district}}</option>
                                                @endif
                                            @endforeach
                                          </select>
                                    </div>
                                </div>
                                <!-- <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Roles <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                      <select class="form-control select2" onchange="displayLocation(this.value)" id="roles" name="roles"  required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div> -->

                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" required>
                                                <option value="1" {{$company->status==1 ? 'selected' : ''}}>Enable</option>
                                                <option value="0" {{$company->status==0 ? 'selected' : ''}}>Disable</option>
                                            </select>
                                      </div>
                                  </div>
                              </div>
                             
                                <label>Term and Condition <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <textarea class="summernote" id="termcond" name="termcond" rows="4" cols="50"> {{$company->termcond}}</textarea>
                                </div>
                                
                              <div class="col-12">
                                <a href="{{route('admin.company.index')}}" class="btn btn-danger mb-2 float-right">Cancel</a>
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

    //   FetchDistrict("{{$company->state_id}}");
$('#district_id').select2({
    selectOnClose: false
});


function FetchDistrict(Id){
    var stateID = Id;
    var array_district =[];
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
        $('select[id="district_id"]').append('<option value="">Select District</option>');
    }
}

</script>
@stop
