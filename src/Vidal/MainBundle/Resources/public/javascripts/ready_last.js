$(document).ready(function() {
    $('.vidalbox-menu').click(function() {
        ga('send', 'event', 'vidalbox', 'click', 'Меню слева');
    });

    $('.neirontin-menu').click(function() {
        ga('send', 'event', 'neirontin', 'click', 'Меню слева');
    });

/*
    setTimeout(function () {
        var url = '/social-top-buttons';
        $.getJSON(url, function(html) {
            $('#social_top_buttons').html(html);
        });
    }, 50);
*/

    // Полностью завершена отрисовка страницы страницы
    document.onreadystatechange = function() {
        if ( document.readyState === 'complete' ) {
            $('.show-after-complete').show();
        }
    }
});