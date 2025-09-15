{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Notification Create</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Notification</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Notification</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{route('admin.notification.store')}}" method="post">
                              @csrf
                              @method('POST')
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" id="notify_selection" name="notify_selection" onchange="showuser(this.value)" required>
                                               <option value="">Select Type</option>
                                               <option value="all">All User</option>
                                               <option value="multi-user">Multi User</option>
                                          </select> 
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 select-users d-none">
                                    <label>Select Users <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                          <select class="form-control select2" id="select_user" name="select_user[]" multiple>
                                               <option value="">Select User</option>
                                               @foreach($users as $user)
                                                <option value="{{$user->id}}">{{$user->name}} / {{$user->mobile}}</option>
                                               @endforeach
                                          </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Title</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="title" id="title" required >
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>Body</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="body" id="body" required >
                                    </div>
                                </div>                                
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.notification.index')}}" class="btn btn-danger mb-2 float-right">Cancel</a>
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

$('#notify_selection').select2({
  selectOnClose: false
});
$('#select_user').select2({
  selectOnClose: false
});

function showuser(value){
    if(value == 'all'){
        $('.select-users').addClass('d-none');
    }else if(value == 'multi-user'){
        $('.select-users').removeClass('d-none');
    }
}


function FetchDistrict(Id){
    var stateID = Id;
    console.log(stateID,'ccc');
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
