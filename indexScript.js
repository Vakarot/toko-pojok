const ctx = document.getElementById('stockChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [
            {
                label: 'Stok Masuk',
                data: [5000, 6000, 7500, 9000, 5000, 4500, 7000, 8500, 6500, 6000, 7000, 8000],
                backgroundColor: '#4caf50'
            },
            {
                label: 'Stok Keluar',
                data: [3000, 4000, 5000, 6500, 3500, 3000, 5000, 6000, 4500, 4000, 5000, 6000],
                backgroundColor: '#03a9f4'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
