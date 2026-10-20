<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Concerns;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasExportRiwayatPembayaranBulkAction
{
    protected function exportRiwayatPembayaranBulkAction(): BulkAction
    {
        return BulkAction::make('export_excel')
            ->label('Export Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function (BulkAction $action, EloquentCollection $records): StreamedResponse {
                return $this->streamExportRiwayatPembayaran($records);
            })
            ->deselectRecordsAfterCompletion();
    }

    protected function streamExportRiwayatPembayaran(EloquentCollection $records): StreamedResponse
    {
        $owner = $this->getOwnerRecord();
        $pemesanan = $owner instanceof \Modules\Ticketing\Models\TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
        $invoice = $pemesanan?->invoice ?? $owner?->invoice ?? 'riwayat-pembayaran';
        $filename = sprintf('riwayat-pembayaran-%s-%s.xlsx', $invoice, now()->format('Ymd_His'));

        $records->loadMissing([
            'ticketingPenumpang.ticketingPembayaranPenumpang',
            'ticketingPembayar',
            'ticketingUnitKerja',
            'creator',
        ]);

        $columns = collect($this->getTable()->getVisibleColumns())
            ->reject(fn ($column) => in_array($column->getName(), ['#', 'actions'], true))
            ->values();

        return response()->streamDownload(function () use ($columns, $records, $filename): void {
            $writer = app(Writer::class);
            $writer->openToBrowser($filename);

            // Baris header kolom
            $headers = $columns->map(fn ($column) => $column->getLabel())->all();
            $writer->addRow(Row::fromValues($headers));

            // Baris data transaksi pembayaran
            foreach ($records as $record) {
                $row = [];
                foreach ($columns as $column) {
                    $row[] = $this->exportRiwayatCellValue($column, $record);
                }
                $writer->addRow(Row::fromValues($row));
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function exportRiwayatCellValue($column, Model $record): mixed
    {
        $name = $column->getName();
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

        // Kolom angka plain (Jumlah dan Total) tanpa format Rp/titik
        if (in_array($name, ['jumlah_membayar', 'total_per_penumpang'], true)) {
            return (int) $value;
        }

        // Status bukti bayar
        if ($name === 'status_bukti_bayar') {
            return $value ? 'Valid' : 'Belum';
        }

        // Format state menggunakan formatter kolom jika ada (WYSIWYG)
        if (method_exists($column, 'formatState')) {
            try {
                $formatted = $column->formatState($value);
                if ($formatted instanceof \Illuminate\Contracts\Support\Htmlable) {
                    $formatted = $formatted->toHtml();
                }

                if (is_string($formatted) && str_contains($formatted, '<')) {
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

        return $value ?? '-';
    }
}
