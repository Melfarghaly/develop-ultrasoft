<?php

namespace Modules\Accounting\Listeners;

use App\BusinessLocation;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\PurchaseCreatedOrModified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Accounting\Entities\AccountingAccount;

class MapPurchaseTransaction
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
    public function handle(PurchaseCreatedOrModified $event)
    {
       
        //get location setting and check if default is set or not, if set the proceed.
        $business_location = BusinessLocation::find($event->transaction->location_id);
        $accounting_default_map = json_decode($business_location->accounting_default_map, true);

        $deposit_to = isset($accounting_default_map['purchases']['deposit_to']) ? $accounting_default_map['purchases']['deposit_to'] : null;
        $payment_account = isset($accounting_default_map['purchases']['payment_account']) ? $accounting_default_map['purchases']['payment_account'] : null;
        
        $tax_account = isset($accounting_default_map['purchases']['tax_account']) ? $accounting_default_map['purchases']['tax_account'] : null;
        $discount_account = isset($accounting_default_map['purchases']['discount_account']) ? $accounting_default_map['purchases']['discount_account'] : null;
        if(is_null($payment_account)){
            $payment_account=AccountingAccount::where('contact_id',$event->transaction->contact_id)->first()->id ?? null;
        }
       
        //if purchase is deleted then delete the mapping
        if(isset($event->isDeleted) && $event->isDeleted){
            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
            $accountingUtil->deleteMap($event->transaction->id, null);
        } else {

            if(!is_null($deposit_to) && !is_null($payment_account)){

                $type = 'purchase';
                $id = $event->transaction->id;
                $user_id = request()->session()->get('user.id');
                $business_id = $event->transaction->business_id;
                
                $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
                $accountingUtil->saveMap($type, $id, $user_id, $business_id, $deposit_to, $payment_account,$tax_account,$discount_account);
            }
        }
    }
}
