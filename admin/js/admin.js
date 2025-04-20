// Admin Panel JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    // Tooltips için
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Tablolarda arama işlevi
    document.querySelectorAll('.search-filter').forEach(searchInput => {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableId = this.getAttribute('data-table');
            const tableRows = document.querySelectorAll(`#${tableId} tbody tr`);
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if(text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Dashboard için grafik (eğer dashboard sayfasındaysak)
    const userChartElement = document.getElementById('userRegistrationChart');
    if (userChartElement) {
        const userChart = new Chart(userChartElement, {
            type: 'line',
            data: {
                labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz'],
                datasets: [{
                    label: 'Kayıt Olan Kullanıcılar',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    fill: false,
                    borderColor: 'rgb(94, 114, 228)',
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    // Platformlar için pasta grafik (eğer dashboard sayfasındaysak)
    const platformChartElement = document.getElementById('platformDistributionChart');
    if (platformChartElement) {
        const platformChart = new Chart(platformChartElement, {
            type: 'doughnut',
            data: {
                labels: ['YouTube', 'Instagram', 'Twitter', 'TikTok', 'Diğer'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
    
    // Kullanıcı detayları modalı
    const userDetailModal = document.getElementById('userDetailModal');
    if (userDetailModal) {
        userDetailModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            
            // AJAX ile kullanıcı detaylarını getir
            fetch('get_user_details.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalUserName').textContent = data.username;
                    document.getElementById('modalUserEmail').textContent = data.email;
                    document.getElementById('modalUserPhone').textContent = data.phone;
                    document.getElementById('modalUserRegistered').textContent = data.created_at;
                    
                    if (data.avatar) {
                        document.getElementById('modalUserAvatar').src = '../uploads/avatars/' + data.avatar;
                    }
                })
                .catch(error => console.error('Kullanıcı detayları alınırken hata oluştu:', error));
        });
    }
    
    // Onay işlemleri için (silme, onaylama vb.)
    document.querySelectorAll('.confirm-action').forEach(btn => {
        btn.addEventListener('click', function(event) {
            if (!confirm(this.getAttribute('data-confirm-message'))) {
                event.preventDefault();
            }
        });
    });
});
