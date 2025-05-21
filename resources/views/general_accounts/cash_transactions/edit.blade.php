@extends('layouts.app')
@section('title', 'تعديل المعاملة النقدية')

@section('content')
    <style>
        .invalid-feedback {
            color: red;
        }

        .is-invalid {
            border-color: red;
            padding-right: calc(1.5em + .75rem);
            background-position: right calc(.375em + .1875rem) center;
        }

        .btn-custom {
            border-radius: 25px;
        }
    </style>

    <!-- Main content -->
    <section class="content">


        <form id="edit_cash_transaction_form" method="POST" action="{{ route('cash_transactions.update', $cashTransaction->id) }}">
    @csrf
    @method('PUT')

    @component('components.widget', ['class' => 'box-solid'])
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="container mt-5">
                    <h1 class="mb-4 text-center">تعديل    </h1>

                    <div class="row mb-3">
                     

                        <div class="col-md-6 mb-2">
                            <label for="document_date" class="form-label">تاريخ السند</label>
                            <input type="text" id="document_date" name="document_date"
                                   class="form-control @error('document_date') is-invalid @enderror datepicker"
                                   value="{{ old('document_date', $cashTransaction->document_date ?? now()->format('Y-m-d')) }}">
                            @error('document_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            @includeIf('cost_center.costCenter', [ 'selected_cost_center' => $cashTransaction->cost_center_id])
                        </div>
                        <div class="col-md-6 mb-2 hide">
                            <label for="currency" class="form-label">العملة</label>
                            <input type="text" id="currency" name="currency"
                                   class="form-control @error('currency') is-invalid @enderror" placeholder="العملة"
                                   value="{{ old('currency', $cashTransaction->currency ?? 'الجنيه') }}">
                            @error('currency')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label for="amount" class="form-label">المبلغ</label>
                            <input type="number" step="0.01" id="amount" name="amount"
                                   class="form-control @error('amount') is-invalid @enderror" placeholder="المبلغ"
                                   value="{{ old('amount', $cashTransaction->amount) }}">
                            @error('amount')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        @php
                        $business = \App\Business::find(session('business.id'));
                        $banks = \DB::table('accounting_accounts')->where('business_id', $business->id)->where('detail_type_id', 30)->pluck('name', 'id');
                        @endphp
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">البنك</label>
                            <select id="bank_name" name="bank_name"
                                    class="form-control @error('bank_name') is-invalid @enderror">
                                @foreach($banks as $id => $name)
                                    <option value="{{ $id }}" {{ old('bank_name', $cashTransaction->bank_name) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('bank_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label for="account_id" class="form-label">اسم الحساب</label>
                            <select id="account_id" name="account_name"
                                    class="form-control accounts-dropdown @error('account_name') is-invalid @enderror">
                                <!-- Options will be dynamically loaded by Select2 -->
                                <option value="{{ old('account_name', $cashTransaction->account_name) }}">{{ old('account_name', $cashTransaction->account->name ?? 'Select an Account') }}</option>
                            </select>
                            @error('account_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 mb-2">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" placeholder="ملاحظات"
                                      rows="3">{{ old('notes', $cashTransaction->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="container text-center m-5">
                        <button type="submit" class="btn btn-primary btn-custom">تحديث</button>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
</form>

    </section>

@stop

@section('javascript')
    <script src="{{ asset('js/cash_deposit.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />    <script type="text/javascript">
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
            @if (session('success'))
                $("#response-message").html('<div class="alert alert-success">{{ session('success') }}</div>')
                    .show();
                setTimeout(function() {
                    $("#response-message").fadeOut();
                }, 3000);
            @endif
        });
    </script>
@endsection
