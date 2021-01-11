@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.servicebdm_plural')}}<small class="ml-3 mr-3">|</small><small>{{trans('lang.servicebdm_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('servicebdm.index') !!}">{{trans('lang.country_plural')}}</a>
          </li>
          <li class="breadcrumb-item active">{{trans('lang.servicebdm_table')}}</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.servicebdm_table')}}</a>
        </li>
        @can('servicebdm.create')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('servicebdm.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.servicebdm_table')}}</a>
        </li>
        @endcan
        @include('layouts.right_toolbar', compact('dataTable'))
      </ul>
    </div>
    <div class="card-body">
      @include('servicebdm.table')
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@endsection

