<?php
// Marketplace sayfası - İçerik üreticileri ve markalar için buluşma platformu
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

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

// Aktif kategori
$activeCategory = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';

// Sayfalama için parametreler
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Arama sorgusu
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// SQL sorgusu oluştur
$where = "WHERE o.is_active = 1";
$params = [];
$types = "";

// Kategori filtresi
if ($activeCategory != 'all' && array_key_exists($activeCategory, $categories)) {
    $where .= " AND o.category = ?";
    $params[] = $activeCategory;
    $types .= "s";
}

// Arama sorgusu
if (!empty($search)) {
    $where .= " AND (o.title LIKE ? OR o.description LIKE ? OR u.username LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

// Toplam sponsorluk sayısını getir
$countSql = "SELECT COUNT(*) as total FROM sponsorship_offers o $where";
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $countSql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $countResult = mysqli_stmt_get_result($stmt);
    $totalOffers = mysqli_fetch_assoc($countResult)['total'];
} else {
    $countResult = mysqli_query($conn, $countSql);
    $totalOffers = mysqli_fetch_assoc($countResult)['total'];
}
$totalPages = ceil($totalOffers / $limit);

// Sponsorluk tekliflerini getir
$sql = "SELECT o.*, u.username, u.avatar, 
        (SELECT COUNT(*) FROM sponsorship_applications WHERE offer_id = o.id) as application_count 
        FROM sponsorship_offers o 
        JOIN users u ON o.user_id = u.id 
        $where 
        ORDER BY o.created_at DESC 
        LIMIT $limit OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

// Sayfa başlığı
$pageTitle = "Xfolio Marketplace";

// Stiller için extra header
$extraHeader = '<link rel="stylesheet" href="css/marketplace.css">';
include 'includes/header.php';
?>

<div class="container marketplace-page py-5">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="mb-2" data-aos="fade-up">Xfolio Marketplace</h1>
            <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                İçerik üreticileri ile markalar arasında bağlantı kurun, iş birliği fırsatları yakalayın
            </p>
        </div>
        <div class="col-lg-4 d-flex align-items-center justify-content-lg-end mt-3 mt-lg-0" data-aos="fade-left">
            <?php if ($isLoggedIn): ?>
                <a href="marketplace_create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Yeni Teklif Oluştur
                </a>
                <a href="marketplace_dashboard.php" class="btn btn-outline-primary ms-2">
                    <i class="fas fa-th-large me-2"></i> Panelim
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i> Giriş Yapın
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Arama ve Filtreleme -->
    <div class="card shadow-sm mb-4" data-aos="fade-up">
        <div class="card-body p-4">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Sponsorluk teklifleri ara..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="all" <?php echo $activeCategory == 'all' ? 'selected' : ''; ?>>Tüm Kategoriler</option>
                        <?php foreach ($categories as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $activeCategory == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Ana İçerik -->
        <div class="col-lg-9">
            <!-- Sponsorluk Teklifleri -->
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="row">
                    <?php while ($offer = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                            <div class="card h-100 offer-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="badge <?php echo getBadgeClass($offer['category']); ?>">
                                        <?php echo isset($categories[$offer['category']]) ? $categories[$offer['category']] : 'Diğer'; ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="far fa-clock me-1"></i> 
                                        <?php echo getTimeElapsed($offer['created_at']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <a href="profile.php?id=<?php echo $offer['user_id']; ?>" class="me-2">
                                            <img src="uploads/avatars/<?php echo $offer['avatar']; ?>" class="avatar-sm rounded-circle" alt="<?php echo htmlspecialchars($offer['username']); ?>" onerror="this.src='uploads/avatars/default-avatar.png'">
                                        </a>
                                        <div>
                                            <a href="profile.php?id=<?php echo $offer['user_id']; ?>" class="text-decoration-none">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($offer['username']); ?></h6>
                                            </a>
                                            <small class="text-muted"><?php echo $offer['is_brand'] ? 'Marka' : 'İçerik Üreticisi'; ?></small>
                                        </div>
                                    </div>
                                    
                                    <h5 class="card-title">
                                        <a href="marketplace_detail.php?id=<?php echo $offer['id']; ?>" class="text-dark text-decoration-none">
                                            <?php echo htmlspecialchars($offer['title']); ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text mb-3">
                                        <?php 
                                            $desc = strip_tags($offer['description']);
                                            echo strlen($desc) > 120 ? substr($desc, 0, 120) . '...' : $desc;
                                        ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <span class="badge bg-success">
                                                <i class="fas fa-coins me-1"></i> 
                                                <?php echo formatBudget($offer['min_budget'], $offer['max_budget'], $offer['currency']); ?>
                                            </span>
                                        </div>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-user-plus me-1"></i> 
                                            <?php echo $offer['application_count']; ?> başvuru
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <?php if ($offer['collaboration_type']): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-handshake me-1"></i> 
                                                    <?php echo getCollaborationType($offer['collaboration_type']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Son başvuru: <?php echo date('d.m.Y', strtotime($offer['deadline'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <a href="marketplace_detail.php?id=<?php echo $offer['id']; ?>" class="btn btn-outline-primary w-100">
                                        Detayları Görüntüle
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Sayfalama -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Sayfalama" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $activeCategory; ?>&search=<?php echo urlencode($search); ?>" aria-label="Önceki">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($startPage + 4, $totalPages);
                        
                        if ($startPage > 1): 
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&category=<?php echo $activeCategory; ?>&search=<?php echo urlencode($search); ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $activeCategory; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $totalPages; ?>&category=<?php echo $activeCategory; ?>&search=<?php echo urlencode($search); ?>"><?php echo $totalPages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $activeCategory; ?>&search=<?php echo urlencode($search); ?>" aria-label="Sonraki">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info text-center py-5" role="alert" data-aos="fade-up">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>Sonuç Bulunamadı</h4>
                    <p class="mb-0">Arama kriterlerinize uygun sponsorluk teklifi bulunamadı. Lütfen farklı bir arama terimi veya kategori deneyin.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Yan Panel -->
        <div class="col-lg-3">
            <!-- Hızlı Erişim -->
            <div class="card shadow-sm mb-4" data-aos="fade-left">
                <div class="card-header">
                    <h5 class="mb-0">Kategoriler</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="?category=all&search=<?php echo urlencode($search); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $activeCategory == 'all' ? 'active' : ''; ?>">
                            Tüm Kategoriler
                            <span class="badge bg-primary rounded-pill"><?php echo $totalOffers; ?></span>
                        </a>
                        
                        <?php
                        // Kategori başına teklif sayısını al
                        $categoryCounts = [];
                        $categoryCountQuery = "SELECT category, COUNT(*) as count FROM sponsorship_offers WHERE is_active = 1 GROUP BY category";
                        $categoryResult = mysqli_query($conn, $categoryCountQuery);
                        
                        while ($row = mysqli_fetch_assoc($categoryResult)) {
                            $categoryCounts[$row['category']] = $row['count'];
                        }
                        
                        // Kategorileri listele
                        foreach ($categories as $key => $value):
                            $count = isset($categoryCounts[$key]) ? $categoryCounts[$key] : 0;
                        ?>
                            <a href="?category=<?php echo $key; ?>&search=<?php echo urlencode($search); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $activeCategory == $key ? 'active' : ''; ?>">
                                <?php echo $value; ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $count; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Bilgilendirme -->
            <div class="card shadow-sm mb-4" data-aos="fade-left" data-aos-delay="100">
                <div class="card-header">
                    <h5 class="mb-0">Nasıl Çalışır?</h5>
                </div>
                <div class="card-body">
                    <div class="steps">
                        <div class="step mb-3">
                            <div class="step-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="step-content">
                                <h6>Profil Oluşturun</h6>
                                <p class="small text-muted mb-0">Profilinizi doldurun ve sosyal medya hesaplarınızı bağlayın.</p>
                            </div>
                        </div>
                        <div class="step mb-3">
                            <div class="step-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="step-content">
                                <h6>Teklifleri Keşfedin</h6>
                                <p class="small text-muted mb-0">Size uygun sponsorluk tekliflerini bulun.</p>
                            </div>
                        </div>
                        <div class="step mb-3">
                            <div class="step-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="step-content">
                                <h6>Başvuru Yapın</h6>
                                <p class="small text-muted mb-0">İlgilendiğiniz tekliflere başvurun.</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="step-content">
                                <h6>İş Birliği Yapın</h6>
                                <p class="small text-muted mb-0">Kabul edilen başvurularla çalışmaya başlayın.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="marketplace_guide.php" class="btn btn-sm btn-outline-primary">Detaylı Bilgi</a>
                </div>
            </div>
            
            <!-- Popüler Markalar -->
            <div class="card shadow-sm mb-4" data-aos="fade-left" data-aos-delay="200">
                <div class="card-header">
                    <h5 class="mb-0">Popüler Markalar</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    // Popüler markaları getir
                    $brandsSql = "SELECT u.id, u.username, u.avatar, COUNT(o.id) as offer_count 
                                FROM users u 
                                JOIN sponsorship_offers o ON u.id = o.user_id 
                                WHERE u.is_brand = 1 AND o.is_active = 1 
                                GROUP BY u.id 
                                ORDER BY offer_count DESC 
                                LIMIT 5";
                    $brandsResult = mysqli_query($conn, $brandsSql);
                    
                    if (mysqli_num_rows($brandsResult) > 0):
                    ?>
                        <ul class="list-group list-group-flush">
                            <?php while ($brand = mysqli_fetch_assoc($brandsResult)): ?>
                                <li class="list-group-item">
                                    <a href="profile.php?id=<?php echo $brand['id']; ?>" class="d-flex align-items-center text-decoration-none">
                                        <img src="uploads/avatars/<?php echo $brand['avatar']; ?>" alt="<?php echo htmlspecialchars($brand['username']); ?>" class="avatar-sm me-3" onerror="this.src='uploads/avatars/default-avatar.png'">
                                        <div>
                                            <h6 class="mb-0 text-dark"><?php echo htmlspecialchars($brand['username']); ?></h6>
                                            <small class="text-muted"><?php echo $brand['offer_count']; ?> aktif teklif</small>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-3 text-center">
                            <p class="text-muted mb-0">Henüz marka bulunamadı.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
 * İş birliği türünü döndürür
 * @param string $type İş birliği türü kodu
 * @return string İş birliği türü açıklaması
 */
function getCollaborationType($type) {
    $types = [
        'sponsored_post' => 'Sponsorlu Gönderi',
        'affiliate' => 'Satış Ortaklığı',
        'review' => 'Ürün İnceleme',
        'ambassador' => 'Marka Elçiliği',
        'event' => 'Etkinlik',
        'content_creation' => 'İçerik Üretimi',
        'influencer' => 'Etkileyici Pazarlama',
        'other' => 'Diğer'
    ];
    
    return isset($types[$type]) ? $types[$type] : 'Diğer';
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
