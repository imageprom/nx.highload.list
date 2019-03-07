(function(){
	$(function(){

	function nxHibGetDate() {
		$.ajax({
			url: window.location.pathname + '?nx_ajax_hibl_action=Y',
			type: 'GET',
			processData: false,
			contentType: false,
			timeout: 50000,
			beforeSend: function () {
				//element.addClass('load');
			}
		}).done(function (data) {
		
			console.log(data);

			
		});
	}

	//nxHibGetDate();
});
})(jQuery);
