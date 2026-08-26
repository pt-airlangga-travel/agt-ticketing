<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ScopesReservasiDashboardQuery;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\ReservasiDokumenResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\ReservasiHotelResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\ReservasiKeretaResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\ReservasiPesawatResource;
use Modules\Ticketing\Models\TicketingPemesanan;

class RecentReservasiWidget extends TableWidget
{
    use ScopesReservasiDashboardQuery;

    protected static ?int $sort = 3;

    protected function getTableQuery(): Builder
    {
        return $this->getReservasiDashboardQuery()
            ->with([
                'ticketingTiketPesawat',
                'ticketingTiketKereta',
                'ticketingKamarHotel',
                'ticketingDokumen',
                'ticketingKategoriPemesanan',
                'ticketingUnitKerja',
            ])
            ->latest('tanggal_pemesanan')
            ->limit(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordUrl(fn (TicketingPemesanan $record): ?string => $this->resolveRecordUrl($record))
            ->columns([
                TextColumn::make('invoice')
                    ->label('Invoice')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('nama_customer')
                    ->label('Pemesan')
                    ->searchable(isIndividual: true, isGlobal: false),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->getStateUsing(fn (TicketingPemesanan $record): string => $this->resolveJenisReservasi($record))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pesawat' => 'primary',
                        'Kereta' => 'warning',
                        'Hotel' => 'info',
                        'Dokumen' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('status_pemesanan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmed' => 'success',
                        'Canceled', 'Refund' => 'danger',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('harga_jual')
                    ->label('Total')
                    ->numeric()
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('tanggal_pemesanan')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ]);
    }

    protected function resolveJenisReservasi(TicketingPemesanan $record): string
    {
        if ($record->ticketingTiketPesawat) {
            return 'Pesawat';
        }

        if ($record->ticketingTiketKereta) {
            return 'Kereta';
        }

        if ($record->ticketingKamarHotel) {
            return 'Hotel';
        }

        if ($record->ticketingDokumen) {
            return 'Dokumen';
        }

        return '-';
    }

    protected function resolveRecordUrl(TicketingPemesanan $record): ?string
    {
        if ($record->ticketingTiketPesawat) {
            return ReservasiPesawatResource::getUrl('edit', ['record' => $record->invoice]);
        }

        if ($record->ticketingTiketKereta) {
            return ReservasiKeretaResource::getUrl('edit', ['record' => $record->invoice]);
        }

        if ($record->ticketingKamarHotel) {
            return ReservasiHotelResource::getUrl('edit', ['record' => $record->invoice]);
        }

        if ($record->ticketingDokumen) {
            return ReservasiDokumenResource::getUrl('edit', ['record' => $record->invoice]);
        }

        return null;
    }
}
