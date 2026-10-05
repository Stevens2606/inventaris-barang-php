<?php
/**
 * UPDATE — Mengubah data barang.
 */
require_once __DIR__ . '/includes/functions.php';

$id     = (int) ($_GET['id'] ?? 0);
$barang = find_barang($id);

if (!$barang) {
    set_flash('error', 'Barang tidak ditemukan. Mungkin sudah dihapus.');
    redirect('index.php');
}

$kategoriList = get_kategori();
$errors       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$barang, $errors] = validasi_barang($_POST, $id);

    if (!$errors) {
        $stmt = db()->prepare(
            'UPDATE barang
             SET kode_barang = :kode, nama_barang = :nama, id_kategori = :kategori, jumlah = :jumlah,
                 satuan = :satuan, harga = :harga, lokasi = :lokasi, tanggal_masuk = :tanggal
             WHERE id_barang = :id'
        );
        $stmt->execute([
            ':kode'     => $barang['kode_barang'],
            ':nama'     => $barang['nama_barang'],
            ':kategori' => $barang['id_kategori'],
            ':jumlah'   => (int) $barang['jumlah'],
            ':satuan'   => $barang['satuan'],
            ':harga'    => $barang['harga'],
            ':lokasi'   => $barang['lokasi'] !== '' ? $barang['lokasi'] : null,
            ':tanggal'  => $barang['tanggal_masuk'],
            ':id'       => $id,
        ]);

        set_flash('success', 'Perubahan pada "' . $barang['nama_barang'] . '" disimpan.');
        redirect('index.php');
    }
}

$judulHalaman = 'Ubah barang';
$aksi         = 'edit.php?id=' . $id;
$labelTombol  = 'Simpan perubahan';

require __DIR__ . '/includes/header.php';
?>
<header class="page-head">
    <div>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Daftar barang</a><span aria-hidden="true">/</span><span>Ubah barang</span>
        </nav>
        <h1>Ubah barang</h1>
        <p class="lead">Perbarui data barang dengan kode <strong><?= e($barang['kode_barang']) ?></strong>.</p>
    </div>
</header>
<?php
require __DIR__ . '/includes/form-barang.php';
require __DIR__ . '/includes/footer.php';
