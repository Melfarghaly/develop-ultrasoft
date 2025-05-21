<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeCompany extends Model
{
    protected $fillable = ['name', 'business_id'];

    
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}

