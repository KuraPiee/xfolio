<?php
// Marketplace teklif detay sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

// Teklif ID'si kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: marketplace.php");
    exit();
}

$offerId = (int)$_GET['id'];

// Teklif detaylarını getir
$sql = "SELECT o.*, u.username, u.avatar, u.bio, u.email,
        (SELECT COUNT(*) FROM sponsorship_applications WHERE offer_id = o.id) as application_count 
        FROM sponsorship_offers o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.is_active = 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $offerId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Teklif bulunamadıysa marketplace ana sayfasına yönlendir
if (mysqli_num_rows($result) == 0) {
    header("Location: marketplace.php");
    exit();
}

$offer = mysqli_fetch_assoc($result);

// Kullanıcının bu teklife başvurup başvurmadığını kontrol et
$hasApplied = false;

if ($isLoggedIn) {
    $checkSql = "SELECT id FROM sponsorship_applications WHERE offer_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($stmt, "ii", $offerId, $userId);
    mysqli_stmt_execute($stmt);
    $checkResult = mysqli_stmt_get_result($stmt);
    $hasApplied = mysqli_num_rows($checkResult) > 0;
}

// Teklif sahibi mi kontrolü
$isOwner = $isLoggedIn && ($userId == $offer['user_id']);

// Başvuru işlemi
$applicationSuccess = false;
$applicationError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply']) && $isLoggedIn && !$isOwner && !$hasApplied) {
    $message = sanitize($_POST['message']);
    $contactEmail = sanitize($_POST['contact_email']);
    
    if (empty($message)) {
        $applicationError = "Lütfen mesajınızı girin.";
    } else if (empty($contactEmail) || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $applicationError = "Geçerli bir iletişim e-posta adresi girin.";
    } else {
        $applySql = "INSERT INTO sponsorship_applications (offer_id, user_id, message, contact_email, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $applySql);
        mysqli_stmt_bind_param($stmt, "iiss", $offerId, $userId, $message, $contactEmail);
        
        if (mysqli_stmt_execute($stmt)) {
            $applicationSuccess = true;
            $hasApplied = true;
            
            // Teklif sahibine bildirim gönder
            $notifType = 'application';
            $notifContent = "{$_SESSION['username']} {$offer['title']} teklifinize başvurdu.";
            $notifSql = "INSERT INTO notifications (user_id, sender_id, type, content) VALUES (?, ?, ?, ?)";
            $notifStmt = mysqli_prepare($conn, $notifSql);
            mysqli_stmt_bind_param($notifStmt, "iiss", $offer['user_id'], $userId, $notifType, $notifContent);
            mysqli_stmt_execute($notifStmt);
        } else {
            $applicationError = "Başvuru gönderilirken bir hata oluştu: " . mysqli_error($conn);
        }
    }
}

// Sponsorluk kategorileri
$categories = array(
    'youtube' => 'YouTube İçerik Üreticileri',
    'instagram' => 'Instagram Fenomenleri',
    'tiktok' => 'TikTok İçerik Üreticileri',
    'twitter' => 'Twitter Fenomenleri',
    'twitch' => 'Twitch Yayıncıları',
    'gaming' => 'Oyun İçerik Üreticileri',
    'lifestyle' => 'Yaşam Tarzı',
    'beauty' => 'Güzellik ve Moda',
    'tech' => 'Teknoloji',
    'food' => 'Yemek ve Mutfak',
    'travel' => 'Gezi ve Seyahat',
    'fitness' => 'Fitness ve Sağlık',
    'education' => 'Eğitim ve Öğretim',
    'business' => 'İş ve Girişimcilik',
    'other' => 'Diğer'
);

// İşbirliği türleri
$collaborationTypes = array(
    'sponsored_post' => 'Sponsorlu Gönderi',
    'affiliate' => 'Satış Ortaklığı',
    'review' => 'Ürün İnceleme',
    'ambassador' => 'Marka Elçiliği',
    'event' => 'Etkinlik',
    'content_creation' => 'İçerik Üretimi',
    'influencer' => 'Etkileyici Pazarlama',
    'other' => 'Diğer'
);

// Sayfa başlığı
$pageTitle = $offer['title'] . " - Xfolio Marketplace";

// Stiller için extra header
$extraHeader = '<link rel="stylesheet" href="css/marketplace.css">';
include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Teklif Başlığı ve Kontroller -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap">
        <div>
            <a href="marketplace.php" class="text-decoration-none mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-2"></i> Marketplace'e Dön
            </a>
            <h1 class="mb-0"><?php echo htmlspecialchars($offer['title']); ?></h1>
            <div class="d-flex align-items-center mt-2">
                <span class="badge <?php echo getBadgeClass($offer['category']); ?> me-2">
                    <?php echo isset($categories[$offer['category']]) ? $categories[$offer['category']] : 'Diğer'; ?>
                </span>
                <span class="text-muted">
                    <i class="far fa-clock me-1"></i> <?php echo getTimeElapsed($offer['created_at']); ?>
                </span>
            </div>
        </div>
        <div class="mt-3 mt-md-0">
            <?php if ($isLoggedIn): ?>
                <?php if ($isOwner): ?>
                    <a href="marketplace_edit.php?id=<?php echo $offer['id']; ?>" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit me-1"></i> Düzenle
                    </a>
                    <a href="marketplace_dashboard.php?section=offers" class="btn btn-primary">
                        <i class="fas fa-tasks me-1"></i> Başvuruları Yönet
                    </a>
                <?php else: ?>
                    <div class="d-flex">
                        <button class="btn btn-outline-primary me-2 share-dropdown-toggle" type="button">
                            <i class="fas fa-share-alt me-1"></i> Paylaş
                        </button>
                        <div class="share-dropdown">
                            <a href="#" class="share-button" data-platform="facebook" data-url="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>" data-title="<?php echo htmlspecialchars($offer['title']); ?>">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="share-button" data-platform="twitter" data-url="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>" data-title="<?php echo htmlspecialchars($offer['title']); ?>">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="share-button" data-platform="linkedin" data-url="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>" data-title="<?php echo htmlspecialchars($offer['title']); ?>">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="share-button" data-platform="whatsapp" data-url="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>" data-title="<?php echo htmlspecialchars($offer['title']); ?>">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="copy-link-button" data-url="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>">
                                <i class="fas fa-link"></i>
                            </a>
                        </div>
                        <?php if (!$hasApplied): ?>
                            <a href="#applicationForm" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Başvur
                            </a>
                        <?php else: ?>
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check me-1"></i> Başvuruldu
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-1"></i> Başvurmak için Giriş Yap
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Ana İçerik -->
        <div class="col-lg-8">
            <div class="card shadow-sm offer-detail-card mb-4" data-aos="fade-up">
                <!-- Başlık Kısmı -->
                <div class="offer-header">
                    <div class="offer-header-overlay"></div>
                    <div class="offer-header-content">
                        <div class="d-flex align-items-center mb-4">
                            <a href="profile.php?id=<?php echo $offer['user_id']; ?>" class="me-3">
                                <img src="uploads/avatars/<?php echo $offer['avatar']; ?>" class="avatar-md rounded-circle border border-2 border-white" alt="<?php echo htmlspecialchars($offer['username']); ?>" onerror="this.src='uploads/avatars/default-avatar.png'">
                            </a>
                            <div>
                                <a href="profile.php?id=<?php echo $offer['user_id']; ?>" class="text-white text-decoration-none">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($offer['username']); ?></h5>
                                </a>
                                <p class="mb-0 text-white-50">
                                    <?php echo $offer['is_brand'] ? 'Marka' : 'İçerik Üreticisi'; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="offer-stats d-flex flex-wrap">
                            <div class="me-4 mb-3">
                                <div class="text-white-50 small">Bütçe</div>
                                <div class="fw-bold text-white">
                                    <?php echo formatBudget($offer['min_budget'], $offer['max_budget'], $offer['currency']); ?>
                                </div>
                            </div>
                            
                            <div class="me-4 mb-3">
                                <div class="text-white-50 small">İş Birliği Türü</div>
                                <div class="fw-bold text-white">
                                    <?php echo isset($collaborationTypes[$offer['collaboration_type']]) ? $collaborationTypes[$offer['collaboration_type']] : 'Diğer'; ?>
                                </div>
                            </div>
                            
                            <div class="me-4 mb-3">
                                <div class="text-white-50 small">Son Başvuru</div>
                                <div class="fw-bold text-white">
                                    <?php echo date('d.m.Y', strtotime($offer['deadline'])); ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-white-50 small">Başvuru</div>
                                <div class="fw-bold text-white">
                                    <?php echo $offer['application_count']; ?> kişi
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detaylar -->
                <div class="card-body p-4">
                    <div class="mb-4" data-aos="fade-up">
                        <h5>Açıklama</h5>
                        <div class="description">
                            <?php echo nl2br(htmlspecialchars($offer['description'])); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($offer['requirements'])): ?>
                    <div class="offer-requirements mb-4" data-aos="fade-up">
                        <h5>Aranan Özellikler</h5>
                        <div class="requirements">
                            <?php 
                            $requirements = explode("\n", $offer['requirements']);
                            foreach ($requirements as $requirement): 
                                if (trim($requirement) !== ''):
                            ?>
                                <div class="requirement-item">
                                    <div class="requirement-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="requirement-content">
                                        <?php echo htmlspecialchars(trim($requirement)); ?>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($offer['deliverables'])): ?>
                    <div class="mb-4" data-aos="fade-up">
                        <h5>Teslim Edilecekler</h5>
                        <div class="deliverables">
                            <?php echo nl2br(htmlspecialchars($offer['deliverables'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Başvuru Formu -->
            <?php if ($isLoggedIn && !$isOwner && !$hasApplied): ?>
            <div class="card shadow-sm mb-4" data-aos="fade-up" id="applicationForm">
                <div class="card-header">
                    <h5 class="mb-0">Başvuru Formu</h5>
                </div>
                <div class="card-body">
                    <?php if ($applicationSuccess): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i> Başvurunuz başarıyla gönderildi! Teklif sahibi değerlendirdikten sonra sizinle iletişime geçecektir.
                        </div>
                    <?php elseif (!empty($applicationError)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $applicationError; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">İletişim E-posta Adresi</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo $isLoggedIn ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
                            <small class="text-muted">Teklif sahibi sizinle bu e-posta üzerinden iletişime geçecektir.</small>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Mesajınız</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Kendinizi tanıtın ve bu iş birliğinde neler sunabileceğinizden bahsedin..." required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms_check" required>
                            <label class="form-check-label" for="terms_check">
                                <a href="privacy_policy.php" target="_blank">Gizlilik Politikasını</a> okudum ve kabul ediyorum.
                            </label>
                        </div>
                        <button type="submit" name="apply" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Başvuru Gönder
                        </button>
                    </form>
                </div>
            </div>
            <?php elseif ($hasApplied): ?>
            <div class="card shadow-sm mb-4" data-aos="fade-up">
                <div class="card-header">
                    <h5 class="mb-0">Başvuru Durumu</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success mb-0" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Bu teklife zaten başvurdunuz. Teklif sahibi inceledikten sonra sizinle iletişime geçecektir.
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Benzer Teklifler -->
            <div class="similar-offers mt-4" data-aos="fade-up">
                <h4 class="mb-3">Benzer Teklifler</h4>
                <?php
                // Aynı kategorideki benzer teklifleri getir
                $similarSql = "SELECT o.*, u.username, u.avatar 
                              FROM sponsorship_offers o 
                              JOIN users u ON o.user_id = u.id 
                              WHERE o.category = ? AND o.id != ? AND o.is_active = 1 
                              ORDER BY o.created_at DESC 
                              LIMIT 3";
                $stmt = mysqli_prepare($conn, $similarSql);
                mysqli_stmt_bind_param($stmt, "si", $offer['category'], $offer['id']);
                mysqli_stmt_execute($stmt);
                $similarResult = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($similarResult) > 0):
                ?>
                <div class="row">
                    <?php while ($similarOffer = mysqli_fetch_assoc($similarResult)): ?>
                    <div class="col-md-4 mb-4" data-aos="fade-up">
                        <div class="card h-100 offer-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge <?php echo getBadgeClass($similarOffer['category']); ?>">
                                    <?php echo isset($categories[$similarOffer['category']]) ? $categories[$similarOffer['category']] : 'Diğer'; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <a href="profile.php?id=<?php echo $similarOffer['user_id']; ?>" class="me-2">
                                        <img src="uploads/avatars/<?php echo $similarOffer['avatar']; ?>" class="avatar-sm rounded-circle" alt="<?php echo htmlspecialchars($similarOffer['username']); ?>" onerror="this.src='uploads/avatars/default-avatar.png'">
                                    </a>
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($similarOffer['username']); ?></h6>
                                    </div>
                                </div>
                                
                                <h6 class="card-title">
                                    <a href="marketplace_detail.php?id=<?php echo $similarOffer['id']; ?>" class="text-dark text-decoration-none">
                                        <?php echo htmlspecialchars($similarOffer['title']); ?>
                                    </a>
                                </h6>
                                
                                <div class="mt-3">
                                    <span class="badge bg-success">
                                        <?php echo formatBudget($similarOffer['min_budget'], $similarOffer['max_budget'], $similarOffer['currency']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="marketplace_detail.php?id=<?php echo $similarOffer['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                    Detayları Görüntüle
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-light text-center p-4">
                    <i class="fas fa-search fa-2x mb-3 text-muted"></i>
                    <p class="mb-0">Bu kategoride başka teklif bulunamadı.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Yan Panel -->
        <div class="col-lg-4">
            <!-- Teklif Sahibi -->
            <div class="card shadow-sm mb-4" data-aos="fade-left">
                <div class="card-header">
                    <h5 class="mb-0">Teklif Sahibi</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <a href="profile.php?id=<?php echo $offer['user_id']; ?>">
                            <img src="uploads/avatars/<?php echo $offer['avatar']; ?>" class="avatar-lg rounded-circle" alt="<?php echo htmlspecialchars($offer['username']); ?>" onerror="this.src='uploads/avatars/default-avatar.png'">
                        </a>
                        <h5 class="mt-3"><?php echo htmlspecialchars($offer['username']); ?></h5>
                        <p class="badge bg-primary"><?php echo $offer['is_brand'] ? 'Marka' : 'İçerik Üreticisi'; ?></p>
                    </div>
                    
                    <?php if (!empty($offer['bio'])): ?>
                    <div class="user-bio mb-3">
                        <p class="mb-0"><?php echo htmlspecialchars($offer['bio']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="profile.php?id=<?php echo $offer['user_id']; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i> Profili Görüntüle
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Teklif Bilgileri -->
            <div class="card shadow-sm mb-4" data-aos="fade-left">
                <div class="card-header">
                    <h5 class="mb-0">Teklif Bilgileri</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Bütçe</span>
                            <span class="fw-bold"><?php echo formatBudget($offer['min_budget'], $offer['max_budget'], $offer['currency']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>İş Birliği Türü</span>
                            <span class="fw-bold"><?php echo isset($collaborationTypes[$offer['collaboration_type']]) ? $collaborationTypes[$offer['collaboration_type']] : 'Diğer'; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Kategori</span>
                            <span class="fw-bold"><?php echo isset($categories[$offer['category']]) ? $categories[$offer['category']] : 'Diğer'; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Yayınlanma Tarihi</span>
                            <span class="fw-bold"><?php echo date('d.m.Y', strtotime($offer['created_at'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Son Başvuru Tarihi</span>
                            <span class="fw-bold"><?php echo date('d.m.Y', strtotime($offer['deadline'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Başvurular</span>
                            <span class="fw-bold"><?php echo $offer['application_count']; ?> kişi</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- QR Kod -->
            <div class="card shadow-sm mb-4" data-aos="fade-left">
                <div class="card-header">
                    <h5 class="mb-0">QR Kod</h5>
                </div>
                <div class="card-body text-center">
                    <div class="auto-generate-qr" data-url="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>"></div>
                    <p class="small text-muted mt-2">Teklifi hızlıca paylaşmak için QR kodu kullanabilirsiniz.</p>
                </div>
            </div>
            
            <!-- Rapor Et -->
            <div class="card shadow-sm" data-aos="fade-left">
                <div class="card-body">
                    <a href="#" class="text-decoration-none text-muted small d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#reportModal">
                        <i class="fas fa-flag me-2"></i> Bu teklifi rapor et
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rapor Et Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Teklifi Rapor Et</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bu teklif hakkında bir sorun mu var?</p>
                <form id="reportForm">
                    <div class="mb-3">
                        <label for="reportReason" class="form-label">Rapor Sebebi</label>
                        <select class="form-select" id="reportReason" required>
                            <option value="">Bir sebep seçin</option>
                            <option value="spam">Spam veya yanıltıcı içerik</option>
                            <option value="inappropriate">Uygunsuz içerik</option>
                            <option value="scam">Dolandırıcılık</option>
                            <option value="other">Diğer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reportDescription" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="reportDescription" rows="3" placeholder="Lütfen problemi detaylı olarak açıklayın..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary report-submit-btn">Rapor Gönder</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rapor gönderme işlemi
    const reportSubmitBtn = document.querySelector('.report-submit-btn');
    if (reportSubmitBtn) {
        reportSubmitBtn.addEventListener('click', function() {
            // Burada normalde AJAX ile rapor gönderme işlemi yapılır
            // Bu basit örnek için sadece bildirim gösteriyoruz
            alert('Raporunuz alındı. Teşekkür ederiz!');
            
            // Modal'ı kapat
            const reportModal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
            reportModal.hide();
            
            // Form'u temizle
            document.getElementById('reportForm').reset();
        });
    }
});
</script>

<?php
/**
 * Bütçe aralığını formatlar
 * @param float $min Minimum bütçe
 * @param float $max Maximum bütçe
 * @param string $currency Para birimi
 * @return string Formatlanmış bütçe aralığı
 */
function formatBudget($min, $max, $currency = 'TL') {
    if ($min == $max) {
        return number_format($min, 0, ',', '.') . ' ' . $currency;
    } else if ($min == 0 && $max > 0) {
        return number_format($max, 0, ',', '.') . ' ' . $currency . ' kadar';
    } else if ($min > 0 && $max == 0) {
        return number_format($min, 0, ',', '.') . ' ' . $currency . ' ve üzeri';
    } else {
        return number_format($min, 0, ',', '.') . ' - ' . number_format($max, 0, ',', '.') . ' ' . $currency;
    }
}

/**
 * Kategori için uygun badge sınıfını döndürür
 * @param string $category Kategori kodu
 * @return string Badge sınıfı
 */
function getBadgeClass($category) {
    $classes = [
        'youtube' => 'bg-danger',
        'instagram' => 'bg-instagram',
        'tiktok' => 'bg-dark',
        'twitter' => 'bg-twitter',
        'twitch' => 'bg-twitch',
        'gaming' => 'bg-success',
        'lifestyle' => 'bg-info',
        'beauty' => 'bg-pink',
        'tech' => 'bg-secondary',
        'food' => 'bg-warning',
        'travel' => 'bg-primary',
        'fitness' => 'bg-success',
        'education' => 'bg-info',
        'business' => 'bg-dark',
        'other' => 'bg-secondary'
    ];
    
    return isset($classes[$category]) ? $classes[$category] : 'bg-secondary';
}

/**
 * Geçen süreyi formatlar
 * @param string $datetime Tarih ve saat
 * @return string Formatlanmış süre
 */
function getTimeElapsed($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Az önce';
    } else if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' dakika önce';
    } else if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' saat önce';
    } else if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' gün önce';
    } else if ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' hafta önce';
    } else if ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' ay önce';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' yıl önce';
    }
}
?>

<?php include 'includes/footer.php'; ?>
