<?php

public function handle_ajax_search() {
    // Kiểm tra nonce
    check_ajax_referer('mtips5s_search_nonce', 'nonce');

    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';

    $query_args = array(
        'post_type'      => 'mtips5s_post',
        's'              => $search_query,
        'posts_per_page' => -1, // Lấy tất cả các bài viết phù hợp
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_mtips5s_star_rating',
                'value'   => $search_query,
                'compare' => 'LIKE',
            ),
            array(
                'key'     => '_mtips5s_review_count',
                'value'   => $search_query,
                'compare' => 'LIKE',
            ),
            array(
                'key'     => '_mtips5s_price',
                'value'   => $search_query,
                'compare' => 'LIKE',
            ),
        ),
    );

    $query = new WP_Query($query_args);

    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $star_rating = get_post_meta(get_the_ID(), '_mtips5s_star_rating', true);
            $review_count = get_post_meta(get_the_ID(), '_mtips5s_review_count', true);
            $price = get_post_meta(get_the_ID(), '_mtips5s_price', true);

            echo '<div class="mtips5s-post-item">';
            echo '<a href="' . get_permalink() . '">';
            if (has_post_thumbnail()) {
                echo get_the_post_thumbnail(get_the_ID(), 'medium');
            }
            echo '<h2>' . get_the_title() . '</h2>';
            echo '</a>';
            echo '<p>' . __('Star Rating: ') . esc_html($star_rating) . '</p>';
            echo '<p>' . __('Review Count: ') . esc_html($review_count) . '</p>';
            echo '<p>' . __('Price: $') . esc_html($price) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p>' . __('No posts found.') . '</p>';
    }

    wp_reset_postdata();

    wp_send_json_success(array('html' => ob_get_clean()));
}

add_action('wp_ajax_mtips5s_search', array($this, 'handle_ajax_search'));
add_action('wp_ajax_nopriv_mtips5s_search', array($this, 'handle_ajax_search'));
