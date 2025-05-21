<?php

namespace App\Http\Controllers;

use App\Product;
use App\SellingPriceGroup;
use App\Utils\Util;
use App\Variation;
use App\VariationGroupPrice;
use DB;
use Excel;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class SellingPriceGroupController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $price_groups = SellingPriceGroup::where('business_id', $business_id)
                        ->select(['name', 'description', 'id', 'is_active']);

            return Datatables::of($price_groups)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@destroy\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_spg_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@activateDeactivate\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs  @if($is_active) tw-dw-btn-error @else tw-dw-btn-success @endif activate_deactivate_spg"><i class="fas fa-power-off"></i> @if($is_active) @lang("messages.deactivate") @else @lang("messages.activate") @endif</button>'
                )
                ->removeColumn('is_active')
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('selling_price_group.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $spg = SellingPriceGroup::create($input);

            //Create a new permission related to the created selling price group
            Permission::create(['name' => 'selling_price_group.'.$spg->id]);

            $output = ['success' => true,
                'data' => $spg,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function show(SellingPriceGroup $sellingPriceGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);

            return view('selling_price_group.edit')
                ->with(compact('spg'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->name = $input['name'];
                $spg->description = $input['description'];
                $spg->save();

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Show interface to download product price excel file.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProductPrice(){
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.update_product_price');
    }

    /**
     * Exports selling price group prices for all the products in xls format
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        $business_id = request()->user()->business_id;
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

        $variations = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                            ->where('p.business_id', $business_id)
                            ->whereIn('p.type', ['single', 'variable'])
                            ->select('sub_sku', 'p.name as product_name','p.sub_unit_ids', 'variations.name as variation_name', 'p.type', 'variations.id', 'pv.name as product_variation_name', 'sell_price_inc_tax')
                            ->with(['group_prices'])
                            ->get();
        $export_data = [];
        foreach ($variations as $variation) {
            $temp = [];
            $temp['product'] = $variation->type == 'single' ? $variation->product_name : $variation->product_name.' - '.$variation->product_variation_name.' - '.$variation->variation_name;
            $temp['sku'] = $variation->sub_sku;
            $temp['Selling Price Including Tax'] = $variation->sell_price_inc_tax;
            // Decode sub_unit_ids if stored as JSON
            $subunit_ids = [];
            if (!empty($variation->sub_unit_ids)) {
                $subunit_ids = is_array($variation->sub_unit_ids)
                    ? $variation->sub_unit_ids
                    : json_decode($variation->sub_unit_ids, true);
            }
            foreach ($price_groups as $price_group) {
                $price_group_id = $price_group->id;
                $variation_pg = $variation->group_prices->filter(function ($item) use ($price_group_id) {
                    return $item->price_group_id == $price_group_id;
                });
               
              
                

                
                $subunit_id = !empty($subunit_ids) ? $subunit_ids[0] : null;

                $temp[$price_group->name.'-baseunit'] = $variation_pg->isNotEmpty() ? $variation_pg->first()->price_inc_tax : '';
                 // Subunit Price (if exists)
                $subunit_price = '';
                if (!empty($subunit_id) && !empty($variation_pg->first()->price_per_unit)) {
                    $subunit_price = $variation_pg->first()->price_per_unit[(int)$subunit_id] ?? '';
                }
                if($variation->sub_sku=='0459'){
                   // dd($variation_pg,$subunit_id,$subunit_price,$variation_pg->first()->price_per_unit[$subunit_id]);
                }
                $temp[$price_group->name . '-subunit'] = $subunit_price;
           
                
               // $temp[$price_group->name.'-subunit'] = $variation_pg->isNotEmpty() ? (!empty($subunit_id) && !empty($variation_pg->first()->price_per_unit) ? $variation_pg->first()->price_per_unit[$subunit_id] : '') : '';

            }
            $export_data[] = $temp;
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }
        ob_start();

        return collect($export_data)->downloadExcel(
            'product_prices.xlsx',
            null,
            true
        );
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importOld(Request $request)
    {
        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('product_group_prices')) {
                $file = $request->file('product_group_prices');

                $parsed_array = Excel::toArray([], $file);

                $headers = $parsed_array[0][0];

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = request()->user()->business_id;
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

                //Get price group names from headers
                $imported_pgs = [];
                foreach ($headers as $key => $value) {
                    if (! empty($value) && $key > 2) {
                        $imported_pgs[$key] = $value;
                    }
                }

                $error_msg = '';
                DB::beginTransaction();

                foreach ($imported_data as $key => $value) {
                    $variation = Variation::where('sub_sku', $value[1])
                                        ->first();
                    if (empty($variation)) {
                        $row = $key + 1;
                        $error_msg = __('lang_v1.product_not_found_exception', ['sku' => $value[1], 'row' => $row]);

                        throw new \Exception($error_msg);
                    }

                    //Check if product base price is changed
                    if($variation->sell_price_inc_tax != $value[2]){
                        //update price for base selling price, adjust default_sell_price, profit %
                        $variation->sell_price_inc_tax = $value[2];
                        $tax = $variation->product->product_tax()->get();
                        $tax_percent = !empty($tax) && !empty($tax->first()) ? $tax->first()->amount : 0;
                        $variation->default_sell_price = $this->commonUtil->calc_percentage_base($value[2], $tax_percent);
                        $variation->profit_percent = $this->commonUtil
                                        ->get_percent($variation->default_purchase_price, $variation->default_sell_price);
                        $variation->update();
                    }

                    //update selling price
                    foreach ($imported_pgs as $k => $v) {
                        $price_group = $price_groups->filter(function ($item) use ($v) {
                            return strtolower($item->name) == strtolower($v);
                        });

                        if ($price_group->isNotEmpty()) {
                            //Check if price is numeric
                            if (! is_null($value[$k]) && ! is_numeric($value[$k])) {
                                $row = $key + 1;
                                $error_msg = __('lang_v1.price_group_non_numeric_exception', ['row' => $row]);

                                throw new \Exception($error_msg);
                            }

                            if (! is_null($value[$k])) {
                                VariationGroupPrice::updateOrCreate(
                                    ['variation_id' => $variation->id,
                                        'price_group_id' => $price_group->first()->id,
                                    ],
                                    ['price_inc_tax' => $value[$k],
                                    ]
                                );
                            }
                        } else {
                            $row = $key + 1;
                            $error_msg = __('lang_v1.price_group_not_found_exception', ['pg' => $v, 'row' => $row]);

                            throw new \Exception($error_msg);
                        }
                    }
                }
                DB::commit();
            }
            $output = ['success' => 1,
                'msg' => __('lang_v1.product_prices_imported_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect('update-product-price')->with('notification', $output);
        }

        return redirect('update-product-price')->with('status', $output);
    }
    public function import(Request $request)
    {
        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }
    
            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);
    
            if ($request->hasFile('product_group_prices')) {
                $file = $request->file('product_group_prices');
                $parsed_array = Excel::toArray([], $file);
    
                $headers = $parsed_array[0][0];
    
                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);
    
                $business_id = request()->user()->business_id;
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();
    
                // Separate baseunit & subunit columns
                $imported_pgs = [];
                foreach ($headers as $key => $value) {
                    if (!empty($value) && $key > 2) {
                        $imported_pgs[$key] = $value;
                    }
                }
    
                $error_msg = '';
                DB::beginTransaction();
    
                foreach ($imported_data as $rowIndex => $row) {
                    $variation = Variation::leftjoin('products','products.id','variations.product_id')
                                ->where('products.business_id',$business_id)
                                ->where('sub_sku', $row[1])
                                ->select('variations.*')
                                ->first();
                    if (empty($variation)) {
                        $row_num = $rowIndex + 1;
                        throw new \Exception(__('lang_v1.product_not_found_exception', ['sku' => $row[1], 'row' => $row_num]));
                    }
    
                    // Update default sell price if changed
                    if ($variation->sell_price_inc_tax != $row[2]) {
                        $variation->sell_price_inc_tax = $row[2];
                        $tax = $variation->product->product_tax()->first();
                        $tax_percent = !empty($tax) ? $tax->amount : 0;
    
                        $variation->default_sell_price = $this->commonUtil->calc_percentage_base($row[2], $tax_percent);
                        $variation->profit_percent = $this->commonUtil->get_percent($variation->default_purchase_price, $variation->default_sell_price);
                        $variation->save();
                    }
    
                    // Decode subunits (if any)
                    $subunit_ids = [];
                    if (!empty($variation->product->sub_unit_ids)) {
                        $subunit_ids = is_array($variation->product->sub_unit_ids)
                            ? $variation->product->sub_unit_ids
                            : json_decode($variation->product->sub_unit_ids, true);
                    }
    
                    // Handle group prices
                    $group_price_data = [];
                    foreach ($imported_pgs as $colIndex => $columnHeader) {
                        // Determine if it's baseunit or subunit
                        if (str_ends_with($columnHeader, '-baseunit')) {
                            $group_name = str_replace('-baseunit', '', $columnHeader);
                            $type = 'base';
                        } elseif (str_ends_with($columnHeader, '-subunit')) {
                            $group_name = str_replace('-subunit', '', $columnHeader);
                            $type = 'sub';
                        } else {
                            continue;
                        }
                        // Check if price group exists with name Note:: that name sholud typical 
                        // be like 'Price Group 1-baseunit' or 'Price Group 1-subunit' and it is Arabic name
                        $price_group = $price_groups->filter(function ($item) use ($group_name) {
                            return \Str::slug($item->name) == \Str::slug($group_name);
                        })->first();
                       
                        
                        if (empty($price_group)) {
                           
                            $row_num = $rowIndex + 1;
                            throw new \Exception(__('lang_v1.price_group_not_found_exception', ['pg' => $group_name, 'row' => $row_num]));
                        }
    
                        $price_value = $row[$colIndex];
                        if (!is_null($price_value) && !is_numeric($price_value)) {
                            $row_num = $rowIndex + 1;
                            $price_value=0;
                            //throw new \Exception(__('lang_v1.price_group_non_numeric_exception', ['row' => $row_num]));
                        }
    
                        $price_group_id = $price_group->id;
    
                        // Store base price or subunit price
                        if (!isset($group_price_data[$price_group_id])) {
                            $group_price_data[$price_group_id] = [
                                'price_inc_tax' => null,
                                'price_per_unit' => [],
                            ];
                        }
    
                        if ($type == 'base') {
                            $group_price_data[$price_group_id]['price_inc_tax'] = $price_value;
                        } elseif ($type == 'sub' && !empty($subunit_ids)) {
                            $subunit_id = $subunit_ids[0]; // assuming first subunit
                            $group_price_data[$price_group_id]['price_per_unit'][$subunit_id] = $price_value;
                        }
                    }
                   // dd($group_price_data,$variation->id,$row[1]);
                    // Save to DB
                    foreach ($group_price_data as $pg_id => $pg_data) {
                        VariationGroupPrice::updateOrCreate(
                            ['variation_id' => $variation->id, 'price_group_id' => $pg_id],
                            [
                                'price_inc_tax' => $pg_data['price_inc_tax'],
                                'price_per_unit' => !empty($pg_data['price_per_unit']) ? $pg_data['price_per_unit'] : null,
                            ]
                        );
                    }
                }
    
                DB::commit();
            }
    
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.product_prices_imported_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
    
            $output = ['success' => 0, 'msg' => $e->getMessage()];
            return redirect('update-product-price')->with('notification', $output);
        }
    
        return redirect('update-product-price')->with('status', $output);
    }
    
    /**
     * Activate/deactivate selling price group.
     */
    public function activateDeactivate($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);
            $spg->is_active = $spg->is_active == 1 ? 0 : 1;
            $spg->save();

            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];

            return $output;
        }
    }
}
