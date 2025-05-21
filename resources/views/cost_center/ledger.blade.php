@extends('layouts.app')
@section('title', __('مراكز التكلفة'))

@section('content')
@include('cost_center.partials.nav')
<section class="content">
    <div class="row ">
        <div class="col-md-12">
            <div class=" box box-primary">
                <div class="box-header">التصفية</div>
                <div class="box-body">
                    <form action="{{ route('cost-center.ledger') }}" method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cost_center_id">المركز</label>
                                    <select name="cost_center_id" id="cost_center_id" class="form-control select2">
                                        <option value="">الكل</option>
                                        @foreach($costCenters as $center)
                                            <option value="{{ $center->id }}" {{ request('cost_center_id') == $center->id ? 'selected' : '' }}>{{ $center->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    {!! Form::label('transaction_date_range', __('report.date_range') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('transaction_date_range', null, ['class' => 'form-control', 'readonly', 'placeholder' => __('report.date_range')]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">تصفية</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="row ">
        <div class="col-md-12">
            <div class=" box box-primary">
                <div class="box-header">القيود</div>
                <div class="box-body">

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr style="background-color: #ccc; direction: ltr;">
                                    <th>المركز</th>
                                    <th>رقم القيد</th>
                                    <th>البيان</th>
                                    <th>الحساب</th>
                                    <th>المصروفات</th>
                                    <th>الإيرادات</th>
                                    <th>الربح</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cost_center as $center)
                                    @include('cost_center.partials.ledger_row', ['center' => $center, 'level' => 0, 'total_debit' => 0, 'total_credit' => 0, 'total_profit' => 0])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .cost-center {
        font-weight: bold;
        background-color: #f0f0f0;
    }
    .cost-center.level-1 { background-color: #e8f4ff; }
    .cost-center.level-2 { background-color: #d1e7ff; }
    .cost-center.level-3 { background-color: #b8daff; }
    .negative { color: red; }
</style>

@endsection
@section('javascript')
<script>
$('#transaction_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#transaction_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                
                
            }
        );
</script>
@endsection