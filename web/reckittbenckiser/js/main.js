$(document).ready(function() {
	$(".logo").click(function (e) {
		window._gaq.push(['_trackEvent', 'logo', 'click']);
	});
	
	$(".bullet-section").click(function (e) {
		var $this = $(this);
		var expanable_block = $this.parent().parent().find(".expandable-block");
		var bullet_disk = $this.find(".bullet-disk");
		var bullet_expanded = $this.find(".bullet-expanded");
		var bullet_collapsed = $this.find(".bullet-collapsed");
		
		if ($this.hasClass("expanded")) {
			bullet_disk.stop().animate({
                left: 2
            }, {
                easing: 'swing'
            });
			setTimeout(function () {
				bullet_collapsed.css("display", "block");
			}, 150);
			$this.removeClass("expanded").addClass("collapsed");
		} else if ($this.hasClass("collapsed")) {
			bullet_disk.stop().animate({
                left: 24
            }, {
                easing: 'swing'
            });	
			setTimeout(function () {
				bullet_collapsed.css("display", "none");
			}, 150);
			$this.removeClass("collapsed").addClass("expanded");
		}
		expanable_block.slideToggle();
		
		$("head").append('<script type="text/javascript" async="async" src="http://track.hubrus.com/pixel?id=24199&type=js"></script>');
		
		if (e && e.preventDefault)
			e.preventDefault();
		return false;
	});
	
	var popup = $(".popup-shader");
	popup.rb_popup();
	$(".shelf li a").click(function (e) {
		popup.data("rb_popup").showPopup($(this).attr("href"));
	
		if (e && e.preventDefault)
			e.preventDefault();
		return false;
	});
	$(".expandable-block-content p > a").click(function (e) {
		popup.data("rb_popup").showPopup($(this).attr("href"));
	
		if (e && e.preventDefault)
			e.preventDefault();
		return false;
	});	
	$(".agreement a").click(function (e) {
		popup.data("rb_popup").showPopup($(this).attr("href"));
	
		if (e && e.preventDefault)
			e.preventDefault();
		return false;
	});	
	$(".details-button").click(function (e) {
		popup.data("rb_popup").showPopup($(this).attr("href"));
	
		if (e && e.preventDefault)
			e.preventDefault();
		return false;
	});	
	$(".h-center a").click(function (e) {
		popup.data("rb_popup").showPopup($(this).attr("href"));
	
		if (e && e.preventDefault)
			e.preventDefault();
		return false;
	});
});
