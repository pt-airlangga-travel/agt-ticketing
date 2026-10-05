<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Pages\CreateReservasiHotel;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Pages\EditReservasiHotel;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Pages\ListReservasiHotels;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\RelationManagers\PenumpangHotelRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\RelationManagers\RiwayatPembayaranRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Schemas\ReservasiHotelForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Tables\ReservasiHotelsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingKamarHotel;
use Modules\Ticketing\Models\TicketingPemesanan;
use UnitEnum;

class ReservasiHotelResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingPemesanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static UnitEnum|string|null $navigationGroup = 'Reservasi';

    protected static ?string $navigationLabel = 'Reservasi Hotel';

    protected static ?string $breadcrumb = 'Reservasi Hotel';

    protected static ?string $slug = 'reservasi-hotel';

    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'invoice';

    public static function getEloquentQuery(): Builder
    {
        return static::getReservasiHotelListQuery();
    }

    public static function getReservasiHotelListQuery(): Builder
    {
        $query = TicketingKamarHotel::query()
            ->select([
                'ticketing_kamar_hotel.*',
                'p.invoice',
                'p.nama_customer',
                'p.status_pemesanan',
                'p.harga_jual',
                'p.tanggal_pemesanan',
                'pb.nama_pembayar',
                'h.nama_hotel',
            ])
            ->join('ticketing_hotel as h', 'ticketing_kamar_hotel.tckt_hotel_id', '=', 'h.id')
            ->join('ticketing_pemesanan as p', 'ticketing_kamar_hotel.tckt_pemesanan_id', '=', 'p.id')
            ->join('ticketing_pembayaran as pb', 'p.id', '=', 'pb.tckt_pemesanan_id')
            ->with([
                'ticketingPemesanan.ticketingKategoriPemesanan',
                'ticketingPemesanan.ticketingUnitKerja',
                'ticketingPemesanan.ticketingPembayaran',
                'ticketingPemesanan.creator',
                'ticketingVendor',
                'ticketingHotel',
                'ticketingPenumpang',
            ]);
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // PRIORITAS: akses penuh (bisa melihat semua reservasi)
        if ($user->canAccess(static::getRbacPermissionNames()['view'])) {
            return $query;
        }

        // Terbatas: hanya melihat reservasi yang dibuat oleh dirinya sendiri
        return $query->where('p.created_by', $user->id);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'ticketingKategoriPemesanan',
                'ticketingUnitKerja',
                'ticketingPembayaran',
                'creator',
                'ticketingKamarHotel',
                'ticketingKamarHotel.ticketingVendor',
                'ticketingKamarHotel.ticketingHotel',
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canAccess(static::getRbacPermissionNames()['view'])) {
            return $query;
        }

        return $query->where('ticketing_pemesanan.created_by', $user->id);
    }

    public static function form(Schema $schema): Schema
    {
        return ReservasiHotelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservasiHotelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PenumpangHotelRelationManager::class,
            RiwayatPembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservasiHotels::route('/'),
            'create' => CreateReservasiHotel::route('/create'),
            'edit' => EditReservasiHotel::route('/{record}/edit'),
        ];
    }
}
