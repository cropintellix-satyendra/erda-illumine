{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Permission Create</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Permission</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Permission</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{route('admin.permissions.store')}}" method="post">
                              @csrf
                              @method('POST')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control @error('permission') is-invalid @enderror" name="permission" value="{{ old('permission')}}" id="permission" required>
                                            @error('permission')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div>
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Role<span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            @foreach($roles as $key => $role)
                                                <div class="col-3">
            										<div class="custom-control custom-checkbox mb-3" style="width: 110%;">
            											<input type="checkbox" class="custom-control-input" name="role[]" value="{{$role->id}}"
                                                                id="role{{$role->id}}">
            											<label class="custom-control-label" for="role{{$role->id}}"> {{$role->name}} </label>
            										</div>
            									</div>
        								    @endforeach	
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.permissions.index')}}" class="btn btn-danger mb-2" style="margin-left: 77%;">Cancel</a>
                                <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                              </div>
                              </form>
                          </div>
                      </div>
                  </div>
                </div>
          </div>
@endsection
