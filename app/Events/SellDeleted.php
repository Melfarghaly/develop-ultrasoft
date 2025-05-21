<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SellDeleted
{
    use SerializesModels;

    public $transaction;

    public $isDeleted;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
        
        //used in accounting MapPaymentTransaction
        $this->isDeleted = true;
    }
}
