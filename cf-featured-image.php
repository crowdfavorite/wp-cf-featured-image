<?php
/*
Plugin Name: CF Featured Image
Plugin URI: http://crowdfavorite.com 
Description: This plugin adds a field to the post page to select a thumbnail image to be added to the feature image area of the main page. 
Version: 1.5
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// 	ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}


// README HANDLING
	add_action('admin_init','cffp_add_readme');

	/**
	 * Enqueue the readme function
	 */
	function cffp_add_readme() {
		if(function_exists('cfreadme_enqueue')) {
			cfreadme_enqueue('cf-featured-image','cffp_readme');
		}
	}
	
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
		$post_id = intval($_REQUEST['post_ID']);
		$img = $_REQUEST['cffp'];
		foreach ($img as $key => $id) {
			delete_post_meta($post_id, $key);
			if (!empty($id) && $id != 'NULL') {
				update_post_meta($post_id, $key, $id);
			}
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
					cffp_get_images($_POST['cffp_area'], $_POST['cffp_att_id'], $_POST['cffp_type'], $_POST['cffp_post_id'],$_POST['cffp_last_selected']);
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
		height:218px;
		overflow:auto;
	}
	.cffp_container {
		text-align: center; 
		width: 160px; 
		float: left;
	}
	#cffp_ajax_spinner {
		width:160px;
		line-height:160px;
		float:left;
		text-align:center;
		vertical-align:middle;
	}
	.cffp_help {
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
		margin:4px;
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
		
		var select_val = jQuery('#'+type+'-click-'+area).attr('cffp_last_checked');
		
		jQuery.post('<?php echo $url; ?>', {
			cf_action: 'cffp_get_images',
			cffp_area: area,
			cffp_att_id: att_id,
			cffp_post_id: postID,
			cffp_last_selected: select_val,
			cffp_type: type
		},function(data){
			data = jQuery(data);
			jQuery('input[type="radio"]', data).each(function() {
				jQuery(this).change(function(){
					var type = jQuery(this).attr('cffp_type');
					var area = jQuery(this).parent().parent().parent().attr('id');
					
					area = area.replace('cffp_'+type+'_imgs__','');
					cffp_last_checked(type, area);
				});
			});
			
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
			jQuery('#'+type+'-click-'+area).attr('class','cffp_type_active');
		});
	}
	function cffp_last_checked(type, area) {
		var last_val = jQuery('#'+area+' .cffp_overall input[type="radio"]:checked').val();
		if (last_val == null) {
			last_val = 0;
		}
		jQuery('#'+type+'-click-_'+area).attr('cffp_last_checked',last_val);
	}
	function cffp_help_text(area) {
		jQuery('#cffp_help_text_'+area).slideToggle();
	}
	<?php
	die();
}

function cffp_admin_head() {
	global $cffp_areas;

	if (is_array($cffp_areas) && !empty($cffp_areas)) {
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
}

// Grab the current page file
global $pagenow;
// An array for checking to see if the content should be added
$cffp_addition = array(
	'post-new.php',
	'page-new.php',
	'post.php',
	'page.php'
);
// Checking to see if the current page is in our addition array
if (in_array($pagenow, $cffp_addition)) {
	add_action('admin_head','cffp_admin_head');
}

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
		<p><a id="post-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'post\',\''.$post->ID.'\')" class="cffp_type_active" cffp_class="post-click" cffp_last_checked="'.$cffp_att_id.'">Post Files</a><a id="other-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'other\',\''.$post->ID.'\')" cffp_class="other-click" cffp_last_checked="'.$cffp_att_id.'">Unattached Files</a><a id="all-click-'.$cffp_id.'" onclick="cffp_getImgs(\''.$cffp_id.'\',\''.$cffp_att_id.'\',\'all\',\''.$post->ID.'\')" cffp_class="all-click" cffp_last_checked="'.$cffp_att_id.'">All Files</a></p>
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
	<div class="cffp_clear"></div>
	');
}

function cffp_get_images($area, $att_id, $type = 'all', $post_id, $last_selected) {
	global $cffp_areas;
	
	$area_info = $cffp_areas[str_replace('_cffp-','',$area)];
	
	if ($type == 'all') {
		$imgs = cffp_get_img_attachments('', $att_id, $area, $type, $area_info['mime_types'], $last_selected);
	}
	if ($type == 'other') {
		$imgs = cffp_get_img_attachments('<= 0', $att_id, $area, $type, $area_info['mime_types'], $last_selected);
	}
	if ($type == 'post') {
		global $post;
		$imgs = cffp_get_img_attachments('= '.$post_id, $att_id, $area, $type, $area_info['mime_types'], $last_selected);
	}
	$container_width = ($imgs['count'] + 1) * 170;
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

function cffp_get_img_attachments($id_string, $cffp_att_id, $cffp_id, $type, $mime_types = array(), $last_selected = 0) {
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
	
	// Setup the vars
	$all_img = '';
	$no_img = '';
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
	
	$cffp_attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' $parent AND post_mime_type NOT LIKE '' AND($mime_query) ORDER BY post_title ASC", ARRAY_A);
	if (count($cffp_attachments)) {
		$count = $count+count($cffp_attachments);
		
		foreach ($cffp_attachments as $cffp_attachment) {
			$image_link = wp_get_attachment_image_src($cffp_attachment['ID']);

			if ($cffp_att_id != $cffp_attachment['ID']) {
				$label = '';
				$img_checked = '';
				
				if ($cffp_attachment['ID'] == $last_selected) {
					$img_checked = ' checked="checked"';
				}
				
				if ($cffp_attachment['post_mime_type'] == 'application/octet-stream') {
					$label = '<label class="cffp_img" style="background: transparent url('.trailingslashit(get_bloginfo('wpurl')).'/wp-content/plugins/cf-featured-image/images/zip.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'">'.$cffp_attachment['post_title'].'</label>';
				}
				else if ($cffp_attachment['post_mime_type'] == 'application/pdf') {
					$label = '<label class="cffp_img" style="background: transparent url('.trailingslashit(get_bloginfo('wpurl')).'/wp-content/plugins/cf-featured-image/images/pdf.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$cffp_attachment['ID'].'">'.$cffp_attachment['post_title'].'</label>';
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
	return array('html' => $return, 'count' => $count);
}

function cffp_get_img_attachments_selected($cffp_att_id, $cffp_id, $type) {
	global $wpdb;
	$return = '';
	
	$cffp_selected = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type LIKE 'attachment' AND ID = '$cffp_att_id' ORDER BY post_title ASC");
	foreach ($cffp_selected as $selected) {
		$image_link = wp_get_attachment_image_src($selected->ID);
		$image_meta = get_post_meta($selected->ID, '_wp_attachment_metadata', true);
		
		$label = '';
		if ($selected->post_mime_type == 'application/octet-stream') {
			$label = '<label class="cffp_img" style="background: transparent url('.trailingslashit(get_bloginfo('wpurl')).'/wp-content/plugins/cf-featured-image/images/zip.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$selected->ID.'">'.$selected->post_title.'</label>';
		}
		else if ($selected->post_mime_type == 'application/pdf') {
			$label = '<label class="cffp_img" style="background: transparent url('.trailingslashit(get_bloginfo('wpurl')).'/wp-content/plugins/cf-featured-image/images/pdf.png) no-repeat scroll center top; width: 150px; height: 12px; line-height:1; padding:148px 0 0 10px;" for="cffp-'.$cffp_id.'-'.$type.'-leadimg-'.$selected->ID.'">'.$selected->post_title.'</label>';
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


?>
