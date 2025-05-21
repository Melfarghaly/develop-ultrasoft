@extends('layouts.app')
@section('title', __('lang_v1.product_sell_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('lang_v1.product_sell_report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']), 'method' => 'get', 'id' => 'product_sell_report_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                    {!! Form::label('search_product', __('lang_v1.search_product') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <input type="hidden" value="" id="variation_id">
                            {!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'), 'autofocus']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('psr_customer_group_id', __( 'lang_v1.customer_group_name' ) . ':') !!}
                        {!! Form::select('psr_customer_group_id', $customer_group, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'psr_customer_group_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':') !!}
                        {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'psr_filter_category_id', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                        {!! Form::select('brand_id', $brands, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'psr_filter_brand_id', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('product_sr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'product_sr_date_filter', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    {!! Form::label('product_sr_start_time', __('lang_v1.time_range') . ':') !!}
                    @php
                        $startDay = Carbon::now()->startOfDay();
                        $endDay   = $startDay->copy()->endOfDay();
                    @endphp
                    <div class="form-group">
                        {!! Form::text('start_time', @format_time($startDay), ['style' => __('lang_v1.select_a_date_range'), 'class' => 'form-control width-50 f-left', 'id' => 'product_sr_start_time']); !!}
                        {!! Form::text('end_time', @format_time($endDay), ['class' => 'form-control width-50 f-left', 'id' => 'product_sr_end_time']); !!}
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active" id="psr_detailed_tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" 
                            id="product_sell_report_table-eta">
                                <thead>
                                    <tr>
                                        <th>InvoiceId</th>
                                        <th>Customer</th>
                                        <th>CustomerTaxRegistration</th>
                                        <th>Date</th>
                                        <th>Currency</th>
                                        <th>ExchangeRate</th>
                                        <th>ItemName</th>
                                        <th>ItemCode</th>
                                        <th>Qty</th>
                                        <th>SalesPrice</th>
                                        <th>DiscountPercent</th>
                                        <th>DiscountAmount</th>
                                        <th>PaymentMethod</th>
                                        <th>T1</th>
                                        <th>T1SubType</th>
                                        <th>InvoiceType</th>
                                        <th>ReferenceOldUUID</th>
                                    </tr>
                                </thead>
                                
                            </table>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(
        '#product_sell_report_form #location_id, #product_sell_report_form #customer_id, #psr_filter_brand_id, #psr_filter_category_id, #psr_customer_group_id'
    ).change(function() {
        $('.nav-tabs li.active').find('a[data-toggle="tab"]').trigger('shown.bs.tab');
    });
    if ($('table#product_sell_report_table-eta').length == 1) {
        $('#product_sr_date_filter').daterangepicker(
            dateRangeSettings, 
            function(start, end) {
                $('#product_sr_date_filter').val(
                    start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                );
                product_sell_report.ajax.reload();
                product_sell_grouped_report.ajax.reload();
                product_sell_report_with_purchase_table.ajax.reload();
                $('.nav-tabs li.active').find('a[data-toggle="tab"]').trigger('shown.bs.tab');
            }
        );
        $('#product_sr_date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $('#product_sr_date_filter').val('');
            product_sell_report.ajax.reload();
            product_sell_grouped_report.ajax.reload();
            product_sell_report_with_purchase_table.ajax.reload();
            $('.nav-tabs li.active').find('a[data-toggle="tab"]').trigger('shown.bs.tab');
        });

        $('#product_sr_start_time, #product_sr_end_time').datetimepicker({
            format: moment_time_format,
            ignoreReadonly: true,
        }).on('dp.change', function(ev){
            product_sell_report.ajax.reload();
            product_sell_report_with_purchase_table.ajax.reload();
            product_sell_grouped_report.ajax.reload();
            $('.nav-tabs li.active').find('a[data-toggle="tab"]').trigger('shown.bs.tab');
        });

        product_sell_report = $('table#product_sell_report_table-eta').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader:false,
            aaSorting: [[0, 'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: 'Blfrtip', // لإظهار أزرار التصدير
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            ajax: {
                url: '/reports/product-sell-report-eta',
                data: function(d) {
                    var start = '';
                    var end = '';
                    var start_time = $('#product_sr_start_time').val();
                    var end_time = $('#product_sr_end_time').val();

                    if ($('#product_sr_date_filter').val()) {
                        start = $('input#product_sr_date_filter')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');

                        start = moment(start + " " + start_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                        end = $('input#product_sr_date_filter')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                        end = moment(end + " " + end_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                    }
                    d.start_date = start;
                    d.end_date = end;

                    d.variation_id = $('#variation_id').val();
                    d.customer_id = $('select#customer_id').val();
                    d.location_id = $('select#location_id').val();
                    d.category_id = $('select#psr_filter_category_id').val();
                    d.brand_id = $('select#psr_filter_brand_id').val();
                    d.customer_group_id = $('#psr_customer_group_id').val();
                },
            },
            columns: [
                { data: 'invoice_no', name: 't.invoice_no' },
                { data: 'customer', name: 'c.name' },
                { data: 'tax_number', name: 'c.tax_number' , searchable: false ,orderable: false},
                { data: 'transaction_date', name: 't.transaction_date' },
                { data: 'currencey', name: 'currencey' , searchable: false ,orderable: false},
                { data: 'rate', name: 'rate', searchable: false ,orderable: false },
                { data: 'product_name', name: 'p.name'},
                { data: 'egscode', name: 'p.egscode' },
                { data: 'sell_qty', name: 'transaction_sell_lines.quantity' },
                { data: 'unit_sale_price', name: 'transaction_sell_lines.unit_price_inc_tax' },
                { data: 'discount_percent', name: 'discount_percent' , searchable: false ,orderable: false },
                { data: 'discount_amount', name: 'transaction_sell_lines.line_discount_amount' },
                { data: 'payment_methods', name: 'payment_methods', searchable: false ,orderable: false },
                { data: 'tax', name: 'tax_rates.name' , searchable: false ,orderable: false},
                { data: 'tax_sub_type', name: 'tax_sub_type' , searchable: false ,orderable: false},//init value
                { data: 'invoice_type', name: 'invoice_type', searchable: false ,orderable: false},
                { data: 'reference_oldUUID', name: 'reference_oldUUID', searchable: false ,orderable: false},
            ],
           
        });
    }
    </script>
@endsection