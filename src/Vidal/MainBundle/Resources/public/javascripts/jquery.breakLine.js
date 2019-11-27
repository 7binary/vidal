(function($){
	$.fn.breakLink = function() {
		var $this = $(this);
		$this.each(function() {
			var $link = $(this);
			var text = $link.text();
			if (text.length > 70) {
				var parts = text.split('/');
				text = parts.join('<span style="display:inline-block;width:0"></span>/');
				$link.html(text);
			}
		});
		return $this;
	}
})(jQuery);