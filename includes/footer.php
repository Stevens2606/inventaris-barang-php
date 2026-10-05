        </main>

        <footer class="site-footer">
            Sistem Informasi Inventaris Barang &copy; <?= date('Y') ?>. Dibangun dengan PHP dan MariaDB.
        </footer>
    </div>
</div>

<dialog id="dialog-hapus" class="dialog" aria-labelledby="dialog-hapus-judul">
    <form method="dialog">
        <span class="dialog-icon"><?= icon('trash') ?></span>
        <h2 id="dialog-hapus-judul">Hapus barang ini?</h2>
        <p><strong data-nama></strong> akan dihapus dari inventaris dan tidak bisa dikembalikan.</p>
        <div class="dialog-actions">
            <button value="batal" class="btn btn-ghost">Batal</button>
            <button value="hapus" class="btn btn-danger-solid"><?= icon('trash') ?>Hapus barang</button>
        </div>
    </form>
</dialog>

<script src="assets/js/app.js?v=2"></script>
</body>
</html>
