<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1>Tambah Produk</h1>
        <div class="row">
            <div class="col">
                <label for="kategoriProduk">Kategori Produk</label>
                <select class="form-select" aria-label="Default select example">
                    <option selected>Pilih Kategori Produk</option>
                    <option value="Bibit">Bibit</option>
                    <option value="Benih">Benih</option>
                    <option value="Perlengkapan">Perlengkapan</option>
                    <option value="Pestisida">Pestisida</option>
                    <option value="Pupuk">Pupuk</option>
                </select>
            </div>
            <div class="col">
                <label for="namaProduk">Nama Produk</label>
                <input type="text" class="form-control" placeholder="Masukkan Nama Produk" aria-label="Nama Produk">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="kodeProduk">Kode Produk</label>
                <input type="text" class="form-control" placeholder="Masukkan Kode Produk" aria-label="Nama Produk">
            </div>
            <div class="col">
                <label for="satuanProduk">Satuan Produk</label>
                <input type="text" class="form-control" placeholder="Masukkan Satuan Produk" aria-label="Satuan Produk">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="kadaluwarsa">Kadaluwarsa</label>
                <input type="date" class="form-control" placeholder="Pilih Tanggal Kadaluwarsa" aria-label="Kadaluwarsa">
            </div>
            <div class="col">
                <label for="hargaProduk">Harga Produk</label>
                <input type="text" class="form-control" placeholder="Masukkan Harga Produk" aria-label="Harga Produk">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="jumlahStok">Jumlah Stok</label>
                <input type="text" class="form-control" placeholder="Masukkan Jumlah Stok" aria-label="Jumlah Stok">
            </div>
            <!-- <div class="col">
                <label for="hargaProduk">Harga Produk</label>
                <input type="text" class="form-control" placeholder="Masukkan Harga Produk" aria-label="Harga Produk">
            </div> -->
        </div>
        
        <button type="submit" class="btn btn-success" href="inventoryInput.php">Kirim Pemesanan</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>