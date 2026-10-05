<?php
/**
 * Formulir barang yang dipakai bersama oleh tambah.php dan edit.php.
 * Variabel yang dibutuhkan: $barang, $errors, $kategoriList, $aksi, $labelTombol
 */
$err = function (string $field) use ($errors): string {
    return isset($errors[$field])
        ? '<p class="field-error" id="err-' . $field . '">' . e($errors[$field]) . '</p>'
        : '';
};
$invalid = function (string $field) use ($errors): string {
    return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="err-' . $field . '"' : '';
};
?>
<?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
        <?= icon('alert') ?>
        <span>Data belum tersimpan. Perbaiki <?= count($errors) ?> isian yang ditandai di bawah.</span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($aksi) ?>" class="form-card" novalidate>
    <?= csrf_field() ?>

    <fieldset>
        <legend>Identitas barang</legend>
        <p class="fieldset-desc">Kode dipakai untuk mencari barang dan tidak boleh sama dengan barang lain.</p>
        <div class="form-grid">
            <div class="field">
                <label for="kode_barang">Kode barang</label>
                <input type="text" id="kode_barang" name="kode_barang" maxlength="20"
                       value="<?= e($barang['kode_barang']) ?>" placeholder="BRG-001" required<?= $invalid('kode_barang') ?>>
                <?= $err('kode_barang') ?>
            </div>

            <div class="field">
                <label for="id_kategori">Kategori</label>
                <select id="id_kategori" name="id_kategori" required<?= $invalid('id_kategori') ?>>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= (int) $k['id_kategori'] ?>"
                            <?= (int) $barang['id_kategori'] === (int) $k['id_kategori'] ? 'selected' : '' ?>>
                            <?= e($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= $err('id_kategori') ?>
            </div>

            <div class="field field-full">
                <label for="nama_barang">Nama barang</label>
                <input type="text" id="nama_barang" name="nama_barang" maxlength="100"
                       value="<?= e($barang['nama_barang']) ?>" placeholder="Contoh: Laptop Lenovo ThinkPad E14" required<?= $invalid('nama_barang') ?>>
                <?= $err('nama_barang') ?>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Stok dan harga</legend>
        <p class="fieldset-desc">Stok <?= STOK_MINIMUM ?> atau kurang akan ditandai menipis di daftar barang.</p>
        <div class="form-grid form-grid-3">
            <div class="field">
                <label for="jumlah">Jumlah stok</label>
                <input type="number" id="jumlah" name="jumlah" min="0" step="1"
                       value="<?= e($barang['jumlah']) ?>" required<?= $invalid('jumlah') ?>>
                <?= $err('jumlah') ?>
            </div>

            <div class="field">
                <label for="satuan">Satuan</label>
                <input type="text" id="satuan" name="satuan" maxlength="20" list="daftar-satuan"
                       value="<?= e($barang['satuan']) ?>" placeholder="unit" required<?= $invalid('satuan') ?>>
                <datalist id="daftar-satuan">
                    <option value="unit"><option value="pcs"><option value="buah">
                    <option value="rim"><option value="box"><option value="set">
                </datalist>
                <?= $err('satuan') ?>
            </div>

            <div class="field">
                <label for="harga">Harga satuan</label>
                <div class="input-prefix">
                    <span aria-hidden="true">Rp</span>
                    <input type="number" id="harga" name="harga" min="0" step="1"
                           value="<?= e($barang['harga'] === '' ? '' : (float) $barang['harga']) ?>" placeholder="0" required<?= $invalid('harga') ?>>
                </div>
                <?= $err('harga') ?>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Penyimpanan</legend>
        <p class="fieldset-desc">Membantu petugas menemukan barang di gudang.</p>
        <div class="form-grid">
            <div class="field">
                <label for="lokasi">Lokasi <span class="optional">(opsional)</span></label>
                <input type="text" id="lokasi" name="lokasi" maxlength="50"
                       value="<?= e($barang['lokasi']) ?>" placeholder="Gudang A, Rak 2"<?= $invalid('lokasi') ?>>
                <?= $err('lokasi') ?>
            </div>

            <div class="field">
                <label for="tanggal_masuk">Tanggal masuk</label>
                <input type="date" id="tanggal_masuk" name="tanggal_masuk"
                       value="<?= e($barang['tanggal_masuk']) ?>" required<?= $invalid('tanggal_masuk') ?>>
                <?= $err('tanggal_masuk') ?>
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <a href="index.php" class="btn btn-ghost">Batal</a>
        <button type="submit" class="btn btn-primary"><?= icon('check') ?><?= e($labelTombol) ?></button>
    </div>
</form>
