<style>
    .first-section{
        border-left: 1px solid black;
    }
</style>
<div class="modal-dialog no-print" role="document">
{!! Form::open(['url' => action('\Modules\Accounting\Http\Controllers\TransactionController@saveMap'), 'method' => 'POST', 'id' => 'save_accounting_map' ]) !!}
    
    <input type="hidden" name="type" value="{{$type}}" id="transaction_type">
    @if(in_array($type, ['sell', 'purchase','sell_return']))
        <input type="hidden" name="id" value="{{$transaction->id}}">
    @elseif(in_array($type, ['sell_payment', 'purchase_payment']))
        <input type="hidden" name="id" value="{{$transaction_payment->id}}">
    @endif

<div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle">
        @if($type == 'sell')
            {{$transaction->invoice_no}}
        @elseif(in_array($type, ['sell_payment', 'purchase_payment']))
            {{$transaction_payment->payment_ref_no}}
        @elseif($type == 'purchase')
            {{$transaction->ref_no}}
        @endif
    </h4>
</div>
<div class="modal-body"> 
    <div class="row">
        <div class="col-md-6 first-section">
            <div class="form-group">
                {!! Form::label('payment_account', __('accounting::lang.payment_account') . ':*' ) !!}
                {!! Form::select('payment_account', !is_null($default_payment_account) ? [$default_payment_account->id => $default_payment_account->name] : [], $default_payment_account->id ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('deposit_to', __('accounting::lang.deposit_to') . ':*' ) !!}
                {!! Form::select('deposit_to', !is_null($default_deposit_to) ? 
                    [$default_deposit_to->id => $default_deposit_to->name] : [], $default_deposit_to->id ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.deposit_to'), 'required' => 'required']); !!}
            </div>
        </div>
        @php
        $business=\App\Business::find(session('business.id'));
       // dd($business);
        $vat_account=\Modules\Accounting\Entities\AccountingAccount::find($business->vat_account_id);
       $vat_credit_account=\Modules\Accounting\Entities\AccountingAccount::find($business->vat_credit_account);
       $credit_discount_account=\Modules\Accounting\Entities\AccountingAccount::find($business->credit_account_id);
       $debit_discount_amount=\Modules\Accounting\Entities\AccountingAccount::find($business->debit_discount_id);

       $acc_arr=[];

       if(!empty($vat_account)){
            $acc_arr[$vat_account->id ]=$vat_account->name;
       }
       if(!empty($vat_credit_account)){
            $acc_arr[$vat_credit_account->id ]=$vat_credit_account->name;
       }
       if(!empty($credit_discount_account)){
            $acc_arr[$credit_discount_account->id ]=$credit_discount_account->name;
       }
       if(!empty($debit_discount_amount)){
            $acc_arr[$debit_discount_amount->id ]=$debit_discount_amount->name;
       }
        @endphp
         @if(in_array($type ,['purchase_return','sell']))
        <div class="col-md-6 first-section">
            <div class="form-group">
                {!! Form::label('vat_account', __('حساب الضريبة') . ':*' ) !!}
                {!! Form::select('vat_account', $acc_arr,$business->vat_account_id ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('discount_account', __('حساب الخصم') . ':*' ) !!}
                {!! Form::select('discount_account', $acc_arr,$business->credit_account_id ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}
            </div>
        </div>
        @elseif(in_array($type ,['purchase']))
        <div class="col-md-6 first-section">
            <div class="form-group">
                {!! Form::label('discount_account', __('حساب الخصم') . ':*' ) !!}
                {!! Form::select('discount_account', $acc_arr,$business->debit_account_id ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('vat_account', __('حساب الضريبة') . ':*' ) !!}
                {!! Form::select('vat_account', $acc_arr,$business->vat_credit_account ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}
            </div>
        </div>
        @elseif(in_array($type ,['sell_return']))
        <div class="col-md-6 first-section">
            <div class="form-group">
                {!! Form::label('discount_account', __('حساب الخصم') . ':*' ) !!}
                {!! Form::select('discount_account', $acc_arr,$business->credit_account_id ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('vat_account', __('حساب الضريبة') . ':*' ) !!}
                {!! Form::select('vat_account', $acc_arr,$business->vat_account_id ?? null, ['class' => 'form-control accounts-dropdown','placeholder' => __('accounting::lang.payment_account'), 'required' => 'required']); !!}
            </div>
        </div>
        @endif
        
    </div>

</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
</div>

{!! Form::close() !!}
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->