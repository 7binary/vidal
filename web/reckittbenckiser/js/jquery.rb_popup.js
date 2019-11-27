(function( $ ){

	function trimPX( value ) {
		return Number(value.substr(0, value.length - 2));
	}
	
	var methods = {
		init : function( options ) {
 			return this.each(function(){
				
				var $this = $(this);
				var data = $this.data('rb_popup');
			
				if ( !data ) {
					var settings = {
					};
					if ( options ) { 
						$.extend( settings, options );
					}
					
					data = {
						settings 					: settings,
						showPopup					: null
					};
					$this.data('rb_popup', data);
					
					var scrollableContent = $(".scrollable-popup-content");
					var scrollBar = $(".popup-scrollbar");
					var slider = $(".popup-slider");
					
					var scrollableContentHeight;
					var scrollableContainerHeight = $(".scrollable-popup-content-container").outerHeight();
					
					var mouseDownPageY;
					var mouseDownPositionY;
					var minScrollPosition = 0;
					var maxScrollPosition = scrollBar.outerHeight() - slider.outerHeight();
					var k;
										
					function setScrollProgress(value) {
						scrollableContent.css('top',(scrollableContainerHeight - scrollableContentHeight)*value);
					}
					
					function setScrollBarProgress(value) { 
						var newPosition = value * (maxScrollPosition - minScrollPosition) + minScrollPosition;
						slider.css('top', newPosition);
					}
					
					function sliderMouseDownHandler(e) {
						$(document).bind('mousemove',mouseMoveHandler);
						$(window).bind('mousemove',mouseMoveHandler);
						mouseDownPageY = e.pageY;
						mouseDownPositionY = trimPX(slider.css('top'));
						$(document).bind('mouseup',sliderMouseUpHandler);
						$('body').bind('mouseup',sliderMouseUpHandler);
						
						if (e && e.preventDefault) {
							e.preventDefault();
						}
						return false;
					}					
					slider.bind('mousedown', sliderMouseDownHandler);
					
					function mouseMoveHandler(e) { 
						var delta = e.pageY - mouseDownPageY;
						var newPosition = mouseDownPositionY + delta;
						if (newPosition > maxScrollPosition) 
							newPosition = maxScrollPosition;
						if (newPosition < minScrollPosition) 
							newPosition = minScrollPosition;
						slider.css('top', newPosition);
						setScrollProgress((newPosition - minScrollPosition)/(maxScrollPosition - minScrollPosition));
					}
					
					function sliderMouseUpHandler(e) {
						$(document).unbind('mousemove',mouseMoveHandler);
						$(window).unbind('mousemove',mouseMoveHandler);
					}
					
					function wheelHandler (e) {						
						var delta = e.originalEvent.wheelDelta / 120 || -e.originalEvent.detail / 4;
						if (delta)
							processWheelDelta(delta);
							
						e = e || window.event;
						if (e.preventDefault)
							e.preventDefault();
						e.returnValue = false; 
					}
					
					function processWheelDelta (delta) {
						var currentPosition = trimPX(scrollableContent.css('top'));
						
						currentPosition = currentPosition + delta * k;
						
						if (currentPosition < (scrollableContainerHeight - scrollableContentHeight)) currentPosition = (scrollableContainerHeight - scrollableContentHeight)
						else if (currentPosition > 0) currentPosition = 0;
						scrollableContent.css('top',currentPosition);
						
						setScrollBarProgress(currentPosition / (scrollableContainerHeight - scrollableContentHeight));
					}
					
					slider.bind('click',function (e) {
						if (e && e.preventDefault) {
							e.preventDefault();
						}
						return false;
					});
					
					$(".popup-close").click(function (e) {
						$(parent.document).unbind('mousewheel DOMMouseScroll', wheelHandler);
						if (navigator.userAgent.indexOf("Opera") == 0) {
							$("html").css("overflow", "visible");
							$("body").css("overflow", "visible");
						}						
						$("body").css("overflow-x", "hidden");
						$this.css("display", "none");						
						
						setScrollProgress(0);
						setScrollBarProgress(0);
						scrollableContent.html('Загрузка. Пожалуйста, подождите...');
						scrollableContentHeight = 0;
					
						if (e && e.preventDefault)
							e.preventDefault();

						return false;
					});
					
					function initNewContent () {						
						scrollableContentHeight = scrollableContent.outerHeight();
						k = (scrollableContentHeight - scrollableContainerHeight) / 45;						
						
						if (scrollableContainerHeight >= scrollableContentHeight) {
							scrollBar.css('display','none');
						} else {
							scrollBar.css('display','block');
							$(parent.document).bind('mousewheel DOMMouseScroll', wheelHandler);
						}						
					}
					
					function showPopup (url) {
						if (navigator.userAgent.indexOf("Opera") == 0) {
							$("html").css("overflow", "hidden");
							$("body").css("overflow", "hidden");
						}
						$this.css("display", "block");
						
						scrollableContent.load('./pages/'+url, function () {						
							scrollableContent.imagesLoaded(initNewContent);
						});
					}
					data.showPopup = showPopup;
				}
			});
		}
	};
 
	$.fn.rb_popup = function( method ) {
		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.rb_popup' );
		}    
	};
 })( jQuery );