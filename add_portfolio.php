<?php
// Portfolyo ekleme sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $platform = sanitize($_POST['platform']);
    $title = sanitize($_POST['title']);
    $link = sanitize($_POST['link']);
    $user_id = $_SESSION['user_id'];
    
    // Link formatını kontrol et
    if (!filter_var($link, FILTER_VALIDATE_URL)) {
        $_SESSION['message'] = "Geçersiz URL formatı. Lütfen https:// ile başlayan tam bir URL girin.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Aynı platform için zaten bir link var mı kontrol et
        $checkSql = "SELECT id FROM portfolios WHERE user_id = ? AND platform = ?";
        $stmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $platform);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['message'] = "Bu platform için zaten bir link eklenmişti. Lütfen var olan linki düzenleyin veya silin.";
            $_SESSION['message_type'] = "warning";
        } else {
            // Yeni portfolyo linki ekle
            $sql = "INSERT INTO portfolios (user_id, platform, link, title) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $platform, $link, $title);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Sosyal medya linkiniz başarıyla eklendi.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Link eklenirken bir hata oluştu: " . mysqli_error($conn);
                $_SESSION['message_type'] = "danger";
            }
        }
    }
    
    // Profil sayfasına yönlendir
    header("Location: profile.php");
    exit();
} else {
    // POST istegi değilse profil sayfasına yönlendir
    header("Location: profile.php");
    exit();
}
?>
