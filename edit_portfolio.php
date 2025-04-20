<?php
// Portfolyo düzenleme sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $portfolio_id = (int)$_POST['portfolio_id'];
    $platform = sanitize($_POST['platform']);
    $title = sanitize($_POST['title']);
    $link = sanitize($_POST['link']);
    $user_id = $_SESSION['user_id'];
    
    // Link formatını kontrol et
    if (!filter_var($link, FILTER_VALIDATE_URL)) {
        $_SESSION['message'] = "Geçersiz URL formatı. Lütfen https:// ile başlayan tam bir URL girin.";
        $_SESSION['message_type'] = "danger";
        header("Location: profile.php");
        exit();
    }
    
    // Portfolyo kullanıcıya ait mi kontrol et
    $checkSql = "SELECT id FROM portfolios WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($stmt, "ii", $portfolio_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['message'] = "Bu portfolyo linki size ait değil veya bulunamadı.";
        $_SESSION['message_type'] = "danger";
        header("Location: profile.php");
        exit();
    }
    
    // Portfolyo linkini güncelle
    $sql = "UPDATE portfolios SET platform = ?, link = ?, title = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssii", $platform, $link, $title, $portfolio_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Sosyal medya linkiniz başarıyla güncellendi.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Link güncellenirken bir hata oluştu: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    
    // Profil sayfasına yönlendir
    header("Location: profile.php");
    exit();
} else {
    // POST isteği değilse profil sayfasına yönlendir
    header("Location: profile.php");
    exit();
}
?>
