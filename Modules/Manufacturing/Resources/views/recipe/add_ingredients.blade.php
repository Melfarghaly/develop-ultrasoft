@extends('layouts.app')
@section('title', __('manufacturing::lang.add_ingredients'))

@section('content')
@include('manufacturing::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black" >@lang('manufacturing::lang.add_ingredients')</h1>
</section>

<!-- Main content -->
<section class="content">
	{!! Form::open(['url' => action([\Modules\Manufacturing\Http\Controllers\RecipeController::class, 'store']), 'method' => 'post', 'id' => 'recipe_form' ]) !!}
	<div id="box_group">
	<div class="box box-solid">
		<div class="box-header"> 
			<h4 class="box-title"><strong>@lang('sale.product'): </strong>{{$variation->product_name}} @if($variation->product_type == 'variable') - {{$variation->product_variation_name}} - {{$variation->name}} @endif</h4>
			<h5 style="color:red">يرجي عند ادخال كل خامة ادخال النسبة مباشرة أولا بأول</h5>
		</div>
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">
					<button type="button" class="tw-dw-btn tw-dw-btn-success tw-text-white pull-right" id="add_ingredient_group">@lang('manufacturing::lang.add_ingredient_group') @show_tooltip(__('manufacturing::lang.ingredient_group_tooltip'))</button>
				</div>
				<div class="col-md-10 col-md-offset-1">
					<div class="form-group">
						{!! Form::label('search_product', __('manufacturing::lang.select_ingredient').':') !!}

						{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('manufacturing::lang.select_ingredient'), 'autofocus' => true ]); !!}

						{!! Form::hidden('variation_id', $variation->id); !!}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<table class="table table-striped table-th-green text-center ingredients_table">
						<thead>
							<tr>
								<th>@lang('manufacturing::lang.ingredient')</th>
								<th>@lang('manufacturing::lang.waste_percent')</th>
								<th>@lang('manufacturing::lang.final_quantity')</th>
								<th>سعر الوحدة</th>
								<th>@lang('lang_v1.price')</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody class="ingredient-row-sortable">
							@php
								$row_index = 0;
								$ingredient_groups = [];
								$ingredient_total = 0;
							@endphp
							@if(!empty($ingredients))
								@foreach($ingredients as $ingredient)
									@php
										$ingredient_obj = (object) $ingredient;
										$price = !empty($ingredient_obj->quantity) ? $ingredient_obj->quantity * $ingredient_obj->dpp_inc_tax : $ingredient_obj->dpp_inc_tax;
										$price = $price * $ingredient_obj->multiplier;
										$ingredient_total += $price;
									@endphp
									@if(empty($ingredient['mfg_ingredient_group_id']))
										@php
											$row_index = $loop->index;
										@endphp

										@include('manufacturing::recipe.ingredient_row', ['ingredient' => (object) $ingredient, 'ig_index' => ''])
										
										@php
											$row_index++;
										@endphp
									@else
										@php
											$ingredient_groups[$ingredient['mfg_ingredient_group_id']][] = $ingredient;
										@endphp
									@endif
								@endforeach
							@endif
						</tbody>
					</table>
				</div>
			</div>
			<span class="btn btn-danger" id="totalQuantity"></span>
		</div>
		
	</div> <!--box end-->
	@php
		$ig_index = 0;
	@endphp
	@foreach($ingredient_groups as $ingredient_group)
		@php
			$ig_name = !empty($ingredient_group[0]['ingredient_group_name']) ? $ingredient_group[0]['ingredient_group_name'] : '';
			$ig_description = !empty($ingredient_group[0]['ig_description']) ? $ingredient_group[0]['ig_description'] : '';
		@endphp
		@include('manufacturing::recipe.ingredient_group', ['ingredients' => $ingredient_group, 'ig_index' => $ig_index, 'ig_name' => $ig_name, 'ig_description' => $ig_description])
		@php
			$ig_index++;
			$row_index += count($ingredient_group);
		@endphp
	@endforeach
	</div>
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">
				<input type="hidden" id="row_index" value="{{$row_index}}">
				<input type="hidden" id="ig_index" value="{{$ig_index}}">
				<div class="col-md-12 text-right">
					<strong>@lang('manufacturing::lang.ingredients_cost'): </strong> <span 
									id="ingredients_cost_text" 
									>{{@num_format($ingredient_total)}}</span>
									<input type="hidden" name="ingredients_cost" id="ingredients_cost" value="{{$recipe->ingredients_cost ?? 0}}">
				</div>
				<div class="col-md-3">
					<div class="form-group">
						{!! Form::label('waste_percent', __('manufacturing::lang.wastage').':') !!} @show_tooltip(__('manufacturing::lang.wastage_tooltip'))
						<div class="input-group">
							{!! Form::text('waste_percent',!empty($recipe->waste_percent) ? @num_format($recipe->waste_percent) : 0, ['class' => 'form-control input_number', 'placeholder' => __('manufacturing::lang.wastage') ]); !!}
							<span class="input-group-addon">
								<i class="fa fa-percent"></i>
							</span>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						{!! Form::label('total_quantity', __('manufacturing::lang.total_output_quantity').':') !!}
						<div class="@if(!is_array($unit_html)) input-group @else input_inline @endif">
							{!! Form::text('total_quantity',!empty($recipe->total_quantity) ? @format_quantity($recipe->total_quantity) : 1, ['class' => 'form-control input_number', 'placeholder' => __('manufacturing::lang.total_output_quantity') ]); !!}
							<span class="@if(!is_array($unit_html)) input-group-addon @endif">
								@if(is_array($unit_html))
									<select name="sub_unit_id" class="form-control" id="sub_unit_id">
										@foreach($unit_html as $key => $value)
											<option 
												value="{{$key}}" 
												data-multiplier="{{$value['multiplier']}}"
												@if(!empty($recipe->sub_unit_id) && $recipe->sub_unit_id == $key)
													selected
												@endif
											>{{$value['name']}}</option>
										@endforeach
									</select>
								@else
									{{ $unit_html }}
								@endif
							</span>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						{!! Form::label('extra_cost', __('manufacturing::lang.production_cost').':') !!} @show_tooltip(__('manufacturing::lang.production_cost_tooltip'))
						<div class="input_inline">
							{!! Form::text('extra_cost',!empty($recipe->extra_cost) ? @num_format($recipe->extra_cost) : 0, ['class' => 'form-control input_number', 'placeholder' => __('manufacturing::lang.extra_cost') ]); !!}
							<span>
								{!! Form::select('production_cost_type',['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage'), 'per_unit' => __('manufacturing::lang.per_unit')], !empty($recipe->production_cost_type) ? $recipe->production_cost_type : 'fixed', ['class' => 'form-control', 'id' => 'production_cost_type']); !!}	
							</span>
						</div>
						<p><strong>
							{{__('manufacturing::lang.total_production_cost')}}:
						</strong>
						<span id="total_production_cost" class="display_currency" data-currency_symbol="true">{{$total_production_cost}}</span></p>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						{!! Form::label('total', __('sale.total').':') !!}
						<div class="input-group">
							@php
								$final_price = $ingredient_total;
								if(!empty($recipe->final_price)) {
									$final_price = $recipe->final_price;
								}

							@endphp
							{!! Form::text('total', @num_format($final_price), ['id' => 'total', 'class' => "form-control", 'readonly']); !!}
							<span class="input-group-addon">
								{{$currency_details->symbol}}
							</span>
						</div>
					</div>
				</div>
			</div>	
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						{!! Form::label('instructions', __('manufacturing::lang.recipe_instructions').':') !!}

						{!! Form::textarea('instructions',!empty($recipe) ? $recipe->instructions : null, ['class' => 'form-control', 'placeholder' => __('manufacturing::lang.recipe_instructions') ]); !!}
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-sm-12">
					<button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right">@lang('messages.save')</button>
				</div>
			</div>
		</div>
	</div>
{!! Form::close() !!}
</section>
@stop
@section('javascript')
	@include('manufacturing::layouts.partials.common_script')
	<script type="text/javascript">
		$('.ingredient-row-sortable').sortable({
			cursor: "move",
			handle: ".handle",
			update: function(event, ui) {
				$(this).children().each(function(index) {
					$(this).find('input.sort_order').val(++index)
				});
			}
		});
	</script>
	<script>

    $(document).ready(function () {
		
		validateTotalQuantity();
    // Update quantity when percent_quantity is changed
    $(document).on('change', '.percent_quantity', function () {
        let $row = $(this).closest('tr'); // Get the current row
        let percentValue = parseFloat($(this).val()) || 0; // Get percent_quantity value
        
        // If the entered percentage exceeds 100%, reset it to 0
        if (percentValue > 100) {
            alert('Percentage cannot exceed 100%. Resetting to 0.');
            $(this).val(0).change(); // Reset the percent_quantity input
            $row.find('.quantity').val(0).change(); // Reset the corresponding quantity
            return; // Exit the function
        }

        let calculatedQuantity = percentValue / 100; // Calculate the quantity
        $row.find('.quantity').val(calculatedQuantity.toFixed(4)); // Update the quantity field

        // Validate total quantity
        let validated = validateTotalQuantity();
        if (!validated) {
            $row.find('.quantity').val(0).change();
            $row.find('.percent_quantity').val(0).change();
        }
    });

    // Validate total quantity
    function validateTotalQuantity() {
        let totalQuantity = 0;
		debugger;
        // Sum all quantity fields
        $('.quantity').each(function () {
            let quantity = parseFloat($(this).val()) || 0;
            totalQuantity += quantity;
        });

        // Update total percentage display
        let totalText = (totalQuantity * 100).toFixed(4) + '%';
        $('#totalQuantity').html(totalText);

        // If the total exceeds 1 (100%), show an alert and return false
        if (totalQuantity > 1) {
            alert('The total quantity cannot exceed 100% (1).');
            return false;
        }
        return true;
    }
});

</script>
@endsection
