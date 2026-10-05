<?php
/**
 * CREATE — Menambah barang baru.
 */
require_once __DIR__ . '/includes/functions.php';

$kategoriList = get_kategori();
$errors       = [];
$barang       = [
    'kode_barang'   => '',
    'nama_barang'   => '',
    'id_kategori'   => 0,
    'jumlah'        => '0',
    'satuan'        => '',
    'harga'         => '',
    'lokasi'        => '',
    'tanggal_masuk' => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$barang, $errors] = validasi_barang($_POST);

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO barang (kode_barang, nama_barang, id_kategori, jumlah, satuan, harga, lokasi, tanggal_masuk)
             VALUES (:kode, :nama, :kategori, :jumlah, :satuan, :harga, :lokasi, :tanggal)'
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
        ]);

        set_flash('success', 'Barang "' . $barang['nama_barang'] . '" ditambahkan.');
        redirect('index.php');
    }
}

$judulHalaman = 'Tambah barang';
$aksi         = 'tambah.php';
$labelTombol  = 'Simpan barang';

require __DIR__ . '/includes/header.php';
?>
<header class="page-head">
    <div>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Daftar barang</a><span aria-hidden="true">/</span><span>Tambah barang</span>
        </nav>
        <h1>Tambah barang</h1>
        <p class="lead">Lengkapi data di bawah untuk mencatat barang baru ke inventaris.</p>
    </div>
</header>
<?php
require __DIR__ . '/includes/form-barang.php';
require __DIR__ . '/includes/footer.php';
