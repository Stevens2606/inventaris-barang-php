-- =========================================================
-- Database : db_inventaris
-- Aplikasi : Sistem Informasi Inventaris Barang
-- DBMS     : MariaDB 10.x / MySQL 5.7+
--
-- Cara pakai:
--   1. Buat database kosong bernama db_inventaris di phpMyAdmin
--      (di InfinityFree namanya otomatis berawalan, mis. if0_12345678_inventaris).
--   2. Pilih database tersebut, buka tab Import, lalu unggah file ini.
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS kategori;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- Tabel kategori (master data)
-- ---------------------------------------------------------
CREATE TABLE kategori (
    id_kategori   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_kategori VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id_kategori),
    UNIQUE KEY uk_nama_kategori (nama_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Tabel barang (data transaksi utama)
-- ---------------------------------------------------------
CREATE TABLE barang (
    id_barang     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    kode_barang   VARCHAR(20)   NOT NULL,
    nama_barang   VARCHAR(100)  NOT NULL,
    id_kategori   INT UNSIGNED  NOT NULL,
    jumlah        INT UNSIGNED  NOT NULL DEFAULT 0,
    satuan        VARCHAR(20)   NOT NULL,
    harga         DECIMAL(12,2) NOT NULL DEFAULT 0,
    lokasi        VARCHAR(50)   NULL,
    tanggal_masuk DATE          NOT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_barang),
    UNIQUE KEY uk_kode_barang (kode_barang),
    KEY idx_nama_barang (nama_barang),
    CONSTRAINT fk_barang_kategori FOREIGN KEY (id_kategori)
        REFERENCES kategori (id_kategori)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Data contoh
-- ---------------------------------------------------------
INSERT INTO kategori (nama_kategori) VALUES
    ('Elektronik'),
    ('Alat Tulis Kantor'),
    ('Furnitur'),
    ('Perlengkapan Kebersihan'),
    ('Jaringan');

INSERT INTO barang (kode_barang, nama_barang, id_kategori, jumlah, satuan, harga, lokasi, tanggal_masuk) VALUES
    ('ELK-001', 'Laptop Lenovo ThinkPad E14',     1,  8, 'unit',  9850000, 'Gudang A, Rak 1', '2026-07-14'),
    ('ELK-002', 'Proyektor Epson EB-X51',         1,  2, 'unit',  6200000, 'Gudang A, Rak 1', '2026-07-20'),
    ('ELK-003', 'Printer Canon PIXMA G2020',      1,  0, 'unit',  2150000, 'Gudang A, Rak 2', '2026-06-02'),
    ('ATK-001', 'Kertas HVS A4 70 gram',          2, 45, 'rim',     52000, 'Gudang B, Rak 3', '2026-08-01'),
    ('ATK-002', 'Pulpen Standard AE7 Hitam',      2, 120, 'pcs',     2500, 'Gudang B, Rak 3', '2026-08-01'),
    ('ATK-003', 'Map Plastik Kancing Folio',      2,  4, 'pcs',      4000, 'Gudang B, Rak 4', '2026-08-10'),
    ('FUR-001', 'Kursi Kerja Ergonomis',          3, 15, 'unit',  1250000, 'Gudang C',        '2026-05-18'),
    ('FUR-002', 'Meja Rapat Lipat 180 cm',        3,  6, 'unit',  1750000, 'Gudang C',        '2026-05-18'),
    ('KBR-001', 'Cairan Pembersih Lantai 5 L',    4,  9, 'jerigen',  85000, 'Gudang B, Rak 6', '2026-09-03'),
    ('JRG-001', 'Router WiFi TP-Link Archer C6',  5,  3, 'unit',   520000, 'Gudang A, Rak 2', '2026-09-12'),
    ('JRG-002', 'Kabel UTP Cat6 Belden 305 m',    5,  5, 'roll',  2400000, 'Gudang A, Rak 5', '2026-09-12');
