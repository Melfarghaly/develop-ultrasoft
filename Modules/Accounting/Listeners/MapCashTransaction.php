<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Utils\TransactionUtil;
use App\Contact;
use Modules\Accounting\Entities\AccountingAccount;
class MapCashTransaction
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
        /**
         *  "document_date" => "2025-02-16"
    "currency" => "الجنيه"
    "amount" => "60"
    "bank_name" => "2544"
    "notes" => null
    "transaction_type" => "withdrawal"
    "document_number" => "6730"
    "business_id" => 18
    "created_by" => 19
    "account_name" => "358"
    "updated_at" => "2025-02-16 09:11:37"
    "created_at" => "2025-02-16 09:11:37"
    "id" => 156
         */
        $cash=$event->cash;
        $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
        if($cash->transaction_type=='deposit'){
            $accountingUtil->saveMap('deposit', $cash->id, auth()->user()->id, $cash->business_id, $cash->bank_name,$cash->account_name);
        }
        if($cash->transaction_type=='withdrawal'){
            $accountingUtil->saveMap('withdrawal', $cash->id, auth()->user()->id, $cash->business_id, $cash->account_name,$cash->bank_name);
        }
        $first_account=AccountingAccount::find($cash->account_name);
        if(!empty($first_account->contact_id)){
            $contact=Contact::find($first_account->contact_id);
        }
        //dd($second_account,$first_account);
        //$account=AccountingAccounts::find($voucher->cash_drawer);
       if(!empty($contact)){
           //   dd($contact);
            $transactionUtil=new TransactionUtil();
            $transactionUtil->payContactVocher('cashTransaction',$cash->id,$contact,$cash->transaction_type,$cash->amount);
       }
       

    }
}
