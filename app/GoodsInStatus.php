<?php

namespace App;

enum GoodsInStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case PartialReceived = 'partial_received';
    case Received = 'received';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::PartialReceived => 'Diterima Sebagian',
            self::Received => 'Diterima Lengkap',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::PartialReceived => 'indigo',
            self::Received => 'blue',
        };
    }
}
