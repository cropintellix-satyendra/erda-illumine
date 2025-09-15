{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
<div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Role Edit</h4>
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
                            <form action="{{route('admin.roles.update',$role->id)}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="row">
                                  <div class="col-md-6 col-xl-3 col-xxl-6 mb-3">
                                      <label>Name <span class="text-danger">*</span></label>
                                      <div class="input-group">
                                          <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{$role->name}}" id="name" required>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                      </div>
                                  </div>
                                 <div class="col-md-6 col-xl-6 col-xxl-6 mb-3">
                                      <label>Permission<span class="text-danger">*</span></label>
                                      <div class="input-group">
                                           @foreach($permissions as $key => $permission)
                                           {{-- @php  dd( $permission->id, $rolePermissions  ,in_array($permission->id, $rolePermissions));  @endphp  --}}
                                                <div class="col-3">
            										<div class="custom-control custom-checkbox mb-3" style="width: 110%;">
            											<input type="checkbox" class="custom-control-input" name="permissions[]" value="{{$permission->id}}" 
            											        @if(in_array($permission->id, $rolePermissions))
                                                                    checked
                                                                @endif
                                                                id="permissions{{$permission->id}}">
            											<label class="custom-control-label" for="permissions{{$permission->id}}"> {{ucwords($permission->name)}} </label>
            										</div>
            									</div>
        								    @endforeach	
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                                <a href="{{route('admin.roles.index')}}" class="btn btn-danger mb-2 float-right">Cancel</a>
                                 <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                              </div>
                              </form>
                          </div>
                      </div>
                  </div>
                </div>
          </div>
@endsection
