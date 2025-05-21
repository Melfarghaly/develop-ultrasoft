<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\Notifications\CustomerNotification;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use Excel;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Events\ContactCreatedOrModified;
use App\CostCenter;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class CostCenterController extends Controller
{

    protected $transactionUtil;
    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(
        TransactionUtil $transactionUtil,
    ) {
        $this->transactionUtil = $transactionUtil;
    }
    public function index() {
       
        $costCenters = CostCenter::where('business_id',\Auth::user()->business_id)->with('children')->where('is_last_record',0)->get();
       
        return view ('cost_center.index',get_defined_vars());
    }
    public function store(Request $request) {
        //$code=CostCenter::where('business_id',\Auth()->user()->id)
        //->where(function($q) use($request){
        //    $q>where('code',$request->code)
        //    ->orWhere('name',$request->name);
        //})
        //-first();
        //if($code){
        //    $output = ['success' => 0,
        //        'msg' => __('الكود او الاسم موجود بالفعل')
        //    ];
        //    return redirect()->back()->with('status', $output);
        //}
        $code = $request->code;
       
        $data = $request->only(['name','level','business_id','code','parent_id']);
        $data['is_last_record'] =!empty($request->is_last_record) ? 1 : 0;
        $data['business_id'] = \Auth::user()->business_id;

        //$data['code'] = $this->generateCode($request->parent_id);
        $costCenter = CostCenter::create($data);
        $output = ['success' => 1,
            'msg' => __('lang_v1.success')
        ];
       return redirect()->back()->with('status', $output);
    }
    public function generateCode($parent_id){
        $parent = CostCenter::find($parent_id);
       
        
        $last_child = CostCenter::where('parent_id',$parent_id)->orderBy('code','desc')->first();
        if($last_child){
            $last_code = $last_child->code;
            $last_code = substr($last_code,strlen($code));
            $last_code = (int)$last_code + 1;
            $last_code = str_pad($last_code, 2, '0', STR_PAD_LEFT);
            $code .= $last_code;
        }else{
            $code .= '01';
        }
        return $code;
        
        
    }
    public function dropdown(Request $request) {
            $costCenters = CostCenter::where('business_id',\Auth::user()->business_id)
            ->where('is_last_record',1)
            ->get();

            $accounts_array = [];
            foreach ($costCenters as $account) {
                $accounts_array[] = [
                    'id' => $account->id,
                    'text' => $account->name,
                    'html' => $account->name,
                ];
            }
            $accounts_array[] = [
                'id' => '',
                'text' => 'لا احد',
                       
                'html' => 'لا احد',
            ];
        return response()->json($accounts_array);
    }
    public function ledger(){
        $costCenters = CostCenter::where('business_id',\Auth::user()->business_id)->with(['transactions','children'])->where('is_last_record',0)->get();
        
        return view ('cost_center.ledger',get_defined_vars());
    }
    public function getLedgerData(Request $request){
        //transaction_date_range transaction_date_range=01-01-2025+-+31-12-2025
        //divde the date range
        $date_range = explode('+-+',$request->transaction_date_range);
        $start_date = $date_range[0];
        $end_date = $date_range[1];
    
       
        $cost_center_id = $request->cost_center_id;
        $cost_center = CostCenter::find($cost_center_id);
        $transactions = $cost_center->transactions()->where('transaction_date','>=',$start_date)
        ->where('transaction_date','<=',$end_date)
        ->get();
        $output = [];
        foreach ($transactions as $transaction) {
            $output[] = [
                'date' => $transaction->transaction_date,
                'type' => $transaction->type,
                'ref_no' => $transaction->ref_no,
                'debit' => $transaction->debit,
                'credit' => $transaction->credit,
                'balance' => $transaction->balance,
                'created_by' => $transaction->map->operation_date,
            ];
        }
        return response()->json($output);
    }
    public function show(Request $request){
        $date_range = explode(' - ',$request->transaction_date_range);
       
        $start_date = $date_range[0]  ?? null;
        $end_date = $date_range[1] ?? null;
        $cost_center_id = $request->cost_center_id;
        $costCenters = CostCenter::where('business_id',\Auth::user()->business_id)
                ->with('children')
                
                ->get();
       
        
        $cost_center=[];
        if(!empty($cost_center_id)){
           
         
            $cost_center = CostCenter::with('children','transactions')
            
            ->where('cost_centers.id',$cost_center_id)->get();
        }
       // dd($cost_center);
       if(!empty($start_date)){
        $start_date=$this->transactionUtil->uf_date($start_date);
            $end_date=$this->transactionUtil->uf_date($end_date);
        }
        return view ('cost_center.ledger',get_defined_vars());
    }
    public function update(Request $request, $id)
    {
        $code=CostCenter::where('business_id',\Auth()->user()->id)->where('code',$request->code)->first();
        if($code){
            $output = ['success' => 0,
                'msg' => __('lang_v1.code_already_exists')
            ];
            return redirect()->back()->with('status', $output);
        }
        $costCenter = CostCenter::findOrFail($id);
        $costCenter->update($request->all());
        $output = ['success' => 1,
            'msg' => __('lang_v1.success')
        ];
        return redirect()->back()->with('status', $output);
    }
    public function destroy($id)
    {
        $costCenter = CostCenter::findOrFail($id);
        $count= AccountingAccountsTransaction::where('cost_center_id',$id)->count();
        if($count){
            $output = ['success' => 0,
                'msg' => __('lang_v1.error_delete_account_transaction')
            ];
            return redirect()->back()->with('status', $output);
        }
        $costCenter->delete();
        $output = ['success' => 1,
            'msg' => __('lang_v1.success')
        ];
        return redirect()->back()->with('status', $output);
    }

}