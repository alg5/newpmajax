(function ($) {


    $().ready(function () {
        // 

        $('#pmheader-postingbox').on('click', 'input.button2[name^=remove]', function (e) {
            e.preventDefault();
            var option = $(this).parent();
            var arr = $(this).attr('name').split('[');
            var id = '[' + arr[1];
            var hidden = $('input[name^=address_list').filter(function (index) {
                return $(this).attr('name').indexOf(id) > -1;
            });
            $(hidden).remove();
            $(option).remove();
        });


        $('#pmheader-postingbox').find('input[name=add_to]').on('click', function (e) {
            e.preventDefault();
            var data_to_send = 'username_list=' + $('#username_list').val();
            if ($('#group_list :selected').length > 0) {
                data_to_send = data_to_send + '&' + $('#group_list ').serialize();
            }
            if ($('input[name^=address_list]').length > 0) {
                data_to_send = data_to_send + '&' + $('input[name^=address_list]').serialize();
            }
            data_to_send = $("#postform").serialize();
            var path = U_NEWPMAJAX_PATH + 'add_to';
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data_to_send,
                url: path,
                success: function (data) {
                    add_recipients_to(data);
                }
            });

        });
        $('#pmheader-postingbox').find('input[name=add_bcc]').on('click', function (e) {
            e.preventDefault();
            var data_to_send = 'username_list=' + $('#username_list').val();
            if ($('#group_list :selected').length > 0) {
                data_to_send = data_to_send + '&' + $('#group_list ').serialize();
            }
            if ($('input[name^=address_list]').length > 0) {
                data_to_send = data_to_send + '&' + $('input[name^=address_list]').serialize();
            }
            data_send = $("#postform").serialize();
            var path = U_NEWPMAJAX_PATH + 'add_bcc';
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data_send,
                url: path,
                 success: function (data) {
                   add_recipients_bcc(data);
                }
            });
        });
    }); //$().ready(function ()

    function add_recipients_to(data) {
        if (data.ERROR) {
            for (i = 0; i < data['ERROR'].length; i++) {
                output_info_new(data['ERROR'][i], 'error');
            }
            return;
        }
        if(data.MESSAGE !='')
        {
                 output_info_new(data.MESSAGE, 'warning');
       }

        $("#username_list").val('');
        $("#group_list option:selected").attr("selected", false);

       if(data.NUM_RECIPIENTS == 0) return;

        var lastclick = $("input[name=lastclick]");
        var recipients = $("#pmheader-postingbox div.column1").last();
        var recipients_list_exist = $(recipients).find("ul.recipients");
        if (recipients_list_exist.length == 0) {
            var divRecipients = $("#pmheader-postingbox div.column1").last();
            if ($("#pmheader-postingbox hr").length == 0)
            {
                $(divRecipients).before('<hr>');
            }
            var dl = '<dl>';
            dl = dl + '<dt>';
            dl = dl + '<label>' + L_RECIPIENTS + ':</label>';
            dl = dl + '</dt>';
            dl = dl + '<dd class="recipients">';
            dl = dl + '<ul class="recipients">';
            dl = dl + '</ul>';
            dl = dl + '</dd>';
            dl = dl + '</dl>';
            $(divRecipients).append(dl);
            recipients_list_exist = $(recipients).find("ul.recipients");

        }
        $.each(data.RECIPIENT_U_LIST, function (index, item) {
            var option = '<li><input type="submit" name="remove_u[' + item.UG_ID + ']" value="x" class="button2">' + item.NAME_FULL + '	</li>';
            $(recipients_list_exist).append(option);
            var hidden = '<input type="hidden" name="address_list[u][' + item.UG_ID + ']" value="to" />';
            $(lastclick).before(hidden);
        });
        $.each(data.RECIPIENT_G_LIST, function (index, item) {
            var option = '<li><input type="submit" name="remove_g[' + item.UG_ID + ']" value="x" class="button2">' + '<a href="' + item.U_VIEW + '"><strong>' + item.NAME + '</strong></a></li>';
            $(recipients_list_exist).append(option);
            var hidden = '<input type="hidden" name="address_list[g][' + item.UG_ID + ']" value="to" />';
            $(lastclick).before(hidden);
        });
    }
    function add_recipients_bcc(data) {
        if (data.ERROR) {
            for (i = 0; i < data['ERROR'].length; i++) {
                output_info_new(data['ERROR'][i], 'error');
            }
            return;
        }
        if(data.MESSAGE !='')
        {
                 output_info_new(data.MESSAGE, 'warning');
       }

        $("#username_list").val('');
        $("#group_list option:selected").attr("selected", false);

       if(data.NUM_RECIPIENTS == 0) return;

        var lastclick = $("input[name=lastclick]");
        
      
            var recipients_list_exist = $("#pmheader-postingbox div.column2").length >1;
            if (!recipients_list_exist) 
            {
                if ($("#pmheader-postingbox hr").length == 0)
                {
                  var divRecipientsTo = $("#pmheader-postingbox div.column1").last();
                  $(divRecipientsTo).before('<hr>');
                }
               var div_bcc = '<div class="column2">';
                var dl = '<dl>';
                dl = dl + '<dt>';
                dl = dl + '<label>' + L_BCC + ':</label>';
                dl = dl + '</dt>';
                dl = dl + '<dd class="recipients">';
                dl = dl + '<ul class="recipients">';
                dl = dl + '</ul>';
                dl = dl + '</dd>';
                dl = dl + '</dl>';
                div_bcc = div_bcc + dl + '</div>';
                $('#pmheader-postingbox fieldset.fields1').append(div_bcc);
        }
        var recipients = $("#pmheader-postingbox div.column2").last();

        var recipients_list_exist = $(recipients).find("ul.recipients");

        $.each(data.RECIPIENT_U_LIST, function (index, item) {
            var option = '<li><input type="submit" name="remove_u[' + item.UG_ID + ']" value="x" class="button2">' + item.NAME_FULL + '	</li>';
            $(recipients_list_exist).append(option);
            var hidden = '<input type="hidden" name="address_list[u][' + item.UG_ID + ']" value="bcc" />';
            $(lastclick).before(hidden);
        });
        $.each(data.RECIPIENT_G_LIST, function (index, item) {
            var option = '<li><input type="submit" name="remove_g[' + item.UG_ID + ']" value="x" class="button2">' + '<a href="' + item.U_VIEW + '"><strong>' + item.NAME + '</strong></a></li>';
            $(recipients_list_exist).append(option);
            var hidden = '<input type="hidden" name="address_list[g][' + item.UG_ID + ']" value="bcc" />';
            $(lastclick).before(hidden);
        });



    }


    //creates a new jQuery UI notification message
    function output_info_new( message, type,  expire, is_reload)
    {
        if(type == null) type= 'notification';
        if(expire == null) expire = 4000;
        var n = noty({
             text: message,
             type: type,
             timeout: expire,
             //dismissQueue: false,
             layout: 'topRight',
             theme: 'defaultTheme',
             callback: {
                afterClose: function() 
                {
                    if (is_reload == null || is_reload == '' || is_reload != true)  return;
                    window.location.reload();
                }
                },

 
             }); 
    }

})(jQuery);                                                                                                                  // Avoid conflicts with other libraries

