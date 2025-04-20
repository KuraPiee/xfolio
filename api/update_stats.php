<?php
/**
 * Xfolio API - Sosyal Medya İstatistiklerini Güncelleme
 */

// Oturum başlat ve yapılandırma dosyalarını dahil et
session_start();
require_once '../includes/config.php';
require_once 'social_stats.php';

// JSON yanıt için header ayarları
header('Content-Type: application/json');

// Kullanıcının oturum açmış olup olmadığını kontrol et
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Oturum açmanız gerekiyor.'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];

// İşlem türünü belirle
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'update_stats':
        // Portfolyo ID'sini kontrol et
        if (!isset($_POST['portfolio_id']) || !is_numeric($_POST['portfolio_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz portfolyo ID.'
            ]);
            exit();
        }
        
        $portfolioId = (int)$_POST['portfolio_id'];
        
        // Portfolyo bilgilerini getir
        $sql = "SELECT platform, link, followers FROM portfolios WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $portfolioId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Portfolyo bulunamadı veya bu portfolyo size ait değil.'
            ]);
            exit();
        }
        
        $portfolio = mysqli_fetch_assoc($result);
        $platform = $portfolio['platform'];
        $link = $portfolio['link'];
        $oldFollowers = $portfolio['followers'];
        
        // Platform istatistiklerini getir
        $stats = getPlatformStats($platform, $link);
        
        // İstatistik başarılı bir şekilde alındıysa güncelle
        if ($stats['success'] && isset($stats['followers'])) {
            $followers = $stats['followers'];
            
            $updateSql = "UPDATE portfolios SET followers = ?, last_updated = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($stmt, "ii", $followers, $portfolioId);
            
            if (mysqli_stmt_execute($stmt)) {
                $followerChange = $followers - $oldFollowers;
                $changeText = ($followerChange > 0) ? '+' . number_format($followerChange) : number_format($followerChange);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'İstatistikler başarıyla güncellendi.',
                    'data' => [
                        'followers' => number_format($followers),
                        'change' => $changeText,
                        'last_updated' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'İstatistikler güncellenirken bir hata oluştu: ' . mysqli_error($conn)
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'İstatistik alınamadı: ' . $stats['message']
            ]);
        }
        break;
        
    case 'update_all':
        // Kullanıcının tüm portfolyo linklerini getir
        $sql = "SELECT id, platform, link, followers FROM portfolios WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $updated = 0;
        $failed = 0;
        $totalChange = 0;
        
        while ($portfolio = mysqli_fetch_assoc($result)) {
            $portfolioId = $portfolio['id'];
            $platform = $portfolio['platform'];
            $link = $portfolio['link'];
            $oldFollowers = $portfolio['followers'];
            
            // Platform istatistiklerini getir
            $stats = getPlatformStats($platform, $link);
            
            // İstatistik başarılı bir şekilde alındıysa güncelle
            if ($stats['success'] && isset($stats['followers'])) {
                $followers = $stats['followers'];
                
                $updateSql = "UPDATE portfolios SET followers = ?, last_updated = NOW() WHERE id = ?";
                $stmt2 = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($stmt2, "ii", $followers, $portfolioId);
                
                if (mysqli_stmt_execute($stmt2)) {
                    $updated++;
                    $totalChange += ($followers - $oldFollowers);
                } else {
                    $failed++;
                }
                
                // İşlem arasında kısa bir bekleme süresi ekleyelim
                usleep(500000); // 0.5 saniye
            } else {
                $failed++;
            }
        }
        
        echo json_encode([
            'success' => $updated > 0,
            'message' => "$updated portfolyo linki güncellendi, $failed portfolyo linki güncellenemedi.",
            'data' => [
                'updated' => $updated,
                'failed' => $failed,
                'total_change' => $totalChange > 0 ? '+' . number_format($totalChange) : number_format($totalChange),
                'last_updated' => date('Y-m-d H:i:s')
            ]
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz işlem.'
        ]);
        break;
}
?>
