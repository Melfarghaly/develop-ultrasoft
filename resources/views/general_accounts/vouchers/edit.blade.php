@extends('layouts.app')
@section('title', 'تعديل سند')

@section('content')
    <style>
        .invalid-feedback {
            color: red;
        }

        .alert-success {
            color: green;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #d4edda;
            border-radius: 5px;
            background-color: #d4edda;
        }

        .alert-danger {
            color: red;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #f8d7da;
            border-radius: 5px;
            background-color: #f8d7da;
        }
    </style>

    <section class="content no-print">
        <div id="response-message"></div>
       
        <form id="voucher_edit_form" method="POST" action="{{ route('vouchers.update', $voucher->id) }}">
    @csrf
    @method('PUT')
    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="container mt-5">
                    <h1 class="mb-4 text-center">تعديل سند استلام نقدي</h1>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">عدد سندات الاستلام</label>
                            <input type="text" class="form-control" value="" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="voucher_date" class="form-label">تاريخ السند</label>
                            <div class='input-group' id='datetimepic'>
                                <input type="text" id="voucher_date" name="voucher_date"
                                    class="form-control datepicker @error('voucher_date') is-invalid @enderror"
                                    value="{{ old('voucher_date', $voucher->voucher_date) }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                            @error('voucher_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            @includeIf('cost_center.costCenter', [ 'selected_cost_center' => $voucher->cost_center_id])
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="currency" class="form-label">العملة</label>
                            <input type="text" id="currency" name="currency"
                                class="form-control @error('currency') is-invalid @enderror"
                                placeholder="العملة"
                                value="{{ old('currency', $voucher->currency ?? 'الجنيه') }}">
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label for="amount" class="form-label">المبلغ</label>
                            <input type="number" step="0.01" id="amount" name="amount"
                                class="form-control @error('amount') is-invalid @enderror"
                                placeholder="المبلغ"
                                value="{{ old('amount', $voucher->amount) }}">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @php
                        $business = \App\Business::find(session('business.id'));
                        $banks =\DB::table('accounting_accounts')->where('business_id',$business->id)->where('parent_account_id',$business->parent_bank_account_id)->pluck('name','id');

                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cash_drawer" class="form-label">الخزنة الافتراضية</label>
                            <select id="cash_drawer" name="cash_drawer"
                                class="form-control accounts- @error('cash_drawer') is-invalid @enderror">
                                @foreach($banks as $id => $name)
                                    <option value="{{ $id }}" {{ $voucher->cash_drawer == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('cash_drawer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label for="account_id" class="form-label">اسم الحساب</label>
                            <select id="account_id" name="account_id"
                                class="form-control accounts-dropdown @error('account_id') is-invalid @enderror">
                                <option selected value="{{$voucher->account->id}}">{{$voucher->account->name}}</option>
                                <!-- Options will be dynamically loaded by Select2 -->
                            </select>
                            @error('account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 mb-2">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea id="notes" name="notes"
                                class="form-control @error('notes') is-invalid @enderror"
                                placeholder="ملاحظات"
                                rows="3">{{ old('notes', $voucher->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="container text-center m-5">
                        <button type="submit" class="btn btn-primary btn-custom m-5">حفظ</button>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
</form>

    </section>
@stop

@section('javascript')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

    <script type="text/javascript">
        $(document).ready(function() {
            function removeHtmlTags(str) {
                return str.replace(/<\/?[^>]+>/gi, '');
            }

            // Initialize Select2
            $("select.accounts-dropdown").select2({
                ajax: {
                    url: '/accounting/accounts-dropdown',
                    dataType: 'json',
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            })
                        };
                    }
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    return data.text;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            }).on('select2:select', function(e) {
                var selectedAccountName = removeHtmlTags(e.params.data.text);
                $('#account_name').val(selectedAccountName);
            });

            $('#voucher_edit_form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var actionUrl = form.attr('action');
                var formData = form.serialize();

                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#response-message').html('<div class="alert-success">' + (
                                response.message || 'تم التعديل بنجاح') + '</div>').show();
                            setTimeout(function() {
                                $('#response-message').fadeOut();
                            }, 3000);
                        } else {
                            $('#response-message').html('<div class="alert-danger">' + (response
                                .message || 'حدث خطأ أثناء التعديل') + '</div>').show();
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorHtml = '<div class="alert-danger"><ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul></div>';
                        $('#response-message').html(errorHtml).show();
                    }
                });
            });

            @if (session('success'))
                $("#response-message").html('<div class="alert-success">{{ session('success') }}</div>').show();
                setTimeout(function() {
                    $("#response-message").fadeOut();
                }, 3000);
            @endif
        });
    </script>
@endsection
