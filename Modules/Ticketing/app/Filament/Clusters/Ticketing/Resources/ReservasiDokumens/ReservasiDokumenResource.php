<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Pages\CreateReservasiDokumen;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Pages\EditReservasiDokumen;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Pages\ListReservasiDokumens;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\RelationManagers\PemilikDokumenRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\RelationManagers\RiwayatPembayaranRelationManager;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Schemas\ReservasiDokumenForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Tables\ReservasiDokumensTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingPemesanan;
use UnitEnum;

class ReservasiDokumenResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingPemesanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static UnitEnum|string|null $navigationGroup = 'Reservasi';

    protected static ?string $navigationLabel = 'Reservasi Dokumen';

    protected static ?string $breadcrumb = 'Reservasi Dokumen';

    protected static ?string $slug = 'reservasi-dokumen';

    protected static ?int $navigationSort = 4;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'invoice';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->select([
                'ticketing_pemesanan.id',
                'ticketing_pemesanan.invoice',
                'ticketing_pemesanan.nama_customer',
                'ticketing_pemesanan.tckt_kategori_pemesanan_id',
                'ticketing_pemesanan.tckt_unit_kerja_id',
                'ticketing_pemesanan.status_pemesanan',
                'ticketing_pemesanan.pulang_pergi',
                'ticketing_pemesanan.tanggal_pemesanan',
                'ticketing_pemesanan.harga_beli',
                'ticketing_pemesanan.harga_publish',
                'ticketing_pemesanan.harga_jual',
                'ticketing_pemesanan.created_by',
                'ticketing_pemesanan.created_at',
                'ticketing_pemesanan.updated_at',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->leftJoin('users', 'ticketing_pemesanan.created_by', '=', 'users.id');

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // PRIORITAS: akses penuh (bisa melihat semua reservasi)
        if ($user->canAccess(static::getRbacPermissionNames()['view'])) {
            return $query;
        }

        // Terbatas: hanya melihat reservasi yang dibuat oleh dirinya sendiri
        return $query->where('ticketing_pemesanan.created_by', $user->id);
    }

    public static function form(Schema $schema): Schema
    {
        return ReservasiDokumenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservasiDokumensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PemilikDokumenRelationManager::class,
            RiwayatPembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservasiDokumens::route('/'),
            'create' => CreateReservasiDokumen::route('/create'),
            'edit' => EditReservasiDokumen::route('/{record}/edit'),
        ];
    }
}
