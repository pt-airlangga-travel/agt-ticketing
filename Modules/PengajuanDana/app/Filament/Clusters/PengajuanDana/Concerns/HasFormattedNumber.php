<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns;

trait HasFormattedNumber
{
    public static function formatDefinedId(string $number): string
    {
        if (strlen($number) > 12) {
            return substr($number, 0, 4)
                .'.'.substr($number, 4, 2)
                .'.'.substr($number, 6, 3)
                .'.'.substr($number, 9, 3)
                .'.'.substr($number, 12, 3)
                .'.'.substr($number, 15, 2);
        }

        return substr($number, 0, 4)
            .'.'.substr($number, 4, 2)
            .'.'.substr($number, 6, 3)
            .'.'.substr($number, 9, 3);
    }
}
