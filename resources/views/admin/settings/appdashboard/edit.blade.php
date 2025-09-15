{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>App Dashboard Settings</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">App Dashboard Settings</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">App Dashboard Settings</h4>
                          </div>
                          <div class="card-body form-validation">
                          {{--  <form class="form-valide" action="{{url('admin/app/settings/update/')}}" method="post" enctype="multipart/form-data"> --}}
                              
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                    <label>States <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control" name="state_id" onchange="FetchDashboard(this.value)"  id="state_id" required>
                                            <option value="">Select States</option>
                                            @foreach($states as $state)
                                                <option value="{{$state->id}}">{{$state->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                  
                               

                              </div>
                              <!--end row-->
                          
                          </div>
                          <div class="card-body form-validation Dashboard">
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Farmer Registration </label>
                                    <div class="input-group">
                                        <input type="checkbox" class="Enable" value="0" name="farmer_registration" id="farmer_registration">
                                    </div>
                                </div> 
                                 <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Crop Data </label>
                                    <div class="input-group">
                                       <input type="checkbox" class="Enable" value="0" name="crop_data" id="crop_data">
                                    </div>
                                </div>
                                 <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Pipe Installation </label>
                                    <div class="input-group">
                                       <input type="checkbox" class="Enable" value="0" name="pipe_installation" id="pipe_installation">
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Polygon </label>
                                    <div class="input-group">
                                       <input type="checkbox" class="Enable" value="0" name="polygon" id="polygon">
                                    </div>
                                </div>
                                 <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Capture Aeration </label>
                                    <div class="input-group">
                                       <input type="checkbox" class="Enable" value="0" name="capture_aeration" id="capture_aeration">
                                    </div>
                                </div>
                                 <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Farmer Benefit </label>
                                    <div class="input-group">
                                       <input type="checkbox" class="Enable" value="0" name="farmer_benefit" id="farmer_benefit">
                                    </div>
                                </div>

                              </div>
                          </div>
                      </div>
                  </div>
                </div>
          </div>

@endsection
@section('scripts')
<script src="{{ asset('vendor/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
<script>

$('#state_id').select2({
  selectOnClose: true
});

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


function FetchDashboard(Id){
    var stateID = Id;
    if(stateID) {
        $.ajax({
            type:'get',
            url: "{{url('admin/app/dashboard/settings')}}/"+stateID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':stateID},
            success: function(data) {
                if(data.farmer_registration){
                    document.getElementById("farmer_registration").checked = true;   
                    $('#farmer_registration').val(data.farmer_registration);
                }else{
                    document.getElementById("farmer_registration").checked = false;   
                    $('#farmer_registration').val(data.farmer_registration);
                }
                
                if(data.crop_data){
                    document.getElementById("crop_data").checked = true;   
                    $('#crop_data').val(data.crop_data);
                }else{
                    document.getElementById("crop_data").checked = false;   
                    $('#crop_data').val(data.crop_data);
                }
                
                if(data.pipe_installation){
                    document.getElementById("pipe_installation").checked = true;
                    $('#pipe_installation').val(data.pipe_installation);
                }else{
                    document.getElementById("pipe_installation").checked = false;   
                    $('#pipe_installation').val(data.pipe_installation);
                }

                if(data.polygon){
                    document.getElementById("polygon").checked = true;
                    $('#polygon').val(data.polygon);
                }else{
                    document.getElementById("polygon").checked = false;   
                    $('#polygon').val(data.polygon);
                }
                
                
                if(data.capture_aeration){
                    document.getElementById("capture_aeration").checked = true; 
                    $('#capture_aeration').val(data.capture_aeration);
                }else{
                    document.getElementById("capture_aeration").checked = false;   
                    $('#capture_aeration').val(data.capture_aeration);
                }
                
                if(data.farmer_benefit){
                    document.getElementById("farmer_benefit").checked = true; 
                    $('#farmer_benefit').val(data.farmer_benefit);
                }else{
                    document.getElementById("farmer_benefit").checked = false;   
                    $('#farmer_benefit').val(data.farmer_benefit);
                }
                
            }
        });
    }else{
       document.getElementById("farmer_registration").checked = false;   
       document.getElementById("crop_data").checked = false;   
       document.getElementById("pipe_installation").checked = false; 
       document.getElementById("capture_aeration").checked = false;
       document.getElementById("farmer_benefit").checked = false;   
    }
}



 $(".Enable").click(function() {
     var state_id = document.getElementById("state_id").value;
     var name = this.name;
     if($('#'+name).is(":checked")){
         var value = 0;
     }else{
         var value = 1;
     }
     if(state_id){
      Swal.fire({
		  title: 'Are you sure?',
		  text: "You want to change status!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, Change it!'
		}).then((result) => {
		  if (result.value == 1) {
		      $.ajax({
                  type:'post',
                  url:"{{url('admin/app/dashboard/status')}}",
                  data: {_token:'{{csrf_token()}}',method:'post',value:value,name:name,state_id:state_id},
                  success:function(data){
                      if(value == 0){
                          $("#"+name).prop("checked", true);
                      }else if(value == 1){
                         $("#"+name).prop("checked", false);
                      }
                    toastr.success("", "Updated Successfully", {
                          timeOut: 5000,closeButton: !0,debug: !1,newestOnTop: !0,progressBar: !0,positionClass: "toast-bottom-center",preventDuplicates: !0,
                          onclick: null,showDuration: "300",hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",
                          hideMethod: "fadeOut",tapToDismiss: !1
                      })
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                    var data = jqXHR.responseJSON.farmer;
                    toastr.error("", "Something went wrong", {
                          positionClass: "toast-bottom-center",timeOut: 5000,closeButton: !0,
                          debug: !1,newestOnTop: !0,progressBar: !0,preventDuplicates: !0,onclick: null,showDuration: "300",
                          hideDuration: "1000",extendedTimeOut: "1000",showEasing: "swing",hideEasing: "linear",showMethod: "fadeIn",hideMethod: "fadeOut",
                          tapToDismiss: !1
                      })
                  }
              });//ajax end
		  }else{//if end of confirmation
		      $("#"+name).prop("checked", false);
		  }
		})//swal end
		
    }else{
        
    }
 });
</script>
@stop
