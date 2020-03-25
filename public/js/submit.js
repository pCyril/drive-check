$(document).ready(function() {
   $('form.submit-on-change select').on('change', function() {
       $(this).closest('form').submit();
   });

    $('form.submit-on-change input').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $(this).closest('form').submit();
        }
    });
});