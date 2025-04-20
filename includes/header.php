<?php
// Start session
session_start();

// Include database configuration
require_once 'config.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect with message
function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Function to display message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        
        // Clear message after displaying
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to get user data
function getUserData($userId) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Function to count unread notifications
function countUnreadNotifications() {
    if (!isLoggedIn()) return 0;
    
    global $conn;
    $userId = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xfolio - Sosyal Medya Bağlantılarınızı Birleştirin</title>
    <meta name="description" content="YouTuber'lar ve Influencer'lar için özel tasarlanmış portföy platformu. Tüm sosyal medya bağlantılarınızı tek bir adreste toplayın.">
    <meta name="keywords" content="influencer, youtuber, portföy, sosyal medya, portfolyo, profil, xfolio">
    <meta property="og:title" content="Xfolio - Sosyal Medya Portföyünüz">
    <meta property="og:description" content="Tüm sosyal medya bağlantılarınızı tek bir adreste toplayın ve paylaşın.">
    <meta property="og:image" content="https://xfolio.xren.com.tr/images/og-image.jpg">
    <meta property="og:url" content="https://xfolio.xren.com.tr">
    <link rel="canonical" href="https://xfolio.xren.com.tr">
    <link rel="icon" href="images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
      <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Tema CSS -->
    <link rel="stylesheet" href="css/themes.css">
    
    <?php if (isset($extraHeader)) echo $extraHeader; ?>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Xfolio Logo" height="40" class="d-inline-block align-text-top">
                Xfolio
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="discover.php">Keşfet</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profilim</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="notifications.php">
                                Bildirimler
                                <?php 
                                $count = countUnreadNotifications();
                                if ($count > 0): 
                                ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $count; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Çıkış</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Giriş</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-4 ms-lg-2" href="register.php">Kayıt Ol</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="main-content">
        <?php displayMessage(); ?>
