-- =========================================================
-- SBS POS SCHEMA (MySQL 8.0+)
-- =========================================================
DROP DATABASE IF EXISTS sbs;
CREATE DATABASE sbs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sbs;

SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1) Master: KATEGORI
-- =========================================================
CREATE TABLE kategori (
  id_kategori VARCHAR(4) NOT NULL, -- PK, contoh: 'HB', 'EL', 'SP01'
  nama        VARCHAR(50) NOT NULL,
  created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT kategori_pkey PRIMARY KEY (id_kategori),
  CONSTRAINT kategori_id_chk CHECK (id_kategori REGEXP '^[A-Z0-9]{2,4}$'),
  CONSTRAINT kategori_nama_uq UNIQUE (nama)
) ENGINE=InnoDB;

-- =========================================================
-- 2) Master: PELANGGAN
--    id_pelanggan: 'P' + 3..6 digit (app-generated)
-- =========================================================
CREATE TABLE pelanggan (
  id_pelanggan    VARCHAR(7)   NOT NULL,
  nama            VARCHAR(100) NOT NULL,
  email           VARCHAR(100) UNIQUE,
  telepon         VARCHAR(15),
  kota            VARCHAR(50),
  alamat          TEXT,
  aktif           TINYINT(1) NOT NULL DEFAULT 1,
  trust_score     TINYINT UNSIGNED NOT NULL DEFAULT 50 CHECK (trust_score BETWEEN 0 AND 100),
  credit_limit    DECIMAL(12,0) NOT NULL DEFAULT 0 CHECK (credit_limit >= 0),
  status_kredit   ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  saldo_kredit    DECIMAL(12,0) NOT NULL DEFAULT 0 CHECK (saldo_kredit >= 0),
  created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT pelanggan_pkey PRIMARY KEY (id_pelanggan),
  CONSTRAINT pelanggan_id_chk CHECK (id_pelanggan REGEXP '^P[0-9]{3,6}$')
) ENGINE=InnoDB;

CREATE INDEX pelanggan_nama_idx ON pelanggan (nama);
CREATE INDEX pelanggan_status_kredit_idx ON pelanggan (status_kredit);
CREATE INDEX pelanggan_saldo_kredit_idx ON pelanggan (saldo_kredit);

-- =========================================================
-- 3) Master: PENGGUNA
--    id_pengguna: "NNN-INIT" (contoh: 001-ADN)
-- =========================================================
CREATE TABLE pengguna (
  id_pengguna     VARCHAR(8)   NOT NULL,
  nama            VARCHAR(100) NOT NULL,
  email           VARCHAR(100) UNIQUE,
  telepon         VARCHAR(15),
  password        VARCHAR(60),
  role            ENUM('kasir','admin') NOT NULL DEFAULT 'kasir',
  terakhir_login  TIMESTAMP NULL,
  created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT pengguna_pkey PRIMARY KEY (id_pengguna),
  CONSTRAINT pengguna_id_chk CHECK (id_pengguna REGEXP '^[0-9]{3}-[A-Z]{2,4}$')
) ENGINE=InnoDB;

CREATE INDEX pengguna_nama_idx ON pengguna (nama);

-- =========================================================
-- 4) Master: PRODUK (Surrogate PK: id_produk BIGINT)
-- =========================================================
CREATE TABLE produk ( 
  id_produk        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sku              VARCHAR(32)  NOT NULL,      -- wajib & unik (internal)
  barcode          VARCHAR(13)  NULL,          -- opsional & unik (EAN-13; boleh NULL)
  no_bpom          VARCHAR(18)  NULL,          -- opsional (boleh NULL)
  nama             VARCHAR(100) NOT NULL,
  id_kategori      VARCHAR(4)   NOT NULL,      -- FK ke kategori
  satuan           ENUM('pcs','karton','pack') NOT NULL DEFAULT 'pcs',
  isi_per_pack     INT NOT NULL DEFAULT 1 CHECK (isi_per_pack > 0), -- pcs=1; karton=isi
  harga_pack       DECIMAL(18,0) NOT NULL CHECK (harga_pack >= 0),
  harga            DECIMAL(18,0) NOT NULL CHECK (harga >= 0),
  stok             INT NOT NULL DEFAULT 0,
  created_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT produk_pkey PRIMARY KEY (id_produk),
  CONSTRAINT produk_sku_uq UNIQUE (sku),
  CONSTRAINT produk_barcode_uq UNIQUE (barcode),
  CONSTRAINT produk_kategori_fk FOREIGN KEY (id_kategori)
    REFERENCES kategori(id_kategori)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX produk_nama_idx     ON produk (nama);
CREATE INDEX produk_kategori_idx ON produk (id_kategori);

-- =========================================================
-- 5) KONVERSI STOK (buka karton -> pcs)
-- =========================================================
CREATE TABLE konversi_stok (
  id_konversi    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  from_produk_id BIGINT UNSIGNED NOT NULL,  -- produk KARTON
  to_produk_id   BIGINT UNSIGNED NOT NULL,  -- produk PCS
  rasio          INT NOT NULL CHECK (rasio > 0),     -- contoh 12 (1 KRT = 12 PCS)
  qty_from       INT NOT NULL CHECK (qty_from > 0),  -- berapa karton dipecah
  qty_to         INT NOT NULL CHECK (qty_to > 0),    -- qty_from * rasio
  keterangan     VARCHAR(200),
  created_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT konversi_pkey PRIMARY KEY (id_konversi),
  CONSTRAINT konversi_from_fk FOREIGN KEY (from_produk_id) REFERENCES produk(id_produk)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT konversi_to_fk   FOREIGN KEY (to_produk_id)   REFERENCES produk(id_produk)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Opsional: VIEW stok setara pcs
CREATE OR REPLACE VIEW v_produk_stok_setara_pcs AS
SELECT
  p.id_produk, p.sku, p.nama, p.id_kategori, p.satuan, p.isi_per_pack, p.stok,
  CASE WHEN p.satuan='kar ton' THEN p.stok * p.isi_per_pack ELSE p.stok END AS stok_setara_pcs
FROM produk p;

-- =========================================================
-- 6) TRANSAKSI (Header)
--    nomor_transaksi: INV-YYYY-MM-SEQ-Pxxxxxx (app-generated)
--    memuat ringkasan "cicilan pintar" (kredit)
-- =========================================================
CREATE TABLE transaksi (
  nomor_transaksi   VARCHAR(40) NOT NULL,
  id_pelanggan      VARCHAR(7)  NOT NULL,
  id_kasir          VARCHAR(8)  NOT NULL,
  tanggal           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_item        INT NOT NULL DEFAULT 0, 
  subtotal          DECIMAL(18,0) NOT NULL DEFAULT 0,
  diskon            DECIMAL(18,0) NOT NULL DEFAULT 0,
  pajak             DECIMAL(18,0) NOT NULL DEFAULT 0,
  biaya_pengiriman  DECIMAL(18,0) NOT NULL DEFAULT 0,
  total             DECIMAL(18,0) NOT NULL DEFAULT 0,
  metode_bayar      ENUM('TUNAI','QRIS','TRANSFER BCA','KREDIT') NOT NULL DEFAULT 'TUNAI',
  status_pembayaran ENUM('MENUNGGU','LUNAS','BATAL') NOT NULL DEFAULT 'MENUNGGU',
  paid_at           TIMESTAMP NULL,
  -- Cicilan Pintar (jika metode_bayar='KREDIT')
  jenis_transaksi   ENUM('TUNAI','KREDIT') NOT NULL DEFAULT 'TUNAI',
  dp                DECIMAL(12,0) NOT NULL DEFAULT 0 CHECK (dp >= 0),
  tenor_bulan       TINYINT UNSIGNED DEFAULT NULL CHECK (tenor_bulan BETWEEN 1 AND 24),
  bunga_persen      DECIMAL(5,0) NOT NULL DEFAULT 0 CHECK (bunga_persen >= 0),
  cicilan_bulanan   DECIMAL(12,0) DEFAULT NULL CHECK (cicilan_bulanan >= 0),
  ar_status         ENUM('NA','AKTIF','LUNAS','GAGAL','BATAL') NOT NULL DEFAULT 'NA',
  id_kontrak        BIGINT UNSIGNED NULL,

  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT transaksi_pkey PRIMARY KEY (nomor_transaksi),
  CONSTRAINT transaksi_no_chk CHECK (nomor_transaksi REGEXP '^INV-[0-9]{4}-[0-9]{2}-[0-9]{3}-P[0-9]{3,6}$'),
  CONSTRAINT transaksi_dp_chk CHECK (dp >= 0),
  CONSTRAINT transaksi_tenor_chk CHECK (tenor_bulan IS NULL OR tenor_bulan BETWEEN 1 AND 24),
  CONSTRAINT transaksi_bunga_chk CHECK (bunga_persen >= 0),
  CONSTRAINT transaksi_cicilan_chk CHECK (cicilan_bulanan IS NULL OR cicilan_bulanan >= 0),
  CONSTRAINT transaksi_pelanggan_fk FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT transaksi_kasir_fk FOREIGN KEY (id_kasir) REFERENCES pengguna(id_pengguna)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX transaksi_pelanggan_idx ON transaksi (id_pelanggan);
CREATE INDEX transaksi_kasir_idx     ON transaksi (id_kasir);
CREATE INDEX transaksi_tanggal_idx   ON transaksi (tanggal);
CREATE INDEX transaksi_status_idx    ON transaksi (status_pembayaran);
CREATE INDEX transaksi_jenis_idx     ON transaksi (jenis_transaksi);
CREATE INDEX transaksi_ar_status_idx ON transaksi (ar_status);

-- =========================================================
-- 7) TRANSAKSI DETAIL (Line Items)
-- =========================================================
CREATE TABLE transaksi_detail (
  id_detail           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor_transaksi     VARCHAR(40) NOT NULL,
  id_produk           BIGINT UNSIGNED NOT NULL,   -- FK ke produk.id_produk
  nama_produk         VARCHAR(255) NOT NULL,      -- denormalisasi utk jejak tampilan
  harga_satuan        DECIMAL(18,0) NOT NULL,
  jumlah              INT NOT NULL CHECK (jumlah > 0),
  mode_qty            ENUM('unit','pack') NOT NULL DEFAULT 'unit',
  diskon_item         DECIMAL(18,0) NOT NULL DEFAULT 0,
  subtotal            DECIMAL(18,0) NOT NULL,
  created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT transaksi_detail_pkey PRIMARY KEY (id_detail),
  CONSTRAINT td_transaksi_fk FOREIGN KEY (nomor_transaksi) REFERENCES transaksi(nomor_transaksi)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT td_produk_fk FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX td_transaksi_idx ON transaksi_detail (nomor_transaksi);
CREATE INDEX td_produk_idx    ON transaksi_detail (id_produk);

-- =========================================================
-- 8) KONTRAK KREDIT (inti Cicilan Pintar) - satu kontrak per transaksi kredit
-- =========================================================
CREATE TABLE kontrak_kredit (
  id_kontrak        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor_kontrak     VARCHAR(30) NOT NULL,
  id_pelanggan      VARCHAR(7)  NOT NULL,
  nomor_transaksi   VARCHAR(40) NOT NULL,
  mulai_kontrak     DATE NOT NULL,
  tenor_bulan       TINYINT UNSIGNED NOT NULL CHECK (tenor_bulan BETWEEN 1 AND 24),
  pokok_pinjaman    DECIMAL(12,0) NOT NULL CHECK (pokok_pinjaman >= 0),
  dp                DECIMAL(12,0) NOT NULL DEFAULT 0 CHECK (dp >= 0),
  bunga_persen      DECIMAL(5,0) NOT NULL DEFAULT 0 CHECK (bunga_persen >= 0),
  cicilan_bulanan   DECIMAL(12,0) NOT NULL CHECK (cicilan_bulanan >= 0),
  status            ENUM('AKTIF','LUNAS','TUNDA','GAGAL') NOT NULL DEFAULT 'AKTIF',
  score_snapshot    TINYINT UNSIGNED NOT NULL DEFAULT 50,
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT kontrak_pkey PRIMARY KEY (id_kontrak),
  CONSTRAINT kontrak_no_uq UNIQUE (nomor_kontrak),
  CONSTRAINT kontrak_pelanggan_fk FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT kontrak_transaksi_fk FOREIGN KEY (nomor_transaksi) REFERENCES transaksi(nomor_transaksi)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX kontrak_status_idx ON kontrak_kredit (status);
CREATE INDEX kontrak_mulai_idx  ON kontrak_kredit (mulai_kontrak);

-- Relasi balik ke transaksi.id_kontrak (dibuat setelah kontrak_kredit ada)
ALTER TABLE transaksi
  ADD CONSTRAINT transaksi_kontrak_fk
  FOREIGN KEY (id_kontrak) REFERENCES kontrak_kredit(id_kontrak)
  ON UPDATE CASCADE ON DELETE SET NULL;

-- =========================================================
-- 9) JADWAL ANGSURAN (1 baris per periode)
-- =========================================================
CREATE TABLE jadwal_angsuran (
  id_angsuran     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_kontrak      BIGINT UNSIGNED NOT NULL,
  periode_ke      TINYINT UNSIGNED NOT NULL CHECK (periode_ke >= 1),
  jatuh_tempo     DATE NOT NULL,
  jumlah_tagihan  DECIMAL(12,0) NOT NULL CHECK (jumlah_tagihan >= 0),
  jumlah_dibayar  DECIMAL(12,0) NOT NULL DEFAULT 0 CHECK (jumlah_dibayar >= 0),
  status          ENUM('DUE','PAID','LATE','VOID') NOT NULL DEFAULT 'DUE',
  paid_at         TIMESTAMP NULL,
  created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT jadwal_pkey PRIMARY KEY (id_angsuran),
  CONSTRAINT jadwal_kontrak_fk FOREIGN KEY (id_kontrak) REFERENCES kontrak_kredit(id_kontrak)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE UNIQUE INDEX jadwal_unq            ON jadwal_angsuran (id_kontrak, periode_ke);
CREATE INDEX        jadwal_jatuh_tempo_idx ON jadwal_angsuran (jatuh_tempo, status);

-- =========================================================
-- 10) PEMBAYARAN (unified: for transactions & credit balance payments)
--     id_pembayaran: PAY-YYYYMMDD-0000001 (app-generated)
--     tipe_pembayaran: 'transaksi' (pembayaran transaksi) or 'kredit' (pembayaran saldo kredit)
-- =========================================================
CREATE TABLE pembayaran (
  id_pembayaran     VARCHAR(32) NOT NULL,
  id_transaksi      VARCHAR(40) NULL,              -- FK to transaksi (NULL for kredit type)
  id_angsuran       BIGINT UNSIGNED NULL,          -- FK to jadwal_angsuran (NULL for kredit type)
  id_pelanggan      VARCHAR(7)  NULL,              -- FK to pelanggan (for kredit type)
  id_kasir          VARCHAR(8)  NULL,              -- FK to pengguna (for kredit type)
  metode            ENUM('tunai','transfer','cek','QRIS','TRANSFER BCA','KREDIT') NOT NULL,
  tipe_pembayaran   ENUM('transaksi','kredit') NOT NULL DEFAULT 'transaksi',
  jumlah            DECIMAL(18,0) NOT NULL,
  tanggal           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  keterangan        VARCHAR(255) NULL,
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT pembayaran_pkey PRIMARY KEY (id_pembayaran),
  CONSTRAINT pembayaran_id_chk CHECK (id_pembayaran REGEXP '^PAY-[0-9]{8}-[0-9]{7}$'),
  CONSTRAINT pembayaran_jumlah_chk CHECK (jumlah > 0),
  
  -- For transaction payments
  CONSTRAINT pembayaran_transaksi_fk FOREIGN KEY (id_transaksi) REFERENCES transaksi(nomor_transaksi)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT pembayaran_angsuran_fk FOREIGN KEY (id_angsuran) REFERENCES jadwal_angsuran(id_angsuran)
    ON UPDATE CASCADE ON DELETE SET NULL,
  
  -- For credit balance payments
  CONSTRAINT pembayaran_pelanggan_fk FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT pembayaran_kasir_fk FOREIGN KEY (id_kasir) REFERENCES pengguna(id_pengguna)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX pembayaran_trx_idx           ON pembayaran (id_transaksi);
CREATE INDEX pembayaran_tanggal_idx       ON pembayaran (tanggal);
CREATE INDEX pembayaran_angsuran_idx      ON pembayaran (id_angsuran);
CREATE INDEX pembayaran_pelanggan_idx     ON pembayaran (id_pelanggan);
CREATE INDEX pembayaran_tipe_idx          ON pembayaran (tipe_pembayaran);

-- =========================================================
-- VIEWS
-- =========================================================

-- View: Piutang Pelanggan (Outstanding Credit)
CREATE OR REPLACE VIEW v_piutang_pelanggan AS
SELECT
  k.id_pelanggan,
  SUM(j.jumlah_tagihan - j.jumlah_dibayar) AS saldo_piutang
FROM kontrak_kredit k
JOIN jadwal_angsuran j ON j.id_kontrak = k.id_kontrak
WHERE k.status IN ('AKTIF','TUNDA')
  AND j.status IN ('DUE','LATE')
GROUP BY k.id_pelanggan;

SET FOREIGN_KEY_CHECKS = 1;
