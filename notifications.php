<?php
// Bildirimler sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Bildirimleri okundu olarak işaretleme işlemi
if (isset($_POST['mark_all_read'])) {
    $markAllSql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $markAllSql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Yönlendirme
    header("Location: notifications.php");
    exit();
}

// Tek bir bildirimi okundu olarak işaretleme işlemi
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notification_id = (int)$_GET['mark_read'];
    
    $markReadSql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $markReadSql);
    mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
    mysqli_stmt_execute($stmt);
    
    // Yönlendirme
    header("Location: notifications.php");
    exit();
}

// Tek bir bildirimi silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notification_id = (int)$_GET['delete'];
    
    $deleteSql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
    mysqli_stmt_execute($stmt);
    
    // Yönlendirme
    header("Location: notifications.php");
    exit();
}

// Tüm bildirimleri silme işlemi
if (isset($_POST['delete_all'])) {
    $deleteAllSql = "DELETE FROM notifications WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $deleteAllSql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Yönlendirme
    header("Location: notifications.php");
    exit();
}

// Bildirimleri getir (en yeniden en eskiye)
$sql = "SELECT n.*, u.username, u.avatar 
        FROM notifications n
        JOIN users u ON n.sender_id = u.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Okunmamış bildirim sayısı
$unreadCountSql = "SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $unreadCountSql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$unreadResult = mysqli_stmt_get_result($stmt);
$unreadCount = mysqli_fetch_assoc($unreadResult)['count'];

// Sayfa başlığı
$pageTitle = "Bildirimler";
include 'includes/header.php';
?>

<div class="container notifications-page">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="page-header d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
                <div>
                    <h1>Bildirimler</h1>
                    <?php if ($unreadCount > 0): ?>
                        <p class="text-muted"><?php echo $unreadCount; ?> okunmamış bildiriminiz var</p>
                    <?php else: ?>
                        <p class="text-muted">Tüm bildirimleriniz okundu</p>
                    <?php endif; ?>
                </div>
                <div class="notification-actions d-flex">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <form action="" method="post" class="me-2">
                            <button type="submit" name="mark_all_read" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-check-double me-1"></i> Tümünü Okundu İşaretle
                            </button>
                        </form>
                        <form action="" method="post">
                            <button type="submit" name="delete_all" class="btn btn-outline-danger btn-sm" onclick="return confirm('Tüm bildirimleri silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash me-1"></i> Tümünü Sil
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="card shadow-sm" data-aos="fade-up">
                    <ul class="list-group list-group-flush notification-list">
                        <?php while ($notification = mysqli_fetch_assoc($result)): ?>
                            <?php
                                // Bildirim türüne göre ikon ve renk belirle
                                switch ($notification['type']) {
                                    case 'follow':
                                        $icon = 'fa-user-plus';
                                        $iconClass = 'bg-primary';
                                        $link = "profile.php?id={$notification['sender_id']}";
                                        break;
                                    case 'comment':
                                        $icon = 'fa-comment';
                                        $iconClass = 'bg-info';
                                        // Yorumun yapıldığı portfolyo linkini oluştur
                                        $link = "portfolio_comments.php?id=" . (isset($notification['portfolio_id']) ? $notification['portfolio_id'] : '');
                                        break;
                                    case 'like':
                                        $icon = 'fa-heart';
                                        $iconClass = 'bg-danger';
                                        $link = "profile.php?id={$notification['sender_id']}";
                                        break;
                                    case 'mention':
                                        $icon = 'fa-at';
                                        $iconClass = 'bg-warning';
                                        $link = "profile.php?id={$notification['sender_id']}";
                                        break;
                                    case 'welcome':
                                        $icon = 'fa-hand-wave';
                                        $iconClass = 'bg-success';
                                        $link = "index.php";
                                        break;
                                    default:
                                        $icon = 'fa-bell';
                                        $iconClass = 'bg-secondary';
                                        $link = "#";
                                        break;
                                }
                            ?>
                            <li class="list-group-item notification-item <?php echo $notification['is_read'] ? '' : 'notification-unread'; ?>">
                                <div class="d-flex align-items-center">
                                    <div class="notification-icon <?php echo $iconClass; ?>">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="notification-avatar">
                                        <a href="profile.php?id=<?php echo $notification['sender_id']; ?>">
                                            <img src="uploads/avatars/<?php echo $notification['avatar']; ?>" alt="<?php echo htmlspecialchars($notification['username']); ?>" class="avatar-sm" onerror="this.src='uploads/avatars/default-avatar.png'">
                                        </a>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-text">
                                            <a href="<?php echo $link; ?>" class="text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($notification['content']); ?>
                                            </a>
                                        </div>
                                        <div class="notification-time small text-muted">
                                            <?php 
                                                $timestamp = strtotime($notification['created_at']);
                                                $now = time();
                                                $diff = $now - $timestamp;
                                                
                                                if ($diff < 60) {
                                                    echo "Az önce";
                                                } elseif ($diff < 3600) {
                                                    echo floor($diff / 60) . " dakika önce";
                                                } elseif ($diff < 86400) {
                                                    echo floor($diff / 3600) . " saat önce";
                                                } elseif ($diff < 604800) {
                                                    echo floor($diff / 86400) . " gün önce";
                                                } else {
                                                    echo date("d.m.Y", $timestamp);
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="notification-actions ms-auto">
                                        <?php if (!$notification['is_read']): ?>
                                            <a href="?mark_read=<?php echo $notification['id']; ?>" class="btn btn-sm btn-light me-1" title="Okundu İşaretle">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $notification['id']; ?>" class="btn btn-sm btn-light" title="Sil" onclick="return confirm('Bu bildirimi silmek istediğinize emin misiniz?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="card shadow-sm" data-aos="fade-up">
                    <div class="card-body text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                            <h4>Bildiriminiz Bulunmuyor</h4>
                            <p class="text-muted">Henüz hiç bildiriminiz yok. Diğer kullanıcılar sizi takip etmeye başladığında veya içeriklerinizle etkileşimde bulunduğunda bildirimler burada görünecek.</p>
                            <a href="discover.php" class="btn btn-primary mt-3">Keşfet Sayfasına Git</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Bildirim stillerini sayfaya ekle */
.notification-list {
    max-height: 600px;
    overflow-y: auto;
}

.notification-item {
    padding: 15px;
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-unread {
    border-left-color: #5e72e4;
    background-color: rgba(94, 114, 228, 0.05);
}

.notification-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    color: white;
}

.notification-avatar {
    margin-right: 12px;
}

.notification-content {
    flex-grow: 1;
}

.empty-state {
    color: #8898aa;
}
</style>

<?php include 'includes/footer.php'; ?>
