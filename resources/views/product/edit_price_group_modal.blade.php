
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPriceGroupModalLabel"><h3 class="box-title">@lang('sale.product'): {{$product->name}} ({{$product->sku}})</h3></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'saveSellingPrices']), 'method' => 'post', 'id' => 'selling_price_form_modal' ]) !!}
	            {!! Form::hidden('product_id', $product->id); !!}
            <div class="modal-body">
               
                <table class="table table-condensed table-bordered table-th-green text-center table-striped">
								<thead>
									<tr>
										@if($product->type == 'variable')
											<th>
												@lang('lang_v1.variation')
											</th>
										@endif
										<th>@lang('lang_v1.default_selling_price_inc_tax')</th>
										@foreach($price_groups as $price_group)
											<th>{{$price_group->name}} @show_tooltip(__('lang_v1.price_group_price_type_tooltip'))</th>
										@endforeach
									</tr>
								</thead>
								<tbody>
									@foreach($product->variations as $variation)
										<tr>
										@if($product->type == 'variable')
											<td>
												{{$variation->product_variation->name}} - {{$variation->name}} ({{$variation->sub_sku}})
											</td>
										@endif
										<td><span class="display_currency" data-currency_symbol="true">{{$variation->sell_price_inc_tax}}</span></td>
											@foreach($price_groups as $price_group)
												<td>
													{!! Form::text('group_prices[' . $price_group->id . '][' . $variation->id . '][price]', !empty($variation_prices[$variation->id][$price_group->id]['price']) ? @num_format($variation_prices[$variation->id][$price_group->id]['price']) : 0, ['class' => 'form-control input_number input-sm'] ); !!}
                                                    
                                                    @php
                                                        $price_type = !empty($variation_prices[$variation->id][$price_group->id]['price_type']) ? $variation_prices[$variation->id][$price_group->id]['price_type'] : 'fixed';

                                                        $name = 'group_prices[' . $price_group->id . '][' . $variation->id . '][price_type]';
                                                    @endphp

                                                    <select name={{$name}} class="form-control">
                                                        <option value="fixed" @if($price_type == 'fixed') selected @endif>@lang('lang_v1.fixed')</option>
                                                        <option value="percentage" @if($price_type == 'percentage') selected @endif>@lang('lang_v1.percentage')</option>
                                                    </select>
													<br>
													@foreach($sub_units as $unit)
													<label>{{$unit->actual_name ?? ''}}</label><br>
													{!! Form::text('group_prices[' . $price_group->id . '][' . $variation->id . '][price_per_unit]['.$unit->id.']', !empty($variation_prices[$variation->id][$price_group->id]['price_per_unit'][$unit->id]) ? @num_format($variation_prices[$variation->id][$price_group->id]['price_per_unit'][$unit->id]) : 0, ['class' => 'form-control input_number input-sm'] ); !!}
													<br>
													@endforeach
												</td>
											@endforeach
										</tr>
									@endforeach
								</tbody>
							</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">اغلاق</button>
                <button type="submit" class="btn btn-primary" form="editPriceGroupForm" id="selling_price_form_submit"> حفظ البيانات </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
