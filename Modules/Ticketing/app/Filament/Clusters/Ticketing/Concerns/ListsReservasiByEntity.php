<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Concerns;

use Carbon\Carbon;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ListsReservasiByEntity
{
    protected function getReservasiAnchorModel(): string
    {
        throw new \BadMethodCallException('getReservasiAnchorModel() must be implemented in the page.');
    }

    protected function getReservasiEagerLoads(): array
    {
        return [
            'ticketingPemesanan.ticketingKategoriPemesanan',
            'ticketingPemesanan.ticketingUnitKerja',
            'ticketingPemesanan.ticketingPembayaran',
            'ticketingPemesanan.ticketingPembayaran.ticketingUnitKerja',
            'ticketingPemesanan.creator',
            'ticketingVendor',
            'ticketingPenumpang',
            'ticketingPenumpang.ticketingPembayaranPenumpang',
            'ticketingPenumpang.ticketingPembayaranPenumpang.ticketingPembayar',
        ];
    }

    protected function getTableQuery(): Builder
    {
        $model = $this->getReservasiAnchorModel();

        return $model::query()->with($this->getReservasiEagerLoads());
    }

    protected function reservasiBaseExportColumns(): array
    {
        return [
            'ticketingPemesanan.invoice' => 'Invoice',
            'ticketingPemesanan.nama_customer' => 'Pemesan',
            'ticketingPemesanan.ticketingKategoriPemesanan.nama_kategori' => 'Kategori',
            'ticketingPemesanan.ticketingUnitKerja.nama_unit_kerja' => 'Unit Kerja Pemesan',
            'ticketingPemesanan.tanggal_pemesanan' => 'Tanggal Pemesanan',
            'ticketingPemesanan.ticketingPembayaran.nama_pembayar' => 'Nama Pembayar',
            'ticketingPemesanan.ticketingPembayaran.ticketingUnitKerja.nama_unit_kerja' => 'Unit Kerja Pembayar',
            'ticketingPemesanan.status_pemesanan' => 'Status',
            'ticketingPemesanan.pulang_pergi' => 'Pulang Pergi',
            'ticketingPemesanan.harga_beli' => 'Harga Beli',
            'ticketingPemesanan.harga_publish' => 'Harga Publish',
            'ticketingPemesanan.harga_jual' => 'Harga Jual',
            'ticketingVendor.nama_vendor' => 'Vendor',
        ];
    }

    protected function exportReservasiAction(): Action
    {
        return Action::make('export_reservasi')
            ->label('Export Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(fn (): StreamedResponse => $this->streamExportReservasi());
    }

    protected function streamExportReservasi(): StreamedResponse
    {
        $columns = collect($this->getTable()->getVisibleColumns())
            ->reject(fn ($column) => $column->getName() === '#')
            ->values();

        $query = $this->getTableQueryForExport();

        $filename = $this->getExportFilename($query);

        return response()->streamDownload(function () use ($columns, $query, $filename): void {
            $writer = app(Writer::class);
            $writer->openToBrowser($filename);

            $writer->addRow(Row::fromValues($columns->map(fn ($column) => $column->getLabel())->all()));

            foreach ($query->get() as $record) {
                $row = [];

                foreach ($columns as $column) {
                    $row[] = $this->exportCellValue($column, $record);
                }

                $writer->addRow(Row::fromValues($row));
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Mengekstrak nilai sel yang WYSIWYG (What You See Is What You Get) dari Column dan Model record.
     */
    protected function exportCellValue($column, $record): mixed
    {
        $name = $column->getName();

        // 1. Kolom pecahan "Pulang" (state custom di tabel, bukan atribut model).
        if (str_starts_with($name, 'pulang_pergi_')) {
            return $this->exportPulangPergiValue($record, $name);
        }

        if ($name === 'detail_pulang_pergi') {
            return $record->formatted_detail_pulang_pergi ?? '-';
        }

        // 2. Kolom Lunas (IconColumn)
        if ($name === 'lunas' || $name === 'lunas_status') {
            $pemesanan = $record->ticketingPemesanan;
            $all = collect($record->ticketingPenumpang ?? $record->pemilikDokumen ?? [])->sortBy('id');
            $penumpangs = ReservasiToggleableColumns::getFilteredPenumpangsForTable($record, $this);

            if ($all->isEmpty()) {
                $isLunas = (bool) ($pemesanan?->lunas || $pemesanan?->isSemuaPenumpangLunas());

                return $isLunas ? 'Lunas' : 'Belum Lunas';
            }

            return $penumpangs->map(function ($p) use ($pemesanan): string {
                return ($pemesanan?->isPenumpangLunas($p->id) ?? false) ? 'Lunas' : 'Belum Lunas';
            })->implode("\n");
        }

        // 3. Evaluasi state kolom melalui Column instance (WYSIWYG)
        $value = null;
        try {
            $column->record($record);
            $value = $column->getState();
        } catch (\Throwable $e) {
            $value = null;
        }

        if ($value === null) {
            $value = data_get($record, $name);
        }

        // 4. Jika kolom adalah IconColumn
        if ($column instanceof \Filament\Tables\Columns\IconColumn) {
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }

            return $value ? (string) $value : '-';
        }

        // 5. Jika state adalah array / Collection (seperti listWithLineBreaks pada penumpang, pembayar, harga per pax, sisa tagihan)
        if (is_array($value) || $value instanceof \Illuminate\Support\Collection) {
            $items = collect($value)->map(function ($item) {
                if ($item instanceof \Illuminate\Contracts\Support\Htmlable) {
                    $item = strip_tags($item->toHtml());
                }

                return is_scalar($item) ? (string) $item : json_encode($item);
            })->all();

            return ! empty($items) ? implode("\n", $items) : '-';
        }

        // 6. Format state menggunakan formatState() jika ada
        if (method_exists($column, 'formatState')) {
            try {
                $formatted = $column->formatState($value);
                if ($formatted instanceof \Illuminate\Contracts\Support\Htmlable) {
                    $formatted = $formatted->toHtml();
                }

                if (is_string($formatted) && str_contains($formatted, '<')) {
                    // Bersihkan HTML tag dan ubah div/br menjadi spasi
                    $clean = preg_replace('/<(?:\/div|div[^>]*|br\s*\/?)>/i', ' ', $formatted);
                    $clean = trim(strip_tags($clean));
                    $clean = preg_replace('/\s+/', ' ', $clean);
                    if (filled($clean)) {
                        $value = $clean;
                    }
                } elseif (filled($formatted)) {
                    $value = $formatted;
                }
            } catch (\Throwable $e) {
            }
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($value instanceof \Illuminate\Contracts\Support\Htmlable) {
            $clean = preg_replace('/<(?:\/div|div[^>]*|br\s*\/?)>/i', ' ', $value->toHtml());
            $clean = trim(strip_tags($clean));

            return preg_replace('/\s+/', ' ', $clean);
        }

        if (str_ends_with($name, 'include_breakfast')) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (str_ends_with($name, 'pulang_pergi') && ! str_contains($name, 'detail')) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (in_array($name, ['jadwal_berangkat_pesawat', 'jadwal_tiba_pesawat', 'jadwal_berangkat_kereta', 'jadwal_tiba_kereta', 'jadwal_checkin', 'jadwal_checkout'], true) && filled($value)) {
            try {
                $value = Carbon::parse($value)->format('d M Y H:i');
            } catch (\Throwable $e) {
            }
        }

        if (is_null($value) || $value === '') {
            return '-';
        }

        return $value;
    }

    /**
     * Nilai kolom "Pulang" untuk export Excel.
     *
     * Kolom-kolom ini memakai state() custom di tabel (dari pulangPergiSegmens()),
     * bukan atribut/relasi model — jadi data_get() mengembalikan null. Di sini
     * kita baca langsung dari parser model, konsisten dengan yang tampil di tabel.
     */
    protected function exportPulangPergiValue($record, string $name): mixed
    {
        // "pulang_pergi_status" adalah status keseluruhan, bukan per-segmen.
        if ($name === 'pulang_pergi_status') {
            return $record->pulangPergiStatus() ?? '-';
        }

        $field = (string) str_replace('pulang_pergi_', '', $name);

        $values = collect($record->pulangPergiSegmens())
            ->pluck($field)
            ->filter(fn ($v) => filled($v))
            ->values()
            ->all();

        // OpenSpout Row hanya menerima scalar — multi-segmen digabung koma.
        return $values ? implode(', ', $values) : '-';
    }

    protected function getExportFilename(Builder $query): string
    {
        $reservationType = $this->getReservationTypeSlug();

        $dateColumn = str_contains(static::class, 'ReservasiDokumens')
            ? 'ticketing_pemesanan.tanggal_pemesanan'
            : 'p.tanggal_pemesanan';

        try {
            $startDate = (clone $query)->min($dateColumn);
            $endDate = (clone $query)->max($dateColumn);
        } catch (\Throwable $e) {
            $startDate = null;
            $endDate = null;
        }

        $startStr = $startDate ? Carbon::parse($startDate)->format('d-m-Y') : now()->format('d-m-Y');
        $endStr = $endDate ? Carbon::parse($endDate)->format('d-m-Y') : now()->format('d-m-Y');

        if ($startStr === $endStr) {
            $dateString = $startStr;
        } else {
            $dateString = sprintf('%s_%s', $startStr, $endStr);
        }

        $filename = sprintf('%s_%s.xlsx', $reservationType, $dateString);

        return str_replace(['/', '\\'], '-', $filename);
    }

    /**
     * Get reservation type slug for filename generation (e.g., reservasi-pesawat)
     */
    protected function getReservationTypeSlug(): string
    {
        $className = static::class;
        
        if (str_contains($className, 'ReservasiPesawats')) {
            return 'reservasi-pesawat';
        }
        
        if (str_contains($className, 'ReservasiKeretas')) {
            return 'reservasi-kereta';
        }
        
        if (str_contains($className, 'ReservasiHotels')) {
            return 'reservasi-hotel';
        }
        
        if (str_contains($className, 'ReservasiDokumens')) {
            return 'reservasi-dokumen';
        }
        
        return 'reservasi-export';
    }
}
