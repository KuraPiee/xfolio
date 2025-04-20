/**
 * Xfolio Sosyal Medya Paylaşım Fonksiyonları
 * Bu dosya sosyal medyada paylaşım yapmayı kolaylaştıran fonksiyonları içerir
 */

document.addEventListener('DOMContentLoaded', function() {
    // Tüm paylaşım butonlarına tıklama olayı ekle
    document.querySelectorAll('.share-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const platform = this.getAttribute('data-platform');
            const shareUrl = this.getAttribute('data-url') || window.location.href;
            const shareTitle = this.getAttribute('data-title') || document.title;
            const shareText = this.getAttribute('data-text') || '';
            const shareImage = this.getAttribute('data-image') || '';
            
            shareToSocialMedia(platform, shareUrl, shareTitle, shareText, shareImage);
        });
    });
    
    // Kopyalama butonlarına tıklama olayı ekle
    document.querySelectorAll('.copy-link-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const url = this.getAttribute('data-url') || window.location.href;
            copyToClipboard(url);
            
            // Başarı mesajı göster
            showToast('success', 'Bağlantı kopyalandı!', 'Paylaşmak istediğiniz bağlantı panoya kopyalandı.');
            
            // Kopyalama animasyonu
            this.classList.add('copied');
            setTimeout(() => {
                this.classList.remove('copied');
            }, 2000);
        });
    });
    
    // Paylaşım kartını aç/kapat
    document.querySelectorAll('.share-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const dropdown = this.nextElementSibling;
            if (dropdown && dropdown.classList.contains('share-dropdown')) {
                dropdown.classList.toggle('show');
            }
        });
    });
    
    // Belge üzerine tıklandığında açık paylaşım kartlarını kapat
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.share-dropdown') && !e.target.closest('.share-dropdown-toggle')) {
            document.querySelectorAll('.share-dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
    
    // QR kod oluşturma butonunu kontrol et
    document.querySelectorAll('.generate-qr-button').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url') || window.location.href;
            const modalId = this.getAttribute('data-target');
            
            if (modalId) {
                const qrContainer = document.querySelector(`${modalId} .qr-code-container`);
                if (qrContainer) {
                    generateQRCode(url, qrContainer);
                }
            }
        });
    });
    
    // Sayfa yüklendiğinde otomatik olarak QR kod oluştur
    document.querySelectorAll('.auto-generate-qr').forEach(container => {
        const url = container.getAttribute('data-url') || window.location.href;
        generateQRCode(url, container);
    });
});

/**
 * Belirtilen platforma göre paylaşım yapar
 * 
 * @param {string} platform - Sosyal medya platformu (facebook, twitter, linkedin, whatsapp, telegram)
 * @param {string} url - Paylaşılacak URL
 * @param {string} title - Paylaşım başlığı
 * @param {string} text - Paylaşım metni
 * @param {string} image - Paylaşım görseli (optional)
 */
function shareToSocialMedia(platform, url, title, text, image) {
    let shareUrl = '';
    const encodedUrl = encodeURIComponent(url);
    const encodedTitle = encodeURIComponent(title);
    const encodedText = encodeURIComponent(text);
    
    switch (platform.toLowerCase()) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
            break;
        case 'twitter':
        case 'x':
            shareUrl = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodedTitle}%20${encodedUrl}`;
            break;
        case 'telegram':
            shareUrl = `https://t.me/share/url?url=${encodedUrl}&text=${encodedTitle}`;
            break;
        case 'pinterest':
            if (image) {
                shareUrl = `https://pinterest.com/pin/create/button/?url=${encodedUrl}&media=${encodeURIComponent(image)}&description=${encodedText}`;
            } else {
                shareUrl = `https://pinterest.com/pin/create/button/?url=${encodedUrl}&description=${encodedText}`;
            }
            break;
        case 'email':
            shareUrl = `mailto:?subject=${encodedTitle}&body=${encodedText}%20${encodedUrl}`;
            break;
        default:
            console.error('Desteklenmeyen platform:', platform);
            return;
    }
    
    // Paylaşım penceresini aç
    window.open(shareUrl, '_blank', 'width=600,height=400,resizable=yes,scrollbars=yes');
}

/**
 * Metni panoya kopyalar
 * 
 * @param {string} text - Kopyalanacak metin
 * @returns {boolean} - Kopyalama başarılı oldu mu
 */
function copyToClipboard(text) {
    // Modern tarayıcılar için navigator.clipboard API'sini kullan
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => {
                console.log('Metin panoya kopyalandı:', text);
                return true;
            })
            .catch(err => {
                console.error('Kopyalama hatası:', err);
                return fallbackCopyToClipboard(text);
            });
    } else {
        // Eski tarayıcılar için alternatif yöntem
        return fallbackCopyToClipboard(text);
    }
}

/**
 * Alternatif kopyalama yöntemi (eski tarayıcılar için)
 * 
 * @param {string} text - Kopyalanacak metin
 * @returns {boolean} - Kopyalama başarılı oldu mu
 */
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    
    // Kaydırma çubuklarını gizle
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.width = '2em';
    textArea.style.height = '2em';
    textArea.style.padding = '0';
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';
    textArea.style.background = 'transparent';
    
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        const success = document.execCommand('copy');
        document.body.removeChild(textArea);
        return success;
    } catch (err) {
        console.error('Kopyalama hatası:', err);
        document.body.removeChild(textArea);
        return false;
    }
}

/**
 * QR kod oluşturur
 * 
 * @param {string} url - QR koduna dönüştürülecek URL
 * @param {HTMLElement} container - QR kodun ekleneceği konteyner
 */
function generateQRCode(url, container) {
    if (!container) return;
    
    // QR kod oluşturmak için Google Charts API kullan
    const qrSize = container.getAttribute('data-size') || 200;
    const qrImageUrl = `https://chart.googleapis.com/chart?cht=qr&chl=${encodeURIComponent(url)}&chs=${qrSize}x${qrSize}&choe=UTF-8&chld=L|0`;
    
    // Önceki QR kodunu temizle
    container.innerHTML = '';
    
    // QR kod görseli oluştur
    const img = document.createElement('img');
    img.src = qrImageUrl;
    img.alt = 'QR Kod';
    img.className = 'qr-code-image';
    
    // Konteynere ekle
    container.appendChild(img);
    
    // İndirme bağlantısı
    const downloadContainer = document.createElement('div');
    downloadContainer.className = 'qr-code-download mt-2 text-center';
    downloadContainer.innerHTML = `
        <a href="${qrImageUrl}" download="qr-code.png" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-download me-1"></i> QR Kodu İndir
        </a>
    `;
    container.appendChild(downloadContainer);
}

/**
 * Paylaşım URL'si oluştur
 * 
 * @param {string} baseUrl - Ana URL (örn: https://xfolio.xren.com.tr/)
 * @param {string} path - URL yolu (örn: profile.php)
 * @param {Object} params - URL parametreleri
 * @returns {string} - Oluşturulan tam URL
 */
function buildShareUrl(baseUrl, path, params) {
    const url = new URL(path, baseUrl);
    
    if (params) {
        Object.keys(params).forEach(key => {
            url.searchParams.append(key, params[key]);
        });
    }
    
    return url.toString();
}

/**
 * Embed kodu oluşturur
 * 
 * @param {string} url - Gömülecek URL
 * @param {number} width - Genişlik (piksel)
 * @param {number} height - Yükseklik (piksel)
 * @returns {string} - HTML embed kodu
 */
function generateEmbedCode(url, width = 600, height = 400) {
    return `<iframe src="${url}" width="${width}" height="${height}" frameborder="0" scrolling="no"></iframe>`;
}

/**
 * Bildirim balonu gösterir
 * @param {string} type - Bildirim türü (success, error, warning, info)
 * @param {string} title - Bildirim başlığı
 * @param {string} message - Bildirim mesajı
 */
function showToast(type, title, message) {
    // Toast container kontrolü
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = 11;
        document.body.appendChild(container);
    }
    
    // Toast ID'si
    const toastId = 'toast-' + Date.now();
    
    // Toast tipi sınıfı
    let colorClass = 'bg-primary text-white';
    let iconClass = 'info-circle';
    
    switch (type) {
        case 'success':
            colorClass = 'bg-success text-white';
            iconClass = 'check-circle';
            break;
        case 'error':
            colorClass = 'bg-danger text-white';
            iconClass = 'exclamation-circle';
            break;
        case 'warning':
            colorClass = 'bg-warning';
            iconClass = 'exclamation-triangle';
            break;
    }
    
    // Toast HTML
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${colorClass}">
                <i class="fas fa-${iconClass} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <small>Şimdi</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    // Toast'u container'a ekle
    document.getElementById('toast-container').insertAdjacentHTML('beforeend', toastHtml);
    
    // Toast'u göster
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        delay: 5000
    });
    toast.show();
    
    // Toast kapandığında DOM'dan kaldır
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}
