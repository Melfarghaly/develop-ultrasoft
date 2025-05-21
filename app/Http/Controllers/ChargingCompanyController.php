<?php

namespace App\Http\Controllers;

use App\ChargeCompany;
use Illuminate\Http\Request;

class ChargingCompanyController extends Controller
{
    public function index()
    {
        return view('charging_company_index');
    }
    public function create()    
    {
        return view('charging_company_create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
          
        ]);
        $chargeCompany = new ChargeCompany();
        $chargeCompany->name = $request->name;
        $chargeCompany->business_id = request()->session()->get('business.id');
        $chargeCompany->save();
        $output = [
            'success' => true,
            'data' => $chargeCompany,
            'msg' => 'Charging Company created successfully'
        ];
        return response()->json($output);
    }
    public function edit($id)
    {
        return view('charging_company.edit');
    }
    public function update(Request $request, $id)
    {
        return view('charging_company.update');
    }
    public function destroy($id)
    {
        return view('charging_company.destroy');
    }
}