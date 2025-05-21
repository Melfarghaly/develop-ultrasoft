@php
$costCenters = \DB::table('cost_centers')
    ->where('business_id',Auth()->user()->business_id)
   
    ->where('is_last_record',1)
    ->get();
    if(!empty($selected_cost_center)){
        
        $selected_cost_center_name = \DB::table('cost_centers')->where('id',$selected_cost_center)->value('name');
    }else{
        $selected_cost_center_name = '';
        $selected_cost_center='';
    }
@endphp
<div class="form-group">
    <label for="cost_center_id">مركز التكلفة</label>
    <select class="form-control select2" id="cost_center_id" name="cost_center_id">
        <option value="">لا يوجد</option>
        
        @foreach($costCenters as $center)
            <option value="{{ $center->id }}" @if($selected_cost_center==$center->id) selected @endif>{{ $center->name }}</option>
        @endforeach
    </select>
</div>