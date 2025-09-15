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
                <!-- <p class="mb-0">Terms & Conditions</p> -->
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex" id="tnc_section">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Tnc</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">kosher  Terms & Conditions</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 col-xxl-12" >
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Terms & Conditions</h4>
                </div>
                <div class="card-body">
                    <form class="" action="{{url('admin/kosher/terms-and-conditions/store')}}" method="post">
                  @csrf
                  @method('POST')
                    <textarea class="summernote" id="cquest_tnc_cquest" name="cquest_tnc_cquest" rows="4" cols="50">
                    {{ $editing ? $Setting->cquest_tnc_cquest : ''}}</textarea><br>
                    <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                    </form>
                    </div>
            </div>
        </div>
        <div class="col-xl-12 col-xxl-12" id="carbon_section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Privacy Policy</h4>
                </div>
                <div class="card-body">
                <form class="" action="{{url('admin/kosher/privacy/policy/store')}}" method="post">
                  @csrf
                  @method('POST')
                    <textarea class="summernote" id="cquest_privacypolicy_cquest" name="cquest_privacypolicy_cquest" rows="4" cols="50">
                    {{ $editing ? $Setting->cquest_privacypolicy_cquest : ''}}</textarea><br>
                    <button type="submit" class="btn btn-primary mb-2 mr-2 float-right">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div> <!-- row end -->
</div>
@stop
