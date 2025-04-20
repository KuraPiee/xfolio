<?php
// Kullanıcı yönetim sayfası - Admin paneli
session_start();
require_once '../includes/config.php';

// Admin yetkisi kontrolü
function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $conn;
    $userId = $_SESSION['user_id'];
    $sql = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return (bool)$row['is_admin'];
    }
    
    return false;
}

// Admin değilse ana sayfaya yönlendir
if (!isAdmin()) {
    header("Location: ../index.php");
    exit();
}

// Sayfalama için parametreler
$limit = 15; // Sayfa başına gösterilecek kullanıcı sayısı
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Arama sorgusu
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $searchCondition = " WHERE username LIKE ? OR email LIKE ? ";
    $searchParam = "%{$search}%";
    $params = [$searchParam, $searchParam];
    $types = "ss";
}

// Toplam kullanıcı sayısını getir
$countSql = "SELECT COUNT(*) as total FROM users" . $searchCondition;
$countStmt = mysqli_prepare($conn, $countSql);

if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}

mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalUsers = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalUsers / $limit);

// Kullanıcıları getir
$sql = "SELECT id, username, email, phone, avatar, created_at, is_verified, is_admin 
        FROM users" . $searchCondition . "
        ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    mysqli_stmt_bind_param($stmt, $types, ...$params);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Sayfada mesaj gösterimi
function displayMessage() {
    if (isset($_SESSION['admin_message'])) {
        $message = $_SESSION['admin_message'];
        $type = $_SESSION['admin_message_type'];
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        
        // Mesajı gösterdikten sonra temizle
        unset($_SESSION['admin_message']);
        unset($_SESSION['admin_message_type']);
    }
}

// Sayfa başlığı
$pageTitle = 'Kullanıcı Yönetimi';
include('includes/admin_header.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('includes/admin_sidebar.php'); ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Kullanıcı Yönetimi</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="user_add.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-user-plus"></i> Yeni Kullanıcı
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Yazdır
                        </button>
                    </div>
                </div>
            </div>
            
            <?php displayMessage(); ?>
            
            <!-- Arama ve Filtreleme -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="" method="GET" class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" class="form-control" placeholder="Kullanıcı adı veya email ile ara..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group">
                        <a href="?filter=all" class="btn btn-outline-secondary btn-sm <?php echo !isset($_GET['filter']) || $_GET['filter'] == 'all' ? 'active' : ''; ?>">Tümü</a>
                        <a href="?filter=verified" class="btn btn-outline-secondary btn-sm <?php echo isset($_GET['filter']) && $_GET['filter'] == 'verified' ? 'active' : ''; ?>">Doğrulanmış</a>
                        <a href="?filter=unverified" class="btn btn-outline-secondary btn-sm <?php echo isset($_GET['filter']) && $_GET['filter'] == 'unverified' ? 'active' : ''; ?>">Doğrulanmamış</a>
                        <a href="?filter=admin" class="btn btn-outline-secondary btn-sm <?php echo isset($_GET['filter']) && $_GET['filter'] == 'admin' ? 'active' : ''; ?>">Adminler</a>
                    </div>
                </div>
            </div>
            
            <!-- Kullanıcı Tablosu -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Kullanıcı Adı</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Kayıt Tarihi</th>
                            <th>Durumu</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <img src="../uploads/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" class="user-avatar-sm" onerror="this.src='../uploads/avatars/default-avatar.png'">
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="badge bg-dark ms-1">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['is_verified']): ?>
                                            <span class="badge bg-success">Doğrulanmış</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Doğrulanmamış</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="user_detail.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger confirm-action" data-confirm-message="Bu kullanıcıyı silmek istediğinize emin misiniz?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if (!$user['is_admin']): ?>
                                                <a href="user_make_admin.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-dark confirm-action" data-confirm-message="Bu kullanıcıyı admin yapmak istediğinize emin misiniz?">
                                                    <i class="fas fa-user-shield"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="user_remove_admin.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary confirm-action" data-confirm-message="Bu kullanıcının admin yetkisini kaldırmak istediğinize emin misiniz?">
                                                    <i class="fas fa-user-minus"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Kullanıcı bulunamadı.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Sayfalama -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Sayfalama">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Önceki">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Sonraki">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <!-- Kullanıcı Detay Modalı -->
            <div class="modal fade" id="userDetailModal" tabindex="-1" aria-labelledby="userDetailModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userDetailModalLabel">Kullanıcı Detayları</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <img id="modalUserAvatar" src="../uploads/avatars/default-avatar.png" alt="User Avatar" class="user-avatar mb-2">
                                <h4 id="modalUserName">Kullanıcı Adı</h4>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 detail-label">Email:</div>
                                <div class="col-8" id="modalUserEmail">email@example.com</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 detail-label">Telefon:</div>
                                <div class="col-8" id="modalUserPhone">+90 555 123 4567</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 detail-label">Kayıt Tarihi:</div>
                                <div class="col-8" id="modalUserRegistered">01.01.2023 12:00</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                            <a href="#" id="modalEditButton" class="btn btn-primary">Düzenle</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include('includes/admin_footer.php'); ?>
