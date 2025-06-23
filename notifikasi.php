<?php
    session_start();
    include 'koneksi.php';
    
    // Handle search
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Function to get low stock notifications
    function getLowStockNotifications($connect, $search = '') {
        $query = "SELECT kode_produk, nama_produk, jumlah_stok, kategori 
                  FROM produk 
                  WHERE jumlah_stok <= 10"; // Threshold for low stock
        
        if (!empty($search)) {
            $query .= " AND nama_produk LIKE ?";
            $stmt = $connect->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("s", $searchParam);
        } else {
            $stmt = $connect->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Function to get expiry notifications
    function getExpiryNotifications($connect, $search = '') {
        $query = "SELECT kode_produk, nama_produk, kadaluwarsa, jumlah_stok 
                  FROM produk 
                  WHERE kadaluwarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                  AND kadaluwarsa >= CURDATE()
                  ORDER BY kadaluwarsa ASC";
        
        if (!empty($search)) {
            $query = "SELECT kode_produk, nama_produk, kadaluwarsa, jumlah_stok 
                      FROM produk 
                      WHERE kadaluwarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                      AND kadaluwarsa >= CURDATE()
                      AND nama_produk LIKE ?
                      ORDER BY kadaluwarsa ASC";
            $stmt = $connect->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("s", $searchParam);
        } else {
            $stmt = $connect->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Function to get recent orders
    function getRecentOrders($connect, $search = '') {
        $query = "SELECT p.id_penjualan, u.nama, p.tanggal_penjualan, p.total_harga, p.metode_pembayaran
                  FROM penjualan p
                  JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                  WHERE DATE(p.tanggal_penjualan) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY p.tanggal_penjualan DESC
                  LIMIT 10";
        
        if (!empty($search)) {
            $query = "SELECT p.id_penjualan, u.nama, p.tanggal_penjualan, p.total_harga, p.metode_pembayaran
                      FROM penjualan p
                      JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                      WHERE DATE(p.tanggal_penjualan) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      AND (u.nama LIKE ? OR p.id_penjualan LIKE ?)
                      ORDER BY p.tanggal_penjualan DESC
                      LIMIT 10";
            $stmt = $connect->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("ss", $searchParam, $searchParam);
        } else {
            $stmt = $connect->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Function to get delivery updates
    function getDeliveryUpdates($connect, $search = '') {
        $query = "SELECT pm.id_pemesanan, u.nama, pr.nama_produk, pm.tanggal_pesan, 
                         pm.tanggal_datang, pm.vendor, pm.jumlah_pesan, pm.jumlah_diterima, pm.stat
                  FROM pemesanan pm
                  JOIN pengguna u ON pm.id_pengguna = u.id_pengguna
                  JOIN produk pr ON pm.kode_produk = pr.kode_produk
                  WHERE DATE(pm.tanggal_pesan) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                  ORDER BY pm.tanggal_pesan DESC
                  LIMIT 10";
        
        if (!empty($search)) {
            $query = "SELECT pm.id_pemesanan, u.nama, pr.nama_produk, pm.tanggal_pesan, 
                             pm.tanggal_datang, pm.vendor, pm.jumlah_pesan, pm.jumlah_diterima, pm.stat
                      FROM pemesanan pm
                      JOIN pengguna u ON pm.id_pengguna = u.id_pengguna
                      JOIN produk pr ON pm.kode_produk = pr.kode_produk
                      WHERE DATE(pm.tanggal_pesan) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                      AND (pr.nama_produk LIKE ? OR pm.vendor LIKE ?)
                      ORDER BY pm.tanggal_pesan DESC
                      LIMIT 10";
            $stmt = $connect->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("ss", $searchParam, $searchParam);
        } else {
            $stmt = $connect->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get all notifications
    $lowStockNotifications = getLowStockNotifications($connect, $search);
    $expiryNotifications = getExpiryNotifications($connect, $search);
    $recentOrders = getRecentOrders($connect, $search);
    $deliveryUpdates = getDeliveryUpdates($connect, $search);

    // Count total new notifications
    $totalNewNotifications = $lowStockNotifications->num_rows + $expiryNotifications->num_rows + $recentOrders->num_rows;
    
    // Function to format time difference
    function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'Just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        return date('M j, Y', strtotime($datetime));
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="notifikasiStyle.css">
    <title>Notifikasi</title>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="logo text-center">
                <img src="assets/logo.png" alt="Logo TokoPojok" />
            </div>
            <nav>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Owner') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php" class="active"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'InventoryControl') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="notifikasi.php" class="active"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="notifikasi.php" class="active"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } ?>
            </nav>
        </aside>

        <main class="main-content">
            <div class="header-top">
                <div>
                    <h1 class="header-title">Notifications</h1>
                    <div class="header-subtitle">Stay Updated with Recent Activities</div>
                </div>
                <div class="search-profile">
                    <form method="GET" class="d-flex">
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..." aria-label="Search notifications" class="form-control me-2" />
                    </form>
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

            <div class="notification-container">
                <div class="notification-header">
                    <div class="notification-title">Recent Notifications</div>
                    <div class="notification-badge"><?= $totalNewNotifications ?> New</div>
                </div>
                
                <?php if ($totalNewNotifications == 0 && !empty($search)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No notifications found for "<?= htmlspecialchars($search) ?>"
                    </div>
                <?php elseif ($totalNewNotifications == 0): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> No new notifications. Everything is up to date!
                    </div>
                <?php endif; ?>

                <!-- Low Stock Notifications -->
                <?php while ($row = $lowStockNotifications->fetch_assoc()): ?>
                <div class="notification-item warning">
                    <div class="notification-icon warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            Low Stock Alert
                            <span class="badge bg-danger">Urgent</span>
                        </div>
                        <div class="notification-text">
                            Stock for <?= htmlspecialchars($row['nama_produk']) ?> is running low 
                            (only <?= $row['jumlah_stok'] ?> units left). Please restock immediately.
                        </div>
                        <div class="notification-meta">
                            <div class="notification-time">Category: <?= ucfirst($row['kategori']) ?></div>
                            <div class="notification-actions">
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>

                <!-- Expiry Notifications -->
                <?php while ($row = $expiryNotifications->fetch_assoc()): 
                    $daysLeft = floor((strtotime($row['kadaluwarsa']) - time()) / (60 * 60 * 24));
                ?>
                <div class="notification-item expiry">
                    <div class="notification-icon expiry">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            Expiry Alert
                            <span class="badge bg-warning text-dark">Warning</span>
                        </div>
                        <div class="notification-text">
                            <?= htmlspecialchars($row['nama_produk']) ?> will expire in <?= $daysLeft ?> days 
                            (<?= date('M j, Y', strtotime($row['kadaluwarsa'])) ?>). Please take action.
                        </div>
                        <div class="notification-meta">
                            <div class="notification-time">Stock: <?= $row['jumlah_stok'] ?> units</div>
                            <div class="notification-actions">
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>

                <!-- Recent Orders -->
                <?php while ($row = $recentOrders->fetch_assoc()): ?>
                <div class="notification-item order">
                    <div class="notification-icon order">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            New Order
                            <span class="badge bg-primary">Order</span>
                        </div>
                        <div class="notification-text">
                            <?= htmlspecialchars($row['nama']) ?> placed a new order with ID #<?= htmlspecialchars($row['id_penjualan']) ?>. 
                            Total: Rp <?= number_format($row['total_harga'], 0, ',', '.') ?> 
                            (<?= ucfirst($row['metode_pembayaran']) ?>)
                        </div>
                        <div class="notification-meta">
                            <div class="notification-time"><?= timeAgo($row['tanggal_penjualan']) ?></div>
                            <div class="notification-actions">
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add interactivity to notifications
        document.querySelectorAll('.notification-btn.mark-read').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                notificationItem.style.opacity = '0.7';
                notificationItem.style.borderLeftColor = '#adb5bd';
                this.innerHTML = '<i class="fas fa-check-circle"></i> Read';
                this.style.color = '#6c757d';
                
                // Update notification count
                const badge = document.querySelector('.notification-badge');
                let currentCount = parseInt(badge.textContent);
                if (currentCount > 0) {
                    badge.textContent = (currentCount - 1) + ' New';
                }
            });
        });
        
        document.querySelectorAll('.notification-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                notificationItem.style.transform = 'translateX(100%)';
                notificationItem.style.opacity = '0';
                setTimeout(() => {
                    notificationItem.remove();
                    
                    // Update notification count
                    const badge = document.querySelector('.notification-badge');
                    let currentCount = parseInt(badge.textContent);
                    if (currentCount > 0) {
                        badge.textContent = (currentCount - 1) + ' New';
                    }
                }, 300);
            });
        });
    </script>
</body>
</html>