$(document).ready(function() {
   $('a[data-confirm]').on('click', function() {
       return confirm($(this).data('confirm'));
   });
});