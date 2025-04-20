<?php
// Giriş sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Giriş Yap";
$error = "";
$email = "";
$remember = false;

// Giriş formunu kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // E-posta ve şifre kontrolü
    if (empty($email) || empty($password)) {
        $error = "Email ve şifre gereklidir.";
    } else {
        $sql = "SELECT id, username, email, password, is_verified FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user['password'])) {
                // E-posta doğrulanmış mı kontrol et
                if ($user['is_verified'] == 1) {
                    // Oturumu başlat
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Beni hatırla seçeneği
                    if ($remember) {
                        $token = bin2hex(random_bytes(16));
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        $sql = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "iss", $user['id'], $token, $expires);
                        mysqli_stmt_execute($stmt);
                        
                        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
                    }
                    
                    // Yönlendirme
                    if (isset($_SESSION['redirect_url'])) {
                        $redirect = $_SESSION['redirect_url'];
                        unset($_SESSION['redirect_url']);
                        header("Location: $redirect");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = "Hesabınız doğrulanmamış. Lütfen e-posta adresinize gönderilen doğrulama bağlantısını kullanın.";
                }
            } else {
                $error = "Geçersiz e-posta veya şifre.";
            }
        } else {
            $error = "Geçersiz e-posta veya şifre.";
        }
    }
}

// Şifremi unuttum işlemleri için değişken
$resetSuccess = isset($_SESSION['reset_success']) ? true : false;
unset($_SESSION['reset_success']);

// Header dosyasını ekle
$extraHeader = '<link rel="stylesheet" href="css/auth.css">';
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="login-container" data-aos="fade-up">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Xfolio'ya Hoşgeldiniz</h2>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($resetSuccess): ?>
                            <div class="alert alert-success" role="alert">
                                Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.
                            </div>
                        <?php endif; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
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
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember" <?php echo $remember ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="remember">Beni hatırla</label>
                                </div>
                                <a href="forgot_password.php" class="text-primary">Şifremi unuttum</a>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="social-login text-center">
                                <p class="text-muted mb-2">Sosyal medya ile giriş yap</p>
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-outline-primary social-btn me-2"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="btn btn-outline-danger social-btn me-2"><i class="fab fa-google"></i></a>
                                    <a href="#" class="btn btn-outline-dark social-btn"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3 bg-light">
                        <p class="mb-0">Henüz hesabınız yok mu? <a href="register.php" class="text-primary">Kayıt ol</a></p>
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
});
</script>

<?php include 'includes/footer.php'; ?>
