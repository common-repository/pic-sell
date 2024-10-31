
jQuery(document).ready(function($){


	 var custom_uploader,
	 click_elem = $('.picsell-upload'),
	 target = $('#builder-header')

   click_elem.click(function(e) {
	   e.preventDefault();			
	   if (custom_uploader) {
		   custom_uploader.open();
		   return;
	   }	
	   custom_uploader = wp.media.frames.file_frame = wp.media({
		   title: 'Choose Image',
		   button: {
			   text: 'Choose Image'
		   },
		   multiple: false
	   });
	   custom_uploader.on('select', function() { 
		   	attachment = custom_uploader.state().get('selection').first().toJSON();
		   	if(attachment.url != ""){
				$('#header-image-preview').attr('src',attachment.url).css({'width':'auto', 'maxHeight':'70px'}).show(500);
				$(document).find('.picsell-upload').hide();
				target.val(attachment.url);		
				$('#submit').trigger('click');  
		   	} 
	   });
	   custom_uploader.open();
   });  

	if($(document).find('#header-image-preview').attr('src') != ""){
		$(document).find('.picsell-upload').hide();
	}

   $('#header-image-preview').parent()
   .on('mouseover', function(){
		if($(this).parent().find('#header-image-preview').attr('src') != ""){
			$(this).find('.header-image-preview-remove').show();
			$(this).find('.header-image-preview-edit').show();
		}
   })
   .on('mouseout', function(){
	  	$(this).find('.header-image-preview-remove').hide();
	  	$(this).find('.header-image-preview-edit').hide();
   }).find('.header-image-preview-remove')
   .on('click', function(){
	   	$(this).parent().find('#header-image-preview').attr('src','');
	   	$(this).parent().parent().find('#builder-header').val('');
	   	$(this).parent().parent().find('.picsell-upload').show();
   }).parent().find('.header-image-preview-edit')
   .on('click', function(){
		$(this).parent().parent().find('.picsell-upload').trigger('click');
	});


	function getAnchor() {
		return (document.URL.split('#').length > 1) ? document.URL.split('#')[1] : null;
	}

	var tabConfig = function(load){

		var anchor = getAnchor();

		$('.nav-tab').each(function(i, obj) {
			var active = false;

			var classList = $(obj).attr('class');
			var classArr = classList.split(/\s+/);
			var findClass = classArr[1];

			if(anchor == null && i == 0 && load){
				$(obj).addClass("nav-tab-active");
			}else if(anchor != null && load && $(obj).hasClass(anchor)){
				$(obj).addClass("nav-tab-active");
			}

			if($(obj).hasClass("nav-tab-active")){
				active = true;
			}

			if(active){
				$('.content .'+findClass).addClass("nav-pic-active");
				$referer = $("input[name='_wp_http_referer']").val().split("#")[0];
				$("input[name='_wp_http_referer']").val($referer+"#"+findClass);
			}else{
				$('.content .'+findClass).removeClass("nav-pic-active");
			}

		});
	}

	$config = ($("body").hasClass("toplevel_page_picsell") || $("body").hasClass("pic-sell_page_picsell_settings-pic") || $("body").hasClass("pic-sell_page_picsell_page_commandes"))?true:false;
	if($config){
		console.log("tabConfig");
		tabConfig(true);
	}
	

	$(".toplevel_page_picsell, .pic-sell_page_picsell_settings-pic, .pic-sell_page_picsell_page_commandes").on("click", ".nav-tab", function(e){
		e.preventDefault();
		var target = e.target;
		if($(target).hasClass("nav-tab-active")){
			return;
		}else{
			$('.nav-tab').each(function(i, obj) {
				$(obj).removeClass("nav-tab-active")
			});
			var classList = $(target).attr('class');
			var classArr = classList.split(/\s+/);
			var findClass = classArr[1];
			$(target).addClass("nav-tab-active");
			location.hash = findClass;		
			$referer = $("input[name='_wp_http_referer']").val().split("#")[0];
			$("input[name='_wp_http_referer']").val($referer+"#"+findClass);
			tabConfig(false);
		}
		
	});
   
   
}); 


