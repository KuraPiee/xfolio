<?php
// Marketplace - Yeni teklif oluşturma sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Düzenleme modu kontrolü
$isEditMode = isset($_GET['id']) && is_numeric($_GET['id']);
$offerId = $isEditMode ? (int)$_GET['id'] : 0;

// Düzenleme modunda teklif bilgilerini getir
if ($isEditMode) {
    $sql = "SELECT * FROM sponsorship_offers WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $offerId, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: marketplace.php");
        exit();
    }
    
    $offer = mysqli_fetch_assoc($result);
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verilerini al
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $collaborationType = sanitize($_POST['collaboration_type']);
    $minBudget = isset($_POST['min_budget']) ? (float)$_POST['min_budget'] : 0;
    $maxBudget = isset($_POST['max_budget']) ? (float)$_POST['max_budget'] : 0;
    $currency = sanitize($_POST['currency']);
    $requirements = sanitize($_POST['requirements']);
    $deliverables = sanitize($_POST['deliverables']);
    $deadline = sanitize($_POST['deadline']);
    $isBrand = isset($_POST['is_brand']) ? 1 : 0;
    
    // Form validasyonu
    if (empty($title)) {
        $error = "Lütfen bir başlık girin.";
    } else if (empty($description)) {
        $error = "Lütfen bir açıklama girin.";
    } else if (empty($category)) {
        $error = "Lütfen bir kategori seçin.";
    } else if (empty($collaborationType)) {
        $error = "Lütfen bir iş birliği türü seçin.";
    } else if ($minBudget < 0 || $maxBudget < 0) {
        $error = "Bütçe değerleri negatif olamaz.";
    } else if ($maxBudget > 0 && $minBudget > $maxBudget) {
        $error = "Minimum bütçe, maksimum bütçeden büyük olamaz.";
    } else if (empty($deadline)) {
        $error = "Lütfen son başvuru tarihi seçin.";
    } else {
        $deadlineDate = new DateTime($deadline);
        $now = new DateTime();
        
        if ($deadlineDate < $now) {
            $error = "Son başvuru tarihi geçmiş bir tarih olamaz.";
        } else {
            // Hata yoksa, teklifi veritabanına ekle veya güncelle
            if ($isEditMode) {
                $updateSql = "UPDATE sponsorship_offers SET 
                            title = ?, 
                            description = ?, 
                            category = ?, 
                            collaboration_type = ?, 
                            min_budget = ?, 
                            max_budget = ?, 
                            currency = ?, 
                            requirements = ?, 
                            deliverables = ?, 
                            deadline = ?, 
                            is_brand = ?, 
                            updated_at = NOW() 
                            WHERE id = ? AND user_id = ?";
                
                $stmt = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($stmt, "ssssddssssiiii", $title, $description, $category, $collaborationType, $minBudget, $maxBudget, $currency, $requirements, $deliverables, $deadline, $isBrand, $offerId, $userId);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Teklif başarıyla güncellendi.";
                } else {
                    $error = "Teklif güncellenirken bir hata oluştu: " . mysqli_error($conn);
                }
            } else {
                $insertSql = "INSERT INTO sponsorship_offers (user_id, title, description, category, collaboration_type, min_budget, max_budget, currency, requirements, deliverables, deadline, is_brand, created_at, updated_at, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)";
                
                $stmt = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($stmt, "issssddsssssi", $userId, $title, $description, $category, $collaborationType, $minBudget, $maxBudget, $currency, $requirements, $deliverables, $deadline, $isBrand);
                
                if (mysqli_stmt_execute($stmt)) {
                    $newOfferId = mysqli_insert_id($conn);
                    $success = "Teklif başarıyla oluşturuldu.";
                    
                    // Başarıyla oluşturuldu, teklifin detay sayfasına yönlendir
                    header("Location: marketplace_detail.php?id=" . $newOfferId . "&created=1");
                    exit();
                } else {
                    $error = "Teklif oluşturulurken bir hata oluştu: " . mysqli_error($conn);
                }
            }
        }
    }
}

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

// Para birimleri
$currencies = array(
    'TL' => 'Türk Lirası (TL)',
    'USD' => 'Amerikan Doları ($)',
    'EUR' => 'Euro (€)',
    'GBP' => 'İngiliz Sterlini (£)',
);

// Sayfa başlığı
$pageTitle = $isEditMode ? "Teklifi Düzenle" : "Yeni Teklif Oluştur";

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
            <h1 class="mb-2"><?php echo $pageTitle; ?></h1>
            <p class="text-muted">
                <?php echo $isEditMode ? "Teklifinizi güncelleyin ve daha fazla başvuru alın." : "Yeni bir sponsorluk teklifi oluşturun ve içerik üreticileriyle iş birliği yapın."; ?>
            </p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="" method="post" class="create-offer-form">
                <!-- Temel Bilgiler -->
                <div class="card shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">Temel Bilgiler</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Başlık</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                value="<?php echo isset($offer['title']) ? htmlspecialchars($offer['title']) : ''; ?>" 
                                placeholder="Örnek: Instagram içerik üreticileri arıyoruz" required>
                            <small class="text-muted">Teklifinizi en iyi şekilde tanımlayan kısa bir başlık yazın.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="5" 
                                placeholder="Teklifinizi detaylı olarak açıklayın..." required><?php echo isset($offer['description']) ? htmlspecialchars($offer['description']) : ''; ?></textarea>
                            <small class="text-muted">İş birliğinin amacını, hedef kitlesini ve genel kapsamını açıklayın.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Kategori</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Kategori seçin</option>
                                    <?php foreach ($categories as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($offer['category']) && $offer['category'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="collaboration_type" class="form-label">İş Birliği Türü</label>
                                <select class="form-select" id="collaboration_type" name="collaboration_type" required>
                                    <option value="">İş birliği türü seçin</option>
                                    <?php foreach ($collaborationTypes as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($offer['collaboration_type']) && $offer['collaboration_type'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bütçe Bilgileri -->
                <div class="card shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">Bütçe Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="min_budget" class="form-label">Minimum Bütçe</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="min_budget" name="min_budget" 
                                        value="<?php echo isset($offer['min_budget']) ? $offer['min_budget'] : ''; ?>" 
                                        placeholder="0" min="0" step="100">
                                    <span class="input-group-text">TL</span>
                                </div>
                                <small class="text-muted">0 bırakırsanız, "Maksimum kadar" olarak gösterilir.</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="max_budget" class="form-label">Maksimum Bütçe</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="max_budget" name="max_budget" 
                                        value="<?php echo isset($offer['max_budget']) ? $offer['max_budget'] : ''; ?>" 
                                        placeholder="0" min="0" step="100">
                                    <span class="input-group-text">TL</span>
                                </div>
                                <small class="text-muted">0 bırakırsanız, "Minimum ve üzeri" olarak gösterilir.</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="currency" class="form-label">Para Birimi</label>
                                <select class="form-select" id="currency" name="currency">
                                    <?php foreach ($currencies as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($offer['currency']) && $offer['currency'] == $key) ? 'selected' : ((!isset($offer['currency']) && $key == 'TL') ? 'selected' : ''); ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detaylı Bilgiler -->
                <div class="card shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0">Detaylı Bilgiler</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="requirements" class="form-label">Aranan Özellikler</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4" 
                                placeholder="Örnek:
- En az 10.000 takipçiye sahip olmak
- Teknoloji alanında içerik üretiyor olmak
- Son 3 ayda düzenli içerik paylaşmış olmak"><?php echo isset($offer['requirements']) ? htmlspecialchars($offer['requirements']) : ''; ?></textarea>
                            <small class="text-muted">İçerik üreticilerinde aradığınız özellikleri her satıra bir madde olacak şekilde yazın.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deliverables" class="form-label">Teslim Edilecekler</label>
                            <textarea class="form-control" id="deliverables" name="deliverables" rows="4" 
                                placeholder="Bu iş birliğinde içerik üreticilerinden beklediğiniz görevleri ve çıktıları açıklayın..."><?php echo isset($offer['deliverables']) ? htmlspecialchars($offer['deliverables']) : ''; ?></textarea>
                            <small class="text-muted">İş birliği kapsamında içerik üreticilerinden beklediğiniz çıktıları ve sorumlulukları belirtin.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Son Başvuru Tarihi</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" 
                                value="<?php echo isset($offer['deadline']) ? date('Y-m-d', strtotime($offer['deadline'])) : date('Y-m-d', strtotime('+7 days')); ?>" 
                                min="<?php echo date('Y-m-d'); ?>" required>
                            <small class="text-muted">Başvuruları ne zamana kadar kabul edeceğinizi belirtin.</small>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_brand" name="is_brand" value="1" 
                                <?php echo (isset($offer['is_brand']) && $offer['is_brand'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_brand">
                                Bu teklif bir marka adına oluşturulmuştur
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Kaydet -->
                <div class="d-flex justify-content-between">
                    <a href="marketplace.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i> İptal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-<?php echo $isEditMode ? 'save' : 'plus'; ?> me-2"></i>
                        <?php echo $isEditMode ? 'Teklifi Güncelle' : 'Teklifi Oluştur'; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Yan Panel -->
        <div class="col-lg-4">
            <!-- İpuçları -->
            <div class="card shadow-sm mb-4" data-aos="fade-left">
                <div class="card-header">
                    <h5 class="mb-0">Başarılı Tekliflerin İpuçları</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; font-size: 14px;">1</div>
                                </div>
                                <div>
                                    <strong>Net olun</strong>
                                    <p class="small text-muted mb-0">Beklentilerinizi ve ödeme koşullarınızı açıkça belirtin.</p>
                                </div>
                            </div>
                        </li>
                        <li class="mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; font-size: 14px;">2</div>
                                </div>
                                <div>
                                    <strong>Değer sunun</strong>
                                    <p class="small text-muted mb-0">İçerik üreticileri için bu iş birliğinin değerini vurgulayın.</p>
                                </div>
                            </div>
                        </li>
                        <li class="mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; font-size: 14px;">3</div>
                                </div>
                                <div>
                                    <strong>Hedeflerinizi paylaşın</strong>
                                    <p class="small text-muted mb-0">Kampanyanın amaçlarını ve hedef kitlesini belirtin.</p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; font-size: 14px;">4</div>
                                </div>
                                <div>
                                    <strong>Gerçekçi olun</strong>
                                    <p class="small text-muted mb-0">İş yükü ve bütçe arasında makul bir denge kurun.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Kurumsal İpuçları -->
            <div class="card shadow-sm mb-4" data-aos="fade-left" data-aos-delay="100">
                <div class="card-header">
                    <h5 class="mb-0">Doğru İçerik Üreticiyi Seçmek</h5>
                </div>
                <div class="card-body">
                    <p class="card-text small mb-4">İş birliği yapacağınız içerik üreticisini seçerken şu kriterlere dikkat edin:</p>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Takipçi sayısından çok etkileşim oranına bakın</li>
                        <li class="mb-2">Markanızla aynı değerleri paylaşan üreticileri tercih edin</li>
                        <li class="mb-2">Hedef kitlenizle örtüşen bir kitleye sahip olmalı</li>
                        <li class="mb-2">Geçmiş iş birlikleri ve içerik kalitesini inceleyin</li>
                        <li>Uzun vadeli iş birliği potansiyeli olanları seçin</li>
                    </ul>
                </div>
            </div>
            
            <!-- Yardım -->
            <div class="card shadow-sm" data-aos="fade-left" data-aos-delay="200">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-question-circle me-2 text-primary"></i> Yardıma mı ihtiyacınız var?</h5>
                    <p class="card-text small">Teklifinizin nasıl oluşturulacağı veya içerik üreticileriyle nasıl çalışılacağı konusunda sorularınız mı var?</p>
                    <a href="marketplace_guide.php" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-book me-2"></i> Marketplace Rehberini İnceleyin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
