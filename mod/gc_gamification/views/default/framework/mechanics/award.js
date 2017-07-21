define(function (require) {

	var $ = require('jquery');
	var lightbox = require('elgg/lightbox');
	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();
	
	$(document).on('submit', '#colorbox .elgg-form-points-award', function (e) {
		e.preventDefault();

		var $form = $(this);

		ajax.action($form.prop('action'), {
			data: ajax.objectify($form)
		}).done(function() {
			lightbox.close();
		});
		
	});
});