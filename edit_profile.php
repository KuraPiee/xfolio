<?php
// Profil düzenleme sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcı bilgilerini getir
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['message'] = "Kullanıcı bulunamadı.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit();
}

$user = mysqli_fetch_assoc($result);
$message = "";
$messageType = "";

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Biyografi güncelleme
    if (isset($_POST['update_bio'])) {
        $bio = sanitize($_POST['bio']);
        
        $updateSql = "UPDATE users SET bio = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($stmt, "si", $bio, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Biyografiniz başarıyla güncellendi.";
            $messageType = "success";
            $user['bio'] = $bio;
        } else {
            $message = "Biyografi güncellenirken bir hata oluştu: " . mysqli_error($conn);
            $messageType = "danger";
        }
    }
    
    // Şifre değiştirme
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Şifre doğrulama
        if (password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                if (strlen($newPassword) >= 6) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    $updateSql = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $updateSql);
                    mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $user_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Şifreniz başarıyla güncellendi.";
                        $messageType = "success";
                    } else {
                        $message = "Şifre güncellenirken bir hata oluştu: " . mysqli_error($conn);
                        $messageType = "danger";
                    }
                } else {
                    $message = "Yeni şifre en az 6 karakter uzunluğunda olmalıdır.";
                    $messageType = "danger";
                }
            } else {
                $message = "Yeni şifreler eşleşmiyor.";
                $messageType = "danger";
            }
        } else {
            $message = "Mevcut şifre yanlış.";
            $messageType = "danger";
        }
    }
    
    // Profil fotoğrafı güncelleme
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $filetype = $_FILES['avatar']['type'];
        $filesize = $_FILES['avatar']['size'];
        
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($ext), $allowed)) {
            $message = "Lütfen sadece JPG, JPEG, PNG veya GIF dosyaları yükleyin.";
            $messageType = "danger";
        } elseif ($filesize > 5242880) { // 5MB
            $message = "Dosya boyutu çok büyük. Maksimum 5MB yükleyebilirsiniz.";
            $messageType = "danger";
        } else {
            // Dosya adını benzersiz yap ve yükle
            $newFilename = $user_id . '_' . time() . '.' . $ext;
            $uploadPath = 'uploads/avatars/' . $newFilename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                // Eski avatarı sil (varsayılan avatar değilse)
                if ($user['avatar'] != 'default-avatar.png' && file_exists('uploads/avatars/' . $user['avatar'])) {
                    unlink('uploads/avatars/' . $user['avatar']);
                }
                
                $updateSql = "UPDATE users SET avatar = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($stmt, "si", $newFilename, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Profil fotoğrafı başarıyla güncellendi.";
                    $messageType = "success";
                    $user['avatar'] = $newFilename;
                } else {
                    $message = "Profil fotoğrafı güncellenirken bir hata oluştu: " . mysqli_error($conn);
                    $messageType = "danger";
                }
            } else {
                $message = "Dosya yüklenirken bir hata oluştu.";
                $messageType = "danger";
            }
        }
    }
    
    // Telefon numarası güncelleme
    if (isset($_POST['update_phone'])) {
        $phone = sanitize($_POST['phone']);
        
        $updateSql = "UPDATE users SET phone = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($stmt, "si", $phone, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Telefon numaranız başarıyla güncellendi.";
            $messageType = "success";
            $user['phone'] = $phone;
        } else {
            $message = "Telefon numarası güncellenirken bir hata oluştu: " . mysqli_error($conn);
            $messageType = "danger";
        }
    }
}

// Sayfa başlığı
$pageTitle = "Profili Düzenle";
include 'includes/header.php';
?>

<div class="container profile-edit-page">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="page-header mb-4" data-aos="fade-up">
                <h1>Profil Bilgilerini Düzenle</h1>
                <p class="text-muted">Profilinizi güncelleyin ve kişiselleştirin</p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm mb-4" data-aos="fade-up">
                <div class="card-header">
                    <h5 class="mb-0">Profil Fotoğrafı</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data" class="profile-image-form">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <img src="uploads/avatars/<?php echo $user['avatar']; ?>" alt="<?php echo $user['username']; ?>" class="avatar-xl rounded-circle mb-3" onerror="this.src='uploads/avatars/default-avatar.png'">
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Yeni Fotoğraf Yükle</label>
                                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                                    <small class="form-text text-muted">JPG, JPEG, PNG veya GIF. Maksimum 5MB.</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Fotoğrafı Güncelle</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header">
                    <h5 class="mb-0">Kişisel Bilgiler</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post" class="bio-form">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="form-text text-muted">Kullanıcı adı değiştirilemez.</small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            <small class="form-text text-muted">E-posta adresi değiştirilemez.</small>
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Biyografi</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="500" placeholder="Kendinizden bahsedin..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            <small class="form-text text-muted">Kendiniz hakkında kısa bir açıklama yazın (maksimum 500 karakter).</small>
                        </div>
                        <button type="submit" name="update_bio" class="btn btn-primary">Biyografiyi Kaydet</button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h5 class="mb-0">Telefon Numarası</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post" class="phone-form">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefon Numarası</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            <small class="form-text text-muted">Telefon numaranız diğer kullanıcılara gösterilmeyecektir.</small>
                        </div>
                        <button type="submit" name="update_phone" class="btn btn-primary">Telefonu Güncelle</button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header">
                    <h5 class="mb-0">Şifre Değiştir</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post" class="password-form">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mevcut Şifre</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">Şifreniz en az 6 karakter uzunluğunda olmalıdır.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Yeni Şifre Tekrar</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Şifremi Değiştir</button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mb-5">
                <a href="profile.php" class="btn btn-secondary">Profilime Dön</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
