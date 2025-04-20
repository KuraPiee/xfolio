    </div>
    <!-- Footer -->
    <footer class="footer mt-auto py-5">
        <div class="container">
            <div class="row">                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5>Xfolio</h5>
                    <p class="text-muted">Tüm sosyal medya bağlantılarınızı tek bir yerde toplayın ve paylaşın. YouTuber'lar ve Influencer'lar için özel tasarlanmış portföy platformu.</p>
                </div>
                <div class="col-lg-2 mb-4 mb-lg-0">
                    <h5>Hızlı Erişim</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Ana Sayfa</a></li>
                        <li><a href="discover.php">Keşfet</a></li>
                        <li><a href="login.php">Giriş</a></li>
                        <li><a href="register.php">Kayıt Ol</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <h5>Yardım</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">S.S.S.</a></li>
                        <li><a href="#">Kullanım Koşulları</a></li>
                        <li><a href="#">Gizlilik Politikası</a></li>
                        <li><a href="#">Bize Ulaşın</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5>Bizi Takip Edin</h5>
                    <div class="social-icons">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start text-center">
                    <p class="small text-muted mb-0">&copy; <?php echo date('Y'); ?> Influencer Portfolyo. Tüm hakları saklıdır.</p>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <p class="small text-muted mb-0">Modern web tasarım. Performans ve SEO uyumlu.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>    <!-- Chart.js Kütüphanesi -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <!-- Tema Değiştirici -->
    <script src="js/theme-switcher.js"></script>
    
    <!-- İstatistik Güncelleme -->
    <script src="js/stats-updater.js"></script>
    
    <!-- Sosyal Medya Paylaşım -->
    <script src="js/social-share.js"></script>
    
    <!-- İstatistik Grafikleri -->
    <script src="js/stats-charts.js"></script>
    
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.getElementById('mainNav').classList.add('navbar-scrolled');
            } else {
                document.getElementById('mainNav').classList.remove('navbar-scrolled');
            }
        });
    </script>
    
    <?php if (isset($extraFooter)) echo $extraFooter; ?>
</body>
</html>
