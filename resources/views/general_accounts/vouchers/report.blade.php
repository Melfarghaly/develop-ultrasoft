@extends('layouts.app')

@section('title', 'كشف حركة الخزينة')

@section('content')
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-solid'])

            <div class="container mt-5">
                <h1 class="mb-4 text-center">كشف حركة الخزينة</h1>

                <div class="mb-4 no-print">
                    <form method="GET" action="{{ route('vouchers.report') }}">
                        <div class="row d-flex align-items-end">
                            <!-- Filter Form -->
                            <div class="col-md-3 mb-3">
                                <label for="voucher_type" class="form-label">نوع السند</label>
                                <select id="voucher_type" name="voucher_type" class="form-control">
                                    <option value="">جميع الأنواع</option>
                                    <option value="cash_disbursement"
                                        {{ request('voucher_type') === 'cash_disbursement' ? 'selected' : '' }}>سند صرف</option>
                                    <option value="cash_receipt"
                                        {{ request('voucher_type') === 'cash_receipt' ? 'selected' : '' }}>سند استلام</option>
                                </select>
                            </div>
                            @php 
                            $business=\App\Business::find(session('business.id'));
                            $banks =\DB::table('accounting_accounts')->where('business_id',$business->id)->where('parent_account_id',$business->parent_bank_account_id)->pluck('name','id');
                            $banksIds = \DB::table('accounting_accounts')->where('business_id',$business->id)->where('parent_account_id',$business->parent_bank_account_id)->pluck('id')->toArray();
                            @endphp
                            
                            <div class="col-md-3 ">
                                    <label for="cash_drawer" class="form-label">الخزنة  </label>
                                    <select id="cash_drawer" name="cash_drawer[]" multiple="multiple"
                                        class="form-control select2 ">
                                        @foreach($banks as $id => $name )
                                                <option value="{{ $id }}" >{{$name}}</option>
                                            @endforeach                                     
                                          </select>
                                    @error('cash_drawer')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                       
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_from" class="form-label">من تاريخ</label>
                                <input type="date" id="date_from" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="date_to" class="form-label">إلى تاريخ</label>
                                <input type="date" id="date_to" name="date_to" class="form-control"
                                    value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-2 mb-6 d-flex justify-content-center" style="padding: 24px;">
                                <button type="submit" class="btn btn-primary btn-custom">عرض</button>
                            </div>
                        </div>
                    </form>
                </div>

                   <!-- Print Button -->
                   <div class="text-center mt-4 no-print hide">
                    <a href="{{ route('vouchers.printReport') }}" class="btn btn-success" target="_blank">
                        طباعة التقرير
                    </a>
                </div>
                <div class="text-center mt-4 no-print">
                    <button onClick="window.print();" class="btn btn-success" >
                        <i class="fa fa-print"></i>
                        طباعة 
                    </button>
                </div>
                
               
                

                @if ($vouchers->isEmpty())
                    <div class="alert alert-info text-center">
                        لا توجد سندات لعرضها.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>رقم السند</th>
                                    <th>نوع السند</th>
                                    <th>تاريخ السند</th>
                                    <th>العملة</th>
                                    <th>الخزنة</th>
                                    <th>اسم الحساب</th>
                                    <th>استلام</th>
                                    <th>صرف</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $total_debit = 0;
                                $total_credit = 0;
                            @endphp
                                @foreach ($vouchers as $voucher)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $voucher->voucher_number }}</td>
                                        <td>{{ $voucher->voucher_type === 'cash_disbursement' ? 'سند صرف' : 'سند استلام' }}</td>
                                        <td>{{ $voucher->voucher_date ?? 'غير محدد' }}</td>
                                        <td>{{ $voucher->currency }}</td>
                                        <td>{{ $voucher->drawer->name ?? ''  }}</td>
                                        <td>{{ $voucher->account->name ?? '' }}</td>
                                        <td>
                                            @if ($voucher->voucher_type == 'cash_receipt')
                                                @php 
                                                $total_debit += $voucher->amount;
                                                @endphp
                                                {{ $voucher->amount ? number_format($voucher->amount, 2) : '0.00' }}
                                            @endif
                                           </td>
                                        <td>
                                            @if ($voucher->voucher_type == 'cash_disbursement')

                                                @php
                                                $total_credit += $voucher->amount;
                                                @endphp
                                                {{ $voucher->amount ? number_format($voucher->amount, 2) : '0.00' }}
                                            @endif
                                        </td>                                        <td>{{ $voucher->notes }}</td>
                                    </tr>
                                @endforeach
                                
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7" class="table-title">الإجمالي</td>
                                <td>{{ number_format($total_debit, 2) }}</td>
                                <td>{{ number_format($total_credit, 2) }}</td>
                                <td >
                                    <strong> الرصيد {{ number_format($total_debit - $total_credit, 2) }}</strong>
                                   
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-right"><strong>الرصيد في بداية الفترة</strong></td>
                                <td colspan="">
                                    
                                    <p class="h4">{{ number_format($balanceBefore, 2) }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-right"><strong>الرصيد   النهائي </strong></td>
                                <td colspan="">
                                    
                                    <p class="h4">{{ number_format($balanceBefore + ($total_debit - $total_credit), 2) }}</p>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
                <div class="mt-4 text-center hide">
                    <p class="h4"><strong>الرصيد في بداية الفترة:</strong> {{ number_format($balanceStartPeriod, 2) }}</p>
                    <p class="h4"><strong>الرصيد في نهاية الفترة:</strong> {{ number_format($balanceEndPeriod, 2) }}</p>
                </div>
            </div>
        @endcomponent
    </section>
@endsection