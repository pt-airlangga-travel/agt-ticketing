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
            'ticketingPemesanan.ticketingUnitKerja.nama_unit_kerja' => 'Unit Kerja',
            'ticketingPemesanan.tanggal_pemesanan' => 'Tanggal Pemesanan',
            'ticketingPemesanan.ticketingPembayaran.nama_pembayar' => 'Nama Pembayar',
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

        return response()->streamDownload(function () use ($columns, $query): void {
            $writer = app(Writer::class);
            $writer->openToBrowser('reservasi_export.xlsx');

            $writer->addRow(Row::fromValues($columns->map(fn ($column) => $column->getLabel())->all()));

            foreach ($query->get() as $record) {
                $penumpangs = $record->ticketingPenumpang?->all() ?? [];

                if (empty($penumpangs)) {
                    $penumpangs = [null];
                }

                foreach ($penumpangs as $penumpang) {
                    $row = [];

                    foreach ($columns as $column) {
                        $name = $column->getName();

                        if ($name === 'ticketingPenumpang.nama_penumpang' || $name === 'penumpang') {
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
                        } else {
                            $value = data_get($record, $name);
                        }

                        if (str_ends_with($name, 'include_breakfast')) {
                            $value = $value ? 'Ya' : 'Tidak';
                        }

                        if (str_ends_with($name, 'pulang_pergi')) {
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
        }, 'reservasi_export.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
