<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="indexStyle.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar col-2">
            <div class="logo">
                <img src="assets/logo.png" alt="logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="#"><i class="fas fa-shopping-cart"></i> Purchase</a></li>
                    <li><a href="#"><i class="fas fa-cash-register"></i> Chasier</a></li>
                    <li><a href="#"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="#"><i class="fas fa-bell"></i> Notifikasi</a></li>
                </ul>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="main-content col-10">
            <div class="header">
                <div>
                    <h1>Hello Luthfan 👋</h1>
                    <p>Selamat Datang</p>
                </div>
                <div class="search-bar">
                    <input type="text" placeholder="Search">
                    <div class="user-info">
                        <img src="https://storage.googleapis.com/a1aa/image/zo6PP4KS4jRPqjjFfiTVERFXjtMKO3D52JBI0TsGCII.jpg" alt="User Avatar">
                        <div>
                            <p>Luthfan Kafi</p>
                            <p>Owner</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="summary">
                <div class="card">
                    <p>Rp 765,000</p>
                    <p>Total Penjualan Hari Ini</p>
                </div>
                <div class="card">
                    <p>Rp 229,500,000</p>
                    <p>Total Penjualan Bulan Ini</p>
                </div>
                <div class="card">
                    <p>153</p>
                    <p>Transaksi Hari Ini</p>
                </div>
            </div>
            <div class="content">
                <div class="chart">
                    <h2>Laporan Stok</h2>
                </div>
                <div class="top-products">
                    <h2>Produk Terlaris</h2>
                    <ul>
                        <li>Bonanza F1</li>
                        <li>Jagung Ketan F1</li>
                        <li>Mapan P-01</li>
                        <li>Cakrabuana 04</li>
                        <li>Dewata 43 F1</li>
                        <li>Shypoon 2</li>
                    </ul>
                </div>
            </div>
            <div class="tables">
                <div class="table-card">
                    <h2>Stok Hampir Habis</h2>
                    <table>
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
                    <table>
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
            </div>
        </div>
    </div>
</body>
</html>