@php 
          $business=\App\Business::find(session('business.id'));
          $banks =\DB::table('accounting_accounts')->where('business_id',$business->id)->where('detail_type_id',30)->pluck('name','id');

          @endphp
          
              <div class="col-md-6 bank-selection">
			  <div class="form-group">
                <label for="cash_drawer" class="form-label">البنك  </label>
                <select id="bank_name" name="payment[{{ $row_index }}][bank_account_id]"
                    class="form-control">
                    @foreach($banks as $id => $name )
                        <option value="{{ $id }}" >{{$name}}</option>
                    @endforeach                                     
                </select>
				</div>
              </div>
              @php 
          $business=\App\Business::find(session('business.id'));
          $cashAccounts =\DB::table('accounting_accounts')->where('business_id',$business->id)->where('parent_account_id',$business->parent_bank_account_id)->pluck('name','id');

          @endphp
          
              <div class="col-md-6 cash-account-selection">
				<div class="form-group">
                <label for="cash_drawer" class="form-label">الخزينة  </label>
					<select id="" name="payment[{{$row_index}}][cash_account_id]"
						class="form-control ">
						@foreach($cashAccounts as $id => $name )
							<option value="{{ $id }}" >{{$name}}</option>
						@endforeach                                     
					</select>
				</div>
              </div>