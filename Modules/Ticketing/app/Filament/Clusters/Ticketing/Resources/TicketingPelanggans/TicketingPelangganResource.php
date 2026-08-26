<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\Pages\CreateTicketingPelanggan;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\Pages\EditTicketingPelanggan;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\Pages\ListTicketingPelanggans;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\Schemas\TicketingPelangganForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\Tables\TicketingPelanggansTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingPelanggan;
use UnitEnum;

class TicketingPelangganResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingPelanggan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Nama Pelanggan';

    protected static ?string $slug = 'nama-pelanggan';

    protected static ?int $navigationSort = 8;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Nama Pelanggan';

    public static function form(Schema $schema): Schema
    {
        return TicketingPelangganForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingPelanggansTable::configure($table);
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
            'index' => ListTicketingPelanggans::route('/'),
            // 'create' => CreateTicketingPelanggan::route('/create'),
            // 'edit' => EditTicketingPelanggan::route('/{record}/edit'),
        ];
    }
}
