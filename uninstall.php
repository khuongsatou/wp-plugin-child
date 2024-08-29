<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Xóa các tùy chọn hoặc dữ liệu đã lưu
delete_option('mtips5s_option');
