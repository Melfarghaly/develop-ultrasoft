<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\AccountingAccount;

class CashTransaction extends Model
{
//use HasFactory;
    protected $guarded = ['id'];
    /*
    protected $fillable = [
        'transaction_type', 'document_number', 'document_date', 'currency', 
        'bank_name', 'account_name', 'amount', 'notes'
    ];
    */
    /**
     * Get the bank that owns the CashTransaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bank()
    {
        return $this->belongsTo(AccountingAccount::class, 'bank_name');
    }
    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'account_name');
    }
    public static function getNextDocumentNumber($type)
    {
        $latestTransaction = self::where('transaction_type', $type)
            ->orderBy('document_number', 'desc')
            ->first();

        $latestNumber = $latestTransaction ? intval(explode('-', $latestTransaction->document_number)[2]) : 0;
        $nextNumber = str_pad($latestNumber + 1, 4, '0', STR_PAD_LEFT);

        return $type . '-' . date('Ymd') . '-' . $nextNumber;
    }
}