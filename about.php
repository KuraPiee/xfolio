<?php
// Ana tanıtım sayfası
session_start();
require_once 'includes/config.php';

// Kullanıcı sayısı
$userCountSql = "SELECT COUNT(*) as count FROM users WHERE is_verified = 1";
$userCountResult = mysqli_query($conn, $userCountSql);
$userCount = mysqli_fetch_assoc($userCountResult)['count'];

// Portfolyo sayısı
$portfolioCountSql = "SELECT COUNT(*) as count FROM portfolios";
$portfolioCountResult = mysqli_query($conn, $portfolioCountSql);
$portfolioCount = mysqli_fetch_assoc($portfolioCountResult)['count'];

// Sponsorluk teklifi sayısı
$offerCountSql = "SELECT COUNT(*) as count FROM sponsorship_offers WHERE is_active = 1";
$offerCountResult = mysqli_query($conn, $offerCountSql);
$offerCount = mysqli_fetch_assoc($offerCountResult)['count'];

// Başarılı iş birliği sayısı
$collaborationCountSql = "SELECT COUNT(*) as count FROM sponsorship_applications WHERE status = 'completed'";
$collaborationCountResult = mysqli_query($conn, $collaborationCountSql);
$collaborationCount = mysqli_fetch_assoc($collaborationCountResult)['count'];

// En popüler platformlar
$popularPlatformsSql = "SELECT platform, COUNT(*) as count FROM portfolios GROUP BY platform ORDER BY count DESC LIMIT 5";
$popularPlatformsResult = mysqli_query($conn, $popularPlatformsSql);

// Sayfa başlığı
$pageTitle = "Xfolio - Tüm Sosyal Medya Hesaplarınızı Tek Yerde Yönetin";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section position-relative">
    <div class="hero-background"></div>
    <div class="container py-5">
        <div class="row align-items-center min-vh-75 py-5">
            <div class="col-lg-6 text-center text-lg-start" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-3">Sosyal Medya Varlığınızı Tek Noktadan Yönetin</h1>
                <p class="lead mb-4">Xfolio ile tüm sosyal medya hesaplarınızı tek bir platformda toplayın, istatistiklerinizi takip edin ve içerik üreticileriyle markalar arasında iş birlikleri oluşturun.</p>
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i> Ücretsiz Kaydol
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i> Daha Fazla Bilgi
                    </a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0" data-aos="fade-left">
                <div class="hero-image text-center">
                    <img src="images/hero-mockup.png" alt="Xfolio Dashboard" class="img-fluid" style="max-width: 90%;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İstatistikler -->
<section class="bg-gradient py-5 text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4 mb-md-0" data-aos="fade-up">
                <div class="stat-item">
                    <div class="display-4 fw-bold mb-2"><?php echo number_format($userCount); ?>+</div>
                    <p class="mb-0">Aktif Kullanıcı</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-item">
                    <div class="display-4 fw-bold mb-2"><?php echo number_format($portfolioCount); ?>+</div>
                    <p class="mb-0">Sosyal Medya Bağlantısı</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-item">
                    <div class="display-4 fw-bold mb-2"><?php echo number_format($offerCount); ?>+</div>
                    <p class="mb-0">Aktif Sponsorluk Teklifi</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-item">
                    <div class="display-4 fw-bold mb-2"><?php echo number_format($collaborationCount); ?>+</div>
                    <p class="mb-0">Başarılı İş Birliği</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Özellikler -->
<section id="features" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold" data-aos="fade-up">Xfolio'nun Sunduğu Özellikler</h2>
            <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">İçerik üreticileri ve markalar için tasarlanmış kapsamlı özellikler</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary text-white rounded mb-3">
                            <i class="fas fa-link"></i>
                        </div>
                        <h4>Portfolyo Yönetimi</h4>
                        <p class="text-muted mb-0">Tüm sosyal medya hesaplarınızı tek bir profilde toplayın. YouTube, Instagram, Twitter, TikTok ve daha fazlası.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-success text-white rounded mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>İstatistik Takibi</h4>
                        <p class="text-muted mb-0">Takipçi sayınızı ve etkileşim oranlarınızı detaylı grafiklerle analiz edin, büyümenizi görsel olarak takip edin.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-info text-white rounded mb-3">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h4>Marketplace</h4>
                        <p class="text-muted mb-0">Markaları ve içerik üreticilerini buluşturan marketplace ile yeni iş birliği fırsatları yakalayın.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-warning text-white rounded mb-3">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <h4>Kolay Paylaşım</h4>
                        <p class="text-muted mb-0">Profilinizi ve portfolyo linklerinizi tek tıkla sosyal medyada paylaşın, QR kod oluşturun.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-danger text-white rounded mb-3">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h4>Özelleştirilebilir Tema</h4>
                        <p class="text-muted mb-0">Farklı renk temaları arasında geçiş yaparak platformu kendi tarzınıza uygun hale getirin.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="feature-icon bg-primary text-white rounded mb-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Keşif Merkezi</h4>
                        <p class="text-muted mb-0">Diğer içerik üreticilerini keşfedin, ilham alın ve takip ederek ağınızı genişletin.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nasıl Çalışır -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold" data-aos="fade-up">Nasıl Çalışır?</h2>
            <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Xfolio'yu kullanmak üç basit adımdan oluşur</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="step-number mb-3">1</div>
                        <h4>Hesap Oluşturun</h4>
                        <p class="text-muted mb-0">Ücretsiz bir hesap oluşturun ve profilinizi düzenleyin. Biyografinizi ve profil resminizi ekleyin.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="step-number mb-3">2</div>
                        <h4>Sosyal Medya Hesaplarınızı Ekleyin</h4>
                        <p class="text-muted mb-0">YouTube, Instagram, Twitter, TikTok ve diğer platformlardaki hesaplarınızı portfolyonuza ekleyin.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="step-number mb-3">3</div>
                        <h4>Paylaşın ve Büyüyün</h4>
                        <p class="text-muted mb-0">Xfolio profilinizi paylaşın, takipçilerinizi artırın ve markalarla iş birlikleri yapın.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Marketplace Tanıtımı -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="marketplace-image">
                    <img src="images/marketplace-mockup.png" alt="Xfolio Marketplace" class="img-fluid rounded shadow">
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="display-5 fw-bold mb-3">Xfolio Marketplace</h2>
                <p class="lead">İçerik üreticileri ve markalar arasında iş birliği fırsatları sunan dijital pazar yeri.</p>
                <ul class="feature-list mb-4">
                    <li><i class="fas fa-check-circle text-success me-2"></i> Sponsorluk teklifleri oluşturun ve başvurun</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i> İş birliklerinizi tek bir panelden yönetin</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i> Marka elçiliği ve içerik üretimi fırsatları</li>
                    <li><i class="fas fa-check-circle text-success me-2"></i> Ürün inceleme ve etkinlik sponsorlukları</li>
                </ul>
                <a href="marketplace.php" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-2"></i> Marketplace'i Keşfet
                </a>
            </div>
        </div>
    </div>
</section>

<!-- İstatistik Takibi -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0" data-aos="fade-left">
                <div class="stats-image">
                    <img src="images/stats-mockup.png" alt="Xfolio İstatistikler" class="img-fluid rounded shadow">
                </div>
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <h2 class="display-5 fw-bold mb-3">Detaylı İstatistik Takibi</h2>
                <p class="lead">Sosyal medya hesaplarınızın performansını gerçek zamanlı olarak takip edin.</p>
                <ul class="feature-list mb-4">
                    <li><i class="fas fa-chart-line text-primary me-2"></i> Takipçi sayısı zaman çizelgesi</li>
                    <li><i class="fas fa-chart-pie text-primary me-2"></i> Platform bazında takipçi dağılımı</li>
                    <li><i class="fas fa-chart-bar text-primary me-2"></i> Büyüme oranı görselleştirmesi</li>
                    <li><i class="fas fa-poll text-primary me-2"></i> Platform ortalamalarıyla karşılaştırmalı analiz</li>
                </ul>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Hemen Başlayın
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Popüler Platformlar -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold" data-aos="fade-up">Popüler Platformlar</h2>
            <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Kullanıcılarımız tarafından en çok eklenen sosyal medya platformları</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="platforms-container" data-aos="fade-up">
                    <?php if (mysqli_num_rows($popularPlatformsResult) > 0): ?>
                        <div class="row text-center">
                            <?php while ($platform = mysqli_fetch_assoc($popularPlatformsResult)): 
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
                                <div class="col">
                                    <div class="platform-icon-lg <?php echo $platformClass; ?> mx-auto mb-3">
                                        <i class="fab <?php echo $icon; ?>"></i>
                                    </div>
                                    <h5><?php echo htmlspecialchars($platformName); ?></h5>
                                    <p class="text-muted"><?php echo $platform['count']; ?> bağlantı</p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <p class="text-muted">Henüz platform verisi bulunmuyor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="discover.php" class="btn btn-outline-primary">
                <i class="fas fa-users me-2"></i> İçerik Üreticilerini Keşfet
            </a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold" data-aos="fade-up">Kullanıcılarımız Ne Diyor?</h2>
            <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">İçerik üreticileri ve markalardan geri bildirimler</p>
        </div>
        
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="testimonial-slider" data-aos="fade-up">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex mb-3">
                                        <div class="testimonial-rating text-warning">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                    <p class="testimonial-text mb-4">"Xfolio sayesinde tüm sosyal medya hesaplarımı tek bir yerde yönetebiliyorum. Takipçi sayılarımı takip etmek ve istatistiklerimi görselleştirmek çok kolay. Üstelik marketplace üzerinden birçok markayla iş birliği yapma fırsatı yakaladım."</p>
                                    <div class="d-flex align-items-center">
                                        <div class="testimonial-avatar me-3">
                                            <img src="images/testimonial-1.jpg" alt="Ayşe Y." class="rounded-circle">
                                        </div>
                                        <div>
                                            <h5 class="mb-0">Ayşe Y.</h5>
                                            <p class="text-muted small mb-0">Lifestyle İçerik Üreticisi</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex mb-3">
                                        <div class="testimonial-rating text-warning">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                    <p class="testimonial-text mb-4">"Markamız için içerik üreticileri bulmak ve iş birliği yapmak artık çok daha kolay. Xfolio Marketplace üzerinden oluşturduğumuz tekliflerle hedef kitlemize uygun içerik üreticilerini hızlıca bulabildik. Şimdiye kadar 15'ten fazla başarılı kampanya yürüttük."</p>
                                    <div class="d-flex align-items-center">
                                        <div class="testimonial-avatar me-3">
                                            <img src="images/testimonial-2.jpg" alt="Mehmet K." class="rounded-circle">
                                        </div>
                                        <div>
                                            <h5 class="mb-0">Mehmet K.</h5>
                                            <p class="text-muted small mb-0">Pazarlama Müdürü, XYZ Marka</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section text-center text-white py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Hemen Xfolio'ya Katılın</h2>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">Sosyal medya varlığınızı yönetin, içerik üreticileriyle veya markalarla iş birlikleri yapın ve online varlığınızı güçlendirin.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="register.php" class="btn btn-light btn-lg" data-aos="fade-up" data-aos-delay="200">
                        <i class="fas fa-user-plus me-2"></i> Ücretsiz Kaydol
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-lg" data-aos="fade-up" data-aos-delay="300">
                        <i class="fas fa-sign-in-alt me-2"></i> Giriş Yap
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Özel CSS -->
<style>
.hero-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, #4158d0 100%);
    color: white;
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTQ0MCIgaGVpZ2h0PSI3NjYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBmaWxsPSIjMDAwIiBmaWxsLW9wYWNpdHk9Ii4xNSIgZD0iTTAgMGgxNDQwdjc2NkgweiIvPjxwYXRoIGQ9Ik0wIDc2NmgxNDQwVjBIMHY3NjZ6bTcyMC0zODNjNjMuNTEgMCAxMTUtNTEuNDkgMTE1LTExNXMtNTEuNDktMTE1LTExNS0xMTVjLTYzLjUxMyAwLTExNSA1MS40OS0xMTUgMTE1czUxLjQ4NyAxMTUgMTE1IDExNXoiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iLjA1Ii8+PC9nPjwvc3ZnPg==');
    opacity: 0.1;
    background-size: cover;
}

.bg-gradient {
    background: linear-gradient(135deg, var(--primary-color) 0%, #4158d0 100%);
}

.feature-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.step-number {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background-color: var(--primary-color);
    color: white;
    border-radius: 50%;
    margin: 0 auto;
    font-weight: bold;
}

.platform-icon-lg {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 36px;
    color: white;
}

.youtube {
    background-color: #FF0000;
}

.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.twitter {
    background-color: #1DA1F2;
}

.twitch {
    background-color: #6441a5;
}

.tiktok {
    background-color: #000000;
}

.facebook {
    background-color: #4267B2;
}

.linkedin {
    background-color: #0077B5;
}

.website {
    background-color: #4CAF50;
}

.feature-list {
    list-style-type: none;
    padding-left: 0;
}

.feature-list li {
    margin-bottom: 12px;
}

.testimonial-avatar img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border: 2px solid var(--primary-color);
}

.cta-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, #4158d0 100%);
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTQ0MCIgaGVpZ2h0PSI3NjYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBmaWxsPSIjMDAwIiBmaWxsLW9wYWNpdHk9Ii4xNSIgZD0iTTAgMGgxNDQwdjc2NkgweiIvPjxwYXRoIGQ9Ik0wIDc2NmgxNDQwVjBIMHY3NjZ6bTcyMC0zODNjNjMuNTEgMCAxMTUtNTEuNDkgMTE1LTExNXMtNTEuNDktMTE1LTExNS0xMTVjLTYzLjUxMyAwLTExNSA1MS40OS0xMTUgMTE1czUxLjQ4NyAxMTUgMTE1IDExNXoiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iLjA1Ii8+PC9nPjwvc3ZnPg==');
    opacity: 0.1;
    background-size: cover;
    z-index: 0;
}

.cta-section > div {
    position: relative;
    z-index: 1;
}

/* Responsive */
@media (max-width: 767px) {
    .hero-section {
        text-align: center;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .platform-icon-lg {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
