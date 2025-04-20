<?php
// Kullanıcıyı admin yapma işlemi
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

// ID parametresi kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['admin_message'] = "Geçersiz kullanıcı ID.";
    $_SESSION['admin_message_type'] = "danger";
    header("Location: users.php");
    exit();
}

$userId = (int)$_GET['id'];

// Kullanıcının kendisini admin yapmasını engelle
if ($userId === $_SESSION['user_id']) {
    $_SESSION['admin_message'] = "Kendinizi admin yapamazsınız.";
    $_SESSION['admin_message_type'] = "danger";
    header("Location: users.php");
    exit();
}

// Kullanıcının var olup olmadığını kontrol et
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    $_SESSION['admin_message'] = "Kullanıcı bulunamadı.";
    $_SESSION['admin_message_type'] = "danger";
    header("Location: users.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Admin yetkisi verme işlemi
$sql = "UPDATE users SET is_admin = 1 WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['admin_message'] = "{$user['username']} adlı kullanıcıya başarıyla admin yetkisi verildi.";
    $_SESSION['admin_message_type'] = "success";
} else {
    $_SESSION['admin_message'] = "Admin yetkisi verilirken bir hata oluştu: " . mysqli_error($conn);
    $_SESSION['admin_message_type'] = "danger";
}

header("Location: users.php");
exit();
?>
