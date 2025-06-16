<?php
session_start();
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="indexStyle.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="logo text-center">
                <img src="assets/logo.png" alt="Logo TokoPojok" />
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Owner') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'InventoryControl') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } ?>
        </aside>

        <!-- Main Content -->
        <main class="main-content">

            <div class="header-top">
                <div>
                    <h1 class="header-title">Hello Luthfan 👋</h1>
                    <div class="header-subtitle">Selamat Datang</div>
                </div>
                <div class="search-profile">
                    <input type="search" placeholder="Cari produk..." aria-label="Search products" />
                    <div class="profile-dropdown dropdown">
                        <div
                            class="profile-icon rounded-circle shadow-sm"
                            id="profileDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            role="button"
                            tabindex="0"
                            title="Profil"
                        >
                            <i class="fas fa-user"></i>
                        </div>
                        <ul
                            class="dropdown-menu dropdown-menu-end mt-2 rounded-3"
                            aria-labelledby="profileDropdown"
                        >
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                                    <i class="fas fa-id-card text-success"></i> Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Improved Summary Cards -->
            <section class="summary">
                <div class="summary-card">
                    <div class="summary-icon sales">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Rp 765,000</h3>
                        <p>Total Penjualan Hari Ini</p>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Rp 229,500,000</h3>
                        <p>Total Penjualan Bulan Ini</p>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon transactions">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="summary-content">
                        <h3>153</h3>
                        <p>Transaksi Hari Ini</p>
                    </div>
                </div>
            </section>

            <!-- Improved Charts and Products -->
            <section class="content-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h2>Laporan Penjualan</h2>
                        <select class="chart-filter" id="filterTahun">
                            <option value="2025" selected>2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                    <div class="chart-wrapper" style="height: 300px; position: relative;">
                        <canvas id="penjualanChart"></canvas>
                    </div>
                </div>

                <div class="products-container">
                    <div class="products-header">
                        <h2>Produk Terlaris</h2>
                    </div>
                    <div class="products-list">
                        <div class="product-item">
                            <span class="product-rank">1</span>
                            <span class="product-name">Bonanza F1</span>
                            <span class="product-sales">320 unit</span>
                        </div>
                        <div class="product-item">
                            <span class="product-rank">2</span>
                            <span class="product-name">Jagung Ketan F1</span>
                            <span class="product-sales">290 unit</span>
                        </div>
                        <div class="product-item">
                            <span class="product-rank">3</span>
                            <span class="product-name">Mapan P-01</span>
                            <span class="product-sales">270 unit</span>
                        </div>
                        <div class="product-item">
                            <span class="product-rank">4</span>
                            <span class="product-name">Cakrabuana 04</span>
                            <span class="product-sales">250 unit</span>
                        </div>
                        <div class="product-item">
                            <span class="product-rank">5</span>
                            <span class="product-name">Dewata 43 F1</span>
                            <span class="product-sales">230 unit</span>
                        </div>
                        <!-- Produk lainnya dengan struktur yang sama -->
                    </div>
                </div>
            </section>

            <!-- Improved Tables Section -->
            <section class="tables-grid">
                <div class="table-container">
                    <div class="table-header">
                        <h2>Stok Hampir Habis</h2>
                        <select class="table-filter" id="stokFilter">
                            <option value="asc">Ascending</option>
                            <option value="desc">Descending</option>
                        </select>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode Produk</th>
                                <th>Kategori</th>
                                <th>Produk</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>PSAGRO01</td>
                                <td>Pestisida</td>
                                <td>Avidor 25 WP</td>
                                <td>5</td>
                            </tr>
                            <tr>
                                <td>PSAKS001</td>
                                <td>Pestisida</td>
                                <td>FOSTIN 610 EC</td>
                                <td>9</td>
                            </tr>
                            <tr>
                                <td>PNBIO001</td>
                                <td>Pupuk</td>
                                <td>AM-BEST 100 ME</td>
                                <td>12</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2>Kedaluwarsa Dekat</h2>
                        <select class="table-filter" id="expFilter">
                            <option value="7">7 Hari</option>
                            <option value="30">1 Bulan</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode Produk</th>
                                <th>Kategori</th>
                                <th>Produk</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BBTTE001</td>
                                <td>Bibit</td>
                                <td>Tebu Telur</td>
                                <td>17-03-2025</td>
                            </tr>
                            <tr>
                                <td>PLKRM001</td>
                                <td>Perlengkapan</td>
                                <td>Polybag 12 x 17</td>
                                <td>17-03-2025</td>
                            </tr>
                            <tr>
                                <td>PLSWN001</td>
                                <td>Perlengkapan</td>
                                <td>Alat Semprot Hama</td>
                                <td>18-03-2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Enhanced Chart.js with Animation
        // Ganti script chart dengan ini
        const ctx = document.getElementById('penjualanChart').getContext('2d');
        const penjualanChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Penjualan',
                    data: [120, 150, 180, 200, 250, 300, 280, 270, 260, 310, 320, 400],
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20,
                        left: 20,
                        right: 20
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart',
                },
                plugins: {
                    legend: { 
                        display: false 
                    },
                    tooltip: {
                        backgroundColor: '#28a745',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.2)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' unit';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 100,
                            padding: 10,
                            callback: function(value) {
                                return value + ' unit';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                }
            }
        });

        // Enhanced Table Sorting with Animation
        document.getElementById('stokFilter').addEventListener('change', function() {
            const table = document.querySelector('.table-container:first-child table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const order = this.value;
            
            // Add fade out effect
            tbody.style.opacity = '0.5';
            tbody.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                rows.sort((a, b) => {
                    const aStok = parseInt(a.cells[3].textContent);
                    const bStok = parseInt(b.cells[3].textContent);
                    return order === 'asc' ? aStok - bStok : bStok - aStok;
                });
                
                // Rebuild table with sorted rows
                rows.forEach(row => tbody.appendChild(row));
                
                // Fade back in
                tbody.style.opacity = '1';
            }, 300);
        });

        // Enhanced Expiry Table Filter
        document.getElementById('expFilter').addEventListener('change', function() {
            const customDateInput = document.getElementById('expCustomDate');
            if (this.value === 'custom') {
                customDateInput.style.display = 'inline-block';
                customDateInput.focus();
            } else {
                customDateInput.style.display = 'none';
                filterExpTable();
            }
        });

        function filterExpTable() {
            const filter = document.getElementById('expFilter').value;
            const customDate = document.getElementById('expCustomDate').value;
            const table = document.querySelector('.table-container:last-child table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const now = new Date();
            let maxDate;
            
            // Add fade out effect
            tbody.style.opacity = '0.5';
            tbody.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                if (filter === '7') {
                    maxDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 7);
                } else if (filter === '30') {
                    maxDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 30);
                } else if (filter === 'custom' && customDate) {
                    maxDate = new Date(customDate);
                } else {
                    rows.forEach(row => row.style.display = '');
                    tbody.style.opacity = '1';
                    return;
                }
                
                rows.forEach(row => {
                    const dateParts = row.cells[3].textContent.split('-');
                    const tglDate = new Date(`${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`);
                    row.style.display = tglDate <= maxDate ? '' : 'none';
                });
                
                // Fade back in
                tbody.style.opacity = '1';
            }, 300);
        }

        // Update chart when year filter changes
        document.getElementById('filterTahun').addEventListener('change', function() {
            // Simulate data change based on year
            const yearData = {
                '2025': [120, 150, 180, 200, 250, 300, 280, 270, 260, 310, 320, 400],
                '2024': [100, 130, 160, 180, 220, 280, 260, 250, 240, 290, 300, 380],
                '2023': [80, 110, 140, 160, 200, 250, 230, 220, 210, 260, 270, 350]
            };
            
            // Animate chart update
            penjualanChart.data.datasets[0].data = yearData[this.value];
            penjualanChart.update();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>