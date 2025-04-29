<?php
// Koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "produk");

// Proses tambah produk
if (isset($_POST['tambah'])) {
    $nama_produk = htmlspecialchars($_POST['nama_produk']);
    $harga = (int)$_POST['harga'];
    $diskon = (int)$_POST['diskon'];
    $nominal_diskon = $harga * $diskon / 100;
    $total_harga = $harga - $nominal_diskon;

    mysqli_query($koneksi, "INSERT INTO produk (nama_produk, harga, diskon, total_harga) VALUES ('$nama_produk', $harga, $diskon, $total_harga)");
    echo "<script>alert('Data berhasil ditambahkan'); window.location.href='index.php';</script>";
    exit;
}

// Proses hapus produk
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk = $id");
    header("Location: index.php");
    exit;
}

// Ambil total keseluruhan
$result_total = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total FROM produk");
$row_total = mysqli_fetch_assoc($result_total);
$total_keseluruhan = (int)$row_total['total'];

// Proses pembayaran
if (isset($_POST['proses_bayar'])) {
    $uang_diberikan = (int)$_POST['uang_diberikan'];
    if ($uang_diberikan >= $total_keseluruhan) {
        $kembalian = $uang_diberikan - $total_keseluruhan;
        mysqli_query($koneksi, "TRUNCATE TABLE produk"); // Kosongkan keranjang
        echo "<script>
            alert('Pembayaran berhasil! Kembalian: Rp " . number_format($kembalian, 0, ',', '.') . "');
            window.location.href='index.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Uang tidak cukup!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk | Aplikasi Kasir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7fb; }
        .card { border-radius: 12px; }
        .btn-primary { background: linear-gradient(90deg, #2563eb, #9333ea); border: none; }
        .btn-outline-danger { border-radius: 8px; }
        .table th, .table td { vertical-align: middle; }
        .copyright { font-size: 0.95rem; color: #888; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold">Aplikasi Perhitungan</h2>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">Aplikasi Perhitungan</li>
              </ol>
            </nav>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" name="nama_produk" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="harga" min="0" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Diskon (%)</label>
                    <input type="number" class="form-control" name="diskon" min="0" max="100" value="0" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="tambah" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah</button>
                    <button type="reset" class="btn btn-outline-danger"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:5%;">No</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Diskon (%)</th>
                        <th>Nominal Diskon</th>
                        <th>Total Setelah Diskon</th>
                        <th style="width:8%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            $nominal_diskon = $row['harga'] * $row['diskon'] / 100;
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?= $row['diskon']; ?>%</td>
                        <td>Rp <?= number_format($nominal_diskon, 0, ',', '.'); ?></td>
                        <td>Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="index.php?hapus=<?= $row['id_produk']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada produk ditambahkan.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total Keseluruhan</th>
                        <th colspan="2">Rp <?= number_format($total_keseluruhan, 0, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>

            <!-- Tombol Bayar -->
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPembayaran">
                    <i class="bi bi-cash-coin"></i> Bayar
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center copyright">
        Copyright &copy; Aplikasi Kasir 2024
    </div>
</div>

<!-- Modal Pembayaran -->
<div class="modal fade" id="modalPembayaran" tabindex="-1" aria-labelledby="modalPembayaranLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPembayaranLabel">Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Total Bayar (Rp)</label>
            <input type="text" class="form-control" name="total_bayar" value="<?= number_format($total_keseluruhan, 0, ',', '.'); ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Uang Diberikan</label>
            <input type="number" class="form-control" id="uangDiberikan" name="uang_diberikan" min="<?= $total_keseluruhan ?>" required oninput="hitungKembalian()">
        </div>
        <div class="mb-3">
            <label class="form-label">Kembalian</label>
            <input type="text" class="form-control" id="kembalian" readonly>
        </div>

        </div>
        <div class="modal-footer">
          <button type="submit" name="proses_bayar" class="btn btn-primary">Proses Pembayaran</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap 5 JS dan Icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function hitungKembalian() {
    const total = <?= $total_keseluruhan ?>;
    const uangDiberikan = parseInt(document.getElementById('uangDiberikan').value) || 0;
    const kembalian = uangDiberikan - total;
    
    document.getElementById('kembalian').value =
        kembalian >= 0 ? `Rp ${kembalian.toLocaleString('id-ID')}` : 'Uang kurang';
}
</script>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>
</html>
