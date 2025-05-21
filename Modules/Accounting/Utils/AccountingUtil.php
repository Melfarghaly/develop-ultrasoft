<?php

namespace Modules\Accounting\Utils;

use DB;
use App\Business;
use App\Check;
use App\Voucher;
use App\CashTransaction;
use App\Utils\Util;
use App\Transaction;
use App\TransactionPayment;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class AccountingUtil extends Util
{
    public function balanceFormula($accounting_accounts_alias = 'accounting_accounts',
                                 $accounting_account_transaction_alias = 'AAT')
    {
        return "SUM( IF(
            ($accounting_accounts_alias.account_primary_type='asset' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type='expense' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type='income' AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='equity' AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='liability' AND $accounting_account_transaction_alias.type='credit'), 
            amount, -1*amount)) as balance";
    }

    public function getAccountingSettings($business_id)
    {
        $accounting_settings = Business::where('id', $business_id)
                                ->value('accounting_settings');

        $accounting_settings = ! empty($accounting_settings) ? json_decode($accounting_settings, true) : [];

        return $accounting_settings;
    }

    public function getAgeingReport($business_id, $type, $group_by, $location_id = null)
    {
        $today = \Carbon::now()->format('Y-m-d');
        $query = Transaction::where('transactions.business_id', $business_id);

        if ($type == 'sell') {
            $query->where('transactions.type', 'sell')
            ->where('transactions.status', 'final');
        } elseif ($type == 'purchase') {
            $query->where('transactions.type', 'purchase')
                ->where('transactions.status', 'received');
        }

        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $dues = $query->whereNotNull('transactions.pay_term_number')
                ->whereIn('transactions.payment_status', ['partial', 'due'])
                ->join('contacts as c', 'c.id', '=', 'transactions.contact_id')
                ->select(
                    DB::raw(
                        'DATEDIFF(
                            "'.$today.'", 
                            IF(
                                transactions.pay_term_type="days",
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY),
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH)
                            )
                        ) as diff'
                    ),
                    DB::raw('SUM(transactions.final_total - 
                        (SELECT COALESCE(SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)), 0) 
                        FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id) )  
                        as total_due'),

                    'c.name as contact_name',
                    'transactions.contact_id',
                    'transactions.invoice_no',
                    'transactions.ref_no',
                    'transactions.transaction_date',
                    DB::raw('IF(
                        transactions.pay_term_type="days",
                        DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY),
                        DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH)
                    ) as due_date')
                )
                ->groupBy('transactions.id')
                ->get();

        $report_details = [];
        if ($group_by == 'contact') {
            foreach ($dues as $due) {
                if (! isset($report_details[$due->contact_id])) {
                    $report_details[$due->contact_id] = [
                        'name' => $due->contact_name,
                        '<1' => 0,
                        '1_30' => 0,
                        '31_60' => 0,
                        '61_90' => 0,
                        '>90' => 0,
                        'total_due' => 0,
                    ];
                }

                if ($due->diff < 1) {
                    $report_details[$due->contact_id]['<1'] += $due->total_due;
                } elseif ($due->diff >= 1 && $due->diff <= 30) {
                    $report_details[$due->contact_id]['1_30'] += $due->total_due;
                } elseif ($due->diff >= 31 && $due->diff <= 60) {
                    $report_details[$due->contact_id]['31_60'] += $due->total_due;
                } elseif ($due->diff >= 61 && $due->diff <= 90) {
                    $report_details[$due->contact_id]['61_90'] += $due->total_due;
                } elseif ($due->diff > 90) {
                    $report_details[$due->contact_id]['>90'] += $due->total_due;
                }

                $report_details[$due->contact_id]['total_due'] += $due->total_due;
            }
        } elseif ($group_by == 'due_date') {
            $report_details = [
                'current' => [],
                '1_30' => [],
                '31_60' => [],
                '61_90' => [],
                '>90' => [],
            ];
            foreach ($dues as $due) {
                $temp_array = [
                    'transaction_date' => $this->format_date($due->transaction_date),
                    'due_date' => $this->format_date($due->due_date),
                    'ref_no' => $due->ref_no,
                    'invoice_no' => $due->invoice_no,
                    'contact_name' => $due->contact_name,
                    'due' => $due->total_due,
                ];
                if ($due->diff < 1) {
                    $report_details['current'][] = $temp_array;
                } elseif ($due->diff >= 1 && $due->diff <= 30) {
                    $report_details['1_30'][] = $temp_array;
                } elseif ($due->diff >= 31 && $due->diff <= 60) {
                    $report_details['31_60'][] = $temp_array;
                } elseif ($due->diff >= 61 && $due->diff <= 90) {
                    $report_details['61_90'][] = $temp_array;
                } elseif ($due->diff > 90) {
                    $report_details['>90'][] = $temp_array;
                }
            }
        }

        return $report_details;
    }

    /**
     * Function to delete a mapping 
     */
    public function deleteMap($transaction_id, $transaction_payment_id){
        $trans=AccountingAccountsTransaction::where('transaction_id', $transaction_id)
            ->whereIn('map_type', ['payment_account', 'deposit_to','tax_account','discount_account'])
            ->where('transaction_payment_id', $transaction_payment_id)
            ->first();
        if(!empty($trans)){
            \DB::table('accounting_acc_trans_mappings')->where('id', $trans->acc_trans_mapping_id)->delete();
            AccountingAccountsTransaction::where('transaction_id', $transaction_id)
            ->whereIn('map_type', ['payment_account', 'deposit_to','tax_account','discount_account'])
            ->where('transaction_payment_id', $transaction_payment_id)
            ->delete();
        }

    }

    /**
     * Function to save a mapping
     */
    public function saveMapOld($type, $id, $user_id, $business_id, $deposit_to, $payment_account){
        if ($type == 'sell') {
            $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstorFail();

            //$payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'credit',
                'sub_type' => $type,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            //Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'debit',
                'sub_type' => $type,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        } elseif (in_array($type, ['purchase_payment', 'sell_payment'])) {
            $transaction_payment = TransactionPayment::where('id', $id)->where('business_id', $business_id)
                            ->firstorFail();

            //$payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => null,
                'transaction_payment_id' => $id,
                'amount' => $transaction_payment->amount,
                'type' => 'credit',
                'sub_type' => $type,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            //Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => null,
                'transaction_payment_id' => $id,
                'amount' => $transaction_payment->amount,
                'type' => 'debit',
                'sub_type' => $type,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        } elseif ($type == 'purchase') {
            $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstorFail();

            //$payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'credit',
                'sub_type' => $type,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            //Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'debit',
                'sub_type' => $type,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        }

        AccountingAccountsTransaction::updateOrCreateMapTransaction($payment_data);
        AccountingAccountsTransaction::updateOrCreateMapTransaction($deposit_data);
    }
    /**
     * new save mapping 
     */
    public function saveMap($type, $id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account = null, $discount_account = null)
    {
        if ($type == 'sell') {
            $this->processSellTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account);
        }elseif($type=='sell_return'){
            $this->processSellReturnTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account);

        } elseif (in_array($type, ['sell_payment'])) {
            $this->processPaymentTransaction($type, $id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account);
        } elseif (in_array($type, ['purchase_payment'])) {
            $this->processPaymentTransaction($type, $id, $user_id, $business_id, $payment_account, $deposit_to, $tax_account, $discount_account);
        } elseif ($type == 'purchase') {
            $this->processPurchaseTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account);
        }elseif($type=='purchase_return'){
            $this->processPurchaseReturnTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account);

        } elseif ($type == 'expense') {
            $this->processExpenseTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account);
        }elseif($type=='check_issued'){
            $this->processCheckIssuedTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account, $tax_account);
        }elseif ($type == 'check_received'){
            $this->processCheckReceivedTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account, $tax_account);
        }elseif (in_array($type ,['cash_disbursement','cash_receipt'])){
            $this->processVoucherTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account, $tax_account);
        
        }elseif (in_array($type ,['withdrawal','deposit'] )){
            $this->processCachTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account, $tax_account);
        }
    }
    private function processCachTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account){

        $cach=CashTransaction::find($id);
        
        $payment_data = $this->createTransactionData($payment_account, null, null, $cach->amount, 'credit', $type, 'payment_account', $user_id,null,null,$cach->id);
        $deposit_data = $this->createTransactionData($deposit_to, null, null, $cach->amount, 'debit', $type, 'deposit_to', $user_id,null,null,$cach->id);

        $this->saveTransactions([$payment_data, $deposit_data], $cach->document_date);

    }
    private function processCheckIssuedTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account){

        $check=Check::find($id);
        $payment_data = $this->createTransactionData($payment_account, null, null, $check->check_value, 'credit', $type, 'payment_account', $user_id,$check->id);
        $deposit_data = $this->createTransactionData($deposit_to, null, null, $check->check_value, 'debit', $type, 'deposit_to', $user_id,$check->id);

        $this->saveTransactions([$payment_data, $deposit_data]);

    }
    private function processVoucherTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account){

        $voucher=Voucher::find($id);

        if($type=='cash_disbursement'){
            $payment_data = $this->createTransactionData($payment_account, null, null, $voucher->amount, 'credit', $type, 'payment_account', $user_id,null,$voucher->id);
            $deposit_data = $this->createTransactionData($deposit_to, null, null, $voucher->amount, 'debit', $type, 'deposit_to', $user_id,null,$voucher->id);
            
        }else{
            $payment_data = $this->createTransactionData($deposit_to, null, null, $voucher->amount, 'credit', $type, 'payment_account', $user_id,null,$voucher->id);
            $deposit_data = $this->createTransactionData($payment_account, null, null, $voucher->amount, 'debit', $type, 'deposit_to', $user_id,null,$voucher->id);
 
        }
      
        $this->saveTransactions([$payment_data, $deposit_data], $voucher->voucher_date);

    }
    private function processCheckReceivedTransaction($id,$type, $user_id, $business_id,$deposit_to, $payment_account){

        $check=Check::find($id);
        $payment_data = $this->createTransactionData($payment_account, null, null, $check->check_value, 'debit', $type, 'payment_account', $user_id,$check->id);
        $deposit_data = $this->createTransactionData($deposit_to, null, null, $check->check_value, 'credit', $type, 'deposit_to', $user_id,$check->id);

        $this->saveTransactions([$payment_data, $deposit_data]);

    }
    private function processSellTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account)
    {
        $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstOrFail();

        $deposit_data = $this->createTransactionData($deposit_to, $id, null, $transaction->final_total, 'debit', 'sell', 'deposit_to', $user_id);
        $tax_data = $this->createTransactionData($tax_account, $id, null, $transaction->tax_amount, 'credit', 'sell', 'tax_account', $user_id);
        $discount_amount=0;
        if ($transaction->discount_type == 'fixed') {
            $discount_amount = $transaction->discount_amount;
        } else { 
            $discount_amount = ($transaction->discount_amount / 100) * $transaction->total_before_tax;
        }
        $discount_data = $this->createTransactionData($discount_account, $id, null, $discount_amount, 'debit', 'sell', 'discount_account', $user_id);
        $payment_data = $this->createTransactionData($payment_account, $id, null, $transaction->final_total-$transaction->tax_amount+$discount_amount, 'credit', 'sell', 'payment_account', $user_id);

        $this->saveTransactions([$payment_data, $deposit_data, $tax_data, $discount_data], $transaction->transaction_date);
    }
    private function processSellReturnTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account)
    {
        $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstOrFail();

        $deposit_data = $this->createTransactionData($deposit_to, $id, null, $transaction->final_total, 'debit', 'sell_return', 'deposit_to', $user_id);
        $tax_data = $this->createTransactionData($tax_account, $id, null, $transaction->tax_amount, 'credit', 'sell_return', 'tax_account', $user_id);
        $discount_amount=0;
        if ($transaction->discount_type == 'fixed') {
            $discount_amount = $transaction->discount_amount;
        } else {
            $discount_amount = ($transaction->discount_amount / 100) * $transaction->total_before_tax;
        }
        $discount_data = $this->createTransactionData($discount_account, $id, null, $discount_amount, 'debit', 'sell_return', 'discount_account', $user_id);
        $payment_data = $this->createTransactionData($payment_account, $id, null, $transaction->final_total-$transaction->tax_amount+$discount_amount, 'credit', 'sell_return', 'payment_account', $user_id);
//c
        $this->saveTransactions([$payment_data, $deposit_data, $tax_data, $discount_data], $transaction->transaction_date);
    }

    private function processPaymentTransaction($type, $id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account)
    {
        $transaction_payment = TransactionPayment::where('id', $id)->where('business_id', $business_id)->firstOrFail();

        $payment_data = $this->createTransactionData($payment_account, null, $id, $transaction_payment->amount, 'credit', $type, 'payment_account', $user_id);
        $deposit_data = $this->createTransactionData($deposit_to, null, $id, $transaction_payment->amount, 'debit', $type, 'deposit_to', $user_id);
        
        // Assuming payments may also have tax and discount, otherwise remove these lines
       // $tax_data = $this->createTransactionData($tax_account, null, $id, $transaction_payment->tax, 'credit', $type, 'tax_account', $user_id);
        //$discount_data = $this->createTransactionData($discount_account, null, $id, $transaction_payment->discount, 'debit', $type, 'discount_account', $user_id);

        $this->saveTransactions([$payment_data, $deposit_data], $transaction_payment->paid_on);    
    }

    private function processPurchaseTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account)
    {
        $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstOrFail();
        $discount_amount=0;
        if ($transaction->discount_type == 'fixed') {
            $discount_amount = $transaction->discount_amount;
        } else {
            $discount_amount = ($transaction->discount_amount / 100) * $transaction->total_before_tax;
        }
        $deposit_data = $this->createTransactionData($deposit_to, $id, null, $transaction->final_total-$transaction->tax_amount+$discount_amount, 'debit', 'purchase', 'deposit_to', $user_id);
        $tax_data = $this->createTransactionData($tax_account, $id, null, $transaction->tax_amount, 'debit', 'purchase', 'tax_account', $user_id);

       
        $payment_data = $this->createTransactionData($payment_account, $id, null, $transaction->final_total, 'credit', 'purchase', 'payment_account', $user_id);
        $discount_data = $this->createTransactionData($discount_account, $id, null, $discount_amount, 'credit', 'purchase', 'discount_account', $user_id);

        $this->saveTransactions([$payment_data, $deposit_data, $tax_data, $discount_data], $transaction->transaction_date);
    }
    private function processPurchaseReturnTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account, $discount_account)
    {
        $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstOrFail();
        $discount_amount=0;
        if ($transaction->discount_type == 'fixed') {
            $discount_amount = $transaction->discount_amount;
        } else {
            $discount_amount = ($transaction->discount_amount / 100) * $transaction->total_before_tax;
        }
        $deposit_data = $this->createTransactionData($deposit_to, $id, null, $transaction->final_total-$transaction->tax_amount+$discount_amount, 'debit', 'purchase_return', 'deposit_to', $user_id);
        $tax_data = $this->createTransactionData($tax_account, $id, null, $transaction->tax_amount, 'credit', 'purchase_return', 'tax_account', $user_id);

       
        $payment_data = $this->createTransactionData($payment_account, $id, null, $transaction->final_total, 'credit', 'purchase_return', 'payment_account', $user_id);
        $discount_data = $this->createTransactionData($discount_account, $id, null, $discount_amount, 'credit', 'purchase_return', 'discount_account', $user_id);

        $this->saveTransactions([$payment_data, $deposit_data, $tax_data, $discount_data], $transaction->transaction_date);
    }

    private function processExpenseTransaction($id, $user_id, $business_id, $deposit_to, $payment_account, $tax_account)
    {
        $transaction = Expense::where('business_id', $business_id)->where('id', $id)->firstOrFail();

        $payment_data = $this->createTransactionData($payment_account, $id, null, $transaction->final_total-$transaction->tax_amount, 'credit', 'expense', 'payment_account', $user_id);
        $deposit_data = $this->createTransactionData($deposit_to, $id, null, $transaction->final_total, 'debit', 'expense', 'deposit_to', $user_id);
        $tax_data = $this->createTransactionData($tax_account, $id, null, $transaction->tax_amount, 'credit', 'expense', 'tax_account', $user_id);

        $this->saveTransactions([$payment_data, $deposit_data, $tax_data], $transaction->transaction_date);
    }

    private function createTransactionData($accounting_account_id, $transaction_id, $transaction_payment_id, $amount, $type, $sub_type, $map_type, $user_id,$check_id=null,$voucher_id=null,$cash_transaction_id=null)
    {
        return [
            'accounting_account_id' => $accounting_account_id,
            'transaction_id' => $transaction_id,
            'transaction_payment_id' => $transaction_payment_id,
            'amount' => $amount,
            'type' => $type,
            'sub_type' => $sub_type,
            'map_type' => $map_type,
            'created_by' => $user_id,
            'check_id'=>$check_id,
            'voucher_id'=>$voucher_id,
            'cash_transaction_id'=>$cash_transaction_id,
         
            'operation_date' => \Carbon::now(),
        ];
    }

    private function saveTransactions(array $transactions,$journal_date= null)
    {
      //  dd($transactions);
        //make jornal _entry 
        //acc_trans_mapping_id
       // dd($journal_date);
        if(empty($journal_date)){
         
            $journal_date=\Carbon::now();
        } else {
            $journal_date = \Carbon::parse($journal_date); // Ensure the time component is preserved
        }
        $note="";
        $cost_center_id = null;
        $business_id=auth()->user()->business_id;
        $accounting_settings = $this->getAccountingSettings($business_id);
        $acc_trans_mapping=null;
            if(!empty($transactions[0]['transaction_id'])){
                $transaction=Transaction::find($transactions[0]['transaction_id']);
                $cost_center_id=$transaction->cost_center_id ?? null;
                $note = $transaction->additional_notes ?? '';
                $acc_trans=AccountingAccountsTransaction::where('transaction_id',$transactions[0]['transaction_id'])->first();
                if(!empty($acc_trans->acc_trans_mapping_id)){
                    $acc_trans_mapping=AccountingAccTransMapping::find($acc_trans->acc_trans_mapping_id);
                }    
            }       
             if(!empty($transactions[0]['check_id'])){
                $id=$transactions[0]['check_id'];
                $check=Check::find($id);
                $note = $check->notes ?? '';
                $acc_trans=AccountingAccountsTransaction::where('check_id',$transactions[0]['check_id'])->first();
                if(!empty($acc_trans->acc_trans_mapping_id)){
                    $acc_trans_mapping=AccountingAccTransMapping::find($acc_trans->acc_trans_mapping_id);
                }
             }
             //cash Transaction
            if(!empty($transactions[0]['cash_transaction_id'])){
                $cash_transaction=CashTransaction::find($transactions[0]['cash_transaction_id']);
                $cost_center_id=$cash_transaction->cost_center_id ?? null;
                $note = $cash_transaction->notes ?? '';
                $acc_trans=AccountingAccountsTransaction::where('cash_transaction_id',$transactions[0]['cash_transaction_id'])->first();
                if(!empty($acc_trans->acc_trans_mapping_id)){
                    $acc_trans_mapping=AccountingAccTransMapping::find($acc_trans->acc_trans_mapping_id);
                }
            }
             if(!empty($transactions[0]['voucher_id'])){
                $voucher=Voucher::find($transactions[0]['voucher_id']);
                $cost_center_id=$voucher->cost_center_id ?? null;
                $journal_date = $voucher->voucher_date;
                $note = $voucher->notes ?? '';
                $acc_trans=AccountingAccountsTransaction::where('voucher_id',$transactions[0]['voucher_id'])->first();
                if(!empty($acc_trans->acc_trans_mapping_id)){
                    $acc_trans_mapping=AccountingAccTransMapping::find($acc_trans->acc_trans_mapping_id);
                }
             }
        
            if(!empty($transactions[0]['transaction_payment_id'])){
                $transaction_payment=TransactionPayment::find($transactions[0]['transaction_payment_id']);
                $note = $transaction_payment->note ?? '';
                $acc_trans=AccountingAccountsTransaction::where('transaction_payment_id',$transactions[0]['transaction_payment_id'])->first();
                if(!empty($acc_trans->acc_trans_mapping_id)){
                    $acc_trans_mapping=AccountingAccTransMapping::find($acc_trans->acc_trans_mapping_id);
                }
            }
        

            if(empty($acc_trans_mapping)){
                $ref_no=null;
                $ref_count = $this->setAndGetReferenceCount('journal_entry');
                if (empty($ref_no)) {
                    $prefix = ! empty($accounting_settings['journal_entry_prefix']) ?
                    $accounting_settings['journal_entry_prefix'] : '';
        
                    //Generate reference number
                    $ref_no = $this->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
                }
                $acc_trans_mapping = new AccountingAccTransMapping();
                $acc_trans_mapping->business_id = $business_id;
                $acc_trans_mapping->ref_no = $ref_no;
                // $acc_trans_mapping->note = $request->get('note');//delete
                $acc_trans_mapping->type = 'journal_entry';
                $acc_trans_mapping->note=$note;
                $acc_trans_mapping->created_by = auth()->user()->id;
                $acc_trans_mapping->operation_date = $journal_date;
                $acc_trans_mapping->save();
            }
           
        //   dd($acc_trans_mapping);
       
        foreach ($transactions as $transaction) {
            if ($transaction['accounting_account_id'] !== null && $transaction['amount']>0 ) {
                $transaction['acc_trans_mapping_id']=$acc_trans_mapping->id;
                $transaction['operation_date']=$journal_date;
                $transaction['cost_center_id']=$cost_center_id; 
                if(!empty($transaction['transaction_id'])){
                    $transaction['cost_center_id']= null;
                    if(($transaction['map_type']!='payment_account' && $transaction['sub_type']=='purchase')
                    || ($transaction['map_type']!='deposit_to' && $transaction['sub_type']=='sell')
                    || ($transaction['map_type']!='payment_account' && $transaction['sub_type']=='sell_return')
                    || ($transaction['map_type']!='deposit_to' && $transaction['sub_type']=='purchase_return')
                    ){
                        $transaction['cost_center_id']=$cost_center_id ?? null;
                    }
                }
                if(!empty($transaction['voucher_id'])){
                    $transaction['cost_center_id']=null;
                    if( ($transaction['map_type']=='payment_account' && $transaction['sub_type']=='cash_receipt')
                    || ($transaction['map_type']=='deposit_to' && $transaction['sub_type']=='cash_disbursement')
                    ){
                        $transaction['cost_center_id']=$cost_center_id ?? null;
                    }
                }
                AccountingAccountsTransaction::updateOrCreateMapTransaction($transaction);
            }
        }
      
    }


}
