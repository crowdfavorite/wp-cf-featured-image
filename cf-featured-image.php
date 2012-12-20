<?php
/*
Plugin Name: CF Featured Image
Plugin URI: http://crowdfavorite.com 
Description: This plugin adds a field to the post page to select a thumbnail image to be added to the feature image area of the main page. 
Version: 1.6
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// 	ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

// Constants

define('CFFP_VERSION', '1.6');
define('CFFP_DIR', plugin_dir_path(__FILE__));
//plugin_dir_url seems to be broken for including in theme files
if (file_exists(trailingslashit(get_template_directory()).'plugins/'.basename(dirname(__FILE__)))) {
	define('CFFP_DIR_URL', trailingslashit(trailingslashit(get_bloginfo('template_url')).'plugins/'.basename(dirname(__FILE__))));
}
else {
	define('CFFP_DIR_URL', trailingslashit(plugins_url(basename(dirname(__FILE__)))));	
}
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
 * Featured Image - Init Functions
 * 
 */
function cffp_resources_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cffp_admin_css':
				cffp_admin_css();
				break;
			case 'cffp_admin_js':
				cffp_admin_js();
				break;
		}
	}
}
add_action('init', 'cffp_resources_handler', 1);

function cffp_request_handler() {
	if (!empty($_REQUEST['cffp'])) {
		$post_id = intval($_REQUEST['post_ID']);
		$img = $_REQUEST['cffp'];
		
		if (is_array($img)) {
			foreach ($img as $key => $id) {
				delete_post_meta($post_id, $key);
				if (!empty($id) && $id != 'NULL') {
					update_post_meta($post_id, $key, $id);
				}
			}
		}
	}
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cffp_get_images':
				if (isset($_POST['cffp_area']) && $_POST['cffp_area'] != '' && isset($_POST['cffp_att_id']) && $_POST['cffp_att_id'] != '' && isset($_POST['cffp_type']) && $_POST['cffp_type'] != '' && isset($_POST['cffp_post_id']) && $_POST['cffp_post_id'] != '') {
					cffp_get_images($_POST['cffp_area'], $_POST['cffp_att_id'], $_POST['cffp_type'], $_POST['cffp_post_id'], $_POST['cffp_last_selected'], $_POST['cffp_offset']);
				}
				die();
		}
	}
	
	// Add the post actions, if we need to
	if (apply_filters('cffp_meta_actions', false)) {
		add_action('admin_head','cffp_admin_head');
		wp_enqueue_script('cffp-admin', site_url('?cf_action=cffp_admin_js'), array('jquery'), CFFP_VERSION);
		wp_enqueue_style('cffp-admin', site_url('?cf_action=cffp_admin_css'), array(), CFFP_VERSION, 'screen');
	}
}
add_action('init', 'cffp_request_handler');

function cffp_meta_default_actions($val) {
	return (is_admin() && cffp_get_type() !== false);
}
add_filter('cffp_meta_actions', 'cffp_meta_default_actions', 1);

function cffp_get_type() {
	global $post, $pagenow;
	
	// We aren't going to do anything with this outside of the admin
	if (!is_admin()) { return false; }
	
	if (empty($post) || is_null($post)) {
		if (!empty($_GET['post']) && $_GET['post'] != 0) {
			return get_post_type(intval($_GET['post']));
		}
		else if (!empty($_GET['post_type'])) {
			return htmlentities($_GET['post_type']);
		}
		else if (!empty($_POST['post_id']) || !empty($_POST['post_ID'])) {
			$post_id = get_post_type(intval($_POST['post_id']));
			if (empty($post_id)) {
				$post_id = get_post_type(intval($_POST['post_ID']));
			}
			return $post_id;
		}
		else if (empty($_GET['post_type']) && !empty($pagenow) && $pagenow == 'post-new.php') {
			return 'post';
		}
		else if (empty($_GET['post_type']) && !empty($pagenow) && $pagenow == 'page-new.php') {
			// For WordPress 2.9- Compatability
			return 'page';
		}
	}
	else {
		if (!empty($post->post_type) && $post->post_type != 'revision') {
			return $post->post_type;
		}
	}
	return false;
}

function cffp_get_areas() {
	return apply_filters('cffp_add_areas',array());
}

function cffp_get_mime_types() {
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
	return apply_filters('cffp_mime_types', $cffp_mime_types);
}

/**
 * 
 * Featured Image - Admin Head Functions
 * 
 */

function cffp_admin_css() {
	header('Content-type: text/css');
	do_action('cffp-admin-css');
	echo file_get_contents(CFFP_DIR.'css/post-edit.css');
	die();
}

function cffp_admin_js() {
	header('Content-type: text/javascript');
	do_action('cffp-admin-js');
	echo file_get_contents(CFFP_DIR.'js/post-edit.js');
	die();
}

function cffp_admin_head() {
	$areas = cffp_get_areas();
	if (is_array($areas) && !empty($areas)) {
		foreach ($areas as $key => $area) {
			$area_id = sanitize_title('cffp-'.$key);
			if (!is_array($area['attach_to'])) {
				$area['attach_to'] = array('post');
			}
			foreach ($area['attach_to'] as $here) {
				if ($here == cffp_get_type()) {
					add_meta_box($area_id, htmlspecialchars($area['name']), 'cffp_edit_post', $here, 'normal', 'high');
				}
			}		
		}
	}
}

/**
 * 
 * Featured Image - Admin Display Functions
 * 
 */

function cffp_edit_post($post,$area) {
	global $wpdb;
	$cffp_areas = cffp_get_areas();
	
	$post_imgs = '';
	$area_info = $cffp_areas[str_replace('cffp-','',$area['id'])];

	$cffp_id = '_'.$area['id'];
	$cffp_description = $area_info['description'];

	$cffp_att_id = get_post_meta($post->ID, $cffp_id, true);
	if ($cffp_att_id == '') {
		$cffp_att_id = 0;
	}

	$id_string = '';
	if ($post->ID != 0) {
		$id_string = '= '.$post->ID;
	}
	
	$post_imgs = cffp_get_img_attachments($id_string, $cffp_att_id, $cffp_id, 'post', $area_info['mime_types']);
	// $selected_img = cffp_get_img_attachments_selected($cffp_att_id, $cffp_id);
	
	if (empty($post_imgs['html']) || $post->ID == 0) {
		$post_container_width = 170;
	}
	else {
		$post_container_width = ($post_imgs['count'] + 1) * 170;
	}
	
	if ($cffp_att_id == 'NULL' || $cffp_att_id == 0 && $cffp_att_id != '') {
		$noimg_checked = ' checked="checked"';
		$noimg_selected = ' cffp_selected';
	}
	else {
		$noimg_checked = '';
		$noimg_selected = '';
	}

	$no_img .= '
			<div class="cffp_container'.$noimg_selected.'">
				<label class="cffp_img" for="cffp-'.$cffp_id.'-leadimg-0">'.__('No Image').'</label>
				<div class="cffp_radio">
					<input type="radio" name="cffp['.$cffp_id.']" id="cffp-'.$cffp_id.'-leadimg-0" value="NULL"'.$noimg_checked.' />
				</div>
			</div>
	';
	
	print('
	<div class="cffp_help">
		<p>'.$cffp_description.' <a onclick="cffp_help_text(\''.$cffp_id.'\')"><em>Help</em></a></p>
		<p id="cffp_help_text_'.$cffp_id.'" style="display:none;"><em>'.__('To add new files, upload them via the Media Gallery. Once files have been uploaded, click the Post Files button to refresh.').'</em></p>
		<p><a id="post-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'post\',\''.$post->ID.'\', \'0\')" class="cffp_type_active" cffp_class="post-click" cffp_last_checked="'.$cffp_att_id.'">Post Files</a><a id="other-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'other\',\''.$post->ID.'\', \'0\')" cffp_class="other-click" cffp_last_checked="'.$cffp_att_id.'">Unattached Files</a><a id="all-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'all\',\''.$post->ID.'\', \'0\')" cffp_class="all-click" cffp_last_checked="'.$cffp_att_id.'">All Files</a></p>
	</div>
	<div class="cffp_overall">
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
					if (empty($post_imgs['html']) || $post->ID == 0) {
						echo $no_img;
					}
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
	<div class="clear"></div>
	');
}

function cffp_get_images($area, $att_id, $type = 'all', $post_id, $last_selected, $offset = 0) {
	$cffp_areas = cffp_get_areas();
	
	$area_info = $cffp_areas[str_replace('_cffp-','',$area)];
	
	$imgs = array();
	
	if ($type == 'all') {
		$imgs = cffp_get_img_attachments('', $att_id, $area, $type, $area_info['mime_types'], $last_selected, $offset, $post_id);
	}
	if ($type == 'other') {
		$imgs = cffp_get_img_attachments('<= 0', $att_id, $area, $type, $area_info['mime_types'], $last_selected, $offset, $post_id);
	}
	if ($type == 'post') {
		global $post;
		$imgs = cffp_get_img_attachments('= '.$post_id, $att_id, $area, $type, $area_info['mime_types'], $last_selected, $offset, $post_id);
	}
	
	
	if (is_array($imgs) && !empty($imgs)) {
		$container_width = $imgs['count']*160;
		if (!empty($imgs['extra'])) {
			$container_width += 160;
		}
		print('
			<div id="cffp_'.$type.'_imgs_'.$area.'" class="cffp_images" style="width:'.$container_width.'px;">
				');
				if (!empty($imgs['selected'])) {
					echo $imgs['selected'];
				}
				if (!empty($imgs['html'])) {
					echo $imgs['html'];
				}
				if (!empty($imgs['extra'])) {
					echo $imgs['extra'];
				}
				print('
			</div>
		');
	}
	die();
}

function cffp_get_img_attachments($id_string, $cffp_att_id, $cffp_id, $type, $mime_types = array(), $last_selected = 0, $offset = 0, $post_id = 0) {
	global $wpdb;
	$cffp_mime_types = cffp_get_mime_types();
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
				$mime_query .= ' post_mime_type LIKE "'.$query_mime_type.'"';
			}
			else {
				$mime_query .= ' OR post_mime_type LIKE "'.$query_mime_type.'"';
			}
		}
	}
	else if (is_array($cffp_mime_types['images']) && !empty($cffp_mime_types['images'])) {
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
	
	// Setup the vars
	$all_img = '';
	$no_img = '';
	$extra_img = '';
	$selected_img = '';
	
	// Lets deal with the No Image area
	if (($cffp_att_id == 'NULL' || $cffp_att_id == 0 || $cffp_att_id == '') && $cffp_att_id == $last_selected) {
		$noimg_checked = ' checked="checked"';
	}
	else {
		$noimg_checked = '';
	}
	if ($cffp_att_id == 'NULL' || $cffp_att_id == 0 || $cffp_att_id == '') {
		$noimg_selected = ' cffp_selected';
	}
	else {
		$noimg_selected = '';
	}
	
	$no_img .= '
			<div class="cffp_container'.$noimg_selected.'">
				<label class="cffp_img" for="cffp-'.$cffp_id.'-leadimg-0">'.__('No Image').'</label>
				<div class="cffp_radio">
					<input type="radio" class="cffp_radios" cffp_type="'.$type.'" name="cffp['.$cffp_id.']" id="cffp-'.$cffp_id.'-leadimg-0" value="NULL"'.$noimg_checked.' />
				</div>
			</div>
	';
	
	// Now lets deal with the selected image if we have one
	if ($cffp_att_id != 'NULL' && $cffp_att_id != 0 && $cffp_att_id != '') {
		$count++;
		$selected_img .= cffp_get_img_attachments_selected($cffp_att_id, $cffp_id, $type);
	}
	
	$offset_text = '';
	if (!$offset) {
		$offset_text = ' LIMIT 0, 50';
	}
	else {
		$offset_text = ' LIMIT '.$offset.', 50';
	}
	
	$cffp_attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' $parent AND post_mime_type NOT LIKE '' AND($mime_query) ORDER BY post_title ASC $offset_text", ARRAY_A);
	$cffp_total_count = $wpdb->get_results("SELECT count(ID) as total FROM $wpdb->posts WHERE post_type LIKE 'attachment' $parent AND post_mime_type NOT LIKE '' AND($mime_query) ORDER BY post_title ASC", ARRAY_A);

	if (count($cffp_attachments)) {
		$count = $count+count($cffp_attachments);
		
		if (($count+$offset) < $cffp_total_count[0]['total']) {
			$offset += 50;
			$extra_img = '
				<div class="cffp_container cffp_container_more">
					<a class="cffp_more_img" href="#" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\''.$type.'\',\''.$post_id.'\', \''.$offset.'\'); return false;">'.__('View More', 'cffp').'</a>
					<input type="hidden" id="cffp_offset" value="'.$offset.'" />
				</div>
			';
		}
		
		foreach ($cffp_attachments as $cffp_attachment) {
			$image_link = wp_get_attachment_image_src($cffp_attachment['ID']);

			if ($cffp_att_id != $cffp_attachment['ID']) {
				$label = '';
				$img_checked = '';
				
				if ($cffp_attachment['ID'] == $last_selected) {
					$img_checked = ' checked="checked"';
				}
				
				if ($cffp_attachment['post_mime_type'] == 'application/octet-stream') {
					$label = '<label class="cffp_img" style="background: transparent url('.CFFP_DIR_URL.'images/zip.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'">'.$cffp_attachment['post_title'].'</label>';
				}
				else if ($cffp_attachment['post_mime_type'] == 'application/pdf') {
					$label = '<label class="cffp_img" style="background: transparent url('.CFFP_DIR_URL.'images/pdf.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'">'.$cffp_attachment['post_title'].'</label>';
				}
				else {
					$label = '<label class="cffp_img" style="background: transparent url('.$image_link[0].') no-repeat scroll center center; width: 150px; height: 150px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'"></label>';
				}
				$all_img .= '
					<div class="cffp_container">
						'.$label.'
						<div class="cffp_radio">
							<input type="radio" class="cffp_radios" cffp_type="'.$type.'" name="cffp['.$cffp_id.']" id="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'" value="'.$cffp_attachment['ID'].'"'.$img_checked.' />
						</div>
					</div>
				';
			}
		}
	}
	$return = $no_img.$selected_img.$all_img;
	return array('html' => $return, 'count' => $count, 'extra' => $extra_img);
}

function cffp_get_img_attachments_selected($cffp_att_id, $cffp_id, $type) {
	global $wpdb;
	$return = '';
	
	$cffp_selected = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' AND ID = '%d' ORDER BY post_title ASC", $cffp_att_id));
	foreach ($cffp_selected as $selected) {
		$image_link = wp_get_attachment_image_src($selected->ID);
		$image_meta = get_post_meta($selected->ID, '_wp_attachment_metadata', true);
		
		$label = '';
		if ($selected->post_mime_type == 'application/octet-stream') {
			$label = '<label class="cffp_img" style="background: transparent url('.CFFP_DIR_URL.'images/zip.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$selected->ID.'">'.$selected->post_title.'</label>';
		}
		else if ($selected->post_mime_type == 'application/pdf') {
			$label = '<label class="cffp_img" style="background: transparent url('.CFFP_DIR_URL.'images/pdf.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$selected->ID.'">'.$selected->post_title.'</label>';
		}
		else {
			$label = '<label class="cffp_img" style="background: transparent url('.$image_link[0].') no-repeat scroll center center; width: 150px; height: 150px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$selected->ID.'"></label>';
		}

		$return .= '
			<div class="cffp_container cffp_selected">
				'.$label.'
				<div class="cffp_radio">
					<input type="radio" class="cffp_radios" cffp_type="'.$type.'" name="cffp['.$cffp_id.']" id="cffp-'.$cffp_id.'-leadimg-'.$selected->ID.'" value="'.$selected->ID.'" checked="checked" />
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

/**
 * Get the attachment src array for the featured image.
 * @return array | null
 */
function cffp_get_attachment_image_src($args) {
	$default_args = array(
		'area' => false, // required
		'size' => 'thumbnail',
		'post_id' => false
	);
	
	$args = wp_parse_args($args, $default_args);
	
	extract($args);
	
	// If we have not specified post id, then use the current post
	if (!$post_id) {
		global $post;
		$post_id = $post->ID;
	}

	if ($area) {
		$image = wp_get_attachment_image_src(get_post_meta($post_id, '_cffp-'.$area, true), $size);
		return $image;
	}
	return null;
}

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
		$cffp_id = get_post_meta($post_id, '_cffp-'.$area, true);
		$cffp_image = wp_get_attachment_image_src($cffp_id, $size);
		if ($cffp_image[0] != '') {
			return '<img src="'.$cffp_image[0].'" alt="'.attribute_escape(get_the_title($cffp_id)).'" />';
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


// README HANDLING

/**
 * Enqueue the readme function
 */
function cffp_add_readme() {
	if(function_exists('cfreadme_enqueue')) {
		cfreadme_enqueue('cf-featured-image','cffp_readme');
	}
}
add_action('admin_init','cffp_add_readme');

/**
 * return the contents of the links readme file
 * replace the image urls with full paths to this plugin install
 *
 * @return string
 */
function cffp_readme() {
	$file = realpath(dirname(__FILE__)).'/README.txt';
	if(is_file($file) && is_readable($file)) {
		$markdown = file_get_contents($file);
		$markdown = preg_replace('|!\[(.*?)\]\((.*?)\)|','![$1]('.WP_PLUGIN_URL.'/cf-featured-image/$2)',$markdown);
		return $markdown;
	}
	return null;
}

?>
