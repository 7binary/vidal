function detectmob() {
	if ((navigator.userAgent.match(/Android/i)
		|| navigator.userAgent.match(/webOS/i)
		|| navigator.userAgent.match(/iPhone/i)
		|| navigator.userAgent.match(/iPad/i)
		|| navigator.userAgent.match(/iPod/i)
		|| navigator.userAgent.match(/BlackBerry/i)
		|| navigator.userAgent.match(/Windows Phone/i))

		&& window.screen.width < 768
	) {
		return true;
	}
	else {
		return false;
	}
}
var $mobile = detectmob();
var $tablet = false;

if (window.screen.width < 768 && $mobile) {
	document.write('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">');
} else if (window.screen.width >= 768 && window.screen.width < 1008) {
	document.write('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">');
	$tablet = true;
} else {
	document.write('<meta id="viewport" name="viewport" content="width=1008">');
}