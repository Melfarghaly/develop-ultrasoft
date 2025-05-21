<!-- Row for Cost Center -->
<tr class="cost-center level-{{ $level }}">
    <td colspan="3">
        {{ str_repeat('— ', $level) }}📂 {{ $center->name }}
    </td>
    <td></td> 
    <td><strong>{{ number_format($center->totalExpenses(), 2) }}</strong></td>
    <td><strong>{{ number_format($center->totalRevenues(), 2) }}</strong></td>
    <td class="{{ $center->totalProfit() < 0 ? 'negative' : '' }}">
        <strong>{{ number_format($center->totalProfit(), 2) }}</strong>
    </td>
    <td></td>
</tr>
@php 

$transactions =  Modules\Accounting\Entities\AccountingAccountsTransaction::where('cost_center_id', $center->id)
            ->whereHas('map', function($query) use ($start_date, $end_date) {
                $query->whereBetween('operation_date', [$start_date, $end_date]);
            })
          
            ->get();
          
@endphp
<!-- Rows for Transactions -->
@foreach($transactions as $transaction)



<tr>
    <td></td>
    <td>{{ $transaction->map->ref_no ?? '' }}</td>
    <td>{{ $transaction->note ?? ''  }}</td>
    <td>{{ $transaction->account->name }}</td>
    <td>
        @if($transaction->type=='debit')
            {{ $transaction->amount }}
        @endif
    </td>
    <td>
        @if($transaction->type=='credit')
            {{ $transaction->amount }}
        @endif
    </td>
    <td></td>
    <td>{{ \Carbon\Carbon::parse($transaction->map->operation_date)->format('d/m/Y') }}</td>
</tr>

@endforeach

<!-- Recursive Call for Children -->
@foreach($center->children as $child)
    @include('cost_center.partials.ledger_row', ['center' => $child, 'level' => $level + 1,'total_debit'=>$total_debit,'total_credit'=>$total_credit])
@endforeach
