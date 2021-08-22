<?php
// Seo Optimizer - Settings Page

// exit if file is called directly
if (!defined('ABSPATH')) {
    exit;
}

// Display the plugin settings page
if (!function_exists('seo_optimizer_display_settings_page')) {
    function seo_optimizer_display_settings_page()
    {
        // check if user is allowed access
        if (!current_user_can('manage_options')) return;
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form class="seo-form">
                <div class="success-message"></div>
                <div class="form-group search-wrapper">
                    <input type="text" name="search" id="search" autocomplete="off" placeholder="Search Page or Post">
                    <div id="response" class="show-response"></div>
                    <button type="button" class="btn-next" id="btn-next">Next</button>
                </div>
                <input type="hidden" name="id" value="">
                <input type="hidden" name="method" value="post">
                <div class="meta-wrapper d-none">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" placeholder="Title" id="title">
                    </div>
                    <div class="form-group">
                        <label for="description">Meta Description</label>
                        <textarea name="description" placeholder="Meta Description" id="description"></textarea>
                    </div>
                    <div class="form-group">
                        <button type="button" id="btn-back" class="btn-back">Back</button>
                        <div class="meta-btn-container clear-fix">
                            <button type="button" id="modal-preview">Preview</button>
                            <button type="submit" id="submit-btn">Save</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        <div id="my-modal" class="modal">

            <!-- Modal content -->
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="site-url"><cite><?php echo site_url() ?></cite></div>
                <div class="site-title-wrapper">
                    <span class="site-title" id="canvas"></span>
                </div>
                <div class="site-meta-description-wrapper">
                    <span class="site-meta-description">
                    </span>
                </div>
            </div>

        </div>
        <script>
            function fill(value) {
                jQuery("#search").val(value.textContent);
                jQuery('input[name="id"]').val(value.dataset.id);
                jQuery("#response").hide();
            }
        </script>
<?php
    }
}
