<?php
/*
Plugin Name: Hidden Featured Image
Plugin URI: https://gdream.dev
Description: Hide features image in page/posts
Author: GDream Studio
Version: 1.0
Author URI: https://gdream.dev
*/

add_action('add_meta_boxes', 'hfi_add_checkbox_to_posts');
add_action('save_post', 'hfi_save_postdata');
add_action('wp_head', 'hfi_hide_featured_image');
add_action('init', 'hfi_init');

function hfi_init() {
    global $hfi_post_types;

    // Initialize targeted post types
    $hfi_post_types = array('post', 'page');
}

function hfi_add_checkbox_to_posts() {
    global $hfi_post_types;

    // Add meta box for each post type
    foreach ($hfi_post_types as $post_type) {
        add_meta_box('hide_featured', 'Hide featured image?', 'hfi_display_checkbox', $post_type, 'side');
    }
}

function hfi_display_checkbox($post) {
    // Use nounce for later check that the save happened while being on the page
    wp_nonce_field(plugin_basename(__FILE__), $post->post_type . '_noncename');
    // Retrieve the _hide_featured field from the post
    $hide_featured = get_post_meta($post->ID, '_hide_featured', true);
?>
    <input type="checkbox" id="_hide_featured" name="_hide_featured" value="1" <?php checked($hide_featured, 1); ?>>
    <label for="_hide_featured">Hidden</label><br>
<?php
}

function hfi_save_postdata($post_id) {
    global $hfi_post_types;

    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
        return;
    }

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (!wp_verify_nonce(@$_POST[$_POST['post_type'] . '_noncename'], plugin_basename(__FILE__))) {
        return;
    }

    // OK, nonce has been verified and now we can save the data according the the capabilities of the user
    if (in_array($_POST['post_type'], $hfi_post_types) && current_user_can('edit_page', $post_id)) {
        $hide_featured = (isset($_POST['_hide_featured']) && $_POST['_hide_featured'] == 1) ? '1' : '2';
        update_post_meta($post_id, '_hide_featured', $hide_featured);
    }
}

function hfi_hide_featured_image() {
    if (is_single() || is_page()) {
        $hide = false;
        $hide_image = get_post_meta(get_the_ID(), '_hide_featured', true);
        $hide_image_b = $hide_image == 1;

        $hide = (is_page() && $hide_image_b) ? true : $hide; 
        $hide = (is_singular('post') && $hide_image_b) ? true : $hide; 
        $hide = (isset($hide_image) && $hide_image_b) ? true : $hide;
      
        if ($hide) { ?>
            <style>
                .featured-image { display: none !important; }          
            </style><?php
        }
    }
}
?>