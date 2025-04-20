<?php
// Marketplace kullanım kılavuzu
session_start();
require_once 'includes/config.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Sayfa başlığı
$pageTitle = "Marketplace Rehberi";

// Stiller için extra header
$extraHeader = '<link rel="stylesheet" href="css/marketplace.css">';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-8 mx-auto text-center">
            <h1 class="mb-3" data-aos="fade-up">Xfolio Marketplace Rehberi</h1>
            <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                İçerik üreticileri ve markalar arasında etkili iş birlikleri oluşturmanın yolları
            </p>
        </div>
    </div>
    
    <div class="row">
        <!-- Ana İçerik -->
        <div class="col-lg-8">
            <!-- Giriş -->
            <div class="card shadow-sm mb-4" id="introduction" data-aos="fade-up">
                <div class="card-body p-4">
                    <h2 class="h3 mb-4">Marketplace'e Hoş Geldiniz</h2>
                    <p>Xfolio Marketplace, içerik üreticileri ve markalar arasında doğrudan bağlantı kurarak, her iki tarafın da karlı ve başarılı iş birlikleri kurmasını sağlayan dijital bir platformdur.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded p-2">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>İçerik Üreticileri İçin</h5>
                                    <p class="mb-0">Markaların tekliflerine erişim kazanın, yeteneklerinizi sergileyin ve gelir kaynaklarınızı çeşitlendirin.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded p-2">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>Markalar İçin</h5>
                                    <p class="mb-0">Hedef kitlenize uygun içerik üreticileriyle çalışın, kampanyalarınızın etkisini artırın.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- İçerik Üreticileri İçin -->
            <div class="card shadow-sm mb-4" id="creators" data-aos="fade-up">
                <div class="card-body p-4">
                    <h2 class="h3 mb-4">İçerik Üreticileri İçin Rehber</h2>
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-search text-primary me-2"></i> Teklifleri Nasıl Bulabilirsiniz?</h5>
                    <p>Marketplace ana sayfasında tüm aktif teklifler listelenir. Kategorilere göre filtreleme yapabilir veya arama kutusunu kullanarak size en uygun teklifleri bulabilirsiniz.</p>
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-paper-plane text-primary me-2"></i> Nasıl Başvuru Yapabilirsiniz?</h5>
                    <p>Teklif detay sayfasındaki "Başvur" butonunu kullanarak başvuru formunu doldurabilirsiniz. Başvuruda kendinizi tanıtan ve markanın neden sizinle çalışması gerektiğini açıklayan bir mesaj yazmanız önemlidir.</p>
                    
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-lightbulb me-2"></i> Etkili Bir Başvuru İçin İpuçları:</h6>
                        <ul class="mb-0 ps-3">
                            <li>Profil sayfanızın güncel ve detaylı olduğundan emin olun</li>
                            <li>Önceki iş birliklerinizden ve başarılarınızdan bahsedin</li>
                            <li>Takipçi sayınız yerine etkileşim oranınızı ve kitle demografinizi vurgulayın</li>
                            <li>Markayla nasıl değer yaratabileceğinizi belirtin</li>
                            <li>Marka için özgün ve yaratıcı fikirleriniz varsa bunları paylaşın</li>
                        </ul>
                    </div>
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-tasks text-primary me-2"></i> Başvurularınızı Nasıl Yönetebilirsiniz?</h5>
                    <p>Marketplace panelindeki "Başvurularım" sekmesinden tüm başvurularınızı ve durumlarını görüntüleyebilirsiniz. Onaylanan başvurularınız "Aktif İş Birlikleri" bölümünde yer alır.</p>
                </div>
            </div>
            
            <!-- Markalar İçin -->
            <div class="card shadow-sm mb-4" id="brands" data-aos="fade-up">
                <div class="card-body p-4">
                    <h2 class="h3 mb-4">Markalar İçin Rehber</h2>
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-bullhorn text-primary me-2"></i> Teklif Nasıl Oluşturabilirsiniz?</h5>
                    <p>"Yeni Teklif Oluştur" butonunu kullanarak kampanyanız için yeni bir teklif oluşturabilirsiniz. Teklifin başarılı olması için aşağıdaki alanları detaylı ve net bir şekilde doldurmanız önemlidir:</p>
                    
                    <ul>
                        <li><strong>Başlık:</strong> İçerik üreticilerinin ilgisini çekecek kısa ve net bir başlık</li>
                        <li><strong>Açıklama:</strong> Kampanyanın amacı, kapsamı ve beklentileriniz</li>
                        <li><strong>Kategori ve İş Birliği Türü:</strong> Doğru hedefleme için uygun kategoriler</li>
                        <li><strong>Bütçe:</strong> Ödenecek miktarın açık ve net belirtilmesi</li>
                        <li><strong>Aranan Özellikler:</strong> İçerik üreticisinde aranan nitelikler</li>
                        <li><strong>Teslim Edilecekler:</strong> İçerik üreticisinden beklenen iş ve sorumluluklar</li>
                        <li><strong>Son Başvuru Tarihi:</strong> Başvuruların ne zamana kadar kabul edileceği</li>
                    </ul>
                    
                    <div class="alert alert-primary mt-3">
                        <h6><i class="fas fa-lightbulb me-2"></i> Başarılı Bir Teklif İçin İpuçları:</h6>
                        <ul class="mb-0 ps-3">
                            <li>Gerçekçi bir bütçe belirleyin, çok düşük bütçeler kaliteli başvuruları engelleyebilir</li>
                            <li>Tercihen uzun vadeli iş birliği fırsatlarından bahsedin</li>
                            <li>İçerik üreticisine yaratıcı özgürlük alanı bırakın</li>
                            <li>Net bir takvim ve zaman çizelgesi belirtin</li>
                        </ul>
                    </div>
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-inbox text-primary me-2"></i> Başvuruları Nasıl Değerlendirebilirsiniz?</h5>
                    <p>Marketplace panelindeki "Gelen Başvurular" sekmesinden tüm tekliflerinize gelen başvuruları görüntüleyebilir ve yönetebilirsiniz. Başvuruları onaylayabilir, reddedebilir veya aktif iş birliklerini tamamlandı olarak işaretleyebilirsiniz.</p>
                    
                    <p>Başvuruları değerlendirirken şunlara dikkat edin:</p>
                    
                    <ul>
                        <li>İçerik üreticisinin profilini ve geçmiş içeriklerini inceleyin</li>
                        <li>Kitlesinin demografik yapısı ve etkileşim oranları</li>
                        <li>Başvuru mesajındaki profesyonellik ve ilgi seviyesi</li>
                        <li>Markanız için ne gibi özgün fikirler sunduğu</li>
                    </ul>
                </div>
            </div>
            
            <!-- İş Birliği Yönetimi -->
            <div class="card shadow-sm mb-4" id="collaboration" data-aos="fade-up">
                <div class="card-body p-4">
                    <h2 class="h3 mb-4">İş Birliği Yönetimi</h2>
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-handshake text-primary me-2"></i> Başarılı Bir İş Birliği İçin En İyi Uygulamalar</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="h6 mb-0">İçerik Üreticileri İçin</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="mb-0 ps-3">
                                        <li>Anlaşılan zaman çizelgelerine sadık kalın</li>
                                        <li>Taslak içerikleri onay için önceden gönderin</li>
                                        <li>Marka değerlerine ve yönergelerine uyun</li>
                                        <li>İçeriğinizin performansını raporlayın</li>
                                        <li>İş birliğinin etkisini artıracak ekstra önerilerinizi paylaşın</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="h6 mb-0">Markalar İçin</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="mb-0 ps-3">
                                        <li>Açık ve kapsamlı bir içerik brifingi hazırlayın</li>
                                        <li>İçerik üreticisine yaratıcı özgürlük tanıyın</li>
                                        <li>Hızlı geri bildirim ve onay süreçleri oluşturun</li>
                                        <li>Ödemeleri zamanında yapın</li>
                                        <li>Başarılı olduğunda uzun vadeli ilişki kurun</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-check-circle text-primary me-2"></i> İş Birliğini Tamamlama</h5>
                    <p>İş birliği tamamlandığında, her iki taraf da "Aktif İş Birlikleri" sekmesinden "Tamamlandı" olarak işaretleyebilir. İş birliği sonrası değerlendirme yaparak deneyimlerinizi paylaşmanız, platformun ve iş birliklerinin kalitesini artırmaya yardımcı olur.</p>
                </div>
            </div>
            
            <!-- SSS -->
            <div class="card shadow-sm mb-4" id="faq" data-aos="fade-up">
                <div class="card-body p-4">
                    <h2 class="h3 mb-4">Sıkça Sorulan Sorular</h2>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faqOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Teklif oluştururken bir ücret ödenir mi?
                                </button>
                            </h3>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Hayır, Xfolio Marketplace'te teklif oluşturmak tamamen ücretsizdir. Platform, sadece başarılı iş birlikleri kurulmasına aracılık eder, ödeme ve anlaşma süreçleri taraflar arasında gerçekleşir.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faqTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    İş birliği sürecinde bir anlaşmazlık olursa ne yapmalıyım?
                                </button>
                            </h3>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Herhangi bir anlaşmazlık durumunda, ilk adım karşı tarafla açık ve dürüst iletişim kurmaktır. Sorun çözülemezse, <a href="contact.php">destek ekibimizle</a> iletişime geçebilirsiniz. Ekibimiz arabuluculuk yaparak sorunu çözmenize yardımcı olacaktır.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faqThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Ödemeler nasıl yapılır?
                                </button>
                            </h3>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Xfolio şu an için ödeme altyapısı sunmamaktadır. İş birliği anlaşmaları ve ödemeler doğrudan taraflar arasında kararlaştırılır ve gerçekleştirilir. İleride güvenli ödeme sistemi eklemeyi planlıyoruz.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faqFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Kaç teklife başvurabilirim?
                                </button>
                            </h3>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="faqFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    İçerik üreticileri olarak istediğiniz kadar teklife başvurabilirsiniz. Ancak, kapasitenizdeki iş yükünü değerlendirmenizi ve sadece gerçekten ilgilendiğiniz ve tamamlayabileceğiniz projelere başvurmanızı öneririz.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faqFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    Teklifimi nasıl öne çıkarabilirim?
                                </button>
                            </h3>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="faqFive" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Teklifinizi öne çıkarmak için: 1) Net ve çekici bir başlık kullanın, 2) Detaylı açıklamalar ve gerçekçi bir bütçe belirleyin, 3) Teklifi doğru kategoride yayınlayın, 4) İçerik üreticileri için değer önerinizi vurgulayın, 5) Gerçekçi ve cazip bir takvim sunun.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sonuç -->
            <div class="card shadow-sm" id="conclusion" data-aos="fade-up">
                <div class="card-body p-4">
                    <h2 class="h3 mb-4">Başlamaya Hazır mısınız?</h2>
                    <p>Xfolio Marketplace, içerik üreticileri ve markalar için yeni fırsatlar ve iş birlikleri sunan dinamik bir platformdur. İster içerik üreticisi ister marka olun, platforma katılarak dijital pazarlama dünyasındaki potansiyelinizi en üst düzeye çıkarabilirsiniz.</p>
                    
                    <div class="mt-4 d-flex">
                        <?php if ($isLoggedIn): ?>
                            <a href="marketplace.php" class="btn btn-primary me-3">
                                <i class="fas fa-search me-2"></i> Marketplace'i Keşfet
                            </a>
                            <a href="marketplace_create.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i> Teklif Oluştur
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=marketplace.php" class="btn btn-primary me-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Giriş Yap
                            </a>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i> Kayıt Ol
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- İçindekiler -->
        <div class="col-lg-4">
            <div class="sticky-top pt-3" style="top: 90px;">
                <div class="card shadow-sm mb-4" data-aos="fade-left">
                    <div class="card-header">
                        <h5 class="mb-0">İçindekiler</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#introduction" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-info-circle me-2 text-primary"></i> Giriş
                            </a>
                            <a href="#creators" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-user me-2 text-primary"></i> İçerik Üreticileri İçin
                            </a>
                            <a href="#brands" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-building me-2 text-primary"></i> Markalar İçin
                            </a>
                            <a href="#collaboration" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-handshake me-2 text-primary"></i> İş Birliği Yönetimi
                            </a>
                            <a href="#faq" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-question-circle me-2 text-primary"></i> SSS
                            </a>
                            <a href="#conclusion" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-flag-checkered me-2 text-primary"></i> Sonuç
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (!$isLoggedIn): ?>
                <!-- Kayıt CTA -->
                <div class="card shadow-sm mb-4 border-primary" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                        <h5>Hemen Başlayın</h5>
                        <p class="mb-4">İçerik üreticisi veya marka olarak kayıt olun ve yeni iş birliği fırsatlarını yakalayın.</p>
                        <a href="register.php" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i> Hemen Kayıt Olun
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- İletişim -->
                <div class="card shadow-sm" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-body">
                        <h5><i class="fas fa-headset me-2 text-primary"></i> Yardıma mı İhtiyacınız Var?</h5>
                        <p class="card-text small">Marketplace hakkında daha fazla bilgi almak veya sorularınız için bizimle iletişime geçebilirsiniz.</p>
                        <a href="contact.php" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-envelope me-2"></i> İletişime Geçin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS ile içindekiler için smooth scroll ve active class -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for TOC links
    document.querySelectorAll('.list-group-item').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
                
                // Update active class
                document.querySelectorAll('.list-group-item').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });
    
    // Update active class on scroll
    window.addEventListener('scroll', function() {
        let current = '';
        
        document.querySelectorAll('.card[id]').forEach(section => {
            const sectionTop = section.offsetTop - 120;
            const sectionHeight = section.offsetHeight;
            
            if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
                current = '#' + section.getAttribute('id');
            }
        });
        
        document.querySelectorAll('.list-group-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('href') === current) {
                item.classList.add('active');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
