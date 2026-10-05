<?php
/**
 * Fungsi bantu yang dipakai di seluruh halaman.
 */
session_start();

require_once __DIR__ . '/../config/database.php';

/** Batas jumlah stok yang dianggap "menipis". */
const STOK_MINIMUM = 5;

/* ---------- Keamanan output & navigasi ---------- */

/** Escape teks sebelum ditampilkan di HTML (mencegah XSS). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Pesan notifikasi (flash message) ---------- */

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------- Proteksi CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Sesi formulir sudah kedaluwarsa. Muat ulang halaman lalu kirim lagi.');
    }
}

/* ---------- Format tampilan ---------- */

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function tanggal_indo(string $tanggal): string
{
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tanggal);
    return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
}

/* ---------- Ikon SVG ---------- */

/** Mengembalikan ikon SVG inline (garis), mengikuti warna teks di sekitarnya. */
function icon(string $nama): string
{
    $paths = [
        'box'    => '<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'list'   => '<path d="M9 6h12M9 12h12M9 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
        'plus'   => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'edit'   => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4 12.5-12.5z"/>',
        'trash'  => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>',
        'layers' => '<path d="M12 2l10 5-10 5L2 7l10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>',
        'coins'  => '<circle cx="12" cy="12" r="9"/><path d="M14.5 9.5c-.5-1-1.5-1.5-2.5-1.5-1.4 0-2.5.8-2.5 2s1.1 1.6 2.5 2 2.5.8 2.5 2-1.1 2-2.5 2c-1 0-2-.5-2.5-1.5M12 6.5V8M12 16v1.5"/>',
        'alert'  => '<path d="M12 3l10 18H2L12 3z"/><path d="M12 10v5M12 18h.01"/>',
        'x'      => '<path d="M18 6L6 18M6 6l12 12"/>',
        'check'  => '<path d="M5 13l4 4L19 7"/>',
    ];

    return '<svg class="icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" '
        . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . ($paths[$nama] ?? '') . '</svg>';
}

/* ---------- Akses data ---------- */

function get_kategori(): array
{
    return db()->query('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll();
}

function find_barang(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM barang WHERE id_barang = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Membersihkan dan memvalidasi input formulir barang.
 *
 * @param array    $input    Data mentah dari $_POST
 * @param int|null $idBarang Diisi saat edit agar cek kode unik mengabaikan barang itu sendiri
 * @return array   [data bersih, daftar error per field]
 */
function validasi_barang(array $input, ?int $idBarang = null): array
{
    $data = [
        'kode_barang'   => strtoupper(trim((string) ($input['kode_barang'] ?? ''))),
        'nama_barang'   => trim((string) ($input['nama_barang'] ?? '')),
        'id_kategori'   => (int) ($input['id_kategori'] ?? 0),
        'jumlah'        => trim((string) ($input['jumlah'] ?? '')),
        'satuan'        => trim((string) ($input['satuan'] ?? '')),
        'harga'         => trim((string) ($input['harga'] ?? '')),
        'lokasi'        => trim((string) ($input['lokasi'] ?? '')),
        'tanggal_masuk' => trim((string) ($input['tanggal_masuk'] ?? '')),
    ];
    $errors = [];

    // Kode barang: wajib, format tertentu, dan unik
    if ($data['kode_barang'] === '') {
        $errors['kode_barang'] = 'Isi kode barang.';
    } elseif (!preg_match('/^[A-Z0-9-]{3,20}$/', $data['kode_barang'])) {
        $errors['kode_barang'] = 'Gunakan 3–20 karakter berupa huruf, angka, atau tanda hubung.';
    } else {
        $sql    = 'SELECT COUNT(*) FROM barang WHERE kode_barang = ?';
        $params = [$data['kode_barang']];
        if ($idBarang !== null) {
            $sql     .= ' AND id_barang <> ?';
            $params[] = $idBarang;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['kode_barang'] = 'Kode ini sudah dipakai barang lain.';
        }
    }

    if ($data['nama_barang'] === '') {
        $errors['nama_barang'] = 'Isi nama barang.';
    } elseif (mb_strlen($data['nama_barang']) > 100) {
        $errors['nama_barang'] = 'Nama barang maksimal 100 karakter.';
    }

    $idKategoriValid = array_map('intval', array_column(get_kategori(), 'id_kategori'));
    if (!in_array($data['id_kategori'], $idKategoriValid, true)) {
        $errors['id_kategori'] = 'Pilih kategori.';
    }

    if (filter_var($data['jumlah'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
        $errors['jumlah'] = 'Isi jumlah dengan bilangan bulat 0 atau lebih.';
    }

    if ($data['satuan'] === '') {
        $errors['satuan'] = 'Isi satuan, misalnya unit, pcs, atau rim.';
    } elseif (mb_strlen($data['satuan']) > 20) {
        $errors['satuan'] = 'Satuan maksimal 20 karakter.';
    }

    if (!is_numeric($data['harga']) || (float) $data['harga'] < 0) {
        $errors['harga'] = 'Isi harga dengan angka 0 atau lebih, tanpa titik pemisah.';
    }

    if (mb_strlen($data['lokasi']) > 50) {
        $errors['lokasi'] = 'Lokasi maksimal 50 karakter.';
    }

    $tgl = DateTime::createFromFormat('Y-m-d', $data['tanggal_masuk']);
    if (!$tgl || $tgl->format('Y-m-d') !== $data['tanggal_masuk']) {
        $errors['tanggal_masuk'] = 'Pilih tanggal masuk yang valid.';
    }

    return [$data, $errors];
}
