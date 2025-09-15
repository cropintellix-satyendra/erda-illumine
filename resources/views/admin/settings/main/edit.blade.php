{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Pipe Settings</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Pipe Settings</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Pipe Settings</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide" action="{{url('admin/pipe/setting/update/'.$settings->id)}}" method="post">
                              @csrf
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Unit <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{$settings->unit}}" name="unit" id="unit">
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Area<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{$settings->area}}" name="area" id="area">
                                    </div>
                                </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                      <label for="name">No. of Pipes <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control" value="{{$settings->no_of_pipe}}" name="no_of_pipe" id="no_of_pipe">
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Type <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="type" required>
                                                <option value="acres" {{$settings->type=='acres' ? 'selected' : ''}}>Acres</option>
                                                <option value="hectare" {{$settings->type=='hectare' ? 'selected' : ''}}>Hectare</option>
                                            </select>
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Status <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select class="form-control" name="status" required>
                                                <option value="1" {{$settings->status==1 ? 'selected' : ''}}>Enable</option>
                                                <option value="0" {{$settings->status==0 ? 'selected' : ''}}>Disable</option>
                                            </select>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                  <a href="{!! url('admin/pipe/setting') !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
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

</script>
@stop
