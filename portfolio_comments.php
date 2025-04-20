<?php
// Portfolyo detay ve yorum sayfası
session_start();
require_once 'includes/config.php';

// URL'den portfolyo ID parametresini al
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$portfolio_id = (int)$_GET['id'];

// Portfolyo bilgilerini getir
$sql = "SELECT p.*, u.username, u.avatar, u.bio 
        FROM portfolios p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $portfolio_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Portfolyo bulunamadıysa ana sayfaya yönlendir
if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$portfolio = mysqli_fetch_assoc($result);

// Platform ikonları ve renkleri
$platformClass = strtolower($portfolio['platform']);
$icon = "fa-link";

if ($platformClass == 'youtube') {
    $icon = "fa-youtube";
} elseif ($platformClass == 'instagram') {
    $icon = "fa-instagram";
} elseif ($platformClass == 'twitter') {
    $icon = "fa-twitter";
} elseif ($platformClass == 'twitch') {
    $icon = "fa-twitch";
} elseif ($platformClass == 'tiktok') {
    $icon = "fa-tiktok";
} elseif ($platformClass == 'facebook') {
    $icon = "fa-facebook-f";
} elseif ($platformClass == 'linkedin') {
    $icon = "fa-linkedin-in";
} elseif ($platformClass == 'website') {
    $icon = "fa-globe";
}

// Kullanıcının giriş yapmış olup olmadığını kontrol et
$isLoggedIn = isset($_SESSION['user_id']);
$isOwnPortfolio = $isLoggedIn && ($_SESSION['user_id'] == $portfolio['user_id']);

// Yorum ekleme işlemi
$commentError = '';
$commentSuccess = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $isLoggedIn) {
    if (isset($_POST['add_comment'])) {
        $comment = sanitize($_POST['comment']);
        
        if (empty($comment)) {
            $commentError = "Yorum alanı boş bırakılamaz.";
        } else {
            $sql = "INSERT INTO comments (user_id, portfolio_id, comment) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iis", $_SESSION['user_id'], $portfolio_id, $comment);
            
            if (mysqli_stmt_execute($stmt)) {
                $commentSuccess = "Yorumunuz başarıyla eklendi.";
                
                // Portfolyo sahibine bildirim gönder (kendi yorumu değilse)
                if ($_SESSION['user_id'] != $portfolio['user_id']) {
                    $notifType = 'comment';
                    $notifContent = "{$_SESSION['username']} {$portfolio['platform']} bağlantınıza yorum yaptı.";
                    $notifSql = "INSERT INTO notifications (user_id, sender_id, type, content) VALUES (?, ?, ?, ?)";
                    $notifStmt = mysqli_prepare($conn, $notifSql);
                    mysqli_stmt_bind_param($notifStmt, "iiss", $portfolio['user_id'], $_SESSION['user_id'], $notifType, $notifContent);
                    mysqli_stmt_execute($notifStmt);
                }
            } else {
                $commentError = "Yorum eklenirken bir hata oluştu: " . mysqli_error($conn);
            }
        }
    }
    
    // Yorum silme işlemi
    if (isset($_POST['delete_comment']) && isset($_POST['comment_id'])) {
        $comment_id = (int)$_POST['comment_id'];
        
        // Yorumu kimin yazdığını kontrol et
        $checkSql = "SELECT user_id FROM comments WHERE id = ?";
        $stmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmt, "i", $comment_id);
        mysqli_stmt_execute($stmt);
        $checkResult = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($checkResult)) {
            // Kendi yorumu veya portfolyo sahibi ise silebilir
            if ($row['user_id'] == $_SESSION['user_id'] || $isOwnPortfolio) {
                $deleteSql = "DELETE FROM comments WHERE id = ?";
                $stmt = mysqli_prepare($conn, $deleteSql);
                mysqli_stmt_bind_param($stmt, "i", $comment_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $commentSuccess = "Yorum başarıyla silindi.";
                } else {
                    $commentError = "Yorum silinirken bir hata oluştu: " . mysqli_error($conn);
                }
            } else {
                $commentError = "Bu yorumu silme yetkiniz yok.";
            }
        } else {
            $commentError = "Yorum bulunamadı.";
        }
    }
}

// Yorumları getir
$commentsSql = "SELECT c.*, u.username, u.avatar 
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.portfolio_id = ?
                ORDER BY c.created_at DESC";
$stmt = mysqli_prepare($conn, $commentsSql);
mysqli_stmt_bind_param($stmt, "i", $portfolio_id);
mysqli_stmt_execute($stmt);
$commentsResult = mysqli_stmt_get_result($stmt);

// Portfolyonun bağlantı istatistiklerini getir (eğer API verileri eklenecekse)
$stats = [
    'followers' => $portfolio['followers'],
    'views' => 0,
    'clicks' => 0,
    'last_updated' => date('Y-m-d H:i:s')
];

// Benzer portfolyoları getir (aynı platform, farklı kullanıcılar)
$similarSql = "SELECT p.*, u.username, u.avatar 
               FROM portfolios p
               JOIN users u ON p.user_id = u.id
               WHERE p.platform = ? AND p.id != ? AND p.user_id != ?
               ORDER BY p.followers DESC
               LIMIT 3";
$stmt = mysqli_prepare($conn, $similarSql);
mysqli_stmt_bind_param($stmt, "sii", $portfolio['platform'], $portfolio_id, $portfolio['user_id']);
mysqli_stmt_execute($stmt);
$similarResult = mysqli_stmt_get_result($stmt);

// Sayfa başlığı
$pageTitle = $portfolio['platform'] . " - " . ($portfolio['title'] ?: $portfolio['platform']) . " Bağlantısı";
include 'includes/header.php';
?>

<div class="container portfolio-details-page">
    <div class="row">
        <div class="col-lg-8">
            <!-- Portfolyo Detayları -->
            <div class="card shadow-sm mb-4" data-aos="fade-up">
                <div class="card-header d-flex align-items-center">
                    <div class="platform-icon-lg <?php echo $platformClass; ?> me-3">
                        <i class="fab <?php echo $icon; ?>"></i>
                    </div>
                    <div>
                        <h2 class="mb-1"><?php echo htmlspecialchars($portfolio['title'] ?: $portfolio['platform']); ?></h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d.m.Y', strtotime($portfolio['created_at'])); ?> tarihinde eklendi
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="mb-3">Bağlantı Bilgileri</h5>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($portfolio['link']); ?>" readonly id="portfolioLink">
                                <button class="btn btn-outline-primary copy-btn" type="button" data-clipboard-target="#portfolioLink">
                                    <i class="fas fa-copy"></i> Kopyala
                                </button>
                                <a href="<?php echo htmlspecialchars($portfolio['link']); ?>" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt"></i> Ziyaret Et
                                </a>
                            </div>
                            <div class="portfolio-owner d-flex align-items-center">
                                <a href="profile.php?id=<?php echo $portfolio['user_id']; ?>" class="d-flex align-items-center text-decoration-none text-dark">
                                    <img src="uploads/avatars/<?php echo $portfolio['avatar']; ?>" alt="<?php echo htmlspecialchars($portfolio['username']); ?>" class="avatar-sm me-2" onerror="this.src='uploads/avatars/default-avatar.png'">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($portfolio['username']); ?></h6>
                                        <small class="text-muted"><?php echo substr($portfolio['bio'], 0, 60) . (strlen($portfolio['bio']) > 60 ? '...' : ''); ?></small>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5 class="mb-3">İstatistikler</h5>
                            <div class="stats-box bg-light p-3 rounded">
                                <div class="mb-2">
                                    <i class="fas fa-users me-2 text-primary"></i>
                                    <span class="stat-label">Takipçi:</span>
                                    <span class="stat-value"><?php echo number_format($stats['followers']); ?></span>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-eye me-2 text-info"></i>
                                    <span class="stat-label">Görüntülenme:</span>
                                    <span class="stat-value"><?php echo number_format($stats['views']); ?></span>
                                </div>
                                <div>
                                    <i class="fas fa-mouse-pointer me-2 text-success"></i>
                                    <span class="stat-label">Tıklama:</span>
                                    <span class="stat-value"><?php echo number_format($stats['clicks']); ?></span>
                                </div>
                                <div class="mt-2 small text-muted">
                                    <i class="fas fa-sync-alt me-1"></i> Son güncelleme: <?php echo date('d.m.Y H:i', strtotime($stats['last_updated'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="social-share d-flex align-items-center justify-content-between">
                        <span class="me-2">Bu bağlantıyı paylaş:</span>
                        <div class="social-share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode("https://xfolio.xren.com.tr/portfolio_comments.php?id=$portfolio_id"); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode("https://xfolio.xren.com.tr/portfolio_comments.php?id=$portfolio_id"); ?>&text=<?php echo urlencode("Xfolio'da {$portfolio['username']}'in {$portfolio['platform']} hesabına göz atın!"); ?>" target="_blank" class="btn btn-sm btn-outline-info me-1">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode("Xfolio'da {$portfolio['username']}'in {$portfolio['platform']} hesabına göz atın: https://xfolio.xren.com.tr/portfolio_comments.php?id=$portfolio_id"); ?>" target="_blank" class="btn btn-sm btn-outline-success me-1">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode("Xfolio'da {$portfolio['username']}'in {$portfolio['platform']} hesabı"); ?>&body=<?php echo urlencode("Merhaba,\n\nXfolio'da {$portfolio['username']}'in {$portfolio['platform']} hesabına göz atmanı öneririm: https://xfolio.xren.com.tr/portfolio_comments.php?id=$portfolio_id"); ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Yorumlar Bölümü -->
            <div class="comments-section mb-4" data-aos="fade-up" data-aos-delay="100">
                <h3 class="mb-3">Yorumlar</h3>
                
                <?php if ($isLoggedIn): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <?php if (!empty($commentError)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $commentError; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($commentSuccess)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo $commentSuccess; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form action="" method="post" class="comment-form">
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Yorum Yap</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Bu bağlantı hakkında düşüncelerinizi paylaşın..."></textarea>
                                </div>
                                <button type="submit" name="add_comment" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Yorum Gönder
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i> Yorum yapabilmek için <a href="login.php">giriş yapmalısınız</a>.
                    </div>
                <?php endif; ?>
                
                <div class="comments-list">
                    <?php if (mysqli_num_rows($commentsResult) > 0): ?>
                        <?php while ($comment = mysqli_fetch_assoc($commentsResult)): ?>
                            <div class="card shadow-sm mb-3 comment" data-aos="fade-up" data-aos-delay="150">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="me-3">
                                            <img src="uploads/avatars/<?php echo $comment['avatar']; ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>" class="avatar-sm" onerror="this.src='uploads/avatars/default-avatar.png'">
                                        </a>
                                        <div class="comment-content w-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">
                                                    <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="text-dark text-decoration-none">
                                                        <?php echo htmlspecialchars($comment['username']); ?>
                                                    </a>
                                                    <?php if ($comment['user_id'] == $portfolio['user_id']): ?>
                                                        <span class="badge bg-primary ms-2">Sahip</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                                </small>
                                            </div>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                            
                                            <?php if ($isLoggedIn && ($comment['user_id'] == $_SESSION['user_id'] || $isOwnPortfolio)): ?>
                                                <form action="" method="post" class="text-end">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" name="delete_comment" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu yorumu silmek istediğinize emin misiniz?')">
                                                        <i class="fas fa-trash-alt"></i> Sil
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-light text-center py-4" role="alert">
                            <i class="far fa-comments fa-3x mb-3 text-muted"></i>
                            <h5>Henüz yorum yapılmamış</h5>
                            <p class="text-muted mb-0">Bu bağlantı hakkında ilk yorumu siz yapın!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Yan Panel -->
        <div class="col-lg-4">
            <!-- Kullanıcının Diğer Bağlantıları -->
            <div class="card shadow-sm mb-4" data-aos="fade-up">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo htmlspecialchars($portfolio['username']); ?>'in Diğer Bağlantıları</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $otherPortfoliosSql = "SELECT * FROM portfolios WHERE user_id = ? AND id != ? ORDER BY platform ASC";
                    $stmt = mysqli_prepare($conn, $otherPortfoliosSql);
                    mysqli_stmt_bind_param($stmt, "ii", $portfolio['user_id'], $portfolio_id);
                    mysqli_stmt_execute($stmt);
                    $otherPortfoliosResult = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($otherPortfoliosResult) > 0):
                    ?>
                        <ul class="list-group list-group-flush">
                            <?php while ($otherPortfolio = mysqli_fetch_assoc($otherPortfoliosResult)): ?>
                                <?php
                                $otherPlatformClass = strtolower($otherPortfolio['platform']);
                                $otherIcon = "fa-link";
                                
                                switch ($otherPlatformClass) {
                                    case 'youtube': $otherIcon = "fa-youtube"; break;
                                    case 'instagram': $otherIcon = "fa-instagram"; break;
                                    case 'twitter': $otherIcon = "fa-twitter"; break;
                                    case 'twitch': $otherIcon = "fa-twitch"; break;
                                    case 'tiktok': $otherIcon = "fa-tiktok"; break;
                                    case 'facebook': $otherIcon = "fa-facebook-f"; break;
                                    case 'linkedin': $otherIcon = "fa-linkedin-in"; break;
                                    case 'website': $otherIcon = "fa-globe"; break;
                                }
                                ?>
                                <li class="list-group-item">
                                    <a href="portfolio_comments.php?id=<?php echo $otherPortfolio['id']; ?>" class="d-flex align-items-center text-dark text-decoration-none">
                                        <div class="platform-icon-sm <?php echo $otherPlatformClass; ?> me-2">
                                            <i class="fab <?php echo $otherIcon; ?>"></i>
                                        </div>
                                        <div class="portfolio-link-text overflow-hidden">
                                            <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($otherPortfolio['title'] ?: $otherPortfolio['platform']); ?></h6>
                                            <small class="text-muted d-block text-truncate"><?php echo htmlspecialchars($otherPortfolio['link']); ?></small>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-3 text-center">
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($portfolio['username']); ?>'in başka bağlantısı bulunmuyor.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="profile.php?id=<?php echo $portfolio['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-user"></i> Profili Görüntüle
                    </a>
                </div>
            </div>
            
            <!-- Benzer Bağlantılar -->
            <div class="card shadow-sm mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header">
                    <h5 class="mb-0">Benzer <?php echo $portfolio['platform']; ?> Bağlantıları</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (mysqli_num_rows($similarResult) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php while ($similar = mysqli_fetch_assoc($similarResult)): ?>
                                <li class="list-group-item">
                                    <a href="portfolio_comments.php?id=<?php echo $similar['id']; ?>" class="d-flex align-items-center text-dark text-decoration-none">
                                        <div class="me-2">
                                            <img src="uploads/avatars/<?php echo $similar['avatar']; ?>" alt="<?php echo htmlspecialchars($similar['username']); ?>" class="avatar-sm" onerror="this.src='uploads/avatars/default-avatar.png'">
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($similar['username']); ?></h6>
                                            <small class="text-muted d-block text-truncate"><?php echo htmlspecialchars($similar['title'] ?: $similar['platform']); ?></small>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-3 text-center">
                            <p class="text-muted mb-0">Benzer <?php echo $portfolio['platform']; ?> bağlantısı bulunmuyor.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="discover.php?filter=&search=<?php echo urlencode($portfolio['platform']); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-search"></i> Daha Fazla <?php echo $portfolio['platform']; ?> Bağlantısı
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Portfolyo detay sayfası için ekstra stiller */
.platform-icon-lg {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.platform-icon-sm {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
}

.stats-box {
    border-left: 4px solid #5e72e4;
}

.stat-label {
    font-weight: 600;
    color: #525f7f;
}

.stat-value {
    font-weight: 700;
    color: #2a2f4c;
}

.comment {
    transition: all 0.3s ease;
}

.comment:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08) !important;
}

/* Clipboard.js kütüphanesi için copy buton stilini ekleyin */
.copy-btn {
    position: relative;
}

.copy-btn.copied:after {
    content: 'Kopyalandı!';
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    opacity: 0;
    animation: fadeInOut 1.5s;
}

@keyframes fadeInOut {
    0% { opacity: 0; }
    20% { opacity: 1; }
    80% { opacity: 1; }
    100% { opacity: 0; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bağlantı kopyalama işlevi
    var clipboard = new ClipboardJS('.copy-btn');
    
    clipboard.on('success', function(e) {
        e.trigger.classList.add('copied');
        setTimeout(function() {
            e.trigger.classList.remove('copied');
        }, 2000);
        e.clearSelection();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
