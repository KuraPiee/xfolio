# Xfolio - İçerik Üreticileri için Portfolyo Platformu

Xfolio, içerik üreticileri ve markalar için geliştirilmiş kapsamlı bir portfolyo ve işbirliği platformudur. Bu platform, kullanıcıların tüm sosyal medya hesaplarını tek bir profilde toplamasına, istatistiklerini takip etmesine ve markalarla işbirliği yapmasına olanak tanır.

## Özellikler

### Portfolyo Yönetimi
- YouTube, Instagram, Twitter, TikTok ve diğer platformlardaki hesaplarınızı tek bir profilde toplayın
- Takipçi sayılarını ve diğer istatistikleri otomatik güncelleme
- Özelleştirilebilir profil sayfaları

### İstatistik Takibi ve Grafikler
- Takipçi sayısı zaman çizelgesi
- Platform bazında takipçi dağılımı pasta grafiği
- Büyüme oranı çubuk grafiği
- Performans karşılaştırma radar grafiği

### Sosyal Medya Paylaşım Özellikleri
- Profil ve portfolyo bağlantılarını sosyal medyada kolay paylaşım
- QR kod oluşturma
- Kolay bağlantı kopyalama
- Embed kodu oluşturma

### Marketplace Sistemi
- İçerik üreticileri ve markalar arasında iş birliği fırsatları
- Sponsorluk teklifi oluşturma ve başvurma
- Başvuru yönetimi
- İş birliği takibi

### Tema Sistemi
- 5 farklı renk teması (varsayılan mavi, koyu tema, yeşil, turuncu ve mor)
- Kullanıcı tercihleri kaydetme

## Teknolojiler

- PHP 7.4+
- MySQL 5.7+
- HTML5, CSS3, JavaScript
- Bootstrap 5
- Chart.js
- Font Awesome 5

## Kurulum

1. Repoyu klonlayın:
```bash
git clone https://github.com/yourusername/xfolio.git
```

2. Veritabanı yapılandırması:
   - `includes/config.php` dosyasında veritabanı bilgilerinizi ayarlayın:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'your_db_username');
   define('DB_PASSWORD', 'your_db_password');
   define('DB_NAME', 'portfolyo_db');
   ```

3. Web sunucunuzda PHP ve MySQL'in kurulu olduğundan emin olun.

4. Web tarayıcısında projenin bulunduğu dizine gidin.

5. Sistem otomatik olarak gereken veritabanı tablolarını oluşturacaktır.

## Kullanım

1. Kullanıcılar sisteme kayıt olmalıdır.
2. Kayıt olduktan sonra e-posta doğrulaması yapılmalıdır.
3. Profil oluşturup sosyal medya hesaplarınızı ekleyebilirsiniz.
4. Marketplace bölümünden iş birliği teklifleri oluşturabilir veya mevcut tekliflere başvurabilirsiniz.
5. İstatistiklerinizi grafiklerle takip edebilirsiniz.

## Lisans

[MIT Lisansı](LICENSE)

## İletişim

Herhangi bir soru, öneri veya geri bildirim için lütfen [erensitki@mail.com](mailto:erensitki@mail.com) adresine e-posta gönderin.

---

&copy; 2025 Xfolio | Tüm hakları saklıdır.
