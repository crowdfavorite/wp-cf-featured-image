Crowd Favorite Featured Image Plugin
====================================

## Description

This plugin shows the WordPress Media Gallery images associated with a post and allows you to select one as a "Featured Image". The image can then be output using a template tag or referenced in other programatic ways. _Featured Image_ also allows for multiple featured images to be associated with a post, through the creation of multiple Featured Image pickers (each with it's own box on the post page).

## Installation and Setup

1. Download the plugin archive and expand it (you've likely already done this).

2. Open cf-featured-image.php to define the distinct image areas of the plugin.

	1. Toward the top of the file you will see a block of code that looks like this:
	
			$cffp_areas = array(
					'area1' => array(
							'name' => 'Area 1',
							'description' => 'Description of Area 1 here'
						),
					'area2' => array(
							'name' => 'Area 2',
							'description' => 'Description of Area 2 here'
						),
			);
		
		Each of these area arrays will become a distinct _Featured Image_ picker. Give each a unique ID (the array name), a name (usually associated with how you will use that image in the template) and some descriptive help text. Add or delete arrays as necessary. 
		
2. Upload the cf-featured-image file to your wp-content/plugins directory.

3. Go to the Plugins page in your WordPress Administration area and click 'Activate' for _Crowd Favorite Featured Image_.

5. Congratulations, you've just installed _Crowd Favorite Featured Image_.

### Implementing the template tag

_Featured Image_ has four main template tags:

- `cffp_get_img($post_id, $size, $area)`: `returns` the path to the area's featured image
- `cffp_img($post_id, $size, $area)`: `echos` the path to the area's featured image
- `cffp_get_img_tag($post_id, $size, $area)`: `returns` an image tag with the path to the area's featured image
- `cffp_img_tag($post_id, $size, $area)`: `echos` an image tag with the path to the area's featured image

Each template tags takes four parameters:

- The post ID (in the loop this can be passed through `get_the_ID()`)
- The desired image size (`thumbnail`, `medium`, or `full`)
- The image area id

## Using _Featured Image_ in the Admin

Upload images via the standard WordPress Media Gallery. Refresh the browser window to see added images. After uploading and refreshing, select the image that you wish to use as the featured image and click "Update Post". Done!