<?php

namespace Modules\Accounting\Listeners;

use App\Transaction;
use App\BusinessLocation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Accounting\Entities\AccountingAccount;
use App\Voucher;
class MapPaymentTransaction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $payment = $event->transactionPayment;
        
        if(empty($payment->transaction_id)){
            return;
        }

        $transaction = Transaction::find($payment->transaction_id);

        if($transaction->type == 'purchase'){
            $type = 'purchase_payment';
            $voucher_type = 'cash_disbursement';
        } elseif($transaction->type == 'sell'){
            $type = 'sell_payment';
            $voucher_type = 'cash_receipt';
        } else {
            return;
        }

        //get location setting
        $business_location = BusinessLocation::find($transaction->location_id);
        $accounting_default_map = json_decode($business_location->accounting_default_map, true);

        //check if default map is set or not, if set the proceed.
        $deposit_to = isset($accounting_default_map[$type]['deposit_to']) ? $accounting_default_map[$type]['deposit_to'] : null;
        //check if user choose another deposit account
        if(!empty($payment->accounting_account_id)){
            $deposit_to = $payment->accounting_account_id;
        }
        
        $payment_account = isset($accounting_default_map[$type]['payment_account']) ? $accounting_default_map[$type]['payment_account'] : null;
        if(is_null($payment_account)){
            $payment_account=AccountingAccount::where('contact_id',$transaction->contact_id)->first()->id ?? null;
        }
        //if payment is deleted then delete the mapping
        if(isset($event->isDeleted) && $event->isDeleted){
            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
            $accountingUtil->deleteMap(null, $payment->id);
        } else {

            //Do the mapping
            if(!is_null($deposit_to) && !is_null($payment_account)){

                $payment_id = $payment->id;
                $user_id = request()->session()->get('user.id');
                $business_id = $transaction->business_id;
                
                $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
                $accountingUtil->saveMap($type, $payment_id, $user_id, $business_id, $deposit_to, $payment_account);
                //save voucher type
                $voucherId=$payment->voucher_id;
                $this->createOrUpdateVoucher($voucherId,$voucher_type,$payment);
               
                

            }
        }
    }
    public function createOrUpdateVoucher($voucherId,$voucher_type,$payment){
        $voucher = Voucher::find($voucherId);
        $accountContact=AccountingAccount::where('contact_id',$payment->transaction->contact_id)->first()->id ?? null;
        if($voucher){
            $voucher->voucher_type=$voucher_type;
            $voucher->amount=$payment->amount;
            $voucher->cash_drawer=$payment->accounting_account_id;
            $voucher->account_name=$accountContact;
            $voucher->voucher_date=$payment->paid_on;
            $voucher->notes='دفعة من فاتورة';
            $voucher->save();
        }else{
            $voucher = new Voucher();
            $voucher->voucher_type=$voucher_type;
            $voucher->business_id=$payment->transaction->business_id;
            $voucher->created_by=auth()->user()->id;
            $voucher->voucher_number=$this->generateUniqueVoucherNumber();
            $voucher->amount=$payment->amount;
            $voucher->cash_drawer=$payment->accounting_account_id;
            $voucher->account_name=$accountContact;
            $voucher->voucher_date=$payment->paid_on;
            $voucher->notes='دفعة من فاتورة';
            $voucher->save();
            //update payment with voucher id
            \DB::table('transaction_payments')->where('id', $payment->id)->update(['voucher_id' => $voucher->id]);
        }
    }
    public function generateUniqueVoucherNumber()
    {
        $lastVoucher = Voucher::orderBy('voucher_number', 'desc')->first();
        $nextNumber = $lastVoucher ? (int)$lastVoucher->voucher_number + 1 : 1;
        
        while (Voucher::where('voucher_number', $nextNumber)->where('business_id',session('business.id'))->exists()) {
            $nextNumber++;
        }
        
        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT); // مثال: ملء الأصفار إلى 4 أرقام
    }
}
