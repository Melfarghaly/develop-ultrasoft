<div class="modal fade" id="update_purchase_status_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">@lang('constructions::lang.update_certificate_status')</h4>
		</div>

		{!! Form::open(['url' => action([\Modules\Constructions\Http\Controllers\WorkCertificateController::class, 'updateStatus']), 'method' => 'post', 'id' => 'update_certificate_status_form']) !!}

		<div class="modal-body">
			<div class="form-group">
				{!! Form::label('status', __('constructions::lang.status') . ':*') !!}
				{!! Form::select('status', $statuses, $transaction->status, ['class' => 'form-control select2', 'required', 'style' => 'width: 100%;']); !!}
				
				{!! Form::hidden('certificate_id', $transaction->id) !!}
			</div>
		</div>

		<div class="modal-footer">
			<button type="submit" class="btn btn-primary">@lang('messages.update')</button>
			<button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
		</div>

		{!! Form::close() !!}

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>