<?php

/*
 * Plugin Name: SEO Optimizer
 * Description: Meta and title description templating, for snippets in the search results.  SEO Optimizer is simple but powerful SEO plugin for beginners level.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 5.7
 * Requires PHP: 7.0
 * Author: Ajay Kumar
 * Author URI: https://ajkumar.in/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: seo-optimizer
 */


// exit if file is called directly
if (!defined('ABSPATH')) {
    exit;
}

// if admin area
if (is_admin()) {

    // include dependencies
    require_once plugin_dir_path(__FILE__) . 'admin/admin-menu.php';
    require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';
}

// define some constant only for database use

define('SEO_OPTIMIZER_DESC_KEY', '_seooptimizer_wp_metadesc');
define('SEO_OPTIMIZER_TITLE_KEY', '_seooptimizer_wp_title');


// enqueue scripts
if (!function_exists('seo_optimizer_ajax_admin_enqueue_scripts')) {
    function seo_optimizer_ajax_admin_enqueue_scripts($hook)
    {
        // check if our page
        if ($hook != 'toplevel_page_seo-optimizer') {
            return;
        }
        // define script url
        $script_url = plugins_url('/admin/js/ajax-admin.js', __FILE__);
        $sytle_url = plugins_url('/admin/css/style.css', __FILE__);

        // enqueue script
        wp_enqueue_script('ajax-admin', $script_url, array('jquery'), '1.0', 'yes');

        // enqueue style
        wp_enqueue_style('style', $sytle_url, [], '1.0', 'all');

        // create nonce for security purpose
        $nonce = wp_create_nonce('ajax_admin');

        // define script
        $script = ['nonce' => $nonce];

        // localize script
        wp_localize_script('ajax-admin', 'ajax_admin', $script);
    }
    add_action('admin_enqueue_scripts', 'seo_optimizer_ajax_admin_enqueue_scripts');
}

//process ajax suggestion
if (!function_exists('seo_optimizer_ajax_admin_handler')) {
    function seo_optimizer_ajax_admin_handler()
    {
        global $wpdb;
        //check nonce
        check_ajax_referer('ajax_admin', 'nonce');

        // check user
        if (!current_user_can('manage_options')) return;

        // user searh term and sanitize 
        $search_text = $_POST['search'] ? sanitize_text_field($_POST['search']) : '';
        $search_text = '%' . $search_text . '%';
        // $wpdb->show_errors();
        $result = $wpdb->get_results("SELECT `id`,`post_title` FROM `$wpdb->posts` WHERE (`post_type` = 'page' OR `post_type` = 'post') AND `post_status` = 'publish' AND `post_title` LIKE '$search_text'");
        // print_r($result);
        // echo json_encode($result);
        $output = "<ul class='response-list-ul'>";
        // print_r($result);
        foreach ($result as $value) {
            $output .= "<li class='response-list' data-id='" . esc_attr($value->id) . "' onclick='fill(this)'><a href='javascript:void(0)' data-id='" . esc_attr($value->id) . "'>" . esc_html($value->post_title) . "</a></li>";
        }
        $output .= "</ul>";
        echo $output;
        // end processing
        wp_die();
    }
    // ajax hook for logged-in users - wp_ajax_{action}
    // ajax live search ajax 
    add_action('wp_ajax_admin_hook', 'seo_optimizer_ajax_admin_handler');
}




if (!function_exists('seo_optimizer_save_form_handler')) {
    function seo_optimizer_save_form_handler()
    {
        global $wpdb;
        //check nonce
        check_ajax_referer('ajax_admin', 'nonce');
        // check user
        if (!current_user_can('manage_options')) return;

        //getting post ID
        $id = $_POST['id'] ? (int)$_POST['id'] : '';

        //checking post ID, empty and type must be int
        if (empty($id) || filter_var($id, FILTER_VALIDATE_INT === false)) {
            wp_send_json_error('Something went wrong. Please refresh & try again.');
        }

        // getting post title for page title
        $title = $_POST['title'] ? sanitize_text_field($_POST['title']) : '';

        // checking for empty post meta title
        if (empty($title)) {
            wp_send_json_error('Title field is required');
        }

        // getting post meta description
        $description = $_POST['description'] ? sanitize_text_field($_POST['description']) : '';

        // checking for empty post meta description
        if (empty($description)) {
            wp_send_json_error('Meta description field is required');
        }

        // getting flag for reference
        $flag = $_POST['flag'] ? strtolower(sanitize_text_field($_POST['flag'])) : '';
        $flag_expected_value = ['post', 'update'];

        // checking for empty and expected value
        if (empty($flag) || !in_array($flag, $flag_expected_value)) {
            wp_send_json_error('Something went wrong. Please refresh & try again.');
        }

        // new meta description and title
        if ($flag === 'post') {

            // add title post meta
            add_post_meta($id, SEO_OPTIMIZER_TITLE_KEY, $title, true);

            // add description post meta
            add_post_meta($id, SEO_OPTIMIZER_DESC_KEY, $description, true);

            // success response
            wp_send_json_success('Successfully saved');
        } elseif ($flag === 'update') {

            // update meta description
            update_post_meta($id, SEO_OPTIMIZER_DESC_KEY, $description);
            // update title
            update_post_meta($id, SEO_OPTIMIZER_TITLE_KEY, $title);
            // success response
            wp_send_json_success('Successfully updated');
        } else {
            // failed response
            wp_send_json_error('Something went wrong. Please refresh & try again.');
        }
        //end processing
        wp_die();
    }
    // form data save/update ajax
    add_action('wp_ajax_save_form', 'seo_optimizer_save_form_handler');
}

if (!function_exists('seo_optimizer_process_form_handler')) {
    function seo_optimizer_process_form_handler()
    {
        global $wpdb;
        //check nonce
        check_ajax_referer('ajax_admin', 'nonce');
        // check user
        if (!current_user_can('manage_options')) return;

        // getting post id
        $id = $_POST['id'] ? (int)$_POST['id'] : '';

        //checking post ID,
        if (empty($id)) {
            wp_send_json_error('Page or Post not found');
        } elseif (filter_var($id, FILTER_VALIDATE_INT === false)) {
            // type must be int
            wp_send_json_error('Something went wrong. Please refresh & try again.');
        }

        // getting post title
        $search = $_POST['search'] ? sanitize_text_field($_POST['search']) : '';

        // checking for empty post title
        if (empty($search)) {
            wp_send_json_error('Search field is required');
        }


        // search status based on search term
        $status = $wpdb->get_results("SELECT `id` FROM `$wpdb->posts` WHERE `id` = $id AND `post_title` = '$search'");
        if (!empty($status)) {

            // If meta description or title already saved in Database
            $data['desc'] = get_post_meta($id, SEO_OPTIMIZER_DESC_KEY, true);
            $data['title'] = get_post_meta($id, SEO_OPTIMIZER_TITLE_KEY, true);
            wp_send_json_success($data);
        } else {
            // for given ID and Title post or page not found
            wp_send_json_error('Page or Post not found');
        }

        // End processing
        wp_die();
    }
    // process the form and get meta data if already exsits
    add_action('wp_ajax_process_form', 'seo_optimizer_process_form_handler');
}



if (!function_exists('seo_optimizer_display_title_description_frontend')) {
    function seo_optimizer_display_title_description_frontend()
    {
        global $post;

        if (get_post_type() == 'post' || get_post_type() == 'page') {
            //get current post or page title
            $custom_title = get_post_meta($post->ID, SEO_OPTIMIZER_TITLE_KEY, true) ?: null;

            //get current post or page description
            $desc = get_post_meta($post->ID, SEO_OPTIMIZER_DESC_KEY, true) ?: null;

            // check for current page or post title or description added or not by our plugin
            if ($custom_title !== null || $desc !== null) {
                // remove default title by wordpress
                if (has_action('wp_head', '_wp_render_title_tag') == 1) {
                    remove_action('wp_head', '_wp_render_title_tag', 1);
                }

?>
                <!-- Generated by SEO Optimizer  - https://ajkumar.in/ -->
                <?php
                // print title to front end
                echo '<title>' . esc_html($custom_title) . '</title>' . "\n";
                //print description to front end
                echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
                //og tags
                echo '<meta property="og:locale" content="' . get_locale() . '" />' . "\n";
                echo '<meta property="og:type" content="website" />' . "\n";
                echo '<meta property="og:title" content="' . esc_attr($custom_title) . '" />' . "\n";
                echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
                echo '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />' . "\n";
                echo '<meta property="og:url" content="' . get_permalink() . '" />' . "\n";
                // has site icon
                if (has_site_icon()) {
                    echo '<meta property="og:image" content="' . esc_url(get_site_icon_url()) . '" />' . "\n";
                    echo '<meta property="og:image:alt" content="' . get_bloginfo('name') . ' site logo' . '" />' . "\n";
                }
                // twitter tag
                echo
                '<meta name="twitter:card" content="summary_large_image" />' . "\n";
                echo '<meta name="twitter:url" content="' . get_permalink() .
                    '" />' . "\n";
                echo
                '<meta name="twitter:title" content="' . esc_attr($custom_title) . '" />' . "\n";
                echo
                '<meta name="twitter:description" content="' . esc_attr($desc) . '" />' . "\n";

                //has site icon
                if (has_site_icon()) {
                    echo '<meta name="twitter:image" content="' . esc_url(get_site_icon_url()) . '" />' . "\n";
                    echo '<meta name="twitter:image:alt" content="' . get_bloginfo('name') . ' site logo' . '" />' . "\n";
                }
                ?>
                <!-- End SEO Optimizer -->
<?php
            }
        }
    }
    add_action('wp_head', 'seo_optimizer_display_title_description_frontend', 0);
}
