<?php
// گرفتن همه نوشته‌ها با متای خاص
$args = array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        array(
            'key' => '_tk_keywords',
            'compare' => 'EXISTS',
        ),
    ),
);
$posts = get_posts($args);
?>

<div class="wrap">
    <h1>🎯 کلمات کلیدی هدف نوشته‌ها</h1>
    <input type="text" id="tk-search" placeholder="جستجو در کلمات کلیدی..." style="width: 300px; margin-bottom: 20px;" />
    <table class="widefat fixed" id="tk-table">
        <thead>
            <tr>
                <th>عنوان نوشته</th>
                <th>کلمات کلیدی هدف</th>
                <th>پیوند یکتا</th>
                <th>لینک</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): 
                $keywords = get_post_meta($post->ID, '_tk_keywords', true);
                ?>
                <tr>
                    <td><?php echo esc_html($post->post_title); ?></td>
<td>
<?php
    $tags = json_decode($keywords, true);
    if (is_array($tags)) {
        foreach ($tags as $tag) {
            echo '<span style="display:inline-block; background:#f3f3f3; border:1px solid #ccc; border-radius:4px; padding:2px 6px; margin:2px; font-size:12px;">' . esc_html($tag['value']) . '</span>';
        }
    } else {
        echo esc_html($keywords); // fallback
    }
?>
</td>
<td>
    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank">
لینک
</a>
    <button class="tk-copy-button" data-link="<?php echo esc_url(get_permalink($post->ID)); ?>" style="margin-left:5px;">📋</button>
</td>


                    <td><a href="<?php echo get_edit_post_link($post->ID); ?>">ویرایش</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
