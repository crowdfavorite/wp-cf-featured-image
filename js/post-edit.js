;(function($) {
	$(function() {
		cffp_getImgs = function(area, att_id, type, postID, offset) {
			var imgs_area = $('#cffp_'+type+'_imgs_'+area);
			
			$('#cffp_all_imgs_'+area+'_wrapper').hide();
			$('#cffp_other_imgs_'+area+'_wrapper').hide();
			$('#cffp_post_imgs_'+area+'_wrapper').hide();
			$('#cffp_ajax_spinner_'+area+'_wrapper').fadeIn();

			var select_val = $('#'+type+'-click-'+area).attr('cffp_last_checked');

			$.post("index.php", {
				cf_action: "cffp_get_images",
				cffp_area: area,
				cffp_att_id: att_id,
				cffp_post_id: postID,
				cffp_last_selected: select_val,
				cffp_type: type,
				cffp_offset: offset
			}, function(data){
				data = $(data);
				$('input[type="radio"]', data).each(function() {
					$(this).change(function(){
						var type = $(this).attr('cffp_type');
						var area = $(this).parent().parent().parent().attr('id');

						area = area.replace('cffp_'+type+'_imgs__','');
						cffp_last_checked(type, area);
					});
				});

				$('#cffp_ajax_spinner_'+area+'_wrapper').hide();
				imgs_area.replaceWith(data);
				$('#cffp_'+type+'_imgs_'+area+'_wrapper').fadeIn();

				if (type != 'all') {
					$('#other-click-'+area).attr('class','');
					$('#post-click-'+area).attr('class','');
				}
				if (type != 'other') {
					$('#all-click-'+area).attr('class','');
					$('#post-click-'+area).attr('class','');
				}
				if (type != 'post') {
					$('#all-click-'+area).attr('class','');
					$('#other-click-'+area).attr('class','');
				}
				$('#'+type+'-click-'+area).attr('class','cffp_type_active');
			});
		};

		cffp_last_checked = function(type, area) {
			var last_val = $('#'+area+' .cffp_overall input[type="radio"]:checked').val();
			if (last_val == null) {
				last_val = 0;
			}
			$('#'+type+'-click-_'+area).attr('cffp_last_checked',last_val);
		};
		
		cffp_help_text = function(area) {
			$('#cffp_help_text_'+area).slideToggle();
		};
	});
})(jQuery);