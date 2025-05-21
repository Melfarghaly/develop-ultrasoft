<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\AccountingAccount;
class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'voucher_type',
        'voucher_date',
        'currency',
        'cash_drawer',
        'account_name',
        'amount',
        'notes',
        'business_id',
        'created_by',
        'cost_center_id',

    ];

    protected $casts = [
       // 'voucher_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the Voucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function drawer()
    {
        return $this->belongsTo(AccountingAccount::class, 'cash_drawer');
    }
    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'account_name');
    }
}
