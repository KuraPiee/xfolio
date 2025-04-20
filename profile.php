<?php
// Profil sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    // Kullanıcı giriş yapmamış, önce giriş yap sayfasına yönlendir
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// URL'den profil ID'si kontrolü
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];

// Profil bilgilerini veritabanından getir
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $profile_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Kullanıcı bulunamadıysa ana sayfaya yönlendir
if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Kendi profili mi kontrol et
$is_own_profile = ($profile_id == $_SESSION['user_id']);

// Takip durumu kontrolü (kendi profili değilse)
$is_following = false;
if (!$is_own_profile) {
    $followCheck = "SELECT id FROM followers WHERE follower_id = ? AND followed_id = ?";
    $stmt = mysqli_prepare($conn, $followCheck);
    mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $profile_id);
    mysqli_stmt_execute($stmt);
    $followResult = mysqli_stmt_get_result($stmt);
    $is_following = (mysqli_num_rows($followResult) > 0);
}

// Takipçi ve takip edilen sayısını getir
// Takipçi sayısı
$followerCountSql = "SELECT COUNT(*) as count FROM followers WHERE followed_id = ?";
$stmt = mysqli_prepare($conn, $followerCountSql);
mysqli_stmt_bind_param($stmt, "i", $profile_id);
mysqli_stmt_execute($stmt);
$followerResult = mysqli_stmt_get_result($stmt);
$followerCount = mysqli_fetch_assoc($followerResult)['count'];

// Takip edilen sayısı
$followingCountSql = "SELECT COUNT(*) as count FROM followers WHERE follower_id = ?";
$stmt = mysqli_prepare($conn, $followingCountSql);
mysqli_stmt_bind_param($stmt, "i", $profile_id);
mysqli_stmt_execute($stmt);
$followingResult = mysqli_stmt_get_result($stmt);
$followingCount = mysqli_fetch_assoc($followingResult)['count'];

// Portfolyo linklerini getir
$portfolioSql = "SELECT * FROM portfolios WHERE user_id = ? ORDER BY platform ASC";
$stmt = mysqli_prepare($conn, $portfolioSql);
mysqli_stmt_bind_param($stmt, "i", $profile_id);
mysqli_stmt_execute($stmt);
$portfolioResult = mysqli_stmt_get_result($stmt);

// Takip et/takibi bırak işlemi
$message = '';
$messageType = '';

if (isset($_POST['follow_action']) && !$is_own_profile) {
    if ($_POST['follow_action'] == 'follow') {
        // Takip et
        $followSql = "INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $followSql);
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $is_following = true;
            $followerCount++;
            $message = "{$user['username']} adlı kullanıcıyı takip etmeye başladınız.";
            $messageType = 'success';
            
            // Bildirim ekle
            $notifType = 'follow';
            $notifContent = "{$_SESSION['username']} sizi takip etmeye başladı.";
            $notifSql = "INSERT INTO notifications (user_id, sender_id, type, content) VALUES (?, ?, ?, ?)";
            $notifStmt = mysqli_prepare($conn, $notifSql);
            mysqli_stmt_bind_param($notifStmt, "iiss", $profile_id, $_SESSION['user_id'], $notifType, $notifContent);
            mysqli_stmt_execute($notifStmt);
        } else {
            $message = "Takip işlemi sırasında bir hata oluştu.";
            $messageType = 'danger';
        }
    } elseif ($_POST['follow_action'] == 'unfollow') {
        // Takibi bırak
        $unfollowSql = "DELETE FROM followers WHERE follower_id = ? AND followed_id = ?";
        $stmt = mysqli_prepare($conn, $unfollowSql);
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $is_following = false;
            $followerCount--;
            $message = "{$user['username']} adlı kullanıcıyı takip etmeyi bıraktınız.";
            $messageType = 'info';
        } else {
            $message = "Takibi bırakma işlemi sırasında bir hata oluştu.";
            $messageType = 'danger';
        }
    }
}

// Sayfa başlığı
$pageTitle = "Profil: " . $user['username'];
include 'includes/header.php';
?>

<div class="container profile-page">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mt-3" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Profil Üst Kısmı -->
    <div class="profile-header card shadow-sm mb-4" data-aos="fade-up">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="uploads/avatars/<?php echo $user['avatar']; ?>" alt="<?php echo $user['username']; ?>" class="avatar-xl rounded-circle" onerror="this.src='uploads/avatars/default-avatar.png'">
                </div>
                <div class="col-md-6">
                    <h2 class="mb-1"><?php echo $user['username']; ?></h2>
                    <p class="text-muted mb-2"><?php echo (!empty($user['bio'])) ? $user['bio'] : 'Bio bilgisi eklenmemiş'; ?></p>
                    
                    <div class="profile-stats mb-3">
                        <div class="profile-stat-item">
                            <span class="count"><?php echo $followerCount; ?></span>
                            <span class="label">Takipçi</span>
                        </div>
                        <div class="profile-stat-item">
                            <span class="count"><?php echo $followingCount; ?></span>
                            <span class="label">Takip Edilen</span>
                        </div>
                        <div class="profile-stat-item">
                            <span class="count"><?php echo mysqli_num_rows($portfolioResult); ?></span>
                            <span class="label">Link</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <?php if ($is_own_profile): ?>
                        <a href="edit_profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-edit"></i> Profili Düzenle
                        </a>
                    <?php else: ?>
                        <form method="post" action="">
                            <?php if ($is_following): ?>
                                <input type="hidden" name="follow_action" value="unfollow">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-user-minus"></i> Takibi Bırak
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="follow_action" value="follow">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Takip Et
                                </button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- İçerik Sekmeler -->
    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="portfolios-tab" data-bs-toggle="tab" data-bs-target="#portfolios" type="button" role="tab" aria-controls="portfolios" aria-selected="true">Portfolyo</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="followers-tab" data-bs-toggle="tab" data-bs-target="#followers" type="button" role="tab" aria-controls="followers" aria-selected="false">Takipçiler</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="following-tab" data-bs-toggle="tab" data-bs-target="#following" type="button" role="tab" aria-controls="following" aria-selected="false">Takip Edilenler</button>
        </li>
    </ul>
    
    <!-- Sekme İçerikleri -->
    <div class="tab-content" id="profileTabContent">
        <!-- Portfolyo Sekmesi -->
        <div class="tab-pane fade show active" id="portfolios" role="tabpanel" aria-labelledby="portfolios-tab">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h3>Sosyal Medya Bağlantıları</h3>
                    <?php if ($is_own_profile): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPortfolioModal">
                            <i class="fas fa-plus"></i> Yeni Link Ekle
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($portfolioResult) > 0): ?>
                <div class="row" data-aos="fade-up">
                    <?php
                    mysqli_data_seek($portfolioResult, 0); // Result set'i başa sar
                    while ($portfolio = mysqli_fetch_assoc($portfolioResult)):
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
                    ?>
                        <div class="col-lg-6 mb-3">
                            <div class="portfolio-card d-flex align-items-center">
                                <div class="platform-icon <?php echo $platformClass; ?>">
                                    <i class="fab <?php echo $icon; ?>"></i>
                                </div>
                                <div class="portfolio-details flex-grow-1">
                                    <h5><?php echo htmlspecialchars($portfolio['title'] ?: $portfolio['platform']); ?></h5>
                                    <a href="<?php echo htmlspecialchars($portfolio['link']); ?>" target="_blank" class="text-truncate d-block"><?php echo htmlspecialchars($portfolio['link']); ?></a>
                                    <?php if ($portfolio['followers'] > 0): ?>
                                        <small class="text-muted"><?php echo number_format($portfolio['followers']); ?> takipçi</small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($is_own_profile): ?>
                                    <div class="portfolio-actions">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1 edit-portfolio" 
                                                data-id="<?php echo $portfolio['id']; ?>"
                                                data-platform="<?php echo $portfolio['platform']; ?>"
                                                data-title="<?php echo htmlspecialchars($portfolio['title']); ?>"
                                                data-link="<?php echo htmlspecialchars($portfolio['link']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-portfolio" 
                                                data-id="<?php echo $portfolio['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($portfolio['title'] ?: $portfolio['platform']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <?php if ($is_own_profile): ?>
                        Henüz bir portfolyo linkiniz bulunmuyor. "Yeni Link Ekle" düğmesini kullanarak sosyal medya hesaplarınızı ekleyebilirsiniz.
                    <?php else: ?>
                        Bu kullanıcının henüz bir portfolyo linki bulunmuyor.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Takipçiler Sekmesi -->
        <div class="tab-pane fade" id="followers" role="tabpanel" aria-labelledby="followers-tab">
            <h3 class="mb-3">Takipçiler</h3>
            <div class="row followers-container" data-aos="fade-up">
                <?php
                // Takipçileri getir
                $followersSql = "SELECT u.* FROM followers f 
                                JOIN users u ON f.follower_id = u.id 
                                WHERE f.followed_id = ?
                                ORDER BY f.created_at DESC";
                $stmt = mysqli_prepare($conn, $followersSql);
                mysqli_stmt_bind_param($stmt, "i", $profile_id);
                mysqli_stmt_execute($stmt);
                $followersResult = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($followersResult) > 0):
                    while ($follower = mysqli_fetch_assoc($followersResult)):
                ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card user-card">
                            <div class="card-body d-flex align-items-center">
                                <img src="uploads/avatars/<?php echo $follower['avatar']; ?>" alt="<?php echo $follower['username']; ?>" class="avatar me-3" onerror="this.src='uploads/avatars/default-avatar.png'">
                                <div class="user-info">
                                    <h5 class="mb-0"><a href="profile.php?id=<?php echo $follower['id']; ?>" class="text-decoration-none"><?php echo $follower['username']; ?></a></h5>
                                    <p class="text-muted mb-0 small"><?php echo substr($follower['bio'], 0, 60) . (strlen($follower['bio']) > 60 ? '...' : ''); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                ?>
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            Henüz takipçi bulunmuyor.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Takip Edilenler Sekmesi -->
        <div class="tab-pane fade" id="following" role="tabpanel" aria-labelledby="following-tab">
            <h3 class="mb-3">Takip Edilenler</h3>
            <div class="row following-container" data-aos="fade-up">
                <?php
                // Takip edilen kullanıcıları getir
                $followingSql = "SELECT u.* FROM followers f 
                                JOIN users u ON f.followed_id = u.id 
                                WHERE f.follower_id = ?
                                ORDER BY f.created_at DESC";
                $stmt = mysqli_prepare($conn, $followingSql);
                mysqli_stmt_bind_param($stmt, "i", $profile_id);
                mysqli_stmt_execute($stmt);
                $followingResult = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($followingResult) > 0):
                    while ($following = mysqli_fetch_assoc($followingResult)):
                ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card user-card">
                            <div class="card-body d-flex align-items-center">
                                <img src="uploads/avatars/<?php echo $following['avatar']; ?>" alt="<?php echo $following['username']; ?>" class="avatar me-3" onerror="this.src='uploads/avatars/default-avatar.png'">
                                <div class="user-info">
                                    <h5 class="mb-0"><a href="profile.php?id=<?php echo $following['id']; ?>" class="text-decoration-none"><?php echo $following['username']; ?></a></h5>
                                    <p class="text-muted mb-0 small"><?php echo substr($following['bio'], 0, 60) . (strlen($following['bio']) > 60 ? '...' : ''); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                ?>
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            Henüz takip edilen kullanıcı bulunmuyor.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($is_own_profile): ?>
<!-- Portfolyo Ekle Modal -->
<div class="modal fade" id="addPortfolioModal" tabindex="-1" aria-labelledby="addPortfolioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPortfolioModalLabel">Yeni Sosyal Medya Linki Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_portfolio.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="platform" class="form-label">Platform</label>
                        <select class="form-select" id="platform" name="platform" required>
                            <option value="">Platform Seçin</option>
                            <option value="YouTube">YouTube</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Twitter">Twitter</option>
                            <option value="TikTok">TikTok</option>
                            <option value="Twitch">Twitch</option>
                            <option value="Facebook">Facebook</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="Website">Web Sitesi</option>
                            <option value="Other">Diğer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık (Opsiyonel)</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Örn: Kişisel YouTube Kanalım">
                    </div>
                    <div class="mb-3">
                        <label for="link" class="form-label">Link</label>
                        <input type="url" class="form-control" id="link" name="link" placeholder="https://..." required>
                        <small class="form-text text-muted">Tam URL adresini ekleyin (https:// ile başlayan)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Portfolyo Düzenle Modal -->
<div class="modal fade" id="editPortfolioModal" tabindex="-1" aria-labelledby="editPortfolioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPortfolioModalLabel">Sosyal Medya Linki Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="edit_portfolio.php" method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_portfolio_id" name="portfolio_id">
                    <div class="mb-3">
                        <label for="edit_platform" class="form-label">Platform</label>
                        <select class="form-select" id="edit_platform" name="platform" required>
                            <option value="YouTube">YouTube</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Twitter">Twitter</option>
                            <option value="TikTok">TikTok</option>
                            <option value="Twitch">Twitch</option>
                            <option value="Facebook">Facebook</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="Website">Web Sitesi</option>
                            <option value="Other">Diğer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Başlık (Opsiyonel)</label>
                        <input type="text" class="form-control" id="edit_title" name="title" placeholder="Örn: Kişisel YouTube Kanalım">
                    </div>
                    <div class="mb-3">
                        <label for="edit_link" class="form-label">Link</label>
                        <input type="url" class="form-control" id="edit_link" name="link" placeholder="https://..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Portfolyo Sil Modal -->
<div class="modal fade" id="deletePortfolioModal" tabindex="-1" aria-labelledby="deletePortfolioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePortfolioModalLabel">Link Silme Onayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bu linki silmek istediğinize emin misiniz? <strong id="delete_portfolio_title"></strong></p>
                <p class="text-danger">Bu işlem geri alınamaz!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form action="delete_portfolio.php" method="post">
                    <input type="hidden" id="delete_portfolio_id" name="portfolio_id">
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Portfolyo düzenleme modalı
    var editButtons = document.querySelectorAll('.edit-portfolio');
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var platform = this.getAttribute('data-platform');
            var title = this.getAttribute('data-title');
            var link = this.getAttribute('data-link');
            
            document.getElementById('edit_portfolio_id').value = id;
            document.getElementById('edit_platform').value = platform;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_link').value = link;
            
            var editModal = new bootstrap.Modal(document.getElementById('editPortfolioModal'));
            editModal.show();
        });
    });
    
    // Portfolyo silme modalı
    var deleteButtons = document.querySelectorAll('.delete-portfolio');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var title = this.getAttribute('data-title');
            
            document.getElementById('delete_portfolio_id').value = id;
            document.getElementById('delete_portfolio_title').textContent = title;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deletePortfolioModal'));
            deleteModal.show();
        });
    });
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
