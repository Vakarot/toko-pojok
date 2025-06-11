<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Produk - TokoPojok</title>

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
  </style>
</head>
<body>

  <div class="container">
    <div class="form-wrapper">
      <h1><i class="bi bi-pencil-square me-2"></i>Edit Produk</h1>
      <p class="form-description">Perbarui informasi produk pada form di bawah ini secara lengkap dan akurat.</p>
      <form id="produkForm">
        <div class="row g-4">
          <div class="col-md-6">
            <label for="kategoriProduk">Kategori Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-tags-fill"></i></span>
              <select id="kategoriProduk" class="form-select" required>
                <option value="" disabled selected>Pilih Kategori Produk</option>
                <option value="Bibit">Bibit</option>
                <option value="Benih">Benih</option>
                <option value="Perlengkapan">Perlengkapan</option>
                <option value="Pestisida">Pestisida</option>
                <option value="Pupuk">Pupuk</option>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <label for="namaProduk">Nama Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-box"></i></span>
              <input type="text" id="namaProduk" class="form-control" placeholder="Masukkan Nama Produk" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="kodeProduk">Kode Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-upc"></i></span>
              <input type="text" id="kodeProduk" class="form-control" placeholder="Masukkan Kode Produk" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="satuanProduk">Satuan Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-rulers"></i></span>
              <input type="text" id="satuanProduk" class="form-control" placeholder="Masukkan Satuan" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="kadaluwarsa">Kadaluwarsa</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-calendar"></i></span>
              <input type="date" id="kadaluwarsa" class="form-control" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="hargaProduk">Harga Produk</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
              <input type="number" id="hargaProduk" class="form-control" placeholder="Masukkan Harga" required />
            </div>
          </div>

          <div class="col-md-6">
            <label for="jumlahStok">Jumlah Stok</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-123"></i></span>
              <input type="number" id="jumlahStok" class="form-control" placeholder="Masukkan Stok" required />
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-5">
            <a href="inventory.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-2"></i>Simpan Perubahan
            </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.getElementById("produkForm").addEventListener("submit", function (e) {
      e.preventDefault();

      const fields = [
        "kategoriProduk", "namaProduk", "kodeProduk",
        "satuanProduk", "kadaluwarsa", "hargaProduk", "jumlahStok"
      ];

      let isValid = fields.every(id => document.getElementById(id).value.trim() !== "");

      if (isValid) {
        Swal.fire({
          icon: "success",
          title: "Berhasil!",
          text: "Data produk berhasil diperbarui.",
          confirmButtonColor: "#4CAF50"
        });
        this.reset();
      } else {
        Swal.fire({
          icon: "warning",
          title: "Gagal",
          text: "Harap lengkapi semua isian terlebih dahulu.",
          confirmButtonColor: "#d33"
        });
      }
    });
  </script>
</body>
</html>