<?php
/**
 * DELETE — Menghapus barang. Hanya menerima POST + token CSRF
 * agar data tidak bisa terhapus lewat tautan biasa.
 */
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

$id     = (int) ($_POST['id'] ?? 0);
$barang = find_barang($id);

if (!$barang) {
    set_flash('error', 'Barang tidak ditemukan. Mungkin sudah dihapus.');
    redirect('index.php');
}

$stmt = db()->prepare('DELETE FROM barang WHERE id_barang = ?');
$stmt->execute([$id]);

set_flash('success', 'Barang "' . $barang['nama_barang'] . '" dihapus.');
redirect('index.php');
