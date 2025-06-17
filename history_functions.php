<?php
// history_functions.php - Helper functions untuk sistem history

/**
 * Fungsi untuk menambahkan riwayat aktivitas ke database
 * @param object $connection - Koneksi database
 * @param string $id_pengguna - ID pengguna yang melakukan aktivitas
 * @param string $action - Deskripsi aktivitas yang dilakukan
 * @return bool - True jika berhasil, false jika gagal
 */
function addHistory($connection, $id_pengguna, $action) {
    $query = "INSERT INTO riwayat (id_pengguna, action, timestamp) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($connection, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $id_pengguna, $action);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    return false;
}

/**
 * Fungsi untuk mendapatkan riwayat dengan pagination dan filter
 * @param object $connection - Koneksi database
 * @param int $limit - Jumlah data per halaman
 * @param int $offset - Offset untuk pagination
 * @param string $filter_user - Filter berdasarkan user
 * @param string $date_from - Filter tanggal mulai
 * @param string $date_to - Filter tanggal akhir
 * @return array - Array hasil query
 */
function getHistory($connection, $limit = 20, $offset = 0, $filter_user = '', $date_from = '', $date_to = '') {
    $query = "SELECT r.kode_riwayat, r.id_pengguna, p.nama, p.role, r.action, r.timestamp 
              FROM riwayat r 
              JOIN pengguna p ON r.id_pengguna = p.id_pengguna";
    
    $conditions = [];
    $params = [];
    
    if ($filter_user) {
        $conditions[] = "r.id_pengguna = ?";
        $params[] = $filter_user;
    }
    
    if ($date_from) {
        $conditions[] = "DATE(r.timestamp) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $conditions[] = "DATE(r.timestamp) <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $query .= " ORDER BY r.timestamp DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        if (!empty($params)) {
            $types = str_repeat('s', count($params) - 2) . 'ii';
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $history = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $history;
    }
    
    return [];
}

/**
 * Fungsi untuk mendapatkan total riwayat (untuk pagination)
 * @param object $connection - Koneksi database
 * @param string $filter_user - Filter berdasarkan user
 * @param string $date_from - Filter tanggal mulai
 * @param string $date_to - Filter tanggal akhir
 * @return int - Jumlah total record
 */
function getTotalHistory($connection, $filter_user = '', $date_from = '', $date_to = '') {
    $query = "SELECT COUNT(*) as total FROM riwayat r JOIN pengguna p ON r.id_pengguna = p.id_pengguna";
    
    $conditions = [];
    $params = [];
    
    if ($filter_user) {
        $conditions[] = "r.id_pengguna = ?";
        $params[] = $filter_user;
    }
    
    if ($date_from) {
        $conditions[] = "DATE(r.timestamp) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $conditions[] = "DATE(r.timestamp) <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['total'];
    }
    
    return 0;
}

/**
 * Fungsi untuk mendapatkan ringkasan aktivitas harian
 * @param object $connection - Koneksi database
 * @param int $days - Jumlah hari ke belakang (default 7)
 * @return array - Array ringkasan aktivitas per hari
 */
function getDailyActivitySummary($connection, $days = 7) {
    $query = "SELECT 
                DATE(timestamp) as tanggal,
                COUNT(*) as total_aktivitas,
                COUNT(DISTINCT id_pengguna) as pengguna_aktif
              FROM riwayat 
              WHERE DATE(timestamp) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              GROUP BY DATE(timestamp)
              ORDER BY tanggal DESC";
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $days);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $summary = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $summary[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $summary;
    }
    
    return [];
}

/**
 * Fungsi untuk mendapatkan statistik aktivitas
 * @param object $connection - Koneksi database
 * @param string $date_from - Filter tanggal mulai
 * @param string $date_to - Filter tanggal akhir
 * @return array - Array statistik aktivitas
 */
function getActivityStats($connection, $date_from = '', $date_to = '') {
    $query = "SELECT 
                COUNT(*) as total_activities,
                COUNT(DISTINCT r.id_pengguna) as active_users,
                COUNT(CASE WHEN DATE(r.timestamp) = CURDATE() THEN 1 END) as today_activities,
                COUNT(CASE WHEN DATE(r.timestamp) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as week_activities,
                COUNT(CASE WHEN DATE(r.timestamp) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as month_activities
              FROM riwayat r";
    
    $conditions = [];
    $params = [];
    
    if ($date_from) {
        $conditions[] = "DATE(r.timestamp) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $conditions[] = "DATE(r.timestamp) <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $stats;
    }
    
    return [
        'total_activities' => 0,
        'active_users' => 0,
        'today_activities' => 0,
        'week_activities' => 0,
        'month_activities' => 0
    ];
}

/**
 * Fungsi untuk mendapatkan aktivitas berdasarkan tipe
 * @param object $connection - Koneksi database
 * @param int $days - Jumlah hari ke belakang
 * @return array - Array aktivitas berdasarkan tipe
 */
function getActivityByType($connection, $days = 7) {
    $query = "SELECT 
                CASE 
                    WHEN action LIKE '%login%' THEN 'Login'
                    WHEN action LIKE '%logout%' THEN 'Logout'
                    WHEN action LIKE '%tambah%' OR action LIKE '%add%' THEN 'Tambah Data'
                    WHEN action LIKE '%edit%' OR action LIKE '%update%' THEN 'Edit Data'
                    WHEN action LIKE '%hapus%' OR action LIKE '%delete%' THEN 'Hapus Data'
                    WHEN action LIKE '%penjualan%' OR action LIKE '%jual%' THEN 'Penjualan'
                    WHEN action LIKE '%pemesanan%' OR action LIKE '%pesan%' THEN 'Pemesanan'
                    ELSE 'Lainnya'
                END as activity_type,
                COUNT(*) as count
              FROM riwayat
              WHERE DATE(timestamp) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              GROUP BY activity_type
              ORDER BY count DESC";
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $days);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $activities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $activities[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $activities;
    }
    
    return [];
}

/**
 * Fungsi untuk export riwayat ke CSV
 * @param object $connection - Koneksi database
 * @param string $filename - Nama file (optional)
 * @param string $date_from - Filter tanggal mulai
 * @param string $date_to - Filter tanggal akhir
 */
function exportHistoryToCSV($connection, $filename = null, $date_from = '', $date_to = '') {
    if (!$filename) {
        $filename = 'history_export_' . date('Y-m-d_H-i-s') . '.csv';
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, "\xEF\xBB\xBF");
    
    // Header CSV
    fputcsv($output, [
        'Kode Riwayat',
        'ID Pengguna', 
        'Nama Pengguna',
        'Role',
        'Action',
        'Timestamp'
    ]);
    
    // Query data
    $query = "SELECT r.kode_riwayat, r.id_pengguna, p.nama, p.role, r.action, r.timestamp 
              FROM riwayat r 
              JOIN pengguna p ON r.id_pengguna = p.id_pengguna";
    
    $conditions = [];
    $params = [];
    
    if ($date_from) {
        $conditions[] = "DATE(r.timestamp) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $conditions[] = "DATE(r.timestamp) <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $query .= " ORDER BY r.timestamp DESC";
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['kode_riwayat'],
                $row['id_pengguna'],
                $row['nama'],
                $row['role'],
                $row['action'],
                $row['timestamp']
            ]);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    fclose($output);
    exit();
}

/**
 * Fungsi untuk membersihkan riwayat lama
 * @param object $connection - Koneksi database
 * @param int $days - Hapus riwayat lebih dari X hari (default 90 hari)
 * @return bool - True jika berhasil
 */
function cleanOldHistory($connection, $days = 90) {
    $query = "DELETE FROM riwayat WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = mysqli_prepare($connection, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $days);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    return false;
}

/**
 * Fungsi untuk mendapatkan aktivitas pengguna terakhir
 * @param object $connection - Koneksi database
 * @param string $id_pengguna - ID pengguna
 * @param int $limit - Jumlah aktivitas terakhir
 * @return array - Array aktivitas pengguna
 */
function getUserLastActivities($connection, $id_pengguna, $limit = 10) {
    $query = "SELECT r.action, r.timestamp 
              FROM riwayat r 
              WHERE r.id_pengguna = ? 
              ORDER BY r.timestamp DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $id_pengguna, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $activities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $activities[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $activities;
    }
    
    return [];
}

/**
 * Fungsi untuk mendapatkan pengguna paling aktif
 * @param object $connection - Koneksi database
 * @param int $days - Periode dalam hari
 * @param int $limit - Jumlah pengguna yang ditampilkan
 * @return array - Array pengguna paling aktif
 */
function getMostActiveUsers($connection, $days = 30, $limit = 5) {
    $query = "SELECT p.nama, p.role, COUNT(r.kode_riwayat) as total_activities
              FROM riwayat r
              JOIN pengguna p ON r.id_pengguna = p.id_pengguna
              WHERE DATE(r.timestamp) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              GROUP BY r.id_pengguna, p.nama, p.role
              ORDER BY total_activities DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $days, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $users;
    }
    
    return [];
}
?>