<?php

namespace Modules\Ticketing\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

abstract class ReservasiService
{
    public function generateInvoiceNumber(string $jenisReservasi): string
    {
        $today = now();
        $lastTwoDigitsOfYear = $today->format('y');
        $month = $today->format('m');

        $latestInvoice = DB::select(
            'SELECT invoice FROM ticketing_pemesanan
             WHERE YEAR(created_at) = YEAR(CURDATE())
               AND MONTH(created_at) = MONTH(CURDATE())
               AND SUBSTRING(invoice,5,1) = ?
             ORDER BY id DESC LIMIT 1',
            [$jenisReservasi]
        );

        $sequenceNumber = 1;
        if (! empty($latestInvoice)) {
            $sequenceNumber = intval(substr($latestInvoice[0]->invoice, -4)) + 1;
        }

        return $lastTwoDigitsOfYear.$month.$jenisReservasi.sprintf('%04d', $sequenceNumber);
    }

    protected function toDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse(str_replace('/', '-', (string) $value))->format('Y-m-d');
    }

    protected function toInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }

    protected function toDateTime(mixed $date, mixed $time): ?string
    {
        if (! $date) {
            return null;
        }

        return $this->toDate($date).' '.$this->normalizeTime($time);
    }

    protected function normalizeDateTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse(str_replace('/', '-', (string) $value))->format('Y-m-d H:i:s');
    }

    protected function normalizeTime(mixed $time): string
    {
        $time = (string) ($time ?? '00:00');
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return $time;
    }
}
