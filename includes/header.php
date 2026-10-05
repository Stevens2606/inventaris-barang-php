<?php
/** Bagian atas halaman. Set $judulHalaman sebelum meng-include file ini. */
$flash        = get_flash();
$halamanAktif = basename($_SERVER['SCRIPT_NAME']);
$menuDaftar   = in_array($halamanAktif, ['index.php', 'edit.php'], true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judulHalaman ?? 'Daftar barang') ?> | Inventaris Barang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <a href="index.php" class="brand">
            <span class="brand-mark" aria-hidden="true"><?= icon('box') ?></span>
            <span class="brand-text">Inventaris<small>Barang</small></span>
        </a>

        <nav class="sidenav" aria-label="Menu utama">
            <a href="index.php" class="<?= $menuDaftar ? 'active' : '' ?>" <?= $menuDaftar ? 'aria-current="page"' : '' ?>>
                <?= icon('list') ?><span>Daftar barang</span>
            </a>
            <a href="tambah.php" class="<?= $halamanAktif === 'tambah.php' ? 'active' : '' ?>" <?= $halamanAktif === 'tambah.php' ? 'aria-current="page"' : '' ?>>
                <?= icon('plus') ?><span>Tambah barang</span>
            </a>
        </nav>

        <p class="sidebar-foot">Sistem informasi persediaan barang kantor dan gudang.</p>
    </aside>

    <div class="main">
        <main class="content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="status">
                    <?= icon($flash['type'] === 'success' ? 'check' : 'alert') ?>
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" class="alert-close" data-tutup aria-label="Tutup pesan"><?= icon('x') ?></button>
                </div>
            <?php endif; ?>
