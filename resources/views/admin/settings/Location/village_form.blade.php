{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
@php
$editing = isset($Village);
$Method  = isset($method);
@endphp
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>{{ $editing ? 'Update' : 'Create'}} Village</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Village</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Village</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide"
                                  action="{{$editing ? url('admin/village/edit/'.$Village->id) : url('admin/village/villagestore')}}"
                                  method="post">
                              @csrf
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>State <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control select2" id="state_id" onchange="FetchDistrict(this.value)" name="state_id" required>
                                             <option value="" >Select State</option>
                                              @foreach($states as $state)
                                                <option value="{{$state->id}}" {{ $editing ? $Village->state_id == $state->id ? 'selected' :'' : ''}}>{{$state->name}}</option>
                                              @endforeach
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Districts <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control select2" onchange="FetchBlock(this.value)" id="district_id" name="district_id" required>
                                                <option value="" >Select District</option>
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Block <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control select2" onchange="FetchPanchayat(this.value)" id="block_id" name="block_id" required>
                                                <option value="" >Select Block</option>
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Panchayat <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control select2" id="panchayat_id" name="panchayat_id" required>
                                                <option value="" >Select Panchayat</option>
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                      <label for="village">Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" id="village" name="village"
                                                id="village" value="{{ old('village', ($editing ? $Village->village : ''))}}">
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                  <a href="{!! route('admin.villages').'#village' !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
                                  <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button> &nbsp;
                              </div>
                              </form>
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

$('#district_id').select2({
  selectOnClose: true
});

$('#block_id').select2({
  selectOnClose: true
});

$('#panchayat_id').select2({
  selectOnClose: true
});


jQuery(".form-valide").validate({
    rules: {
        "village": {
            required: !0,
            minlength: 3
        },
    },
    messages: {
        "village": {
            required: "Please enter a village",
            minlength: "Your village must consist of at least 3 characters"
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
$(function(){
  //for edit purpose only
  FetchDistrict({{ $editing ? $Village->state_id : ''}});//fetch district
  FetchBlock({{ $editing ?  $Village->district_id : ''}});//fetch block
  FetchPanchayat({{ $editing ? $Village->taluka_id : ''}});//fetch panchayat
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
                $('select[name="district_id"]').empty();
                $('select[name="district_id"]').append('<option value="">Select District</option>');
                $.each(data.district, function(key, value) {
                        var districtId = '{{ $editing ? $Village->district_id : ' ' }}';
                        if(districtId == value.id){
                            var isSelected = 'Selected';
                        }else{
                            var isSelected = ' ';
                        }
                    $('select[name="district_id"]').append('<option value="'+ value.id +'" '+isSelected+'>'+ value.district +'</option>');
                });
                FetchBlock($('#district_id').val());//fetch district
            }
        });
    }else{
        $('select[name="district_id"]').empty();
    }
}

function FetchBlock(Id){
    var districtID = Id;
    if(districtID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/block')}}/"+districtID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':districtID},
            success: function(data) {
                $('select[name="block_id"]').empty();
                $('select[name="block_id"]').append('<option value="">Select Taluka</option>');
                $.each(data.Taluka, function(i, v) {
                    var talukaId = '{{ $editing ? $Village->taluka_id : ' ' }}';
                    if(talukaId == v.id){
                        var isSelected = 'Selected';
                    }else{
                        var isSelected = ' ';
                    }
                    $('select[name="block_id"]').append('<option value="'+ v.id +'" '+isSelected+'>'+ v.taluka +'</option>');
                });
                FetchPanchayat($('#block_id').val())
            }
        });
    }else{
        $('select[name="block_id"]').empty();
    }
}
// });

// $( "select[name='block_id']" ).change(function () {
function FetchPanchayat(Id){
    var blockID = Id;//$(this).val();
    if(blockID) {
        $.ajax({
            type:'post',
            url: "{{url('admin/fetch/panchayat')}}/"+blockID,
            dataType: 'Json',
            data: {_token:'{{csrf_token()}}','id':blockID},
            success: function(data) {
                $('select[name="panchayat_id"]').empty();
                $('select[name="panchayat_id"]').append('<option value="">Select Panchayat</option>');
                $.each(data.panchayat, function(i, v) {
                    var panchayatId = '{{ $editing ? $Village->panchayat_id : ' ' }}';
                    if(panchayatId == v.id){
                        var isSelected = 'Selected';
                    }else{
                        var isSelected = ' ';
                    }
                    $('select[name="panchayat_id"]').append('<option value="'+ v.id +'" '+isSelected+'>'+ v.panchayat +'</option>');
                });
            }
        });
    }else{
        $('select[name="panchayat_id"]').empty();
    }
}
// });
</script>
@stop
