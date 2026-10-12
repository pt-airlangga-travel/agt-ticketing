<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Pages\CreateReservasiPesawat;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Pages\EditReservasiPesawat;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Pages\ListReservasiPesawats;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\RelationManagers\PenumpangPesawatRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\RelationManagers\RiwayatPembayaranRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Schemas\ReservasiPesawatForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Tables\ReservasiPesawatsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingTiketPesawat;
use UnitEnum;

class ReservasiPesawatResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingTiketPesawat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected static UnitEnum|string|null $navigationGroup = 'Reservasi';

    protected static ?string $navigationLabel = 'Reservasi Pesawat';

    protected static ?string $breadcrumb = 'Reservasi Pesawat';

    protected static ?string $slug = 'reservasi-pesawat';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'nomor_ticket';

    public static function getEloquentQuery(): Builder
    {
        return static::getReservasiPesawatListQuery();
    }

    public static function getReservasiPesawatListQuery(): Builder
    {
        return static::reservasiPesawatListQuery();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'ticketingPemesanan',
                'ticketingPemesanan.creator',
                'ticketingPemesanan.ticketingKategoriPemesanan',
                'ticketingPemesanan.ticketingUnitKerja',
                'ticketingPemesanan.ticketingPembayaran',
                'ticketingVendor',
                'ticketingMaskapai',
                'ticketingBerangkatBandara',
                'ticketingTibaBandara',
                'ticketingPenumpang',
                'ticketingPenumpang.ticketingPembayaranPenumpang',
                'ticketingPenumpang.ticketingPembayaranPenumpang.ticketingUnitKerja',
                'ticketingPenumpang.ticketingPembayaranPenumpang.ticketingPembayar',
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canAccess(static::getRbacPermissionNames()['view'])) {
            return $query;
        }

        return $query->whereHas('ticketingPemesanan', function (Builder $subQuery): void {
            $subQuery->where('created_by', auth()->id());
        });
    }

    protected static function reservasiPesawatListQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->select([
                'ticketing_tiket_pesawat.*',
                'p.invoice',
                'p.nama_customer',
                'p.status_pemesanan',
                'p.harga_jual',
                'p.tanggal_pemesanan',
                'pb.nama_pembayar',
                'm.nama_maskapai',
                'bt.nama_bandara as bandara_tiba',
                'bb.nama_bandara as bandara_berangkat',
            ])
            ->join('ticketing_maskapai as m', 'ticketing_tiket_pesawat.tckt_maskapai_id', '=', 'm.id')
            ->join('ticketing_bandara as bt', 'ticketing_tiket_pesawat.tckt_bandara_tiba_id', '=', 'bt.id')
            ->join('ticketing_bandara as bb', 'ticketing_tiket_pesawat.tckt_bandara_berangkat_id', '=', 'bb.id')
            ->join('ticketing_pemesanan as p', 'ticketing_tiket_pesawat.tckt_pemesanan_id', '=', 'p.id')
            ->join('ticketing_pembayaran as pb', 'p.id', '=', 'pb.tckt_pemesanan_id')
            ->with([
                'ticketingPemesanan',
                'ticketingPemesanan.creator',
                'ticketingPemesanan.ticketingKategoriPemesanan',
                'ticketingPemesanan.ticketingUnitKerja',
                'ticketingPemesanan.ticketingPembayaran',
                'ticketingPemesanan.ticketingPembayaran.ticketingUnitKerja',
                'ticketingVendor',
                'ticketingMaskapai',
                'ticketingBerangkatBandara',
                'ticketingTibaBandara',
                'ticketingPenumpang',
                'ticketingPenumpang.ticketingPembayaranPenumpang',
                'ticketingPenumpang.ticketingPembayaranPenumpang.ticketingUnitKerja',
                'ticketingPenumpang.ticketingPembayaranPenumpang.ticketingPembayar',
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

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if ($record && $record->ticketingPemesanan?->invoice) {
            return $record->ticketingPemesanan->invoice;
        }

        return parent::getRecordTitle($record);
    }

    public static function form(Schema $schema): Schema
    {
        return ReservasiPesawatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservasiPesawatsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PenumpangPesawatRelationManager::class,
            RiwayatPembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservasiPesawats::route('/'),
            'create' => CreateReservasiPesawat::route('/create'),
            'edit' => EditReservasiPesawat::route('/{record}/edit'),
        ];
    }
}
