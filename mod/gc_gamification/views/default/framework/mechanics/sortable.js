define(function (require) {

	var $ = require('jquery');
	var elgg = require('elgg');
	require('jquery-ui');
	
	function orderBadges(event, ui) {
		var data = ui.item.closest('.gm-badge-gallery').sortable('serialize');

		elgg.action('action/badge/order?' + data);

		ui.item.css('top', 0);
		ui.item.css('left', 0);
	}

	elgg.register_hook_handler('init', 'system', function () {
		$(".gm-badge-gallery:has(.elgg-state-sortable)").sortable({
			items: 'li.elgg-item',
			//connectWith: '.gm-badge-gallery',
			handle: 'img',
			forcePlaceholderSize: true,
			placeholder: 'gm-badge-placeholder',
			opacity: 0.8,
			revert: 500,
			stop: orderBadges
		});
	});

});