<?php

namespace Modules\Ticketing\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class ReservasiService
{
    public function generateInvoiceNumber(string $jenisReservasi): string
    {
        $today = now();
        $lastTwoDigitsOfYear = $today->format('y');
        $month = $today->format('m');

        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $prefix = $lastTwoDigitsOfYear.$month.$jenisReservasi;
        $latestInvoice = DB::table('ticketing_pemesanan')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('invoice', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice');

        $sequenceNumber = 1;
        if (! empty($latestInvoice)) {
            $sequenceNumber = intval(substr($latestInvoice, -4)) + 1;
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

    /**
     * Buang kolom yang belum ada di DB (mis. migrasi `lunas` tertunda di production).
     */
    protected function filterPemesananColumns(array $data): array
    {
        if (array_key_exists('lunas', $data) && ! Schema::hasColumn('ticketing_pemesanan', 'lunas')) {
            unset($data['lunas']);
        }

        return $data;
    }
}
