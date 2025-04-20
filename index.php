<?php
// Index.php - Homepage
$extraHeader = '<link rel="canonical" href="https://yourwebsite.com/index.php">';

// Include header
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1>Tüm Sosyal Medya Bağlantılarınızı Tek Bir Yerde Toplayın</h1>
                <p>Influencer Portfolyo ile YouTube, Instagram, Twitter, Twitch ve diğer tüm sosyal medya platformlarındaki hesaplarınızı tek bir profilde birleştirin ve takipçilerinizle kolayca paylaşın.</p>
                <div class="hero-buttons">
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-light btn-lg me-3">Hemen Başla</a>
                        <a href="discover.php" class="btn btn-outline-light btn-lg">İnfluencer'ları Keşfet</a>
                    <?php else: ?>
                        <a href="profile.php" class="btn btn-light btn-lg me-3">Profilime Git</a>
                        <a href="discover.php" class="btn btn-outline-light btn-lg">İnfluencer'ları Keşfet</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                <img src="images/hero-image.svg" alt="Sosyal Medya Portfolyo" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2>Neden Influencer Portfolyo?</h2>
            <p class="lead">Sosyal medya hesaplarınızı yönetmeyi kolaylaştırıyoruz</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 text-center p-4 hover-effect">
                    <div class="card-body">
                        <i class="fas fa-link fa-3x mb-3 text-primary"></i>
                        <h3>Tek Link</h3>
                        <p>Tüm sosyal medya bağlantılarınızı tek bir adreste toplayın ve paylaşın. Takipçileriniz için kolay erişim sağlayın.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 text-center p-4 hover-effect">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                        <h3>Analitik</h3>
                        <p>Profilinizin ziyaretçi istatistiklerini görün. Hangi sosyal medya platformlarına daha çok ilgi olduğunu keşfedin.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 text-center p-4 hover-effect">
                    <div class="card-body">
                        <i class="fas fa-user-friends fa-3x mb-3 text-primary"></i>
                        <h3>Topluluk</h3>
                        <p>Diğer içerik üreticileriyle bağlantı kurun, takip edin ve iş birliği fırsatları yakalayın.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2>Nasıl Çalışır?</h2>
            <p class="lead">3 Basit Adımda Portfolyonuzu Oluşturun</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 p-4 hover-effect">
                    <div class="card-body">
                        <div class="step-number">1</div>
                        <h3>Hesap Oluşturun</h3>
                        <p>Hızlıca hesabınızı oluşturun ve profilinizi tamamlayın. Kişisel bilgilerinizi ve profil fotoğrafınızı ekleyin.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 p-4 hover-effect">
                    <div class="card-body">
                        <div class="step-number">2</div>
                        <h3>Sosyal Medya Bağlantılarınızı Ekleyin</h3>
                        <p>YouTube, Instagram, Twitter ve diğer platformlardaki hesaplarınızın bağlantılarını profilinize ekleyin.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 p-4 hover-effect">
                    <div class="card-body">
                        <div class="step-number">3</div>
                        <h3>Portfolyonuzu Paylaşın</h3>
                        <p>Benzersiz profil bağlantınızı sosyal medyada, e-postalarda veya kartvizitinizde paylaşın.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Users Section -->
<section class="popular-users py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2>Popüler İnfluencer'lar</h2>
            <p class="lead">Platformumuzdaki öne çıkan içerik üreticileri</p>
        </div>
        
        <div class="row">
            <?php
            // Get popular users (users with most followers)
            $sql = "SELECT u.id, u.username, u.avatar, u.bio, COUNT(f.id) as follower_count 
                    FROM users u 
                    LEFT JOIN followers f ON u.id = f.followed_id 
                    GROUP BY u.id 
                    ORDER BY follower_count DESC 
                    LIMIT 8";
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                while ($user = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up">
                    <div class="card user-card h-100 hover-effect">
                        <div class="card-body text-center">
                            <img src="uploads/avatars/<?php echo $user['avatar']; ?>" alt="<?php echo $user['username']; ?>" class="avatar-lg mb-3">
                            <h5 class="card-title"><?php echo $user['username']; ?></h5>
                            <p class="card-text small text-muted"><?php echo substr($user['bio'], 0, 60) . (strlen($user['bio']) > 60 ? '...' : ''); ?></p>
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <span class="me-2"><i class="fas fa-users"></i> <?php echo $user['follower_count']; ?> takipçi</span>
                            </div>
                            <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary btn-sm">Profili Görüntüle</a>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<div class="col-12 text-center"><p>Henüz kullanıcı yok.</p></div>';
            }
            ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="discover.php" class="btn btn-primary">Daha Fazla İnfluencer Keşfet</a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2>Kullanıcılarımız Ne Diyor?</h2>
            <p class="lead">Platformumuzu kullananların deneyimleri</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 p-4 hover-effect">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="images/testimonial-1.jpg" alt="Testimonial User" class="avatar-sm me-3">
                            <div>
                                <h5 class="mb-0">Ayşe Yılmaz</h5>
                                <small class="text-muted">YouTube İçerik Üreticisi</small>
                            </div>
                        </div>
                        <p class="card-text">"Tüm sosyal medya hesaplarımı tek bir yerde toplamak çok pratik oldu. Artık takipçilerim tüm içeriklerime kolayca erişebiliyor."</p>
                        <div class="testimonial-rating">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 p-4 hover-effect">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="images/testimonial-2.jpg" alt="Testimonial User" class="avatar-sm me-3">
                            <div>
                                <h5 class="mb-0">Mehmet Can</h5>
                                <small class="text-muted">Twitch Yayıncısı</small>
                            </div>
                        </div>
                        <p class="card-text">"Portfolyo platformu sayesinde izleyicilerim tüm sosyal medya hesaplarımı kolayca bulabiliyor. Bu da takipçi sayımı artırmama yardımcı oldu."</p>
                        <div class="testimonial-rating">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 p-4 hover-effect">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="images/testimonial-3.jpg" alt="Testimonial User" class="avatar-sm me-3">
                            <div>
                                <h5 class="mb-0">Zeynep Kaya</h5>
                                <small class="text-muted">Instagram Influencer</small>
                            </div>
                        </div>
                        <p class="card-text">"Modern ve kullanımı kolay bir platform. Diğer içerik üreticilerini keşfetmek ve bağlantı kurmak için harika bir yer."</p>
                        <div class="testimonial-rating">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center" data-aos="fade-up">
                <h2>Hemen Influencer Portfolyonuzu Oluşturun</h2>
                <p class="lead mb-4">Tüm sosyal medya bağlantılarınızı tek bir yerde toplayın ve takipçi kitlenizi büyütün.</p>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-primary btn-lg">Ücretsiz Başlayın</a>
                <?php else: ?>
                    <a href="profile.php" class="btn btn-primary btn-lg">Profilime Git</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>
