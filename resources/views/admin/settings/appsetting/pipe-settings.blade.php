{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Pipe Threshold Settings</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Pipe Threshold Settings</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Pipe Threshold Settings</h4>
                          </div>
                          <div class="card-body form-validation">
                            <form class="form-valide" action="{{url('admin/pipe/threshold/settings/update/'.$pipe_threshold->id)}}" method="post" enctype="multipart/form-data">
                              @csrf
                              <div class="row">
                                <div class="col-md-6 col-xl-3 col-xxl-6 mb-3 form-group">
                                    <label for="name">Pipe Threshold Setting <span class="text-danger">*</span> (E.x for ±15% -> 0.15 or ±10% -> 0.10)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{$pipe_threshold->threshold_pipe_installation}}" name="threshold_pipe_installation" id="threshold_pipe_installation" Required>
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
