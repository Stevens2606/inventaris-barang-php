/**
 * Interaksi kecil di sisi browser:
 * 1. Dialog konfirmasi sebelum menghapus barang.
 * 2. Tombol tutup pada pesan notifikasi.
 */
document.addEventListener('DOMContentLoaded', function () {
    var dialog = document.getElementById('dialog-hapus');
    var formTarget = null;

    document.querySelectorAll('form.form-hapus').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var nama = form.dataset.nama || 'Barang ini';

            // Cadangan untuk browser lama yang belum mendukung <dialog>
            if (!dialog || typeof dialog.showModal !== 'function') {
                if (window.confirm('Hapus ' + nama + ' dari inventaris?')) {
                    form.submit();
                }
                return;
            }

            formTarget = form;
            dialog.querySelector('[data-nama]').textContent = nama;
            dialog.returnValue = '';
            dialog.showModal();
        });
    });

    if (dialog) {
        dialog.addEventListener('close', function () {
            if (dialog.returnValue === 'hapus' && formTarget) {
                formTarget.submit();
            }
            formTarget = null;
        });

        // Klik di luar kotak dialog = batal
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                dialog.close('batal');
            }
        });
    }

    document.querySelectorAll('[data-tutup]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            tombol.closest('.alert').remove();
        });
    });
});
