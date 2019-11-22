(function ($) {
	$(function () {
		$('.debug-bar-elementor-accordian').accordion({ collapsible: true, heightStyle: "content", active: false });
		$('.debug-bar-elementor-accordian a[data-id]').click(function () {
			wpDebugBar.actions.restore();
			$('.elementor-element').removeClass('debug-bar-elementor-highlight');
			var element = $('.elementor-element[data-id="' + $(this).data('id') + '"]');
			element.addClass('debug-bar-elementor-highlight').focus();
			$([document.documentElement, document.body]).animate({
				scrollTop: element.offset().top
			}, 500);
		})
		return false;
	})
})(jQuery);
