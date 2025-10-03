-- ======================================================================
-- SBS POS SCHEMA (MySQL 8.0+)
-- ======================================================================
-- Database
CREATE DATABASE IF NOT EXISTS `sbs`
USE `sbs`;

-- ======================================================================
-- 1) Master: KATEGORI
-- id_kategori: AUTO_INCREMENT smallint (cukup angka)
-- ======================================================================
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`        VARCHAR(50)      NOT NULL,
  `created_at`  TIMESTAMP         NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP         NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `kategori_pkey` PRIMARY KEY (`id_kategori`),
  CONSTRAINT `kategori_nama_uq` UNIQUE (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================================
-- 2) Master: PELANGGAN
-- id_pelanggan: format P001..P999999 (P + 3..6 digit), aplikasi yang generate
-- contoh: P001, P010, P1000, P999999
-- ======================================================================
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `id_pelanggan`   VARCHAR(7)     NOT NULL,
  `nama`           VARCHAR(100)   NOT NULL,
  `email`          VARCHAR(100)            UNIQUE,
  `telepon`        VARCHAR(15),
  `kota`           VARCHAR(50),
  `alamat`         TEXT,
  `aktif`          TINYINT(1)     NOT NULL DEFAULT 1,
  `tanggal_daftar` DATE                    DEFAULT (CURRENT_DATE),
  `created_at`     TIMESTAMP       NULL    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NULL    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `pelanggan_pkey` PRIMARY KEY (`id_pelanggan`),
  CONSTRAINT `pelanggan_id_chk` CHECK (`id_pelanggan` REGEXP '^P[0-9]{3,6}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `pelanggan_nama_idx` ON `pelanggan` (`nama`);

-- ======================================================================
-- 3) Master: PENGGUNA
-- id_pengguna: "NNN-INIT" (3 digit nomor urut + '-' + 2..4 huruf kapital)
-- contoh: 001-ADN (angka "001-" digenerate sistem; "ADEN" diisi manual)
-- ======================================================================
CREATE TABLE IF NOT EXISTS `pengguna` (
  `id_pengguna`    VARCHAR(8)     NOT NULL, -- max "999-AAAA" = 8 char
  `nama`           VARCHAR(100)   NOT NULL,
  `email`          VARCHAR(100)            UNIQUE,
  `telepon`        VARCHAR(15),
  `password`     VARCHAR(60),
  `role`           ENUM('kasir','admin') NOT NULL DEFAULT 'kasir',
  `terakhir_login` TIMESTAMP NULL,
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `pengguna_pkey` PRIMARY KEY (`id_pengguna`),
  CONSTRAINT `pengguna_id_chk` CHECK (`id_pengguna` REGEXP '^[0-9]{3}-[A-Z]{2,4}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `pengguna_nama_idx` ON `pengguna` (`nama`);

-- ======================================================================
-- 4) Master: PRODUK
-- id_produk: EAN-13 → CHAR(13) digit saja
-- ======================================================================
CREATE TABLE IF NOT EXISTS `produk` (
  `id_produk`          CHAR(13)            NOT NULL,
  `nama`               VARCHAR(100)        NOT NULL,
  `gambar`             VARCHAR(255),
  `nomor_bpom`         VARCHAR(50),

  `harga`              DECIMAL(18,2)       NOT NULL CHECK (`harga` >= 0),
  `biaya_produk`       DECIMAL(18,2)       NOT NULL DEFAULT 0 CHECK (`biaya_produk` >= 0),

  `stok`               INT                 NOT NULL DEFAULT 0,
  `batas_stok`         INT                 NOT NULL DEFAULT 0,

  `satuan`             VARCHAR(32)                  DEFAULT 'pcs',
  `satuan_pack`        VARCHAR(32)                  DEFAULT 'karton',
  `isi_per_pack`       INT                 NOT NULL DEFAULT 1,
  `harga_pack`         DECIMAL(18,2),

  -- Bagian harga bertingkat / diskon
  `min_beli_diskon`    INT,                -- minimal jumlah beli untuk diskon
  `harga_diskon_unit`  DECIMAL(18,2),      -- harga per unit saat diskon
  `harga_diskon_pack`  DECIMAL(18,2),      -- harga per pack saat diskon

  `created_at`         TIMESTAMP NULL     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  `id_kategori`        SMALLINT UNSIGNED  NOT NULL,

  CONSTRAINT `produk_pkey`        PRIMARY KEY (`id_produk`),
  CONSTRAINT `produk_id_chk`      CHECK (`id_produk` REGEXP '^[0-9]{13}$'),
  CONSTRAINT `produk_kategori_fk` FOREIGN KEY (`id_kategori`) 
      REFERENCES `kategori`(`id_kategori`)
      ON UPDATE CASCADE ON DELETE RESTRICT,

  INDEX `produk_nama_idx` (`nama`),
  INDEX `produk_kategori_idx` (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX `produk_stok_idx` ON `produk` (`stok`);

-- ======================================================================
-- 5) Transaksi Header
-- nomor_transaksi: INV-YYYY-MM-SEQ-Pxxxxxx (dinamis; aplikasi yang generate)
-- contoh: INV-2025-05-001-P000001
-- metode_bayar diselaraskan dengan channel Midtrans umum (tunai + non-tunai)
-- Midtrans fields disediakan (order_id, transaction_id, status, payment_type, VA, response snapshot)
-- ======================================================================
CREATE TABLE IF NOT EXISTS `transaksi` (
  `nomor_transaksi`        VARCHAR(40)    NOT NULL,
  `id_pelanggan`           VARCHAR(7)     NOT NULL,
  `id_kasir`               VARCHAR(8)     NOT NULL,
  `tanggal`                TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_item`             INT            NOT NULL DEFAULT 0,
  `subtotal`               DECIMAL(18,2)  NOT NULL DEFAULT 0,
  `diskon`                 DECIMAL(18,2)  NOT NULL DEFAULT 0,
  `pajak`                  DECIMAL(18,2)  NOT NULL DEFAULT 0,
  `biaya_pengiriman`       DECIMAL(18,2)  NOT NULL DEFAULT 0,
  `total`                  DECIMAL(18,2)  NOT NULL DEFAULT 0,

  `metode_bayar` ENUM(
    'TUNAI','QRIS',
    'VA_BCA','VA_BNI','VA_BRI','VA_PERMATA','VA_MANDIRI',
    'GOPAY','OVO','DANA','LINKAJA','SHOPEEPAY',
    'CREDIT_CARD','MANUAL_TRANSFER'
  ) NOT NULL DEFAULT 'TUNAI',

  `status_pembayaran` ENUM('PENDING','PAID','FAILED','VOID','EXPIRED','REFUND_PARTIAL','REFUNDED')
    NOT NULL DEFAULT 'PENDING',

  `paid_at`                TIMESTAMP NULL,

  -- Midtrans bindings
  `midtrans_order_id`        VARCHAR(64),
  `midtrans_transaction_id`  VARCHAR(64),
  `midtrans_status`          VARCHAR(64),
  `midtrans_payment_type`    VARCHAR(64),
  `midtrans_va_numbers`      JSON,             -- array VA number jika ada
  `midtrans_gross_amount`    DECIMAL(18,2),
  `midtrans_response`        JSON,             -- simpan snapshot response Midtrans

  `is_locked`              TINYINT(1)   NOT NULL DEFAULT 0,
  `locked_at`              TIMESTAMP NULL,

  `created_at`             TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT `transaksi_pkey` PRIMARY KEY (`nomor_transaksi`),
  CONSTRAINT `transaksi_no_chk` CHECK (`nomor_transaksi` REGEXP '^INV-[0-9]{4}-[0-9]{2}-[0-9]{3}-P[0-9]{3,6}$'),
  CONSTRAINT `transaksi_pelanggan_fk` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan`(`id_pelanggan`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `transaksi_kasir_fk` FOREIGN KEY (`id_kasir`) REFERENCES `pengguna`(`id_pengguna`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `transaksi_pelanggan_idx` ON `transaksi` (`id_pelanggan`);
CREATE INDEX `transaksi_kasir_idx`     ON `transaksi` (`id_kasir`);
CREATE INDEX `transaksi_tanggal_idx`   ON `transaksi` (`tanggal`);
CREATE INDEX `transaksi_status_idx`    ON `transaksi` (`status_pembayaran`);

-- ======================================================================
-- 6) Transaksi Detail (line items)
-- id_detail: AUTO_INCREMENT (cukup angka)
-- id_produk: EAN-13 (FK ke produk)
-- mode_qty: 'unit' atau 'pack'
-- pack_size_snapshot: menyimpan snapshot pack_size saat transaksi
-- ======================================================================
CREATE TABLE IF NOT EXISTS `transaksi_detail` (
  `id_detail`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `nomor_transaksi`      VARCHAR(40)      NOT NULL,
  `id_produk`            CHAR(13)         NOT NULL,
  `nama_produk`          VARCHAR(255)     NOT NULL,
  `harga_satuan`         DECIMAL(18,2)    NOT NULL,
  `jumlah`               INT              NOT NULL,
  `mode_qty`             ENUM('unit','pack') NOT NULL DEFAULT 'unit',
  `pack_size_snapshot`   INT              NOT NULL DEFAULT 1,
  `diskon_item`          DECIMAL(18,2)    NOT NULL DEFAULT 0,
  `subtotal`             DECIMAL(18,2)    NOT NULL,

  `created_at`           TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT `transaksi_detail_pkey` PRIMARY KEY (`id_detail`),
  CONSTRAINT `td_jumlah_chk` CHECK (`jumlah` > 0),
  CONSTRAINT `td_packsize_chk` CHECK (`pack_size_snapshot` > 0),
  CONSTRAINT `td_produk_chk` CHECK (`id_produk` REGEXP '^[0-9]{13}$'),
  CONSTRAINT `td_transaksi_fk` FOREIGN KEY (`nomor_transaksi`) REFERENCES `transaksi`(`nomor_transaksi`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `td_produk_fk` FOREIGN KEY (`id_produk`) REFERENCES `produk`(`id_produk`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `td_transaksi_idx` ON `transaksi_detail` (`nomor_transaksi`);
CREATE INDEX `td_produk_idx`    ON `transaksi_detail` (`id_produk`);

-- ======================================================================
-- 7) Pembayaran
-- id_pembayaran: PAY-YYYYMMDD-0000001 (aplikasi yang generate)
-- Boleh ada banyak pembayaran per transaksi (split payment)
-- Disertakan snapshot Midtrans di sini juga
-- ======================================================================
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id_pembayaran`          VARCHAR(32)   NOT NULL,
  `id_transaksi`           VARCHAR(40)   NOT NULL,
  `metode`                 ENUM(
    'TUNAI','QRIS',
    'VA_BCA','VA_BNI','VA_BRI','VA_PERMATA','VA_MANDIRI',
    'GOPAY','OVO','DANA','LINKAJA','SHOPEEPAY',
    'CREDIT_CARD','MANUAL_TRANSFER'
  ) NOT NULL,
  `jumlah`                 DECIMAL(18,2) NOT NULL,
  `tanggal`                TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `keterangan`             VARCHAR(255),

  -- Midtrans bindings (per payment)
  `midtrans_transaction_id` VARCHAR(64),
  `midtrans_status`         VARCHAR(64),
  `midtrans_payment_type`   VARCHAR(64),
  `midtrans_response`       JSON,

  CONSTRAINT `pembayaran_pkey` PRIMARY KEY (`id_pembayaran`),
  CONSTRAINT `pembayaran_id_chk` CHECK (`id_pembayaran` REGEXP '^PAY-[0-9]{8}-[0-9]{7}$'),
  CONSTRAINT `pembayaran_transaksi_fk` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi`(`nomor_transaksi`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `pembayaran_jumlah_chk` CHECK (`jumlah` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `pembayaran_trx_idx`   ON `pembayaran` (`id_transaksi`);
CREATE INDEX `pembayaran_tanggal_idx` ON `pembayaran` (`tanggal`);

-- ======================================================================
-- Fin
-- ======================================================================
SET FOREIGN_KEY_CHECKS = 1;
