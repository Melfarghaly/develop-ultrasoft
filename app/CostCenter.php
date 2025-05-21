<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
class CostCenter extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(CostCenter::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CostCenter::class, 'parent_id');
    }
    public function transactions()
    {
        return $this->hasMany(AccountingAccountsTransaction::class, 'cost_center_id');
    }
    public function totalExpenses()
    {
        $total = $this->transactions->where('type','debit')->sum('amount'); // Sum expenses for current cost center

        foreach ($this->children as $child) {
            $total += $child->totalExpenses(); // Add child expenses recursively
        }

        return $total;
    }

    public function totalRevenues()
    {
        $total = $this->transactions->where('type','credit')->sum('amount'); // Sum revenues for current cost center

        foreach ($this->children as $child) {
            $total += $child->totalRevenues(); // Add child revenues recursively
        }

        return $total;
    }

    public function totalProfit()
    {
        return $this->totalRevenues() - $this->totalExpenses(); // Profit = Revenue - Expenses
    }
    public static function forDropdown($business_id, $show_none = false)
    {
        $query = CostCenter::where('business_id', $business_id);
        $query->where('is_last_record', 1);
        $dropdown = $query->pluck('name', 'id');
        if ($show_none) {
            $dropdown->prepend(__('messages.please_select'), '');
        }

        return $dropdown;
    }
}