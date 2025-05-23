@php
    $hide_tax = '';
    if( session()->get('business.enable_inline_tax') == 0){
        $hide_tax = 'hide';
    }
    $currency_precision = session('business.currency_precision', 2);
    $quantity_precision = session('business.quantity_precision', 2);
@endphp
<div class="table-responsive">
    <table class="table table-condensed table-bordered table-th-green text-center table-striped" 
    id="purchase_entry_table">
        <thead>
              <tr>
                <th>#</th>
                <th>@lang( 'product.product_name' )</th>
                <th>@lang( 'constructions::lang.description' )</th>
                <th>@if(empty($is_purchase_order)) @lang( 'purchase.purchase_quantity' ) @else @lang( 'lang_v1.order_quantity' ) @endif</th>
                <th>@lang( 'constructions::lang.unit_text' )</th>
                <th>@lang( 'lang_v1.unit_cost_before_discount' )</th>
                <th>@lang( 'lang_v1.discount_percent' )</th>
                <th>@lang( 'purchase.unit_cost_before_tax' )</th>
                <th class="{{$hide_tax}}">@lang( 'purchase.subtotal_before_tax' )</th>
                <th class="{{$hide_tax}}">@lang( 'purchase.product_tax' )</th>
                <th class="{{$hide_tax}}">@lang( 'purchase.net_cost' )</th> 
                <th>@lang( 'constructions::lang.implementation_rate' )</th>
                <th>@lang( 'purchase.line_total' )</th>
                
               
                <th>
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </th>
              </tr>
        </thead>
        <tbody>
    <?php $row_count = 0; ?>
    @foreach($purchase->purchase_lines as $purchase_line)
        <tr @if(!empty($purchase_line->purchase_order_line) && !empty($common_settings['enable_purchase_order'])) data-purchase_order_id="{{$purchase_line->purchase_order_line->transaction_id}}" @endif  @if(!empty($purchase_line->purchase_requisition_line) && !empty($common_settings['enable_purchase_requisition'])) data-purchase_requisition_id="{{$purchase_line->purchase_requisition_line->transaction_id}}" @endif>
            <td><span class="sr_number"></span></td>
            <td>
                {{ $purchase_line->product->name }} ({{$purchase_line->variations->sub_sku}})
                @if( $purchase_line->product->type == 'variable') 
                    <br/>(<b>{{ $purchase_line->variations->product_variation->name}}</b> : {{ $purchase_line->variations->name}})
                @endif
            </td>
            <td>
                {!! Form::textarea('purchases[' . $loop->index . '][description]', $purchase_line->description, ['class' => 'form-control input-sm']); !!}
            </td>
            <td>
                @if(!empty($purchase_line->purchase_order_line_id) && !empty($common_settings['enable_purchase_order']))
                    {!! Form::hidden('purchases[' . $loop->index . '][purchase_order_line_id]', $purchase_line->purchase_order_line_id ); !!}
                @endif

                @if(!empty($purchase_line->purchase_requisition_line_id) && !empty($common_settings['enable_purchase_requisition']))
                    {!! Form::hidden('purchases[' . $loop->index . '][purchase_requisition_line_id]', $purchase_line->purchase_requisition_line_id ); !!}
                @endif

                {!! Form::hidden('purchases[' . $loop->index . '][product_id]', $purchase_line->product_id ); !!}
                {!! Form::hidden('purchases[' . $loop->index . '][variation_id]', $purchase_line->variation_id ); !!}
                {!! Form::hidden('purchases[' . $loop->index . '][purchase_line_id]',
                $purchase_line->id); !!}

                @php
                    $check_decimal = 'false';
                    if($purchase_line->product->unit->allow_decimal == 0){
                        $check_decimal = 'true';
                    }
                    $max_quantity = 0;

                    if(!empty($purchase_line->purchase_order_line_id) && !empty($common_settings['enable_purchase_order'])){
                        $max_quantity = $purchase_line->purchase_order_line->quantity - $purchase_line->purchase_order_line->po_quantity_purchased + $purchase_line->quantity;
                    }
                @endphp

                <input type="text" 
                name="purchases[{{$loop->index}}][quantity]" 
                value="{{@format_quantity($purchase_line->quantity)}}"
                class="form-control input-sm purchase_quantity input_number mousetrap"
                required
                data-rule-abs_digit={{$check_decimal}}
                data-msg-abs_digit="{{__('lang_v1.decimal_value_not_allowed')}}"
                @if(!empty($max_quantity))
                    data-rule-max-value="{{$max_quantity}}"
                    data-msg-max-value="{{__('lang_v1.max_quantity_quantity_allowed', ['quantity' => $max_quantity])}}" 
                @endif
                >

                <input type="hidden" class="base_unit_cost" value="{{$purchase_line->variations->default_purchase_price}}">
                @if(!empty($purchase_line->sub_units_options))
                    <br>
                    <select name="purchases[{{$loop->index}}][sub_unit_id]" class="form-control input-sm sub_unit">
                        @foreach($purchase_line->sub_units_options as $sub_units_key => $sub_units_value)
                            <option value="{{$sub_units_key}}" 
                                data-multiplier="{{$sub_units_value['multiplier']}}"
                                @if($sub_units_key == $purchase_line->sub_unit_id) selected @endif>
                                {{$sub_units_value['name']}}
                            </option>
                        @endforeach
                    </select>
                @else
                    {{ $purchase_line->product->unit->short_name }}
                @endif

                <input type="hidden" name="purchases[{{$loop->index}}][product_unit_id]" value="{{$purchase_line->product->unit->id}}">

                <input type="hidden" class="base_unit_selling_price" value="{{$purchase_line->variations->sell_price_inc_tax}}">

                @if(!empty($purchase_line->product->second_unit))
                    <br><br>
                    <span style="white-space: nowrap;">
                    @lang('lang_v1.quantity_in_second_unit', ['unit' => $purchase_line->product->second_unit->short_name])*:</span><br>
                    <input type="text" 
                    name="purchases[{{$row_count}}][secondary_unit_quantity]" 
                    value="{{@format_quantity($purchase_line->secondary_unit_quantity)}}"
                    class="form-control input-sm input_number"
                    required>
                @endif
            </td>
            <td>
                {!! Form::text('purchases[' . $loop->index . '][unit_text]', $purchase_line->unit_text, ['class' => 'form-control input-sm']); !!}
            </td>
            <td>
                {!! Form::text('purchases[' . $loop->index . '][pp_without_discount]', number_format($purchase_line->pp_without_discount/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost_without_discount input_number', 'required']); !!}
            </td>
            <td>
                {!! Form::text('purchases[' . $loop->index . '][discount_percent]', number_format($purchase_line->discount_percent, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm inline_discounts input_number', 'required']); !!} <b>%</b>
            </td>
            <td>
                {!! Form::text('purchases[' . $loop->index . '][purchase_price]', 
                number_format($purchase_line->purchase_price/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost input_number', 'required']); !!}
            </td>
            <td class="{{$hide_tax}}">
                <span class="row_subtotal_before_tax">
                    {{number_format($purchase_line->quantity * $purchase_line->purchase_price/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                </span>
                <input type="hidden" class="row_subtotal_before_tax_hidden" value="{{number_format($purchase_line->quantity * $purchase_line->purchase_price/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}">
            </td>

            <td class="{{$hide_tax}}">
                <div class="input-group">
                    <select name="purchases[{{ $loop->index }}][purchase_line_tax_id]" class="form-control input-sm purchase_line_tax_id" placeholder="'Please Select'">
                        <option value="" data-tax_amount="0" @if( empty( $purchase_line->tax_id ) )
                        selected @endif >@lang('lang_v1.none')</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" data-tax_amount="{{ $tax->amount }}" @if( $purchase_line->tax_id == $tax->id) selected @endif >{{ $tax->name }}</option>
                        @endforeach
                    </select>
                    <span class="input-group-addon purchase_product_unit_tax_text">
                        {{number_format($purchase_line->item_tax/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                    </span>
                    {!! Form::hidden('purchases[' . $loop->index . '][item_tax]', number_format($purchase_line->item_tax/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'purchase_product_unit_tax']); !!}
                </div>
            </td>
            <td class="{{$hide_tax}}">
                {!! Form::text('purchases[' . $loop->index . '][purchase_price_inc_tax]', number_format($purchase_line->purchase_price_inc_tax/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm purchase_unit_cost_after_tax input_number', 'required']); !!}
            </td>
            <td>
                @php
                    $implementation_rate = $purchase_line->implementation_rate/100;
                @endphp
                {!! Form::text('purchases[' . $loop->index . '][implementation_rate]', $purchase_line->implementation_rate, ['class' => 'form-control input-sm purchase_implementation_rate input_number', 'required']); !!}
            </td>
            <td>
                <span class="row_subtotal_after_tax">
                {{number_format($purchase_line->purchase_price_inc_tax * $implementation_rate *$purchase_line->quantity/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
                </span>
                <input type="hidden" class="row_subtotal_after_tax_hidden" value="{{number_format($purchase_line->purchase_price_inc_tax * $implementation_rate *$purchase_line->quantity/$purchase->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}">
            </td>

           

            
            <td><i class="fa fa-times remove_purchase_entry_row text-danger" title="Remove" style="cursor:pointer;"></i></td>
        </tr>
        <?php $row_count = $loop->index + 1 ; ?>
    @endforeach
        </tbody>
    </table>
</div>
<input type="hidden" id="row_count" value="{{ $row_count }}">