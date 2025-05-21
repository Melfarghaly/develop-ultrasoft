@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    @if($journal->type == 'opening_journal_entry')
        <h1>تعديل القيد الافتتاحي
        - {{$journal->ref_no}}
        </h1>
       
    @else
    <h1>@lang( 'accounting::lang.journal_entry' ) - {{$journal->ref_no}}</h1>
    @endif
</section>
<section class="content">

{!! Form::open(['url' => action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'update'], $journal->id), 
    'method' => 'PUT', 'id' => 'journal_add_form']) !!}

	@component('components.widget', ['class' => 'box-primary'])

        <div class="row">
            
            <div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('journal_date', __('accounting::lang.journal_date') . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
						{!! Form::text('journal_date', @format_datetime($journal->operation_date), ['class' => 'form-control datetimepicker', 'readonly', 'required']); !!}
					</div>
				</div>
			</div>
            <div class="col-sm-3">
                <a  class="btn btn-primary" traget="_blank" href="{{action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'show'], [$journal->id])}}">
                    <i class="fas fa-eye" aria-hidden="true"></i>عرض للطباعة
                </a>
            </div>
        </div>
       
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('note', __('lang_v1.additional_notes')) !!}
                    {!! Form::textarea('note', $journal->note, ['class' => 'form-control', 'rows' => 3]); !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">

            <table class="table table-bordered table-striped hide-footer" id="journal_table">
                <thead>
                    <tr>
                    <th class="col-md-1">#</th>
                        <th class="col-md-3">@lang( 'accounting::lang.account' )</th>
                        <th class="col-md-2">@lang( 'accounting::lang.cost_center' )</th>
                        <th class="col-md-2">@lang( 'accounting::lang.debit' )</th>
                        <th class="col-md-2">@lang( 'accounting::lang.credit' )</th>
                        <th class="col-md-5">ملاحظة</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @for($i = 1; $i <= count($accounts_transactions)+5; $i++)
                        <tr>

                            @php
                                $account_id = '';
                                $debit = '';
                                $credit = '';
                                $note = '';
                                $default_array = [];
                                $cost_center_array=[];
                                $current_cost_center='';





                            @endphp

                            @if(isset($accounts_transactions[$i-1]))
                                @php

                                    $account_id = $accounts_transactions[$i-1]['accounting_account_id'];
                                    $debit = ($accounts_transactions[$i-1]['type'] == 'debit') ? $accounts_transactions[$i-1]['amount'] : '';
                                    $credit = ($accounts_transactions[$i-1]['type'] == 'credit') ? $accounts_transactions[$i-1]['amount'] : '';
                                   $note = $accounts_transactions[$i-1]['note'];
                                    $default_array = [$account_id => $accounts_transactions[$i-1]['account']['name']];
                                    $current_cost_center=$accounts_transactions[$i-1]['cost_center_id'];
                                 
                                    if(!empty($current_cost_center)){
                                        $cost_center_array = [$current_cost_center => $accounts_transactions[$i-1]['cost_center']['name']];    

                                    }

                                @endphp

                                {!! Form::hidden('accounts_transactions_id[' . $i . ']', $accounts_transactions[$i-1]['id']); !!}
                            @endif
                        
                            <td>{{$i}}</td>
                            <td>
                                {!! Form::select('account_id[' . $i . ']', $default_array, $account_id, 
                                            ['class' => 'form-control accounts-dropdown account_id', 
                                            'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                            </td>
                            <td>
                                {!! Form::select('cost_center_id[' . $i . ']',  $cost_center_array, $current_cost_center, 
                                            ['class' => 'form-control cost-centers-dropdown cost_center_id', 
                                            'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                            </td>

                            <td>
                                {!! Form::text('debit[' . $i . ']', $debit, ['class' => 'form-control input_number debit']); !!}
                            </td>

                            <td>
                                {!! Form::text('credit[' . $i . ']', $credit, ['class' => 'form-control input_number credit']); !!}
                            </td>
                            <td>
                                {!! Form::text('notes[' . $i . ']', $note, ['class' => 'form-control ']); !!}
                            </td>
                        </tr>
                        @php
                        $current_cost_center='';
                        @endphp
                    @endfor
                </tbody>

                <tfoot>
                    <tr>
                        <th></th>
                        <th class="text-center">@lang( 'accounting::lang.total' )</th>
                        <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>
                        <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span></th>
                    </tr>
                </tfoot>
            </table>
            <button type="button" id="add_row" class="btn btn-success pull-left">اضافة صف</button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <button type="button" class="btn btn-primary pull-right btn-flat journal_add_btn">@lang('messages.save')</button>
            </div>
        </div>
        
    @endcomponent

    {!! Form::close() !!}
</section>

@stop

@section('javascript')
@include('accounting::accounting.common_js')
<script>
    let rowIndex = {{ count($accounts_transactions) + 6 }};

$('#add_row').click(function() {
    let newRow = `<tr>
        <td>${rowIndex }</td>
        <td>
            <select name="account_id[${rowIndex}]" class="form-control accounts-dropdown account_id" style="width: 100%;">
            </select>
        </td>
         <td>
            <select name="cost_center_id[${rowIndex}]" class="form-control cost-centers-dropdown cost_center_id" style="width: 100%;">
            </select>
        </td>
        <td><input type="text" name="debit[${rowIndex}]" class="form-control input_number debit"></td>
        <td><input type="text" name="credit[${rowIndex}]" class="form-control input_number credit"></td>
        <td><input type="text" name="note[${rowIndex}]" class="form-control"></td>
    </tr>`;
    $('#journal_table tbody').append(newRow);
    $("select.accounts-dropdown").select2({
        ajax: {
            url: '{{route("accounts-dropdown")}}',
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data
                }
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        templateResult: function(data) {
            return data.html;
        },
        templateSelection: function(data) {
            return data.text;
        }
    });
   

    rowIndex++;
});

</script>
<script type="text/javascript">
    $(document).ready(function(){
        calculate_total();
        
        $('.journal_add_btn').click(function(e){
            //e.preventDefault();
            calculate_total();
            
            var is_valid = true;

            if (parseFloat($('.total_credit_hidden').val()).toFixed(2) !== parseFloat($('.total_debit_hidden').val()).toFixed(2)) {
                is_valid = false;
                alert("@lang('accounting::lang.credit_debit_equal')");
            }


            //check if all account selected or not
            $('table > tbody  > tr').each(function(index, tr) { 
                var credit = __read_number($(tr).find('.credit'));
                var debit = __read_number($(tr).find('.debit'));

                if(credit != 0 || debit != 0){
                    if($(tr).find('.account_id').val() == ''){
                        is_valid = false;
                        alert("@lang('accounting::lang.select_all_accounts')");
                    }
                }
            });

            if(is_valid){
                $('form#journal_add_form').submit();
            }

            return is_valid;
        });

        
        $(document).on('change','.debit', function(){
            if($(this).val() > 0){
                $(this).parents('tr').find('.credit').val('');
            }
            calculate_total();
        });
        $(document).on('change','.credit', function(){
            if($(this).val() > 0){
                $(this).parents('tr').find('.debit').val('');
            }
            calculate_total();
        });
	});

    function calculate_total(){
        var total_credit = 0;
        var total_debit = 0;
        $('table > tbody  > tr').each(function(index, tr) { 
            var credit = __read_number($(tr).find('.credit'));
            total_credit += credit;

            var debit = __read_number($(tr).find('.debit'));
            total_debit += debit;
        });

        $('.total_credit_hidden').val(total_credit);
        $('.total_debit_hidden').val(total_debit);

        $('.total_credit').text(__currency_trans_from_en(total_credit));
        $('.total_debit').text(__currency_trans_from_en(total_debit));
    }

</script>
@endsection