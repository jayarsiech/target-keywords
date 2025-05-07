<?php
/*
Plugin Name: Target Keywords for Posts
Description: Add and manage target SEO keywords for each post (not visible to users).
Version: 1.0
Author: jayarsiech
Author URI: https://instagram.com/jayarsiech
*/

// افزودن متاباکس به نوشته‌ها
function tk_add_keywords_metabox() {
    $screens = ['post', 'product']; // ← حالا هم برای نوشته، هم محصول

    foreach ($screens as $screen) {
        add_meta_box(
            'tk_keywords_box',
            '🎯 کلمات کلیدی هدف',
            'tk_render_metabox',
            $screen,
            'side',
            'high'
        );
    }
}

add_action('add_meta_boxes', 'tk_add_keywords_metabox');

function tk_render_metabox($post) {
    $keywords = get_post_meta($post->ID, '_tk_keywords', true);
    wp_nonce_field('tk_save_keywords', 'tk_keywords_nonce');
    $all_keywords = tk_get_all_keywords_except($post->ID);

    echo '<input name="tk_keywords" id="tk_keywords_input" value="' . esc_attr($keywords) . '">';
   // نمایش پست‌های مرتبط
$related_posts = tk_get_related_posts_by_keywords($post->ID);
if (!empty($related_posts)) {
echo '<div style="margin-top:15px;"><strong>🔗 پست‌های مرتبط برای لینک‌سازی:</strong><ul>';
foreach ($related_posts as $related) {
    $permalink = esc_url(get_permalink($related->ID));
    $title = esc_html($related->post_title);

    echo '<li><span class="tk-copy-related" data-link="' . $permalink . '" style="cursor:pointer; color:#2271b1; text-decoration:underline;">' . $title . '</span></li>';
}
echo '</ul><div id="tk-copy-feedback" style="color:green; margin-top:5px; display:none;">✅ لینک کپی شد</div></div>';

}
// بررسی کلمات تکراری
if (!empty($all_keywords)) {
    echo '<div id="tk-duplicate-warning" style="margin-top: 10px; color: #cc0000; font-weight:bold;"></div>';
    
}
echo '<script>window.tk_existing_keywords = ' . json_encode($all_keywords) . ';</script>';
 ?>


    <p>هر کلمه رو با Enter اضافه کن (مثلاً: آموزش وردپرس ↵ سئو داخلی ↵)</p>
    <?php
}
function tk_clear_anchor_cache_on_save($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!in_array(get_post_type($post_id), ['post', 'product'])) return;

    delete_transient('tk_anchor_analysis_data');
}
add_action('save_post', 'tk_clear_anchor_cache_on_save');

// پست های مرتبط
function tk_get_related_posts_by_keywords($current_post_id) {
    $current_keywords = get_post_meta($current_post_id, '_tk_keywords', true);
    $current_array = json_decode($current_keywords, true);

    if (!is_array($current_array) || empty($current_array)) return [];

    $search_keywords = array_map(function($item) {
        return sanitize_text_field($item['value']);
    }, $current_array);

    // جستجوی پست‌هایی که حداقل یکی از این کلمات رو دارن
    $meta_query = ['relation' => 'OR'];
    foreach ($search_keywords as $kw) {
        $meta_query[] = [
            'key' => '_tk_keywords',
            'value' => $kw,
            'compare' => 'LIKE'
        ];
    }

    $args = [
        'post_type' => ['post', 'product'],
        'post_status' => 'publish',
        'posts_per_page' => 10,
        'post__not_in' => [$current_post_id],
        'meta_query' => $meta_query
    ];

    return get_posts($args);
}

// ذخیره کردن کلیدواژه‌ها
function tk_save_keywords($post_id) {
    if (!isset($_POST['tk_keywords_nonce']) || !wp_verify_nonce($_POST['tk_keywords_nonce'], 'tk_save_keywords')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $keywords = sanitize_text_field($_POST['tk_keywords']);
    update_post_meta($post_id, '_tk_keywords', $keywords);
}
add_action('save_post', 'tk_save_keywords');

// اضافه کردن آیتم منوی مدیریت
function tk_add_admin_menu() {
    add_menu_page(
        'کلمات کلیدی هدف',
        'کلمات کلیدی مطالب',
        'manage_options',
        'tk_keywords_list',
        'tk_render_admin_page',
    'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36"><text y="32" font-size="32">🎯</text></svg>'),
        26
    );
    add_submenu_page(
    'tk_keywords_list',
    'تحلیل انکر تکست‌ها',
    ' انکر تکست‌ها',
    'manage_options',
    'tk_anchor_analysis',
    'tk_render_anchor_analysis_page'
);

}
add_action('admin_menu', 'tk_add_admin_menu');

// تحلیل
function tk_render_anchor_analysis_page() {
    require_once plugin_dir_path(__FILE__) . 'admin/anchor-analysis-page.php';
}


// لود کردن فایل صفحه مدیریت
function tk_render_admin_page() {
    require_once plugin_dir_path(__FILE__) . 'admin/keywords-list-page.php';
}

// بررسی کلمات تکراری
function tk_get_all_keywords_except($exclude_post_id) {
    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'post__not_in' => [$exclude_post_id],
        'meta_query' => [
            [
                'key' => '_tk_keywords',
                'compare' => 'EXISTS'
            ]
        ]
    ];

    $posts = get_posts($args);
    $keywords = [];

    foreach ($posts as $post) {
        $meta = get_post_meta($post->ID, '_tk_keywords', true);
        $data = json_decode($meta, true);
        if (is_array($data)) {
            foreach ($data as $item) {
                $val = strtolower(trim($item['value']));
                if (!empty($val)) {
                    $keywords[] = $val;
                }
            }
        }
    }

    return array_unique($keywords);
}


// افزودن ستون به post و product
function tk_add_custom_column($columns) {
    $columns['tk_keywords'] = '🎯 کلمات کلیدی هدف';
    return $columns;
}
add_filter('manage_post_posts_columns', 'tk_add_custom_column');
add_filter('manage_product_posts_columns', 'tk_add_custom_column');

// مقداردهی به ستون
function tk_render_custom_column($column, $post_id) {
    if ($column == 'tk_keywords') {
        $meta = get_post_meta($post_id, '_tk_keywords', true);
        $decoded = json_decode($meta, true);

        if (is_array($decoded) && !empty($decoded)) {
            foreach ($decoded as $item) {
                echo '<span style="background:#f3f3f3; border:1px solid #ccc; border-radius:4px; padding:2px 6px; margin:1px; display:inline-block;">' . esc_html($item['value']) . '</span>';
            }
        } else {
            echo '<span style="color:red;">🔴 بدون کلیدواژه</span>';
        }
    }
}
add_action('manage_post_posts_custom_column', 'tk_render_custom_column', 10, 2);
add_action('manage_product_posts_custom_column', 'tk_render_custom_column', 10, 2);


// فایل ها
function tk_enqueue_metabox_scripts($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') return;

    $base = plugin_dir_path(__FILE__);
    $url  = plugin_dir_url(__FILE__);

    wp_enqueue_script('tagify-js', 'https://cdn.jsdelivr.net/npm/@yaireo/tagify', [], null, true);
    wp_enqueue_style('tagify-css', 'https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css');

    wp_enqueue_script(
        'tk-metabox',
        $url . 'js/metabox.js',
        ['tagify-js'],
        filemtime($base . 'js/metabox.js'),
        true
    );
}

add_action('admin_enqueue_scripts', 'tk_enqueue_metabox_scripts');

function tk_enqueue_admin_page_scripts($hook) {
    if ($hook !== 'toplevel_page_tk_keywords_list') return;

    $base = plugin_dir_path(__FILE__);
    $url  = plugin_dir_url(__FILE__);

    wp_enqueue_script(
        'tk-admin-list',
        $url . 'js/admin-list.js',
        [],
        filemtime($base . 'js/admin-list.js'),
        true
    );
}

add_action('admin_enqueue_scripts', 'tk_enqueue_admin_page_scripts');

//فایل ها
function tk_enqueue_anchor_analysis_assets($hook) {
    if (strpos($hook, 'tk_anchor_analysis') !== false) {
        $base = plugin_dir_path(__FILE__);
        $url  = plugin_dir_url(__FILE__);

        wp_enqueue_script('tk-anchor-js', $url . 'js/anchor-analysis.js', [], filemtime($base . 'js/anchor-analysis.js'), true);
        wp_enqueue_style('tk-anchor-css', $url . 'css/anchor-analysis.css', [], filemtime($base . 'css/anchor-analysis.css'));
    }
}
add_action('admin_enqueue_scripts', 'tk_enqueue_anchor_analysis_assets');

