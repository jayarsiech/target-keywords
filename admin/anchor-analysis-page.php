<?php
// بررسی کش
$cached_data = get_transient('tk_anchor_analysis_data');

if (isset($_GET['tk_refresh']) || !$cached_data) {
    $args = [
        'post_type' => ['post', 'product'],
        'post_status' => 'publish',
        'posts_per_page' => -1
    ];

    $posts = get_posts($args);
    $anchors = [];

   foreach ($posts as $post) {
    $post_title = get_the_title($post);
    $post_link  = get_permalink($post->ID);

    $sources = [];

    // بررسی محتوای اصلی
    if (!empty($post->post_content)) {
        $sources[] = [
            'type'    => 'content',
            'content' => $post->post_content
        ];
    }

    // بررسی فیلدهای ACF
// بررسی فیلدهای ACF مربوط به مقاله جاری
if (function_exists('get_fields')) {
    $acf_fields = get_fields($post->ID);
    if (is_array($acf_fields)) {
        foreach ($acf_fields as $field_name => $value) {
            if (!is_object($value) && !is_array($value)) {
                if (is_string($value) && strpos($value, '<a') !== false) {
                    $sources[] = [
                        'type'    => 'acf',
                        'content' => $value
                    ];
                }
            }
        }
    }
}

    foreach ($sources as $source) {
        preg_match_all('#<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#si', $source['content'], $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $href        = esc_url($match[1]);
            $anchor_text = wp_strip_all_tags(trim($match[2]));
            if (!$anchor_text) continue;

            $key = md5(mb_strtolower($anchor_text));

            if (!isset($anchors[$key])) {
                $anchors[$key] = [
                    'text'  => $anchor_text,
                    'links' => []
                ];
            }

            $anchors[$key]['links'][] = [
                'source_title' => $post_title,
                'source_url'   => $post_link,
                'target_url'   => $href,
                'source_type'  => $source['type']
            ];
        }
    }
}


    set_transient('tk_anchor_analysis_data', $anchors, HOUR_IN_SECONDS);
} else {
    $anchors = $cached_data;
}
?>

<div class="wrap">
    <h1>📎 تحلیل انکر تکست‌ها</h1>

    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=tk_anchor_analysis&tk_refresh=1')); ?>" class="button">
            ♻️ تجزیه و تحلیل مجدد
        </a>
    </p>

    <input type="text" id="tk-search-anchor" placeholder="جستجو در انکر تکست..." style="margin-bottom:15px; width:300px; padding:6px;">

    <?php if (!empty($anchors)): ?>
        <table class="widefat fixed striped" id="tk-anchor-table">
            <thead>
                <tr>
                    <th>انکر تکست</th>
<th id="tk-sort-usage" style="cursor: pointer; color: #0073aa;" title="مرتب‌سازی تعداد استفاده">
    تعداد استفاده

</th>

                    <th>مشاهده</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($anchors as $key => $data): ?>
            <tr data-usage="<?php echo count($data['links']); ?>">
                        <td><strong><?php echo esc_html($data['text']); ?></strong></td>
                        <td><?php echo count($data['links']); ?> بار</td>
                        <td>
                            <button class="tk-show-details button" data-target="<?php echo esc_attr($key); ?>">
                                مشاهده
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<div id="tk-pagination" style="margin-top:20px; text-align:center;"></div>
       <?php foreach ($anchors as $key => $data): ?>
    <div class="tk-anchor-modal" id="tk-modal-<?php echo esc_attr($key); ?>">
        <div class="tk-modal-content">
            <div class="tk-modal-header">
                <h3>تحلیل برای: "<?php echo esc_html($data['text']); ?>"</h3>
            </div>
            <div class="tk-modal-body">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>مبدا</th>
                            <th>مقصد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['links'] as $link): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($link['source_url']); ?>" target="_blank">
                                        <?php echo esc_html($link['source_title']); ?>
                                    </a>
                                    <br>
                                    <small style="color:#666;">منبع: <?php echo $link['source_type'] === 'acf' ? 'ACF' : 'محتوا'; ?></small>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($link['target_url']); ?>" target="_blank">
                                        <?php echo esc_html($link['target_url']); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="tk-modal-footer">
                <button class="button tk-close-modal">بستن</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>


    <?php else: ?>
        <p>هیچ انکر تکستی پیدا نشد.</p>
    <?php endif; ?>
</div>
