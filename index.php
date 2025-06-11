<?php
/*session_start();

// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="indexStyle.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo text-center">
                <img src="assets/logo.png" alt="Logo TokoPojok" />
            </div>
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
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div>
                    <h1>Hello Luthfan 👋</h1>
                    <p>Selamat Datang</p>
                </div>
                <div class="search-bar">
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search">
                    </div>
                    <div class="user-info dropdown">
                        <div class="profile-box">
                            <img src="https://storage.googleapis.com/a1aa/image/zo6PP4KS4jRPqjjFfiTVERFXjtMKO3D52JBI0TsGCII.jpg" alt="User Avatar">
                        </div>
                        <div>
                            <p>Luthfan Kafi</p>
                            <p>Owner</p>
                        </div>
                        <div class="dropdown-menu">
                            <a href="#">Profil</a>
                            <a href="#">Logout</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <section class="summary">
                <div class="card">
                    <div class="icon-container icon-money">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-content">
                        <p>Rp 765,000</p>
                        <span>Total Penjualan Hari Ini</span>
                    </div>
                </div>
                <div class="card">
                    <div class="icon-container icon-chart">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-content">
                        <p>Rp 229,500,000</p>
                        <span>Total Penjualan Bulan Ini</span>
                    </div>
                </div>
                <div class="card">
                    <div class="icon-container icon-receipt">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="card-content">
                        <p>153</p>
                        <span>Transaksi Hari Ini</span>
                    </div>
                </div>
            </section>

            <!-- Charts and Products -->
            <section class="content">
                <div class="chart-section">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2>Laporan Penjualan</h2>
                            <select id="filterTahun" class="filter-tahun" onchange="updateChart()">
                                <option value="2025" selected>2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                        <canvas id="penjualanChart"></canvas>
                    </div>
                </div>
                <div class="top-products outer-card">
                    <h2>Produk Terlaris</h2>
                    <div class="top-products-cards">
                        <div class="top-product-card">
                            <div class="product-rank">1</div>
                            <div class="product-info">
                                <div class="product-name">Bonanza F1</div>
                                <div class="product-sales">320 unit</div>
                            </div>
                        </div>
                        <div class="top-product-card">
                            <div class="product-rank">2</div>
                            <div class="product-info">
                                <div class="product-name">Jagung Ketan F1</div>
                                <div class="product-sales">290 unit</div>
                            </div>
                        </div>
                        <div class="top-product-card">
                            <div class="product-rank">3</div>
                            <div class="product-info">
                                <div class="product-name">Mapan P-01</div>
                                <div class="product-sales">270 unit</div>
                            </div>
                        </div>
                        <div class="top-product-card">
                            <div class="product-rank">4</div>
                            <div class="product-info">
                                <div class="product-name">Cakrabuana 04</div>
                                <div class="product-sales">250 unit</div>
                            </div>
                        </div>
                        <div class="top-product-card">
                            <div class="product-rank">5</div>
                            <div class="product-info">
                                <div class="product-name">Dewata 43 F1</div>
                                <div class="product-sales">230 unit</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tables -->
            <section class="tables">
                <div class="table-card">
                    <div class="table-card-header">
                        <h2>Stok Hampir Habis</h2>
                        <select class="table-filter" id="stokFilter" onchange="sortStokTable()">
                            <option value="asc">Stok Ascending</option>
                            <option value="desc">Stok Descending</option>
                        </select>
                    </div>
                    <table class="table" id="stokTable">
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
                <div class="table-card">
                    <div class="table-card-header">
                        <h2>Kedaluwarsa Dekat <span style="color: #4a7c59;">7 Hari</span></h2>
                        <select class="table-filter" id="expFilter" onchange="filterExpTable()">
                            <option value="7">7 Hari</option>
                            <option value="30">1 Bulan</option>
                            <option value="custom">Custom</option>
                        </select>
                        <input type="date" id="expCustomDate" style="display:none; margin-left:8px;" onchange="filterExpTable()" />
                    </div>
                    <table class="table" id="expTable">
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
        // Chart.js
        const ctx = document.getElementById('penjualanChart').getContext('2d');
        const penjualanChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Penjualan',
                    data: [120, 150, 180, 200, 250, 300, 280, 270, 260, 310, 320, 400],
                    backgroundColor: [
                        'rgba(76, 175, 80, 0.7)',
                    ],
                    borderRadius: 16,
                    borderSkipped: false,
                    maxBarThickness: 38
                }]
            },
            options: {
                responsive: true,
                aspectRatio: 2.2,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#4caf50',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#e6f4ea',
                        borderWidth: 1,
                        padding: 12,
                        caretSize: 8,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Unit Terjual' },
                        grid: { color: '#e6f4ea' },
                        ticks: { stepSize: 50 }
                    },
                    x: {
                        title: { display: true, text: 'Bulan' },
                        grid: { display: false }
                    }
                }
            }
        });

        // Stok Table Sort
        function sortStokTable() {
            const table = document.getElementById('stokTable').getElementsByTagName('tbody')[0];
            const rows = Array.from(table.rows);
            const order = document.getElementById('stokFilter').value;
            rows.sort((a, b) => {
                const stokA = parseInt(a.cells[3].innerText);
                const stokB = parseInt(b.cells[3].innerText);
                return order === 'asc' ? stokA - stokB : stokB - stokA;
            });
            rows.forEach(row => table.appendChild(row));
        }

        // Exp Table Filter
        document.getElementById('expFilter').addEventListener('change', function() {
            if (this.value === 'custom') {
                document.getElementById('expCustomDate').style.display = 'inline-block';
            } else {
                document.getElementById('expCustomDate').style.display = 'none';
                filterExpTable();
            }
        });

        function filterExpTable() {
            const filter = document.getElementById('expFilter').value;
            const customDate = document.getElementById('expCustomDate').value;
            const table = document.getElementById('expTable').getElementsByTagName('tbody')[0];
            const rows = Array.from(table.rows);
            const now = new Date();
            let maxDate;
            if (filter === '7') {
                maxDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 7);
            } else if (filter === '30') {
                maxDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 30);
            } else if (filter === 'custom' && customDate) {
                maxDate = new Date(customDate.split('-').reverse().join('-'));
            } else {
                rows.forEach(row => row.style.display = '');
                return;
            }
            rows.forEach(row => {
                const tgl = row.cells[3].innerText.split('-').reverse().join('-');
                const tglDate = new Date(tgl);
                row.style.display = tglDate <= maxDate ? '' : 'none';
            });
        }
    </script>
</body>
</html>