<style>
    .bg-dark{
        background-color: rgb(79 77 80) !important;
        color: white !important;
    }
    table.bg-dark{
        color: white !important;
        background-color: rgb(79 77 80) !important;
    }
    .product_table th{
        background-color: rgb(79 77 80) !important;
        color: white !important;    
    }
    .inv_table td{
        color: black !important;
        background-color: #d9d9d9 !important;
        border: 1px solid #000 !important;
    }
    .inv_table th{
        border: 1px solid #000 !important;
    }
</style>
<!-- business information here -->
<div class="row" dir="ltr" style="color: #000000 !important;">
    <div class="col-xs-4">

        <!-- Title of receipt -->
        @if (!empty($receipt_details->invoice_heading))
            <h3 class="text-left" style="color:red !important">
                {!! $receipt_details->invoice_heading !!}
            </h3>
            <div class="" dir="ltr">
                <table class=" table table-bordered pull-left text-left" style="border: 2px soild #333 !important">
                    <thead class="thead-dark inv_table">
                        <tr>
                            <th class="text-right">Date:</th>
                            <td class="text-right">
                
                                    {{ \Carbon\Carbon::parse($receipt_details->transaction->transaction_date)->format('Y-m-d') }}

                            </td>

                        </tr>
                        <tr>
                            <th class="text-right">Qout. No:</th>
                            <td class="text-right"> {{ $receipt_details->invoice_no }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Vald for:</th>
                            <td class="text-right">{{ $receipt_details->due_date ?? '15 days' }}</td>
                        </tr>
                    </thead>
                </table>
            </div>
        @endif
    </div>
    <div class="col-xs-3">

    </div>
    <div class="col-xs-5 text-left">
        @if (empty($receipt_details->letter_head))
            @if (!empty($receipt_details->logo))
                <img style="width: 100%;" src="{{ $receipt_details->logo }}"
                    class="img img-responsive center-block">
            @endif
        @endif
        <!-- Address -->
        <p>

          
        <table class="" dir="ltr" style="padding: 0px; margin:0px;width:fit-content">
            <thead>
                <tr>
                    <th class="text-right">Adress:</th>
                    <th class="text-right"><span style="font-size:12px !important">64 Abd El Salam Aref St., Cairo, Egypt</span></th>
                </tr>
                <tr>
                    <th class="text-right"> mobile:</th>
                    <th class="text-right"><span>+20 1069140286</span></th>
                </tr>
                <tr>
                    <th class="text-right">Tel: </th>
                    <th class="text-right"><span>+20 2 2391 40 28</span></th>
                </tr>
                <tr>
                    <th class="text-right">Fax: </th>
                    <th class="text-right"><span>+20 2 2392 52 69</span></th>
                </tr>
                <tr>
                    <th class="text-right">Email:</th>
                    <th class="text-right"><span>Info@alriadisports.com</span></th>
                </tr>
                <tr>
                    <th class="text-right">Web:</th>
                    <th class="text-right"><span>www.alriadisport.com</span></th>
                </tr>
            </thead>
        </table>
        </p>
    </div>
</div>
<div class="row" style="color: #000000 !important;">
    <div class="col-xs-4">

    </div>
    <div class="col-xs-4">

    </div>
    {{-- customer details  --}}

    <div class="col-xs-4 text-left">
        @if (!empty($receipt_details->customer_info))
            <h4 style="" class="bg-dark text-center">Customer information
            </h4>
            <table class="table  text-nowrap pull-left text-left" dir="ltr">
                <thead>
                    <tr>
                        <th class="text-right">Client Name:</th>
                        <th class="text-right">
                            {{ $receipt_details->customer_name }}
                        </th>
                    </tr>

                    <tr>
                        <th class="text-right">Phone:</th>
                        <th class="text-right">{{ $receipt_details->customer_phone ?? '' }}</th>
                    </tr>
                </thead>
            </table>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <!-- Table for items -->
        <table class="table table-striped table-bordered product_table" dir="ltr">
            <thead class="bg-dark">
                <tr>
                    <th class="text-right bg-dark">Description</th>
                    <th class="text-center bg-dark">Quantity</th>
                    <th class="text-center bg-dark" style="width: 102px;">Unit Price</th>
                    <th class="text-center bg-dark">Total</th>
                </tr>
            <tbody>
                @forelse($receipt_details->lines as $line)
                    <tr>
                        <td>
                            @if (!empty($line['image']))
                                <img src="{{ $line['image'] }}" alt="Image" width="50"
                                    style="float: left; margin-right: 8px;">
                            @endif
                            {{ $line['name'] }} {{ $line['product_variation'] }} {{ $line['variation'] }}
                            @if (!empty($line['sub_sku']))
                                , {{ $line['sub_sku'] }}
                                @endif @if (!empty($line['brand']))
                                    , {{ $line['brand'] }}
                                    @endif @if (!empty($line['cat_code']))
                                        , {{ $line['cat_code'] }}
                                    @endif
                                    @if (!empty($line['product_custom_fields']))
                                        , {{ $line['product_custom_fields'] }}
                                    @endif
                                    @if (!empty($line['product_description']))
                                        <small>
                                            {!! $line['product_description'] !!}
                                        </small>
                                    @endif
                                    @if (!empty($line['sell_line_note']))
                                        <br>
                                        <small>
                                            {!! $line['sell_line_note'] !!}
                                        </small>
                                    @endif
                                    @if (!empty($line['lot_number']))
                                        <br> {{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}
                                    @endif
                                    @if (!empty($line['product_expiry']))
                                        , {{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}
                                    @endif

                                    @if (!empty($line['warranty_name']))
                                        <br><small>{{ $line['warranty_name'] }} </small>
                                        @endif @if (!empty($line['warranty_exp_date']))
                                            <small>- {{ @format_date($line['warranty_exp_date']) }} </small>
                                        @endif
                                        @if (!empty($line['warranty_description']))
                                            <small> {{ $line['warranty_description'] ?? '' }}</small>
                                        @endif

                                        @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                            <br><small>
                                                1 {{ $line['units'] }} = {{ $line['base_unit_multiplier'] }}
                                                {{ $line['base_unit_name'] }} <br>
                                                {{ $line['base_unit_price'] }} x {{ $line['orig_quantity'] }} =
                                                {{ $line['line_total'] }}
                                            </small>
                                        @endif
                        </td>
                        <td class="text-center">
                            {{ $line['quantity'] }} {{ $line['units'] }}

                            @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                <br><small>
                                    {{ $line['quantity'] }} x {{ $line['base_unit_multiplier'] }} =
                                    {{ $line['orig_quantity'] }} {{ $line['base_unit_name'] }}
                                </small>
                            @endif
                        </td>
                        <td class="text-center">{{ $line['unit_price_before_discount'] }}</td>
                        @if (!empty($receipt_details->discounted_unit_price_label))
                            <td class="text-center">
                                {{ $line['unit_price_inc_tax'] }}
                            </td>
                        @endif
                        @if (!empty($receipt_details->item_discount_label))
                            <td class="text-center">
                                {{ $line['total_line_discount'] ?? '0.00' }}

                                @if (!empty($line['line_discount_percent']))
                                    ({{ $line['line_discount_percent'] }}%)
                                @endif
                            </td>
                        @endif
                        <td class="text-center">{{ $line['line_total'] }}</td>
                    </tr>
                    @if (!empty($line['modifiers']))
                        @foreach ($line['modifiers'] as $modifier)
                            <tr>
                                <td>
                                    {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                    @if (!empty($modifier['sub_sku']))
                                        , {{ $modifier['sub_sku'] }}
                                        @endif @if (!empty($modifier['cat_code']))
                                            , {{ $modifier['cat_code'] }}
                                        @endif
                                        @if (!empty($modifier['sell_line_note']))
                                            ({!! $modifier['sell_line_note'] !!})
                                        @endif
                                </td>
                                <td class="text-center">{{ $modifier['quantity'] }} {{ $modifier['units'] }} </td>
                                <td class="text-center">{{ $modifier['unit_price_inc_tax'] }}</td>
                                @if (!empty($receipt_details->discounted_unit_price_label))
                                    <td class="text-center">{{ $modifier['unit_price_exc_tax'] }}</td>
                                @endif
                                @if (!empty($receipt_details->item_discount_label))
                                    <td class="text-center">0.00</td>
                                @endif
                                <td class="text-center">{{ $modifier['line_total'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="4">&nbsp;</td>
                        @if (!empty($receipt_details->discounted_unit_price_label))
                            <td></td>
                        @endif
                        @if (!empty($receipt_details->item_discount_label))
                            <td></td>
                        @endif
                    </tr>
                @endforelse
                @for($i = 0; $i < 5 - count($receipt_details->lines); $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td class="bg-dark">
                        <div  class="bg-dark">
                       Special Notes & Instructions
                       </div>
                    </td>
                    <td rowspan="2" class="text-center"> 
                        <h4>GRAND
                            TOTAL</h4>
                    </td>
                    <td rowspan="2" colspan="2" class="text-center">
                        <h4> {{ $receipt_details->total }}</h4>
                    </td>
                </tr>
                <tr>
                    <td >
                        <p>Payment Terms: Advanced Payment<br>
                            Delivery Fees: Free<br>
                            Delivery Duration: Within 3 days from confirmation</p>
                    </td>

                </tr>
            </tfoot>
        </table>
    </div>
</div>
<br><br><br>
<div class="row"  style="padding-left: 0; padding-right: 0px;">
  <div class="col-xs-12" style="padding-left: 0; padding-right: 0px;">
       <img src="/img/invFooter.PNG" alt="footer" style="width:105%">
    </div>
</div
