
<div class="invocie-content">
<table>
    <tr>
        <td>
            <div class="invoice_con">
                <div class="row">
                    <div class="col-md-12">
                        <img src="{{ $receipt_details->letter_head}}" class="img-responsive" >
                    </div>
                </div>
                <table style="width: 100%; margin-top: -10px;">
                    <tr>
                        <td style=" vertical-align: top;">
                            <h3 style="text-align: left;">
                                @if($receipt_details->transaction->sub_type=='permission')  
                                اذن تسليم بضاعة
                                @else
                                {{ $receipt_details->invoice_heading }}
                                @endif
                                <small style="color: red;"> {{ $receipt_details->invoice_no }}</small>
                            </h3>
                        </td>
                        <td style=" vertical-align: top;">
                            <h4 style="text-align: right;">
                                {{ $receipt_details->date_label }} : {{ date('d-m-Y', strtotime($receipt_details->transaction->transaction_date)) }}
                            </h4>
                            <h4 style="text-align: right;" class="textright">
                                {{ $receipt_details->customer_label }} : {{ $receipt_details->customer_name }}
                            </h4>
                        </td>
                    </tr>
                </table>
              
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="table-responsive" id="product-table-invoie">
                            <table class="table product-table-invoie">
                                <thead>
                                    <tr>
                                        <th style="width:60%">{{ $receipt_details->table_product_label }}</th>
                                        @if($receipt_details->transaction->sub_type=='permission')
                                        <th>رقم أمر التوريد</th>
                                        @else

                                        <th>{{ $receipt_details->table_unit_price_label }}</th>
                                        @endif
                                        <th>{{ $receipt_details->table_qty_label }}</th>
                                        <th>{{ $receipt_details->table_subtotal_label }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        @php 
                                        $total =0;
                                        $count = 0;
                                        @endphp
                                    @foreach($receipt_details->lines as $line)
                                    <tr>
                                        <td>{{ $line['name'] }}</td>
                                        @if($receipt_details->transaction->sub_type=='permission')
                                        <td>&nbsp;</td>
                                        @else
                                        <td>{{ $line['unit_price'] }}</td>
                                        @endif
                                        <td>{{ $line['quantity'] }}</td>
                                        <td>{{ $line['line_total'] }}</td>
                                        @php 
                                        $total += $line['line_total_uf'];
                                        $count ++;
                                        @endphp
                                    </tr>
                                    @endforeach
                                    {{-- 
                                    @for($i = $count; $i < 11; $i++)
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @endfor
                                    --}}
                                </tbody>
                                <tfoot>
                                    @if(!empty($receipt_details->tax))
                                    <tr class="footer_row">
                                        <td colspan="3"> الاجمالي قبل الضريبة</td>
                                        <td> {{ $total }}</td>
                                    </tr>
                                    <tr class="footer_row">
                                        <td colspan="3"> الضريبة</td>
                                        <td> {{ $receipt_details->tax }}</td>
                                    </tr>
                                    @endif
                                    <tr class="footer_row">
                                        <td colspan="3">فقط وقدره : {{$receipt_details->total_in_words}} لاغير</td>
                                        <td> {{  $receipt_details->total }}</td>
                                    </tr>
                                  
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </td>
    </tr>
    <tfoot>
        <tr>
            <td>
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="text-center"> {!! $receipt_details->footer_text !!} </h4>
                        @if($receipt_details->transaction->sub_type=='permission')
                        <p style="text-align:right; font-size: 16px;font-weight: bold;" class="textright">
                            استلمت الأصناف الموضحة في حالة جيدة تحت الفحص والمعاينة : <br>
                            اسم المستلم : _________________________          التوقيع : _________________________
                            <br>

                        </p>
                        @endif
                        @if($receipt_details->transaction->sub_status=='quotation')
                        <p style="text-align:right; font-size: 16px;font-weight: bold;" class="textright">
                        الأسعار غير شاملة الضريبة 
                        <br>
                            الشروط<br>
                        
                        
                            1-مكان التسليم : _________________________
                            <br>
                            2-طريقة الدفع : _________________________
                            <br>
                            3-مدة التسليم : _________________________
                        </p>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </tfoot>
</table>
@if($receipt_details->transaction->status=='draft')
@for($i = $count; $i < 23; $i++)
<br>
@endfor
@endif
</div>

<style>
.table-responsive{
    border: 6px double #100808 !important;
    border-radius: 20px !important;
    padding: 0px !important;
}
.product-table-invoie{
    border: 1px solid #100808 !important;
    border-radius: 20px !important;
}

.product-table-invoie td,.product-table-invoie th{
    border-bottom: 1px dotted #100808 !important;
    border-top :none !important;
    padding: 3px !important;
    border-right: 1.3px solid #100808 !important;
    border-left: 1.3px solid #100808 !important;
    color: black  !important;
}
.product-table-invoie th{
    text-align: center !important;
    border-bottom: 1px solid #100808 !important;
    padding: 10px !important;
}
.footer_row{
    border-top: 1px solid #100808 !important;
    border-bottom: 1px solid #100808 !important;
    border-right: 1.3px solid #100808 !important;
    border-left: 1.3px solid #100808 !important;
    padding: 10px !important;
    font-weight: bold !important;
    text-align: center !important;
}
.product-table-invoie,.invoice_con{
    direction: ltr !important;
}
.textright{
    text-align: right !important;
    direction: rtl;

}
</style>