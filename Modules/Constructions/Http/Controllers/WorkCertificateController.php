<?php

namespace Modules\Constructions\Http\Controllers;

use App\User;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Business;
use App\Variation;
use App\Transaction;
use App\CustomerGroup;
use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use Illuminate\Routing\Controller;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Events\PurchaseCreatedOrModified;
use Modules\Constructions\Entities\Project;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountTransaction;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Log;

class WorkCertificateController extends Controller{    /**     * All Utils instance.     */    protected $productUtil;    protected $transactionUtil;    protected $moduleUtil;    protected $businessUtil;        /**     * Payment line dummy data     */    protected $dummyPaymentLine;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(
        ProductUtil $productUtil, 
        TransactionUtil $transactionUtil, 
        BusinessUtil $businessUtil, 
        ModuleUtil $moduleUtil
    ) {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('constructions.view_work_certificate') && !auth()->user()->can('constructions.create_work_certificate')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $work_certificates = Transaction::with('contact')
                ->where('business_id', $business_id)
                ->where('type', 'purchase')
                ->where('sub_type', 'work_certificate')
                ->select('transactions.*');
            $work_certificates = $this->transactionUtil->getListPurchases($business_id);
            $work_certificates->where('transactions.sub_type', 'work_certificate');
            // Apply filters
            if (!empty(request()->contact_id)) {
                $work_certificates->where('contact_id', request()->contact_id);
            }

            if (!empty(request()->project_id)) {
                $work_certificates->where('project_id', request()->project_id);
            }

            if (!empty(request()->location_id)) {
                $work_certificates->where('location_id', request()->location_id);
            }

            if (!empty(request()->payment_status)) {
                $work_certificates->where('payment_status', request()->payment_status);
            }

            if (!empty(request()->status)) {
                $work_certificates->where('status', request()->status);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $work_certificates->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            return Datatables::of($work_certificates)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">'.
                                __('messages.actions').
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can('constructions.view_work_certificate')) {
                      //  $html .= '<li><a href="#" data-href="'.action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'show'], [$row->id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>'.__('messages.view').'</a></li>';
                        $html .= '<li><a class="print-certificate" target="_blank" href="'.action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'printCertificate'], [$row->id]).'"><i class="fas fa-print" aria-hidden="true"></i>'.__('messages.print').'</a></li>';
                    }

                    if (auth()->user()->can('constructions.edit_work_certificate')) {
                        $html .= '<li><a href="'.action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'edit'], [$row->id]).'"><i class="fas fa-edit"></i>'.__('messages.edit').'</a></li>';
                    }

                    if (auth()->user()->can('constructions.delete_work_certificate')) {
                        $html .= '<li><a href="'.action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'destroy'], [$row->id]).'" class="delete-work-certificate"><i class="fas fa-trash"></i>'.__('messages.delete').'</a></li>';
                    }

                    
                    $html .= '</ul></div>';

                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {
                    return $row->ref_no;
                })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('contact_name', function ($row) {
                    return $row->name . ' - ' . $row->supplier_business_name;
                })
                ->editColumn('status', function ($row) {
                    $statuses = [
                        'draft' => '<span class="label label-info">'.__('constructions::lang.draft').'</span>',
                        'pending' => '<span class="label label-warning">'.__('constructions::lang.pending').'</span>',
                        'approved' => '<span class="label label-success">'.__('constructions::lang.approved').'</span>',
                        'rejected' => '<span class="label label-danger">'.__('constructions::lang.rejected').'</span>',
                        'final' => '<span class="label label-success">'.__('constructions::lang.final').'</span>',
                    ];
                    return $statuses[$row->status] ?? '';
                })
                ->editColumn('payment_status', function ($row) {
                    $payment_statuses = [
                        'paid' => '<span class="label label-success">'.__('constructions::lang.paid').'</span>',
                        'due' => '<span class="label label-danger">'.__('constructions::lang.due').'</span>',
                        'partial' => '<span class="label label-warning">'.__('constructions::lang.partial').'</span>',
                    ];
                    return $payment_statuses[$row->payment_status] ?? '';
                })
                ->editColumn('final_total', '{{@num_format($final_total)}}')
                ->rawColumns(['action', 'status', 'payment_status'])
                ->make(true);
        }

        $projects = Project::where('business_id', $business_id)
                       ->pluck('name', 'id');

        $suppliers = Contact::suppliersDropdown($business_id);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $payment_statuses = [
            'paid' => __('constructions::lang.paid'),
            'due' => __('constructions::lang.due'),
            'partial' => __('constructions::lang.partial'),
        ];
        $statuses = [
            'draft' => __('constructions::lang.draft'),
            'pending' => __('constructions::lang.pending'),
            'approved' => __('constructions::lang.approved'),
            'rejected' => __('constructions::lang.rejected'),
            'final' => __('constructions::lang.final'),
        ];
        $orderStatuses = [
            'draft' => __('constructions::lang.draft'),
            'pending' => __(    'constructions::lang.pending'),
            'approved' => __('constructions::lang.approved'),
            'rejected' => __('constructions::lang.rejected'),
            'final' => __('constructions::lang.final'),
        ];
        return view('constructions::work-certificates.index')
            ->with(compact('suppliers', 'business_locations', 'payment_statuses', 'statuses', 'projects', 'orderStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('constructions.create_work_certificate')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];

        return view('constructions::work-certificates.create')
            ->with(compact('taxes', 'orderStatuses', 'business_locations', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'common_settings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\PurchaseController::class, 'index']));
            }

            $transaction_data = $request->only(['ref_no','type' ,'status', 'contact_id', 'transaction_date', 'total_before_tax', 'location_id', 'discount_type', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids']);

            $exchange_rate = $transaction_data['exchange_rate'];
            
            //Reverse exchange rate and save it.
            //$transaction_data['exchange_rate'] = $transaction_data['exchange_rate'];

            //TODO: Check for "Undefined index: total_before_tax" issue
            //Adding temporary fix by validating
            $request->validate([
                'status' => 'required',
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                'document' => 'file|max:'.(config('constants.document_size_limit') / 1000),
            ]);

            $user_id = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            //Update business exchange rate.
            Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            //unformat input values
            $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($transaction_data['discount_type'] == 'fixed') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif ($transaction_data['discount_type'] == 'percentage') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details);
            } else {
                $transaction_data['discount_amount'] = 0;
            }

            $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount'], $currency_details) * $exchange_rate;
            $transaction_data['shipping_charges'] = $this->productUtil->num_uf($transaction_data['shipping_charges'], $currency_details) * $exchange_rate;
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details) * $exchange_rate;

            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['sub_type'] = 'work_certificate';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
            if(!$this->transactionUtil->fy_check($business_id,$transaction_data['transaction_date'])){
                $output = ['success' => 0,
                'msg' => __('lang_v1.fy_year_closed'),
                ];
                return redirect()->back()->with('status', $output);
            }
            //upload document
            $transaction_data['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            $transaction_data['custom_field_1'] = $request->input('custom_field_1', null);
            $transaction_data['custom_field_2'] = $request->input('custom_field_2', null);
            $transaction_data['custom_field_3'] = $request->input('custom_field_3', null);
            $transaction_data['custom_field_4'] = $request->input('custom_field_4', null);

            $transaction_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
            $transaction_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
            $transaction_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
            $transaction_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
            $transaction_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);
            
            $transaction_data['cost_center_id'] = $request->input('cost_center_id', null);

            if ($request->input('additional_expense_value_1') != '') {
                $transaction_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
                $transaction_data['additional_expense_value_1'] = $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate;
            }

            if ($request->input('additional_expense_value_2') != '') {
                $transaction_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
                $transaction_data['additional_expense_value_2'] = $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate;
            }

            if ($request->input('additional_expense_value_3') != '') {
                $transaction_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
                $transaction_data['additional_expense_value_3'] = $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details) * $exchange_rate;
            }

            if ($request->input('additional_expense_value_4') != '') {
                $transaction_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');
                $transaction_data['additional_expense_value_4'] = $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details) * $exchange_rate;
            }

            DB::beginTransaction();

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            //Generate reference number
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
            }

            $transaction = Transaction::create($transaction_data);

            $purchase_lines = [];
            $purchases = $request->input('purchases');

            $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing);

            //Add Purchase payments
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $request->input('payment'));

            //update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            if (! empty($transaction->purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($transaction->purchase_order_ids);
            }

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $this->transactionUtil->activityLog($transaction, 'added');

            PurchaseCreatedOrModified::dispatch($transaction);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('purchase.purchase_add_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        
        return redirect('constructions/work-certificates')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('constructions.view_work_certificate')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        $certificate = Transaction::where('business_id', $business_id)
                            ->where('id', $id)
                            ->with([
                                'contact', 'purchase_lines', 
                                'purchase_lines.product', 
                                'purchase_lines.variations', 
                                'purchase_lines.variations.product_variation', 
                                'location', 'payment_lines'
                            ])
                            ->first();
        
        // Check if the transaction exists and is a work certificate
        if (empty($certificate) || $certificate->type != 'purchase' || $certificate->sub_type != 'work_certificate') {
            abort(404, 'Work Certificate not found');
        }
        
        $project = null;
        if (!empty($certificate->project_id)) {
            $project = Project::find($certificate->project_id);
        }
        
        $statuses = [
            'draft' => __('constructions::lang.draft'),
            'pending' => __('constructions::lang.pending'),
            'approved' => __('constructions::lang.approved'),
            'rejected' => __('constructions::lang.rejected'),
            'final' => __('constructions::lang.final'),
        ];
        
        return view('constructions::work-certificates.show')
                ->with(compact('certificate', 'project', 'statuses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('constructions.edit_work_certificate')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\PurchaseController::class, 'index']));
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (! $this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]), ]);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                'msg' => __('lang_v1.return_exist'), ]);
        }

        $business = Business::find($business_id);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
        $purchase = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(
                        'contact',
                        'purchase_lines',
                        'purchase_lines.product',
                        'purchase_lines.product.unit',
                        'purchase_lines.product.second_unit',
                        //'purchase_lines.product.unit.sub_units',
                        'purchase_lines.variations',
                        'purchase_lines.variations.product_variation',
                        'location',
                        'purchase_lines.sub_unit',
                        'purchase_lines.purchase_order_line'
                    )
                    ->first();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        $orderStatuses = $this->productUtil->orderStatuses();

        $business_locations = BusinessLocation::forDropdown($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];

        $purchase_orders = null;
        if (! empty($common_settings['enable_purchase_order'])) {
            $purchase_orders = Transaction::where('business_id', $business_id)
                                        ->where('type', 'purchase_order')
                                        ->where('contact_id', $purchase->contact_id)
                                        ->where(function ($q) use ($purchase) {
                                            $q->where('status', '!=', 'completed');

                                            if (! empty($purchase->purchase_order_ids)) {
                                                $q->orWhereIn('id', $purchase->purchase_order_ids);
                                            }
                                        })
                                        ->pluck('ref_no', 'id');
        }

        return view('constructions::work-certificates.edit')
            ->with(compact(
                'taxes',
                'purchase',
                'orderStatuses',
                'business_locations',
                'business',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'purchase_orders',
                'common_settings'
            ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request, $id)
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $transaction = Transaction::findOrFail($id);

            //Validate document size
            $request->validate([
                'document' => 'file|max:'.(config('constants.document_size_limit') / 1000),
            ]);

            $transaction = Transaction::findOrFail($id);
            $before_status = $transaction->status;
            $business_id = request()->session()->get('user.business_id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            $transaction_before = $transaction->replicate();

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $update_data = $request->only(['ref_no', 'status', 'contact_id','sub_type',
                'transaction_date', 'total_before_tax',
                'discount_type', 'discount_amount', 'tax_id',
                'tax_amount', 'shipping_details',
                'shipping_charges', 'final_total',
                'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids', ]);

            $exchange_rate = $update_data['exchange_rate'];

            //Reverse exchage rate and save
            //$update_data['exchange_rate'] = number_format(1 / $update_data['exchange_rate'], 2);

            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);
            if(!$this->transactionUtil->fy_check($business_id,$update_data['transaction_date'])){
                $output = ['success' => 0,
                'msg' => __('lang_v1.fy_year_closed'),
                ];
                return redirect()->back()->with('status', $output);
            }
            //unformat input values
            $update_data['total_before_tax'] = $this->productUtil->num_uf($update_data['total_before_tax'], $currency_details) * $exchange_rate;
            //sub_type
            $update_data['sub_type'] = 'work_certificate';
            // If discount type is fixed them multiply by exchange rate, else don't
            if ($update_data['discount_type'] == 'fixed') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif ($update_data['discount_type'] == 'percentage') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details);
            } else {
                $update_data['discount_amount'] = 0;
            }

            $update_data['tax_amount'] = $this->productUtil->num_uf($update_data['tax_amount'], $currency_details) * $exchange_rate;
            $update_data['shipping_charges'] = $this->productUtil->num_uf($update_data['shipping_charges'], $currency_details) * $exchange_rate;
            $update_data['final_total'] = $this->productUtil->num_uf($update_data['final_total'], $currency_details) * $exchange_rate;
            //unformat input values ends

            $update_data['custom_field_1'] = $request->input('custom_field_1', null);
            $update_data['custom_field_2'] = $request->input('custom_field_2', null);
            $update_data['custom_field_3'] = $request->input('custom_field_3', null);
            $update_data['custom_field_4'] = $request->input('custom_field_4', null);

            $update_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
            $update_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
            $update_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
            $update_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
            $update_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);
            $update_data['cost_center_id'] = $request->input('cost_center_id', null);
            //upload document
            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (! empty($document_name)) {
                $update_data['document'] = $document_name;
            }

            $purchase_order_ids = $transaction->purchase_order_ids ?? [];

            $update_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
            $update_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
            $update_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
            $update_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');

            $update_data['additional_expense_value_1'] = $request->input('additional_expense_value_1') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_2'] = $request->input('additional_expense_value_2') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_3'] = $request->input('additional_expense_value_3') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_4'] = $request->input('additional_expense_value_4') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details) * $exchange_rate : 0;

            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            //Update transaction payment status
            $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id);
            $transaction->payment_status = $payment_status;

            $purchases = $request->input('purchases');

            $delete_purchase_lines = $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing, $before_status);

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $new_purchase_order_ids = $transaction->purchase_order_ids ?? [];
            $purchase_order_ids = array_merge($purchase_order_ids, $new_purchase_order_ids);
            if (! empty($purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($purchase_order_ids);
            }

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            PurchaseCreatedOrModified::dispatch($transaction);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('purchase.purchase_update_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return back()->with('status', $output);
        }

        return redirect('constructions/work-certificates')->with('status', $output);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('constructions.delete_work_certificate')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            
                        $transaction = Transaction::where('business_id', $business_id)                                ->where('id', $id)                                ->where('type', 'purchase')                                ->where('sub_type', 'work_certificate')                                ->first();                        if (empty($transaction)) {                return response()->json(['success' => 0, 'msg' => __('constructions::lang.certificate_not_found')]);            }
            
            DB::beginTransaction();
            
            // Delete purchase lines
            $transaction->purchase_lines()->delete();
            
            // Delete transaction
            $transaction->delete();
            
            DB::commit();
            
            $output = ['success' => 1,
                        'msg' => __('constructions::lang.work_certificate_delete_success')
                    ];
                    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                        'msg' => __('messages.something_went_wrong')
                    ];
        }

        return $output;
    }
    
    /**
     * Print work certificate
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**     * Show modal for updating status     *      * @param Request $request     * @return \Illuminate\Http\Response     */    public function getUpdateStatus(Request $request)    {        if (!auth()->user()->can('constructions.update_work_certificate_status')) {            abort(403, 'Unauthorized action.');        }        $business_id = $request->session()->get('user.business_id');                $transaction = Transaction::where('business_id', $business_id)                        ->where('id', $request->certificate_id)                        ->where('type', 'purchase')                        ->where('sub_type', 'work_certificate')                        ->first();                if (empty($transaction)) {            abort(404, __('constructions::lang.certificate_not_found'));        }        $statuses = [            'draft' => __('constructions::lang.draft'),            'pending' => __('constructions::lang.pending'),            'approved' => __('constructions::lang.approved'),            'rejected' => __('constructions::lang.rejected'),            'final' => __('constructions::lang.final'),        ];        return view('constructions::work-certificates.partials.update_purchase_status_modal')                ->with(compact('transaction', 'statuses'));    }        
    public function printCertificate($id)
    {
        if (!auth()->user()->can('constructions.view_work_certificate')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        $certificate = Transaction::where('business_id', $business_id)
                            ->where('id', $id)
                            ->with([
                                'contact', 'purchase_lines', 
                                'purchase_lines.product', 
                                'purchase_lines.variations', 
                                'purchase_lines.variations.product_variation', 
                                'location', 'payment_lines'
                            ])
                            ->first();
        
        if (empty($certificate) || $certificate->type != 'purchase' || $certificate->sub_type != 'work_certificate') {
            abort(404, 'Work Certificate not found');
        }
        
        $project = null;
        if (!empty($certificate->project_id)) {
            $project = Project::find($certificate->project_id);
        }
        $purchase=$certificate;
        $output = ['success' => 1, 'receipt' => []];
        $business = Business::find($business_id);
        $output['receipt']['is_duplicate'] = false;
        $account_id =AccountingAccount::where('contact_id',$certificate->contact_id)->first()->id ?? null;
        $payment_lines = AccountingAccountsTransaction::where('accounting_account_id', $account_id)->where('type','debit')->get();

        return view('constructions::work-certificates.print', compact('certificate', 'project', 'business','purchase','payment_lines'));
        
        return $output;
    }
    
    /**
     * Update work certificate status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        if (!auth()->user()->can('constructions.update_work_certificate_status')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            
                        $transaction = Transaction::where('business_id', $business_id)                                ->where('id', $request->certificate_id)                                ->where('type', 'purchase')                                ->where('sub_type', 'work_certificate')                                ->first();                        if (empty($transaction)) {                return response()->json(['success' => 0, 'msg' => __('constructions::lang.certificate_not_found')]);            }
            
            DB::beginTransaction();
            
            $transaction->status = $request->status;
            $transaction->save();
            
            // Add activity log
            activity()
                ->performedOn($transaction)
                ->withProperties(['status' => $request->status, 'updated_by' => auth()->user()->id])
                ->log('Work certificate status updated');
            
            DB::commit();
            
            $output = ['success' => 1,
                        'msg' => __('constructions::lang.status_update_success')
                    ];
                    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                        'msg' => __('messages.something_went_wrong')
                    ];
        }

        return $output;
    }
}
