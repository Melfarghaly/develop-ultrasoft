<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountingAccountsTransaction extends Model
{
    protected $guarded = [];
    protected $fillable = [
        'transaction_id',           // Nullable, ensure it's handled properly
        'map_type',                 // map_type like 'payment_account'
        'transaction_payment_id',   // Nullable, ensure it's handled properly
        'check_id',  
        'voucher_id',               // The check ID associated with the transaction
        'accounting_account_id',    // The related accounting account
        'amount',                   // The amount of the transaction
        'acc_trans_mapping_id',     // Transaction mapping ID
        'type',                     // Transaction type (e.g., debit/credit)
        'sub_type',                 // Sub-type (e.g., 'check_issued')
        'created_by',               // The user who created this record
        'operation_date',           // The date of the transaction, ensure it's in the right format
        'note',                     // Nullable, ensure it's handled properly
        'cash_transaction_id',      // Nullable, ensure it's handled properly
        'cost_center_id'            // Nullable, ensure it's handled properly
    ];
    public function account()
    {
        return $this->belongsTo('Modules\Accounting\Entities\AccountingAccount', 'accounting_account_id');
    }
    public function cost_center()
    {
        return $this->belongsTo('App\CostCenter', 'cost_center_id');
    }
    public function map()
    {
        return $this->belongsTo('Modules\Accounting\Entities\AccountingAccTransMapping','acc_trans_mapping_id');
    }

    /**
     * Creates new account transaction
     *
     * @return obj
     */
    public static function createTransaction($data)
    {
        $transaction = new AccountingAccountsTransaction();

        $transaction->amount = $data['amount'];
        $transaction->accounting_account_id = $data['accounting_account_id'];
        $transaction->transaction_id = ! empty($data['transaction_id']) ? $data['transaction_id'] : null;
        $transaction->type = $data['type'];
        $transaction->sub_type = ! empty($data['sub_type']) ? $data['sub_type'] : null;
        $transaction->map_type = ! empty($data['map_type']) ? $data['map_type'] : null;
        $transaction->operation_date = ! empty($data['operation_date']) ? $data['operation_date'] : \Carbon::now();
        $transaction->created_by = $data['created_by'];
        $transaction->note = ! empty($data['note']) ? $data['note'] : null;

        return $transaction->save();
    }

    /**
     * Creates/updates account transaction
     *
     * @return obj
     */
    public static function updateOrCreateMapTransaction($data)
    {
      
        $transaction = AccountingAccountsTransaction::updateOrCreate(
            ['transaction_id' => $data['transaction_id'],
                'map_type' => $data['map_type'],
                'transaction_payment_id' => $data['transaction_payment_id'],
                'check_id'=>$data['check_id'],
                'voucher_id'=>$data['voucher_id'],
                'cash_transaction_id'=>$data['cash_transaction_id']


            ],
            ['accounting_account_id' => $data['accounting_account_id'], 'amount' => $data['amount'],
            'acc_trans_mapping_id'=>$data['acc_trans_mapping_id'],
            'cost_center_id'=>$data['cost_center_id'] ?? null,
                'type' => $data['type'], 'sub_type' => $data['sub_type'], 'created_by' => $data['created_by'], 'operation_date' => $data['operation_date'],
            ]
        );
            // Log the transaction or throw an exception if it fails.
        if ($transaction) {
            \Log::info('Transaction created/updated successfully:', $transaction->toArray());
        } else {
            \Log::error('Failed to create/update the transaction.');
        }
     

    }
}
