jQuery( document ).ready(function($){
	jQuery("#subcats").change( function(e){
		var page = jQuery(this).val();
		document.location.href = '?cat='+page;
	});
});

