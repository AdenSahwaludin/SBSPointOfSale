<?php

namespace App;

enum AdjustmentType: string
{
    case ReturPelanggan = 'retur_pelanggan';
    case ReturGudang = 'retur_gudang';
    case KoreksiPlus = 'koreksi_plus';
    case KoreksiMinus = 'koreksi_minus';
    case PenyesuaianOpname = 'penyesuaian_opname';
    case Expired = 'expired';
    case Rusak = 'rusak';

    public function label(): string
    {
        return match ($this) {
            self::ReturPelanggan => 'Retur dari Pelanggan',
            self::ReturGudang => 'Retur ke Gudang',
            self::KoreksiPlus => 'Koreksi Plus',
            self::KoreksiMinus => 'Koreksi Minus',
            self::PenyesuaianOpname => 'Penyesuaian Opname',
            self::Expired => 'Barang Expired',
            self::Rusak => 'Barang Rusak',
        };
    }

    public function isPositive(): bool
    {
        return in_array($this, [
            self::ReturPelanggan,
            self::ReturGudang,
            self::KoreksiPlus,
        ]);
    }

    public function isNegative(): bool
    {
        return in_array($this, [
            self::KoreksiMinus,
            self::Expired,
            self::Rusak,
        ]);
    }
}
