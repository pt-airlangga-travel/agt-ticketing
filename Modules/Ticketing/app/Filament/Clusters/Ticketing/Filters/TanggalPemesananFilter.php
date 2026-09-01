<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Filters;

use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TanggalPemesananFilter extends DateRangeFilter
{
    protected function setUp(): void
    {
        $this->name('tanggal_pemesanan');

        $this->label('Periode Tanggal Pemesanan');

        $this->format('d/m/Y');

        parent::setUp();

        $this->ranges([
            __('filament-daterangepicker-filter::message.today') => [now()->startOfDay(), now()->endOfDay()],
            'Besok' => [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()],
            'Hari Ini & Besok' => [now()->startOfDay(), now()->addDay()->endOfDay()],
            __('filament-daterangepicker-filter::message.yesterday') => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            __('filament-daterangepicker-filter::message.last_7_days') => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            __('filament-daterangepicker-filter::message.last_30_days') => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            __('filament-daterangepicker-filter::message.this_month') => [now()->startOfMonth(), now()->endOfMonth()],
            __('filament-daterangepicker-filter::message.last_month') => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
        ]);

        $this->modifyQueryUsing(function (Builder $query, $startDate, $endDate): Builder {
            if ($startDate === null && $endDate === null) {
                return $query;
            }

            return $query->whereHas('ticketingPemesanan', function (Builder $q) use ($startDate, $endDate): Builder {
                return $q
                    ->when($startDate, fn (Builder $qq) => $qq->whereDate('tanggal_pemesanan', '>=', $startDate))
                    ->when($endDate, fn (Builder $qq) => $qq->whereDate('tanggal_pemesanan', '<=', $endDate));
            });
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'tanggal_pemesanan';
    }
}
