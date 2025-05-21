@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

@include('accounting::layouts.nav')

<style>
        /* Printing optimization */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }

        /* Table styling */
        table {
            border: 1px solid #ddd;
            width: 100%;
        }

        th, td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }

        th {
            background-color:rgb(44, 45, 47);
        }

        .table-title {
            background-color: #f1f1f1;
            font-weight: bold;
        }
    </style>
<section class="content "style="background-color:white;">
<div class="container my-4">
        <div class="row">
            <!-- Header Section -->
            <div class="col-12 text-center">
                <h4 class="fw-bold">قيد يومية</h4>
              
            </div>
        </div>

        <!-- Journal Details -->
        <div class="row my-3">
            <div class="col-md-6">
                <p><strong>رقم القيد:</strong>{{$journal->ref_no}}</p>
                <p><strong>التاريخ:</strong> {{$journal->operation_date}}</p>
                <p><strong>ملاحظة:</strong> {{$journal->note}}</p>
            </div>
            
        </div>

        <!-- Table Section -->
        <div class="row">
            <div class="col-12">
                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الحساب</th>
                            <th>
                                مركز التكلفة
                            </th>
                            <th>
                                (مدين)
                            </th>
                            <th>
                                 (دائن)
                            </th>
                            <th>وصف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $total_debit = 0;
                            $total_credit = 0;
                            $transes=Modules\Accounting\Entities\AccountingAccountsTransaction::join('accounting_accounts as aa','aa.id','accounting_accounts_transactions.accounting_account_id')->where('acc_trans_mapping_id',$journal->id)->get();
                      
                            @endphp
                        <!-- Example Row -->
                        @foreach($transes as $trans)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$trans->name}}</td>
                            <td>{{$trans->cost_center->name ?? '' }}</td>
                            <td>
                                @if($trans->type=='debit')
                                {{$trans->amount}}
                                @php 
                                $total_debit += $trans->amount
                                @endphp 
                                @endif
                            </td>
                            <td>
                                @if($trans->type=='credit')
                                @php
                                $total_credit += $trans->amount;
                                @endphp
                                {{$trans->amount}}
                                @endif
                            </td>
                            <td>{{$trans->note ?? ''}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="table-title">Total</td>
                            <td>{{$total_debit}}</td>
                            <td>{{$total_credit}}</td>
                            <td></td>
                        </tr>
                </table>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="row mt-5">
            <div class="col-6">
                <p><strong>Prepared By:</strong> _________________________</p>
            </div>
            <div class="col-6 text-end">
                <p><strong>Approved By:</strong> _________________________</p>
            </div>
        </div>

        <!-- Print Button -->
        <div class="row my-4 no-print">
            <div class="col-12 text-center">
                <button class="btn btn-primary" onclick="window.print()">Print Journal</button>
            </div>
        </div>
    </div>
</section>
    @endsection