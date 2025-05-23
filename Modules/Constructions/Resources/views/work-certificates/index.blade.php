@extends('constructions::layouts.master')
@section('title', __('constructions::lang.work_certificates'))

@section('main_content')
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('constructions::lang.work_certificates')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_location_id', __('purchase.business_location') . ':') !!}
                    {!! Form::select('purchase_list_filter_location_id', $business_locations, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_supplier_id', __('purchase.supplier') . ':') !!}
                    {!! Form::select('purchase_list_filter_supplier_id', $suppliers, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_project_id', __('constructions::lang.project') . ':') !!}
                    {!! Form::select('purchase_list_filter_project_id', $projects, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_status', __('constructions::lang.certificate_status') . ':') !!}
                    {!! Form::select('purchase_list_filter_status', $statuses, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_payment_status', __('purchase.payment_status') . ':') !!}
                    {!! Form::select(
                        'purchase_list_filter_payment_status',
                        $payment_statuses,
                        null,
                        ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('purchase_list_filter_date_range', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        'readonly',
                    ]) !!}
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary', 'title' => __('constructions::lang.all_work_certificates')])
            @can('constructions.create_work_certificate')
                @slot('tool')
                    <div class="box-tools">
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                            href="{{action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'create'])}}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endcan
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="work_certificates_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.action')</th>
                            <th>@lang('purchase.ref_no')</th>
                            <th>@lang('constructions::lang.contractor')</th>
                            <th>@lang('purchase.business_location')</th>
                            <th>@lang('purchase.purchase_date')</th>
                            <th>@lang('constructions::lang.certificate_status')</th>
                            <th>@lang('purchase.payment_status')</th>
                            <th>@lang('purchase.grand_total')</th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade update_status_modal" id="update_status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>

    <section id="receipt_section" class="print_section"></section>

    <!-- /.content -->
@stop
@section('javascript')
    <script>
        $(document).ready(function() {
            // Work certificates table
            var work_certificates_table = $('#work_certificates_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'index']) }}",
                    data: function(d) {
                        if ($('#purchase_list_filter_location_id').length) {
                            d.location_id = $('#purchase_list_filter_location_id').val();
                        }
                        if ($('#purchase_list_filter_supplier_id').length) {
                            d.contact_id = $('#purchase_list_filter_supplier_id').val();
                        }
                        if ($('#purchase_list_filter_project_id').length) {
                            d.project_id = $('#purchase_list_filter_project_id').val();
                        }
                        if ($('#purchase_list_filter_payment_status').length) {
                            d.payment_status = $('#purchase_list_filter_payment_status').val();
                        }
                        if ($('#purchase_list_filter_status').length) {
                            d.status = $('#purchase_list_filter_status').val();
                        }

                        var start = '';
                        var end = '';
                        if ($('#purchase_list_filter_date_range').val()) {
                            start = $('input#purchase_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            end = $('input#purchase_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                aaSorting: [[4, 'desc']],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },

                    { data: 'ref_no', name: 'ref_no' },
                   
                    { data: 'contact_name', name: 'contact_name' },
                    { data: 'location_name', name: 'business_locations.name' },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'status', name: 'status' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'final_total', name: 'final_total' },
                ],
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#work_certificates_table'));
                }
            });

            // Filters
            $(document).on('change', '#purchase_list_filter_location_id, #purchase_list_filter_supplier_id, #purchase_list_filter_project_id, #purchase_list_filter_payment_status, #purchase_list_filter_status',  
                function() {
                    work_certificates_table.ajax.reload();
                }
            );

            // Date range filter
            $('#purchase_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    work_certificates_table.ajax.reload();
                }
            );
            $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#purchase_list_filter_date_range').val('');
                work_certificates_table.ajax.reload();
            });

                        // Update status            $(document).on('click', '.update_status', function(e) {                e.preventDefault();                var certificate_id = $(this).data('certificate_id');                var status = $(this).data('status');                $.ajax({                    method: 'GET',                    url: "{{ url('constructions/work-certificates/update-status') }}",                    dataType: 'html',                    data: {                        certificate_id: certificate_id,                        status: status                    },                    success: function(data) {                        $('#update_status_modal').html(data).modal('show');                    }                });            });                        $(document).on('submit', '#update_certificate_status_form', function(e) {                e.preventDefault();                var form = $(this);                                $.ajax({                    method: 'POST',                    url: form.attr('action'),                    dataType: 'json',                    data: form.serialize(),                    beforeSend: function(xhr) {                        __disable_submit_button(form.find('button[type="submit"]'));                    },                    success: function(result) {                        if (result.success == true) {                            $('#update_status_modal').modal('hide');                            toastr.success(result.msg);                            work_certificates_table.ajax.reload();                        } else {                            toastr.error(result.msg);                        }                        form.find('button[type="submit"]').attr('disabled', false);                    }                });            });

            $(document).on('click', '.delete-work-certificate', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    work_certificates_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
