<!-- business information here -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- <link rel="stylesheet" href="style.css"> -->
        <title>Receipt-{{$receipt_details->invoice_no}}</title>
        <style>
            body{
                direction: rtl;
                font-family: 'Arial', sans-serif;
                text-align: right;
            }
            .tabl-border {
                border : 2px solid black;
                border-radius: 15px !important;
                width: 100%;
                
            }
            .text-left{
                text-align: right;
            }
            .text-right{
                text-align: left;
            }
            .f-right{
                float:left ;
            }
            .f-left{
                float:right ;
            }
            .tabl-border td,.tabl-border th{
                border : 2px solid black;
                padding: 5px;
                border-radius: 20px !important;
                
                
            }
        </style>
    </head>
    <body>
        <div class="ticket">
			@if(empty($receipt_details->letter_head))
				@if(!empty($receipt_details->logo))
					<div class="text-box centered">
						<img style="max-height: 100px; width: auto;" src="{{$receipt_details->logo}}" alt="Logo">
					</div>
				@endif
				<div class="text-box">
				<p class="centered">
					<!-- Header text -->
					@if(!empty($receipt_details->header_text))
						<span class="headings">{!! $receipt_details->header_text !!}</span>
						<br/>
					@endif

					<!-- business information here -->
					@if(!empty($receipt_details->display_name))
						<span class="headings">
							{{$receipt_details->display_name}}
						</span>
						<br/>
					@endif
					
					@if(!empty($receipt_details->address))
						{!! $receipt_details->address !!}
						<br/>
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

					@if(!empty($receipt_details->sub_heading_line1))
						{{ $receipt_details->sub_heading_line1 }}<br/>
					@endif
					@if(!empty($receipt_details->sub_heading_line2))
						{{ $receipt_details->sub_heading_line2 }}<br/>
					@endif
					@if(!empty($receipt_details->sub_heading_line3))
						{{ $receipt_details->sub_heading_line3 }}<br/>
					@endif
					@if(!empty($receipt_details->sub_heading_line4))
						{{ $receipt_details->sub_heading_line4 }}<br/>
					@endif		
					@if(!empty($receipt_details->sub_heading_line5))
						{{ $receipt_details->sub_heading_line5 }}<br/>
					@endif

					@if(!empty($receipt_details->tax_info1))
						<br><b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
					@endif

					@if(!empty($receipt_details->tax_info2))
						<b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
					@endif			
				</p>
				</div>
                <hr>
                <center>
                    <h4> مبيعات</h4>
                </center>
              
			@endif
				@if(!empty($receipt_details->letter_head))
					<div class="text-box">
						<img style="width: 100%;margin-bottom: 10px;" src="{{$receipt_details->letter_head}}">
					</div>
				@endif
			<div class="border-top textbox-info">
				
				<p class="f-right">
					{{$receipt_details->invoice_no}}
				</p>
                <p class="f-left"><strong>{!! $receipt_details->invoice_no_prefix !!}</strong></p>
			</div>
			<div class="textbox-info">
				
				<p class="f-right">
					{{$receipt_details->invoice_date}}
				</p>
                <p class="f-left"><strong>{!! $receipt_details->date_label !!}</strong></p>
			</div>
			
			@if(!empty($receipt_details->due_date_label))
				<div class="textbox-info">
					
					<p class="f-right">{{$receipt_details->due_date ?? ''}}</p>
                    <p class="f-left"><strong>{{$receipt_details->due_date_label}}</strong></p>
				</div>
			@endif

			@if(!empty($receipt_details->sales_person_label))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->sales_person_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->sales_person}}</p>
				</div>
			@endif
			@if(!empty($receipt_details->commission_agent_label))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->commission_agent_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->commission_agent}}</p>
				</div>
			@endif

			@if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->brand_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->repair_brand}}</p>
				</div>
			@endif

			@if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->device_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->repair_device}}</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->model_no_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->repair_model_no}}</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
				<div class="textbox-info">
					<p class="f-left"><strong>{{$receipt_details->serial_no_label}}</strong></p>
				
					<p class="f-right">{{$receipt_details->repair_serial_no}}</p>
				</div>
			@endif

			@if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!! $receipt_details->repair_status_label !!}
					</strong></p>
					<p class="f-right">
						{{$receipt_details->repair_status}}
					</p>
				</div>
        	@endif

        	@if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
	        	<div class="textbox-info">
	        		<p class="f-left"><strong>
	        			{!! $receipt_details->repair_warranty_label !!}
	        		</strong></p>
	        		<p class="f-right">
	        			{{$receipt_details->repair_warranty}}
	        		</p>
	        	</div>
        	@endif

        	<!-- Waiter info -->
			@if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
	        	<div class="textbox-info">
	        		<p class="f-left"><strong>
	        			{!! $receipt_details->service_staff_label !!}
	        		</strong></p>
	        		<p class="f-right">
	        			{{$receipt_details->service_staff}}
					</p>
	        	</div>
	        @endif

	        @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
	        	<div class="textbox-info">
	        		<p class="f-left"><strong>
	        			@if(!empty($receipt_details->table_label))
							<b>{!! $receipt_details->table_label !!}</b>
						@endif
	        		</strong></p>
	        		<p class="f-right">
	        			{{$receipt_details->table}}
	        		</p>
	        	</div>
	        @endif

			@if (!empty($receipt_details->sell_custom_field_1_value))
				<div class="textbox-info">
					<p class="f-left"><strong>{!! $receipt_details->sell_custom_field_1_label !!}</strong></p>
					<p class="f-right">
						{{$receipt_details->sell_custom_field_1_value}}
					</p>
				</div>
			@endif
			@if (!empty($receipt_details->sell_custom_field_2_value))
				<div class="textbox-info">
					<p class="f-left"><strong>{!! $receipt_details->sell_custom_field_2_label !!}</strong></p>
					<p class="f-right">
						{{$receipt_details->sell_custom_field_2_value}}
					</p>
				</div>
			@endif
			@if (!empty($receipt_details->sell_custom_field_3_value))
				<div class="textbox-info">
					<p class="f-left"><strong>{!! $receipt_details->sell_custom_field_3_label !!}</strong></p>
					<p class="f-right">
						{{$receipt_details->sell_custom_field_3_value}}
					</p>
				</div>
			@endif
			@if (!empty($receipt_details->sell_custom_field_4_value))
				<div class="textbox-info">
					<p class="f-left"><strong>{!! $receipt_details->sell_custom_field_4_label !!}</strong></p>
					<p class="f-right">
						{{$receipt_details->sell_custom_field_4_value}}
					</p>
				</div>
			@endif

	        <!-- customer info -->
	        <div class="textbox-info">
	        	<p style="vertical-align: top;"><strong>
	        		{{$receipt_details->customer_label ?? ''}}
	        	</strong></p>

	        	<p>
	        		@if(!empty($receipt_details->customer_info))
	        			<div class="bw">
						{!! $receipt_details->customer_info !!}
						</div>
					@endif
	        	</p>
	        </div>
			
			@if(!empty($receipt_details->client_id_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{{ $receipt_details->client_id_label }}
					</strong></p>
					<p class="f-right">
						{{ $receipt_details->client_id }}
					</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->customer_tax_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{{ $receipt_details->customer_tax_label }}
					</strong></p>
					<p class="f-right">
						{{ $receipt_details->customer_tax_number }}
					</p>
				</div>
			@endif

			@if(!empty($receipt_details->customer_custom_fields))
				<div class="textbox-info">
					<p class="centered">
						{!! $receipt_details->customer_custom_fields !!}
					</p>
				</div>
			@endif
			
			@if(!empty($receipt_details->customer_rp_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{{ $receipt_details->customer_rp_label }}
					</strong></p>
					<p class="f-right">
						{{ $receipt_details->customer_total_rp }}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_1_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_1_label!!} 
					</strong></p>
					<p class="f-right">
						{!!$receipt_details->shipping_custom_field_1_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_2_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_2_label!!} 
					</strong></p>
					<p class="f-right">
						{!!$receipt_details->shipping_custom_field_2_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_3_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_3_label!!} 
					</strong></p>
					<p class="f-right">
						{!!$receipt_details->shipping_custom_field_3_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_4_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_4_label!!} 
					</strong></p>
					<p class="f-right">
						{!!$receipt_details->shipping_custom_field_4_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->shipping_custom_field_5_label))
				<div class="textbox-info">
					<p class="f-left"><strong>
						{!!$receipt_details->shipping_custom_field_5_label!!} 
					</strong></p>
					<p class="f-right">
						{!!$receipt_details->shipping_custom_field_5_value ?? ''!!}
					</p>
				</div>
			@endif
			@if(!empty($receipt_details->sale_orders_invoice_no))
				<div class="textbox-info">
					<p class="f-left"><strong>
						@lang('restaurant.order_no')
					</strong></p>
					<p class="f-right">
						{!!$receipt_details->sale_orders_invoice_no ?? ''!!}
					</p>
				</div>
			@endif

			@if(!empty($receipt_details->sale_orders_invoice_date))
				<div class="textbox-info">
					<p class="f-left"><strong>
						@lang('lang_v1.order_dates')
					</strong></p>
					<p class="f-right">
						{!!$receipt_details->sale_orders_invoice_date ?? ''!!}
					</p>
				</div>
			@endif
			<div class="bb-lg mt-15 mb-10"></div>
            <table style="padding-top: 5px !important" class=" tabl-border" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th class="description">
                        	اسم الصنف
                        </th>
                        <th class="text-right">
                        	الكمية
                        </th>
                        <th class="text-right">
                        	السعر
                        </th>
                        <th class="text-right">
                            المجموع</th>
                <tbody>
                	@forelse($receipt_details->lines as $line)
	                    <tr class="bb-lg">
	                        <td class="description">
	                        	<div style="display:flex; width: 100%;">
	                        		<p class="m-0 mt-5" style="white-space: nowrap;">#{{$loop->iteration}}.&nbsp;</p>
	                        		<p class="text-left m-0 mt-5 pull-left">{{$line['name']}}  
			                        	@if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif
			                        	@if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif
			                        	@if(!empty($line['product_description']))
			                        		<br>
			                            	<span class="f-8">
			                            		{!!$line['product_description']!!}
			                            	</span>
			                            @endif
			                        	@if(!empty($line['sell_line_note']))
			                        	<br>
	                        			<span class="f-8">
			                        	{!!$line['sell_line_note']!!}
			                        	</span>
			                        	@endif 
			                        	@if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif 
			                        	@if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif

			                        	@if(!empty($line['variation']))
			                        		,
			                        		{{$line['product_variation']}} {{$line['variation']}}
			                        	@endif
			                        	@if(!empty($line['warranty_name']))
			                            	, 
			                            	<small>
			                            		{{$line['warranty_name']}}
			                            	</small>
			                            @endif
			                            @if(!empty($line['warranty_exp_date']))
			                            	<small>
			                            		- {{@format_date($line['warranty_exp_date'])}}
			                            	</small>
			                            @endif
			                            @if(!empty($line['warranty_description']))
			                            	<small> {{$line['warranty_description'] ?? ''}}</small>
			                            @endif

			                            @if($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
				                            <br><small>
				                            	1 {{$line['units']}} = {{$line['base_unit_multiplier']}} {{$line['base_unit_name']}} <br> {{$line['quantity']}} x {{$line['base_unit_multiplier']}} = {{$line['orig_quantity']}} {{$line['base_unit_name']}} <br>
                            					{{$line['base_unit_price']}} x {{$line['orig_quantity']}} = {{$line['line_total']}}
				                            </small>
				                            @endif
	                        		</p>
	                        	</div>
                            </td>
                            <td class="text-right">
	                        {{ $line['quantity'] }}
                            </td>
                            <td class="text-right">
                            {{$line['unit_price_before_discount']}}
                            </td>
                            <td>
                            @if(empty($receipt_details->hide_price))
                            <p class="text-right width-40 price m-0 bw">{{$line['line_total']}}</p>
                            @endif
                            </td>
	                        	
	                       
	                    </tr>
	                    @if(!empty($line['modifiers']))
							@foreach($line['modifiers'] as $modifier)
								<tr>
									<td>
										<div style="display:flex;">
	                        				<p style="width: 28px;" class="m-0">
	                        				</p>
	                        				<p class="text-left width-60 m-0" style="margin:0;">
	                        					{{$modifier['name']}} 
	                        					@if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
			                            		@if(!empty($modifier['sell_line_note']))({!!$modifier['sell_line_note']!!}) @endif
	                        				</p>
	                        				<p class="text-right width-40 m-0">
	                        					{{$modifier['variation']}}
	                        				</p>
	                        			</div>	
	                        			<div style="display:flex;">
	                        				<p style="width: 28px;"></p>
	                        				<p class="text-left width-50 quantity">
	                        					{{$modifier['quantity']}}
	                        					@if(empty($receipt_details->hide_price))
	                        					x {{$modifier['unit_price_inc_tax']}}
	                        					@endif
	                        				</p>
	                        				<p class="text-right width-50 price">
	                        					{{$modifier['line_total']}}
	                        				</p>
	                        			</div>		                             
			                        </td>
			                    </tr>
							@endforeach
						@endif
                    @endforeach
                </tbody>
            </table >
            <br>
            <br>
            <table class="tabl-border" style="font-size: 12px;">
            @if(!empty($receipt_details->total_quantity_label))
            <tr>
                <td>{!! $receipt_details->total_quantity_label !!}</td>
                <td>{{$receipt_details->total_quantity}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->total_items_label))
            <tr>
                <td>{!! $receipt_details->total_items_label !!}</td>
                <td>{{$receipt_details->total_items}}</td>
            </tr>
            @endif

            @if(empty($receipt_details->hide_price))
            <tr>
                <td><strong>{!! $receipt_details->subtotal_label !!}</strong></td>
                <td><strong>{{$receipt_details->subtotal}}</strong></td>
            </tr>

            @if(!empty($receipt_details->shipping_charges))
            <tr>
                <td>{!! $receipt_details->shipping_charges_label !!}</td>
                <td>{{$receipt_details->shipping_charges}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->packing_charge))
            <tr>
                <td>{!! $receipt_details->packing_charge_label !!}</td>
                <td>{{$receipt_details->packing_charge}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->discount))
            <tr>
                <td>{!! $receipt_details->discount_label !!}</td>
                <td>(-) {{$receipt_details->discount}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->total_line_discount))
            <tr>
                <td>{!! $receipt_details->line_discount_label !!}</td>
                <td>(-) {{$receipt_details->total_line_discount}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->additional_expenses))
            @foreach($receipt_details->additional_expenses as $key => $val)
            <tr>
                <td>{{$key}}:</td>
                <td>(+) {{$val}}</td>
            </tr>
            @endforeach
            @endif

            @if(!empty($receipt_details->reward_point_label))
            <tr>
                <td>{!! $receipt_details->reward_point_label !!}</td>
                <td>(-) {{$receipt_details->reward_point_amount}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->tax))
            <tr>
                <td>{!! $receipt_details->tax_label !!}</td>
                <td>(+) {{$receipt_details->tax}}</td>
            </tr>
            @endif

            @if($receipt_details->round_off_amount > 0)
            <tr>
                <td>{!! $receipt_details->round_off_label !!}</td>
                <td>{{$receipt_details->round_off}}</td>
            </tr>
            @endif

            <tr>
                <td><strong>{!! $receipt_details->total_label !!}</strong></td>
                <td><strong>{{$receipt_details->total}}</strong></td>
            </tr>

            @if(!empty($receipt_details->total_in_words))
            <tr style="display: none;">
                <td colspan="2" class="text-right">
                    <small>({{$receipt_details->total_in_words}})</small>
                </td>
            </tr>
            @endif

            @if(!empty($receipt_details->payments))
            @foreach($receipt_details->payments as $payment)
            <tr>
                <td>{{$payment['method']}} ({{$payment['date']}})</td>
                <td>{{$payment['amount']}}</td>
            </tr>
            @endforeach
            @endif

            @if(!empty($receipt_details->total_paid))
            <tr>
                <td>{!! $receipt_details->total_paid_label !!}</td>
                <td>{{$receipt_details->total_paid}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
            <tr>
                <td>{!! $receipt_details->total_due_label !!}</td>
                <td>{{$receipt_details->total_due}}</td>
            </tr>
            @endif

            @if(!empty($receipt_details->all_due))
            <tr>
                <td>{!! $receipt_details->all_bal_label !!}</td>
                <td>{{$receipt_details->all_due}}</td>
            </tr>
            @endif
            @endif
        </table>

            <div class="border-bottom width-100">&nbsp;</div>
            @if(empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label) )
	            <!-- tax -->
	            @if(!empty($receipt_details->taxes))
	            	<table class="border-bottom width-100 table-f-12">
	            		<tr>
	            			<th colspan="2" class="text-center">{{$receipt_details->tax_summary_label}}</th>
	            		</tr>
	            		@foreach($receipt_details->taxes as $key => $val)
	            			<tr>
	            				<td class="left">{{$key}}</td>
	            				<td class="right">{{$val}}</td>
	            			</tr>
	            		@endforeach
	            	</table>
	            @endif
            @endif

            @if(!empty($receipt_details->additional_notes))
	            <p class="centered" >
	            	{!! nl2br($receipt_details->additional_notes) !!}
	            </p>
            @endif

            {{-- Barcode --}}
			@if($receipt_details->show_barcode)
				<br/>
				<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
			@endif

			@if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
				<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE')}}">
			@endif

			@if(!empty($receipt_details->footer_text))
				<p class="centered">
					{!! $receipt_details->footer_text !!}
				</p>
			@endif
        </div>
        <!-- <button id="btnPrint" class="hidden-print">Print</button>
        <script src="script.js"></script> -->
    </body>
</html>

<style type="text/css">
.f-8 {
	font-size: 8px !important;
}
body {
	color: #000000;
}
@media print {
	* {
    	font-size: 12px;
    	font-family: 'Times New Roman';
    	word-break: break-all;
	}
	.f-8 {
		font-size: 8px !important;
	}

.headings{
	font-size: 16px;
	font-weight: 700;
	text-transform: uppercase;
}

.sub-headings{
	font-size: 15px;
	font-weight: 700;
}

.border-top{
    border-top: 1px solid #242424;
}
.border-bottom{
	border-bottom: 1px solid #242424;
}

.border-bottom-dotted{
	border-bottom: 1px dotted darkgray;
}

td.serial_number, th.serial_number{
	width: 5%;
    max-width: 5%;
}

td.description,
th.description {
    width: 35%;
    max-width: 35%;
}

td.quantity,
th.quantity {
    width: 15%;
    max-width: 15%;
    word-break: break-all;
}
td.unit_price, th.unit_price{
	width: 25%;
    max-width: 25%;
    word-break: break-all;
}

td.price,
th.price {
    width: 20%;
    max-width: 20%;
    word-break: break-all;
}

.centered {
    text-align: center;
    align-content: center;
}

.ticket {
    width: 100%;
    max-width: 100%;
}

img {
    max-width: inherit;
    width: auto;
}

    .hidden-print,
    .hidden-print * {
        display: none !important;
    }
}
.table-info {
	width: 100%;
}
.table-info tr:first-child td, .table-info tr:first-child th {
	padding-top: 8px;
}
.table-info th {
	text-align: left;
}
.table-info td {
	text-align: right;
}
.logo {
	float: left;
	width:35%;
	padding: 10px;
}

.text-with-image {
	float: left;
	width:65%;
}
.text-box {
	width: 100%;
	height: auto;
}
.m-0 {
	margin:0;
}
.textbox-info {
	clear: both;
}
.textbox-info p {
	margin-bottom: 0px
}
.flex-box {
	display: flex;
	width: 100%;
}
.flex-box p {
	width: 50%;
	margin-bottom: 0px;
	white-space: nowrap;
}

.table-f-12 th, .table-f-12 td {
	font-size: 12px;
	word-break: break-word;
}

.bw {
	word-break: break-word;
}
.bb-lg {
	border-bottom: 1px solid lightgray;
}
</style>