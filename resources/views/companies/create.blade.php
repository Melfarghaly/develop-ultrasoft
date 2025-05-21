@extends('layouts.app')

@section('title', isset($company) ? 'تعديل الشركة' : 'إضافة شركة جديدة')

@section('content')
<section class="content no-print">
    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="container mt-5">
                    <!-- Page header -->
                    <h1 class="mb-4 text-center">{{ isset($company) ? 'تعديل الشركة' : 'إضافة شركة جديدة' }}</h1>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="{{ isset($company) ? route('companies.update', $company) : route('companies.store') }}" method="POST">
                                @csrf
                                @if(isset($company))
                                    @method('PUT')
                                @endif

                                <div class="mb-3">
                                    <label for="name" class="form-label">اسم الشركة</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ $company->name ?? old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">طرق الدفع</label>
                                    <div class="row">
                                        @foreach($paymentMethods as $method)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_methods[]" value="{{ $method->id }}" id="method{{ $method->id }}"
                                                        {{ isset($company) && $company->paymentMethods->contains($method->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="method{{ $method->id }}">
                                                        {{ $method->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('companies.index') }}" class="btn btn-secondary btn-custom">
                                        <i class="fas fa-arrow-left me-2"></i>العودة
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-custom">
                                        <i class="fas fa-save me-2"></i>{{ isset($company) ? 'تحديث' : 'إضافة' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
</section>
@endsection

@push('styles')
<style>
    .btn-custom {
        border-radius: 20px;
        padding: 10px 20px;
    }
    .card {
        border-radius: 15px;
    }
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>
@endpush