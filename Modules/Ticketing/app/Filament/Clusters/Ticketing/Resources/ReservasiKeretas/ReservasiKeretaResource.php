<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Pages\CreateReservasiKereta;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Pages\EditReservasiKereta;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Pages\ListReservasiKeretas;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\RelationManagers\PenumpangKeretaRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\RelationManagers\RiwayatPembayaranRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Schemas\ReservasiKeretaForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Tables\ReservasiKeretasTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingTiketKereta;
use UnitEnum;

class ReservasiKeretaResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingPemesanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static UnitEnum|string|null $navigationGroup = 'Reservasi';

    protected static ?string $navigationLabel = 'Reservasi Kereta';

    protected static ?string $breadcrumb = 'Reservasi Kereta';

    protected static ?string $slug = 'reservasi-kereta';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'invoice';

    public static function getEloquentQuery(): Builder
    {
        return static::getReservasiKeretaListQuery();
    }

    public static function getReservasiKeretaListQuery(): Builder
    {
        $query = TicketingTiketKereta::query()
            ->select([
                'ticketing_tiket_kereta.*',
                'p.invoice',
                'p.nama_customer',
                'p.status_pemesanan',
                'p.harga_jual',
                'p.tanggal_pemesanan',
                'pb.nama_pembayar',
                'k.nama_kereta',
                'sb.nama_stasiun as stasiun_berangkat',
                'st.nama_stasiun as stasiun_tiba',
            ])
            ->join('ticketing_kereta as k', 'ticketing_tiket_kereta.tckt_kereta_id', '=', 'k.id')
            ->join('ticketing_stasiun as sb', 'ticketing_tiket_kereta.tckt_stasiun_berangkat_id', '=', 'sb.id')
            ->join('ticketing_stasiun as st', 'ticketing_tiket_kereta.tckt_stasiun_tiba_id', '=', 'st.id')
            ->join('ticketing_pemesanan as p', 'ticketing_tiket_kereta.tckt_pemesanan_id', '=', 'p.id')
            ->join('ticketing_pembayaran as pb', 'p.id', '=', 'pb.tckt_pemesanan_id')
            ->with([
                'ticketingPemesanan.ticketingKategoriPemesanan',
                'ticketingPemesanan.ticketingUnitKerja',
                'ticketingPemesanan.ticketingPembayaran',
                'ticketingPemesanan.creator',
                'ticketingVendor',
                'ticketingKereta',
                'ticketingBerangkatStasiun',
                'ticketingTibaStasiun',
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
                'ticketingTiketKereta',
                'ticketingTiketKereta.ticketingVendor',
                'ticketingTiketKereta.ticketingKereta',
                'ticketingTiketKereta.ticketingBerangkatStasiun',
                'ticketingTiketKereta.ticketingTibaStasiun',
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
        return ReservasiKeretaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservasiKeretasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PenumpangKeretaRelationManager::class,
            RiwayatPembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservasiKeretas::route('/'),
            'create' => CreateReservasiKereta::route('/create'),
            'edit' => EditReservasiKereta::route('/{record}/edit'),
        ];
    }
}
