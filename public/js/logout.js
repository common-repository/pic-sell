jQuery(document).ready(function($){
	$('.button_pic.logout').click(function(){
		var data = {
			action : "picsell_logout"
		}

		console.log("logout...");
		
		$.post( picsell_ajax.ajaxurl, data, function( response ) {
			window.location.reload(true);
		}, "JSON" );
	});
});