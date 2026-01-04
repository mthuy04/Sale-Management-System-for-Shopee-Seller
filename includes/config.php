<?php
// includes/config.php

// Cấu hình y hệt dự án Teacher Bee của bạn
$host = '127.0.0.1'; // Dùng IP này thay cho localhost để tránh lỗi kết nối
$user = 'root';
$pass = '';          // Mật khẩu để trống
$db   = 'sms_shopee'; // Tên DB của dự án HappyHome
$port = 3306;        // Cổng MySQL chuẩn

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#ffeeee;'>
        <h3>Lỗi kết nối Database!</h3>
        <p>Hệ thống không thể kết nối tới CSDL <b>$db</b>.</p>
        <p><b>Chi tiết lỗi:</b> " . $e->getMessage() . "</p>
        <hr>
        <p><b>Cách khắc phục:</b><br>
        1. Vào phpMyAdmin tạo database tên là <b>sms_shopee</b> (nếu chưa tạo).<br>
        2. Config hiện tại đang dùng port <b>3306</b> và mật khẩu <b>rỗng</b> (theo dự án cũ của bạn).
        </p>
    </div>");
}
?>