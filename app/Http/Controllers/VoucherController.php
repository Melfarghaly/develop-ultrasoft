<?php

namespace App\Http\Controllers;

use App\Events\VoucherAdded;
use App\Voucher;
use App\TransactionPayment;
use App\Transaction;

use Illuminate\Http\Request;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use DB; 
class VoucherController extends Controller
{
    protected $businessUtil;
    protected $transactionUtil;

    public function __construct(BusinessUtil $businessUtil,TransactionUtil $transactionUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
  
    }
    public function index(Request $request)
    {
       
        $voucherType = $request->query('voucher_type', '');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
    
        $query = Voucher::where('business_id',auth()->user()->business_id);
    
        if ($voucherType) {
            $query->where('voucher_type', $voucherType);
        }
    
        if ($dateFrom) {
            $query->whereDate('voucher_date', '>=', $dateFrom);
        }
    
        if ($dateTo) {
            $query->whereDate('voucher_date', '<=', $dateTo);
        }
    
        //dd($request->cash_drawer);
        if(!empty($request->cash_drawer )){
            $query->whereIn('cash_drawer',$request->cash_drawer);
        }
    
        $vouchers = $query->get();
    
        // Count cash receipts and disbursements
        $cashReceiptCount = Voucher::where('voucher_type', 'cash_receipt')->count();
        $cashDisbursementCount = Voucher::where('voucher_type', 'cash_disbursement')->count();
    
     
        return view('general_accounts.vouchers.index',get_defined_vars());
    }
    
    

    public function generateUniqueVoucherNumber($type)
    {
        $lastVoucher = Voucher::orderBy('voucher_number', 'desc')
        ->where('voucher_type', $type)
        ->where('business_id',\Auth()->user()->business_id)
        ->first();
       // dd( $lastVoucher);
       
        $nextNumber = $lastVoucher ? (int)$lastVoucher->voucher_number + 1 : 1;
        //dd( $nextNumber);
        while (Voucher::where('voucher_number', $nextNumber)->where('voucher_type', $type)->where('business_id',\Auth()->user()->business_id)->exists()) {
            $nextNumber++;
        }
        //dd($nextNumber);
        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT); // مثال: ملء الأصفار إلى 4 أرقام
    }
    public function createCashReceipt()
    {
        $receivedCashCount = $this->generateUniqueVoucherNumber('cash_receipt');
        //dd($receivedCashCount);
        return view('general_accounts.vouchers.create_cash_receipt', compact('receivedCashCount'));
    }
    public function createCashDisbursement()
    {
        $disbursedCashCount = $this->generateUniqueVoucherNumber('cash_disbursement');
        //dd($disbursedCashCount);
        return view('general_accounts.vouchers.create_cash_disbursement', compact('disbursedCashCount'));
    }
    public function storeCashDisbursement(Request $request)
    {
        $request->validate([
            'voucher_date' => 'required',
            'currency' => 'required|string|max:10',
            'amount' => 'required|numeric',
            'cash_drawer' => 'required|string|max:50',
            'account_id' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);
    
        try {
            $voucherNumber =$this->generateUniqueVoucherNumber('cash_disbursement');
            DB::beginTransaction();
           $voucher= Voucher::create([
                'voucher_number' => $voucherNumber,
                'voucher_date' => $this->businessUtil->uf_date($request->voucher_date,true),
                'currency' => $request->currency,
                'amount' => $request->amount,
                'cash_drawer' => $request->cash_drawer,
                'account_name' => $request->account_id,
                'voucher_type' => 'cash_disbursement',
                'notes' => $request->notes,
                'business_id'=>auth()->user()->business_id,
                'created_by'=>auth()->user()->id,
                'cost_center_id'=>isset($request->cost_center_id) ? $request->cost_center_id : null,
            ]);
            if($voucher){
                event(new VoucherAdded($voucher,'created'));
            }
           DB::commit();
            return response()->json(['success' => true, 'message' => 'سند الصرف تم حفظه بنجاح.']);
        } catch (\Exception $e) {
            DB::rollBack();
           
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function storeCashReceipt(Request $request)
    {
       
        $request->validate([
            'voucher_date' => 'required',
            'currency' => 'required|string|max:10',
            'amount' => 'required|numeric',
            'cash_drawer' => 'required|string|max:50',
            'account_id' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);
    
        try {
            $voucherNumber  =$this->generateUniqueVoucherNumber('cash_receipt');
            DB::beginTransaction();
           $voucher= Voucher::create([
                'voucher_number' => $voucherNumber,
                'voucher_date' => $this->businessUtil->uf_date($request->voucher_date,true),
                'currency' => $request->currency,
                'amount' => $request->amount,
                'cash_drawer' => $request->cash_drawer,
                'account_name' => $request->account_id,
                'voucher_type' => 'cash_receipt',
                'notes' => $request->notes,
                'business_id'=>auth()->user()->business_id,
                'created_by'=>auth()->user()->id,
                'cost_center_id'=>isset($request->cost_center_id) ? $request->cost_center_id : null,
            ]);
            if($voucher){
                event(new VoucherAdded($voucher,'created'));
            }
           DB::commit();
            return response()->json(['success' => true, 'message' => 'سند الاستلام تم حفظه بنجاح.']);
        } catch (\Exception $e) {
            DB::rollBack();
           //dd($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage().$e->getFile().$e->getLine()]);
        }
    }

    public function edit(Voucher $voucher)
    {
        return view('general_accounts.vouchers.edit', compact('voucher'));
    }
    public function print(Voucher $voucher)
    {
        return view('general_accounts.vouchers.print', compact('voucher'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            // 'voucher_number' => 'required|string|max:255',
            'voucher_date' => 'required|date',
            'currency' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'cash_drawer' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
           
        ]);

        $voucher = Voucher::findOrFail($id);
        $voucher->update($request->all());
        if($voucher){
            event(new VoucherAdded($voucher,'updated'));
        }
       
        return response()->json(['success' => true, 'message' => 'تم تعديل السند بنجاح']);
    }

    public function destroy(Voucher $voucher)
    {
        try{
            DB::beginTransaction();
            $trans=DB::table('accounting_accounts_transactions')->where('voucher_id',$voucher->id)->first();  
            if($trans){
                DB::table('accounting_acc_trans_mappings')->where('id',$trans->acc_trans_mapping_id)->delete();
                DB::table('accounting_accounts_transactions')->where('voucher_id',$voucher->id)->delete();  
            }
           $payment=DB::table('transaction_payments')->where('voucher_id',$voucher->id)->first();
           if($payment){
                $this->deletePayment($payment->id);

           }
            $voucher->delete();
            DB::commit();
           
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        
        return redirect()->route('vouchers.index')->with('success', 'تم حذف السند بنجاح.');
    }
    function deletePayment($id){
       
            $payment = TransactionPayment::findOrFail($id);

           

            if (! empty($payment->transaction_id)) {
                TransactionPayment::deletePayment($payment);
            } else { //advance payment
                $adjusted_payments = TransactionPayment::where('parent_id',
                                            $payment->id)
                                            ->get();

                $total_adjusted_amount = $adjusted_payments->sum('amount');

                //Get customer advance share from payment and deduct from advance balance
                $total_customer_advance = $payment->amount - $total_adjusted_amount;
                if ($total_customer_advance > 0) {
                    $this->transactionUtil->updateContactBalance($payment->payment_for, $total_customer_advance, 'deduct');
                }

                //Delete all child payments
                foreach ($adjusted_payments as $adjusted_payment) {
                    //Make parent payment null as it will get deleted
                    $adjusted_payment->parent_id = null;
                    TransactionPayment::deletePayment($adjusted_payment);
                }

                //Delete advance payment
                TransactionPayment::deletePayment($payment);
            }

          

                
       
            return true;
    }
    public function report(Request $request)
    {
        // Retrieve filter parameters from the request
        $voucherType = $request->query('voucher_type', '');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $business_id =auth()->user()->business_id;
        // Build the query based on filters

       
        $query = Voucher::where('business_id',$business_id);
    
        if ($voucherType) {
            $query->where('voucher_type', $voucherType);
        }
    
        if ($dateFrom) {
            $query->whereDate('voucher_date', '>=', $dateFrom);
        }
    
        if ($dateTo) {
            $query->whereDate('voucher_date', '<=', $dateTo);
        }
        //dd($request->cash_drawer);
        if(!empty($request->cash_drawer )){
            $query->whereIn('cash_drawer',$request->cash_drawer);
        }
    
       
        $vouchers = $query->orderBy('id','desc')->get();
    //balance before from_date

        $balanceBefore = 0; // تعديل الرصيد السابق حسب الحاجة
        if ($dateFrom) {
            $balanceBeforeQ = Voucher::where('business_id',$business_id)
                ->where('voucher_date', '<', $dateFrom);
                if(!empty($request->cash_drawer )){
                    $balanceBeforeQ->whereIn('cash_drawer',$request->cash_drawer);
                }
                $balanceBeforeQ->select(
                    DB::raw("SUM(CASE WHEN voucher_type = 'cash_disbursement' THEN amount ELSE 0 END) as cash_disbursement"),
                    DB::raw("SUM(CASE WHEN voucher_type = 'cash_receipt' THEN amount ELSE 0 END) as cash_receipt")

                );
            $balnceBefor=$balanceBeforeQ->first();
           
            $balanceBefore = $balnceBefor->cash_receipt - $balnceBefor->cash_disbursement;
              
        }
        // Optionally, add additional data to be passed to the view
        $cashDisbursementCount = Voucher::where('voucher_type', 'cash_disbursement')->count();
        $cashReceiptCount = Voucher::where('voucher_type', 'cash_receipt')->count();
    
        $totalDisbursements = $query->where('voucher_type', 'cash_disbursement')->sum('amount');
        $totalReceipts = $query->where('voucher_type', 'cash_receipt')->sum('amount');
    
        $initialBalance = 0;  // تعديل الرصيد السابق حسب الحاجة
        $balanceStartPeriod = $initialBalance + $totalReceipts;
        $balanceEndPeriod = $balanceStartPeriod - $totalDisbursements;
        
        return view('general_accounts.vouchers.report', get_defined_vars());
    }
    
    public function syncVoucher(){
       
        $business_id=session('business.id');
        $vouchers =Voucher::where('business_id',$business_id)->get();
        foreach ($vouchers as $voucher){
            event(new VoucherAdded($voucher,'updated'));
        }
        return true;
    }

    public function printReport(Request $request)
{
    $voucherType = $request->input('voucher_type');
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    $business_id =auth()->user()->business_id;
 
    $query = Voucher::where('business_id',$business_id);

    if ($voucherType) {
        $query->where('voucher_type', $voucherType);
    }

    if ($dateFrom) {
        $query->whereDate('voucher_date', '>=', $dateFrom);
    }

    if ($dateTo) {
        $query->whereDate('voucher_date', '<=', $dateTo);
    }

    $vouchers = $query->get();

    // Add notes based on voucher type
    foreach ($vouchers as $voucher) {
        if ($voucher->voucher_type === 'cash_disbursement') {
            $voucher->notes = "تم إنشاء سند صرف بقيمة " . number_format($voucher->amount, 2) . " " . $voucher->currency;
        } elseif ($voucher->voucher_type === 'cash_receipt') {
            $voucher->notes = "تم إنشاء سند استلام بقيمة " . number_format($voucher->amount, 2) . " " . $voucher->currency;
        }
    }

    $totalDisbursements = $vouchers->where('voucher_type', 'cash_disbursement')->sum('amount');
    $totalReceipts = $vouchers->where('voucher_type', 'cash_receipt')->sum('amount');

    $initialBalance = 0;  // تعديل الرصيد السابق حسب الحاجة
    $balanceStartPeriod = $initialBalance + $totalReceipts;
    $balanceEndPeriod = $balanceStartPeriod - $totalDisbursements;

    return view('general_accounts.vouchers.print_report', compact(
        'vouchers',
        'totalDisbursements',
        'totalReceipts',
        'balanceStartPeriod',
        'balanceEndPeriod'
    ));
}

}
