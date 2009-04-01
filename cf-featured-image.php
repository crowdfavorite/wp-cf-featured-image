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
 * $cffp_areas['key'] = array(
 *		'name' => 'Name Here', 
 *		'description' => 'Description of the area here',
 *		'attach_to' => array('post','page'),  // defaults to 'post' of not present
 * 		'mime_types' => array('jpeg','png','gif','jpg'), // defaults to all if not present
 *	);
 *	$cffp_areas['area1'] = array(
 *		'name' => 'Area 1',
 *		'description' => 'Description of Area 1 here',
 *		'attach_to' => array('post','page'), // appear on both posts and pages admin pages
 *	);
 *	$cffp_areas['area2'] = array(
 *		'name' => 'Area 2',
 *		'description' => 'Description of Area 2 here',
 *		'attach_to' => array('post'), // appear on only post admin pages
 * 		'mime_types' => array('pdf'),
 *	);
 */

/**
* Filters
* 
* apply_filters('cffp_add_areas',$cffp_areas);
 */

/**
 * 
 * Filterable Mime Types array
 * 
 **/

$cffp_mime_types = array(
	'images' => array(
		'png' => 'image/png',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpg',
		'gif' => 'image/gif',
	),
	'other' => array(
		'pdf' => 'application/pdf',
		'zip' => 'application/octet-stream',
	),
);


/**
 * 
 * Featured Image - Init Functions
 * 
 */

function cffp_request_handler() {
	global $cffp_mime_types,$cffp_areas;
	$cffp_mime_types = apply_filters('cffp_mime_types', $cffp_mime_types);
	$cffp_areas = apply_filters('cffp_add_areas',$cffp_areas);
	
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
				if (isset($_POST['cffp_area']) && $_POST['cffp_area'] != '' && isset($_POST['cffp_att_id']) && $_POST['cffp_att_id'] != '' && isset($_POST['cffp_type']) && $_POST['cffp_type'] != '' && isset($_POST['cffp_post_id']) && $_POST['cffp_post_id'] != '') {
					cffp_get_images($_POST['cffp_area'], $_POST['cffp_att_id'], $_POST['cffp_type'], $_POST['cffp_post_id']);
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
	#cffp_ajax_spinner {
		width:160px;
		line-height:160px;
		float:left;
		text-align:center;
		vertical-align:middle;
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
		height: 150px;
		line-height: 150px;
		padding: 5px;
		overflow: hidden;
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
	$url = trailingslashit(get_bloginfo('url'));
	if (FORCE_SSL_ADMIN) {
		$url = str_replace('http','https',$url);
	}
	header('Content-type: text/javascript');
	?>
	function cffp_getImgs(area, att_id, type, postID) {
		var imgs_area = jQuery('#cffp_'+type+'_imgs_'+area);

		jQuery('#cffp_all_imgs_'+area+'_wrapper').hide();
		jQuery('#cffp_other_imgs_'+area+'_wrapper').hide();
		jQuery('#cffp_post_imgs_'+area+'_wrapper').hide();
		jQuery('#cffp_ajax_spinner_'+area+'_wrapper').fadeIn();
		
		jQuery.post('<?php echo $url; ?>', {
			cf_action: 'cffp_get_images',
			cffp_area: area,
			cffp_att_id: att_id,
			cffp_post_id: postID,
			cffp_type: type,
		},function(data){
			jQuery('#cffp_ajax_spinner_'+area+'_wrapper').hide();
			imgs_area.replaceWith(data);
			jQuery('#cffp_'+type+'_imgs_'+area+'_wrapper').fadeIn();

			if (type != 'all') {
				jQuery('#other-click-'+area).attr('class','');
				jQuery('#post-click-'+area).attr('class','');
			}
			if (type != 'other') {
				jQuery('#all-click-'+area).attr('class','');
				jQuery('#post-click-'+area).attr('class','');
			}
			if (type != 'post') {
				jQuery('#all-click-'+area).attr('class','');
				jQuery('#other-click-'+area).attr('class','');
			}
			if (type == 'all') {
				jQuery('#all-click-'+area).attr('class','cffp_type_active');
			}
			if (type == 'other') {
				jQuery('#other-click-'+area).attr('class','cffp_type_active');
			}
			if (type == 'post') {
				jQuery('#post-click-'+area).attr('class','cffp_type_active');
			}
		});
	}
	function cffp_help_text(area) {
		jQuery('#cffp_help_text_'+area).slideToggle();
	}
	<?php
	die();
}

function cffp_admin_head() {
	global $cffp_areas;

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
	$post_imgs = cffp_get_img_attachments('= '.$post->ID, $cffp_att_id, $cffp_id, 'post', $area_info['mime_types']);
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
		<p id="cffp_help_text_'.$cffp_id.'" style="display:none;"><em>'.__('To add new files, upload them via the Media Gallery. Once files have been uploaded, click the Post Files button to refresh.').'</em></p>
		<p><a id="post-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'post\',\''.$post->ID.'\')" class="cffp_type_active">Post Files</a><a id="other-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'other\',\''.$post->ID.'\')">Unattached Files</a><a id="all-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'all\',\''.$post->ID.'\')">All Files</a></p>
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
		<div id="cffp_ajax_spinner_'.$cffp_id.'_wrapper" style="display:none;">
			<div id="cffp_ajax_spinner">
				<img src="'.trailingslashit(get_bloginfo('wpurl')).'/wp-content/plugins/cf-featured-image/images/ajax-loader.gif" border="0" />
				<span class="ajax-loading">
					'.__('Loading...','cf-archives').'
				</span>
			</div>		
		</div>
		<div id="cffp_post_imgs_'.$cffp_id.'_wrapper">
			<div id="cffp_post_imgs_'.$cffp_id.'">
				<div class="cffp_images" style="width:'.$post_container_width.'px;">
					');
					if (!empty($post_imgs['html']) && $post->ID != 0) {
						echo $post_imgs['html'];
					}
					print('
				</div>
			</div>
		</div>
		<div id="cffp_other_imgs_'.$cffp_id.'_wrapper" style="display:none;">
			<div id="cffp_other_imgs_'.$cffp_id.'">
			</div>
		</div>
		<div id="cffp_all_imgs_'.$cffp_id.'_wrapper" style="display:none;">
			<div id="cffp_all_imgs_'.$cffp_id.'">
			</div>
		</div>
	</div>
	<div class="cffp_clear"></div>
	');
}

function cffp_get_images($area, $att_id, $type = 'all', $post_id) {
	global $cffp_areas;
	
	$area_info = $cffp_areas[str_replace('_cffp-','',$area)];
	
	if ($type == 'all') {
		$imgs = cffp_get_img_attachments('', $att_id, $area, $type, $area_info['mime_types']);
	}
	if ($type == 'other') {
		$imgs = cffp_get_img_attachments('<= 0', $att_id, $area, $type, $area_info['mime_types']);
	}
	if ($type == 'post') {
		global $post;
		$imgs = cffp_get_img_attachments('= '.$post_id, $att_id, $area, $type, $area_info['mime_types']);
	}
	$container_width = ($imgs['count'] + 2) * 170;
	print('
		<div id="cffp_'.$type.'_imgs_'.$area.'" class="cffp_images" style="width:'.$container_width.'px;">
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

function cffp_get_img_attachments($id_string, $cffp_att_id, $cffp_id, $type, $mime_types = array()) {
	global $wpdb,$cffp_mime_types;
	$return = '';
	$parent = '';
	$mime_query = '';
	$count = 0;
	if ($id_string != '') {
		$parent = ' AND post_parent '.$id_string;
	}
	
	if (!empty($mime_types)) {
		foreach ($mime_types as $mime) {
			$query_mime_type = '';
			if ($cffp_mime_types['images'][$mime] != '') {
				$query_mime_type = $cffp_mime_types['images'][$mime];
			}
			if ($cffp_mime_types['other'][$mime] != '') {
				$query_mime_type = $cffp_mime_types['other'][$mime];
			}
			if ($mime_query == '') {
				$mime_query .= ' post_mime_type LIKE "'.$cffp_mime_types['other'][$mime].'"';
			}
			else {
				$mime_query .= ' OR post_mime_type LIKE "'.$cffp_mime_types['other'][$mime].'"';
			}
		}
	}
	else {
		foreach ($cffp_mime_types['images'] as $cffp_key => $cffp_mime) {
			if ($mime_query == '') {
				$mime_query .= ' post_mime_type LIKE "'.$cffp_mime.'"';
			}
			else {
				$mime_query .= ' OR post_mime_type LIKE "'.$cffp_mime.'"';
			}
		}
	}

	$count = 1;
	$cffp_attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' $parent AND post_mime_type NOT LIKE '' AND($mime_query)", ARRAY_A);
	if (count($cffp_attachments)) {
		$count = count($cffp_attachments);
		
		foreach ($cffp_attachments as $cffp_attachment) {
			$image_link = wp_get_attachment_image_src($cffp_attachment['ID']);

			if ($cffp_att_id != $cffp_attachment['ID']) {
				$label = '';
				if ($cffp_attachment['post_mime_type'] == 'application/octet-stream' || $cffp_attachment['post_mime_type'] == 'application/pdf') {
					$label = '<label class="cffp_img" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'">'.$cffp_attachment['post_title'].'</label>';
				}
				else {
					$label = '<label class="cffp_img" style="background: transparent url('.$image_link[0].') no-repeat scroll center center; width: 150px; height: 150px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'"></label>';
				}
				$return .= '
					<div class="cffp_container">
						'.$label.'
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
	
	$cffp_selected = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' AND ID = '$cffp_att_id'");
	foreach ($cffp_selected as $selected) {
		$image_link = wp_get_attachment_image_src($selected->ID);
		$image_meta = get_post_meta($selected->ID, '_wp_attachment_metadata', true);
		
		$label = '';
		if ($selected->post_mime_type == 'application/octet-stream' || $selected->post_mime_type == 'application/pdf') {
			$label = '<label class="cffp_img" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$selected->ID.'">'.$selected->post_title.'</label>';
		}
		else {
			$label = '<label class="cffp_img" style="background: transparent url('.$image_link[0].') no-repeat scroll center center; width: 150px; height: 150px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$selected->ID.'"></label>';
		}

		$return .= '
			<div class="cffp_container cffp_selected">
				'.$label.'
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
	if ($area != '') {
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}
		$cffp_image = wp_get_attachment_image_src(get_post_meta($post_id, '_cffp-'.$area, true), $size);
		if ($cffp_image[0] != '') {
			return $cffp_image[0];
		}
	}
	return '';
}

function cffp_get_img_tag($post_id = 0, $size = 'thumbnail', $area = '') {
	if ($area != '') {
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}
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

function cffp_get_attachment($area = '', $post_id = 0) {
	if (!empty($area)) {
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}
		$link = wp_get_attachment_url(get_post_meta($post_id, '_cffp-'.$area, true));
		if (!empty($link)) {
			$cffp_image = $link;
			return $cffp_image;
		}
	}
	return false;
}

function cffp_get_attachment_link($area, $link_text = '', $post_id = 0) {
	if (!empty($area)) {
		$link = '';
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}
		if (empty($link_text)) {
			$link_text = wp_get_attachment_url(get_post_meta($post_id, '_cffp-'.$area, true));
		}
		$link = wp_get_attachment_url(get_post_meta($post_id, '_cffp-'.$area, true));
		if (!empty($link)) {
			$cffp_image = '<a href="'.$link.'">'.$link_text.'</a>';
			return $cffp_image;
		}
	}
	return false;
}

function cffp_attachment($area, $post_id = 0) {
	echo cffp_get_attachment($area, $post_id);
}

function cffp_attachment_link($area, $link_text = '', $post_id = 0) {
	echo cffp_get_attachment_link($area, $post_id, $link_text);
}


?>
