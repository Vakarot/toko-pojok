<?php
    session_start();
    include 'koneksi.php';
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
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search notifications..." aria-label="Search notifications" class="form-control me-2" />
                        <?php if (!empty($search)): ?>
                            <a href="notifikasi.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
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

            <div class="notification-container">
                <div class="notification-header">
                    <div class="notification-title">Recent Notifications</div>
                    <div class="notification-badge">4 New</div>
                </div>
                
                <!-- Notification Items -->
                <div class="notification-item order">
                    <div class="notification-icon order">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            New Order
                            <span class="badge bg-primary">Order</span>
                        </div>
                        <div class="notification-text">Luthfan Kafi placed a new order with ID #24020. Please process the order.</div>
                        <div class="notification-meta">
                            <div class="notification-time">Today, 10:30 AM</div>
                            <div class="notification-actions">
                                <button class="notification-btn mark-read"><i class="far fa-check-circle"></i> Mark as read</button>
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="notification-item warning">
                    <div class="notification-icon warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            Low Stock Alert
                            <span class="badge bg-danger">Urgent</span>
                        </div>
                        <div class="notification-text">Stock for Avidor 25 WP is running low (only 5 units left). Please restock immediately.</div>
                        <div class="notification-meta">
                            <div class="notification-time">Yesterday, 3:45 PM</div>
                            <div class="notification-actions">
                                <button class="notification-btn mark-read"><i class="far fa-check-circle"></i> Mark as read</button>
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="notification-item warning">
                    <div class="notification-icon warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            Low Stock Alert
                            <span class="badge bg-danger">Urgent</span>
                        </div>
                        <div class="notification-text">Stock for FOSTIN 610 EC is critically low (only 3 units left). Please restock immediately.</div>
                        <div class="notification-meta">
                            <div class="notification-time">Yesterday, 1:20 PM</div>
                            <div class="notification-actions">
                                <button class="notification-btn mark-read"><i class="far fa-check-circle"></i> Mark as read</button>
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="notification-item expiry">
                    <div class="notification-icon expiry">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            Expiry Alert
                            <span class="badge bg-warning text-dark">Warning</span>
                        </div>
                        <div class="notification-text">Tebu Telur will expire in 7 days (April 30, 2025). Please take action.</div>
                        <div class="notification-meta">
                            <div class="notification-time">April 23, 2025, 9:15 AM</div>
                            <div class="notification-actions">
                                <button class="notification-btn mark-read"><i class="far fa-check-circle"></i> Mark as read</button>
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="notification-divider"></div>
                
                <!-- Archived Notifications -->
                <div class="notification-header" style="margin-top: 1.5rem;">
                    <div class="notification-title">Earlier Notifications</div>
                </div>
                
                <div class="notification-item">
                    <div class="notification-icon" style="background-color: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-category">
                            Delivery Completed
                            <span class="badge bg-info">Delivery</span>
                        </div>
                        <div class="notification-text">Order #24015 has been successfully delivered to the customer.</div>
                        <div class="notification-meta">
                            <div class="notification-time">April 22, 2025, 2:30 PM</div>
                            <div class="notification-actions">
                                <button class="notification-btn delete"><i class="far fa-trash-alt"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
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
            });
        });
        
        document.querySelectorAll('.notification-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                notificationItem.style.transform = 'translateX(100%)';
                notificationItem.style.opacity = '0';
                setTimeout(() => {
                    notificationItem.remove();
                }, 300);
            });
        });
    </script>
</body>
</html>