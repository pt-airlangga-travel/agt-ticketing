<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Pages;

use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ListsReservasiByEntity;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\ReservasiPesawatResource;
use Modules\Ticketing\Models\TicketingTiketPesawat;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListReservasiPesawats extends ListRecords
{
    use HasClusterSubNavigation;
    use ListsReservasiByEntity;

    protected static string $resource = ReservasiPesawatResource::class;

    protected function getTableQuery(): Builder
    {
        return ReservasiPesawatResource::getReservasiPesawatListQuery();
    }

    protected function getReservasiAnchorModel(): string
    {
        return TicketingTiketPesawat::class;
    }

    protected function getReservasiEagerLoads(): array
    {
        return [
            'ticketingPemesanan.ticketingKategoriPemesanan',
            'ticketingPemesanan.ticketingUnitKerja',
            'ticketingPemesanan.ticketingPembayaran',
            'ticketingVendor',
            'ticketingMaskapai',
            'ticketingBerangkatBandara',
            'ticketingTibaBandara',
            'ticketingPenumpang',
            'ticketingPenumpang.ticketingPembayaranPenumpang',
            'ticketingPenumpang.ticketingPembayaranPenumpang.ticketingPembayar',
        ];
    }

    protected function getReservasiExportColumns(): array
    {
        return array_merge($this->reservasiBaseExportColumns(), [
            'penumpang' => 'Penumpang',
            'penumpang_pembayar' => 'Pembayar',
            'ticketingMaskapai.nama_maskapai' => 'Maskapai',
            'nomor_ticket' => 'Nomor Tiket',
            'nomor_penerbangan' => 'Nomor Penerbangan',
            'kode_booking_pesawat' => 'Kode Booking Pesawat',
            'kelas' => 'Kelas',
            'jenis_penerbangan' => 'Jenis Penerbangan',
            'ticketingBerangkatBandara.kode_bandara' => 'Kode Bandara Berangkat',
            'ticketingBerangkatBandara.nama_bandara' => 'Bandara Berangkat',
            'ticketingTibaBandara.kode_bandara' => 'Kode Bandara Tiba',
            'ticketingTibaBandara.nama_bandara' => 'Bandara Tiba',
            'jadwal_berangkat_pesawat' => 'Jadwal Berangkat (Pesawat)',
            'jadwal_tiba_pesawat' => 'Jadwal Tiba (Pesawat)',
            'detail_pulang_pergi' => 'Detail Pulang Pergi',
        ]);
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
                        $value = null;

                        if ($name === 'ticketingPenumpang.nama_penumpang') {
                            $value = $penumpang?->nama_penumpang;
                        } elseif ($name === 'pembayar_per_penumpang') {
                            $pembayaranId = $record->ticketingPemesanan?->ticketingPembayaran?->id;
                            $pembayaran = $penumpang?->ticketingPembayaranPenumpang
                                ->where('tckt_pembayaran_id', $pembayaranId)
                                ->first();
                            $nama = $pembayaran?->ticketingPembayar?->nama_pembayar ?? ($pembayaran?->nama_pembayar ?? '-');
                            $unit = $pembayaran?->ticketingUnitKerja?->nama_unit_kerja;
                            $value = $unit ? "{$nama} ({$unit})" : $nama;
                        } else {
                            $value = data_get($record, $name);
                        }

                        if (str_ends_with($name, 'include_breakfast')) {
                            $value = $value ? 'Ya' : 'Tidak';
                        }

                        if (str_ends_with($name, 'pulang_pergi')) {
                            $value = $value ? 'Ya' : 'Tidak';
                        }

                        if (in_array($name, ['jadwal_berangkat_pesawat', 'jadwal_tiba_pesawat'], true) && filled($value)) {
                            $value = Carbon::parse($value)->format('d M Y H:i');
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

    protected function getHeaderActions(): array
    {
        return [
            $this->exportReservasiAction(),
            CreateAction::make()
                ->label('+ Tambah')
                ->button()
                ->createAnother(false),
        ];
    }
}
