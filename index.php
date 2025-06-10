<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> -->

    <!-- Custom CSS -->
    <link rel="stylesheet" href="indexStyle.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/logo.png" alt="Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="#"><i class="fas fa-shopping-cart"></i> Purchase</a></li>
                    <li><a href="#"><i class="fas fa-cash-register"></i> Chasier</a></li>
                    <li><a href="#"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="#"><i class="fas fa-bell"></i> Notifikasi</a></li>
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
                        <img src="https://storage.googleapis.com/a1aa/image/zo6PP4KS4jRPqjjFfiTVERFXjtMKO3D52JBI0TsGCII.jpg" alt="User Avatar">
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
            <!-- <h3>Ringkasan Penjualan</h3> -->
            <section class="summary">
                <div class="card">
                    <div class="icon-container icon-money">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-content">
                        <p>Rp 765,000</p>
                        <p>Total Penjualan Hari Ini</p>
                    </div>
                </div>
                <div class="card">
                    <div class="icon-container icon-chart">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-content">
                        <p>Rp 229,500,000</p>
                        <p>Total Penjualan Bulan Ini</p>
                    </div>
                </div>
                <div class="card">
                    <div class="icon-container icon-receipt">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="card-content">
                        <p>153</p>
                        <p>Transaksi Hari Ini</p>
                    </div>
                </div>
            </section>

            <!-- Charts and Products -->
            <section class="content">
                <div class="chart-section">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2>Laporan Penjualan</h2>
                            <select id="filterTahun" onchange="updateChart()">
                                <option value="2025" selected>2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                        <canvas id="penjualanChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="top-products">
                    <h2>Produk Terlaris</h2>
                    <div class="top-products-cards">
                        <div class="top-product-card"><span class="rank">1.</span> Bonanza F1</div>
                        <div class="top-product-card"><span class="rank">2.</span> Jagung Ketan F1</div>
                        <div class="top-product-card"><span class="rank">3.</span> Mapan P-01</div>
                        <div class="top-product-card"><span class="rank">4.</span> Cakrabuana 04</div>
                        <div class="top-product-card"><span class="rank">5.</span> Dewata 43 F1</div>
                    </div>
                </div>
            </section>

            <!-- Tables -->
            <section class="tables">
                <div class="table-card">
                    <h2>Stok Hampir Habis</h2>
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
                <div class="table-card">
                    <h2>Kedaluwarsa Dekat <span style="color: #4a7c59;">7 Hari</span></h2>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('penjualanChart').getContext('2d');
        const penjualanChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Penjualan',
                    data: [120, 150, 180, 200, 250, 300, 280, 270, 260, 310, 320, 400], // ← Ubah sesuai data real
                    backgroundColor: '#4CAF50',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Unit Terjual'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });
    </script>


</body>
</html>