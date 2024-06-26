<?php
/*
Plugin Name: WP Hashtags
Description: タグのテキスト部分の最初に＃を付与します。
Version: 1.0.0
Author: Kasiri
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add menu link in the admin settings
function wp_hashtags_add_settings_page() {
    add_options_page(
        'WP Hashtags Settings',
        'WP Hashtags',
        'manage_options',
        'wp-hashtags',
        'wp_hashtags_settings_page'
    );
}
add_action('admin_menu', 'wp_hashtags_add_settings_page');

// Settings page content
function wp_hashtags_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP Hashtags</h1>
        
        <h2>タグの一括変更</h2>
        <form method="post" action="">
            <input type="hidden" name="wp_hashtags_nonce" value="<?php echo wp_create_nonce('wp-hashtags-nonce'); ?>" />
            <p><input type="submit" name="add_hashtags" class="button-primary" value="タグ名の先頭に＃を付与して保存" /></p>
        </form>
        
        <form method="post" action="">
            <input type="hidden" name="wp_hashtags_nonce" value="<?php echo wp_create_nonce('wp-hashtags-nonce'); ?>" />
            <p><input type="submit" name="remove_hashtags" class="button-primary" value="タグ名の先頭の＃を削除して保存" /></p>
        </form>
        
    </div>
    <?php
}

// Process bulk operations for tags
function wp_hashtags_process_bulk_operations() {
    if (isset($_POST['add_hashtags']) || isset($_POST['remove_hashtags'])) {
        if (!isset($_POST['wp_hashtags_nonce']) || !wp_verify_nonce($_POST['wp_hashtags_nonce'], 'wp-hashtags-nonce')) {
            return;
        }
        
        $tags = get_terms(array(
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
        ));
        
        if (is_wp_error($tags)) {
            return;
        }
        
        foreach ($tags as $tag) {
            $tag_name = $tag->name;
            
            if (isset($_POST['add_hashtags'])) {
                // Add # to the beginning of tag name
                if (substr($tag_name, 0, 1) !== '#') {
                    $updated_tag_name = '#' . $tag_name;
                } else {
                    $updated_tag_name = $tag_name; // Already starts with #
                }
            } elseif (isset($_POST['remove_hashtags'])) {
                // Remove # from the beginning of tag name
                $updated_tag_name = ltrim($tag_name, '#');
            }
            
            // Update the tag name only if it has changed
            if ($updated_tag_name !== $tag_name) {
                wp_update_term($tag->term_id, 'post_tag', array(
                    'name' => $updated_tag_name,
                    'slug' => $tag->slug, // Maintain the original slug
                ));
            }
        }
        
        echo '<div id="message" class="updated notice is-dismissible"><p>タグの操作が正常に実行されました。</p></div>';
    }
}
add_action('admin_init', 'wp_hashtags_process_bulk_operations');
?>
