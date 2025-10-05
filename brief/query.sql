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
  id_kategori SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama        VARCHAR(50) NOT NULL,
  created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT kategori_pkey PRIMARY KEY (id_kategori),
  CONSTRAINT kategori_nama_uq UNIQUE (nama)
) ENGINE=InnoDB;

-- =========================================================
-- 2) Master: PELANGGAN (dengan field ringan untuk Cicilan Pintar)
-- id_pelanggan: P + 3..6 digit (app yang generate)
-- =========================================================
CREATE TABLE pelanggan (
  id_pelanggan    VARCHAR(7)  NOT NULL,
  nama            VARCHAR(100) NOT NULL,
  email           VARCHAR(100) UNIQUE,
  telepon         VARCHAR(15),
  kota            VARCHAR(50),
  alamat          TEXT,
  aktif           TINYINT(1) NOT NULL DEFAULT 1,
  trust_score     TINYINT UNSIGNED NOT NULL DEFAULT 50 CHECK (trust_score BETWEEN 0 AND 100),
  credit_limit    DECIMAL(12) NOT NULL DEFAULT 0 CHECK (credit_limit >= 0),
  created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT pelanggan_pkey PRIMARY KEY (id_pelanggan),
  CONSTRAINT pelanggan_id_chk CHECK (id_pelanggan REGEXP '^P[0-9]{3,6}$')
) ENGINE=InnoDB;

CREATE INDEX pelanggan_nama_idx ON pelanggan (nama);

-- =========================================================
-- 3) Master: PENGGUNA
-- id_pengguna: "NNN-INIT" (contoh: 001-ADN)
-- =========================================================
CREATE TABLE pengguna (
  id_pengguna     VARCHAR(8)  NOT NULL,
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
-- 4) Master: PRODUK (EAN-13 sebagai PK)
-- =========================================================
CREATE TABLE produk (
  id_produk        CHAR(13) NOT NULL,
  nama             VARCHAR(100) NOT NULL,
  gambar           VARCHAR(255),
  nomor_bpom       VARCHAR(50),
  harga            DECIMAL(18) NOT NULL CHECK (harga >= 0),
  biaya_produk     DECIMAL(18) NOT NULL DEFAULT 0 CHECK (biaya_produk >= 0),
  stok             INT NOT NULL DEFAULT 0,
  batas_stok       INT NOT NULL DEFAULT 0,
  satuan           VARCHAR(32) DEFAULT 'pcs',
  satuan_pack      VARCHAR(32) DEFAULT 'karton',
  isi_per_pack     INT NOT NULL DEFAULT 1 CHECK (isi_per_pack > 0),
  harga_pack       DECIMAL(18),
  min_beli_diskon  INT,
  harga_diskon_unit DECIMAL(18),
  harga_diskon_pack DECIMAL(18),
  id_kategori      SMALLINT UNSIGNED NOT NULL,
  created_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT produk_pkey PRIMARY KEY (id_produk),
  CONSTRAINT produk_id_chk CHECK (id_produk REGEXP '^[0-9]{13}$'),
  CONSTRAINT produk_kategori_fk FOREIGN KEY (id_kategori)
    REFERENCES kategori(id_kategori)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX produk_nama_idx ON produk (nama);
CREATE INDEX produk_kategori_idx ON produk (id_kategori);
CREATE INDEX produk_stok_idx ON produk (stok);

-- =========================================================
-- 5) TRANSAKSI (Header)
-- nomor_transaksi: INV-YYYY-MM-SEQ-Pxxxxxx (app generate)
-- Mencakup tanda kredit + parameter ringkas cicilan
-- =========================================================
CREATE TABLE transaksi (
  nomor_transaksi   VARCHAR(40) NOT NULL,
  id_pelanggan      VARCHAR(7)  NOT NULL,
  id_kasir          VARCHAR(8)  NOT NULL,
  tanggal           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_item        INT NOT NULL DEFAULT 0, 
  subtotal          DECIMAL(18) NOT NULL DEFAULT 0,
  diskon            DECIMAL(18) NOT NULL DEFAULT 0,
  pajak             DECIMAL(18) NOT NULL DEFAULT 0,
  biaya_pengiriman  DECIMAL(18) NOT NULL DEFAULT 0,
  total             DECIMAL(18) NOT NULL DEFAULT 0,
  metode_bayar ENUM(
    'TUNAI','QRIS','SHOPEEPAY', 
  ) NOT NULL DEFAULT 'TUNAI',
  status_pembayaran ENUM(
  'MENUNGGU',
  'LUNAS',       
  'GAGAL',
  'BATAL',
  'REFUND_SEBAGIAN',
  'REFUND_PENUH'    
)
NOT NULL DEFAULT 'MENUNGGU',
  paid_at           TIMESTAMP NULL,
  -- Cicilan Pintar (ringkas)
  jenis_transaksi   ENUM('TUNAI','KREDIT') NOT NULL DEFAULT 'TUNAI',
  dp                DECIMAL(12) NOT NULL DEFAULT 0 CHECK (dp >= 0),
  tenor_bulan       TINYINT UNSIGNED DEFAULT NULL CHECK (tenor_bulan BETWEEN 1 AND 24),
  bunga_persen      DECIMAL(5) NOT NULL DEFAULT 0 CHECK (bunga_persen >= 0),
  cicilan_bulanan   DECIMAL(12) DEFAULT NULL CHECK (cicilan_bulanan >= 0),
  ar_status         ENUM('NA','AKTIF','LUNAS','GAGAL','BATAL') NOT NULL DEFAULT 'NA',
  id_kontrak        BIGINT UNSIGNED NULL,

  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT transaksi_pkey PRIMARY KEY (nomor_transaksi),
  CONSTRAINT transaksi_no_chk CHECK (nomor_transaksi REGEXP '^INV-[0-9]{4}-[0-9]{2}-[0-9]{3}-P[0-9]{3,6}$'),
  CONSTRAINT transaksi_pelanggan_fk FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT transaksi_kasir_fk FOREIGN KEY (id_kasir) REFERENCES pengguna(id_pengguna)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX transaksi_pelanggan_idx ON transaksi (id_pelanggan);
CREATE INDEX transaksi_kasir_idx ON transaksi (id_kasir);
CREATE INDEX transaksi_tanggal_idx ON transaksi (tanggal);
CREATE INDEX transaksi_status_idx ON transaksi (status_pembayaran);
CREATE INDEX transaksi_jenis_idx ON transaksi (jenis_transaksi);
CREATE INDEX transaksi_ar_status_idx ON transaksi (ar_status);

-- =========================================================
-- 6) TRANSAKSI DETAIL (Line Items)
-- =========================================================
CREATE TABLE transaksi_detail (
  id_detail           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor_transaksi     VARCHAR(40) NOT NULL,
  id_produk           CHAR(13) NOT NULL,
  nama_produk         VARCHAR(255) NOT NULL,
  harga_satuan        DECIMAL(18) NOT NULL,
  jumlah              INT NOT NULL CHECK (jumlah > 0),
  mode_qty            ENUM('unit','pack') NOT NULL DEFAULT 'unit',
  pack_size_snapshot  INT NOT NULL DEFAULT 1 CHECK (pack_size_snapshot > 0),
  diskon_item         DECIMAL(18) NOT NULL DEFAULT 0,
  subtotal            DECIMAL(18) NOT NULL,
  created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT transaksi_detail_pkey PRIMARY KEY (id_detail),
  CONSTRAINT td_produk_chk CHECK (id_produk REGEXP '^[0-9]{13}$'),
  CONSTRAINT td_transaksi_fk FOREIGN KEY (nomor_transaksi) REFERENCES transaksi(nomor_transaksi)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT td_produk_fk FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX td_transaksi_idx ON transaksi_detail (nomor_transaksi);
CREATE INDEX td_produk_idx ON transaksi_detail (id_produk);

-- =========================================================
-- 7) KONTRAK KREDIT (inti Cicilan Pintar)
-- Satu kontrak per transaksi kredit
-- =========================================================
CREATE TABLE kontrak_kredit (
  id_kontrak        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor_kontrak     VARCHAR(30) NOT NULL,
  id_pelanggan      VARCHAR(7)  NOT NULL,
  nomor_transaksi   VARCHAR(40) NOT NULL,
  mulai_kontrak     DATE NOT NULL,
  tenor_bulan       TINYINT UNSIGNED NOT NULL CHECK (tenor_bulan BETWEEN 1 AND 24),
  pokok_pinjaman    DECIMAL(12) NOT NULL CHECK (pokok_pinjaman >= 0),
  dp                DECIMAL(12) NOT NULL DEFAULT 0 CHECK (dp >= 0),
  bunga_persen      DECIMAL(5) NOT NULL DEFAULT 0 CHECK (bunga_persen >= 0),
  cicilan_bulanan   DECIMAL(12) NOT NULL CHECK (cicilan_bulanan >= 0),
  status            ENUM('AKTIF','LUNAS','TUNDA','GAGAL') NOT NULL DEFAULT 'AKTIF',
  score_snapshot    TINYINT UNSIGNED NOT NULL DEFAULT 50,
  alasan_eligibilitas VARCHAR(200),
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
CREATE INDEX kontrak_mulai_idx ON kontrak_kredit (mulai_kontrak);

-- Relasi balik ke transaksi.id_kontrak
ALTER TABLE transaksi
  ADD CONSTRAINT transaksi_kontrak_fk
  FOREIGN KEY (id_kontrak) REFERENCES kontrak_kredit(id_kontrak)
  ON UPDATE CASCADE ON DELETE SET NULL;

-- =========================================================
-- 8) JADWAL ANGSURAN (1 baris per periode)
-- =========================================================
CREATE TABLE jadwal_angsuran (
  id_angsuran     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_kontrak      BIGINT UNSIGNED NOT NULL,
  periode_ke      TINYINT UNSIGNED NOT NULL CHECK (periode_ke >= 1),
  jatuh_tempo     DATE NOT NULL,
  jumlah_tagihan  DECIMAL(12) NOT NULL CHECK (jumlah_tagihan >= 0),
  jumlah_dibayar  DECIMAL(12) NOT NULL DEFAULT 0 CHECK (jumlah_dibayar >= 0),
  status          ENUM('DUE','PAID','LATE','VOID') NOT NULL DEFAULT 'DUE',
  paid_at         TIMESTAMP NULL,
  created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT jadwal_pkey PRIMARY KEY (id_angsuran),
  CONSTRAINT jadwal_kontrak_fk FOREIGN KEY (id_kontrak) REFERENCES kontrak_kredit(id_kontrak)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE UNIQUE INDEX jadwal_unq ON jadwal_angsuran (id_kontrak, periode_ke);
CREATE INDEX jadwal_jatuh_tempo_idx ON jadwal_angsuran (jatuh_tempo, status);

-- =========================================================
-- 9) PEMBAYARAN (mendukung split payment + mengikat ke angsuran)
-- id_pembayaran: PAY-YYYYMMDD-0000001 (app generate)
-- =========================================================
CREATE TABLE pembayaran (
  id_pembayaran     VARCHAR(32) NOT NULL,
  id_transaksi      VARCHAR(40) NOT NULL,
  id_angsuran       BIGINT UNSIGNED NULL,
  metode            ENUM(
    'TUNAI','QRIS',
    'VA_BCA','VA_BNI','VA_BRI','VA_PERMATA','VA_MANDIRI',
    'GOPAY','OVO','DANA','LINKAJA','SHOPEEPAY',
    'CREDIT_CARD','MANUAL_TRANSFER'
  ) NOT NULL,
  jumlah            DECIMAL(18) NOT NULL CHECK (jumlah > 0),
  tanggal           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  keterangan        VARCHAR(255),
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT pembayaran_pkey PRIMARY KEY (id_pembayaran),
  CONSTRAINT pembayaran_id_chk CHECK (id_pembayaran REGEXP '^PAY-[0-9]{8}-[0-9]{7}$'),
  CONSTRAINT pembayaran_transaksi_fk FOREIGN KEY (id_transaksi) REFERENCES transaksi(nomor_transaksi)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT pembayaran_angsuran_fk FOREIGN KEY (id_angsuran) REFERENCES jadwal_angsuran(id_angsuran)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX pembayaran_trx_idx ON pembayaran (id_transaksi);
CREATE INDEX pembayaran_tanggal_idx ON pembayaran (tanggal);
CREATE INDEX pembayaran_angsuran_idx ON pembayaran (id_angsuran);

-- =========================================================
-- 10) VIEW Piutang (opsional, bantu screening & laporan)
-- =========================================================
CREATE OR REPLACE VIEW v_piutang_pelanggan AS
SELECT
  k.id_pelanggan,
  SUM(j.jumlah_tagihan - j.jumlah_dibayar) AS saldo_piutang
FROM kontrak_kredit k
JOIN jadwal_angsuran j ON j.id_kontrak = k.id_kontrak
WHERE k.status IN ('AKTIF','TUNDA') AND j.status IN ('DUE','LATE')
GROUP BY k.id_pelanggan;

SET FOREIGN_KEY_CHECKS = 1;
