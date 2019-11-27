;
(function ($) {
    $.fn.customFile = function () {

        return this.each(function () {

            var $file = $(this);
            var $input = $('<input type="text" class="input-upload" ' +
                'value="загрузите изображение" style="width:200px"/>');

            $file.parent('div').css('float', 'none');

            // Hide by shifting to the left so we
            // can still trigger events
            $file.css({
                position: 'absolute',
                left: '-9999px'
            });

            $input.insertAfter($file);

			var $inputBtn = $('<a href="#" class="btn-red" style="margin-left:10px">Обзор</a>');
			$inputBtn.insertAfter($input);

			$inputBtn.click(function(e) {
				e.preventDefault();
				$file.focus().click();
			});

            // Prevent focus
            $file.attr('tabIndex', -1);

            $input.click(function () {
                $file.focus().click(); // Open dialog
            });

            $file.change(function () {
                var filename = $file.val().split('\\').pop();
                $input.val(filename) // Set the value
                    .attr('title', filename) // Show filename in title tootlip
                    .focus(); // Regain focus
            });

            $input.on({
                blur: function () {
                    $file.trigger('blur');
                },
                keydown: function (e) {
                    if (e.which === 13) { // Enter
                        if (!isIE) {
                            $file.trigger('click');
                        }
                    } else if (e.which === 8 || e.which === 46) { // Backspace & Del
                        // On some browsers the value is read-only
                        // with this trick we remove the old input and add
                        // a clean clone with all the original events attached
                        $file.replaceWith($file = $file.clone(true));
                        $file.trigger('change');
                        $input.val('');
                    } else if (e.which === 9) { // TAB
                        return;
                    } else { // All other keys
                        return false;
                    }
                }
            });
        });
    };
}(jQuery));