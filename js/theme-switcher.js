/**
 * Theme Switcher - Xfolio için tema değiştirme fonksiyonları
 */
document.addEventListener('DOMContentLoaded', function() {
    // Tema seçim düğmesini oluştur
    createThemeSwitcher();
    
    // Kullanıcının tema tercihini local storage'dan al
    const currentTheme = localStorage.getItem('theme') || 'default';
    
    // Temayı uygula
    applyTheme(currentTheme);
    
    // Tema değiştirme olaylarını dinle
    document.querySelectorAll('.theme-option').forEach(option => {
        option.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');
            applyTheme(theme);
            localStorage.setItem('theme', theme);
        });
    });
});

/**
 * Tema seçim düğmesini oluşturur
 */
function createThemeSwitcher() {
    const themes = [
        { id: 'default', icon: 'fa-circle', color: '#5e72e4', name: 'Varsayılan' },
        { id: 'dark', icon: 'fa-moon', color: '#1a202c', name: 'Koyu' },
        { id: 'green', icon: 'fa-leaf', color: '#38b2ac', name: 'Yeşil' },
        { id: 'orange', icon: 'fa-fire', color: '#ed8936', name: 'Turuncu' },
        { id: 'purple', icon: 'fa-gem', color: '#9f7aea', name: 'Mor' }
    ];
    
    // Ana düğme
    const switcher = document.createElement('div');
    switcher.className = 'theme-switcher';
    switcher.innerHTML = '<i class="fas fa-palette"></i>';
    
    // Tema seçenekleri menüsü
    const menu = document.createElement('div');
    menu.className = 'theme-menu';
    menu.style.display = 'none';
    menu.style.position = 'absolute';
    menu.style.bottom = '60px';
    menu.style.right = '0';
    menu.style.backgroundColor = 'var(--card-bg)';
    menu.style.border = '1px solid var(--border-color)';
    menu.style.borderRadius = '10px';
    menu.style.padding = '10px';
    menu.style.boxShadow = '0 4px 6px var(--shadow-color)';
    menu.style.width = '200px';
    
    // Tema seçeneklerini oluştur
    themes.forEach(theme => {
        const option = document.createElement('div');
        option.className = 'theme-option d-flex align-items-center p-2 mb-1';
        option.setAttribute('data-theme', theme.id);
        option.style.cursor = 'pointer';
        option.style.borderRadius = '5px';
        option.style.transition = 'all 0.3s ease';
        option.innerHTML = `
            <div class="theme-icon me-2" style="width: 24px; height: 24px; border-radius: 50%; background-color: ${theme.color}; display: flex; align-items: center; justify-content: center;">
                <i class="fas ${theme.icon}" style="color: white; font-size: 12px;"></i>
            </div>
            <div>${theme.name}</div>
        `;
        
        option.addEventListener('mouseover', function() {
            this.style.backgroundColor = 'var(--hover-color)';
        });
        
        option.addEventListener('mouseout', function() {
            this.style.backgroundColor = '';
        });
        
        menu.appendChild(option);
    });
    
    // Ana düğmeye menüyü ekle
    switcher.appendChild(menu);
    
    // Ana düğme tıklama olayını ekle
    switcher.addEventListener('click', function(e) {
        if (e.target === this || e.target.closest('i') === this.querySelector('i')) {
            const isVisible = menu.style.display === 'block';
            menu.style.display = isVisible ? 'none' : 'block';
        }
    });
    
    // Belgeye tıklandığında menüyü kapat
    document.addEventListener('click', function(e) {
        if (!switcher.contains(e.target)) {
            menu.style.display = 'none';
        }
    });
    
    // Body'ye ekle
    document.body.appendChild(switcher);
}

/**
 * Belirtilen temayı uygular
 * @param {string} theme - Tema ID
 */
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    
    // Tema değiştirildiğinde butonları güncelle
    document.querySelectorAll('.theme-option').forEach(option => {
        if (option.getAttribute('data-theme') === theme) {
            option.style.backgroundColor = 'var(--hover-color)';
        } else {
            option.style.backgroundColor = '';
        }
    });
}
