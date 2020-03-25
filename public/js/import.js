$(document).ready(function() {
   $('input[data-checkall]').on('change', function() {
       $('input[name="'+ $(this).data('checkall') + '"]').each(function() {
           if ($(this).attr('checked')) {
               $(this).removeAttr('checked');
           } else {
               $(this).attr('checked', 'checked');
           }
       });
   });
});