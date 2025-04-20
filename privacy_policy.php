<?php
// Gizlilik Politikası sayfası
session_start();
require_once 'includes/config.php';

// Sayfa başlığı
$pageTitle = "Gizlilik Politikası - Xfolio";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="mb-4">Gizlilik Politikası</h1>
                    <p class="text-muted mb-4">Son güncelleme: 20 Nisan 2025</p>
                    
                    <div class="privacy-content">
                        <section class="mb-5">
                            <h2 class="h4">1. Giriş</h2>
                            <p>Xfolio ("biz", "bize" veya "bizim") olarak, gizliliğinize saygı duyuyoruz ve kişisel verilerinizin korunmasına büyük önem veriyoruz. Bu Gizlilik Politikası, web sitemizi ziyaret ettiğinizde veya hizmetlerimizi kullandığınızda kişisel verilerinizi nasıl topladığımızı, kullandığımızı, açıkladığımızı ve koruduğumuzu açıklamaktadır.</p>
                            <p>Hizmetlerimizi kullanarak, bu politikada belirtilen uygulamaları kabul etmiş olursunuz. Politikamızı kabul etmiyorsanız, lütfen web sitemizi kullanmayınız ve hizmetlerimize abone olmayınız.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">2. Topladığımız Bilgiler</h2>
                            <p>Web sitemizi ziyaret ettiğinizde ve hizmetlerimizi kullandığınızda aşağıdaki bilgileri toplayabiliriz:</p>
                            
                            <h3 class="h5 mt-4">2.1. Doğrudan Sağladığınız Bilgiler</h3>
                            <ul>
                                <li>Hesap oluşturma ve oturum açma bilgileri (ad, e-posta, şifre vb.)</li>
                                <li>Profil bilgileri (kullanıcı adı, biyografi, profil resmi vb.)</li>
                                <li>İletişim ve destek talepleri için verilen bilgiler</li>
                                <li>Portfolyo ve sosyal medya bağlantıları</li>
                                <li>Marketplace üzerinden iş birliği yapmak için paylaştığınız bilgiler</li>
                                <li>Ödeme bilgileri (premium üyelik alınması durumunda)</li>
                            </ul>
                            
                            <h3 class="h5 mt-4">2.2. Otomatik Olarak Toplanan Bilgiler</h3>
                            <ul>
                                <li>Kullanım verileri (ziyaret ettiğiniz sayfalar, tıklamalar, görüntüleme süreleri)</li>
                                <li>Cihaz bilgileri (IP adresi, tarayıcı türü, işletim sistemi)</li>
                                <li>Çerezler ve benzer izleme teknolojileri aracılığıyla toplanan bilgiler</li>
                                <li>Konum bilgileri (ülke, şehir düzeyinde)</li>
                            </ul>
                            
                            <h3 class="h5 mt-4">2.3. Üçüncü Taraf Kaynaklardan Alınan Bilgiler</h3>
                            <ul>
                                <li>Sosyal medya platformları üzerinden bağlantı kurduğunuzda paylaşmayı tercih ettiğiniz bilgiler</li>
                                <li>İzin verdiğiniz takdirde sosyal medya istatistikleri</li>
                                <li>İş ortaklarımızdan ve reklamcılarımızdan alınan veriler</li>
                            </ul>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">3. Bilgilerin Kullanımı</h2>
                            <p>Topladığımız bilgileri aşağıdaki amaçlarla kullanıyoruz:</p>
                            <ul>
                                <li>Hizmetlerimizi sağlamak ve yönetmek</li>
                                <li>Hesabınızı oluşturmak ve yönetmek</li>
                                <li>Size özelleştirilmiş içerik ve öneriler sunmak</li>
                                <li>Marketplace üzerinden iş birliği fırsatları sunmak</li>
                                <li>İletişim taleplerinize yanıt vermek</li>
                                <li>Hizmetlerimizi geliştirmek ve iyileştirmek</li>
                                <li>Site ve hizmet kullanımını analiz etmek</li>
                                <li>Güvenlik ve dolandırıcılık önleme</li>
                                <li>Yasal yükümlülüklerimizi yerine getirmek</li>
                                <li>İzin verdiğiniz diğer amaçlar için</li>
                            </ul>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">4. Bilgilerin Paylaşılması</h2>
                            <p>Kişisel bilgilerinizi aşağıdaki durumlar dışında üçüncü taraflarla paylaşmıyoruz:</p>
                            <ul>
                                <li><strong>Hizmet Sağlayıcıları:</strong> Hizmetlerimizi sunmamıza yardımcı olan üçüncü taraf hizmet sağlayıcılarla (örn. hosting, analiz, ödeme işlemcileri) bilgi paylaşabiliriz.</li>
                                <li><strong>İzinli Paylaşımlar:</strong> Profilinizi herkese açık hale getirdiğinizde, portfolyo bilgileriniz ve sosyal medya bağlantılarınız diğer kullanıcılar tarafından görüntülenebilir.</li>
                                <li><strong>Marketplace İş Birlikleri:</strong> Marketplace üzerinden bir teklife başvurduğunuzda veya teklif oluşturduğunuzda, belirlediğiniz bilgiler ilgili taraflarla paylaşılır.</li>
                                <li><strong>Yasal Gereklilikler:</strong> Yasal bir yükümlülüğü yerine getirmek, haklarımızı korumak veya yasaların gerektirdiği durumlarda bilgilerinizi paylaşabiliriz.</li>
                                <li><strong>İş Transferleri:</strong> Şirket birleşmesi, satın alma veya varlık satışı durumunda bilgileriniz devredilebilir.</li>
                                <li><strong>Açık İzninizle:</strong> Açık izninizi aldığımız diğer durumlarda bilgilerinizi paylaşabiliriz.</li>
                            </ul>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">5. Veri Güvenliği</h2>
                            <p>Kişisel verilerinizin güvenliğini sağlamak için çeşitli teknik ve organizasyonel önlemler uyguluyoruz. Bunlar arasında:</p>
                            <ul>
                                <li>SSL/TLS şifreleme kullanımı</li>
                                <li>Güvenli şifreleme yöntemleri</li>
                                <li>Erişim kontrolü ve yetkilendirme</li>
                                <li>Düzenli güvenlik değerlendirmeleri ve testleri</li>
                                <li>Veri minimizasyonu ve anonimleştirme uygulamaları</li>
                            </ul>
                            <p>Ancak internet üzerinden hiçbir veri aktarımı veya elektronik depolama yöntemi %100 güvenli değildir. Bu nedenle, kişisel verilerinizin mutlak güvenliğini garanti edemeyiz.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">6. Çerezler ve İzleme Teknolojileri</h2>
                            <p>Web sitemiz ve hizmetlerimiz, çerezler ve benzer izleme teknolojileri kullanmaktadır. Çerezler, bilgisayarınıza veya mobil cihazınıza yerleştirilen küçük metin dosyalarıdır ve aşağıdaki amaçlarla kullanılır:</p>
                            <ul>
                                <li>Temel site işlevselliğini sağlamak (gerekli çerezler)</li>
                                <li>Oturum yönetimi ve kimlik doğrulama</li>
                                <li>Site kullanımını ve performansını analiz etmek</li>
                                <li>Kişiselleştirilmiş deneyim sunmak</li>
                            </ul>
                            <p>Tarayıcı ayarlarınızı değiştirerek çerezleri devre dışı bırakabilir veya çerez bildirimlerini alabilirsiniz. Ancak, bazı çerezleri devre dışı bırakırsanız, web sitemizin bazı özellikleri düzgün çalışmayabilir.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">7. Kullanıcı Hakları</h2>
                            <p>Kişisel verilerinizle ilgili olarak aşağıdaki haklara sahipsiniz:</p>
                            <ul>
                                <li><strong>Erişim Hakkı:</strong> Hakkınızda hangi bilgilere sahip olduğumuzu öğrenebilirsiniz.</li>
                                <li><strong>Düzeltme Hakkı:</strong> Yanlış veya eksik bilgilerinizin düzeltilmesini isteyebilirsiniz.</li>
                                <li><strong>Silme Hakkı:</strong> Belirli durumlarda kişisel verilerinizin silinmesini talep edebilirsiniz.</li>
                                <li><strong>İşleme Sınırlandırma Hakkı:</strong> Belirli durumlarda kişisel verilerinizin işlenmesini sınırlandırabilirsiniz.</li>
                                <li><strong>Veri Taşınabilirliği Hakkı:</strong> Verilerinizi yapılandırılmış, yaygın olarak kullanılan ve makine tarafından okunabilir bir biçimde almanızı ve bu verileri başka bir veri sorumlusuna iletmenizi isteyebilirsiniz.</li>
                                <li><strong>İtiraz Etme Hakkı:</strong> Meşru menfaatlerimiz temelinde işlenen kişisel verilerinize itiraz edebilirsiniz.</li>
                            </ul>
                            <p>Bu haklarınızı kullanmak için lütfen aşağıdaki iletişim bilgilerini kullanarak bizimle iletişime geçin. Talebinizi 30 gün içinde yanıtlamaya çalışacağız.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">8. Çocukların Gizliliği</h2>
                            <p>Hizmetlerimiz 18 yaşın altındaki kişilere yönelik değildir. 18 yaşın altındaki kişilerden bilerek kişisel bilgi toplamıyoruz. Eğer bir ebeveyn veya vasi iseniz ve çocuğunuzun bize kişisel bilgi verdiğini düşünüyorsanız, lütfen bizimle iletişime geçin ve bu bilgileri sistemlerimizden kaldırmak için gerekli adımları atacağız.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">9. Üçüncü Taraf Bağlantıları</h2>
                            <p>Web sitemiz, üçüncü taraf web sitelerine bağlantılar içerebilir. Bu üçüncü taraf sitelerin gizlilik politikaları veya içeriği üzerinde kontrolümüz yoktur ve bunlardan sorumlu değiliz. Bu bağlantıları kullanmadan önce ilgili sitelerin gizlilik politikalarını incelemenizi öneririz.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">10. Sosyal Medya İntegrasyonu</h2>
                            <p>Xfolio, çeşitli sosyal medya platformlarıyla entegrasyon sağlar. Bu entegrasyon aracılığıyla izin verdiğinizde, sosyal medya profillerinizden belirli bilgileri toplayabiliriz. Bu bilgiler, ilgili sosyal medya platformlarının gizlilik politikaları uyarınca toplanır ve işlenir. Sosyal medya platformlarının bilgilerinizi nasıl kullandığı hakkında bilgi edinmek için lütfen ilgili platformların kendi gizlilik politikalarını inceleyin.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">11. Gizlilik Politikası Değişiklikleri</h2>
                            <p>Bu Gizlilik Politikasını zaman zaman güncelleyebiliriz. Önemli değişiklikler yapıldığında, web sitemizde bir bildirim yayınlayarak veya size doğrudan bir bildirim göndererek sizi bilgilendireceğiz. Politikada yapılan değişikliklerin yürürlüğe girdiği tarihi belirlemek için lütfen bu sayfanın üst kısmındaki "Son güncelleme" tarihine bakın.</p>
                            <p>Bu nedenle, güncel gizlilik uygulamalarımızdan haberdar olmak için bu sayfayı düzenli olarak kontrol etmenizi öneririz. Web sitemizi ve hizmetlerimizi kullanmaya devam etmeniz, güncellenmiş politikayı kabul ettiğiniz anlamına gelir.</p>
                        </section>
                        
                        <section class="mb-5">
                            <h2 class="h4">12. Bize Ulaşın</h2>
                            <p>Bu Gizlilik Politikası veya kişisel verilerinizin işlenmesi hakkında herhangi bir sorunuz, endişeniz veya talebiniz varsa, lütfen aşağıdaki iletişim bilgilerini kullanarak bizimle iletişime geçin:</p>
                            <div class="contact-info">
                                <p><strong>E-posta:</strong> privacy@xfolio.com.tr</p>
                                <p><strong>Adres:</strong> Yıldız Teknik Üniversitesi Teknoloji Geliştirme Bölgesi, D2 Blok, No: 401, Esenler/İstanbul</p>
                                <p><strong>İletişim Formu:</strong> <a href="contact.php">İletişim Sayfamız</a></p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home me-2"></i> Ana Sayfaya Dön
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.privacy-content h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.privacy-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: var(--primary-color);
}

.privacy-content p, .privacy-content li {
    color: var(--text-color);
}

.privacy-content ul {
    margin-bottom: 1rem;
}

.privacy-content ul li {
    margin-bottom: 0.5rem;
}

.contact-info {
    background-color: var(--light-bg);
    padding: 1rem;
    border-radius: 0.5rem;
    margin-top: 1rem;
}
</style>

<?php include 'includes/footer.php'; ?>
