<?php

if ( !defined('ABSPATH') ) {
    die;
}

class Mtips5s {

    public function __construct() {
        $this->register_hooks();
        $this->register_ajax_hooks();
    }

    private function register_hooks() {
        add_action('init', array($this, 'custom_post_type'));
        add_action('init', array($this, 'create_mtips5s_taxonomy'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // Đăng ký shortcode
        add_shortcode('mtips5s_list', array($this, 'display_mtips5s_list'));

        // Thêm meta box
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));

        // Thêm cột tùy chỉnh vào danh sách quản lý
        add_filter('manage_mtips5s_post_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_mtips5s_post_posts_custom_column', array($this, 'custom_column'), 10, 2);

        // Sắp xếp theo cột tùy chỉnh
        add_filter('manage_edit-mtips5s_post_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_posts', array($this, 'sort_custom_columns'));
    }

    private function register_ajax_hooks() {
        add_action('wp_ajax_mtips5s_search', array($this, 'handle_ajax_search'));
        add_action('wp_ajax_nopriv_mtips5s_search', array($this, 'handle_ajax_search'));
    }

    

    public function custom_post_type() {
        register_post_type('mtips5s_post', array(
            'labels'      => array(
                'name'          => __('Mtips5s Posts'),
                'singular_name' => __('Mtips5s Post'),
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => 'mtips5s'),
            'supports'    => array('title', 'editor', 'thumbnail'),
            'taxonomies'  => array('mtips5s_category'),
        ));
    }

    public function create_mtips5s_taxonomy() {
        register_taxonomy('mtips5s_category', 'mtips5s_post', array(
            'labels' => array(
                'name'          => __('Mtips5s Categories'),
                'singular_name' => __('Mtips5s Category'),
            ),
            'hierarchical' => true,
            'public'       => true,
            'rewrite'      => array('slug' => 'mtips5s-category'),
        ));
    }

    public function enqueue_assets() {
        wp_enqueue_style('mtips5s-style', plugin_dir_url(__FILE__) . '../assets/css/style.css');
        wp_enqueue_script('mtips5s-script', plugin_dir_url(__FILE__) . '../assets/js/script.js', array('jquery'), null, true);

        wp_localize_script('mtips5s-script', 'mtips5sAjax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }

    // Thêm meta box cho hạng sao, đánh giá, và giá
    public function add_meta_boxes() {
        add_meta_box(
            'mtips5s_meta_box',
            __('Mtips5s Post Details'),
            array($this, 'render_meta_box'),
            'mtips5s_post',
            'side',
            'default'
        );
    }

    public function render_meta_box($post) {
        // Lấy giá trị hiện tại của các trường
        $star_rating = get_post_meta($post->ID, '_mtips5s_star_rating', true);
        $review_count = get_post_meta($post->ID, '_mtips5s_review_count', true);
        $price = get_post_meta($post->ID, '_mtips5s_price', true);

        // Render các trường trong meta box
        ?>
        <label for="mtips5s_star_rating"><?php _e('Star Rating'); ?></label>
        <input type="number" id="mtips5s_star_rating" name="mtips5s_star_rating" value="<?php echo esc_attr($star_rating); ?>" min="1" max="5" step="0.5">

        <label for="mtips5s_review_count"><?php _e('Review Count'); ?></label>
        <input type="number" id="mtips5s_review_count" name="mtips5s_review_count" value="<?php echo esc_attr($review_count); ?>">

        <label for="mtips5s_price"><?php _e('Price'); ?></label>
        <input type="number" id="mtips5s_price" name="mtips5s_price" value="<?php echo esc_attr($price); ?>" step="0.01">
        <?php
    }

    public function save_meta_boxes($post_id) {
        if (array_key_exists('mtips5s_star_rating', $_POST)) {
            update_post_meta($post_id, '_mtips5s_star_rating', sanitize_text_field($_POST['mtips5s_star_rating']));
        }
        if (array_key_exists('mtips5s_review_count', $_POST)) {
            update_post_meta($post_id, '_mtips5s_review_count', sanitize_text_field($_POST['mtips5s_review_count']));
        }
        if (array_key_exists('mtips5s_price', $_POST)) {
            update_post_meta($post_id, '_mtips5s_price', sanitize_text_field($_POST['mtips5s_price']));
        }
    }

        // Thêm cột tùy chỉnh vào danh sách quản lý
        public function set_custom_columns($columns) {
            $columns['star_rating'] = __('Star Rating');
            $columns['review_count'] = __('Review Count');
            $columns['price'] = __('Price');
            return $columns;
        }
    
        public function custom_column($column, $post_id) {
            switch ($column) {
                case 'star_rating':
                    $star_rating = get_post_meta($post_id, '_mtips5s_star_rating', true);
                    echo esc_html($star_rating);
                    break;
                case 'review_count':
                    $review_count = get_post_meta($post_id, '_mtips5s_review_count', true);
                    echo esc_html($review_count);
                    break;
                case 'price':
                    $price = get_post_meta($post_id, '_mtips5s_price', true);
                    echo esc_html($price);
                    break;
            }
        }


    // Đặt cột tùy chỉnh là sortable
    public function sortable_columns($columns) {
        $columns['star_rating'] = 'star_rating';
        $columns['review_count'] = 'review_count';
        $columns['price'] = 'price';
        return $columns;
    }

        // Sắp xếp theo cột tùy chỉnh
    public function sort_custom_columns($query) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }
    
            $orderby = $query->get('orderby');
    
            if ('star_rating' === $orderby) {
                $query->set('meta_key', '_mtips5s_star_rating');
                $query->set('orderby', 'meta_value_num');
            } elseif ('review_count' === $orderby) {
                $query->set('meta_key', '_mtips5s_review_count');
                $query->set('orderby', 'meta_value_num');
            } elseif ('price' === $orderby) {
                $query->set('meta_key', '_mtips5s_price');
                $query->set('orderby', 'meta_value_num');
            }
        }
    

    // Hàm hiển thị danh sách post dưới dạng shortcode với chế độ list/grid và sắp xếp
    public function display_mtips5s_list($atts) {
        // Xử lý các tham số shortcode
        $atts = shortcode_atts(
            array(
                'posts_per_page' => 5,
                'view'           => 'list', // Chế độ mặc định là list
                'orderby'        => 'date', // Sắp xếp theo ngày đăng
                'order'          => 'DESC', // Thứ tự giảm dần
            ), 
            $atts, 
            'mtips5s_list'
        );
    
        // Lấy các tham số từ URL nếu có
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : $atts['orderby'];
        $order = isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : $atts['order'];
        $search_query = isset($_GET['search_query']) ? sanitize_text_field($_GET['search_query']) : '';
    
        // Xác định meta_key dựa trên giá trị của orderby
        $meta_key = '';
        if ($orderby === 'star_rating') {
            $meta_key = '_mtips5s_star_rating';
        } elseif ($orderby === 'review_count') {
            $meta_key = '_mtips5s_review_count';
        } elseif ($orderby === 'price') {
            $meta_key = '_mtips5s_price';
        }
    
        // Cấu hình các tham số cho WP_Query
        $query_args = array(
            'post_type'      => 'mtips5s_post',
            'posts_per_page' => $atts['posts_per_page'],
            'meta_key'       => $meta_key,
            'orderby'        => $meta_key ? 'meta_value_num' : $orderby,
            'order'          => $order,
            's'              => $search_query, // Thêm từ khóa tìm kiếm vào truy vấn
        );
    
        $query = new WP_Query($query_args);
    
        ob_start();
    
        // Thêm form tìm kiếm
        echo '<div class="mtips5s-search-form">';
        echo '<form id="mtips5s-search-form" method="get">';
        echo '<input type="text" name="search_query" placeholder="' . esc_attr__('Search...', 'text-domain') . '" value="' . esc_attr($search_query) . '" />';
        echo '</form>';
        echo '</div>';
    
        // Thêm nút chuyển đổi chế độ hiển thị
        echo '<div class="mtips5s-view-toggle">';
        echo '<button class="mtips5s-toggle" data-view="list">' . __('List View') . '</button>';
        echo '<button class="mtips5s-toggle" data-view="grid">' . __('Grid View') . '</button>';
        echo '</div>';
    
        // Thêm các nút sắp xếp
        echo '<div class="mtips5s-sort-buttons">';
        echo '<a href="' . esc_url(add_query_arg(array('orderby' => 'star_rating', 'order' => ($order === 'ASC' ? 'DESC' : 'ASC'), 'search_query' => $search_query))) . '">' . __('Sort by Star Rating') . '</a>';
        echo ' | ';
        echo '<a href="' . esc_url(add_query_arg(array('orderby' => 'review_count', 'order' => ($order === 'ASC' ? 'DESC' : 'ASC'), 'search_query' => $search_query))) . '">' . __('Sort by Review Count') . '</a>';
        echo ' | ';
        echo '<a href="' . esc_url(add_query_arg(array('orderby' => 'price', 'order' => ($order === 'ASC' ? 'DESC' : 'ASC'), 'search_query' => $search_query))) . '">' . __('Sort by Price') . '</a>';
        echo '</div>';
    
        if ($query->have_posts()) {
            echo '<div class="mtips5s-posts mtips5s-' . esc_attr($atts['view']) . '-view">';
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
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . __('No posts found.') . '</p>';
        }
    
        return ob_get_clean();
    }

    public function handle_ajax_search() {
        $atts = array(
            'posts_per_page' => 5,
            'view'           => 'list',
            'orderby'        => isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date',
            'order'          => isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : 'DESC',
        );
    
        $search_query = isset($_GET['search_query']) ? sanitize_text_field($_GET['search_query']) : '';
        $meta_query = array('relation' => 'OR');
        if (!empty($search_query)) {
            $meta_query[] = array(
                'key'     => '_mtips5s_star_rating',
                'value'   => $search_query,
                'compare' => 'LIKE',
                'type'    => 'NUMERIC'
            );
        }

        echo '<pre>'; print_r($meta_query); echo '</pre>';

    
        if (!empty($search_query)) {
            $meta_query[] = array(
                'key'     => '_mtips5s_review_count',
                'value'   => $search_query,
                'compare' => 'LIKE',
                'type'    => 'NUMERIC'
            );
        }
    
        if (!empty($search_query)) {
            $meta_query[] = array(
                'key'     => '_mtips5s_price',
                'value'   => $search_query,
                'compare' => 'LIKE',
                'type'    => 'NUMERIC'
            );
        }
    
        $meta_key = '';
        if ($atts['orderby'] === 'star_rating') {
            $meta_key = '_mtips5s_star_rating';
        } elseif ($atts['orderby'] === 'review_count') {
            $meta_key = '_mtips5s_review_count';
        } elseif ($atts['orderby'] === 'price') {
            $meta_key = '_mtips5s_price';
        }

        // Tạo một hàm tùy chỉnh để thay đổi điều kiện WHERE của truy vấn
        add_filter('posts_where', function($where) use ($search_query) {
            if (!empty($search_query)) {
                global $wpdb;
                $where .= $wpdb->prepare(" OR {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
            }
            // Thêm điều kiện post_type vào WHERE
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_type = %s", 'mtips5s_post');
            return $where;
        });
        
        $query_args = array(
            'post_type'      => 'mtips5s_post',
            'posts_per_page' => $atts['posts_per_page'],
            'meta_key'       => $meta_key,
            'orderby'        => $meta_key ? 'meta_value_num' : $atts['orderby'],
            'order'          => $atts['order'],
            // 's'              => $search_query,
            'meta_query'     => $meta_query
        );
    
        $query = new WP_Query($query_args);
        // echo '<pre>'; print_r($query ); echo '</pre>';

        // Loại bỏ filter sau khi sử dụng để không ảnh hưởng đến các truy vấn khác
        remove_filter('posts_where', function($where) use ($search_query) {
            if (!empty($search_query)) {
                global $wpdb;
                $where .= $wpdb->prepare(" OR {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
            }
            // Thêm điều kiện post_type vào WHERE
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_type = %s", 'mtips5s_post');
            return $where;
        });


        ob_start();
    
        if ($query->have_posts()) {
            echo '<div class="mtips5s-posts mtips5s-' . esc_attr($atts['view']) . '-view">';
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
            echo '</div>';
        } else {
            echo '<p>' . __('No posts found.') . '</p>';
        }
    
        wp_reset_postdata();
        echo ob_get_clean();
        wp_die();
    }
    
    
    
    
    
    
    

    public function run() {
        // Các thao tác cần thiết khi plugin khởi chạy
    }

    public static function activate() {
        // Logic kích hoạt như tạo bảng database, thêm tùy chọn
    }

    public static function deactivate() {
        // Logic vô hiệu hóa như xóa bảng database, xóa tùy chọn
    }
}

// Kích hoạt plugin
register_activation_hook(__FILE__, array('Mtips5s', 'activate'));

// Vô hiệu hóa plugin
register_deactivation_hook(__FILE__, array('Mtips5s', 'deactivate'));
