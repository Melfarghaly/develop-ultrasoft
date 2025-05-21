<!-- business information here -->

<style>
	body {
      font-family: Arial, sans-serif;
      direction: rtl;
      text-align: right;
    }
	.product_table th{
		
		border: 1px solid #ccc !important;
	}
	.product_table td{
		border: 1px solid #ccc !important;
	}
    .product_table{
        direction: ltr !important;
    }
    .row{
        direction: ltr !important;
        text-align: left !important;
        
    }
    .text-right{
        text-align: right !important;
    }
    .text-left{
        text-align: left !important;
    }
    
    .table-slim th{
        text-align: left !important;
    }
   
    .table-slim>tbody>tr>td, .table-slim>tbody>tr>th, .table-slim>tfoot>tr>td, .table-slim>tfoot>tr>th, .table-slim>thead>tr>td, .table-slim>thead>tr>th {
        padding: 14px;
        border: 2px solid black !important;
        border-top: 2px solid black !important;
    }
	.table-bordered td,.table-bordered th{
		border: 2px solid black !important;
        border-top: 2px solid black !important;
	}
    .table-slim>tbody>tr>th{
        border-right-color: white !important;
    }
	.container {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
    }

    .column {
      width: 48%;
    }

    .column div {
      margin-bottom: 5px;
    }

    .labele {
      font-weight: bold;
    }
	.left-column {
	  text-align: left;
	}

	.right-column {
	  text-align: left;
	}
</style>
<div class="row" style="color: #000000 !important;">
		<!-- Logo -->
		@if(empty($receipt_details->letter_head))
			@if(!empty($receipt_details->logo))
				<img style="max-height: 120px; width: auto;" src="{{$receipt_details->logo}}" class="img img-responsive center-block">
			@endif

			<!-- Header text -->
			@if(!empty($receipt_details->header_text))
				<div class="col-xs-12">
					{!! $receipt_details->header_text !!}
				</div>
			@endif

			<!-- business information here -->
			<div class="col-xs-12 text-center">
				<h2 class="text-center">
					<!-- Shop & Location Name  -->
					@if(!empty($receipt_details->display_name))
						{{$receipt_details->display_name}}
					@endif
				</h2>

				<!-- Address -->
				<p>
				@if(!empty($receipt_details->address))
						<small class="text-center">
						{!! $receipt_details->address !!}
						</small>
				@endif
				@if(!empty($receipt_details->contact))
					<br/>{!! $receipt_details->contact !!}
				@endif	
				@if(!empty($receipt_details->contact) && !empty($receipt_details->website))
					, 
				@endif
				@if(!empty($receipt_details->website))
					{{ $receipt_details->website }}
				@endif
				@if(!empty($receipt_details->location_custom_fields))
					<br>{{ $receipt_details->location_custom_fields }}
				@endif
				</p>
				<p>
				@if(!empty($receipt_details->sub_heading_line1))
					{{ $receipt_details->sub_heading_line1 }}
				@endif
				@if(!empty($receipt_details->sub_heading_line2))
					<br>{{ $receipt_details->sub_heading_line2 }}
				@endif
				@if(!empty($receipt_details->sub_heading_line3))
					<br>{{ $receipt_details->sub_heading_line3 }}
				@endif
				@if(!empty($receipt_details->sub_heading_line4))
					<br>{{ $receipt_details->sub_heading_line4 }}
				@endif		
				@if(!empty($receipt_details->sub_heading_line5))
					<br>{{ $receipt_details->sub_heading_line5 }}
				@endif
				</p>
				<p>
				@if(!empty($receipt_details->tax_info1))
					<b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
				@endif

				@if(!empty($receipt_details->tax_info2))
					<b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
				@endif
				</p>
			@endif


			
		</div>
		@if(!empty($receipt_details->letter_head))
			<div class="col-xs-12 text-center">
				<img style="width: 100%;margin-bottom: 10px;" src="{{$receipt_details->letter_head}}">
			</div>
		@endif
        <div class="col-xs-4">
		@if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
			<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
		@endif
	</div>
	<div class="col-xs-8 ">
	<table class="table">
    <tr>
	<td class="right-column">
        <span class="labele">Simple Taxable Invoice</span><br>
        <span class="labele">Customer Name:</span>  <br>
		{{ $receipt_details->transaction->contact->name }}<br>
		
		
        <span class="labele">Customer Phone Number:</span><br> 
		
		{{ $receipt_details->transaction->contact->mobile }}<br>
		
        <span class="labele">Customer Vat Number:</span> <br>
			{{ $receipt_details->transaction->contact->tax_number }}<br>
		
        <span class="labele">Customer Location Address:</span><br> 
		
		{{ $receipt_details->transaction->contact->contact_address }}<br>
      </td>
      <td class="left-column">
       
        default Register A<br>
		<br>
        <span class="labele">Vat Number </span> <br>{{$receipt_details->business->tax_number_1}}<br><br>
        <span class="labele">Invoice Number:</span> 
			
			{{$receipt_details->invoice_no}}
		<br><br>
        <span class="labele">Invoice Date:</span> 

			{{$receipt_details->transaction->transaction_date}}
		<br>
        <span class="labele">Invoice type:</span> Sell Invoice
      </td>
      
    </tr>
  </table>
	
</div>

<div class="row" style="color: #000000 !important;">
	@includeIf('sale_pos.receipts.partial.common_repair_invoice')
</div>

<div class="row" style="color: #000000 !important;">
	<div class="col-xs-12">
		<br/>
		@php
			$p_width = 45;
		@endphp
		@if(!empty($receipt_details->item_discount_label))
			@php
				$p_width -= 10;
			@endphp
		@endif
		@if(!empty($receipt_details->discounted_unit_price_label))
			@php
				$p_width -= 10;
			@endphp
		@endif
		@php
        $total_before_tax = 0;
        $total_tax = 0;
        $total_after_tax = 0;
        $total_quantity = 0;
        @endphp
		<table class="table table-responsive table-slim product_table">
			<thead>

				<tr>
                   <th class="text-right" width="15%">{{$receipt_details->table_qty_label}}</th>
					<th width="{{$p_width}}%">{{$receipt_details->table_product_label}}</th>
					<td>Unit</td>
					<th class="text-right" width="15%">{{$receipt_details->table_unit_price_label}}</th>
                    <th>Tax</th>
					@if(!empty($receipt_details->discounted_unit_price_label))
						<th class="text-right" width="10%">{{$receipt_details->discounted_unit_price_label}}</th>
					@endif
					@if(!empty($receipt_details->item_discount_label))
						<th class="text-right" width="10%">{{$receipt_details->item_discount_label}}</th>
					@endif
					<th class="text-right" width="15%">{{$receipt_details->table_subtotal_label}}</th>
					<th class="text-right" width="15%">Total(Tax Inclusive)</th>
				</tr>
			</thead>
			<tbody>
				@forelse($receipt_details->lines as $k=> $line)
					<tr>
                    <td class="text-right">
							{{$line['quantity']}}

							@if($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                            <br><small>
                            	{{$line['quantity']}} x {{$line['base_unit_multiplier']}} = {{$line['orig_quantity']}} {{$line['base_unit_name']}}
                            </small>
                            @endif
						</td>
						<td>
							@if(!empty($line['image']))
								<img src="{{$line['image']}}" alt="Image" width="50" style="float: left; margin-right: 8px;">
							@endif
                            {{$line['name']}} {{$line['product_variation']}} {{$line['variation']}} 
                            @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif
                            @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif
                            @if(!empty($line['product_description']))
                            	<small>
                            		{!!$line['product_description']!!}
                            	</small>
                            @endif 
                            @if(!empty($line['sell_line_note']))
                            <br>
                            <small>
                            	{!!$line['sell_line_note']!!}
                            </small>
                            @endif 
                            @if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif 
                            @if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif

                            @if(!empty($line['warranty_name'])) <br><small>{{$line['warranty_name']}} </small>@endif @if(!empty($line['warranty_exp_date'])) <small>- {{@format_date($line['warranty_exp_date'])}} </small>@endif
                            @if(!empty($line['warranty_description'])) <small> {{$line['warranty_description'] ?? ''}}</small>@endif

                            @if($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                            <br><small>
                            	1 {{$line['units']}} = {{$line['base_unit_multiplier']}} {{$line['base_unit_name']}} <br>
                            	{{$line['base_unit_price']}} x {{$line['orig_quantity']}} = {{$line['line_total']}}
                            </small>
                            @endif
                        </td>
						<td> {{$line['units']}} </td>
						<td class="text-right">{{$line['unit_price_before_discount']}}</td>
                        <td>{{$line['tax_unformatted']}} / {{$line['tax_percent']}}%</td>
						@if(!empty($receipt_details->discounted_unit_price_label))
							<td class="text-right">
								{{$line['unit_price_inc_tax']}} 
							</td>
						@endif
						@if(!empty($receipt_details->item_discount_label))
							<td class="text-right">
								{{$line['total_line_discount'] ?? '0.00'}}

								@if(!empty($line['line_discount_percent']))
								 	({{$line['line_discount_percent']}}%)
								@endif
							</td>
						@endif
						<td>
						@php 
						$tax_amount = $line['tax_unformatted'] * $line['quantity'];
						$total_before_tax+=$line['unit_price_before_discount_uf'] * $line['quantity_uf'];
						$total_after_tax+=$line['line_total_uf'];
						@endphp
						{{$total_before_tax}}
						</td>
						
						<td class="text-right">{{$total_after_tax}}</td>
					</tr>
					@if(!empty($line['modifiers']))
						@foreach($line['modifiers'] as $modifier)
							<tr>
								<td>
		                            {{$modifier['name']}} {{$modifier['variation']}} 
		                            @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
		                            @if(!empty($modifier['sell_line_note']))({!!$modifier['sell_line_note']!!}) @endif 
		                        </td>
								<td class="text-right">{{$modifier['quantity']}} {{$modifier['units']}} </td>
								<td class="text-right">{{$modifier['unit_price_inc_tax']}}</td>
								@if(!empty($receipt_details->discounted_unit_price_label))
									<td class="text-right">{{$modifier['unit_price_exc_tax']}}</td>
								@endif
								@if(!empty($receipt_details->item_discount_label))
									<td class="text-right">0.00</td>
								@endif
								<td class="text-right">{{$modifier['line_total']}}</td>
							</tr>
						@endforeach
					@endif
				@empty
					<tr>
						<td colspan="4">&nbsp;</td>
						@if(!empty($receipt_details->discounted_unit_price_label))
    					<td></td>
    					@endif
    					@if(!empty($receipt_details->item_discount_label))
    					<td></td>
    					@endif
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<table class="table table-striped table-bordered ">
			
				<tr>
					<th class="text-center" width="50%">Total (Tax Exuclusive)</th>
					<td class="text-center">{{$total_before_tax}}</td>
				</tr>

				<tr>
					<th class="text-center" width="50%">Total Tax</th>
					<td class="text-center">{{$total_after_tax- $total_before_tax}}</td>
				</tr>
				<tr>
					<th class="text-center" width="50%">Total (Tax Inclusive)</th>
					<td class="text-center">{{$total_after_tax}}</td>
				</tr>
		</table>

</div>
<div class="row  hide" style="color: #000000 !important;">

	

	<div class="col-xs-6">
        <div class="table-responsive">
          	<table class="table table-slim">
				<tbody>
					@if(!empty($receipt_details->total_quantity_label))
						<tr>
							<th style="width:70%">
								{!! $receipt_details->total_quantity_label !!}
							</th>
							<td class="text-right">
								{{$receipt_details->total_quantity}}
							</td>
						</tr>
					@endif

					@if(!empty($receipt_details->total_items_label))
						<tr>
							<th style="width:70%">
								{!! $receipt_details->total_items_label !!}
							</th>
							<td class="text-right">
								{{$receipt_details->total_items}}
							</td>
						</tr>
					@endif
					<tr>
						<th style="width:70%">
							{!! $receipt_details->subtotal_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->subtotal}}
						</td>
					</tr>
					@if(!empty($receipt_details->total_exempt_uf))
					<tr>
						<th style="width:70%">
							@lang('lang_v1.exempt')
						</th>
						<td class="text-right">
							{{$receipt_details->total_exempt}}
						</td>
					</tr>
					@endif
					<!-- Shipping Charges -->
					@if(!empty($receipt_details->shipping_charges))
						<tr>
							<th style="width:70%">
								{!! $receipt_details->shipping_charges_label !!}
							</th>
							<td class="text-right">
								{{$receipt_details->shipping_charges}}
							</td>
						</tr>
					@endif

					@if(!empty($receipt_details->packing_charge))
						<tr>
							<th style="width:70%">
								{!! $receipt_details->packing_charge_label !!}
							</th>
							<td class="text-right">
								{{$receipt_details->packing_charge}}
							</td>
						</tr>
					@endif

					<!-- Discount -->
					@if( !empty($receipt_details->discount) )
						<tr>
							<th>
								{!! $receipt_details->discount_label !!}
							</th>

							<td class="text-right">
								(-) {{$receipt_details->discount}}
							</td>
						</tr>
					@endif

					@if( !empty($receipt_details->total_line_discount) )
						<tr>
							<th>
								{!! $receipt_details->line_discount_label !!}
							</th>

							<td class="text-right">
								(-) {{$receipt_details->total_line_discount}}
							</td>
						</tr>
					@endif

					@if( !empty($receipt_details->additional_expenses) )
						@foreach($receipt_details->additional_expenses as $key => $val)
							<tr>
								<td>
									{{$key}}:
								</td>

								<td class="text-right">
									(+) {{$val}}
								</td>
							</tr>
						@endforeach
					@endif

					@if( !empty($receipt_details->reward_point_label) )
						<tr>
							<th>
								{!! $receipt_details->reward_point_label !!}
							</th>

							<td class="text-right">
								(-) {{$receipt_details->reward_point_amount}}
							</td>
						</tr>
					@endif

					<!-- Tax -->
					@if( !empty($receipt_details->tax) )
						<tr>
							<th>
								{!! $receipt_details->tax_label !!}
							</th>
							<td class="text-right">
								(+) {{$receipt_details->tax}}
							</td>
						</tr>
					@endif

					@if( $receipt_details->round_off_amount > 0)
						<tr>
							<th>
								{!! $receipt_details->round_off_label !!}
							</th>
							<td class="text-right">
								{{$receipt_details->round_off}}
							</td>
						</tr>
					@endif

					<!-- Total -->
					<tr class="hide">
						<th>
							{!! $receipt_details->total_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->total}}
							@if(!empty($receipt_details->total_in_words))
								<br>
								
							@endif
						</td>
					</tr>
					
			<!-- Total Paid-->
			@if(!empty($receipt_details->total_paid))
				<tr>
					<th>
						{!! $receipt_details->total_paid_label !!}
					</th>
					<td class="text-right">
						{{$receipt_details->total_paid}}
					</td>
				</tr>
			@endif

			<!-- Total Due-->
			@if(!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
			<tr>
				<th>
					{!! $receipt_details->total_due_label !!}
				</th>
				<td class="text-right">
					{{$receipt_details->total_due}}
				</td>
			</tr>
			@endif

                    @if(!empty($receipt_details->all_due))
                    <tr>
                        <th>
                            {!! $receipt_details->all_bal_label !!}
                        </th>
                        <td class="text-right">
                            {{$receipt_details->all_due}}
                        </td>
                    </tr>
                    @endif
				</tbody>
        	</table>
        </div>
    </div>

	<div class="col-xs-6">

		<table class="table table-slim">

			@if(!empty($receipt_details->payments))
				@foreach($receipt_details->payments as $payment)
					<tr class="hide">
						<td>{{$payment['method']}}</td>
						<td class="text-right" >{{$payment['amount']}}</td>
						<td class="text-right">{{$payment['date']}}</td>
					</tr>
				@endforeach
			@endif

		</table>
	</div>

    <div class="border-bottom col-md-12">
	    @if(empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label) )
	        <!-- tax -->
	        @if(!empty($receipt_details->taxes))
	        	<table class="table table-slim table-bordered">
	        		<tr>
	        			<th colspan="2" class="text-center">{{$receipt_details->tax_summary_label}}</th>
	        		</tr>
	        		@foreach($receipt_details->taxes as $key => $val)
	        			<tr>
	        				<td class="text-center"><b>{{$key}}</b></td>
	        				<td class="text-center">{{$val}}</td>
	        			</tr>
	        		@endforeach
	        	</table>
	        @endif
	    @endif
	</div>

	@if(!empty($receipt_details->additional_notes))
	    <div class="col-xs-12">
	    	<p>{!! nl2br($receipt_details->additional_notes) !!}</p>
	    </div>
    @endif
    
</div>
<div class="row" style=" color: #000000 !important; border-top:1px solid black; border-bottom:1px solid black;">

@if($receipt_details->show_barcode || $receipt_details->show_qr_code)
		<div class="@if(!empty($receipt_details->footer_text)) col-xs-12 @else col-xs-12 @endif text-center">
        Sales Note:<br>
        السعر شامل القيمة المضافة
			@if($receipt_details->show_barcode)
				{{-- Barcode --}}
				<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
			@endif
			
			
		</div>
	@endif
	@if(!empty($receipt_details->footer_text))
	<div class="@if($receipt_details->show_barcode || $receipt_details->show_qr_code) col-xs-12 @else col-xs-12 @endif">
		{!! $receipt_details->footer_text !!}
	</div>
	@endif
	
</div>
