@extends('constructions::layouts.master')
@section('title', __('constructions::lang.projects'))

@section('main_content')
    <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">@lang('constructions::lang.active_projects')</h3>
            <div class="box-tools">
                @if(auth()->user()->can('constructions.add_project'))
                    <a href="#" class="btn btn-block btn-primary">
                        <i class="fa fa-plus"></i> @lang('constructions::lang.add_new_project')
                    </a>
                @endif
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <p>@lang('constructions::lang.constructions_module') @lang('messages.successfully_added')</p>
                        <p>@lang('messages.add_some_data')</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
