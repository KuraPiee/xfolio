/**
 * Xfolio İstatistik Güncelleme
 * Bu dosya sosyal medya istatistiklerini API üzerinden günceller
 */

document.addEventListener('DOMContentLoaded', function() {
    // İstatistik güncelleme butonlarını bul ve eventleri ekle
    const updateButtons = document.querySelectorAll('.update-stats-btn');
    
    updateButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const portfolioId = this.getAttribute('data-id');
            const statsContainer = this.closest('.portfolio-card').querySelector('.stats-container');
            const oldFollowers = statsContainer ? statsContainer.getAttribute('data-followers') : 0;
            
            // Butonun yükleniyor animasyonunu göster
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            // API isteği gönder
            updatePortfolioStats(portfolioId)
                .then(response => {
                    if (response.success) {
                        // İstatistiği güncelle
                        if (statsContainer) {
                            // Takipçi sayısı
                            const followerElement = statsContainer.querySelector('.follower-count');
                            if (followerElement) {
                                followerElement.textContent = response.data.followers;
                            }
                            
                            // Değişim göstergesi
                            const changeElement = statsContainer.querySelector('.follower-change');
                            if (changeElement) {
                                changeElement.textContent = response.data.change;
                                
                                // Artış/azalış durumuna göre renk değiştir
                                if (response.data.change.startsWith('+')) {
                                    changeElement.classList.remove('text-danger');
                                    changeElement.classList.add('text-success');
                                } else if (response.data.change.startsWith('-')) {
                                    changeElement.classList.remove('text-success');
                                    changeElement.classList.add('text-danger');
                                }
                            }
                            
                            // Son güncelleme bilgisi
                            const lastUpdateElement = statsContainer.querySelector('.last-update-time');
                            if (lastUpdateElement) {
                                lastUpdateElement.textContent = formatDate(response.data.last_updated);
                            }
                            
                            // Veriler güncellendiğinde bir animation göster
                            statsContainer.classList.add('stats-updated');
                            setTimeout(() => {
                                statsContainer.classList.remove('stats-updated');
                            }, 2000);
                        }
                        
                        // Başarı mesajı göster
                        showToast('success', 'İstatistikler güncellendi!', `Son takipçi sayısı: ${response.data.followers}`);
                    } else {
                        // Hata mesajı göster
                        showToast('error', 'İstatistikler güncellenemedi', response.message);
                    }
                })
                .catch(error => {
                    console.error('İstatistik güncelleme hatası:', error);
                    showToast('error', 'İstatistikler güncellenemedi', 'Bir hata oluştu, lütfen tekrar deneyin.');
                })
                .finally(() => {
                    // Butonun durumunu normale döndür
                    this.innerHTML = '<i class="fas fa-sync-alt"></i>';
                    this.disabled = false;
                });
        });
    });
    
    // Tüm istatistikleri güncelleme butonu
    const updateAllButton = document.getElementById('update-all-stats');
    if (updateAllButton) {
        updateAllButton.addEventListener('click', function() {
            // Butonun yükleniyor animasyonunu göster
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Güncelleniyor...';
            this.disabled = true;
            
            // API isteği gönder
            updateAllPortfolioStats()
                .then(response => {
                    if (response.success) {
                        // Başarı mesajı göster
                        showToast('success', 'Tüm istatistikler güncellendi!', 
                            `${response.data.updated} link güncellendi, ${response.data.failed} link güncellenemedi. Toplam değişim: ${response.data.total_change}`);
                        
                        // Sayfayı yenile
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    } else {
                        // Hata mesajı göster
                        showToast('error', 'İstatistikler güncellenemedi', response.message);
                    }
                })
                .catch(error => {
                    console.error('İstatistik güncelleme hatası:', error);
                    showToast('error', 'İstatistikler güncellenemedi', 'Bir hata oluştu, lütfen tekrar deneyin.');
                })
                .finally(() => {
                    // Butonun durumunu normale döndür
                    this.innerHTML = '<i class="fas fa-sync-alt"></i> Tüm İstatistikleri Güncelle';
                    this.disabled = false;
                });
        });
    }
});

/**
 * Belirli bir portfolyo linkinin istatistiklerini günceller
 * @param {number} portfolioId - Portfolyo ID
 * @returns {Promise} API yanıtı
 */
function updatePortfolioStats(portfolioId) {
    const formData = new FormData();
    formData.append('portfolio_id', portfolioId);
    
    return fetch('/api/update_stats.php?action=update_stats', {
        method: 'POST',
        body: formData
    }).then(response => response.json());
}

/**
 * Kullanıcının tüm portfolyo linklerinin istatistiklerini günceller
 * @returns {Promise} API yanıtı
 */
function updateAllPortfolioStats() {
    return fetch('/api/update_stats.php?action=update_all', {
        method: 'POST'
    }).then(response => response.json());
}

/**
 * Tarih formatını daha güzel hale getirir
 * @param {string} dateString - Tarih metni (YYYY-MM-DD HH:MM:SS)
 * @returns {string} Formatlanmış tarih
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    
    // Geçerliliği kontrol et
    if (isNaN(date.getTime())) {
        return "Bilinmiyor";
    }
    
    const now = new Date();
    const diffMs = now - date;
    const diffSec = Math.floor(diffMs / 1000);
    
    if (diffSec < 60) {
        return "Az önce";
    }
    
    const diffMin = Math.floor(diffSec / 60);
    if (diffMin < 60) {
        return `${diffMin} dakika önce`;
    }
    
    const diffHour = Math.floor(diffMin / 60);
    if (diffHour < 24) {
        return `${diffHour} saat önce`;
    }
    
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    
    return `${day}.${month}.${year} ${hours}:${minutes}`;
}

/**
 * Bildirim balonu gösterir
 * @param {string} type - Bildirim türü (success, error, warning, info)
 * @param {string} title - Bildirim başlığı
 * @param {string} message - Bildirim mesajı
 */
function showToast(type, title, message) {
    // Bootstrap toast oluştur
    const toastContainer = document.getElementById('toast-container');
    
    // Eğer container yoksa oluştur
    if (!toastContainer) {
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
