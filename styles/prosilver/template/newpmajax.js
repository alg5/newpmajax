(function ($) {  

    
    $().ready(function () {

// 
        $('#pmheader-postingbox').find('input[name=add_to]').on('click', function (e) {
                e.preventDefault();
//                if ($('#group_list :selected').length >0)
//               $('#postform').submit();
                 var path = U_NEWPMAJAX_PATH +  'add_to/0/0/0';
                console.log($('#username_list').val());
                 $.ajax({
		                type: 'POST',
		                dataType: 'json',
                       data: "username_list=$('#username_list').val()",
		                url: path,
		                success: function(data){
               alert('3');
			                //togle_thanks(data);
		                }
	                });
              
                 });



 }); //$().ready(function ()





})(jQuery);                                                                 // Avoid conflicts with other libraries


