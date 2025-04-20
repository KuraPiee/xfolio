<?php
// Keşfet sayfası - Kullanıcıların diğer içerik üreticilerini keşfetmesi için
session_start();
require_once 'includes/config.php';

// Sayfalama için parametreler
$limit = 12; // Sayfa başına gösterilecek kullanıcı sayısı
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtreleme ve arama parametreleri
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : '';

// SQL sorgu koşullarını oluştur
$where = "WHERE is_verified = 1"; // Sadece doğrulanmış hesapları göster
$params = [];
$types = "";

// Arama sorgusu varsa
if (!empty($search)) {
    $where .= " AND (username LIKE ? OR bio LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Filtreleme seçenekleri
if ($filter == 'popular') {
    // Popüler kullanıcılar (en çok takipçisi olanlar)
    $orderBy = "ORDER BY follower_count DESC";
} elseif ($filter == 'new') {
    // Yeni kullanıcılar
    $orderBy = "ORDER BY u.created_at DESC";
} elseif ($filter == 'active') {
    // En aktif kullanıcılar (en çok portfolyo linki ekleyenler)
    $orderBy = "ORDER BY portfolio_count DESC";
} else {
    // Varsayılan sıralama
    $orderBy = "ORDER BY follower_count DESC, u.created_at DESC";
}

// Toplam kullanıcı sayısını getir
$countSql = "SELECT COUNT(*) AS total FROM users $where";
$stmt = mysqli_prepare($conn, $countSql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$countResult = mysqli_stmt_get_result($stmt);
$totalUsers = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalUsers / $limit);

// Kullanıcıları getir
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM followers WHERE followed_id = u.id) AS follower_count,
        (SELECT COUNT(*) FROM portfolios WHERE user_id = u.id) AS portfolio_count
        FROM users u
        $where
        $orderBy
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Giriş yapılmış mı kontrol et
$isLoggedIn = isset($_SESSION['user_id']);

// Popüler platformları getir
$platformsSql = "SELECT platform, COUNT(*) AS count 
                FROM portfolios 
                GROUP BY platform 
                ORDER BY count DESC 
                LIMIT 5";
$platformsResult = mysqli_query($conn, $platformsSql);

// Trend olan kullanıcıları getir (son 7 gün içinde en çok takipçi kazananlar)
$trendingSql = "SELECT u.id, u.username, u.avatar, u.bio, 
               COUNT(f.id) AS new_followers 
               FROM users u
               JOIN followers f ON u.id = f.followed_id
               WHERE f.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
               GROUP BY u.id
               ORDER BY new_followers DESC
               LIMIT 5";
$trendingResult = mysqli_query($conn, $trendingSql);

// Sayfa başlığı
$pageTitle = "Keşfet";
include 'includes/header.php';
?>

<div class="container discover-page">
    <div class="row">
        <!-- Sol kenar çubuğu -->
        <div class="col-lg-3">
            <div class="sidebar-container" data-aos="fade-right">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filtrele</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="GET" id="filterForm">
                            <div class="mb-3">
                                <label for="search" class="form-label">Ara</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Kullanıcı adı veya biyografi...">
                            </div>
                            <div class="mb-3">
                                <label for="filter" class="form-label">Sıralama</label>
                                <select class="form-select" id="filter" name="filter">
                                    <option value="" <?php echo $filter == '' ? 'selected' : ''; ?>>Varsayılan</option>
                                    <option value="popular" <?php echo $filter == 'popular' ? 'selected' : ''; ?>>Popüler</option>
                                    <option value="new" <?php echo $filter == 'new' ? 'selected' : ''; ?>>Yeni Katılanlar</option>
                                    <option value="active" <?php echo $filter == 'active' ? 'selected' : ''; ?>>En Aktif</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                        </form>
                    </div>
                </div>
                
                <!-- Popüler Platformlar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Popüler Platformlar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($platformsResult) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php while ($platform = mysqli_fetch_assoc($platformsResult)): ?>
                                    <?php
                                    $platformName = $platform['platform'];
                                    $platformClass = strtolower($platformName);
                                    $icon = "fa-link";
                                    
                                    switch ($platformClass) {
                                        case 'youtube': $icon = "fa-youtube"; break;
                                        case 'instagram': $icon = "fa-instagram"; break;
                                        case 'twitter': $icon = "fa-twitter"; break;
                                        case 'twitch': $icon = "fa-twitch"; break;
                                        case 'tiktok': $icon = "fa-tiktok"; break;
                                        case 'facebook': $icon = "fa-facebook-f"; break;
                                        case 'linkedin': $icon = "fa-linkedin-in"; break;
                                        case 'website': $icon = "fa-globe"; break;
                                    }
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fab <?php echo $icon; ?> me-2 platform-icon-sm <?php echo $platformClass; ?>"></i>
                                            <?php echo htmlspecialchars($platformName); ?>
                                        </span>
                                        <span class="badge bg-primary rounded-pill"><?php echo $platform['count']; ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted mb-0">Henüz platform verisi bulunmuyor.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Trend Olan Kullanıcılar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Yükselen Yıldızlar</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($trendingResult) > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php while ($trendingUser = mysqli_fetch_assoc($trendingResult)): ?>
                                    <li class="list-group-item">
                                        <a href="profile.php?id=<?php echo $trendingUser['id']; ?>" class="d-flex align-items-center text-decoration-none text-dark">
                                            <img src="uploads/avatars/<?php echo $trendingUser['avatar']; ?>" alt="<?php echo htmlspecialchars($trendingUser['username']); ?>" class="avatar-sm me-2" onerror="this.src='uploads/avatars/default-avatar.png'">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($trendingUser['username']); ?></h6>
                                                <small class="text-success">
                                                    <i class="fas fa-arrow-up"></i> 
                                                    <?php echo $trendingUser['new_followers']; ?> yeni takipçi
                                                </small>
                                            </div>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="p-3">
                                <p class="text-muted mb-0">Henüz trend olan kullanıcı bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ana içerik -->
        <div class="col-lg-9">
            <div class="discover-header mb-4" data-aos="fade-up">
                <h1>İçerik Üreticilerini Keşfet</h1>
                <p class="lead text-muted">Farklı platformlarda içerik üreten kişileri bulun ve takip edin</p>
            </div>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="row">
                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                            <div class="card user-card h-100 hover-effect">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <a href="profile.php?id=<?php echo $user['id']; ?>" class="user-card-img">
                                            <img src="uploads/avatars/<?php echo $user['avatar']; ?>" alt="<?php echo htmlspecialchars($user['username']); ?>" class="avatar-lg rounded-circle" onerror="this.src='uploads/avatars/default-avatar.png'">
                                        </a>
                                        <div class="ms-3">
                                            <h5 class="card-title mb-0">
                                                <a href="profile.php?id=<?php echo $user['id']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($user['username']); ?></a>
                                            </h5>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-users me-1"></i> <?php echo $user['follower_count']; ?> takipçi
                                            </p>
                                        </div>
                                    </div>
                                    <p class="card-text small mb-3">
                                        <?php 
                                        if (!empty($user['bio'])) {
                                            echo substr(htmlspecialchars($user['bio']), 0, 100) . (strlen($user['bio']) > 100 ? '...' : '');
                                        } else {
                                            echo '<span class="text-muted">Bio bilgisi eklenmemiş</span>';
                                        }
                                        ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="user-stats small text-muted">
                                            <span title="Portfolyo Link Sayısı"><i class="fas fa-link me-1"></i> <?php echo $user['portfolio_count']; ?></span>
                                            <span class="ms-2" title="Kayıt Tarihi"><i class="fas fa-calendar-alt me-1"></i> <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                                        </div>
                                        <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">Profili Gör</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Sayfalama -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Sayfalama" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>" aria-label="Önceki">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php
                            // Sayfa numaralarını göster
                            $startPage = max(1, min($page - 2, $totalPages - 4));
                            $endPage = min($totalPages, max(5, $page + 2));
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>" aria-label="Sonraki">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info" role="alert" data-aos="fade-up">
                    <i class="fas fa-info-circle me-2"></i> Arama kriterlerinize uygun kullanıcı bulunamadı. Lütfen farklı bir arama terimi deneyin veya filtreyi değiştirin.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtreleme formundaki select değiştiğinde formu otomatik gönder
    document.getElementById('filter').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
