{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Role Create</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Role</a></li>
                        </ol>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                  <div class="col-12">
                      <div class="card">
                          <div class="card-header">
                              <h4 class="card-title">Role</h4>
                          </div>
                          <div class="card-body">
                            <form action="{{route('admin.roles.store')}}" method="post">
                              @csrf
                              @method('POST')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name')}}" id="name" required>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div>
                                  {{-- <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Permission<span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            <select multiple class="form-control @error('permission') is-invalid @enderror" name="permission[]">
                                                  <option value="">Select Permission</option>
                                                  @foreach($permissions as $permission)
                                                    <option value="{{$permission->id}}" >{{$permission->name}}</option>
                                                  @endforeach
                                            </select>
                                            @error('permission')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div> --}}
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Permission<span class="text-danger">*</span></label>
                                      <div class="input-group">
                                            @foreach($permissions as $key => $permission)
                                                <div class="col-3">
            										<div class="custom-control custom-checkbox mb-3" style="width: 110%;">
            											<input type="checkbox" class="custom-control-input" name="permission[]" value="{{$permission->id}}"
                                                                id="permission{{$permission->id}}">
            											<label class="custom-control-label" for="permission{{$permission->id}}"> {{ucwords($permission->name)}} </label>
            										</div>
            									</div>
        								    @endforeach	
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.roles.index')}}" class="btn btn-danger mb-2" style="margin-left: 77%;">Cancel</a>
                                 <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                              </div>
                              </form>
                          </div>
                      </div>
                  </div>
                </div>
          </div>
@endsection
