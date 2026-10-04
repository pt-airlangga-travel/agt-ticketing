<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingActivityLogs\Tables;

use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TicketingActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('created_at', 'desc')
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->columns([
                TextColumn::make('#')->rowIndex(),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Oleh')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('entity_label')
                    ->label('Data')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('pemesanan.invoice')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_label')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dibuat' => 'success',
                        'Diubah' => 'warning',
                        'Dihapus' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('changes')
                    ->label('Perubahan')
                    ->formatStateUsing(fn ($record): HtmlString => new HtmlString(
                        self::renderChanges($record)
                    ))
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    protected static function renderChanges($record): string
    {
        $changes = $record->changes ?? [];

        if (! is_array($changes) || empty($changes)) {
            return '<span class="text-gray-400 dark:text-gray-500">—</span>';
        }

        $html = '<div class="grid gap-y-0.5">';

        foreach ($changes as $field => $change) {
            $label = self::fieldLabel($field);

            if (is_array($change) && array_key_exists('old', $change) && array_key_exists('new', $change)) {
                $old = self::formatValue($field, $change['old']);
                $new = self::formatValue($field, $change['new']);

                $html .= '<div class="leading-tight">';
                $html .= '<span class="font-semibold text-gray-800 dark:text-white">'.e($label).'</span>';
                $html .= '<span class="text-gray-500 dark:text-gray-400">: </span>';
                $html .= '<span class="line-through text-gray-400 dark:text-gray-500">'.e($old).'</span>';
                $html .= '<span class="text-gray-500 dark:text-gray-400"> → </span>';
                $html .= '<span class="text-gray-800 dark:text-white">'.e($new).'</span>';
                $html .= '</div>';
            } else {
                $html .= '<div class="leading-tight">';
                $html .= '<span class="font-semibold text-gray-800 dark:text-white">'.e($label).'</span>';
                $html .= '<span class="text-gray-500 dark:text-gray-400">: </span>';
                $html .= '<span class="text-gray-800 dark:text-white">'.e(self::formatValue($field, $change)).'</span>';
                $html .= '</div>';
            }
        }

        return $html.'</div>';
    }

    protected static function fieldLabel(string $field): string
    {
        $labels = [
            'invoice' => 'Invoice',
            'nama_customer' => 'Nama Pemesan',
            'nama_pembayar' => 'Nama Pembayar',
            'tckt_kategori_pemesanan_id' => 'Kategori Pemesanan',
            'tckt_unit_kerja_id' => 'Unit Kerja',
            'status_pemesanan' => 'Status Pemesanan',
            'status_pemesanan_pulang_pergi' => 'Status Pulang Pergi',
            'pulang_pergi' => 'Pulang Pergi',
            'tanggal_pemesanan' => 'Tanggal Pemesanan',
            'harga_beli' => 'Harga Beli',
            'harga_publish' => 'Harga Publish',
            'harga_jual' => 'Harga Jual',
            'created_by' => 'Dibuat Oleh',
            'tckt_hotel_id' => 'Hotel',
            'tckt_vendor_id' => 'Vendor',
            'tckt_maskapai_id' => 'Maskapai',
            'tckt_kereta_id' => 'Kereta',
            'tckt_bandara_berangkat_id' => 'Bandara Berangkat',
            'tckt_bandara_tiba_id' => 'Bandara Tiba',
            'tckt_stasiun_berangkat_id' => 'Stasiun Berangkat',
            'tckt_stasiun_tiba_id' => 'Stasiun Tiba',
            'nomor_ticket' => 'No. Tiket',
            'nomor_penerbangan' => 'No. Penerbangan',
            'kode_booking_pesawat' => 'Kode Booking Pesawat',
            'kode_booking_kereta' => 'Kode Booking Kereta',
            'kelas' => 'Kelas',
            'jadwal_berangkat_pesawat' => 'Waktu Keberangkatan Pesawat',
            'jadwal_tiba_pesawat' => 'Waktu Kedatangan Pesawat',
            'jadwal_berangkat_kereta' => 'Waktu Keberangkatan Kereta',
            'jadwal_tiba_kereta' => 'Waktu Kedatangan Kereta',
            'jadwal_checkin' => 'Waktu Check-in',
            'jadwal_checkout' => 'Waktu Check-out',
            'zona_waktu' => 'Zona Waktu',
            'zona_waktu_kedatangan' => 'Zona Waktu Kedatangan',
            'detail_pulang_pergi' => 'Detail Pulang Pergi',
            'jumlah_kamar' => 'Jumlah Kamar',
            'lama_menginap' => 'Lama Menginap',
            'tipe_kamar' => 'Tipe Kamar',
            'include_breakfast' => 'Termasuk Sarapan',
            'jenis_dokumen' => 'Jenis Dokumen',
            'keterangan' => 'Keterangan',
        ];

        return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    protected static function formatValue(string $field, mixed $value): string
    {
        if (is_null($value) || $value === '') {
            return '-';
        }

        if (in_array($field, ['harga_beli', 'harga_publish', 'harga_jual'], true)) {
            return 'Rp '.number_format((int) $value, 0, ',', '.');
        }

        if ($field === 'pulang_pergi') {
            return (int) $value === 1 ? 'Ya' : 'Tidak';
        }

        if ($field === 'include_breakfast') {
            return $value ? 'Ya' : 'Tidak';
        }

        if (in_array($field, ['created_at', 'updated_at', 'tanggal_pemesanan'], true)) {
            return Carbon::parse($value)->format('d M Y');
        }

        if (in_array($field, [
            'jadwal_berangkat_pesawat',
            'jadwal_tiba_pesawat',
            'jadwal_berangkat_kereta',
            'jadwal_tiba_kereta',
            'jadwal_checkin',
            'jadwal_checkout',
        ], true)) {
            return Carbon::parse($value)->format('d M Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
