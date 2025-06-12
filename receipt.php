<?php
//receipt.php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['transaction'])) {
    header('Location: cashier.php');
    exit;
}

$transaction = $_SESSION['transaction'];

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Receipt - TokoPojok</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .receipt { 
                width: 80mm; 
                margin: 0; 
                box-shadow: none; 
                border: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full receipt">
            <!-- Receipt Header -->
            <div class="text-center border-b-2 border-dashed border-gray-300 p-6">
                <h1 class="text-2xl font-bold text-green-800 mb-2">TOKO POJOK</h1>
                <p class="text-sm text-gray-600">Jl. Contoh No. 123, Yogyakarta</p>
                <p class="text-sm text-gray-600">Telp: (0274) 123456</p>
            </div>
            
            <!-- Transaction Info -->
            <div class="p-6 border-b-2 border-dashed border-gray-300">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">No. Transaksi:</span>
                    <span class="text-sm font-semibold">#<?= str_pad($transaction['id_penjualan'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Tanggal:</span>
                    <span class="text-sm"><?= date('d/m/Y H:i:s', strtotime($transaction['tanggal'])); ?></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Kasir:</span>
                    <span class="text-sm">Luthfan Kafi</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pembayaran:</span>
                    <span class="text-sm font-semibold uppercase bg-green-100 text-green-800 px-2 py-1 rounded">
                        <?= $transaction['payment_method']; ?>
                    </span>
                </div>
            </div>
            
            <!-- Items -->
            <div class="p-6 border-b-2 border-dashed border-gray-300">
                <h3 class="font-semibold mb-4 text-center">DETAIL PEMBELIAN</h3>
                <div class="space-y-3">
                    <?php foreach ($transaction['items'] as $kode_produk => $item): ?>
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-sm"><?= htmlspecialchars($item['nama_produk']); ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($kode_produk); ?></p>
                                <p class="text-xs text-gray-600">
                                    <?= $item['quantity']; ?> x <?= formatRupiah($item['harga']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-sm"><?= formatRupiah($item['harga'] * $item['quantity']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Total -->
            <div class="p-6 border-b-2 border-dashed border-gray-300">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Total Item:</span>
                    <span class="text-sm"><?= $transaction['total_items']; ?> item</span>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">TOTAL:</span>
                    <span class="text-lg font-bold text-green-800"><?= formatRupiah($transaction['total_harga']); ?></span>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="p-6 text-center">
                <p class="text-sm text-gray-600 mb-2">Terima kasih atas kunjungan Anda!</p>
                <p class="text-xs text-gray-500 mb-4">Barang yang sudah dibeli tidak dapat dikembalikan</p>
                <div class="border-t pt-4">
                    <p class="text-xs text-gray-400">Powered by TokoPojok System</p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="p-6 bg-gray-50 rounded-b-lg no-print">
                <div class="flex space-x-3">
                    <button onclick="window.print()" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 flex items-center justify-center space-x-2">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                    <a href="cashier.php" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center space-x-2 text-decoration-none">
                        <i class="fas fa-plus"></i>
                        <span>Transaksi Baru</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
        
        // Clear transaction data after printing
        window.addEventListener('afterprint', function() {
            <?php unset($_SESSION['transaction']); ?>
        });
    </script>
</body>
</html>