<?php

namespace Modules\Accounting\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Utils\AccountingUtil;
use Yajra\DataTables\Facades\DataTables;

class JournalEntryController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $util;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $util, ModuleUtil $moduleUtil, AccountingUtil $accountingUtil)
    {
        $this->util = $util;
        $this->moduleUtil = $moduleUtil;
        $this->accountingUtil = $accountingUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $journal = AccountingAccTransMapping::where('accounting_acc_trans_mappings.business_id', $business_id)
                        ->join('users as u', 'accounting_acc_trans_mappings.created_by', 'u.id')
                        ->where('type', 'journal_entry')
                        ->select(['accounting_acc_trans_mappings.id', 'ref_no', 'operation_date', 'note',
                            DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                        ]);

            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $journal->whereDate('accounting_acc_trans_mappings.operation_date', '>=', $start)
                            ->whereDate('accounting_acc_trans_mappings.operation_date', '<=', $end);
            }

            return Datatables::of($journal)
                ->addColumn(
                    'action', function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">'.
                                    __('messages.actions').
                                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                        if (auth()->user()->can('accounting.view_journal')) {
                             $html .= '<li>
                                     <a  traget="_blank" href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'show'], [$row->id]).'">
                                         <i class="fas fa-eye" aria-hidden="true"></i>'.__("messages.view").'
                                     </a>
                                     </li>';
                        }

                        if (auth()->user()->can('accounting.edit_journal')) {
                            $html .= '<li>
                                    <a href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'edit'], [$row->id]).'">
                                        <i class="fas fa-edit"></i>'.__('messages.edit').'
                                    </a>
                                </li>';
                        }

                        if (auth()->user()->can('accounting.delete_journal')) {
                            $html .= '<li>
                                    <a href="#" data-href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'destroy'], [$row->id]).'" class="delete_journal_button">
                                        <i class="fas fa-trash" aria-hidden="true"></i>'.__('messages.delete').'
                                    </a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    })
                    ->editColumn('ref_no',function($row){
                        $html=$row->ref_no;
                        $trans=\DB::table('accounting_accounts_transactions')->where('acc_trans_mapping_id',$row->id)->first();
                        if(!empty($trans->transaction_id)){
                           $t= DB::table('transactions')->where('id',$trans->transaction_id)->first();
                            if($t->type=='sell'){
                                $html.='(<a href="#" data-href="/sells/'.$trans->transaction_id.'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i></a>)';
                            }elseif($t->type=='purchase'){
                                $html.='(<a href="#" data-href="/purchases/'.$trans->transaction_id.'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i></a>)';

                            }
                        }
                        return $html;
                    })
                    ->addColumn('debit',function($row){
                        $transes=\DB::table('accounting_accounts_transactions')->join('accounting_accounts as aa','aa.id','accounting_accounts_transactions.accounting_account_id')->where('acc_trans_mapping_id',$row->id)->where('type','debit')
                        ->select(
                            'accounting_accounts_transactions.amount',
                            'aa.name',
                            'aa.gl_code'
                        )->get();
                        $html="<table class='table'> ";
                        
                        foreach($transes as $trans){
                            $style="";
                            if(request()->debit_val==$trans->amount){
                                $style="background-color:yellow";
                            }
                            $html.="<tr><td style='".$style."'>  ";
                            $html.=$this->accountingUtil->num_f($trans->amount,true);; 
                            $html.="</td><td> ";
                            $html.=$trans->name." - ".$trans->gl_code ?? '' ."</td></tr>"; 
                        }
                        $html.="</table> ";
                         return $html;
                    })
                    ->addColumn('credit',function($row){
                        $transes=\DB::table('accounting_accounts_transactions')->join('accounting_accounts as aa','aa.id','accounting_accounts_transactions.accounting_account_id')->where('acc_trans_mapping_id',$row->id)->where('type','credit')
                        ->select(
                            'accounting_accounts_transactions.amount',
                            'aa.name',
                            'aa.gl_code'
                        )->get();
                         $html="<table class='table'> ";
                        foreach($transes as $trans){
                            $style="";
                            if(request()->credit_val==$trans->amount){
                                $style="background-color:yellow";
                            }
                            $html.="<tr><td style='".$style."'>  ";
                            $html.=$this->accountingUtil->num_f($trans->amount,true);; 
                            $html.="</td><td> ";
                            $html.=$trans->name." - ".$trans->gl_code ?? '' ."</td></tr>"; 
                        }
                        $html.="</table> ";
                        return $html;
                    })
                ->rawColumns(['action','debit','credit','ref_no'])
                ->make(true);
        }

        return view('accounting::journal_entry.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        return view('accounting::journal_entry.create');
    }
    public function createOpening()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_journal'))) {
            abort(403, 'Unauthorized action.');
        }
        $journal=AccountingAccTransMapping::where('business_id',$business_id)->where('type','opening_journal_entry')->first();
        if(!empty($journal)){
            return redirect()->action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'edit'], [$journal->id]);
        }
        return view('accounting::journal_entry.opening_jornal_entry');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $account_ids = $request->get('account_id');
            $credits = $request->get('credit');
            $debits = $request->get('debit');
            $note = $request->get('notes');
            $cost_center_id = $request->get('cost_center_id');
            $journal_date = $request->get('journal_date');

            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);
            $type = 'journal_entry';
            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('journal_entry');
            if(!empty($request->get('type'))){
                $ref_no = 'OB-'.'001';
                $type='opening_journal_entry';
            }
            elseif (empty($ref_no)) {
                $prefix = ! empty($accounting_settings['journal_entry_prefix']) ?
                $accounting_settings['journal_entry_prefix'] : '';

                //Generate reference number
                $ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
            }
            if(!$this->util->fy_check($business_id, $this->util->uf_date($journal_date, true))){
                $output = ['success' => 0,
                'msg' => __('lang_v1.fy_year_closed'),
                ];
                return redirect()->back()->with('status', $output);
            }
           
            $acc_trans_mapping = new AccountingAccTransMapping();
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->type = $type;
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = $this->util->uf_date($journal_date, true);
            $acc_trans_mapping->save();

            //save details in account trnsactions table
            foreach ($account_ids as $index => $account_id) {
                if (! empty($account_id)) {
                    $transaction_row = [];
                    $transaction_row['accounting_account_id'] = $account_id;

                    if (! empty($credits[$index])) {
                        $transaction_row['amount'] = $credits[$index];
                        $transaction_row['type'] = 'credit';
                    }

                    if (! empty($debits[$index])) {
                        $transaction_row['amount'] = $debits[$index];
                        $transaction_row['type'] = 'debit';
                    }
                    if (! empty($note[$index])) {
                        $transaction_row['note'] = $note[$index];
                    
                    }
                    if (! empty($cost_center_id[$index])) {
                        $transaction_row['cost_center_id'] = $cost_center_id[$index];
                    
                    }

                    $transaction_row['created_by'] = $user_id;
                    $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                    $transaction_row['sub_type'] = 'journal_entry';
                    $transaction_row['acc_trans_mapping_id'] = $acc_trans_mapping->id;
                  

                    $accounts_transactions = new AccountingAccountsTransaction();
                    $accounts_transactions->fill($transaction_row);
                    $accounts_transactions->save();
                }
            }

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_journal'))) {
            abort(403, 'Unauthorized action.');
        }
        $journal=AccountingAccTransMapping::where('business_id',$business_id)
            ->whereIn('type',['journal_entry','opening_journal_entry'])
            ->where('id',$id)->first();
      

        return view('accounting::journal_entry.show',get_defined_vars());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        $journal = AccountingAccTransMapping::where('business_id', $business_id)
                    ->whereIn('type', ['journal_entry', 'opening_journal_entry'])
                    ->where('id', $id)
                    ->firstOrFail();
        $accounts_transactions = AccountingAccountsTransaction::with(['account','cost_center'])
                                    ->where('acc_trans_mapping_id', $id)
                                    ->get()->toArray();

        return view('accounting::journal_entry.edit')
            ->with(compact('journal', 'accounts_transactions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            $journal_date = $request->get('journal_date');
            $user_id = request()->session()->get('user.id');
            if(!$this->util->fy_check($business_id,$this->util->uf_date($journal_date, true))){
                $output = ['success' => 0,
                'msg' => __('lang_v1.fy_year_closed'),
                ];
                return redirect()->back()->with('status', $output);
            }
            $account_ids = $request->get('account_id');
            $accounts_transactions_id = $request->get('accounts_transactions_id');
            $credits = $request->get('credit');
            $debits = $request->get('debit');
            $notes=$request->get('notes');
            $cost_center_id=$request->get('cost_center_id');

            $acc_trans_mapping = AccountingAccTransMapping::where('business_id', $business_id)
                        ->whereIn('type', ['journal_entry', 'opening_journal_entry'])
                        ->where('id', $id)
                        ->firstOrFail();
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->operation_date = $this->util->uf_date($journal_date, true);
            $acc_trans_mapping->update();

            //save details in account trnsactions table
            foreach ($account_ids as $index => $account_id) {
                if (! empty($account_id)) {
                    $transaction_row = [];
                    // تحقق إن فيه إما credit أو debit
                    $hasCredit = !empty($credits[$index]);
                    $hasDebit = !empty($debits[$index]);

                    if (! $hasCredit && ! $hasDebit) {
                        if(!empty($accounts_transactions_id[$index])) {
                            AccountingAccountsTransaction::where('id',$accounts_transactions_id[$index])->delete();
                        }
                        continue; // تخطى السطر لو مفيش قيمة
                    }
                    $transaction_row['accounting_account_id'] = $account_id;

                    if (! empty($credits[$index])) {
                        $transaction_row['amount'] = $credits[$index];
                        $transaction_row['type'] = 'credit';
                    }

                    if (! empty($debits[$index])) {
                        $transaction_row['amount'] = $debits[$index];
                        $transaction_row['type'] = 'debit';
                    }

                    if (! empty($notes[$index])) {
                        $transaction_row['note'] = $notes[$index];
                    
                    }

                    if (! empty($cost_center_id[$index])) {
                        $transaction_row['cost_center_id'] = $cost_center_id[$index];
                    
                    }

                    $transaction_row['created_by'] = $user_id;
                    $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                    $transaction_row['sub_type'] = 'journal_entry';
                    $transaction_row['acc_trans_mapping_id'] = $acc_trans_mapping->id;

                    if (! empty($accounts_transactions_id[$index])) {
                        $accounts_transactions = AccountingAccountsTransaction::find($accounts_transactions_id[$index]);
                        $accounts_transactions->fill($transaction_row);
                        $accounts_transactions->update();
                    } else {
                        $accounts_transactions = new AccountingAccountsTransaction();
                        $accounts_transactions->fill($transaction_row);
                        $accounts_transactions->save();
                    }
                } elseif (! empty($accounts_transactions_id[$index])) {
                    AccountingAccountsTransaction::where('id',$accounts_transactions_id[$index])->delete();
                }
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            print_r('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            exit;
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.delete_journal'))) {
            //abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');

        $acc_trans_mapping = AccountingAccTransMapping::where('id', $id)
                        ->where('business_id', $business_id)->firstOrFail();

        if (! empty($acc_trans_mapping)) {
            $acc_trans_mapping->delete();
            AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)->delete();
        }

        return ['success' => 1,
            'msg' => __('lang_v1.deleted_success'),
        ];
    }
}
