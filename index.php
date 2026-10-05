<?php
/**
 * READ — Daftar barang dengan pencarian, filter kategori, dan paginasi.
 */
require_once __DIR__ . '/includes/functions.php';

$cari       = trim((string) ($_GET['cari'] ?? ''));
$filterKat  = (int) ($_GET['kategori'] ?? 0);
$halaman    = max(1, (int) ($_GET['halaman'] ?? 1));
$perHalaman = 10;

// Susun kondisi WHERE secara dinamis dengan parameter terikat
$where  = [];
$params = [];
if ($cari !== '') {
    $where[]          = '(b.nama_barang LIKE :cari_nama OR b.kode_barang LIKE :cari_kode)';
    $params[':cari_nama'] = '%' . $cari . '%';
    $params[':cari_kode'] = '%' . $cari . '%';
}
if ($filterKat > 0) {
    $where[]             = 'b.id_kategori = :kategori';
    $params[':kategori'] = $filterKat;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Hitung total data untuk paginasi
$stmt = db()->prepare("SELECT COUNT(*) FROM barang b $whereSql");
$stmt->execute($params);
$totalData    = (int) $stmt->fetchColumn();
$totalHalaman = max(1, (int) ceil($totalData / $perHalaman));
$halaman      = min($halaman, $totalHalaman);
$offset       = ($halaman - 1) * $perHalaman;

// Ambil data halaman ini
$sql = "SELECT b.*, k.nama_kategori
        FROM barang b
        JOIN kategori k ON k.id_kategori = b.id_kategori
        $whereSql
        ORDER BY b.updated_at DESC, b.id_barang DESC
        LIMIT :limit OFFSET :offset";
$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perHalaman, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$daftarBarang = $stmt->fetchAll();

// Ringkasan seluruh gudang
$stmt = db()->prepare(
    'SELECT COUNT(*) AS jenis,
            COALESCE(SUM(jumlah), 0) AS unit,
            COALESCE(SUM(jumlah * harga), 0) AS nilai,
            COALESCE(SUM(jumlah <= :minimum), 0) AS menipis
     FROM barang'
);
$stmt->execute([':minimum' => STOK_MINIMUM]);
$ringkasan = $stmt->fetch();

$kategoriList = get_kategori();
$adaFilter    = $cari !== '' || $filterKat > 0;

/** Membuat URL halaman dengan tetap membawa filter yang aktif. */
function url_halaman(int $no, string $cari, int $kategori): string
{
    return 'index.php?' . http_build_query([
        'cari'     => $cari !== '' ? $cari : null,
        'kategori' => $kategori > 0 ? $kategori : null,
        'halaman'  => $no,
    ]);
}

$judulHalaman = 'Daftar barang';
require __DIR__ . '/includes/header.php';
?>

<header class="page-head">
    <div>
        <h1>Daftar barang</h1>
        <p class="lead">Pantau stok, nilai persediaan, dan barang yang perlu segera diisi ulang.</p>
    </div>
    <a href="tambah.php" class="btn btn-primary"><?= icon('plus') ?>Tambah barang</a>
</header>

<section class="stats" aria-label="Ringkasan persediaan">
    <div class="stat">
        <span class="stat-icon"><?= icon('layers') ?></span>
        <div>
            <p class="stat-label">Jenis barang</p>
            <p class="stat-value"><?= number_format((int) $ringkasan['jenis'], 0, ',', '.') ?></p>
        </div>
    </div>
    <div class="stat">
        <span class="stat-icon"><?= icon('box') ?></span>
        <div>
            <p class="stat-label">Total unit</p>
            <p class="stat-value"><?= number_format((int) $ringkasan['unit'], 0, ',', '.') ?></p>
        </div>
    </div>
    <div class="stat">
        <span class="stat-icon"><?= icon('coins') ?></span>
        <div>
            <p class="stat-label">Nilai persediaan</p>
            <p class="stat-value"><?= rupiah($ringkasan['nilai']) ?></p>
        </div>
    </div>
    <div class="stat <?= $ringkasan['menipis'] > 0 ? 'stat-warn' : '' ?>">
        <span class="stat-icon"><?= icon('alert') ?></span>
        <div>
            <p class="stat-label">Stok menipis (≤ <?= STOK_MINIMUM ?>)</p>
            <p class="stat-value"><?= (int) $ringkasan['menipis'] ?></p>
        </div>
    </div>
</section>

<section class="panel">
    <form method="get" action="index.php" class="toolbar" role="search">
        <div class="search-field">
            <?= icon('search') ?>
            <label class="sr-only" for="cari">Cari barang</label>
            <input type="search" id="cari" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama atau kode barang">
        </div>

        <label class="sr-only" for="kategori">Kategori</label>
        <select id="kategori" name="kategori">
            <option value="0">Semua kategori</option>
            <?php foreach ($kategoriList as $k): ?>
                <option value="<?= (int) $k['id_kategori'] ?>" <?= $filterKat === (int) $k['id_kategori'] ? 'selected' : '' ?>>
                    <?= e($k['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-dark">Terapkan</button>
        <?php if ($adaFilter): ?>
            <a href="index.php" class="btn btn-ghost">Hapus filter</a>
        <?php endif; ?>
    </form>

    <?php if (!$daftarBarang): ?>
        <div class="empty">
            <span class="empty-icon"><?= icon($adaFilter ? 'search' : 'box') ?></span>
            <?php if ($adaFilter): ?>
                <p class="empty-title">Tidak ada barang yang cocok</p>
                <p>Coba kata kunci lain atau tampilkan semua kategori.</p>
                <a href="index.php" class="btn btn-ghost">Hapus filter</a>
            <?php else: ?>
                <p class="empty-title">Gudang masih kosong</p>
                <p>Catat barang pertama untuk mulai mengelola stok.</p>
                <a href="tambah.php" class="btn btn-primary"><?= icon('plus') ?>Tambah barang</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Kode</th>
                    <th scope="col">Barang</th>
                    <th scope="col">Kategori</th>
                    <th scope="col" class="num">Stok</th>
                    <th scope="col" class="num">Harga satuan</th>
                    <th scope="col">Tanggal masuk</th>
                    <th scope="col"><span class="sr-only">Aksi</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($daftarBarang as $b):
                    $jumlah = (int) $b['jumlah'];
                    $status = $jumlah === 0 ? 'habis' : ($jumlah <= STOK_MINIMUM ? 'menipis' : 'aman');
                ?>
                    <tr>
                        <td class="kode"><?= e($b['kode_barang']) ?></td>
                        <td class="barang">
                            <span class="nama"><?= e($b['nama_barang']) ?></span>
                            <?php if ($b['lokasi']): ?><span class="sub"><?= e($b['lokasi']) ?></span><?php endif; ?>
                        </td>
                        <td><span class="kategori"><?= e($b['nama_kategori']) ?></span></td>
                        <td class="num">
                            <span class="stok stok-<?= $status ?>"><?= number_format($jumlah, 0, ',', '.') ?> <?= e($b['satuan']) ?></span>
                            <?php if ($status !== 'aman'): ?>
                                <span class="sub"><?= $status === 'habis' ? 'Habis' : 'Menipis' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="num"><?= rupiah($b['harga']) ?></td>
                        <td class="tanggal"><?= e(tanggal_indo($b['tanggal_masuk'])) ?></td>
                        <td class="aksi">
                            <a href="edit.php?id=<?= (int) $b['id_barang'] ?>" class="btn btn-small btn-ghost"><?= icon('edit') ?>Ubah</a>
                            <form method="post" action="hapus.php" class="form-hapus" data-nama="<?= e($b['nama_barang']) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $b['id_barang'] ?>">
                                <button type="submit" class="btn btn-small btn-danger"><?= icon('trash') ?>Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <nav class="pagination" aria-label="Halaman">
            <span>Menampilkan <?= $offset + 1 ?>–<?= $offset + count($daftarBarang) ?> dari <?= $totalData ?> barang</span>
            <?php if ($totalHalaman > 1): ?>
                <div class="pages">
                    <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                        <a href="<?= e(url_halaman($i, $cari, $filterKat)) ?>"
                           class="page-link <?= $i === $halaman ? 'active' : '' ?>"
                           <?= $i === $halaman ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
