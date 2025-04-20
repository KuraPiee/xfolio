<?php
// Şifre sıfırlama sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Şifremi Unuttum";
$error = "";
$success = "";
$email = "";

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize($_POST['email']);
    
    // E-posta alanı boş mu kontrol et
    if (empty($email)) {
        $error = "E-posta adresi gereklidir.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi girin.";
    } else {
        // Kullanıcıyı kontrol et
        $sql = "SELECT id, username FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Sıfırlama token'ı oluştur
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Token'ı veritabanına kaydet
            $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iss", $user['id'], $token, $expires);
            
            if (mysqli_stmt_execute($stmt)) {
                // Sıfırlama e-postası gönder
                $resetLink = "https://xfolio.xren.com.tr/reset_password.php?token=" . $token;
                $username = $user['username'];
                
                $subject = "Xfolio Şifre Sıfırlama";
                $message = "
                <html>
                <head>
                    <title>Xfolio Şifre Sıfırlama</title>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <img src='https://xfolio.xren.com.tr/images/logo.png' alt='Xfolio Logo' style='height: 60px;'>
                        </div>
                        <h2 style='color: #5e72e4;'>Merhaba $username,</h2>
                        <p>Xfolio hesabınız için şifre sıfırlama talebinde bulundunuz. Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:</p>
                        <p style='text-align: center;'>
                            <a href='$resetLink' style='background-color: #5e72e4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Şifremi Sıfırla</a>
                        </p>
                        <p>Ya da aşağıdaki bağlantıyı tarayıcınıza kopyalayın:</p>
                        <p>$resetLink</p>
                        <p>Bu bağlantı 1 saat içinde geçerliliğini yitirecektir.</p>
                        <p>Bu e-postayı siz talep etmediyseniz, lütfen dikkate almayın.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='text-align: center; color: #777; font-size: 12px;'>
                            &copy; " . date('Y') . " Xfolio. Tüm hakları saklıdır.
                        </p>
                    </div>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Xfolio <noreply@xfolio.xren.com.tr>" . "\r\n";
                
                mail($email, $subject, $message, $headers);
                
                // Başarı mesajı göster
                $_SESSION['reset_success'] = true;
                header("Location: login.php");
                exit();
            } else {
                $error = "Şifre sıfırlama talebiniz işlenirken bir hata oluştu.";
            }
        } else {
            // Kullanıcı bulunamadı, ancak güvenlik nedeniyle aynı mesajı göster
            $success = "Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.";
        }
    }
}

// Header dosyasını ekle
$extraHeader = '<link rel="stylesheet" href="css/auth.css">';
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="forgot-password-container" data-aos="fade-up">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Şifremi Unuttum</h2>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <p class="text-muted text-center mb-4">Şifrenizi sıfırlamak için e-posta adresinizi girin.</p>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="forgot-password-form">
                            <div class="mb-4">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Şifre Sıfırlama Bağlantısı Gönder</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3 bg-light">
                        <a href="login.php" class="text-primary">Giriş sayfasına dön</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
