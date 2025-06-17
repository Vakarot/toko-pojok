<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['id_pengguna'])) {
    $userQuery = "SELECT nama FROM pengguna WHERE id_pengguna = ?";
    $stmt = $connect->prepare($userQuery);
    $stmt->bind_param("s", $_SESSION['id_pengguna']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userName = $row['nama'];
    }
}

// Get today's sales total
$todaySalesQuery = "SELECT COALESCE(SUM(total_harga), 0) as total_today FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE()";
$todaySalesResult = $connect->query($todaySalesQuery);
$todaySales = $todaySalesResult->fetch_assoc()['total_today'];

// Get this month's sales total
$monthSalesQuery = "SELECT COALESCE(SUM(total_harga), 0) as total_month FROM penjualan WHERE MONTH(tanggal_penjualan) = MONTH(CURDATE()) AND YEAR(tanggal_penjualan) = YEAR(CURDATE())";
$monthSalesResult = $connect->query($monthSalesQuery);
$monthSales = $monthSalesResult->fetch_assoc()['total_month'];

// Get today's transaction count
$todayTransQuery = "SELECT COUNT(*) as count_today FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE()";
$todayTransResult = $connect->query($todayTransQuery);
$todayTransactions = $todayTransResult->fetch_assoc()['count_today'];

// Get monthly sales data for chart
function getMonthlySalesData($connect, $year = null) {
    if ($year === null) {
        $year = date('Y');
    }
    
    $monthlyData = array_fill(0, 12, 0);
    
    $query = "SELECT MONTH(tanggal_penjualan) as month, SUM(total_harga) as total 
              FROM penjualan 
              WHERE YEAR(tanggal_penjualan) = ? 
              GROUP BY MONTH(tanggal_penjualan)";
    
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $monthlyData[$row['month'] - 1] = (int)$row['total'];
    }
    
    return $monthlyData;
}

// Get top selling products
function getTopProducts($connect) {
    $query = "SELECT p.nama_produk, SUM(dp.jumlah) as total_sold
              FROM detail_penjualan dp
              JOIN produk p ON dp.kode_produk = p.kode_produk
              GROUP BY dp.kode_produk, p.nama_produk
              ORDER BY total_sold DESC
              LIMIT 5";
    
    $result = $connect->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get low stock products
function getLowStockProducts($connect) {
    $query = "SELECT kode_produk, kategori, nama_produk, jumlah_stok
              FROM produk 
              WHERE jumlah_stok <= 15
              ORDER BY jumlah_stok ASC
              LIMIT 10";
    
    $result = $connect->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get products expiring soon
function getExpiringProducts($connect, $days = 30) {
    $query = "SELECT kode_produk, kategori, nama_produk, kadaluwarsa
              FROM produk 
              WHERE kadaluwarsa <= DATE_ADD(CURDATE(), INTERVAL ? DAY) 
              AND kadaluwarsa >= CURDATE()
              ORDER BY kadaluwarsa ASC
              LIMIT 10";
    
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get data for display
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
$monthlyData = getMonthlySalesData($connect, $selectedYear);
$topProducts = getTopProducts($connect);
$lowStockProducts = getLowStockProducts($connect);
$expiringDays = isset($_GET['exp_days']) ? $_GET['exp_days'] : 30;
$expiringProducts = getExpiringProducts($connect, $expiringDays);

// Convert monthly data to JSON for JavaScript
$monthlyDataJson = json_encode($monthlyData);
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
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } ?>
        </aside>

        <!-- Main Content -->
        <main class="main-content">

            <div class="header-top">
                <div>
                    <h1 class="header-title">Hello <?= htmlspecialchars($userName) ?> 👋</h1>
                    <div class="header-subtitle">Selamat Datang</div>
                </div>
                <div class="search-profile">
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
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Dynamic Summary Cards -->
            <section class="summary">
                <div class="summary-card">
                    <div class="summary-icon sales">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Rp <?= number_format($todaySales, 0, ',', '.') ?></h3>
                        <p>Total Penjualan Hari Ini</p>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Rp <?= number_format($monthSales, 0, ',', '.') ?></h3>
                        <p>Total Penjualan Bulan Ini</p>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon transactions">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="summary-content">
                        <h3><?= $todayTransactions ?></h3>
                        <p>Transaksi Hari Ini</p>
                    </div>
                </div>
            </section>

            <!-- Charts and Products -->
            <section class="content-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h2>Laporan Penjualan</h2>
                        <select class="chart-filter" id="filterTahun" onchange="changeYear()">
                            <option value="2025" <?= $selectedYear == 2025 ? 'selected' : '' ?>>2025</option>
                            <option value="2024" <?= $selectedYear == 2024 ? 'selected' : '' ?>>2024</option>
                            <option value="2023" <?= $selectedYear == 2023 ? 'selected' : '' ?>>2023</option>
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
                        <?php if (empty($topProducts)): ?>
                            <div class="alert alert-info">Belum ada data penjualan produk</div>
                        <?php else: ?>
                            <?php foreach ($topProducts as $index => $product): ?>
                            <div class="product-item">
                                <span class="product-rank"><?= $index + 1 ?></span>
                                <span class="product-name"><?= htmlspecialchars($product['nama_produk']) ?></span>
                                <span class="product-sales"><?= $product['total_sold'] ?> unit</span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Dynamic Tables Section -->
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
                            <?php if (empty($lowStockProducts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada produk dengan stok rendah</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lowStockProducts as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['kode_produk']) ?></td>
                                    <td><?= ucfirst($product['kategori']) ?></td>
                                    <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                    <td><span class="badge bg-danger"><?= $product['jumlah_stok'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2>Kedaluwarsa Dekat</h2>
                        <select class="table-filter" id="expFilter" onchange="changeExpiryFilter()">
                            <option value="7" <?= $expiringDays == 7 ? 'selected' : '' ?>>7 Hari</option>
                            <option value="30" <?= $expiringDays == 30 ? 'selected' : '' ?>>1 Bulan</option>
                            <option value="60" <?= $expiringDays == 60 ? 'selected' : '' ?>>2 Bulan</option>
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
                            <?php if (empty($expiringProducts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada produk yang akan kadaluwarsa</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($expiringProducts as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['kode_produk']) ?></td>
                                    <td><?= ucfirst($product['kategori']) ?></td>
                                    <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= date('d-m-Y', strtotime($product['kadaluwarsa'])) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dynamic Chart.js with real data
        const ctx = document.getElementById('penjualanChart').getContext('2d');
        let penjualanChart;
        
        function createChart(data) {
            if (penjualanChart) {
                penjualanChart.destroy();
            }
            
            penjualanChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Total Penjualan (Rp)',
                        data: data,
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
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
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
                                padding: 10,
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
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
        }
        
        // Initialize chart with current data
        const currentData = <?= $monthlyDataJson ?>;
        createChart(currentData);
        
        // Function to change year
        function changeYear() {
            const year = document.getElementById('filterTahun').value;
            window.location.href = '?year=' + year + '&exp_days=' + <?= $expiringDays ?>;
        }
        
        // Function to change expiry filter
        function changeExpiryFilter() {
            const days = document.getElementById('expFilter').value;
            window.location.href = '?year=' + <?= $selectedYear ?> + '&exp_days=' + days;
        }

        // Enhanced Table Sorting with Animation
        document.getElementById('stokFilter').addEventListener('change', function() {
            const table = document.querySelector('.table-container:first-child table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const order = this.value;
            
            // Skip if no data rows
            if (rows.length === 0 || rows[0].cells.length === 1) return;
            
            // Add fade out effect
            tbody.style.opacity = '0.5';
            tbody.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                rows.sort((a, b) => {
                    const aStok = parseInt(a.cells[3].querySelector('span').textContent);
                    const bStok = parseInt(b.cells[3].querySelector('span').textContent);
                    return order === 'asc' ? aStok - bStok : bStok - aStok;
                });
                
                // Rebuild table with sorted rows
                rows.forEach(row => tbody.appendChild(row));
                
                // Fade back in
                tbody.style.opacity = '1';
            }, 300);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>