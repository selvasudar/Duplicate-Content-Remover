<?php
/**
 * Plugin Name: Duplicate Content Remover
 * Description: Identifies and helps remove duplicate content in WordPress posts and pages
 * Version: 1.0
 * Author: Selvakumar Duraipandian
 * Author URI: https://linkedin.com/in/selvakumarduraipandian
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Remove default canonical URLs
remove_action('wp_head', 'rel_canonical');

// Add menu item to WordPress admin
add_action('admin_menu', 'dcm_add_admin_menu');
function dcm_add_admin_menu() {
    add_menu_page(
        'Duplicate Content Remover',
        'Duplicate Content',
        'manage_options',
        'duplicate-content-remover',
        'dcm_admin_page',
        'dashicons-admin-page',
        30
    );
}

// Create the admin page
function dcm_admin_page() {
    // Process deletions if approved
    if (isset($_POST['dcm_delete_posts']) && check_admin_referer('dcm_delete_posts')) {
        $posts_to_delete = isset($_POST['posts_to_delete']) ? $_POST['posts_to_delete'] : array();
        foreach ($posts_to_delete as $post_id) {
            wp_delete_post($post_id, true);
        }
        echo '<div class="notice notice-success"><p>Selected duplicate posts have been deleted.</p></div>';
    }

    // Get current page number
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $items_per_page = 20;

    // Get duplicate content
    $all_duplicates = dcm_find_duplicates();
    $total_groups = count($all_duplicates);
    $total_pages = ceil($total_groups / $items_per_page);
    
    // Get paginated duplicates
    $start = ($current_page - 1) * $items_per_page;
    $duplicates = array_slice($all_duplicates, $start, $items_per_page);
    
    ?>
    <div class="wrap">
        <h1>Duplicate Content Remover</h1>
        <?php if (empty($all_duplicates)): ?>
            <div class="notice notice-success"><p>No duplicate content found!</p></div>
        <?php else: ?>
            <form method="post" action="">
                <?php wp_nonce_field('dcm_delete_posts'); ?>
                <p>
                    <input type="submit" name="dcm_delete_posts" class="button button-primary" value="Delete Selected Duplicates">
                </p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Delete</th>
                            <th>Title</th>
                            <th>URL</th>
                            <th>Post Type</th>
                            <th>Date</th>
                            <th>Canonical URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($duplicates as $group): ?>
                            <?php 
                            // Skip first post in group (keep original)
                            $original = array_shift($group);
                            $original_url = get_permalink($original->ID);
                            ?>
                            <tr>
                                <td></td>
                                <td><strong>ORIGINAL: </strong><?php echo esc_html($original->post_title); ?></td>
                                <td><?php echo esc_url($original_url); ?></td>
                                <td><?php echo esc_html($original->post_type); ?></td>
                                <td><?php echo get_the_date('', $original->ID); ?></td>
                                <td>N/A</td>
                            </tr>
                            <?php foreach ($group as $post): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="posts_to_delete[]" value="<?php echo esc_attr($post->ID); ?>">
                                    </td>
                                    <td><?php echo esc_html($post->post_title); ?></td>
                                    <td><?php echo esc_url(get_permalink($post->ID)); ?></td>
                                    <td><?php echo esc_html($post->post_type); ?></td>
                                    <td><?php echo get_the_date('', $post->ID); ?></td>
                                    <td><?php echo esc_url($original_url); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr><td colspan="6" style="background-color: #f0f0f0;"></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $total_groups; ?> duplicate groups</span>
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => __('&laquo;'),
                                'next_text' => __('&raquo;'),
                                'total' => $total_pages,
                                'current' => $current_page
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>                
            </form>
        <?php endif; ?>
    </div>
    <?php
}

// Function to find duplicate content
function dcm_find_duplicates() {
    global $wpdb;
    
    // Get all posts and pages
    $posts = $wpdb->get_results("
        SELECT ID, post_title, post_name, post_type, post_status 
        FROM {$wpdb->posts} 
        WHERE post_type IN ('post', 'page') 
        AND post_status = 'publish'
        ORDER BY post_title, ID
    ");

    $duplicates = array();
    $current_group = array();
    $last_title = '';

    foreach ($posts as $post) {
        // Get base slug without numbers
        $base_slug = preg_replace('/-\d+$/', '', $post->post_name);
        
        // Check if this post has the same base title/slug as the previous one
        if ($last_title === $post->post_title || 
            strpos($post->post_name, $base_slug) === 0) {
            if (empty($current_group)) {
                // Add the first post of the group
                $current_group[] = $last_post;
            }
            $current_group[] = $post;
        } else {
            // If we have a group of duplicates, add it to our results
            if (count($current_group) > 1) {
                $duplicates[] = $current_group;
            }
            $current_group = array();
        }
        
        $last_title = $post->post_title;
        $last_post = $post;
    }

    // Don't forget to add the last group if it exists
    if (count($current_group) > 1) {
        $duplicates[] = $current_group;
    }

    return $duplicates;
}

// Add CSS for admin page
add_action('admin_head', 'dcm_admin_styles');
function dcm_admin_styles() {
    ?>
    <style>
        .duplicate-content-manager .widefat td {
            vertical-align: middle;
        }
        .duplicate-content-manager .original {
            background-color: #f7f7f7;
        }
        .tablenav-pages {
            margin: 1em 0;
        }
        .tablenav-pages a,
        .tablenav-pages span.current {
            padding: 3px 6px;
            margin: 0 2px;
            border: 1px solid #ddd;
            text-decoration: none;
        }
        .tablenav-pages span.current {
            background: #0073aa;
            color: white;
            border-color: #0073aa;
        }
    </style>
    <?php
}

// Add canonical URL to duplicate posts
add_action('wp_head', 'dcm_add_canonical_url');
function dcm_add_canonical_url() {
    if (is_singular(['post', 'page'])) {
        global $post;
        
        // Get base slug without numbers
        $current_base_slug = preg_replace('/-\d+$/', '', $post->post_name);
        
        // Query to find posts with same base slug
        global $wpdb;
        $possible_duplicates = $wpdb->get_results($wpdb->prepare("
            SELECT ID, post_title, post_name, post_date 
            FROM {$wpdb->posts} 
            WHERE post_type = %s 
            AND post_status = 'publish' 
            AND (post_name = %s OR post_name LIKE %s)
            ORDER BY post_date ASC",
            $post->post_type,
            $current_base_slug,
            $wpdb->esc_like($current_base_slug) . '-%'
        ));

        // If we found multiple posts
        if (count($possible_duplicates) > 1) {
            // First post in chronological order is considered original
            $original = $possible_duplicates[0];
            
            // If current post is not the original, add canonical
            if ($post->ID !== $original->ID) {
                echo '<link rel="canonical" href="' . esc_url(get_permalink($original->ID)) . '" />' . "\n";
            }
        }

        // Also check for title duplicates if no slug duplicates found
        else {
            $title_duplicates = $wpdb->get_results($wpdb->prepare("
                SELECT ID, post_title, post_name, post_date 
                FROM {$wpdb->posts} 
                WHERE post_type = %s 
                AND post_status = 'publish' 
                AND post_title = %s
                ORDER BY post_date ASC",
                $post->post_type,
                $post->post_title
            ));

            if (count($title_duplicates) > 1) {
                $original = $title_duplicates[0];
                if ($post->ID !== $original->ID) {
                    echo '<link rel="canonical" href="' . esc_url(get_permalink($original->ID)) . '" />' . "\n";
                }
            }
        }
    }
}
