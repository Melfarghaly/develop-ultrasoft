<?php

namespace App\Http\Controllers;

use App\ChargeCompany;
use App\PaymentMethod;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index() 
    {
        $companies = ChargeCompany::where('business_id', auth()->user()->business_id)->get();
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        $paymentMethods = [];
        return view('companies.create', compact('paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|',
           
        ]);

       $business_id = auth()->user()->business_id;
       $company = ChargeCompany::create($request->only('name') + ['business_id' => $business_id]);

        return redirect()->route('companies.index')->with('success', 'تم إنشاء الشركة بنجاح');
    }

    public function edit(ChargeCompany $company)
    {
        $paymentMethods = [];
        return view('companies.edit', compact('company', 'paymentMethods'));
    }

    public function update(Request $request, ChargeCompany $company)
    {
        $request->validate([
            'name' => 'required',
            
        ]);

        $company->update([
            'name' => $request->name
        ]);

    

        return redirect()->route('companies.index')->with('success', 'تم تحديث الشركة بنجاح');
    }
    public function detachPaymentMethod(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);
    
        $company = ChargeCompany::findOrFail($validatedData['company_id']);
        $paymentMethod = PaymentMethod::findOrFail($validatedData['payment_method_id']);
    
        if ($company->paymentMethods()->where('payment_method_id', $paymentMethod->id)->exists()) {
            $company->paymentMethods()->detach($paymentMethod);
            return response()->json(['message' => 'تم إزالة طريقة الدفع من الشركة بنجاح.']);
        }
    
        return response()->json(['message' => 'طريقة الدفع غير مرتبطة بهذه الشركة.']);
    }
    public function destroy(ChargeCompany $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'تم حذف الشركة بنجاح');
    }
    public function getPaymentMethods(ChargeCompany $company)
    {
        return response()->json($company->paymentMethods);
    }
}