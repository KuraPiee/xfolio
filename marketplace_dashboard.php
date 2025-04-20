<?php
// Marketplace dashboard sayfası - Kullanıcıların tekliflerini ve başvurularını yönettiği sayfa
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$userId = $_SESSION['user_id'];

// Aktif sekme
$section = isset($_GET['section']) ? sanitize($_GET['section']) : 'offers';

// İşlem mesajları
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Teklifin aktiflik durumunu değiştir
if (isset($_GET['toggle_status']) && isset($_GET['offer_id']) && is_numeric($_GET['offer_id'])) {
    $offerId = (int)$_GET['offer_id'];
    
    // Teklifin sahibi mi kontrol et
    $checkSql = "SELECT id FROM sponsorship_offers WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($stmt, "ii", $offerId, $userId);
    mysqli_stmt_execute($stmt);
    $checkResult = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $updateSql = "UPDATE sponsorship_offers SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($stmt, "i", $offerId);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Teklif durumu başarıyla güncellendi.";
        } else {
            $error = "Teklif durumu güncellenirken bir hata oluştu: " . mysqli_error($conn);
        }
    } else {
        $error = "Bu teklifi düzenleme yetkiniz yok.";
    }
}

// Başvuru durumunu güncelle
if (isset($_GET['update_status']) && isset($_GET['application_id']) && is_numeric($_GET['application_id'])) {
    $applicationId = (int)$_GET['application_id'];
    $status = sanitize($_GET['update_status']);
    
    $validStatuses = ['pending', 'approved', 'rejected', 'completed'];
    
    if (in_array($status, $validStatuses)) {
        // Başvurunun teklifinin sahibi mi kontrol et
        $checkSql = "SELECT a.id 
                    FROM sponsorship_applications a 
                    JOIN sponsorship_offers o ON a.offer_id = o.id 
                    WHERE a.id = ? AND o.user_id = ?";
        $stmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmt, "ii", $applicationId, $userId);
        mysqli_stmt_execute($stmt);
        $checkResult = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $updateSql = "UPDATE sponsorship_applications SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($stmt, "si", $status, $applicationId);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Başvuru durumu başarıyla güncellendi.";
                
                // Başvuru sahibine bildirim gönder
                $getApplicationSql = "SELECT user_id, offer_id FROM sponsorship_applications WHERE id = ?";
                $stmt = mysqli_prepare($conn, $getApplicationSql);
                mysqli_stmt_bind_param($stmt, "i", $applicationId);
                mysqli_stmt_execute($stmt);
                $appResult = mysqli_stmt_get_result($stmt);
                $application = mysqli_fetch_assoc($appResult);
                
                // Teklif başlığını al
                $getOfferSql = "SELECT title FROM sponsorship_offers WHERE id = ?";
                $stmt = mysqli_prepare($conn, $getOfferSql);
                mysqli_stmt_bind_param($stmt, "i", $application['offer_id']);
                mysqli_stmt_execute($stmt);
                $offerResult = mysqli_stmt_get_result($stmt);
                $offer = mysqli_fetch_assoc($offerResult);
                
                $notifType = 'application_update';
                
                switch ($status) {
                    case 'approved':
                        $notifContent = "'{$offer['title']}' teklifine yaptığınız başvuru kabul edildi!";
                        break;
                    case 'rejected':
                        $notifContent = "'{$offer['title']}' teklifine yaptığınız başvuru reddedildi.";
                        break;
                    case 'completed':
                        $notifContent = "'{$offer['title']}' iş birliği tamamlandı olarak işaretlendi.";
                        break;
                    default:
                        $notifContent = "'{$offer['title']}' teklifine yaptığınız başvurunun durumu güncellendi.";
                }
                
                $notifSql = "INSERT INTO notifications (user_id, sender_id, type, content) VALUES (?, ?, ?, ?)";
                $notifStmt = mysqli_prepare($conn, $notifSql);
                mysqli_stmt_bind_param($notifStmt, "iiss", $application['user_id'], $userId, $notifType, $notifContent);
                mysqli_stmt_execute($notifStmt);
            } else {
                $error = "Başvuru durumu güncellenirken bir hata oluştu: " . mysqli_error($conn);
            }
        } else {
            $error = "Bu başvuruyu güncelleme yetkiniz yok.";
        }
    } else {
        $error = "Geçersiz durum değeri.";
    }
}

// Kullanıcı kendi tekliflerini getir
$offersSql = "SELECT * FROM sponsorship_offers WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $offersSql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$offersResult = mysqli_stmt_get_result($stmt);

// Kullanıcı başvurularını getir
$applicationsSql = "SELECT a.*, o.title, o.category, o.collaboration_type, o.min_budget, o.max_budget, o.currency, o.deadline, 
                   u.username, u.avatar 
                   FROM sponsorship_applications a 
                   JOIN sponsorship_offers o ON a.offer_id = o.id 
                   JOIN users u ON o.user_id = u.id 
                   WHERE a.user_id = ? 
                   ORDER BY a.created_at DESC";
$stmt = mysqli_prepare($conn, $applicationsSql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$applicationsResult = mysqli_stmt_get_result($stmt);

// Kullanıcının tekliflerine gelen başvuruları getir
$receivedSql = "SELECT a.*, o.title, o.category, o.collaboration_type, 
               u.username, u.avatar, u.id as applicant_id
               FROM sponsorship_applications a 
               JOIN sponsorship_offers o ON a.offer_id = o.id 
               JOIN users u ON a.user_id = u.id 
               WHERE o.user_id = ? 
               ORDER BY a.created_at DESC";
$stmt = mysqli_prepare($conn, $receivedSql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$receivedResult = mysqli_stmt_get_result($stmt);

// Kullanıcının aktif iş birliklerini getir
$collaborationsSql = "SELECT a.*, o.title, o.category, o.collaboration_type, o.min_budget, o.max_budget, o.currency,
                     u.username, u.avatar, u.id as partner_id
                     FROM sponsorship_applications a 
                     JOIN sponsorship_offers o ON a.offer_id = o.id 
                     JOIN users u ON CASE WHEN a.user_id = ? THEN o.user_id ELSE a.user_id END = u.id
                     WHERE (a.user_id = ? OR o.user_id = ?) AND a.status = 'approved' 
                     ORDER BY a.updated_at DESC";
$stmt = mysqli_prepare($conn, $collaborationsSql);
mysqli_stmt_bind_param($stmt, "iii", $userId, $userId, $userId);
mysqli_stmt_execute($stmt);
$collaborationsResult = mysqli_stmt_get_result($stmt);

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
$pageTitle = "Marketplace Panel";

// Stiller için extra header
$extraHeader = '<link rel="stylesheet" href="css/marketplace.css">';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-8">
            <a href="marketplace.php" class="text-decoration-none mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-2"></i> Marketplace'e Dön
            </a>
            <h1 class="mb-2">Marketplace Panel</h1>
            <p class="text-muted">Sponsorluk tekliflerinizi ve başvurularınızı yönetin.</p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex align-items-center justify-content-lg-end mt-3 mt-lg-0">
            <a href="marketplace_create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Yeni Teklif Oluştur
            </a>
        </div>
    </div>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Sol Dashboard Navigasyonu -->
        <div class="col-lg-3 mb-4">
            <div class="dashboard-nav" data-aos="fade-right">
                <a href="?section=offers" class="nav-link <?php echo $section == 'offers' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i> Tekliflerim
                </a>
                <a href="?section=applications" class="nav-link <?php echo $section == 'applications' ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i> Başvurularım
                </a>
                <a href="?section=received" class="nav-link <?php echo $section == 'received' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i> Gelen Başvurular
                </a>
                <a href="?section=collaborations" class="nav-link <?php echo $section == 'collaborations' ? 'active' : ''; ?>">
                    <i class="fas fa-handshake"></i> Aktif İş Birlikleri
                </a>
                <a href="marketplace_guide.php" class="nav-link">
                    <i class="fas fa-question-circle"></i> Yardım & Rehber
                </a>
            </div>
        </div>
        
        <!-- Ana İçerik -->
        <div class="col-lg-9">
            <?php if ($section == 'offers'): ?>
                <!-- Tekliflerim -->
                <div class="card shadow-sm" data-aos="fade-up">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tekliflerim</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($offersResult) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Başlık</th>
                                            <th scope="col">Kategori</th>
                                            <th scope="col">Başvuru</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col">Son Başvuru</th>
                                            <th scope="col">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($offer = mysqli_fetch_assoc($offersResult)): 
                                            // Teklife gelen başvuru sayısını getir
                                            $appCountSql = "SELECT COUNT(*) AS count FROM sponsorship_applications WHERE offer_id = ?";
                                            $stmt = mysqli_prepare($conn, $appCountSql);
                                            mysqli_stmt_bind_param($stmt, "i", $offer['id']);
                                            mysqli_stmt_execute($stmt);
                                            $appCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
                                        ?>
                                            <tr>
                                                <td>
                                                    <a href="marketplace_detail.php?id=<?php echo $offer['id']; ?>" class="fw-bold text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($offer['title']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo isset($categories[$offer['category']]) ? $categories[$offer['category']] : 'Diğer'; ?>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill <?php echo $appCount > 0 ? 'bg-primary' : 'bg-secondary'; ?>">
                                                        <?php echo $appCount; ?> başvuru
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($offer['is_active']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $deadline = new DateTime($offer['deadline']);
                                                    $now = new DateTime();
                                                    $interval = $now->diff($deadline);
                                                    $isPast = $deadline < $now;
                                                    
                                                    if ($isPast) {
                                                        echo '<span class="text-danger">Süresi doldu</span>';
                                                    } else if ($interval->days == 0) {
                                                        echo '<span class="text-warning">Bugün</span>';
                                                    } else {
                                                        echo $interval->days . ' gün kaldı';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="marketplace_detail.php?id=<?php echo $offer['id']; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="marketplace_create.php?id=<?php echo $offer['id']; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?section=offers&toggle_status=1&offer_id=<?php echo $offer['id']; ?>" class="btn btn-outline-<?php echo $offer['is_active'] ? 'warning' : 'success'; ?>">
                                                            <i class="fas fa-<?php echo $offer['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                <h4>Henüz teklifiniz yok</h4>
                                <p class="text-muted mb-4">Yeni bir teklif oluşturarak içerik üreticileriyle iş birliği yapabilirsiniz.</p>
                                <a href="marketplace_create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Yeni Teklif Oluştur
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($section == 'applications'): ?>
                <!-- Başvurularım -->
                <div class="card shadow-sm" data-aos="fade-up">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Başvurularım</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($applicationsResult) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Teklif</th>
                                            <th scope="col">Teklif Sahibi</th>
                                            <th scope="col">Kategori</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col">Başvuru Tarihi</th>
                                            <th scope="col">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($application = mysqli_fetch_assoc($applicationsResult)): ?>
                                            <tr>
                                                <td>
                                                    <a href="marketplace_detail.php?id=<?php echo $application['offer_id']; ?>" class="fw-bold text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($application['title']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="profile.php?id=<?php echo $application['user_id']; ?>" class="text-decoration-none d-flex align-items-center">
                                                        <img src="uploads/avatars/<?php echo $application['avatar']; ?>" class="avatar-xs rounded-circle me-2" alt="<?php echo htmlspecialchars($application['username']); ?>" onerror="this.src='uploads/avatars/default-avatar.png'">
                                                        <?php echo htmlspecialchars($application['username']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo isset($categories[$application['category']]) ? $categories[$application['category']] : 'Diğer'; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    switch ($application['status']) {
                                                        case 'pending':
                                                            echo '<span class="status-badge pending">Beklemede</span>';
                                                            break;
                                                        case 'approved':
                                                            echo '<span class="status-badge approved">Onaylandı</span>';
                                                            break;
                                                        case 'rejected':
                                                            echo '<span class="status-badge rejected">Reddedildi</span>';
                                                            break;
                                                        case 'completed':
                                                            echo '<span class="status-badge completed">Tamamlandı</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="status-badge pending">Beklemede</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d.m.Y', strtotime($application['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="marketplace_detail.php?id=<?php echo $application['offer_id']; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                <h4>Henüz başvurunuz yok</h4>
                                <p class="text-muted mb-4">Marketplace'de teklifleri keşfederek başvurabilirsiniz.</p>
                                <a href="marketplace.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i> Teklifleri Keşfet
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($section == 'received'): ?>
                <!-- Gelen Başvurular -->
                <div class="card shadow-sm" data-aos="fade-up">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Gelen Başvurular</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($receivedResult) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Teklif</th>
                                            <th scope="col">Başvuran</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col">Başvuru Tarihi</th>
                                            <th scope="col">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($received = mysqli_fetch_assoc($receivedResult)): ?>
                                            <tr>
                                                <td>
                                                    <a href="marketplace_detail.php?id=<?php echo $received['offer_id']; ?>" class="fw-bold text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($received['title']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="profile.php?id=<?php echo $received['applicant_id']; ?>" class="text-decoration-none d-flex align-items-center">
                                                        <img src="uploads/avatars/<?php echo $received['avatar']; ?>" class="avatar-xs rounded-circle me-2" alt="<?php echo htmlspecialchars($received['username']); ?>" onerror="this.src='uploads/avatars/default-avatar.png'">
                                                        <?php echo htmlspecialchars($received['username']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php 
                                                    switch ($received['status']) {
                                                        case 'pending':
                                                            echo '<span class="status-badge pending">Beklemede</span>';
                                                            break;
                                                        case 'approved':
                                                            echo '<span class="status-badge approved">Onaylandı</span>';
                                                            break;
                                                        case 'rejected':
                                                            echo '<span class="status-badge rejected">Reddedildi</span>';
                                                            break;
                                                        case 'completed':
                                                            echo '<span class="status-badge completed">Tamamlandı</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="status-badge pending">Beklemede</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d.m.Y', strtotime($received['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="dropdownAction<?php echo $received['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            İşlem
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownAction<?php echo $received['id']; ?>">
                                                            <li>
                                                                <a class="dropdown-item" href="profile.php?id=<?php echo $received['applicant_id']; ?>">
                                                                    <i class="fas fa-user me-2"></i> Profili Görüntüle
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php if ($received['status'] == 'pending'): ?>
                                                                <li>
                                                                    <a class="dropdown-item text-success" href="?section=received&update_status=approved&application_id=<?php echo $received['id']; ?>">
                                                                        <i class="fas fa-check me-2"></i> Onayla
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="?section=received&update_status=rejected&application_id=<?php echo $received['id']; ?>">
                                                                        <i class="fas fa-times me-2"></i> Reddet
                                                                    </a>
                                                                </li>
                                                            <?php elseif ($received['status'] == 'approved'): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="?section=received&update_status=completed&application_id=<?php echo $received['id']; ?>">
                                                                        <i class="fas fa-check-double me-2"></i> Tamamlandı Olarak İşaretle
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h4>Henüz gelen başvuru yok</h4>
                                <p class="text-muted mb-4">Tekliflerinize henüz başvuru yapılmamış.</p>
                                <a href="marketplace_create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Yeni Teklif Oluştur
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($section == 'collaborations'): ?>
                <!-- Aktif İş Birlikleri -->
                <div class="card shadow-sm" data-aos="fade-up">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Aktif İş Birlikleri</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($collaborationsResult) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">İş Birliği</th>
                                            <th scope="col">İş Ortağı</th>
                                            <th scope="col">Kategori</th>
                                            <th scope="col">Onay Tarihi</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($collab = mysqli_fetch_assoc($collaborationsResult)): ?>
                                            <tr>
                                                <td>
                                                    <a href="marketplace_detail.php?id=<?php echo $collab['offer_id']; ?>" class="fw-bold text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($collab['title']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="profile.php?id=<?php echo $collab['partner_id']; ?>" class="text-decoration-none d-flex align-items-center">
                                                        <img src="uploads/avatars/<?php echo $collab['avatar']; ?>" class="avatar-xs rounded-circle me-2" alt="<?php echo htmlspecialchars($collab['username']); ?>" onerror="this.src='uploads/avatars/default-avatar.png'">
                                                        <?php echo htmlspecialchars($collab['username']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo isset($categories[$collab['category']]) ? $categories[$collab['category']] : 'Diğer'; ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d.m.Y', strtotime($collab['updated_at'])); ?>
                                                </td>
                                                <td>
                                                    <?php if ($collab['status'] == 'completed'): ?>
                                                        <span class="status-badge completed">Tamamlandı</span>
                                                    <?php else: ?>
                                                        <span class="status-badge approved">Aktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="marketplace_detail.php?id=<?php echo $collab['offer_id']; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($collab['status'] == 'approved'): ?>
                                                            <a href="?section=collaborations&update_status=completed&application_id=<?php echo $collab['id']; ?>" class="btn btn-outline-success">
                                                                <i class="fas fa-check-double"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                                <h4>Aktif iş birliğiniz yok</h4>
                                <p class="text-muted mb-4">Henüz onaylanmış bir iş birliği bulunmuyor.</p>
                                <div class="d-flex justify-content-center">
                                    <a href="marketplace.php" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-2"></i> Teklifleri Keşfet
                                    </a>
                                    <a href="?section=received" class="btn btn-outline-primary">
                                        <i class="fas fa-inbox me-2"></i> Gelen Başvurular
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
