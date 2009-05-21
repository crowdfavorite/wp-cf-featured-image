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

### Using _Featured Image_ in the Admin

- The Featured Image plugin gives the user the ability to add a "Featured" image to certain areas of pages via the themes
- To select a featured image:
	1.  Log in to the WP Admin
	2.  Click on the "Edit" link under the "Posts" area of the left side navigation
	3.  Click on the Post Title for the post you would like to add a "Featured" image to to get to the post edit page
	4.  Add the image you would like to be the "Featured" image to the post
	5.  Click on the "Post Files" link under the "Featured Image" section of the screen to get the new image in the area to be selectable
	6.  Click on the image you would like to be the "Featured" image
	7.  Click the "Update Post" button to save the changes
	
### New Functionality

- The newest version of the Featured Image plugin gives the user much better handling of images
- There are now 3 different areas to find images to be featured for the current post
	1.  Post Files
		- The post files area shows all of the images attached to the current post.
		- When a new image is added to the post, click on the "Post Files" link to refresh the section and bring up the newly added image
	2.  Unattached Files
		- This section shows all of the files that are not attached to any posts
	3.  All Files
		- This section shows all of the files on the site.
		- This could potentially be a lot of images, so if "Loading..." is display for a long time, please be patient as it is trying to load all of the images on the site.
- The new functionality with these 3 sections means that a single image, or group of images can be used multiple times throughout the site as the featured image
