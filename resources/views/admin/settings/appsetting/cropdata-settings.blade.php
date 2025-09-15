{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>CropData Settings</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">CropData Settings</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">CropData Settings</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide" action="{{url('admin/cropdata/settings/update/'.$cropdata->id)}}" method="post" enctype="multipart/form-data">
                              @csrf
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Preparation Date Interval <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{$cropdata->preparation_date_interval}}" name="preparation_date_interval" id="preparation_date_interval" Required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Transplantation Date Interval <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{$cropdata->transplantation_date_interval}}" name="transplantation_date_interval" id="transplantation_date_interval" Required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">End Days <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{$cropdata->cropdata_end_days}}" name="cropdata_end_days" id="cropdata_end_days" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" Required>
                                    </div>
                                </div>
                              </div>
                              <div class="col-12">
                                  <a href="{!! url('admin/cropdata/settings') !!}" class="btn btn-danger mb-2 float-right">Cancel</a>
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
<script>

</script>
@stop
