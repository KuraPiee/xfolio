<?php
// Kayıt sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Kayıt Ol";
$error = "";
$success = "";
$username = "";
$email = "";
$phone = "";

// Kayıt formunu kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);
    
    // Form doğrulama
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($phone)) {
        $error = "Tüm alanlar gereklidir.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi girin.";
    } elseif (strlen($password) < 6) {
        $error = "Şifreniz en az 6 karakter olmalıdır.";
    } elseif ($password !== $confirmPassword) {
        $error = "Şifreler eşleşmiyor.";
    } else {
        // Kullanıcı adı ve e-posta kontrolü
        $checkUser = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $checkUser);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Bu kullanıcı adı veya e-posta adresi zaten kullanımda.";
        } else {
            // Şifre hashleme
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Doğrulama kodu oluşturma
            $verificationCode = bin2hex(random_bytes(16));
            
            // Kullanıcı kaydı
            $insertUser = "INSERT INTO users (username, email, password, phone, verification_code) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertUser);
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $hashedPassword, $phone, $verificationCode);
            
            if (mysqli_stmt_execute($stmt)) {
                // Doğrulama e-postası gönderimi
                $subject = "Xfolio Hesap Doğrulaması";
                $verificationLink = "https://xfolio.xren.com.tr/verify.php?code=" . $verificationCode;
                
                $message = "
                <html>
                <head>
                    <title>Xfolio Hesap Doğrulaması</title>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <img src='https://xfolio.xren.com.tr/images/logo.png' alt='Xfolio Logo' style='height: 60px;'>
                        </div>
                        <h2 style='color: #5e72e4;'>Merhaba $username,</h2>
                        <p>Xfolio'ya hoş geldiniz. Hesabınızı doğrulamak için aşağıdaki bağlantıya tıklayın:</p>
                        <p style='text-align: center;'>
                            <a href='$verificationLink' style='background-color: #5e72e4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Hesabımı Doğrula</a>
                        </p>
                        <p>Ya da aşağıdaki bağlantıyı tarayıcınıza kopyalayın:</p>
                        <p>$verificationLink</p>
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
                
                // SMTP gönderimi yerine burada mail() fonksiyonunu kullanıyoruz
                // Gerçek uygulamada PHPMailer gibi bir kütüphane kullanmak daha güvenli olacaktır
                mail($email, $subject, $message, $headers);
                
                $success = "Kayıt işleminiz başarıyla tamamlandı! Lütfen e-posta adresinize gönderilen doğrulama bağlantısını kullanın.";
                $username = $email = $phone = ""; // Form alanlarını temizle
            } else {
                $error = "Kayıt sırasında bir hata oluştu: " . mysqli_error($conn);
            }
        }
    }
}

// Header dosyasını ekle
$extraHeader = '<link rel="stylesheet" href="css/auth.css">';
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="register-container" data-aos="fade-up">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Xfolio'ya Kayıt Ol</h2>
                        
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
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="register-form">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                                <small class="form-text text-muted">Profiliniz için benzersiz bir kullanıcı adı seçin.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <small class="form-text text-muted">E-posta adresiniz hesap doğrulaması için kullanılacaktır.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefon Numarası</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Şifreniz en az 6 karakter uzunluğunda olmalıdır.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    <a href="terms.php" target="_blank">Kullanım Şartları</a> ve <a href="privacy.php" target="_blank">Gizlilik Politikası</a>'nı okudum ve kabul ediyorum.
                                </label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Kayıt Ol</button>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="social-login text-center">
                                <p class="text-muted mb-2">Sosyal medya ile kayıt ol</p>
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-outline-primary social-btn me-2"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="btn btn-outline-danger social-btn me-2"><i class="fab fa-google"></i></a>
                                    <a href="#" class="btn btn-outline-dark social-btn"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3 bg-light">
                        <p class="mb-0">Zaten bir hesabınız var mı? <a href="login.php" class="text-primary">Giriş yap</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Şifre göster/gizle işlevi
    const togglePassword = document.querySelector('.toggle-password');
    const password = document.querySelector('#password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Şifre güvenliği kontrolü
    const passwordInput = document.querySelector('#password');
    passwordInput.addEventListener('input', function() {
        // Şifre güvenliği kontrolü yapılabilir
    });
});
</script>

<?php include 'includes/footer.php'; ?>
