<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Accounting\Entities\AccountingAccount;
use App\Contact;
use App\Utils\TransactionUtil;
class MapChecks
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
        $check=$event->check;
        $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
        //dd($check);
        if($check->check_type=='issued'){
            $accountingUtil->saveMap('check_issued', $check->id, $check->created_by, $check->business_id, $check->account_id,$check->bank_id);
        }
        if($check->check_type=='received'){
            $accountingUtil->saveMap('check_received', $check->id, $check->created_by, $check->business_id, $check->account_id,$check->bank_id);
        }
        $first_account=AccountingAccount::find($check->account_id);
        if(!empty($first_account->contact_id)){
            $contact=Contact::find($first_account->contact_id);
        }else{
            $second_account=AccountingAccount::find($check->bank_id);
            $contact=Contact::find($second_account->contact_id);
        }
        //dd($second_account,$first_account);
        //$account=AccountingAccounts::find($voucher->cash_drawer);
       if(!empty($contact)){
          
            $transactionUtil=new TransactionUtil();
            $transactionUtil->payContactVocher('check',$check->id,$contact,$check->check_type,$check->check_value);
       }

    }
}
