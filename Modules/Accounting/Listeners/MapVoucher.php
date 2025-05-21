<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Utils\TransactionUtil;
use App\Contact;
use Modules\Accounting\Entities\AccountingAccount;
class MapVoucher
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
        //
        $voucher=$event->voucher;
        $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();

        if($voucher->voucher_type=='cash_disbursement'){
            $accountingUtil->saveMap('cash_disbursement', $voucher->id,auth()->user()->id, $voucher->business_id, $voucher->account_name,$voucher->cash_drawer);
        }
        if($voucher->voucher_type=='cash_receipt'){
            $accountingUtil->saveMap('cash_receipt', $voucher->id, auth()->user()->id, $voucher->business_id, $voucher->account_name,$voucher->cash_drawer);
        }
        $first_account=AccountingAccount::find($voucher->account_name);
        if(!empty($first_account->contact_id)){
            $contact=Contact::find($first_account->contact_id);
        }else{
            $second_account=AccountingAccount::find($voucher->cash_drawer);
            $contact=Contact::find($second_account->contact_id);
        }
        //dd($second_account,$first_account);
        //$account=AccountingAccounts::find($voucher->cash_drawer);
       if(!empty($contact)){
           //   dd($contact); 
            $transactionUtil=new TransactionUtil();
            $transactionUtil->payContactVocher('voucher',$voucher->id,$contact,$voucher->voucher_type,$voucher->amount);
       }
       
    }
}
