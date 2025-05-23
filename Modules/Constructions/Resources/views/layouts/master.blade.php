@extends('layouts.app')
@section('title', __('constructions::lang.constructions'))

@section('content')

    @include('constructions::layouts.nav')
    
    <section class="content-header">
        <h1>@yield('title')</h1>
    </section>

    <section class="content">
        @yield('content_header')
        @if(!empty($__env->yieldContent('content_subheader')))
            @yield('content_subheader')
        @endif

        @yield('main_content')
    </section>
    
@endsection
