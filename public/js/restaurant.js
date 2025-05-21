$(document).ready(function() {
    //If location is set then show tables.
    //

    setTimeout(function(){
        var res_categories_ids = $('#res_categories_ids').val();
        if (res_categories_ids) {
            debugger;
            // Loop through each selected category ID
            for (var i = 0; i < res_categories_ids.length; i++) {
                var id = res_categories_ids[i];
                // Show elements with class corresponding to the category ID
                if (res_categories_ids.includes('all')) {
                    debugger;
                      $('*[class*="category_"]').removeClass('hide no-print');
                }
                $('.category_' + id).removeClass('hide');
                $('.category_' + id).removeClass('no-print');
            }
        }
    },1000);
   $('#sell_list_filter_date_range').daterangepicker(
        $.extend({}, dateRangeSettings, {
            startDate: moment().startOf('day'), // Set the start date to the beginning of today
            endDate: moment().endOf('day') // Set the end date to the end of today
        }),
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            refresh_orders();
        }
    );
    refresh_orders();
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        refresh_orders();
    });
    getLocationTables($('input#location_id').val());

    $('select#select_location_id').change(function() {
        var location_id = $(this).val();
        getLocationTables(location_id);
    });

    $(document).on('click', 'button.add_modifier', function() {
        var checkbox = $(this)
            .closest('div.modal-content')
            .find('input:checked');
        selected = [];
        checkbox.each(function() {
            selected.push($(this).val());
        });
        var index = $(this)
            .closest('div.modal-content')
            .find('input.index')
            .val();

        var quantity = __read_number($(this).closest('tr').find('input.pos_quantity'));
        add_selected_modifiers(selected, index, quantity);
    });
    $(document).on('click', '#refresh_orders', function() {
        refresh_orders();
    });

    //Auto refresh orders
    if ($('#refresh_orders').length > 0) {
        var refresh_interval = parseInt($('#__orders_refresh_interval').val()) * 1000;

        setInterval(function(){ 
            refresh_orders();
        }, refresh_interval);
    }
});

function getLocationTables(location_id) {
    var transaction_id = $('span#restaurant_module_span').data('transaction_id');

    if (location_id != '') {
        $.ajax({
            method: 'GET',
            url: '/modules/data/get-pos-details',
            data: { location_id: location_id, transaction_id: transaction_id },
            dataType: 'html',
            success: function(result) {
                $('span#restaurant_module_span').html(result);
                //REPAIR MODULE:set technician from repair module
                if ($("#repair_technician").length) {
                    $("select#res_waiter_id").val($("#repair_technician").val()).change();
                }
            },
        });
    }
}

function add_selected_modifiers(selected, index, quantity = 1) {
    if (selected.length > 0) {
        $.ajax({
            method: 'GET',
            url: $('button.add_modifier').data('url'),
            data: { selected: selected, index: index, quantity: quantity },
            dataType: 'html',
            success: function(result) {
                if (result != '') {
                    $('table#pos_table tbody')
                        .find('tr')
                        .each(function() {
                            if ($(this).data('row_index') == index) {
                                $(this)
                                    .find('td:first .selected_modifiers')
                                    .html(result);
                                return false;
                            }
                        });

                    //Update total price.
                    pos_total_row();
                }
            },
        });
    } else {
        $('table#pos_table tbody')
            .find('tr')
            .each(function() {
                if ($(this).data('row_index') == index) {
                    $(this)
                        .find('td:first .selected_modifiers')
                        .html('');
                    return false;
                }
            });

        //Update total price.
        pos_total_row();
    }
}
$('select#res_categories_ids').change(function(){
    debugger;
    refresh_orders();

});
$('select#order_status').change(function(){
    refresh_orders();
});
function refresh_orders() {
    $('.overlay').removeClass('hide');
    
    // Get the values from the form inputs
    var orders_for = $('input#orders_for').val();
    var res_categories_ids = $('select#res_categories_ids').val();
    var order_status = $('select#order_status').val();
    var service_staff_id = $('select#service_staff_id').val() || '';
    var start=null;
    var end=null;
    if($('#sell_list_filter_date_range').val()) {
         start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
         end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
        
    }
    // First AJAX request
    $.ajax({
        method: 'POST',
        url: '/modules/refresh-orders-list',
        data: {
            orders_for: orders_for,
            service_staff_id: service_staff_id,
            res_categories_ids: res_categories_ids,
            order_status: order_status,
            start_date:start,
            end_date:end
        },
        dataType: 'html',
        success: function(data) {
            $('#orders_div').html(data);
            $('.overlay').addClass('hide');

        },
        error: function(xhr, status, error) {
            console.error('Error refreshing orders list:', error);
            $('.overlay').addClass('hide');
        }
    });

    // Second AJAX request
    $.ajax({
        method: 'POST',
        url: '/modules/refresh-line-orders-list',
        data: {
            orders_for: orders_for,
            service_staff_id: service_staff_id,
            res_categories_ids: res_categories_ids,
            order_status: order_status
        },
        dataType: 'html',
        success: function(data) {
            $('#line_orders_div').html(data);
            $('.overlay').addClass('hide');
        },
        error: function(xhr, status, error) {
            console.error('Error refreshing line orders list:', error);
            $('.overlay').addClass('hide');
        }
    });
    setTimeout(function(){
        var res_categories_ids = $('#res_categories_ids').val();
        if (res_categories_ids) {
            debugger;
            // Loop through each selected category ID
            for (var i = 0; i < res_categories_ids.length; i++) {
                var id = res_categories_ids[i];
                if (res_categories_ids.includes('all')) {
                       $('*[class*="category_"]').removeClass('hide no-print');
                }
                // Show elements with class corresponding to the category ID
                $('.category_' + id).removeClass('hide');
                $('.category_' + id).removeClass('no-print');
            }
        }
    },1000);
    
    setTimeout(function(){
        var order_counter= $('.order_div').length;
        $('#order_counter').html(order_counter);
       
            var totalCookingTime = 0;
            var totalFinishedTime = 0;
            var cookingCount = 0;
            var finishedCount = 0;
        
            // Iterate through each cooking_time span and accumulate the values
            $('.cooking_time').each(function() {
                var value = parseInt($(this).data('value'));
                if (!isNaN(value) && value > 0) {
                    totalCookingTime += value;
                    cookingCount++;
                }
            });
        
            // Iterate through each finished_time span and accumulate the values
            $('.finished_time').each(function() {
                var value = parseInt($(this).data('value'));
                if (!isNaN(value) && value > 0) {
                    totalFinishedTime += value;
                    finishedCount++;
                }
            });
        
            // Calculate averages
            var averageCookingTime = cookingCount > 0 ? (totalCookingTime / cookingCount).toFixed(2) : 0;
            var averageFinishedTime = finishedCount > 0 ? (totalFinishedTime / finishedCount).toFixed(2) : 0;
        
            // Display the averages
            $('#cooking_time').text(averageCookingTime + ' دقيقة');
            $('#finished_time').text( averageFinishedTime + ' دقيقة');
   
        
    },1000);
}
