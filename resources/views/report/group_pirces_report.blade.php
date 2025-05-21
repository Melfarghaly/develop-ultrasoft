@extends('layouts.app')
@section('title', __('تقرير قوائم الاسعار'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
   
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('تقرير قوائم الاسعار')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('price_group', __('قائمة الاسعار') . ':') !!}
                        {!! Form::select('price_group', $selling_price_groups, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'price_group_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::checkbox('have_price_group_only', 1, false, ['id' => 'have_price_group_only']); !!}
                        {!! Form::label('have_price_group_only', __('لديها قائمة اسعار فقط')); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
               @php
                $custom_labels = json_decode(session('business.custom_labels'), true);
                $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
                $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
                $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
                $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
                @endphp
                <table class="table table-bordered table-striped" id="price_group_table" style="width:100%">
                    <thead>
                        <tr>
                            <th>@lang('messages.action')</th>
                            <th>SKU</th>
                            <th>@lang('business.product')</th>
                            <th>@lang('lang_v1.variation')</th>
                            <th>@lang('product.category')</th>
                            
                            <th class="stock_sell_price">سعر البيع</th>
                          
                          
                            

                        </tr>
                    </thead>
                    
                </table>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
<script>
        //Stock report table
    price_group_table = $('#price_group_table').DataTable({
        processing: true,
        fixedHeader:false,
        order: [[1, 'asc']],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]], //NOT WORKING

        buttons: [
            {
                extend: 'excel',
                text: '{{ __('Excel') }}',
                className: 'btn btn-primary btn-sm pull-right',
            }
        ],

       
        dom: 'Blfrtip', 

        serverSide: true,
        scrollY: "75vh",
        scrollX:        true,
        scrollCollapse: true,
        ajax: {
            url: '/reports/stock-price-groups',
            data: function(d) {
                d.price_group_id= $('#price_group_id').val();
                d.have_price_group_only= $('#have_price_group_only').is(':checked') ? 1 : 0;
               
            },
        },
        columns: 
            [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                 { data: 'sub_sku', name: 'variations.sub_sku' },
                { data: 'name', name: 'products.name' },
                { data: 'variation', name: 'variation', orderable: false, searchable: true },
               
                { data: 'category_name', name: 'c.name', orderable: false, searchable: false },
                { data: 'sell_price', name: 'sell_price', orderable: false, searchable: false }
            ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#price_group_table'));
        },
       
        
    });
    $(document).on("change", "#price_group_id, #have_price_group_only", function() {
        price_group_table.ajax.reload();

    });
    //submit form via ajax
    $(document).on("click", "#selling_price_form_submit", function() {
        var data = $('#selling_price_form_modal').serialize();
        var url = $('#selling_price_form_modal').attr('action');
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function(data) {
                $('.view_modal').modal('hide');
                price_group_table.ajax.reload();
                if(data.success==true){
                    toastr.success(data.msg);
                }else{
                    toastr.error(data.msg);
                }
            },
            error: function() {
                $('.view_modal').modal('hide');
            }
        });
    });
    //when click on input for update price
    $(document).on("change", ".sell_price_input", function() {
        var data = $(this).val();
        var url = $(this).attr('data-url');
        var variation_id = $(this).attr('data-variation_id');
        var price_group_id = $(this).attr('data-price_group_id');
        $.ajax({
            type: "POST",
            url: url,
            data: {
                price_inc_tax: data,
                variation_id: variation_id,
                price_group_id: price_group_id
            },
            success: function(data) {
                toastr.success(data.msg);
            },
            error: function() {
                toastr.error(data.msg);
            }
        });
    });
    
    

        
</script>
@endsection