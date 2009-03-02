<?php
/*
Plugin Name: CF Featured Image
Plugin URI: http://crowdfavorite.com 
Description: This plugin adds a field to the post page to select a thumbnail image to be added to the feature image area of the main page. 
Version: 1.2b1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// 	ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}

/**
 * 
 * Format for areas array
 * 'key' => array(
 *			'name' => 'Name Here', 
 *			'description' => 'Description of the area here',
 *			'attach_to' => array('post','page')  // defaults to 'post' of not present
 *		)
 */
$cffp_areas = array(
		'area1' => array(
				'name' => 'Area 1',
				'description' => 'Description of Area 1 here',
				'attach_to' => array('post','page') // appear on both posts and pages admin pages
			),
		'area2' => array(
				'name' => 'Area 2',
				'description' => 'Description of Area 2 here',
				'attach_to' => array('post') // appear on only post admin pages
			),
);

/**
 * 
 * Featured Image - Init Functions
 * 
 */

function cffp_request_handler() {
	if (isset($_REQUEST['cffp'])) {
		$post_id = $_REQUEST['post_ID'];
		$img = $_REQUEST['cffp'];
		foreach ($img as $key => $id) {
			delete_post_meta($post_id,$key);
			update_post_meta($post_id,$key,$id);
		}
	}
	if (isset($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cffp_admin_css':
				cffp_admin_css();
				break;
			case 'cffp_admin_js':
				cffp_admin_js();
				break;
		}
	}
	if (isset($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cffp_get_images':
				if (isset($_POST['cffp_area']) && $_POST['cffp_area'] != '' && isset($_POST['cffp_att_id']) && $_POST['cffp_att_id'] != '' && isset($_POST['cffp_type']) && $_POST['cffp_type'] != '') {
					cffp_get_images($_POST['cffp_area'], $_POST['cffp_att_id'], $_POST['cffp_type']);
				}
				die();
		}
	}
}
add_action('init', 'cffp_request_handler');

/**
 * 
 * Featured Image - Admin Head Functions
 * 
 */

function cffp_admin_css() {
	header('Content-type: text/css');
	?>
	.cffp_overall {
		height:230px;
		overflow:auto;
	}
	.cffp_container {
		text-align: center; 
		width: 160px; 
		float: left;
		padding: 5px;
	}
	.cffp_help a {
		cursor:pointer;
		padding: 0 10px;
	}
	.cffp_type_active {
		background-color:#21759B;
		color:#FFFFFF;
	}
	.cffp_selected {
		background-color: #D3D5DB;
		-moz-border-radius-topleft:5px;
		-khtml-border-top-left-radius:5px;
		-webkit-border-top-left-radius:5px;
		border-top-left-radius:5px;
		-moz-border-radius-topright: 5px;
		-khtml-border-top-right-radius: 5px;
		-webkit-border-top-right-radius: 5px;
		border-top-right-radius: 5px;
		-moz-border-radius-bottomleft: 5px;
		-khtml-border-bottom-left-radius:5px;
		-webkit-border-bottom-left-radius:5px;
		border-bottom-left-radius:5px;
		-moz-border-radius-bottomright: 5px;
		-khtml-border-bottom-right-radius: 5px;
		-webkit-border-bottom-right-radius: 5px;
		border-bottom-right-radius: 5px;
	}
	.cffp_img {
		display:block;
		height: 160px;
		line-height: 160px;
		padding: 5px;
	}
	.cffp_radio {
		padding: 10px 0 0;
	}
	.cffp_help {
		padding: 10px;
	}
	.cffp_clear {
		float:none;
		clear:both;
	}
	<?php
	die();
}

function cffp_admin_js() {
	header('Content-type: text/javascript');
	?>
	function cffp_postImgs(area) {
		jQuery('#cffp_other_imgs_'+area).slideUp();
		jQuery('#cffp_post_imgs_'+area).slideDown();
		jQuery('#cffp_all_imgs_'+area).slideUp();
		jQuery('#post-click-'+area).attr('class','cffp_type_active');
		jQuery('#other-click-'+area).attr('class','');
		jQuery('#all-click-'+area).attr('class','');				
	}
	function cffp_otherImgs(area, att_id) {
		var ajaxSpinner = '<div id="cffp-ajax-spinner"><img src="<?php echo trailingslashit(get_bloginfo('wpurl')); ?>/wp-content/plugins/cf-featured-image/images/ajax-loader.gif" border="0" /><br /><span class="ajax-loading"><?php _e('Loading...','cf-archives'); ?></span></div>';
		var other_imgs = jQuery('#cffp_other_imgs_'+area);
		
		if (other_imgs.attr('class') != 'filled') {
			other_imgs.append(ajaxSpinner);
			jQuery.post('<?php echo trailingslashit(get_bloginfo('url')); ?>', {
				cf_action: 'cffp_get_images',
				cffp_area: area,
				cffp_att_id: att_id,
				cffp_type: 'other',
			},function(data){
				jQuery('#cffp-ajax-spinner').remove();
				other_imgs.append(data);
			});
		}
		jQuery('#cffp_all_imgs_'+area).slideUp();
		jQuery('#cffp_post_imgs_'+area).slideUp();
		other_imgs.attr('class','filled').slideDown();
		jQuery('#post-click-'+area).attr('class','');
		jQuery('#other-click-'+area).attr('class','cffp_type_active');
		jQuery('#all-click-'+area).attr('class','');		
	}
	function cffp_allImgs(area, att_id) {
		var ajaxSpinner = '<div id="cffp-ajax-spinner"><img src="<?php echo trailingslashit(get_bloginfo('wpurl')); ?>/wp-content/plugins/cf-featured-image/images/ajax-loader.gif" border="0" /><br /><span class="ajax-loading"><?php _e('Loading...','cf-archives'); ?></span></div>';
		var all_imgs = jQuery('#cffp_all_imgs_'+area);
		
		if (all_imgs.attr('class') != 'filled') {
			all_imgs.append(ajaxSpinner);
			jQuery.post('<?php echo trailingslashit(get_bloginfo('url')); ?>', {
				cf_action: 'cffp_get_images',
				cffp_area: area,
				cffp_att_id: att_id,
				cffp_type: 'all',
			},function(data){
				jQuery('#cffp-ajax-spinner').remove();
				all_imgs.append(data);
			});
		}
		jQuery('#cffp_other_imgs_'+area).slideUp();
		jQuery('#cffp_post_imgs_'+area).slideUp();
		all_imgs.attr('class','filled').slideDown();
		jQuery('#post-click-'+area).attr('class','');
		jQuery('#other-click-'+area).attr('class','');
		jQuery('#all-click-'+area).attr('class','cffp_type_active');		
	}
	function cffp_help_text(area) {
		jQuery('#cffp_help_text_'+area).slideToggle();
	}
	<?php
	die();
}

function cffp_admin_head() {
	global $cffp_areas;
	$cffp_areas = apply_filters('cffp_add_areas',$cffp_areas);

	echo '<link rel="stylesheet" type="text/css" href="'.trailingslashit(get_bloginfo('url')).'index.php?cf_action=cffp_admin_css" />';
	echo '<script type="text/javascript" src="'.trailingslashit(get_bloginfo('wpurl')).'index.php?cf_action=cffp_admin_js"></script>';	
	
	foreach ($cffp_areas as $key => $area) {
		$area_id = sanitize_title('cffp-'.$key);
		if (!is_array($area['attach_to'])) {
			$area['attach_to'] = array('post');
		}
		foreach ($area['attach_to'] as $here) {
			add_meta_box($area_id, htmlspecialchars($area['name']), 'cffp_edit_post', $here, 'normal', 'high');
		}		
	}
}
add_action('admin_head','cffp_admin_head');

/**
 * 
 * Featured Image - Admin Display Functions
 * 
 */

function cffp_edit_post($post,$area) {
	global $wpdb, $cffp_areas;
	
	$post_imgs = '';
	$area_info = $cffp_areas[str_replace('cffp-','',$area['id'])];

	$cffp_id = '_'.$area['id'];
	$cffp_description = $area_info['description'];

	$cffp_att_id = get_post_meta($post->ID, $cffp_id, true);
	if ($cffp_att_id == '') {
		$cffp_att_id = 0;
	}
	$post_imgs = cffp_get_img_attachments('= '.$post->ID, $cffp_att_id, $cffp_id, 'post');
	$selected_img = cffp_get_img_attachments_selected($cffp_att_id, $cffp_id);
	
	if ($cffp_att_id == 'NULL' || $cffp_att_id == '') {
		$noimg_checked = ' checked="checked"';
		$noimg_selected = ' cffp_selected';
	}
	else {
		$noimg_checked = '';
		$noimg_selected = '';
	}
	
	$post_container_width = ($post_imgs['count'] + 2) * 170;
	
	print('
	<div class="cffp_help">
		<p>'.$cffp_description.' <a onclick="cffp_help_text(\''.$cffp_id.'\')"><em>Help</em></a></p>
		<p id="cffp_help_text_'.$cffp_id.'" style="display:none;"><em>'.__('To add new images, upload them via the Media Gallery. Refresh your browser after uploading to see new images.').'</em></p>
		<p><a id="post-click-'.$cffp_id.'" onclick="cffp_postImgs(\''.$cffp_id.'\')" class="cffp_type_active">Post Images</a><a id="other-click-'.$cffp_id.'" onclick="cffp_otherImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\')">Unattached Images</a><a id="all-click-'.$cffp_id.'" onclick="cffp_allImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\')">All Images</a></p>
	</div>
	<div class="cffp_overall">
		<div id="cffp_none" style="width:170px;">
			<div class="cffp_container'.$noimg_selected.'">
				<label class="cffp_img" for="cffp-'.$cffp_id.'-leadimg-0">'.__('No Image').'</label>
				<div class="cffp_radio">
					<input type="radio" name="cffp['.$cffp_id.']" id="cffp-'.$cffp_id.'-leadimg-0" value="NULL"'.$noimg_checked.' />
				</div>
			</div>
		</div>
		<div id="cffp_selected_img_'.$cffp_id.'">
			');
			if ($selected_img != '') {
				echo $selected_img;
			}
			print('
		</div>
		<div id="cffp_post_imgs_'.$cffp_id.'">
			<div class="cffp_images" style="width:'.$post_container_width.'px;">
				');
				if (!empty($post_imgs['html']) && $post->ID != 0) {
					echo $post_imgs['html'];
				}
				print('
			</div>
		</div>
		<div id="cffp_other_imgs_'.$cffp_id.'" style="display:none;">
		</div>
		<div id="cffp_all_imgs_'.$cffp_id.'" style="display:none;">
		</div>
	</div>
	<div class="cffp_clear"></div>
	');
}

function cffp_get_images($area, $att_id, $type = 'all') {

	if ($type == 'all') {
		$imgs = cffp_get_img_attachments('', $att_id, $area, $type);
	}
	if ($type == 'other') {
		$imgs = cffp_get_img_attachments('<= 0', $att_id, $area, $type);
	}
	$container_width = ($imgs['count'] + 2) * 170;

	print('
		<div class="cffp_images" style="width:'.$container_width.'px;">
			');
			if (!empty($imgs['selected'])) {
				echo $imgs['selected'];
			}
			if (!empty($imgs['html'])) {
				echo $imgs['html'];
			}
			print('
		</div>
	');
	die();
}

function cffp_get_img_attachments($id_string, $cffp_att_id, $cffp_id, $type) {
	global $wpdb;
	$return = '';
	$parent = '';
	$count = 0;
	if ($id_string != '') {
		$parent = ' AND post_parent '.$id_string;
	}
	$cffp_attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' AND post_mime_type LIKE 'image%' $parent", ARRAY_A);
	if (count($cffp_attachments)) {
		$count = count($cffp_attachments);
		$return = '';
		
		foreach ($cffp_attachments as $cffp_attachment) {
			$image_link = wp_get_attachment_image_src($cffp_attachment['ID']);
			$image_meta = get_post_meta($cffp_attachment['ID'],'_wp_attachment_metadata',true);

			if ($cffp_att_id != $cffp_attachment['ID']) {
				$return .= '
					<div class="cffp_container">
						<label class="cffp_img" style="background: transparent url('.$image_link[0].') no-repeat scroll center center; width: 150px; height: 150px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'">
						</label>
						<div class="cffp_radio">
							<input type="radio" name="cffp['.$cffp_id.']" id="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'" value="'.$cffp_attachment['ID'].'" />
						</div>
					</div>
				';
			}
		}
		
	}
	return array('html' => $return, 'count' => $count);
}

function cffp_get_img_attachments_selected($cffp_att_id, $cffp_id) {
	global $wpdb;
	$return = '';
	
	$cffp_selected = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' AND post_mime_type LIKE 'image%' AND ID = '$cffp_att_id'");
	
	foreach ($cffp_selected as $selected) {
		$image_link = wp_get_attachment_image_src($selected->ID);
		$image_meta = get_post_meta($selected->ID, '_wp_attachment_metadata', true);

		$return .= '
			<div class="cffp_container cffp_selected">
				<label class="cffp_img" style="background: transparent url('.$image_link[0].') no-repeat scroll center center; width: 150px; height: 150px;" for="cffp-'.$cffp_id.'-leadimg-'.$selected->ID.'">
				</label>
				<div class="cffp_radio">
					<input type="radio" name="cffp['.$cffp_id.']" id="cffp-'.$cffp_id.'-leadimg-'.$selected->ID.'" value="'.$selected->ID.'" checked="checked" />
				</div>
			</div>
		';
	}
	return $return;
}

/**
 * 
 * Featured Image - Front End Display Functions
 * 
 */

function cffp_display($area = '') {
	global $post;
	if ($area != '') {
		$image = wp_get_attachment_image_src(get_post_meta($post->ID, '_cffp-'.$area, true), 'thumbnail');
		return $image[0];
	}
	return '';
}

function cffp_get_img($post_id = 0, $size = 'thumbnail', $area = '') {
	if ($post_id != 0 && $area != '') {
		$cffp_image = wp_get_attachment_image_src(get_post_meta($post_id, '_cffp-'.$area, true), $size);
		if ($cffp_image[0] != '') {
			return $cffp_image[0];
		}
	}
	return '';
}

function cffp_get_img_tag($post_id = 0, $size = 'thumbnail', $area = '') {
	if ($post_id != 0 && $area != '') {
		$cffp_image = wp_get_attachment_image_src(get_post_meta($post_id, '_cffp-'.$area, true), $size);
		if ($cffp_image[0] != '') {
			return '<img src="'.$cffp_image[0].'" alt="featured post image for post id: '.$post_id.'" />';
		}
	}
	return '';
}

function cffp_img($post_id = 0, $size = 'thumbnail', $area = '') {
	echo cffp_get_img($post_id, $size, $area);
}

function cffp_img_tag($post_id = 0, $size = 'thumbnail', $area = '') {
	echo cffp_get_img_tag($post_id, $size, $area);
}
?>
