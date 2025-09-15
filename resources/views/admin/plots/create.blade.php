@extends('layouts.default')
@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-sm-6">
        <div class="page-title-box">
            <h4>{!! $title !!}</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{!! url('/') !!}">Home</a></li>
                    <li class="breadcrumb-item active">{!! $title !!}</li>
                </ol>
        </div>
    </div>
</div>
<!-- end page title -->
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
				<h4 class="card-title">Add User</h4>
				<form class="form-horizontal" method="POST" action="{{ route('admin.user.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3 row">
                            <label for="first_name" class="col-sm-3 text-end control-label col-form-label">Name</label>
                            <div class="col-sm-4s col">
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{!! old('first_name') !!}" placeholder="First Name">
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="last_name" class="col-sm-3 text-end control-label col-form-label sr-only">Last Name</label>
                            <div class="col-sm-4s col">
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{!! old('last_name') !!}" placeholder="Last Name">
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!--<div class="mb-3 row">
                            <label for="mobile" class="col-sm-3 text-end control-label col-form-label">Mobile</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="mobile" name="mobile" value="{!! old('mobile') !!}" placeholder="Mobile">
                                @error('mobile')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>-->
                        <div class="mb-3 row">
                            <label for="mobile" class="col-sm-3 text-end control-label col-form-label">Mobile</label>
                            <div class="col-sm-3s col">
                                <input type="text" class="form-control" id="mobile" name="mobile" value="{!! old('mobile') !!}" placeholder="Mobile">
                                @error('mobile')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="mobile" class="col-sm-3 text-end control-label col-form-label sr-only">Mobile</label>
                            <div class="col-sm-3s col">
                                <select name="company_id" class="form-control" placeholder="Select Company">
                                    <option value="">-- Select Company --</option>
                                    @if ($users->count()>0)
                                        @foreach ($users as $option)
                                            <option value="{!! $option->id !!}">{!! $option->meta->company_code !!} - {!! ucwords($option->name) !!}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('company_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="email" class="col-sm-3 text-end control-label col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" id="email" name="email" value="{!! old('email') !!}" placeholder="Email Address">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password" class="col-sm-3 text-end control-label col-form-label">Password</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password-confirm" class="col-sm-3 text-end control-label col-form-label">Confirm Password</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirm Password">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="status" class="col-sm-3 text-end control-label col-form-label">Status</label>
                            <div class="col-sm-9">
                                <input type="checkbox" id="switch3" switch="bool" name="status" value="1" checked />
                                <label for="switch3" data-on-label="Yes" data-off-label="No"></label>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="status" class="col-sm-3 text-end control-label col-form-label">Assign Survey</label>
                            <div class="col-sm-9">
                            	@if($survey_type->count()>0)
                            	@foreach($survey_type as $type)
                            	<div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="survey_type[]" value="{!! $type->ID !!}" id="form_survey_type_{!! $type->ID !!}">
                                    <label class="form-check-label" for="form_survey_type_{!! $type->ID !!}">
                                        {!! $type->post_title !!} <span class="badge bg-info">Survey</span>
                                    </label>
                                </div>
                            	@endforeach
                            	@endif
                            </div>
                        </div>
                        
                    </div>
                    <div class="p-3 border-top">
                        <div class="text-end">
                            <button type="submit" class="btn btn-info rounded-pill px-4 waves-effect waves-light">Save</button>
                            <button type="cancel" class="btn btn-dark rounded-pill px-4 waves-effect waves-light" onclick="window.location='{!! route('admin.user.index') !!}';return false;">Cancel</button>
                        </div>
                    </div>
                </form>
			</div>
		</div>
	</div>
</div>
@stop
