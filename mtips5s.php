<?php
/*
Plugin Name: Mtips5s
Plugin URI: http://example.com/
Description: Mô tả ngắn về plugin Mtips5s.
Version: 1.0
Author: Tên tác giả
Author URI: http://example.com/
License: GPL2
*/

// Nếu truy cập trực tiếp vào file thì dừng lại
if ( !defined('ABSPATH') ) {
    die;
}

// Bao gồm các file cần thiết
require_once plugin_dir_path(__FILE__) . 'includes/class-mtips5s.php';

// Khởi tạo plugin
function run_mtips5s() {
    $plugin = new Mtips5s();
    $plugin->run();
}
run_mtips5s();
