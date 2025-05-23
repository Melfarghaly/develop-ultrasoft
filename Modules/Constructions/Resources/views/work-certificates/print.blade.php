@extends('layouts.guest')

@section('title', __('constructions::lang.summary_of_work_certificate'))

@section('content')
<style>
    body{
        direction: rtl;
        text-align: right;
       
    }   
    .work-certificate {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #000;
        padding-bottom: 20px;
    }
    .company-logo {
        max-width: 200px;
        margin-bottom: 10px;
    }
    .certificate-title {
        font-size: 24px;
        font-weight: bold;
        margin: 10px 0;
    }
    .certificate-subtitle {
        font-size: 18px;
        color: #666;
    }
    .info-section {
        margin: 20px 0;
    }
    .info-row {
        display: flex;
        margin-bottom: 10px;
    }
    .info-label {
        width: 200px;
        font-weight: bold;
    }
    .info-value {
        flex: 1;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .items-table th,
    .items-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .items-table th {
        background-color: #f5f5f5;
    }
    .totals-section {
        margin-top: 20px;
        text-align: right;
    }
    .total-row {
        margin: 5px 0;
    }
    .signature-section {
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
    }
    .signature-box {
        width: 200px;
        text-align: center;
    }
    .signature-line {
        border-top: 1px solid #000;
        margin-top: 50px;
        padding-top: 5px;
    }
    .footer {
        margin-top: 50px;
        text-align: center;
        font-size: 12px;
        color: #666;
    }
    .work-certificate {
        padding: 5px;
            border: 1px solid #000 !important;
        }
    @media print {
        .no-print {
            display: none;
        }
        .work-certificate {
            padding: 5px;
            border: 1px solid #000 !important;
        }
    }
</style>

<div class="work-certificate">
    <div class="header">
        @if(!empty($business->logo))
            <img src="{{ $business->logo }}" alt="Logo" class="company-logo">
        @endif
        <div class="certificate-title">{{ __('constructions::lang.summary_of_work_certificate') }}</div>
        <div class="certificate-subtitle">{{ $business->name }}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">{{ __('purchase.ref_no') }}:</div>
            <div class="info-value">{{ $purchase->ref_no }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('constructions::lang.contractor') }}:</div>
            <div class="info-value">{{ $purchase->contact->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('constructions::lang.work_date') }}:</div>
            <div class="info-value">{{ @format_date($purchase->transaction_date) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('business.address') }}:</div>
            <div class="info-value">{{ $purchase->contact->address }}</div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('product.product_name') }}</th>
                <th>{{ __('constructions::lang.description') }}</th>
                <th>{{ __('purchase.purchase_quantity') }}</th>
                <th>{{ __('constructions::lang.unit_text') }}</th>
                <th>{{ __('purchase.unit_cost_before_tax') }}</th>
                <th>{{ __('constructions::lang.implementation_rate') }}</th>
                <th>{{ __('purchase.line_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->purchase_lines as $line)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $line->product->name }}</td>
                    <td>{{ $line->description }}</td>
                    <td>{{ @format_quantity($line->quantity) }}</td>
                    <td>{{ $line->unit_text }}</td>
                    <td> @format_currency($line->purchase_price) </td>
                    <td>{{ $line->implementation_rate }} %</td>
                    <td> @format_currency($line->purchase_price * $line->quantity * ($line->implementation_rate/100)) </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row">
            <strong>{{ __('purchase.total_before_tax') }}:</strong>
            <span> @format_currency($purchase->total_before_tax) </span>
        </div>
        @if($purchase->discount_amount > 0)
            <div class="total-row">
                <strong>{{ __('purchase.discount') }}:</strong>
                <span> @format_currency($purchase->discount_amount) </span>
            </div>
        @endif
        @if($purchase->tax_amount > 0)
            <div class="total-row">
                <strong>{{ __('purchase.purchase_tax') }}:</strong>
                <span> @format_currency($purchase->tax_amount) </span>
            </div>
        @endif
        <div class="total-row">
            <strong>{{ __('purchase.purchase_total') }}:</strong>
                <span> @format_currency($purchase->final_total) </span>
        </div>
    </div>
    <!--payment lines table  -->

        <div class="payment-section">
            <div class="payment-title">{{ __('constructions::lang.payment_lines') }}</div>
            <table class="payment-table table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('constructions::lang.ref_no') }}</th>
                        <th>{{ __('constructions::lang.payment_date') }}</th>
                        <th>{{ __('constructions::lang.payment_amount') }}</th>
                        <th>{{ __('constructions::lang.note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payment_lines as $payment_line)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment_line->map->ref_no }}</td>
                            <td>{{ $payment_line->map->operation_date }}</td>
                            <td> @format_currency($payment_line->amount) </td>
                            <td>{{ $payment_line->map->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">{{ __('constructions::lang.contractor_signature') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">{{ __('constructions::lang.company_signature') }}</div>
        </div>
    </div>

    <div class="footer">
        <p>{{ $business->name }} - {{ $business->address }}</p>
        <p>{{ __('business.tel') }}: {{ $business->mobile }} | {{ __('business.email') }}: {{ $business->email }}</p>
    </div>
</div>

<div class="no-print" style="text-align: center; margin-top: 20px;">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fa fa-print"></i> {{ __('طباعة') }}
    </button>
</div>
@endsection
