@extends('layouts.app')

@section('title', 'عرض السندات')

@section('content')
    <style>
        body {
            direction: rtl;
            text-align: right;
            font-family: 'Cairo', sans-serif;
        }
        .voucher-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .voucher-header {
            text-align: center;
            margin-bottom: 30px;
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid;
            /* width: fit-content; */
            /* text-align: center; */
          
            box-shadow: 2px 2px 2px 1px rgb(0 0 0) !important;
        }

        .voucher-details {
            margin-bottom: 20px;
            border: 2.5px solid black;
            border-radius: 30px;
            padding: 15px;
        }
        .voucher-details label {
            font-weight: bold;
        }
        span.bg-gray{
            font-weight: 900;
            padding: 5px;
        }
        label{
            
            line-height: 2 !important;
        }
    </style>

<div class="container">
<div class="voucher-container">
    <div class="row">
        
        
        <div class="col-xs-4">
            <h4>
                {{ session('business.name') }}
            </h4>
        </div>
        <div class="col-xs-4"></div>
        <div class="col-xs-4">
            <img src="/public/uploads/business_logos/{{ session('business.logo') }}" alt="" width="50px" style="float: left;">
        </div>
    </div>
    <hr>
    <div class="voucher-header">
        <h2 style="font-family:     font-family: 'Font Awesome 5 Free';;"> {{__('lang_v1.'.$cashTransaction->transaction_type)}}</h2>

    </div>
    <div class="mb-3">
            <label>رقم السند:</label>
            <span>{{ $cashTransaction->document_number }}</span>
    </div>
    <div class="voucher-details">
        
        <div class="row">
                <div class="mb-3 col-xs-6">
                    <label>التاريخ:</label>
                    <span class="bg-gray">{{ date("Y-m-d",strtotime($cashTransaction->document_date)) }}</span>
                </div>

                <div class="mb-3 col-xs-6">
                    <label>المبلغ:</label>
                    <span class="bg-gray">{{ $cashTransaction->amount }}</span>
                </div>
                @if($cashTransaction->transaction_type=='withdrawal')
                <div class="col-xs-12">
                    <label>استلمنا من :</label>
                </div>
                @else
                <div class="col-xs-12">
                    <label> يصرف الي :</label>
                </div>
                @endif
                <div class="col-xs-12">
                    <label> مبلغ وقدره :</label>
                    @php
                    $util=new \App\Utils\Util();
                    @endphp
                    <span class="bg-gray">{{ $util->numToWord($cashTransaction->amount,'ar') }}</span>
                </div>
                <div class="mb-3 col-xs-12">
                    <label>البنك:</label>
                    <span>{{ $cashTransaction->bank->name ?? '' }}</span>
                </div>
                <div class="mb-3 col-xs-12">
                    <label>الحساب:</label>
                    <span>{{ $cashTransaction->account->name  ?? ''}}</span>
                </div>

                <div class="mb-3 col-xs-12">
                    <label>الملاحظات:</label>
                    <span>{{ $cashTransaction->notes }}</span>
                </div>
                <div class="mb-3 col-xs-8">
                    <label>المستلم :</label>
                    <span>____________________</span>
                </div>
                <div class="mb-3 col-xs-4">
                    <label>الحسابات </label><br>
                    <span>اعتماد المدير المالي</span>
                </div>
                <br>
                <br>
                <div class="mb-3 col-xs-6">
                    <label>توقيع أمين الخزينة </label><br>
                    <span> ____________________ </span>
                </div>
        </div>  
    </div>

    <div class="text-center">
        <button class="btn btn-primary no-print" onclick="window.print()">طباعة</button>
    </div>
</div>
</div>
@endsection
<!-- Bootstrap JS for responsiveness -->
 @section('javascript')
<script>
    $(document).ready(function(){
        //window.print();
    })
</script>
@endsection