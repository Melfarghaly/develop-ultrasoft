<?php

namespace Modules\ZatcaIntegrationKsa\Http\Controllers;

use App\BusinessLocation;
use App\Product;
use App\TaxRate;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\ZatcaIntegrationKsa\Entities\ZatcaDocument;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\AdditionalDocumentReference;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\AllowanceCharge;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\BillingReference;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\Client;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\Delivery;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\InvoiceGenerator;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\InvoiceLine;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\LegalMonetaryTotal;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\LineTaxCategory;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\PaymentType;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\PIH;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\ReturnReason;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\Supplier;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\TaxesTotal;
use Modules\ZatcaIntegrationKsa\Http\Controllers\src\Invoice\TaxSubtotal;

class ZatcaInvoiceController extends Controller
{
    public function salesList()
    {
        if (!auth()->user()->can('ZatcaIntegrationKsa.sales')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, false);


        return view('zatcaintegrationksa::sale.sales_list', compact('business_locations'));
    }

    public function returnSalesList()
    {
        if (!auth()->user()->can('ZatcaIntegrationKsa.sales_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, false);

        return view('zatcaintegrationksa::sale.return_sales_list', compact('business_locations'));
    }

    public function sycs_sale($id)
    {
        try {
            \DB::beginTransaction();

            $business_id = request()->session()->get('user.business_id');

            $query = Transaction::where('business_id', $business_id)
                ->where('id', $id)->firstOrFail();

            // Retrieve the business location associated with the transaction.
            $businessLocation = BusinessLocation::where('id', $query->location_id)
                ->where('business_id', $business_id)
                ->whereNotNull('zatca_response')
                ->whereNotNull('zatca_details')
                ->first();

            if (!$businessLocation) {
                return [
                    'success' => 0,
                    'msg' => __('zatcaintegrationksa::lang.missing_zatca_details'),
                ];
            }

            $response = $this->sync_zatca_sale($id, $business_id);

            if (!$response['success']) {
                $output = [
                    'success' => 0,
                    'msg' => $response['msg'],
                ];
                return $output;
            }

            \DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return $output;
        } catch (\Exception $e) {
            \DB::rollBack();
            // Log the error or handle it as needed.
            Log::error(__('zatcaintegrationksa::lang.failed_to_process_sale', ['error' => $e->getMessage()]));
            return [
                'success' => 0,
                'msg' => __('zatcaintegrationksa::lang.failed_to_process_sale', ['error' => $e->getMessage()]),
            ];

            return $output;
        }
    }

    public function sync_zatca_sale($id, $business_id)
    {
        try {
            // Query the transaction with the given ID and include contact and sell lines.
            $query = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->with(['contact', 'sell_lines', 'sell_lines.variations.product_variation', 'sell_lines.variations']);
            $sell = $query->firstOrFail();

            // Retrieve the business location associated with the transaction.
            $businessLocation = BusinessLocation::where('id', $sell->location_id)
                ->where('business_id', $business_id)
                ->firstOrFail();

            // Initialize client information.
            $client = (new Client())
                ->setVatNumber($sell->contact->tax_number)
                ->setStreetName($sell->contact->street_name)
                ->setBuildingNumber($sell->contact->building_number)
                ->setPlotIdentification($sell->contact->additional_number) // need to added
                ->setSubDivisionName($sell->contact->state)
                ->setCityName($sell->contact->city)
                ->setPostalNumber($sell->contact->zip_code)
                ->setCountryName($sell->contact->country ?? 'SA') // Ensure country name is always a string
                ->setClientName($sell->contact->name);

            // Decode the response and setting data from the business location.
            $responseData = json_decode($businessLocation->zatca_response, true);
            $setting = json_decode($businessLocation->zatca_details, true);

            // Initialize supplier information.
            $supplier = (new Supplier()) // all data here must be required
                ->setCrn($setting['crn'])
                ->setStreetName($setting['street_name'])
                ->setBuildingNumber($setting['building_number'])
                ->setPlotIdentification($setting['plot_identification'])
                ->setSubDivisionName($setting['sub_division_name'])
                ->setCityName($setting['city_name'])
                ->setPostalNumber($setting['postal_number'])
                ->setCountryName($setting['country_name'])
                ->setVatNumber($setting['vat_number'])
                ->setVatName($setting['vat_name']);

            // Initialize delivery information.
            $delivery = (new Delivery()) // invoice expected delievery date
                ->setDeliveryDateTime(date('Y-m-d', strtotime($sell->transaction_date)));

            // Initialize payment type.
            $paymentType = (new PaymentType()) // invoice payment type  : reference  : https://zatca.gov.sa/ar/E-Invoicing/SystemsDevelopers/Documents/20220624_ZATCA_Electronic_Invoice_XML_Implementation_Standard_vF.pdf , section : 11.2.5 Payment means type code
                ->setPaymentType('1');
            // Retrieve the last hash by location.
            $pih = $this->getLastHashByLocation($sell->location_id, $business_id);

            // Initialize previous hash.
            $previous_hash = (new PIH())
                ->setPIH($pih); // note this value it from step 3 , 4

            $icv = $this->getNextDocumentIcv($sell->location_id, $business_id);

            // Initialize additional document reference.
            $additionalDocumentReference = (new AdditionalDocumentReference())
                ->setInvoiceID($icv); // note this value it from step 1 invoice counter value icv

            // Initialize variables for calculation.
            $sub_total = 0;
            $tax_amount = 0;
            $discountTotal = 0;
            $invoiceLines = [];
            $taxSubtotals = [];

            // Initialize allowance charge array.
            $allowanceCharge = [];

            // Process each sell line.
            foreach ($sell->sell_lines as $index => $line) {
                try {
                    // Retrieve product name.
                    $product = Product::findOrFail($line->product_id);

                    $product_name = $product->name;

                    if ($product->type == 'variable') {
                        $variation_name = $line->variations->product_variation->name ?? '';
                        $variation_value = $line->variations->name ?? '';
                        $product_name .= " - {$variation_name} - {$variation_value}";
                    }
                    $sub_sku = $line->variations->sub_sku ?? '';
                    $brand = $product->brand->name ?? '';
                    $product_name .= !empty($sub_sku) ? ", {$sub_sku}" : '';
                    $product_name .= !empty($brand) ? ", {$brand}" : '';

                    // Calculate line discount total.
                    $line_discount_total = round(($line->line_discount_amount ?? 0), 2); // 20

                    // Calculate line subtotal.
                    $line_subtotal = round(($line->unit_price * $line->quantity), 2); // 100 * 2 = 200
                    // Calculate line tax total.
                    $line_tax_total = round(($line->item_tax * $line->quantity), 2); // 15 * 2 = 30
                    // Calculate line net total.
                    $line_net_total = round($line->unit_price_inc_tax, 2); // 200 + 30 - 20 = 210

                    // Update total calculations.
                    $sub_total = round($sub_total + $line_subtotal, 2);
                    $tax_amount = round($tax_amount + $line_tax_total, 2);
                    $discountTotal = round($discountTotal + $line_discount_total, 2);

                    // Calculate tax percentage.
                    $taxPercentage = round(TaxRate::find($line->tax_id)->amount ?? 0, 2);

                    $taxcategory = $this->getChargeTaxCategory($taxPercentage);

                    // Update tax subtotals.
                    if (!isset($taxSubtotals[$taxPercentage])) {
                        $taxSubtotals[$taxPercentage] = ['taxable' => 0, 'tax' => 0, 'taxcategory' => ''];
                    }

                    $taxSubtotals[$taxPercentage]['taxable'] = round($taxSubtotals[$taxPercentage]['taxable'] + ($line_subtotal - $line_discount_total), 2);

                    $taxSubtotals[$taxPercentage]['tax'] = round($taxSubtotals[$taxPercentage]['tax'] + $line_tax_total, 2);

                    $taxSubtotals[$taxPercentage]['taxcategory'] = $taxcategory;

                    // Create invoice line.
                    $invoiceLines[] = (new InvoiceLine())
                        ->setLineID($line->id)
                        ->setLineName($product_name)
                        ->setLineCurrency('SAR')
                        ->setLinePrice(round($line->unit_price, 2)) // 100
                        ->setLineQuantity(round($line->quantity, 2)) // 2
                        ->setLineSubTotal(round($line_subtotal, 2)) // 200
                        ->setLineTaxTotal(round($tax_amount, 2)) // 30
                        ->setLineNetTotal(round($line_subtotal + $tax_amount, 2)) // 210
                        ->setLineTaxCategories((new LineTaxCategory())
                            ->setTaxCategory($taxcategory)
                            ->setTaxPercentage($taxPercentage) // 15%
                            ->getElement())
                        ->setLineDiscountReason('Applied Discount')
                        ->setLineDiscountAmount(round($line_discount_total, 2)) // 20
                        ->getElement();

                    // Create allowance charge.
                    $allowanceCharge[] = (new AllowanceCharge())
                        ->setAllowanceChargeCurrency('SAR')
                        ->setAllowanceChargeIndex(1 + $index)
                        ->setAllowanceChargeAmount(round($line_discount_total, 2)) // 20
                        ->setAllowanceChargeTaxCategory($taxcategory)
                        ->setAllowanceChargeTaxPercentage($taxPercentage)
                        ->getElement();
                } catch (\Exception $e) {
                    Log::error(__('zatcaintegrationksa::lang.failed_to_process_sell_line', ['error' => $e->getMessage()]));
                    return [
                        'success' => 0,
                        'msg' => __('zatcaintegrationksa::lang.failed_to_process_sell_line', ['error' => $e->getMessage()]),
                    ];
                }
            }

            // Prepare tax subtotal elements.
            $taxSubtotalElements = [];
            foreach ($taxSubtotals as $percentage => $values) {
                try {
                    $taxSubtotalElements[] = (new TaxSubtotal())
                        ->setTaxCurrencyCode('SAR')
                        ->setTaxableAmount(round($values['taxable'], 2)) // 100 * 2 = 200
                        ->setTaxAmount(round($values['tax'], 2)) // 30
                        ->setTaxCategory($values['taxcategory'])
                        ->setTaxPercentage(round($percentage, 2)) // 15%
                        ->getElement();
                } catch (\Exception $e) {
                    Log::error(__('zatcaintegrationksa::lang.failed_to_process_tax_subtotal', ['error' => $e->getMessage()]));
                    return [
                        'success' => 0,
                        'msg' => __('zatcaintegrationksa::lang.failed_to_process_tax_subtotal', ['error' => $e->getMessage()]),
                    ];
                }
            }

            // Initialize taxes total.
            $taxesTotal = (new TaxesTotal())
                ->setTaxCurrencyCode('SAR')
                ->setTaxTotal(round($tax_amount, 2)); // 30

            // Initialize legal monetary total.
            $legalMonetaryTotal = (new LegalMonetaryTotal())
                ->setTotalCurrency('SAR')
                ->setLineExtensionAmount(round($sub_total, 2)) //  200
                ->setTaxExclusiveAmount(round($sub_total - $discountTotal, 2)) // 200 - 20
                ->setTaxInclusiveAmount(round($sub_total + $tax_amount - $discountTotal, 2)) // 200 + 30 - 20 = 210
                ->setAllowanceTotalAmount(round($discountTotal, 2)) // 20
                ->setPrepaidAmount(0)
                ->setPayableAmount(round($sub_total + $tax_amount - $discountTotal, 2)); // 200 + 30 - 20 = 210

            // Generate a unique identifier for the document.
            $uuid = (string) Str::orderedUuid();

            // Determine if the document should be sent to ZATCA.
            $document = false;
            $certificateEncoded = $responseData['data']['complianceCertificate'];
            $privateKeyEncoded = $responseData['data']['privateKey'];
            $certificateSecret = $responseData['data']['complianceSecret'];
            if ($setting['portal_mode'] != 'developer-portal') {
                $certificateEncoded = $responseData['data']['productionCertificate'];
                $privateKeyEncoded = $responseData['data']['privateKey'];
                $certificateSecret = $responseData['data']['productionCertificateSecret'];
                $document = true;
            }

            // Generate the invoice.
            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting['portal_mode'])
                ->setZatcaLang('en')
                ->setInvoiceNumber($sell->invoice_no)
                ->setInvoiceUuid($uuid)
                ->setInvoiceIssueDate(date('Y-m-d', strtotime($sell->transaction_date)))
                ->setInvoiceIssueTime(date('H:i:s', strtotime($sell->transaction_date)))
                ->setInvoiceType('0200000', '388') // 0200000 simplified (B2C) , 0100000 standard (B2B)
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceSupplier($supplier)
                ->setInvoiceClient($client)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal(...$taxSubtotalElements) // Adding both tax subtotals
                ->setInvoiceAllowanceCharges(...$allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($certificateEncoded)
                ->setPrivateKeyEncoded($privateKeyEncoded)
                ->setCertificateSecret($certificateSecret)
                ->sendDocument($document); // false for developer portal, true otherwise

            // Log the invoice generation response.
            ZatcaDocument::create([
                'icv' => $icv, // Replace with actual ICV value
                'uuid' => $uuid,
                'hash' => $response['hash'] ?? null,
                'xml' => $response['xml'] ?? null,
                'sent_to_zatca' => $response['success'] ?? false,
                'sent_to_zatca_status' => $response['success'] ? 'success' : 'failed',
                'signing_time' => $response['signing_time'] ?? null,
                'response' => json_encode($response ?? null), // Convert response to JSON
                'type' => 'sale',
                'transaction_id' => $sell->id, // Replace with actual transaction ID
                'location_id' => $sell->location_id,
                'business_id' => $sell->business_id,
                'portal_mode' => $setting['portal_mode'],
            ]);

            $status = $response['success'] ? 'success' : 'failed';

            $sell->zatca_status = $status;

            $sell->save();

            if (!$response['success']) {
                return [
                    'success' => 0,
                    'msg' => __('zatcaintegrationksa::lang.failed_to_sync_zatca_sale', ['error' => $e->getMessage()]),
                ];
            }

            return [
                'success' => 1,
            ];
        } catch (\Exception $e) {
            Log::error(__('zatcaintegrationksa::lang.failed_to_process_sale', ['error' => $e->getMessage()]));
            return [
                'success' => 0,
                'msg' => __('zatcaintegrationksa::lang.failed_to_process_sale', ['error' => $e->getMessage()]),
            ];
        }
    }

    public function sycs_sale_return($id)
    {

        try {
            \DB::beginTransaction();

            // Retrieve the business ID from the session.
            $business_id = request()->session()->get('user.business_id');

            $sell_return = Transaction::where('business_id', $business_id)
                ->where('id', $id)->firstOrFail();

            $sell = Transaction::where('business_id', $business_id)
                ->where('id', $sell_return->return_parent_id)->firstOrFail();

            // Ensure the parent sale is synced successfully before proceeding with the return
            if ($sell->zatca_status != 'success') {
                $output = [
                    'success' => 0,
                    'msg' => __('zatcaintegrationksa::lang.parent_sale_not_synced'),
                ];

                return $output;
            }

            // Retrieve the business location associated with the transaction.
            $businessLocation = BusinessLocation::where('id', $sell->location_id)
                ->where('business_id', $business_id)
                ->whereNotNull('zatca_response')
                ->whereNotNull('zatca_details')
                ->first();
            if (!$businessLocation) {
                return [
                    'success' => 0,
                    'msg' => __('zatcaintegrationksa::lang.missing_zatca_details'),
                ];
            }

            $response = $this->sync_zatca_sale_return($id, $business_id);


            if (!$response['success']) {
                $output = [
                    'success' => 0,
                    'msg' => $response['msg'],
                ];
                return $output;
            }

            \DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return $output;
        } catch (\Exception $e) {
            \DB::rollBack();
            // Log the error or handle it as needed.
            Log::error(__('zatcaintegrationksa::lang.failed_to_process_sale', ['error' => $e->getMessage()]));
            return [
                'success' => 0,
                'msg' => __('zatcaintegrationksa::lang.failed_to_process_sale', ['error' => $e->getMessage()]),
            ];

            return $output;
        }
    }

    public function sync_zatca_sale_return($id, $business_id)
    {
        try {
            // Query the transaction with the given ID and include contact and sell lines.
            $sell_return = Transaction::where('business_id', $business_id)
                ->where('id', $id)->firstOrFail();

            $query = Transaction::where('business_id', $business_id)
                ->where('id', $sell_return->return_parent_id)
                ->with(['contact', 'sell_lines', 'sell_lines.variations.product_variation', 'sell_lines.variations']);
            $sell = $query->firstOrFail();

            // Retrieve the business location associated with the transaction.
            $businessLocation = BusinessLocation::where('id', $sell->location_id)
                ->where('business_id', $business_id)
                ->firstOrFail();

            // Initialize client information.
            $client = (new Client())
                ->setVatNumber($sell->contact->tax_number)
                ->setStreetName($sell->contact->street_name)
                ->setBuildingNumber($sell->contact->building_number)
                ->setPlotIdentification($sell->contact->additional_number) // need to added
                ->setSubDivisionName($sell->contact->state)
                ->setCityName($sell->contact->city)
                ->setPostalNumber($sell->contact->zip_code)
                ->setCountryName($sell->contact->country ?? 'SA')
                ->setClientName($sell->contact->name);

            // Decode the response and setting data from the business location.
            $responseData = json_decode($businessLocation->zatca_response, true);
            $setting = json_decode($businessLocation->zatca_details, true);

            // Initialize supplier information.
            $supplier = (new Supplier()) // all data here must be required
                ->setCrn($setting['crn'])
                ->setStreetName($setting['street_name'])
                ->setBuildingNumber($setting['building_number'])
                ->setPlotIdentification($setting['plot_identification'])
                ->setSubDivisionName($setting['sub_division_name'])
                ->setCityName($setting['city_name'])
                ->setPostalNumber($setting['postal_number'])
                ->setCountryName($setting['country_name'])
                ->setVatNumber($setting['vat_number'])
                ->setVatName($setting['vat_name']);

            // Initialize delivery information.
            $delivery = (new Delivery()) // invoice expected delievery date
                ->setDeliveryDateTime(date('Y-m-d', strtotime($sell_return->transaction_date)));

            // Initialize payment type.
            $paymentType = (new PaymentType()) // invoice payment type  : reference  : https://zatca.gov.sa/ar/E-Invoicing/SystemsDevelopers/Documents/20220624_ZATCA_Electronic_Invoice_XML_Implementation_Standard_vF.pdf , section : 11.2.5 Payment means type code
                ->setPaymentType('1');
            // Retrieve the last hash by location.
            $pih = $this->getLastHashByLocation($sell->location_id, $business_id);

            $returnReason = (new ReturnReason()) // invoice return reason if invoice credit note or debit note
                ->setReturnReason('SET_RETURN_REASON');

            $billingReference = (new BillingReference())
                ->setBillingReference($sell->id); // need to discussed

            // Initialize previous hash.
            $previous_hash = (new PIH())
                ->setPIH($pih);

            $icv = $this->getNextDocumentIcv($sell->location_id, $business_id);

            // Initialize additional document reference.
            $additionalDocumentReference = (new AdditionalDocumentReference())
                ->setInvoiceID($icv);

            // Initialize variables for calculation.
            $sub_total = 0;
            $tax_amount = 0;
            $discountTotal = 0;
            $invoiceLines = [];
            $taxSubtotals = [];

            // Initialize allowance charge array.
            $allowanceCharge = [];

            // Process each sell line.
            foreach ($sell->sell_lines as $index => $line) {
                try {
                    if ($line->quantity_returned != 0) {
                        // Retrieve product name.
                        $product = Product::findOrFail($line->product_id);

                        $product_name = $product->name;

                        if ($product->type == 'variable') {
                            $variation_name = $line->variations->product_variation->name ?? '';
                            $variation_value = $line->variations->name ?? '';
                            $product_name .= " - {$variation_name} - {$variation_value}";
                        }
                        $sub_sku = $line->variations->sub_sku ?? '';
                        $brand = $product->brand->name ?? '';
                        $product_name .= !empty($sub_sku) ? ", {$sub_sku}" : '';
                        $product_name .= !empty($brand) ? ", {$brand}" : '';

                        // Calculate line discount total.
                        $line_discount_total = round(($line->line_discount_amount ?? 0), 2); // 20

                        // Calculate line subtotal.
                        $line_subtotal = round(($line->unit_price * $line->quantity_returned), 2); // 100 * 2 = 200
                        // Calculate line tax total.
                        $line_tax_total = round(($line->item_tax * $line->quantity_returned), 2); // 15 * 2 = 30
                        // Calculate line net total.
                        $line_net_total = round($line->unit_price_inc_tax, 2); // 200 + 30 - 20 = 210
                        // Update total calculations.
                        $sub_total = round($sub_total + $line_subtotal, 2);
                        $tax_amount = round($tax_amount + $line_tax_total, 2);
                        $discountTotal = round($discountTotal + $line_discount_total, 2);
                        // Calculate tax percentage.
                        $taxPercentage = round(TaxRate::find($line->tax_id)->amount ?? 0, 2);

                        $taxcategory = $this->getChargeTaxCategory($taxPercentage);
                        // Update tax subtotals.
                        if (!isset($taxSubtotals[$taxPercentage])) {
                            $taxSubtotals[$taxPercentage] = ['taxable' => 0, 'tax' => 0, 'taxcategory' => ''];
                        }

                        $taxSubtotals[$taxPercentage]['taxable'] = round($taxSubtotals[$taxPercentage]['taxable'] + ($line_subtotal - $line_discount_total), 2);

                        $taxSubtotals[$taxPercentage]['tax'] = round($taxSubtotals[$taxPercentage]['tax'] + $line_tax_total, 2);

                        $taxSubtotals[$taxPercentage]['taxcategory'] = $taxcategory;

                        // Create invoice line.
                        $invoiceLines[] = (new InvoiceLine())
                            ->setLineID($line->id)
                            ->setLineName($product_name)
                            ->setLineCurrency('SAR')
                            ->setLinePrice(round($line->unit_price, 2)) // 100
                            ->setLineQuantity(round($line->quantity_returned, 2)) // 2
                            ->setLineSubTotal(round($line_subtotal, 2)) // 200
                            ->setLineTaxTotal(round($tax_amount, 2)) // 30
                            ->setLineNetTotal(round($line_subtotal + $tax_amount, 2)) // 210
                            ->setLineTaxCategories((new LineTaxCategory())
                                ->setTaxCategory($taxcategory)
                                ->setTaxPercentage($taxPercentage) // 15%
                                ->getElement())
                            ->setLineDiscountReason('Applied Discount')
                            ->setLineDiscountAmount(round($line_discount_total, 2)) // 20
                            ->getElement();

                        // Create allowance charge.
                        $allowanceCharge[] = (new AllowanceCharge())
                            ->setAllowanceChargeCurrency('SAR')
                            ->setAllowanceChargeIndex(1 + $index)
                            ->setAllowanceChargeAmount(round($line_discount_total, 2)) // 20
                            ->setAllowanceChargeTaxCategory($taxcategory)
                            ->setAllowanceChargeTaxPercentage($taxPercentage)
                            ->getElement();
                    }
                } catch (\Exception $e) {
                    Log::error(__('zatcaintegrationksa::lang.failed_to_process_sell_line', ['error' => $e->getMessage()]));
                    return [
                        'success' => 0,
                        'msg' => __('zatcaintegrationksa::lang.failed_to_process_sell_line', ['error' => $e->getMessage()]),
                    ];
                }
            }

            // Prepare tax subtotal elements.
            $taxSubtotalElements = [];
            foreach ($taxSubtotals as $percentage => $values) {
                try {
                    $taxSubtotalElements[] = (new TaxSubtotal())
                        ->setTaxCurrencyCode('SAR')
                        ->setTaxableAmount(round($values['taxable'], 2)) // 100 * 2 = 200
                        ->setTaxAmount(round($values['tax'], 2)) // 30
                        ->setTaxCategory($values['taxcategory'])
                        ->setTaxPercentage(round($percentage, 2)) // 15%
                        ->getElement();
                } catch (\Exception $e) {
                    Log::error(__('zatcaintegrationksa::lang.failed_to_process_tax_subtotal', ['error' => $e->getMessage()]));
                    return [
                        'success' => 0,
                        'msg' => __('zatcaintegrationksa::lang.failed_to_process_tax_subtotal', ['error' => $e->getMessage()]),
                    ];
                }
            }

            // Initialize taxes total.
            $taxesTotal = (new TaxesTotal())
                ->setTaxCurrencyCode('SAR')
                ->setTaxTotal(round($tax_amount, 2)); // 30

            // Initialize legal monetary total.
            $legalMonetaryTotal = (new LegalMonetaryTotal())
                ->setTotalCurrency('SAR')
                ->setLineExtensionAmount(round($sub_total, 2)) //  200
                ->setTaxExclusiveAmount(round($sub_total - $discountTotal, 2)) // 200 - 20
                ->setTaxInclusiveAmount(round($sub_total + $tax_amount - $discountTotal, 2)) // 200 + 30 - 20 = 210
                ->setAllowanceTotalAmount(round($discountTotal, 2)) // 20
                ->setPrepaidAmount(0)
                ->setPayableAmount(round($sub_total + $tax_amount - $discountTotal, 2)); // 200 + 30 - 20 = 210

            // Generate a unique identifier for the document.
            $uuid = (string) Str::orderedUuid();

            // Determine if the document should be sent to ZATCA.
            $document = false;
            $certificateEncoded = $responseData['data']['complianceCertificate'];
            $privateKeyEncoded = $responseData['data']['privateKey'];
            $certificateSecret = $responseData['data']['complianceSecret'];
            if ($setting['portal_mode'] != 'developer-portal') {
                $certificateEncoded = $responseData['data']['productionCertificate'];
                $privateKeyEncoded = $responseData['data']['privateKey'];
                $certificateSecret = $responseData['data']['productionCertificateSecret'];
                $document = true;
            }

            // Generate the invoice.
            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting['portal_mode'])
                ->setZatcaLang('en')
                ->setInvoiceNumber($sell_return->invoice_no)
                ->setInvoiceUuid($uuid)
                ->setInvoiceIssueDate(date('Y-m-d', strtotime($sell_return->transaction_date)))
                ->setInvoiceIssueTime(date('H:i:s', strtotime($sell_return->transaction_date)))
                ->setInvoiceType('0200000', '381')
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceSupplier($supplier)
                ->setInvoiceClient($client)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                ->setInvoiceReturnReason($returnReason)
                ->setInvoiceBillingReference($billingReference)
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal(...$taxSubtotalElements) // Adding both tax subtotals
                ->setInvoiceAllowanceCharges(...$allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($certificateEncoded)
                ->setPrivateKeyEncoded($privateKeyEncoded)
                ->setCertificateSecret($certificateSecret)
                ->sendDocument($document); // false for developer portal, true otherwise

            // Log the invoice generation response.
            ZatcaDocument::create([
                'icv' => $icv, // Replace with actual ICV value
                'uuid' => $uuid,
                'hash' => $response['hash'] ?? null,
                'xml' => $response['xml'] ?? null,
                'sent_to_zatca' => $response['success'] ?? false,
                'sent_to_zatca_status' => $response['success'] ? 'success' : 'failed',
                'signing_time' => $response['signing_time'] ?? null,
                'response' => json_encode($response ?? null), // Convert response to JSON
                'type' => 'sale-return',
                'transaction_id' => $sell_return->id, // Replace with actual transaction ID
                'location_id' => $sell->location_id,
                'business_id' => $sell->business_id,
                'portal_mode' => $setting['portal_mode'],
            ]);

            $status = $response['success'] ? 'success' : 'failed';

            $sell_return->zatca_status = $status;

            $sell_return->save();

            if (!$response['success']) {
                return [
                    'success' => 0,
                    'msg' => __('zatcaintegrationksa::lang.failed_to_sync_zatca_sale', ['error' => $e->getMessage()]),
                ];
            }

            return [
                'success' => 1,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process sale for ZATCA integration: ' . $e->getMessage());
            return [
                'success' => 0,
                'msg' => __('zatcaintegrationksa::lang.failed_to_process_sale', ['error' => $e->getMessage()]),
            ];
        }
    }

    public function getChargeTaxCategory($vatPercent)
    {
        $taxCategories = [
            0 => 'Z', // Zero-rated VAT
            5 => 'S', // Standard-rated VAT (5%)
            15 => 'S', // Standard-rated VAT (15%)
        ];

        if (!array_key_exists($vatPercent, $taxCategories)) {
            throw new InvalidArgumentException("Invalid VAT percentage: $vatPercent");
        }

        return $taxCategories[$vatPercent];
    }

    public function getNextDocumentIcv($locationId, $business_id)
    {
        $lastIcv = ZatcaDocument::where('location_id', $locationId)->where('business_id', $business_id)->orderBy('id', 'desc')->value('icv');

        return $lastIcv ? $lastIcv + 1 : 1;
    }

    public function getLastHashByLocation($locationId, $business_id)
    {
        // Use the latest() method to order by the 'created_at' column by default.
        // The value() method directly returns the 'hash' field or null if not found.
        $hash = ZatcaDocument::where('location_id', $locationId)

            ->where('sent_to_zatca_status', 'success')
            ->where('business_id', $business_id)
            ->latest()
            ->value('hash');
        // Return the hash if it exists, otherwise return false.
        return $hash !== null ? $hash : base64_encode(hash('sha256', '0', true));
    }

    public function DeleteTestingInvoice()
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            // Query ZatcaDocument for simulation and developer-portal portal_modes
            $query = ZatcaDocument::where('business_id', $business_id)->whereIn('portal_mode', ['simulation', 'developer-portal']);

            // Retrieve transaction IDs from the query
            $transaction_ids = $query->pluck('transaction_id');

            // Update transactions with the retrieved IDs to set zatca_status to null
            Transaction::whereIn('id', $transaction_ids)->where('business_id', $business_id)->update([
                'zatca_status' => null,
            ]);

            // Delete the documents from the query
            $query->delete();

            return redirect()->back()->with('status', [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ]);
        } catch (\Exception $e) {
            // Log the error if an exception occurs during the deletion process
            \Log::error('Error deleting testing invoice: ' . $e->getMessage());

            return redirect()->back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function downloadXml($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $document = ZatcaDocument::where('transaction_id', $id)->where('business_id', $business_id)->firstOrFail();

        $response = json_decode($document->response);
        $decodedData = base64_decode($response->xml);

        return response()->streamDownload(function () use ($decodedData) {
            echo $decodedData;
        }, 'zatca_xml_' . $document->icv . '.xml');
    }

    public function showInvoiceError($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $document = ZatcaDocument::where('transaction_id', $id)->where('business_id', $business_id)->orderBy('created_at', 'desc')->first();

            $response = json_decode($document->response);

            $errors = $response->response->validationResults->errorMessages;

            return view('zatcaintegrationksa::sale.error', compact('errors'));
        } catch (\Exception $e) {
            \Log::error('Error showing invoice error: ' . $e->getMessage());
            return redirect()->back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }
}
