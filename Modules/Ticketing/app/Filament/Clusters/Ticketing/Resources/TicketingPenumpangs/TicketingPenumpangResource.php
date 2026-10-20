<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Pages\CreateTicketingPenumpang;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Pages\EditTicketingPenumpang;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Pages\ListTicketingPenumpangs;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Schemas\TicketingPenumpangForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Tables\TicketingPenumpangsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingPenumpang;
use UnitEnum;

class TicketingPenumpangResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingPenumpang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Nama Penumpang';

    protected static ?string $slug = 'nama-penumpang';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'nama_penumpang';

    public static function form(Schema $schema): Schema
    {
        return TicketingPenumpangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingPenumpangsTable::configure($table);
    }

    // Sementara: bypass RBAC agar menu langsung tampil tanpa menunggu seeder.
    // TODO: hapus setelah permission master-data.nama-penumpang.* di-seed ke role ticketing.
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return true;
    }

    public static function canDeleteAny(): bool
    {
        return true;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTicketingPenumpangs::route('/'),
            // 'create' => CreateTicketingPenumpang::route('/create'),
            // 'edit' => EditTicketingPenumpang::route('/{record}/edit'),
        ];
    }
}
