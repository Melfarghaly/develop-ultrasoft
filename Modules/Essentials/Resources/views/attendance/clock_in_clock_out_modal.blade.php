@php
$is_location_required=1;
@endphp
<div class="modal fade" id="clock_in_clock_out_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
	  <div class="modal-content">

	    {!! Form::open(['url' => action('\Modules\Essentials\Http\Controllers\AttendanceController@clockInClockOut'), 'method' => 'post', 'id' => 'clock_in_clock_out_form' ]) !!}
	    <div class="modal-header">
	      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      	<h4 class="modal-title"><span id="clock_in_text">@lang( 'essentials::lang.clock_in' )</span>
	      	<span id="clock_out_text">@lang( 'essentials::lang.clock_out' )</span></h4>
	    </div>
	    <div class="modal-body">
	    	<div class="row">
	    		<input type="hidden" name="type" id="type">
		      	<div class="form-group col-md-12">
		      		<strong>@lang( 'essentials::lang.ip_address' ): {{$ip_address}}</strong>
		      	</div>
		      	<div class="form-group col-md-12 clock_in_note @if(!empty($clock_in)) hide @endif">
		        	{!! Form::label('clock_in_note', __( 'essentials::lang.clock_in_note' ) . ':') !!}
		        	{!! Form::textarea('clock_in_note', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.clock_in_note'), 'rows' => 3 ]); !!}
		      	</div>
		      	<div class="form-group col-md-12 clock_out_note @if(empty($clock_in)) hide @endif">
		        	{!! Form::label('clock_out_note', __( 'essentials::lang.clock_out_note' ) . ':') !!}
		        	{!! Form::textarea('clock_out_note', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.clock_out_note'), 'rows' => 3 ]); !!}
		      	</div>
	    	</div>
	    </div>

	    <div class="modal-footer">
	      <button type="submit" class="btn btn-primary">@lang( 'messages.submit' )</button>
	      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>

	    {!! Form::close() !!}

	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
        	
</div>