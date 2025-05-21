<?php
namespace Modules\Accounting\Listeners;

use App\Business;
use App\Utils\ContactUtil;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\ContactCreatedOrModified;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class MapContactAccount
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
     * @param  ContactCreatedOrModified  $event
     * @return void
     */
    public function handle(ContactCreatedOrModified $event)
    {
        $contact = (object)$event->contact;
        if(!empty($contact))
        // Check if the contact account already exists
        $existingAccount = AccountingAccount::where('contact_id', $contact->id)->first();

        if (!$existingAccount) {
        
            $business = Business::find($contact->business_id);

            if (!empty($business->supplier_parent_account) && $contact->type == 'supplier') {
                $this->createContactAccount($business->supplier_parent_account, $contact);
                $this->storeBalance($contact);
            }

            if (!empty($business->customer_parent_account) && $contact->type == 'customer') {
                $this->createContactAccount($business->customer_parent_account, $contact);
                $this->storeBalance($contact);
            }
        }
        else{
            $this->updateContactAccount($contact,$existingAccount);
        }
    }


    private function createContactAccount($parentAccountId, $contact)
    {
        $account = AccountingAccount::find($parentAccountId);

        if ($account) {
            $account->child_accounts()->create([
                'name' => $contact->name.' '.$contact->supplier_business_name,
                'business_id'=>$contact->business_id,
                'account_primary_type' => $account->account_primary_type,
                'account_sub_type_id' => $account->account_sub_type_id,
                'detailed_type_id' => $account->detailed_type_id,
                'parent_account_id' => $account->id,
                'contact_id' => $contact->id,
                'status' => 'active',
                'created_by' => $contact->created_by,
            ]);
        }
    }
    private function updateContactAccount($contact, $account)
    {
        $contactUtil=new ContactUtil();
        $contactInfo=$contactUtil->getContactInfo($contact->business_id,$contact->id);
        $account->update([
            'name' => $contact->name,
            'status' => $contact->status ?? $account->status,
        ]);
        $type = $contact->type=='customer' ? 'debit' : 'credit';
        $transaction = AccountingAccountsTransaction::updateOrCreate(
            [
                'accounting_account_id'=>$account->id,
                'sub_type'=>'opening_balance'
            ],
            ['amount' =>$contactInfo->opening_balance,
          
                'type' => $type, 'sub_type' => 'opening_balance', 'created_by' => auth()->user()->id, 'operation_date' => $contact->created_at
            ]
        );
    }
    private function storeBalance($contact){
        $contactUtil=new ContactUtil();
        $account=AccountingAccount::where('contact_id',$contact->id)->first();
        $contactInfo=$contactUtil->getContactInfo($contact->business_id,$contact->id);
        $data = [
            'amount' => $contactInfo->opening_balance,
            'accounting_account_id' => $account->id,
            'created_by' => auth()->user()->id,
            'operation_date' => $contact->created_at,
        ];

        //Opening balance
        $data['type'] = $contact->type=='customer' ? 'debit' : 'credit';
        $data['sub_type'] = 'opening_balance';
        AccountingAccountsTransaction::createTransaction($data);
    }
}
