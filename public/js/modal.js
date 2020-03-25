$(document).ready(function() {
    $('.close-modal').on('click', function(){
        $('.modal-wrapper').addClass('hidden');
        $('.modal').removeClass('full');
        $('.modal-body').html('');
        $('.modal-title').html('&nbsp;');
    });

    $('a[data-modal], button[data-modal]').on('click', function() {
        if ($(this).data('modal-full')) {
            $('.modal').addClass('full');
        }

        if ($(this).data('modal-title')) {
            $('.modal-title').html($(this).data('modal-title'));
        }

        $('.modal-wrapper').removeClass('hidden');

        loadModalContent($(this).data('url'));
    });

    $('.modal-wrapper .submit').on('click', function() {
        var $form = $('.modal-wrapper .modal-form');

        if ($form.hasClass('no-ajax')) {
            $form.submit();
            return;
        }

        $.post($form.attr('action'), $form.serialize()).done(function(data) {
            if (!data) {
                document.location.reload();
                return;
            }

            $('.modal-body').html(data);
            preventA();
            preventFilterForm();
        });
    });

    function loadModalContent(url) {
        $.get(url).done(function(data){
            $('.modal-body').html(data);

            preventA();
            preventFilterForm();
        });
    }

    function preventA() {
        $('.modal-body a').on('click', function() {
            if ($(this).attr('href') && $(this).attr('href') !== '#' && $(this).attr('href') !== 'javascript:void(0);') {
                loadModalContent($(this).attr('href'));
            }
            return false;
        });
    }

    function preventFilterForm() {
        $('.modal-body form.submit-on-change select').on('change', function() {
            var $form = $(this).closest('form');

            $.post($form.attr('action'), $form.serialize()).done(function(data) {
                $('.modal-body').html(data);

                preventA();
                preventFilterForm();
            })
        });

        $('.modal-body form.submit-on-change input').keypress(function(event){
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if(keycode == '13'){
                var $form = $(this).closest('form');

                $.post($form.attr('action'), $form.serialize()).done(function(data) {
                    $('.modal-body').html(data);

                    preventA();
                    preventFilterForm();
                })
            }
        });
    }

});