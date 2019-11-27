$(document).ready(function() {
	var scroll_timer;
	var displayed = false;
	var $link = $('#top-link');
	var $window = $(window);
	var top = $(document.body).children(0).position().top;

	$window.scroll(function() {
		clearTimeout(scroll_timer);
		scroll_timer = setTimeout(function() {
			if ($window.scrollTop() <= top) {
				displayed = false;
				$link.fadeOut(500);
			}
			else if (displayed == false) {
				displayed = true;
				$link.stop(true, true).fadeIn(500);
			}
		}, 100);
	});

	$link.click(function() {
		$('html, body').animate({scrollTop: 0}, 'slow');
		displayed = false;
		$link.fadeOut(500);
		return false;
	});
});