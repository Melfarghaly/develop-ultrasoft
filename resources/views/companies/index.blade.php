@extends('layouts.app')

@section('title', 'الشركات')

@section('content')
<section class="content no-print">
    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="container mt-5">
                    <!-- Page header -->
                    <h1 class="mb-4 text-center">الشركات</h1>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('companies.create') }}" class="btn btn-primary btn-custom">
                            <i class="fas fa-plus-circle me-2"></i>إضافة شركة جديدة
                        </a>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">الاسم</th>
                                            <th class="text-center">طرق الدفع</th>
                                            <th class="text-center">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($companies as $company)
                                            <tr>
                                                <td class="text-center">{{ $company->name }}</td>
                                                <td class="text-center">
                                                  
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-warning me-2">
                                                        <i class="fas fa-edit"></i> تعديل
                                                    </a>
                                                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                                            <i class="fas fa-trash-alt"></i> حذف
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">لا توجد شركات مسجلة</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
    .table th, .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.9em;
        padding: 5px 10px;
        margin: 2px;
    }
</style>
@endpush