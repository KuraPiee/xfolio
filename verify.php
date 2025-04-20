<?php
// E-posta doğrulama sayfası
session_start();
require_once 'includes/config.php';

$pageTitle = "E-posta Doğrulama";
$message = "";
$messageType = "danger";

// Doğrulama kodu kontrolü
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $code = $_GET['code'];
    
    // Veritabanında bu koda sahip kullanıcıyı ara
    $sql = "SELECT id, username, email, is_verified FROM users WHERE verification_code = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Kullanıcı zaten doğrulanmış mı kontrol et
        if ($user['is_verified'] == 1) {
            $message = "Hesabınız zaten doğrulanmış. <a href='login.php'>Giriş yapabilirsiniz</a>.";
            $messageType = "info";
        } else {
            // Kullanıcıyı doğrula
            $sql = "UPDATE users SET is_verified = 1, verification_code = '' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user['id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Hesabınız başarıyla doğrulandı! Şimdi <a href='login.php'>giriş yapabilirsiniz</a>.";
                $messageType = "success";
            } else {
                $message = "Hesabınızı doğrularken bir hata oluştu: " . mysqli_error($conn);
            }
        }
    } else {
        $message = "Geçersiz veya süresi dolmuş doğrulama kodu.";
    }
} else {
    $message = "Geçersiz doğrulama bağlantısı.";
}

// Header dosyasını ekle
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="verify-container" data-aos="fade-up">
                <div class="card shadow-lg">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <?php if ($messageType == "success"): ?>
                                <i class="fas fa-check-circle display-1 text-success mb-3"></i>
                            <?php elseif ($messageType == "info"): ?>
                                <i class="fas fa-info-circle display-1 text-info mb-3"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle display-1 text-danger mb-3"></i>
                            <?php endif; ?>
                            
                            <h2 class="mb-3">E-posta Doğrulama</h2>
                            <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                                <?php echo $message; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">Ana Sayfaya Dön</a>
                            <?php if ($messageType == "danger"): ?>
                                <a href="resend_verification.php" class="btn btn-outline-primary ms-2">Doğrulama E-postasını Yeniden Gönder</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
