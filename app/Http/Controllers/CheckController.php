<?php

namespace App\Http\Controllers;

use App\Check;
use App\Account;
use App\CheckTransaction;
use App\Events\CheckAdded;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use DB;
class CheckController extends Controller
{
    protected $businessUtil; 

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
  
    }
    public function index(Request $request)
    {
        $checkType = $request->query('check_type', '');
        $issueDateFrom = $request->query('issue_date_from', '');
        $issueDateTo = $request->query('issue_date_to', '');
        $dueDateFrom = $request->query('due_date_from', '');
        $dueDateTo = $request->query('due_date_to', '');
        $account_name = $request->query('account_name', '');
    
        $query = Check::query();
    
        if ($checkType) {
            $query->where('check_type', $checkType);
        }
    
        if ($issueDateFrom) {
            $query->whereDate('issue_date', '>=', $issueDateFrom);
        }
    
        if ($issueDateTo) {
            $query->whereDate('issue_date', '<=', $issueDateTo);
        }
    
        if ($dueDateFrom) {
            $query->whereDate('due_date', '>=', $dueDateFrom);
        }
    
        if ($dueDateTo) {
            $query->whereDate('due_date', '<=', $dueDateTo);
        }
        if ($account_name) {
            $query->where('account_name', 'like', "%{$account_name}%");
        }
    
        // Paginate results
        $checks = $query->paginate(1000);
    
        return view('checks.Operations.index', [
            'checks' => $checks,
            'checkType' => $checkType,
            'issueDateFrom' => $issueDateFrom,
            'issueDateTo' => $issueDateTo,
            'dueDateFrom' => $dueDateFrom,
            'dueDateTo' => $dueDateTo,
            'account_name' => $account_name
        ]);
    }
    
    



    // public function createIssued()
    // {
    //     $business_id = session('business_id');

    //     $issuedCheckCount = Check::where('check_type', 'issued')->count();
    //     $banks = Account::forDropdown($business_id, false);
        
    //     return view('checks.Operations.create-issued', compact('issuedCheckCount', 'banks'));
    // }
    

    public function createIssued()
    {
        if (auth()->check()) {
            $business_id = auth()->user()->business_id;
            
            // dd($business_id);

            $issuedCheckCount = Check::where('check_type', 'issued')->count();
            
            $banks = Account::forDropdown($business_id, true);

            $currencies = $this->businessUtil->allCurrencies();
            
            return view('checks.Operations.create-issued', compact('issuedCheckCount', 'banks','currencies'));
        } else {
            abort(403, 'Unauthorized action.');
        }
    }


    public function storeIssued(Request $request)
    {
     
        try {
        /*    
            $request->validate([
                'check_number' => 'required|string|unique:checks,check_number',
                'account_name' => 'required|string',
                'issue_date' => 'required|date|after_or_equal:today',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'check_value' => 'required|numeric|min:0',
                'currency' => 'required|string',
                'notes' => 'nullable|string',
            ], [
                'issue_date.after_or_equal' => 'تاريخ التحرير يجب أن يكون اليوم أو بعده.',
                'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون اليوم أو بعده وتاريخ التحرير.',
                'check_value.min' => 'قيمة الشيك يجب أن تكون أكبر من أو تساوي 0.',
            ]);
            */
            DB::beginTransaction();
            $inputs=$request->except('_token');
            $inputs['check_type']='issued';
            $inputs['business_id']=auth()->user()->business_id;
            $inputs['created_by']=auth()->user()->id;


           $inputs['issue_date']=$this->businessUtil->uf_date($request->issue_date);
           $inputs['due_date']=$this->businessUtil->uf_date($request->due_date);
       //dd($inputs);
            $check=Check::create($inputs);
        
            $issuedCheckCount = Check::where('check_type', 'issued')->count();
         
           
            $output=[
                'success'=>true,
               'msg'=>'تم اضافة الشيك'
              ];
              DB::commit();
              event(new CheckAdded($check,'created'));
            return redirect()->route('checks.create.issued')
                ->with('status',$output)
                ->with('issuedCheckCount', $issuedCheckCount);
      
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage().$e->getFile().$e->getLine());
          $output=[
            'success'=>false,
            'msg'=>'حدث خطأ '.$e->getMessage()
          ];
         // dd($e->getMessage());
            return redirect()->back()
            ->with('status',$output)
                ->withInput();
        }
        
    }
    
    public function generateUniqueNumber($type)
    {
        $lastVoucher = Check::orderBy('voucher_number', 'desc')
        ->where('check_type', $type)
        ->where('business_id',\Auth()->user()->business_id)
        ->first();
       // dd( $lastVoucher);
       
        $nextNumber = $lastVoucher ? (int)$lastVoucher->voucher_number + 1 : 1;
        //dd( $nextNumber);
        while (Check::where('voucher_number', $nextNumber)->where('check_type', $type)->where('business_id',\Auth()->user()->business_id)->exists()) {
            $nextNumber++;
        }
        //dd($nextNumber);
        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT); // مثال: ملء الأصفار إلى 4 أرقام
    } 
    public function createReceived()
    {
        $receivedCheckCount = Check::where('check_type', 'received')->count();
     
            $business_id = auth()->user()->business_id;
            
            // dd($business_id);

            $issuedCheckCount = Check::where('check_type', 'issued')->count();
            
            $banks = Account::forDropdown($business_id, true);

            $currencies = $this->businessUtil->allCurrencies();
            
       
        return view('checks.Operations.create-received', compact('receivedCheckCount','banks','currencies'));
    }

    public function storeReceived(Request $request)
    {
        try {
           
                DB::beginTransaction();
                $inputs=$request->except('_token');
                $inputs['check_type']='received';
                $inputs['business_id']=auth()->user()->business_id;
                $inputs['created_by']=auth()->user()->id;
    
    
               $inputs['issue_date']=$this->businessUtil->uf_date($request->issue_date);
               $inputs['due_date']=$this->businessUtil->uf_date($request->due_date);
           
                $check=Check::create($inputs);
            
                $receivedCheckCount = Check::where('check_type', 'received')->count();
             
               
                $output=[
                    'success'=>true,
                   'msg'=>'تم اضافة الشيك'
                  ];
                  DB::commit();
                  event(new CheckAdded($check,'created'));
                return redirect()->route('checks.create.received')
                    ->with('status',$output)
                    ->with('receivedCheckCount', $receivedCheckCount);
          
            } catch (\Exception $e) {
                DB::rollBack();
              $output=[
                'success'=>false,
                'msg'=>'حدث خطأ '.$e->getMessage()
              ];
             // dd($e->getMessage());
                return redirect()->back()
                ->with('status',$output)
                    ->withInput();
            }
            
    }
    
    
    


    // عرض نموذج التعديل
    public function edit($id)
    {
        $business_id = auth()->user()->business_id;
        $check = Check::findOrFail($id);
        $banks = Account::forDropdown($business_id, true);

        $currencies = $this->businessUtil->allCurrencies();
        return view('checks.Operations.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Fetch the existing check record
            $check = Check::findOrFail($id);

            // Update check attributes
            $inputs = $request->except('_token', '_method');
          
            $inputs['business_id'] = $check->business_id; // Retain original business_id
            $inputs['updated_by'] = auth()->user()->id;

            // Format the dates
            $inputs['issue_date'] = $this->businessUtil->uf_date($request->issue_date);
            $inputs['due_date'] = $this->businessUtil->uf_date($request->due_date);

            // Update the check record
            $check->update($inputs);

            // Recalculate count if needed
           

            $output = [
                'success' => true,
                'msg' => 'تم تحديث الشيك بنجاح'
            ];

            DB::commit();
            event(new CheckAdded($check,'updated')); // Trigger an update event
            return redirect()->back()
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();

            $output = [
                'success' => false,
                'msg' => 'حدث خطأ ' . $e->getMessage().$e->getFile().$e->getLine()  
            ];

            return redirect()->back()
                ->with('status', $output)
                ->withInput();
        }
    }


    public function updateBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:checks,id',
            'bank' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $check = Check::find($request->id);

        if (!$check) {
            return response()->json(['error' => 'شيك غير موجود'], 404);
        }

        $check->bank = $request->bank;
        $check->save();

        return response()->json(['success' => 'تم تحديث اسم البنك بنجاح']);
    }


    public function destroy($id)
    {
        $check = Check::findOrFail($id);
       
        try{
            DB::beginTransaction();
            $trans=DB::table('accounting_accounts_transactions')->where('check_id',$check->id)->first();  
            if($trans){
                DB::table('accounting_acc_trans_mappings')->where('id',$trans->acc_trans_mapping_id)->delete();
                DB::table('accounting_accounts_transactions')->where('check_id',$check->id)->delete();  
            }
           $payment=DB::table('transaction_payments')->where('check_id',$check->id)->first();
           if($payment){
                $this->deletePayment($payment->id);

           }
            $check->delete();
            DB::commit();
           
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('checks.index')->with('erorr', 'حدث خطأ أثناء الحذف');
        }
        return redirect()->route('checks.index')->with('success', 'تم حذف الشيك بنجاح');
    }


    public function print($id)
    {
        $check = Check::findOrFail($id);
        return view('checks.Operations.print', compact('check'));
    }
}