/**
 * Xfolio - İstatistik Grafikleri ve Analizleri
 * Chart.js kütüphanesini kullanarak sosyal medya metrikleri için grafikler oluşturur
 */

document.addEventListener('DOMContentLoaded', function() {
    // Chart.js kütüphanesinin yüklü olup olmadığını kontrol et
    if (typeof Chart === 'undefined') {
        console.error('Chart.js kütüphanesi bulunamadı. Grafikler gösterilemiyor.');
        return;
    }
    
    // Tema renklerini al
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#5e72e4';
    const secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim() || '#f7fafc';
    const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim() || '#2d3748';
    
    // Takipçi sayısı zaman grafiği
    const followerHistoryCanvas = document.getElementById('followerHistoryChart');
    if (followerHistoryCanvas) {
        // Veri noktalarını al
        const dataPoints = JSON.parse(followerHistoryCanvas.getAttribute('data-stats') || '[]');
        
        if (dataPoints.length > 0) {
            const labels = dataPoints.map(point => point.date);
            const data = dataPoints.map(point => point.followers);
            
            new Chart(followerHistoryCanvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Takipçi Sayısı',
                        data: data,
                        backgroundColor: 'rgba(94, 114, 228, 0.2)',
                        borderColor: primaryColor,
                        borderWidth: 2,
                        pointBackgroundColor: primaryColor,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: textColor
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 10,
                            displayColors: false
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        } else {
            // Veri yoksa bilgi mesajı göster
            const parentElement = followerHistoryCanvas.parentElement;
            const errorMsg = document.createElement('div');
            errorMsg.className = 'text-center text-muted py-4';
            errorMsg.innerHTML = '<i class="fas fa-chart-line fa-3x mb-3"></i><p>Henüz yeterli veri bulunmuyor.</p>';
            
            parentElement.innerHTML = '';
            parentElement.appendChild(errorMsg);
        }
    }
    
    // Platform bazında takipçi dağılımı pasta grafiği
    const platformDistributionCanvas = document.getElementById('platformDistributionChart');
    if (platformDistributionCanvas) {
        // Platform verilerini al
        const platformData = JSON.parse(platformDistributionCanvas.getAttribute('data-stats') || '[]');
        
        if (platformData.length > 0) {
            const labels = platformData.map(item => item.platform);
            const data = platformData.map(item => item.followers);
            
            // Platform renklerini belirle
            const backgroundColors = platformData.map(item => {
                switch (item.platform.toLowerCase()) {
                    case 'youtube': return '#FF0000';
                    case 'instagram': return '#C13584';
                    case 'twitter': return '#1DA1F2';
                    case 'facebook': return '#4267B2';
                    case 'tiktok': return '#000000';
                    case 'twitch': return '#6441a5';
                    case 'linkedin': return '#0077B5';
                    case 'website': return '#4CAF50';
                    default: return '#777777';
                }
            });
            
            new Chart(platformDistributionCanvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: textColor
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${context.label}: ${Number(value).toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        } else {
            // Veri yoksa bilgi mesajı göster
            const parentElement = platformDistributionCanvas.parentElement;
            const errorMsg = document.createElement('div');
            errorMsg.className = 'text-center text-muted py-4';
            errorMsg.innerHTML = '<i class="fas fa-chart-pie fa-3x mb-3"></i><p>Platform verisi bulunmuyor.</p>';
            
            parentElement.innerHTML = '';
            parentElement.appendChild(errorMsg);
        }
    }
    
    // Haftalık büyüme oranı çubuk grafiği
    const growthRateCanvas = document.getElementById('growthRateChart');
    if (growthRateCanvas) {
        // Büyüme verilerini al
        const growthData = JSON.parse(growthRateCanvas.getAttribute('data-stats') || '[]');
        
        if (growthData.length > 0) {
            const labels = growthData.map(item => item.platform);
            const data = growthData.map(item => item.growth_rate);
            
            const barColors = data.map(value => value >= 0 ? 'rgba(40, 167, 69, 0.7)' : 'rgba(220, 53, 69, 0.7)');
            const borderColors = data.map(value => value >= 0 ? '#28a745' : '#dc3545');
            
            new Chart(growthRateCanvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Haftalık Büyüme Oranı (%)',
                        data: data,
                        backgroundColor: barColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: textColor,
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: textColor
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    return `Büyüme: ${value}%`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Veri yoksa bilgi mesajı göster
            const parentElement = growthRateCanvas.parentElement;
            const errorMsg = document.createElement('div');
            errorMsg.className = 'text-center text-muted py-4';
            errorMsg.innerHTML = '<i class="fas fa-chart-bar fa-3x mb-3"></i><p>Büyüme verisi bulunmuyor.</p>';
            
            parentElement.innerHTML = '';
            parentElement.appendChild(errorMsg);
        }
    }
    
    // Karşılaştırma grafiği
    const comparisonCanvas = document.getElementById('comparisonChart');
    if (comparisonCanvas) {
        // Karşılaştırma verilerini al
        const comparisonData = JSON.parse(comparisonCanvas.getAttribute('data-stats') || '[]');
        
        if (comparisonData.length > 0 && comparisonData.user && comparisonData.average) {
            const user = comparisonData.user;
            const average = comparisonData.average;
            const labels = Object.keys(user);
            
            const userData = labels.map(key => user[key]);
            const averageData = labels.map(key => average[key]);
            
            new Chart(comparisonCanvas, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Sizin Puanınız',
                            data: userData,
                            backgroundColor: 'rgba(94, 114, 228, 0.3)',
                            borderColor: primaryColor,
                            borderWidth: 2,
                            pointBackgroundColor: primaryColor,
                            pointBorderColor: '#fff',
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Platform Ortalaması',
                            data: averageData,
                            backgroundColor: 'rgba(173, 181, 189, 0.3)',
                            borderColor: '#adb5bd',
                            borderWidth: 2,
                            pointBackgroundColor: '#adb5bd',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                display: false
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            pointLabels: {
                                color: textColor,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: textColor
                            }
                        }
                    }
                }
            });
        } else {
            // Veri yoksa bilgi mesajı göster
            const parentElement = comparisonCanvas.parentElement;
            const errorMsg = document.createElement('div');
            errorMsg.className = 'text-center text-muted py-4';
            errorMsg.innerHTML = '<i class="fas fa-chart-radar fa-3x mb-3"></i><p>Karşılaştırma verisi bulunmuyor.</p>';
            
            parentElement.innerHTML = '';
            parentElement.appendChild(errorMsg);
        }
    }
});

/**
 * İstatistik verileri oluşturur (test amaçlı)
 * 
 * @param {string} platform - Platform adı
 * @param {number} days - Günler
 * @returns {Object[]} - İstatistik verileri
 */
function generateTestData(platform, days = 30) {
    const data = [];
    const today = new Date();
    let followers = Math.floor(Math.random() * 5000) + 1000; // Başlangıç takipçi sayısı
    
    for (let i = days - 1; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        
        // Rastgele değişim (-%1 ile %3 arası)
        const change = followers * (Math.random() * 0.04 - 0.01);
        followers += Math.floor(change);
        
        data.push({
            date: date.toISOString().split('T')[0],
            followers: followers,
            platform: platform
        });
    }
    
    return data;
}
