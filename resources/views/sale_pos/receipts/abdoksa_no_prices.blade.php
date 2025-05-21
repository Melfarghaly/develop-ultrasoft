<!-- business information here -->
<style>
	.product_table th{
		background-color: #c0e0ff !important;
		border: 1px solid black !important;
	}
	.product_table td{
		border: 1px solid black !important;
	}
    body {
      font-family: Arial, sans-serif;
      direction: rtl;
      text-align: right;
    }
    .invoice-table {
      width: 100%;
      border-collapse: collapse;
    }
    .invoice-table td {
      padding: 1px 2px;
    }
    .invoice-table .label {
      font-weight: bold;
      color: #000;
      border: none !important;
    }
    .invoice-table .value {
      font-weight: normal;
    }
    .section {
      display: inline-block;
      width: 48%;
      vertical-align: top;
    }
    .section:first-child {
      padding-right: 2%;
    }
    .section:last-child {
      padding-left: 2%;
    }
    .line_tables {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .line_tables th, .line_tables td {
      border: 1px solid #000;
      padding: 2px;
      text-align: center;
    }

    .line_tables th {
      background-color: #f2f2f2;
      font-weight: bold;
    }
    .summary-table{
        float: left;
    }
    .amount-in-words{
        float: right;
    }
    .summary-table td {
        padding: 2px 4px;
      text-align: right;
      font-weight: bold;
      border: 1px solid #000;
    }

    .total-row {
      background-color: #f2f2f2;
      font-weight: bold;
    }

    .amount-in-words {
      text-align: left;
      margin-top: 10px;
      font-weight: bold;
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
        <div class="invoice">
        <div class="section">
        <table class="invoice-table">
            <tr>
            <td class="label">رقم الفاتورة</td>
            <td class="value">{{ $receipt_details->invoice_no }}</td>
            <td class="label">Inv no </td>
          <tr>
            <td class="label">التاريخ</td>
            <td class="value">
                {{ date('Y-m-d',strtotime($receipt_details->invoice_date)) }}
            </td>
                <td class="label">Date</td>
            </tr>
            <tr>
				<td class="label">{{ $receipt_details->commission_agent_label }} </td>
				<td class="value">
					@if(!empty($receipt_details->commission_agent))
						{{ $receipt_details->commission_agent }}
					@endif
				</td>
			</tr>
			<tr>
				<td class="label">الرقم الداخلي</td>
				<td class="value">
				
					@if(!empty($receipt_details->additional_notes))
						{{ $receipt_details->additional_notes }}
					@endif
				</td>
			</tr>

        </table>
    </div>
    <div class="section">
      <table class="invoice-table">
        <tr>
          <td class="label">العميل</td>
          <td class="value" style="width:70%">{{ $receipt_details->transaction->contact->name }}  </td>
        </tr>
        <tr>
          <td class="label"> العنوان</td>
          <td class="value">  {{ $receipt_details->transaction->contact->contact_address }} </td>
        
        <tr>
			<td> الموبايل</td>
		<td class="value">{{ $receipt_details->transaction->contact->mobile }}</td>
          
        </tr>
        <tr>
          
        </tr>
      </table>
    </div>
   
  </div>
	
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
        <table class="line_tables">
    <thead>
      <tr>
		<th style="width: 5%;">#</th>
        <th>رقم الصنف</th>
        <th>اسم الصنف</th>
        <th>الوحدة</th>
        <th>الكمية</th>
        <th>السعر</th>
        <th>الإجمالي</th>
		{{-- 
        <th>الضريبة</th>
        <th>الإجمالي بالضريبة</th>
		--}}
      </tr>
    </thead>
    <tbody>
        @php
        $total_before_tax = 0;
        $total_tax = 0;
        $total_after_tax = 0;
        $total_quantity = 0;
        @endphp
    @forelse($receipt_details->lines as $k=> $line)
					<tr>
						<td class="text-center">
							{{$loop->iteration}}
						</td>
						<td> @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif</td>
						<td>
							@if(!empty($line['image']))
								<img src="{{$line['image']}}" alt="Image" width="50" style="float: left; margin-right: 8px;">
							@endif
                            {{$line['name']}} {{$line['product_variation']}} {{$line['variation']}} 
                            @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif
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
                        <td>{{$line['units']}}</td>
						<td class="text-right">
							
								@php
								$total_quantity+=$line['quantity_uf'];
								@endphp
								{{$line['quantity_uf']}}</td>

							@if($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                            <br><small>
                            	{{$line['quantity']}} x {{$line['base_unit_multiplier']}} = {{$line['orig_quantity']}} {{$line['base_unit_name']}}
                            </small>
                            @endif
						</td>
						<td class="text-right">{{$line['unit_price_before_discount']}}</td>
						<td>
                        @php
						$total_before_tax+=$line['unit_price_before_discount_uf'] * $line['quantity_uf'];
						@endphp
						{{$line['unit_price_before_discount_uf'] * $line['quantity_uf']}}</td>
                        </td>
                        
                        @php
						$total_tax+=$line['line_total_uf'] - ($line['unit_price_before_discount_uf'] * $line['quantity_uf']);
						@endphp
						{{-- 
                        <td class="text-right"></td>
						--}}
						
						@if(!empty($receipt_details->item_discount_label))
							<td class="text-right hide">
								{{$line['total_line_discount'] ?? '0.00'}}

								@if(!empty($line['line_discount_percent']))
								 	({{$line['line_discount_percent']}}%)
								@endif
							</td>
						@endif
						@php 
						$total_after_tax+=$line['line_total_uf'];
						@endphp
						{{-- 
						<td class="text-right"></td>
						--}}
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
    <tfoot>
      <tr class="total-row">
        <td colspan="3">الإجمالي<br>Total</td>
        <td>{{ $total_quantity }}</td>
        <td></td>
        <td>{{ $total_before_tax }}</td>
		{{-- 
        <td>{{$total_tax}}</td>
        <td>{{$total_after_tax}}</td>
		--}}
      </tr>
    </tfoot>
  </table>

  <table class="summary-table" style>
    <tbody>
    <tr>
    <td>Total</td><td>الإجمالي </td>
        <td>{{ $total_before_tax }}</td>
      </tr>
      <tr>
      <td>Tax</td><td>الضريبة </td>
        <td>{{ number_format($total_tax + $receipt_details->tax_uf,2) }}</td>
      </tr>
      <tr>
      <td>Due</td><td>الإجمالي بالضريبة</td>
        <td><strong>{{ $receipt_details->total }} </strong></td>
      </tr>
	  {{--
      <tr>
      <td>Paid</td><td>المدفوع</td>
        <td>{{ $receipt_details->total_paid }}</td>
      </tr>
	  <tr>
      <td>Bal. Due</td><td>مستحق الفاتورة  </td>
        <td>{{$receipt_details->total_due}}</td>
      </tr>
      <tr>
      <td>Bal. Prior</td><td> 
	 اجمالي حساب العميل 
	  </td>
        <td>{{$receipt_details->all_due }}</td>
      </tr>
      --}}
    </tbody>
  </table>

  <div class="amount-in-words">
    المطلوب:{{ $receipt_details->total_in_words }}
  </div>
		
	</div>
</div>

<div class="row hide" style="color: #000000 !important;">
	<div class="col-md-12"><hr/></div>
	<div class="col-xs-6">

		<table class="table table-slim">

			@if(!empty($receipt_details->payments))
				@foreach($receipt_details->payments as $payment)
					<tr>
						<td>{{$payment['method']}}</td>
						<td class="text-right" >{{$payment['amount']}}</td>
						<td class="text-right">{{$payment['date']}}</td>
					</tr>
				@endforeach
			@endif

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
		</table>
	</div>

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
					<tr>
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
					
				</tbody>
        	</table>
        </div>
    </div>

	<div class="col-xs-12">
		<table class="table">
		<tr>
						<th  style="border: 1px solid black;">
						<center>({{$receipt_details->total_in_words}})</center>
						</th>
						
					</tr>

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
<div class="row hide" style="color: #000000 !important;">
	@if(!empty($receipt_details->footer_text))
	<div class="@if($receipt_details->show_barcode || $receipt_details->show_qr_code) col-xs-8 @else col-xs-12 @endif">
		{!! $receipt_details->footer_text !!}
	</div>
	@endif
	@if($receipt_details->show_barcode || $receipt_details->show_qr_code)
		<div class="@if(!empty($receipt_details->footer_text)) col-xs-4 @else col-xs-12 @endif text-center">
			@if($receipt_details->show_barcode)
				{{-- Barcode --}}
				<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
			@endif
			
			
		</div>
	@endif
</div>
<div class="row hide">
	<div class="col-xs-4">
		الصندوق
	</div>
	<div class="col-xs-4">
		المستلم
	</div>
	<div class="col-xs-4">
		المخازن
	</div>
</div>
<div class="row ">
   <div class="col-md-12">
        <div class="text-center">
             <p style="    background: #f1f1f1 !important;
    font-size: 19px;
    font-weight: 900;
">شكرا لزيارتكم</p>
        </div>
   </div>
   <div class="col-md-12">
   @if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
				<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE')}}">
			@endif
   </div>
</div>
