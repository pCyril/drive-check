$(document).ready(function() {
    $('*[data-tooltip]').each(function() {
        $(this).find('.tooltip').css({
            'top': '-34px',
            'left': $(this).width() + 15 + 'px',
        });
    });

    $('*[data-tooltip]').on('click', function() {
       $(this).toggleClass('force');
    });
});