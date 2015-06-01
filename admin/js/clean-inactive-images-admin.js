(function ($) {
    'use strict';

    $(document).ready(function () {
            $('#cii_start').on('click', function () {
                console.log('getting all images in uploads folder...');
                $('#loading').fadeIn();
                makeAjaxRequest();
            });
        }
    );

    var makeAjaxRequest = function () {
        console.log('making ajax request');
        $.get(
            ajaxurl,
            {action: 'get_uploaded_images'}
        )
            .done(function (response) {
                $('#loading').fadeOut();
                $('#results').html(response);
            })
        ;
    }

})(jQuery);
