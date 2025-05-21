<?php

namespace App\Http\Controllers;

use App\User;
use App\Contact;

use App\Transaction;
use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\TransactionSellLine;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use App\Events\SellReturnAdded;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Events\TransactionPaymentDeleted;
use Modules\Accounting\Utils\AccountingUtil;

class SellReturnController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $transactionUtil;

    protected $accountingUtil;

    protected $contactUtil;

    protected $businessUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ContactUtil $contactUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil, AccountingUtil $accountingUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
        $this->accountingUtil = $accountingUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')

            ->join(
                'business_locations AS BS',
                'transactions.location_id',
                '=',
                'BS.id'
            )
            ->leftJoin(
                'transactions AS T',
                'transactions.return_parent_id',
                '=',
                'T.id'
            )
            ->leftJoin(
                'transaction_payments AS TP',
                'transactions.id',
                '=',
                'TP.transaction_id'
            )
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell_return')
            ->select(
                'transactions.id',
                'transactions.transaction_date',
                'transactions.ref_no',
                'transactions.invoice_no',
                'contacts.name',
                'transactions.zatca_status',
                'contacts.supplier_business_name',
                'transactions.status',
                'transactions.payment_status',
                'transactions.final_total',
                'transactions.return_parent_id',
                'BS.name as location_name',
                'T.ref_no as parent_sale',
                DB::raw('SUM(TP.amount) as amount_paid')
            )
            ->groupBy('transactions.id');
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }
            //cost center filter
            if (request()->has('cost_center_id')) {
                $cost_center_id = request()->get('cost_center_id');
                if (! empty($cost_center_id)) {
                    $sells->where('transactions.cost_center_id', $cost_center_id);
                }
            }
            if (! auth()->user()->can('access_sell_return') && auth()->user()->can('access_own_sell_return')) {
                $sells->where('transactions.created_by', request()->session()->get('user.id'));
            }

            //Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (! empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (! empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (! empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                        ->whereDate('transactions.transaction_date', '<=', $end);
            }

           // $sells->groupBy('transactions.id');

           //for zatca module Retrieve the 'is_zatca' parameter from the request; default to 0 if not provided and only comes 1 from zatca module
            $is_zatca = !empty(request()->input('is_zatca')) ? request()->input('is_zatca') : 0;

            if ($is_zatca) {
                // Include 'zatca_status' in the selected columns
                $sells->addSelect('transactions.zatca_status');

                // Check if 'zatca_status' filter is provided in the request
                if (!empty(request()->input('zatca_status'))) {
                    // If 'zatca_status' is 'pending', filter transactions where 'zatca_status' is NULL
                    if (request()->input('zatca_status') == 'pending') {
                        $sells->whereNull('transactions.zatca_status');
                    } else {
                        // Otherwise, filter transactions by the given 'zatca_status' value
                        $sells->where('transactions.zatca_status', request()->input('zatca_status'));
                    }
                }
            }


            return Datatables::of($sells, $is_zatca)
                ->addColumn(
                    'action',
                    function ($row) use ($is_zatca) {
                        if ($is_zatca) {
                            if ($row->zatca_status == 'success') {
                                return '<div class="btn-group">
                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle"
                                    data-toggle="dropdown" aria-expanded="false">' .
                                    __('messages.actions') .
                                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                    <li>
                                        <a class="download-xml" href="'.action([\Modules\ZatcaIntegrationKsa\Http\Controllers\ZatcaInvoiceController::class, 'downloadXml'], [$row->id]).'">
                                            <i class="fas fa-file-download"></i> '.__('zatcaintegrationksa::lang.download_xml').'
                                        </a>
                                    </li>
                                    <li>
                                        <a class="download-a3-pdf" href="#">
                                            <i class="fas fa-file-download"></i> '.__('zatcaintegrationksa::lang.download_a3_pdf').'
                                        </a>
                                    </li>
                                </ul></div>';                            
                            } else {
                                return '<a href="' . action([\Modules\ZatcaIntegrationKsa\Http\Controllers\ZatcaInvoiceController::class, 'sycs_sale_return'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max return_sale_sycs">' . __('zatcaintegrationksa::lang.sync') . '</a>';
                            }
                        }
                $html = '<div class="btn-group">
                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info tw-w-max dropdown-toggle" 
                                    data-toggle="dropdown" aria-expanded="false">'.
                                    __('messages.actions').
                                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                if (! empty($row->return_parent_id)) {
                    $html .= '<li><a href="'.action([\App\Http\Controllers\SellReturnController::class, 'add'], $row->return_parent_id).'" ><i class="glyphicon glyphicon-edit"></i>'.
                            __('messages.edit').
                            '</a></li>';
                } else {
                 //   $html .= '<li><a href="'.action([\App\Http\Controllers\CombinedSellReturnController::class, 'edit'], $row->id).'" ><i class="glyphicon glyphicon-edit"></i>'.
                 //           __('messages.edit').
                 //           '</a></li>';
                }

                if ($row->payment_status != 'paid') {
                    $html .= '<li><a href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'addPayment'], [$row->id]).'" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i>'.__('purchase.add_payment').'</a></li>';
                }

                $html .= '<li><a href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->id]).'" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i>'.__('purchase.view_payments').'</a></li>';

                $html .= '<li><a href="'.action([\App\Http\Controllers\SellReturnController::class, 'destroy'], $row->id).'" class="delete_sell_return" ><i class="fa fa-trash"></i>'.
                            __('messages.delete').
                            '</a></li>';
                $html .= '</ul></div>';

                return $html;
            }) 
            ->removeColumn('id')
            ->removeColumn('return_parent_id')
            ->editColumn(
                'final_total',
                '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
            )
            ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
            ->editColumn('name', function ($row) {
                $name = ! empty($row->name) ? $row->name : '';

                return $name.' '.$row->supplier_business_name;
            })
            ->editColumn(
                'payment_status',
                '<a href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, \'show\'], [$id])}}" class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="@if($payment_status != "paid"){{__(\'lang_v1.\' . $payment_status)}}@else{{__("lang_v1.received")}}@endif"><span class="label @payment_status($payment_status)">@if($payment_status != "paid"){{__(\'lang_v1.\' . $payment_status)}} @else {{__("lang_v1.received")}} @endif
                    </span></a>'
            )
            ->editColumn('parent_sale', function ($row) {
                $html = '';
                if (! empty($row->parent_sale)) {
                    $html = '<a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->return_parent_id]).'" class="btn-modal" data-container=".view_modal">'.$row->parent_purchase.'</a>';
                }

                return $html;
            })
            ->editColumn('business_location', function ($row) {
                return $row->location_name ?? '' ;
            })
            ->addColumn('payment_due', function ($row) {
                $due = $row->final_total - $row->amount_paid;

                return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="'.$due.'">'.$due.'</sapn>';
            })
            ->setRowAttr([
                'data-href' => function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        $return_id = ! empty($row->return_parent_id) ? $row->return_parent_id : $row->id;

                        return  action([\App\Http\Controllers\SellReturnController::class, 'show'], [$return_id]);
                    } else {
                        return '';
                    }
                }, ])
            ->rawColumns(['final_total', 'action', 'payment_status', 'parent_purchase', 'payment_due'])
            ->make(true);
    }
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sell_return.index')->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\SellReturnController::class, 'index']));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        //$walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $taxes = [];
        if (! empty($sell->return_parent->tax)) {
            if ($sell->return_parent->tax->is_tax_group) {
                $taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->return_parent->tax, $sell->return_parent->tax_amount));
            } else {
                $taxes[$sell->return_parent->tax->name] = $sell->return_parent->tax_amount;
            }
        }

        return view('sell_return.create')
            ->with(compact('business_locations', 'taxes'));
    }
    public function save()
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input_data = $request->only(['location_id', 'transaction_date', 'final_total', 'ref_no',
                'tax_id', 'tax_amount', 'contact_id', ]);
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $user_id = $request->session()->get('user.id');

            $input_data['type'] = 'purchase_return';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            //cost center
            if (request()->has('cost_center_id')) {
                $cost_center_id = request()->get('cost_center_id');
                if (! empty($cost_center_id)) {
                    $input_data['cost_center_id'] = $cost_center_id;
                }
            }
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['total_before_tax'] = $input_data['final_total'] - $input_data['tax_amount'];

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('purchase_return');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('purchase_return', $ref_count);
            }

            //upload document
            $input_data['document'] = $this->productUtil->uploadFile($request, 'document', 'documents');

            $products = $request->input('products');

            if (! empty($products)) {
                $product_data = [];

                foreach ($products as $product) {
                    $unit_price = $this->productUtil->num_uf($product['unit_price']);
                    $return_line = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => 0,
                        'purchase_price' => $unit_price,
                        'pp_without_discount' => $unit_price,
                        'purchase_price_inc_tax' => $unit_price,
                        'quantity_returned' => $this->productUtil->num_uf($product['quantity']),
                        'lot_number' => ! empty($product['lot_number']) ? $product['lot_number'] : null,
                        'exp_date' => ! empty($product['exp_date']) ? $this->productUtil->uf_date($product['exp_date']) : null,
                    ];

                    $product_data[] = $return_line;

                    //Decrease available quantity
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $input_data['location_id'],
                        $this->productUtil->num_uf($product['quantity'])
                    );
                }

                $purchase_return = Transaction::create($input_data);
                $purchase_return->purchase_lines()->createMany($product_data);

                //update payment status
                $this->transactionUtil->updatePaymentStatus($purchase_return->id, $purchase_return->final_total);
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.purchase_return_added_success'),
            ];
            event(new PurchaseReturnAdded($purchase_return));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('purchase-return')->with('status', $output);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add($id)
    {
        if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $sell = Transaction::where('business_id', $business_id)
                            ->with(['sell_lines', 'location', 'return_parent', 'contact', 'tax', 'sell_lines.sub_unit', 'sell_lines.product', 'sell_lines.product.unit'])
                            ->find($id);

        foreach ($sell->sell_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }

            $sell->sell_lines[$key]->formatted_qty = $this->transactionUtil->num_f($value->quantity, false, null, true);
        }

        return view('sell_return.add')
            ->with(compact('sell'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->except('_token');

            if (! empty($input['products'])) {
                $business_id = $request->session()->get('user.business_id');

                //Check if subscribed or not
                if (! $this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\SellReturnController::class, 'index']));
                }

                $user_id = $request->session()->get('user.id');

                DB::beginTransaction(); 

                $sell_return = $this->transactionUtil->addSellReturn($input, $business_id, $user_id);
                event(new SellReturnAdded($sell_return ));
                $receipt = $this->receiptContent($business_id, $sell_return->location_id, $sell_return->id);

                // for zatca invoice response
                $this->moduleUtil->getModuleData('after_sales_return', ['transaction' => $sell_return]);

                DB::commit();

                $output = ['success' => 1,
                    'msg' => __('lang_v1.success'),
                    'receipt' => $receipt,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            } else {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                $msg = __('messages.something_went_wrong');
            }

            $output = ['success' => 0,
                'msg' => $msg,
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(
                                    'contact',
                                    'return_parent',
                                    'tax',
                                    'sell_lines',
                                    'sell_lines.product',
                                    'sell_lines.variations',
                                    'sell_lines.sub_unit',
                                    'sell_lines.product',
                                    'sell_lines.product.unit',
                                    'location'
                                );

        if (! auth()->user()->can('access_sell_return') && auth()->user()->can('access_own_sell_return')) {
            $sells->where('created_by', request()->session()->get('user.id'));
        }
        $sell = $query->first();
        if(empty($sell->return_parent)){
            $sell->return_parent=$sell;
        }
        foreach ($sell->sell_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }
        }

        $sell_taxes = [];
        if (! empty($sell->return_parent->tax)) {
            if ($sell->return_parent->tax->is_tax_group) {
                $sell_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->return_parent->tax, $sell->return_parent->tax_amount));
            } else {
                $sell_taxes[$sell->return_parent->tax->name] = $sell->return_parent->tax_amount;
            }
        }

        $total_discount = 0;
        if ($sell->return_parent->discount_type == 'fixed') {
            $total_discount = $sell->return_parent->discount_amount;
        } elseif ($sell->return_parent->discount_type == 'percentage') {
            $discount_percent = $sell->return_parent->discount_amount;
            if ($discount_percent == 100) {
                $total_discount = $sell->return_parent->total_before_tax;
            } else {
                $total_after_discount = $sell->return_parent->final_total - $sell->return_parent->tax_amount;
                $total_before_discount = $total_after_discount * 100 / (100 - $discount_percent);
                $total_discount = $total_before_discount - $total_after_discount;
            }
        }

        $activities = Activity::forSubject($sell->return_parent)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        return view('sell_return.show')
            ->with(compact('sell', 'sell_taxes', 'total_discount', 'activities'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                //Begin transaction
                DB::beginTransaction();

                $query = Transaction::where('id', $id)
                    ->where('business_id', $business_id)
                    ->where('type', 'sell_return')
                    ->with(['sell_lines', 'payment_lines']);

                if (! auth()->user()->can('access_sell_return') && auth()->user()->can('access_own_sell_return')) {
                    $sells->where('created_by', request()->session()->get('user.id'));
                }
                $sell_return = $query->first();

                $sell_lines = TransactionSellLine::where('transaction_id',
                                            $sell_return->return_parent_id)
                                    ->get();

                if (! empty($sell_return)) {
                    $transaction_payments = $sell_return->payment_lines;

                    foreach ($sell_lines as $sell_line) {
                        if ($sell_line->quantity_returned > 0) {
                            $quantity = 0;
                            $quantity_before = $this->transactionUtil->num_f($sell_line->quantity_returned);

                            $sell_line->quantity_returned = 0;
                            $sell_line->save();

                            //update quantity sold in corresponding purchase lines
                            $this->transactionUtil->updateQuantitySoldFromSellLine($sell_line, 0, $quantity_before);

                            // Update quantity in variation location details
                            $this->productUtil->updateProductQuantity($sell_return->location_id, $sell_line->product_id, $sell_line->variation_id, 0, $quantity_before);
                        }
                    }

                    $sell_return->delete();
                    $this->accountingUtil->deleteMap( $id, null);
                    foreach ($transaction_payments as $payment) {
                        event(new TransactionPaymentDeleted($payment));
                    }
                }

                DB::commit();
                $output = ['success' => 1,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (\Exception $e) {
                DB::rollBack();

                if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                    $msg = $e->getMessage();
                } else {
                    \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                    $msg = __('messages.something_went_wrong');
                }

                $output = ['success' => 0,
                    'msg' => $msg,
                ];
            }

            return $output;
        }
    }

    /**
     * Returns the content for the receipt
     *
     * @param  int  $business_id
     * @param  int  $location_id
     * @param  int  $transaction_id
     * @param  string  $printer_type = null
     * @return array
     */
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null
    ) {
        $output = ['is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => [],
        ];

        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        //Check if printing of invoice is enabled or not.
        if ($location_details->print_receipt_on_invoice == 1) {
            //If enabled, get print type.
            $output['is_enabled'] = true;

            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_details->invoice_layout_id);

            //Check if printer setting is provided.
            $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

            $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);

            //If print type browser - return the content, printer - return printer config data, and invoice format config
            $output['print_title'] = $receipt_details->invoice_no;
            if ($receipt_printer_type == 'printer') {
                $output['print_type'] = 'printer';
                $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
                $output['data'] = $receipt_details;
            } else {
                $output['html_content'] = view('sell_return.receipt', compact('receipt_details'))->render();
            }
        }

        return $output;
    }

    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];

                $business_id = $request->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                                ->where('id', $transaction_id)
                                ->first();

                if (empty($transaction)) {
                    return $output;
                }
                if(empty($transaction->return_parent)){
                    $transaction->return_parent=$transaction;
                }
                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, 'browser');

                if (! empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                dd($e->getMessage(), $e->getLine(), $e->getFile());
                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Function to validate sell for sell return
     */
    public function validateInvoiceToReturn($invoice_no)
    {
        if (! auth()->user()->can('sell.create') && ! auth()->user()->can('direct_sell.access') && ! auth()->user()->can('view_own_sell_only')) {
            return ['success' => 0,
                'msg' => trans('lang_v1.permission_denied'),
            ];
        }

        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
                            ->where('invoice_no', $invoice_no);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (! auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('created_by', auth()->user()->id);
        }

        $sell = $query->first();

        if (empty($sell)) {
            return ['success' => 0,
                'msg' => trans('lang_v1.sell_not_found'),
            ];
        }

        return ['success' => 1,
            'redirect_url' => action([\App\Http\Controllers\SellReturnController::class, 'add'], [$sell->id]),
        ];
    }

   
}
