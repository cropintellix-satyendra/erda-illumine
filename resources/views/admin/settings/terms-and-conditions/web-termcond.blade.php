{{-- Extends layout --}}
@extends('layout.default')
{{-- Content --}}
@php
$editing = isset($Setting);
@endphp
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Web Privacy Policy</h4>
                <p class="mb-0">Web Privacy Policy</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex" id="tnc_section">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Form</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Web Privacy Policy</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 col-xxl-12" >
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Web & App Privacy Policy </h4>
                </div>
                <div class="card-body">
                    <form class="" action="{{url('admin/store/web/privacy/policy')}}" method="post">
                  @csrf
                  @method('POST')
                    <textarea class="summernote" id="web_privacypolicy" name="web_privacypolicy" rows="4" cols="50">
                    {{ $editing ? $Setting->web_privacypolicy : ''}}</textarea><br>
                    <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                    </form>
                    </div>
            </div>
        </div>
    </div> <!-- row end -->

    <div class="row">
        <div class="col-xl-12 col-xxl-12" >
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Web & App Terms and Conditions</h4>
                </div>
                <div class="card-body">
                    <form class="" action="{{url('admin/store/web/termsandcondition')}}" method="post">
                  @csrf
                  @method('POST')
                    <textarea class="summernote" id="app_termncond" name="app_termncond" rows="4" cols="50">
                    {{ $editing ? $Setting->app_termncond : ''}}</textarea><br>
                    <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                    </form>
                    </div>
            </div>
        </div>
    </div> <!-- row end -->


</div>
@stop
