@extends('layouts.app')
@section('title', __('stock_adjustment.edit'))

@section('content')
    <style>
        .invalid-feedback {
            color: red;
        }
    </style>
    <!-- Main content -->
    <section class="content no-print">
    {!! Form::model($check, [
            'route' => ['checks.update', $check->id],
            'method' => 'put',
            'id' => 'edit_check_form',
        ]) !!}

    @component('components.widget', ['class' => 'box-solid'])
        <div class="container mt-5">
            <h1 class="mb-4 text-center">تعديل شيك وارد</h1>

            @if (session('success'))
                <div id="success-message" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row mb-3">
               

                <div class="col-md-4 mb-2">
                    <label for="account_id" class="form-label">اسم الحساب</label>
                    <select id="account_id" name="account_id"
                        class="form-control accounts-dropdown @error('account_id') is-invalid @enderror">
                       <option value="{{$check->account->id ?? '' }}" selected>{{$check->account->name ?? '' }}</option>
                    </select>
                    @error('account_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-4 mb-2">
                    <label for="cost_center" class="form-label">مركز التكلفة</label>
                    <input type="text" id="cost_center" name="cost_center"
                        class="form-control @error('cost_center') is-invalid @enderror" 
                        value="{{ old('cost_center', $check->cost_center) }}">
                    @error('cost_center')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <label for="check_number" class="form-label">رقم الشيك</label>
                    <input type="text" id="check_number" name="check_number"
                        class="form-control @error('check_number') is-invalid @enderror" 
                        value="{{ old('check_number', $check->check_number) }}">
                    @error('check_number')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                @php 
                   $business=\App\Business::find(session('business.id'));
                   $banks =\DB::table('accounting_accounts')->where('business_id',$business->id)->where('detail_type_id',30)->pluck('name','id');
              
                   @endphp
                <div class="col-md-6 mb-2">
                    <label for="bank" class="form-label">البنك</label>
                    <select id="bank" name="bank_id" 
                        class="form-control ">
                        @foreach($banks as $id => $name)
                            <option value="{{ $id }}" {{  $check->bank_id == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('bank')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <label for="issue_date" class="form-label">تاريخ التحرير</label>
                    <input type="text" id="issue_date" name="issue_date"
                        class="form-control @error('issue_date') is-invalid @enderror datepicker"
                        value="{{  @format_date($check->issue_date) }}">
                    @error('issue_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-6 mb-2">
                    <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
                    <input type="text" id="due_date" name="due_date"
                        class="form-control @error('due_date') is-invalid @enderror datepicker"
                        value="{{  @format_date($check->due_date) }}">
                    @error('due_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <div class="col-md-6 mb-2">
                    <label for="check_value" class="form-label">قيمة الشيك</label>
                    <input type="number" step="0.01" id="check_value" name="check_value"
                        class="form-control @error('check_value') is-invalid @enderror"
                        value="{{ old('check_value', $check->check_value) }}">
                    @error('check_value')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-6 mb-2">
                    <label for="currency" class="form-label">العملة</label>
                    <select id="currency" name="currency" 
                        class="form-control select2 @error('currency') is-invalid @enderror">
                        <option value="{{ session('currency')['id'] }}">{{ session('currency')['code'] }}</option>
                        @foreach ($currencies as $id => $name)
                            <option value="{{ $id }}" {{ old('currency', $check->currency) == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 mb-2">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea id="notes" name="notes" 
                        class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $check->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="text-center m-5">
                <button type="submit" class="btn btn-primary btn-custom">تحديث</button>
            </div>
        </div>
    @endcomponent
    {!! Form::close() !!}
</section>

@stop
@section('javascript')
    <script type="text/javascript">
        $(function() {

                        //// select accounts-dropdown////
                        function removeHtmlTags(str) {
                return str.replace(/<\/?[^>]+>/gi, '');
            }
            $("select.accounts-dropdown").select2({
                ajax: {
                    url: '/accounting/accounts-dropdown',
                    dataType: 'json',
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            })
                        };
                    }
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    return data.text;
                },
                templateSelection: function(data) {
                    return data.text;
                }

            }).on('select2:select', function(e) {
                var selectedAccountName = removeHtmlTags(e.params.data.text);
                $('#account_name').val(selectedAccountName);
            });

            var bindDatePicker = function() {
                $(".date").datetimepicker({
                    format: 'YYYY-MM-DD',
                    icons: {
                        time: "fa fa-clock-o",
                        date: "fa fa-calendar",
                        up: "fa fa-arrow-up",
                        down: "fa fa-arrow-down"
                    }
                }).find('input:first').on("blur", function() {
                    // check if the date is correct. We can accept dd-mm-yyyy and yyyy-mm-dd.
                    // update the format if it's yyyy-mm-dd
                    var date = parseDate($(this).val());

                    if (!isValidDate(date)) {
                        //create date based on momentjs (we have that)
                        date = moment().format('YYYY-MM-DD');
                    }

                    $(this).val(date);
                });
            }

            var isValidDate = function(value, format) {
                format = format || false;
                // lets parse the date to the best of our knowledge
                if (format) {
                    value = parseDate(value);
                }

                var timestamp = Date.parse(value);

                return isNaN(timestamp) == false;
            }

            var parseDate = function(value) {
                var m = value.match(/^(\d{1,2})(\/|-)?(\d{1,2})(\/|-)?(\d{4})$/);
                if (m)
                    value = m[5] + '-' + ("00" + m[3]).slice(-2) + '-' + ("00" + m[1]).slice(-2);

                return value;
            }

            bindDatePicker();

            // Automatically hide success message after 5 seconds
            setTimeout(function() {
                $('#success-message').fadeOut('slow');
            }, 4000);
        });
    </script>
@endsection