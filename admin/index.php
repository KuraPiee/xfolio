<?php
// Admin panel ana sayfası - Sadece admin kullanıcıları erişebilir
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

// İstatistikleri getir
$totalUsers = 0;
$totalPortfolios = 0;
$newUsersToday = 0;
$activeUsers = 0;

// Toplam kullanıcı sayısı
$sql = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $totalUsers = $row['total'];
}

// Toplam portfolyo sayısı
$sql = "SELECT COUNT(*) as total FROM portfolios";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $totalPortfolios = $row['total'];
}

// Bugün kaydolan kullanıcı sayısı
$sql = "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $newUsersToday = $row['total'];
}

// Son 30 günde aktif kullanıcılar (en az bir portfolyo ekleyenler)
$sql = "SELECT COUNT(DISTINCT user_id) as total FROM portfolios WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $activeUsers = $row['total'];
}

// Sayfa başlığı
$pageTitle = 'Admin Panel - Dashboard';
include('includes/admin_header.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('includes/admin_sidebar.php'); ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download"></i> Rapor İndir
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <i class="fas fa-calendar"></i> Bu Hafta
                    </button>
                </div>
            </div>

            <!-- İstatistik Kartları -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Toplam Kullanıcılar</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalUsers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Toplam Portfolyo Linkleri</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalPortfolios; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-link fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Bugün Kaydolan Kullanıcılar</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $newUsersToday; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Aktif Kullanıcılar (Son 30 gün)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeUsers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Son Kaydolan Kullanıcılar -->
            <h2>Son Kaydolan Kullanıcılar</h2>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>Email</th>
                            <th>Kayıt Tarihi</th>
                            <th>Doğrulandı</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Son 10 kullanıcıyı getir
                        $sql = "SELECT id, username, email, created_at, is_verified FROM users ORDER BY created_at DESC LIMIT 10";
                        $result = mysqli_query($conn, $sql);
                        
                        while ($user = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>{$user['id']}</td>
                                <td>{$user['username']}</td>
                                <td>{$user['email']}</td>
                                <td>" . date('d.m.Y H:i', strtotime($user['created_at'])) . "</td>
                                <td>" . ($user['is_verified'] ? '<span class="badge bg-success">Evet</span>' : '<span class="badge bg-danger">Hayır</span>') . "</td>
                                <td>
                                    <a href='user_detail.php?id={$user['id']}' class='btn btn-sm btn-primary'><i class='fas fa-eye'></i></a>
                                    <a href='user_edit.php?id={$user['id']}' class='btn btn-sm btn-warning'><i class='fas fa-edit'></i></a>
                                    <a href='user_delete.php?id={$user['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Bu kullanıcıyı silmek istediğinize emin misiniz?\")'><i class='fas fa-trash'></i></a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Son Eklenen Portfolyo Linkleri -->
            <h2 class="mt-5">Son Eklenen Portfolyo Linkleri</h2>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı</th>
                            <th>Platform</th>
                            <th>Link</th>
                            <th>Eklenme Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Son 10 portfolyo linkini getir
                        $sql = "SELECT p.id, p.platform, p.link, p.created_at, u.username 
                                FROM portfolios p 
                                JOIN users u ON p.user_id = u.id 
                                ORDER BY p.created_at DESC LIMIT 10";
                        $result = mysqli_query($conn, $sql);
                        
                        while ($portfolio = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>{$portfolio['id']}</td>
                                <td>{$portfolio['username']}</td>
                                <td>{$portfolio['platform']}</td>
                                <td><a href='{$portfolio['link']}' target='_blank'>{$portfolio['link']}</a></td>
                                <td>" . date('d.m.Y H:i', strtotime($portfolio['created_at'])) . "</td>
                                <td>
                                    <a href='portfolio_edit.php?id={$portfolio['id']}' class='btn btn-sm btn-warning'><i class='fas fa-edit'></i></a>
                                    <a href='portfolio_delete.php?id={$portfolio['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Bu portfolyo linkini silmek istediğinize emin misiniz?\")'><i class='fas fa-trash'></i></a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php include('includes/admin_footer.php'); ?>
