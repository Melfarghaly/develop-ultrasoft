
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
<table>
    <tr>
        <td>


<div class="invoice_con">
    
    <div class="row">
        <div class="col-md-12 ">
            <div class="table-responsive">
                <table class="table product-table-invoie">
                    <thead>
                        <tr>
                            <th style="width:60%">{{ $receipt_details->table_product_label }}</th>
                            
                            <th>{{ $receipt_details->table_unit_price_label }}</th>
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
                            <td>{{ $line['unit_price'] }}</td>
                            <td>{{ $line['quantity'] }}</td>
                            <td>{{ $line['line_total'] }}</td>
                            @php 
                            $total += $line['line_total_uf'];
                            $count ++;
                            @endphp
                        </tr>
                        @endforeach
                        @for($i = $count; $i < 11; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr class="footer_row">
                            <td colspan="3">فقط وقدره : {{$receipt_details->total_in_words}} لاغير</td>
                            <td> {{  $total }}</td>
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
        </div>
    </div>
    </td>
</tr>
   </tfoot>
</table>