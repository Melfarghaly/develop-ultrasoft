            <!--
            <div class="check-details">
                <div><label>رقم الشيك:</label> {{ $check->check_number }}</div>
                <div><label>اسم البنك:</label> {{ $check->bankaccount->name  ?? ''}}</div>
                <div><label>اسم الحساب:</label> {{ $check->account->name ?? ''  }}</div>
                <div><label>تاريخ التحرير:</label> {{ $check->issue_date->format('Y-m-d') }}</div>
                <div><label>تاريخ الاستحقاق:</label> {{ $check->due_date->format('Y-m-d') }}</div>
                <div><label>قيمة الشيك:</label> {{ $check->check_value }} </div>
                <div><label>إلى:</label> {{ $check->pay_to }}</div>
                <div><label>ملاحظات:</label> {{ $check->notes }}</div>
            </div>
    -->
    @extends('layouts.app')

@section('title', 'عرض الشيكات')

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
        <h2 style="font-family:     font-family: 'Font Awesome 5 Free';;"> {{__('lang_v1.check_'.$check->check_type)}}</h2>

    </div>
   
    <div class="voucher-details">
        
        <div class="row">
                <div class="mb-3 col-xs-6">
                    <label>التاريخ:</label>
                    <span class="bg-gray">{{ $check->issue_date->format('Y-m-d') }}</span>
                </div>

                <div class="mb-3 col-xs-6">
                    <label>المبلغ:</label>
                    <span class="bg-gray">{{ $check->check_value }}</span>
                </div>
                @if($check->check_type=='received')
                <div class="col-xs-12">
                    <label>استلمنا من :</label>
                    <span class="bg-gray">{{ $check->bankaccount->name }}</span>
                </div>
                @else
                <div class="col-xs-12">
                    <label> يصرف الي :</label>
                    <span class="bg-gray">{{ $check->account->name }}</span>
                    
                </div>
                @endif
                <div class="col-xs-12">
                    <label> مبلغ وقدره :</label>
                    @php
                    $util=new \App\Utils\Util();
                    @endphp
                    <span class="bg-gray">{{ $util->numToWord($check->check_value,'ar') }}</span>
                </div>
                <div class="mb-3 col-xs-6">
                    <label>شيك رقم :</label>
                    <span>{{ $check->check_number }}</span>
                </div>
                <div class="mb-3 col-xs-6">
                    <label> تاريخ استحقاق :</label>
                    <span>{{  $check->due_date->format('Y-m-d') }}</span>
                </div>
                <div class="mb-3 col-xs-12">
                    <label>وذلك نظير:</label>
                    <span></span>
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