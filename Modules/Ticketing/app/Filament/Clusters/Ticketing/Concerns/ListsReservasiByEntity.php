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
                $penumpangs = $record->ticketingPenumpang?->all() ?? [];

                if (empty($penumpangs)) {
                    $penumpangs = [null];
                }

                foreach ($penumpangs as $index => $penumpang) {
                    $row = [];

                    foreach ($columns as $column) {
                        $name = $column->getName();

                        if ($name === 'penumpang_id') {
                            $invoice = $record->ticketingPemesanan?->invoice ?? $record->invoice ?? '-';
                            $value = $penumpang ? $invoice . '-' . ($index + 1) : '';
                        } elseif ($name === 'ticketingPenumpang.nama_penumpang' || $name === 'penumpang') {
                            $value = $penumpang?->nama_penumpang;
                        } elseif ($name === 'pembayar_per_penumpang' || $name === 'penumpang_pembayar') {
                            $pembayaranId = $record->ticketingPemesanan?->ticketingPembayaran?->id;
                            $pembayaran = $penumpang?->ticketingPembayaranPenumpang
                                ->where('tckt_pembayaran_id', $pembayaranId)
                                ->first();
                            $value = $pembayaran?->ticketingPembayar?->nama_pembayar ?? ($pembayaran?->nama_pembayar ?? '-');
                            $unit = $pembayaran?->ticketingUnitKerja?->nama_unit_kerja;
                            $value = $unit ? "{$value} ({$unit})" : $value;
                            if ($penumpang === null) {
                                $value = data_get($record, $name) ?? $value;
                            }
                        } elseif ($name === 'detail_pulang_pergi') {
                            $value = $record->formatted_detail_pulang_pergi ?? '-';
                        } elseif (str_starts_with($name, 'pulang_pergi_')) {
                            // Kolom pecahan "Pulang" (state custom di tabel, bukan atribut model).
                            $value = $this->exportPulangPergiValue($record, $name);
                        } else {
                            $value = data_get($record, $name);
                        }

                        if (str_ends_with($name, 'include_breakfast')) {
                            $value = $value ? 'Ya' : 'Tidak';
                        }

                        if (str_ends_with($name, 'pulang_pergi') && !str_contains($name, 'detail')) {
                            $value = $value ? 'Ya' : 'Tidak';
                        }

                        if (in_array($name, ['jadwal_berangkat_pesawat', 'jadwal_tiba_pesawat', 'jadwal_berangkat_kereta', 'jadwal_tiba_kereta', 'jadwal_checkin', 'jadwal_checkout'], true) && filled($value)) {
                            try {
                                $value = Carbon::parse($value)->format('d M Y H:i');
                            } catch (\Throwable $e) {
                            }
                        }

                        if (is_null($value)) {
                            $value = '';
                        }

                        $row[] = $value;
                    }

                    $writer->addRow(Row::fromValues($row));
                }
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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

        return $values ?: '-';
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
