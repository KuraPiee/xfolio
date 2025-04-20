<?php
// Portfolyo silme sayfası
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
    $user_id = $_SESSION['user_id'];
    
    // Portfolyo kullanıcıya ait mi kontrol et
    $checkSql = "SELECT id, platform, title FROM portfolios WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($stmt, "ii", $portfolio_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['message'] = "Bu portfolyo linki size ait değil veya bulunamadı.";
        $_SESSION['message_type'] = "danger";
    } else {
        $portfolio = mysqli_fetch_assoc($result);
        $portfolioName = $portfolio['title'] ?: $portfolio['platform'];
        
        // Portfolyo linkini sil
        $sql = "DELETE FROM portfolios WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $portfolio_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "\"{$portfolioName}\" linki başarıyla silindi.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Link silinirken bir hata oluştu: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
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
