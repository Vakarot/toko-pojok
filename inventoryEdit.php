<?php
include 'koneksi.php';

// Get product code from URL parameter
$kode_produk = isset($_GET['kode']) ? $_GET['kode'] : '';
$data = null;

// If editing existing product, fetch data
if ($kode_produk) {
    $query = "SELECT * FROM produk WHERE kode_produk = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "s", $kode_produk);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    
    if (!$data) {
        header("Location: inventory.php?error=produk_tidak_ditemukan");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori = $_POST['kategoriProduk'];
    $nama_produk = $_POST['namaProduk'];
    $kode_produk_new = $_POST['kodeProduk'];
    $satuan = $_POST['satuanProduk'];
    $kadaluwarsa = $_POST['kadaluwarsa'];
    $harga = $_POST['hargaProduk'];
    $jumlah_stok = $_POST['jumlahStok'];
    
    // Validate required fields
    if (empty($kategori) || empty($nama_produk) || empty($kode_produk_new) || 
        empty($satuan) || empty($kadaluwarsa) || empty($harga) || empty($jumlah_stok)) {
        $error = "Semua field harus diisi!";
    } else {
        // Check if updating existing product
        if ($kode_produk && $kode_produk != $kode_produk_new) {
            // Check if new product code already exists
            $check_query = "SELECT kode_produk FROM produk WHERE kode_produk = ? AND kode_produk != ?";
            $check_stmt = mysqli_prepare($connect, $check_query);
            mysqli_stmt_bind_param($check_stmt, "ss", $kode_produk_new, $kode_produk);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error = "Kode produk sudah ada!";
            }
        } elseif (!$kode_produk) {
            // Check if product code exists for new product
            $check_query = "SELECT kode_produk FROM produk WHERE kode_produk = ?";
            $check_stmt = mysqli_prepare($connect, $check_query);
            mysqli_stmt_bind_param($check_stmt, "s", $kode_produk_new);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error = "Kode produk sudah ada!";
            }
        }
        
        if (!isset($error)) {
            if ($kode_produk) {
                // Update existing product
                $update_query = "UPDATE produk SET kategori=?, nama_produk=?, kode_produk=?, satuan=?, kadaluwarsa=?, harga=?, jumlah_stok=? WHERE kode_produk=?";
                $update_stmt = mysqli_prepare($connect, $update_query);
                mysqli_stmt_bind_param($update_stmt, "sssssdis", $kategori, $nama_produk, $kode_produk_new, $satuan, $kadaluwarsa, $harga, $jumlah_stok, $kode_produk);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $success = "Data produk berhasil diperbarui!";
                    // Update data array for form display
                    $data = [
                        'kategori' => $kategori,
                        'nama_produk' => $nama_produk,
                        'kode_produk' => $kode_produk_new,
                        'satuan' => $satuan,
                        'kadaluwarsa' => $kadaluwarsa,
                        'harga' => $harga,
                        'jumlah_stok' => $jumlah_stok
                    ];
                    $kode_produk = $kode_produk_new; // Update for next operations
                } else {
                    $error = "Gagal memperbarui data: " . mysqli_error($connect);
                }
            } else {
                // Insert new product
                $insert_query = "INSERT INTO produk (kode_produk, kategori, nama_produk, satuan, kadaluwarsa, harga, jumlah_stok) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($connect, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, "sssssdi", $kode_produk_new, $kategori, $nama_produk, $satuan, $kadaluwarsa, $harga, $jumlah_stok);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    $success = "Produk baru berhasil ditambahkan!";
                    // Redirect to edit mode for this new product
                    header("Location: inventoryEdit.php?kode=" . urlencode($kode_produk_new) . "&success=1");
                    exit();
                } else {
                    $error = "Gagal menambahkan produk: " . mysqli_error($connect);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $kode_produk ? 'Edit' : 'Tambah' ?> Produk - TokoPojok</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet"/>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"/>

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    * {
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(to right, #f0f4f3, #e0f7fa);
      padding: 60px 0;
    }

    .form-wrapper {
      background-color: #fff;
      padding: 50px 40px;
      border-radius: 20px;
      box-shadow: 0 16px 32px rgba(0, 0, 0, 0.1);
      max-width: 900px;
      margin: auto;
      border-top: 6px solid #4CAF50;
      transition: transform 0.3s ease;
    }

    .form-wrapper:hover {
      transform: translateY(-4px);
    }

    h1 {
      color: #4CAF50;
      font-weight: 600;
      margin-bottom: 10px;
      text-align: center;
    }

    .form-description {
      text-align: center;
      margin-bottom: 30px;
      font-size: 16px;
      color: #6c757d;
    }

    label {
      font-weight: 500;
      color: #495057;
      margin-bottom: 5px;
    }

    .form-control,
    .form-select {
      border-radius: 12px;
      padding: 10px 14px;
      border: 1px solid #ced4da;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #4CAF50;
      box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
    }

    .input-group-text {
      background-color: #f1f3f5;
      border-radius: 12px 0 0 12px;
      border: 1px solid #ced4da;
      border-right: none;
    }

    .btn-success {
      background: linear-gradient(135deg, #4CAF50, #81C784);
      border: none;
      padding: 12px 28px;
      border-radius: 14px;
      font-weight: 500;
      transition: background-color 0.3s ease, transform 0.2s;
      color: #fff;
    }

    .btn-success:hover {
      background: linear-gradient(135deg, #43A047, #66BB6A);
      transform: scale(1.03);
    }

    .btn-secondary {
      border-radius: 14px;
      padding: 12px 28px;
    }

    .alert {
      border-radius: 12px;
      border: none;
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="form-wrapper">
      <h1><i class="bi bi-<?= $kode_produk ? 'pencil-square' : 'plus-square' ?> me-2"></i><?= $kode_produk ? 'Edit' : 'Tambah' ?> Produk</h1>
      <p class="form-description"><?= $kode_produk ? 'Perbarui informasi produk pada form di bawah ini secara lengkap dan akurat.' : 'Tambahkan produk baru dengan mengisi form di bawah ini secara lengkap dan akurat.' ?></p>
      
      <?php if (isset($error)): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($success)): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="row g-4">
          <div class="col-md-6">
            <label for="kategoriProduk">Kategori Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-tags-fill"></i></span>
              <select id="kategoriProduk" name="kategoriProduk" class="form-select" required>
                <option value="" disabled <?= !$data ? 'selected' : '' ?>>Pilih Kategori Produk</option>
                <option value="bibit" <?= ($data && $data['kategori'] == 'bibit') ? 'selected' : '' ?>>Bibit</option>
                <option value="benih" <?= ($data && $data['kategori'] == 'benih') ? 'selected' : '' ?>>Benih</option>
                <option value="perlengkapan" <?= ($data && $data['kategori'] == 'perlengkapan') ? 'selected' : '' ?>>Perlengkapan</option>
                <option value="pestisida" <?= ($data && $data['kategori'] == 'pestisida') ? 'selected' : '' ?>>Pestisida</option>
                <option value="pupuk" <?= ($data && $data['kategori'] == 'pupuk') ? 'selected' : '' ?>>Pupuk</option>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <label for="namaProduk">Nama Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-box"></i></span>
              <input type="text" id="namaProduk" name="namaProduk" class="form-control" placeholder="Masukkan Nama Produk" value="<?= $data ? htmlspecialchars($data['nama_produk']) : '' ?>" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="kodeProduk">Kode Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-upc"></i></span>
              <input type="text" id="kodeProduk" name="kodeProduk" class="form-control" placeholder="Masukkan Kode Produk" value="<?= $data ? htmlspecialchars($data['kode_produk']) : '' ?>" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="satuanProduk">Satuan Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-rulers"></i></span>
              <input type="text" id="satuanProduk" name="satuanProduk" class="form-control" placeholder="Masukkan Satuan" value="<?= $data ? htmlspecialchars($data['satuan']) : '' ?>" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="kadaluwarsa">Kadaluwarsa</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-calendar"></i></span>
              <input type="date" id="kadaluwarsa" name="kadaluwarsa" class="form-control" value="<?= $data ? htmlspecialchars($data['kadaluwarsa']) : '' ?>" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="hargaProduk">Harga Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
              <input type="number" id="hargaProduk" name="hargaProduk" class="form-control" placeholder="Masukkan Harga" value="<?= $data ? htmlspecialchars($data['harga']) : '' ?>" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="jumlahStok">Jumlah Stok</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-123"></i></span>
              <input type="number" id="jumlahStok" name="jumlahStok" class="form-control" placeholder="Masukkan Stok" value="<?= $data ? htmlspecialchars($data['jumlah_stok']) : '' ?>" required />
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-5">
            <a href="inventory.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-2"></i><?= $kode_produk ? 'Simpan Perubahan' : 'Tambah Produk' ?>
            </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

  <?php if (isset($success) && isset($_GET['success'])): ?>
  <script>
    Swal.fire({
      icon: "success",
      title: "Berhasil!",
      text: "<?= htmlspecialchars($success) ?>",
      confirmButtonColor: "#4CAF50"
    });
  </script>
  <?php endif; ?>

</body>
</html>