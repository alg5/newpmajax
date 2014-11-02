(function ($) {


    $().ready(function () {
        // 
        $('#pmheader-postingbox').find('input[name=add_to]').on('click', function (e) {
            e.preventDefault();
            var data_to_send = 'username_list=' + $('#username_list').val();
            if ($('#group_list :selected').length > 0) {
                data_to_send = data_to_send + '&' + $('#group_list ').serialize();
            }
            if ($('input[name^=address_list]').length > 0) {
                data_to_send = data_to_send + '&' + $('input[name^=address_list]').serialize();
            }
            //console.log($('.address_list'));
            //console.log($('input[name^=address_list]'));
            var path = U_NEWPMAJAX_PATH + 'add_to/0/0';
            // alert(data_to_send);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data_to_send,
                url: path,
                success: function (data) {
                    //alert('3');
                    console.log(data);
                    //togle_thanks(data);
                }
            });

        });



    }); //$().ready(function ()





})(jQuery);                                                                             // Avoid conflicts with other libraries


