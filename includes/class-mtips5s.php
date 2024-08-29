<?php

if ( !defined('ABSPATH') ) {
    die;
}

class Mtips5s {

    // Constructor
    public function __construct() {
        // Đăng ký các hook cần thiết
        $this->register_hooks();
    }

    // Đăng ký các hook
    private function register_hooks() {
        // Đăng ký các hook để tạo custom post type và taxonomy
        add_action('init', array($this, 'custom_post_type'));
        add_action('init', array($this, 'create_mtips5s_taxonomy'));

        // Đăng ký shortcode
        add_shortcode('mtips5s_list', array($this, 'render_mtips5s_list'));

        // Enqueue các tài nguyên CSS và JS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    // Hàm tạo custom post type
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

    // Hàm tạo taxonomy
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

    // Hàm hiển thị danh sách post type dưới dạng shortcode
    public function render_mtips5s_list($atts) {
        // Thiết lập mặc định cho các tham số shortcode
        $atts = shortcode_atts(array(
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ), $atts, 'mtips5s_list');

        // Truy vấn các bài viết từ custom post type
        $query = new WP_Query(array(
            'post_type'      => 'mtips5s_post',
            'posts_per_page' => $atts['posts_per_page'],
            'orderby'        => $atts['orderby'],
            'order'          => $atts['order'],
        ));

        // Bắt đầu output
        $output = '<div class="mtips5s-list">';

        if ($query->have_posts()) {
            $output .= '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                $output .= '<li>';
                $output .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                if (has_post_thumbnail()) {
                    $output .= '<div>' . get_the_post_thumbnail(get_the_ID(), 'thumbnail') . '</div>';
                }
                $output .= '<p>' . get_the_excerpt() . '</p>';
                $output .= '</li>';
            }
            $output .= '</ul>';
        } else {
            $output .= '<p>No posts found.</p>';
        }

        $output .= '</div>';

        // Reset lại post data
        wp_reset_postdata();

        return $output;
    }

    // Hàm enqueue tài nguyên CSS và JS
    public function enqueue_assets() {
        wp_enqueue_style('mtips5s-style', plugin_dir_url(__FILE__) . '../assets/css/style.css');
        wp_enqueue_script('mtips5s-script', plugin_dir_url(__FILE__) . '../assets/js/script.js', array('jquery'), null, true);
    }

    // Khởi chạy plugin
    public function run() {
        // Đây là nơi bạn có thể thêm các thao tác cần thiết khi plugin khởi chạy
    }

    // Hàm kích hoạt plugin
    public static function activate() {
        // Logic kích hoạt như tạo bảng database, thêm tùy chọn
    }

    // Hàm vô hiệu hóa plugin
    public static function deactivate() {
        // Logic vô hiệu hóa như xóa bảng database, xóa tùy chọn
    }
}

// Kích hoạt plugin
register_activation_hook(__FILE__, array('Mtips5s', 'activate'));

// Vô hiệu hóa plugin
register_deactivation_hook(__FILE__, array('Mtips5s', 'deactivate'));
