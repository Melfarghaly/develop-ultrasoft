<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckTransaction extends Model
{
    use HasFactory;

    // Fillable attributes
    protected $fillable = ['check_id', 'bank', 'cashbox', 'account', 'amount', 'type'];

    // Date casting
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with Check model
    public function check()
    {
        return $this->belongsTo(Check::class);
    }
    public function bank()
    {
        return $this->belongsTo(AccountingAccount::class, 'bank_name');
    }
    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'account_name');
    }

}
