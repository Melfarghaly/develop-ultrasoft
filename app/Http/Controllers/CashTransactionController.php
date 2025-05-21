<?php

namespace App\Http\Controllers;

use App\CashTransaction;
use App\Events\CachTransactionAdded;
use Illuminate\Http\Request;
use App\Utils\BusinessUtil;
use DB;
class CashTransactionController extends Controller
{
    protected $businessUtil;

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
  
    }
    public function index(Request $request)
    {
        $transactionType = $request->input('transaction_type');
        $documentDateFrom = $request->input('document_date_from');
        $documentDateTo = $request->input('document_date_to');

        $query = CashTransaction::where('business_id',\Auth()->user()->business_id);

        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }

        if ($documentDateFrom) {
            $query->whereDate('document_date', '>=', $documentDateFrom);
        }

        if ($documentDateTo) {
            $query->whereDate('document_date', '<=', $documentDateTo);
        }

        $transactions = $query->get();

        $cashDepositCount = CashTransaction::where('transaction_type', 'deposit')->count();
        $cashWithdrawalCount = CashTransaction::where('transaction_type', 'withdrawal')->count();

        return view('general_accounts.cash_transactions.index', compact('transactions', 'cashDepositCount', 'cashWithdrawalCount'));
    }


    private function generateDocumentNumber($type)
    {
        // Generate a unique document number
        $lastTransaction = CashTransaction::where('transaction_type', $type)
            ->where('business_id',\Auth()->user()->business_id)
            ->orderBy('document_number', 'desc')
            ->first();

        $nextNumber = $lastTransaction ? (int)$lastTransaction->voucher_number + 1 : 1;
        $nextNumber = $nextNumber + 1;
            //dd($nextNumber);
            return str_pad($nextNumber, 4, '0', STR_PAD_LEFT); // مثال: ملء الأصفار إلى 4 أرقام
       
        return  $nextNumber;
    }

    public function createDeposit()
    {
        $documentNumber = $this->generateDocumentNumber('deposit');

        $cashDepositCount = null;

        $cashWithdrawalCount = null;

        return view('general_accounts.cash_transactions.create_cash_deposit', compact('documentNumber', 'cashDepositCount', 'cashWithdrawalCount'));
    }

    public function createWithdrawal()
    {
        $cashDepositCount = null;

        $cashWithdrawalCount = null;
        $documentNumber = $this->generateDocumentNumber('withdrawal');
        return view('general_accounts.cash_transactions.create_cash_withdrawal', compact('documentNumber', 'cashWithdrawalCount'));
    }

    public function storeDeposit(Request $request)
    {
        $validated = $request->validate([
            'document_date' => 'required',
            'currency' => 'required|string',
            'amount' => 'required|numeric',
            'bank_name' => 'required|string',
            'account_id' => 'required|string',
            'notes' => 'nullable|string',
        ]);
            $validated['document_date']=$this->businessUtil->uf_date($request->document_date,true);
        try {
            $documentNumber = $this->generateDocumentNumber('deposit');
            $deposit = CashTransaction::create(array_merge($validated, [
                'transaction_type' => 'deposit',
                'document_number' => $documentNumber,
                'business_id'=>auth()->user()->business_id,
                'created_by'=>auth()->user()->id,
                'account_name'=>$request->account_id
            ]));
            event( new CachTransactionAdded($deposit,'created'));
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإيداع النقدي بنجاح.',
                'documentNumber' => $documentNumber,
                'depositCount' => CashTransaction::where('transaction_type', 'deposit')->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإيداع: ' . $e->getMessage()
            ]);
        }
    }

    public function storeWithdrawal(Request $request)
    {
        $validated = $request->validate([
            'document_date' => 'required',
            'currency' => 'required|string',
            'amount' => 'required|numeric',
            'bank_name' => 'required|string',
            'account_id' => 'required|string',
            'notes' => 'nullable|string',
        ]);
            $validated['document_date']=$this->businessUtil->uf_date($request->document_date,true);
        try {
            $documentNumber = $this->generateDocumentNumber('withdrawal');
            $withdrawal = CashTransaction::create(array_merge($validated, [
                'transaction_type' => 'withdrawal',
                'document_number' => $documentNumber,
                'business_id'=>auth()->user()->business_id,
                'created_by'=>auth()->user()->id,
                'account_name'=>$request->account_id
            ]));
            event( new CachTransactionAdded($withdrawal,'created'));
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ السحب النقدي بنجاح.',
                'documentNumber' => $documentNumber,
                'withdrawalCount' => CashTransaction::where('transaction_type', 'withdrawal')->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ السحب: ' . $e->getMessage()
            ]);
        }
    }

    public function show(CashTransaction $cashTransaction)
    {
        return view('general_accounts.cash_transactions.show', compact('cashTransaction'));
    }

    public function edit(CashTransaction $cashTransaction, $actionType = null)
    {
        $cashDepositCount = CashTransaction::where('transaction_type', 'deposit')->count();
        $cashWithdrawalCount = CashTransaction::where('transaction_type', 'withdrawal')->count();
        
        // Determine the transaction type based on the actionType or the cashTransaction itself
        $transactionType = $actionType ? $actionType : $cashTransaction->transaction_type;
    
        return view('general_accounts.cash_transactions.edit', compact('cashTransaction', 'transactionType', 'cashDepositCount', 'cashWithdrawalCount'));
    }
    
    public function print(CashTransaction $cashTransaction, $actionType = null)
    {
        $cashDepositCount = CashTransaction::where('transaction_type', 'deposit')->count();
        $cashWithdrawalCount = CashTransaction::where('transaction_type', 'withdrawal')->count();
        
        // Determine the transaction type based on the actionType or the cashTransaction itself
        $transactionType = $actionType ? $actionType : $cashTransaction->transaction_type;
    
        return view('general_accounts.cash_transactions.print', compact('cashTransaction', 'transactionType', 'cashDepositCount', 'cashWithdrawalCount'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            // 'document_number' => 'required|unique:cash_transactions,document_number,' . $cashTransaction->id,
            'document_date' => 'required|date',
            'currency' => 'nullable|string',
            'amount' => 'required|numeric',
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $cashTransaction = cashTransaction::findOrFail($id);
        $cashTransaction->update($request->all());
        event( new CachTransactionAdded($cashTransaction,'created'));
        return redirect()->route('cash_transactions.index')->with('success', 'تم تعديل المعاملة بنجاح.');
    }


    public function destroy(CashTransaction $cashTransaction)
    {
        try{
            DB::beginTransaction();
            $trans=DB::table('accounting_accounts_transactions')->where('cash_transaction_id',$cashTransaction->id)->first();  
            if($trans){
                DB::table('accounting_acc_trans_mappings')->where('id',$trans->acc_trans_mapping_id)->delete();
                DB::table('accounting_accounts_transactions')->where('cash_transaction_id',$cashTransaction->id)->delete();  
            }
           //$payment=DB::table('transaction_payments')->where('cash_transaction_id',$cashTransaction->id)->first();
           //if($payment){
           //     $this->deletePayment($payment->id);
//
           //}
            $cashTransaction->delete();
            DB::commit();
           
        }catch(\Exception $e){
            DB::rollBack();
            $output = [
                'success' => false,
                'msg' => 'حدث خطأ أثناء حذف المعاملة: ' . $e->getMessage()
            ];
            return redirect()->route('cash_withdrawals.index')->with('status', $output);
        }
        return redirect()->route('cash_withdrawals.index')->with('success', 'تم حذف المعاملة بنجاح.');
    }


    public function report(Request $request)
    {
        // Retrieve filter parameters from the request
        $transactionType = $request->input('transaction_type');
        $documentDateFrom = $request->input('document_date_from');
        $documentDateTo = $request->input('document_date_to');
        $bankName = $request->input('bank_name');

        $query = CashTransaction::where('business_id',\Auth()->user()->business_id);

        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }

        if ($documentDateFrom) {
            $query->whereDate('document_date', '>=', $documentDateFrom);
        }

        if ($documentDateTo) {
            $query->whereDate('document_date', '<=', $documentDateTo);
        }

        if ($bankName) {
            $query->whereIn('bank_name', $bankName);
        }

        $transactions = $query->get();

        // Add notes based on transaction type
        /*
        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'deposit') {
                $transaction->notes = "تم إنشاء إيداع في البنك بقيمة " . number_format($transaction->amount, 2) . " " . $transaction->currency;
            } elseif ($transaction->transaction_type === 'withdrawal') {
                $transaction->notes = "تم إنشاء سحب من البنك بقيمة " . number_format($transaction->amount, 2) . " " . $transaction->currency;
            }
        }
        */
        $balanceBefore = 0; // تعديل الرصيد السابق حسب الحاجة
        if ($documentDateFrom) {
            $balanceBeforeQ = CashTransaction::where('business_id',\Auth()->user()->business_id)
                ->where('document_date', '<', $documentDateFrom);
                if ($bankName) {
                    $balanceBeforeQ->whereIn('bank_name', $bankName);
                }
                $balanceBeforeQ->select(
                    \DB::raw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as deposit"),
                    \DB::raw("SUM(CASE WHEN transaction_type = 'withdrawal' THEN amount ELSE 0 END) as withdrawal")

                );
            $balnceBefor=$balanceBeforeQ->first();
           
            $balanceBefore = $balnceBefor->deposit - $balnceBefor->withdrawal;
              
        }
        // Optionally, add additional data to be passed to the view
        $cashDepositCount = CashTransaction::where('transaction_type', 'deposit')->count();
        $cashWithdrawalCount = CashTransaction::where('transaction_type', 'withdrawal')->count();
        $bankNames = CashTransaction::distinct()->pluck('bank_name');

        return view('general_accounts.cash_transactions.report', compact('transactions', 'balanceBefore','cashDepositCount', 'cashWithdrawalCount', 'bankNames'));
    }
    public function printReport(Request $request)
    {
        $transactionType = $request->input('transaction_type');
        $documentDateFrom = $request->input('document_date_from');
        $documentDateTo = $request->input('document_date_to');
        $bankName = $request->input('bank_name');

        $query = CashTransaction::where('business_id',\Auth()->user()->business_id);

        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }

        if ($documentDateFrom) {
            $query->whereDate('document_date', '>=', $documentDateFrom);
        }

        if ($documentDateTo) {
            $query->whereDate('document_date', '<=', $documentDateTo);
        }

        if ($bankName) {
            $query->where('bank_name', $bankName);
        }

        $transactions = $query->get();

        // Add notes based on transaction type
        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'deposit') {
                $transaction->notes = "تم إنشاء إيداع في البنك بقيمة " . number_format($transaction->amount, 2) . " " . $transaction->currency;
            } elseif ($transaction->transaction_type === 'withdrawal') {
                $transaction->notes = "تم إنشاء سحب من البنك بقيمة " . number_format($transaction->amount, 2) . " " . $transaction->currency;
            }
        }

        $totalDeposits = $transactions->where('transaction_type', 'deposit')->sum('amount');
        $totalWithdrawals = $transactions->where('transaction_type', 'withdrawal')->sum('amount');

        $initialBalance = 0;  // تعديل الرصيد السابق حسب الحاجة
        $balanceStartPeriod = $initialBalance + $totalDeposits;
        $balanceEndPeriod = $balanceStartPeriod - $totalWithdrawals;

        return view('general_accounts.cash_transactions.print_report', compact(
            'transactions',
            'totalDeposits',
            'totalWithdrawals',
            'balanceStartPeriod',
            'balanceEndPeriod'
        ));
    }
}
