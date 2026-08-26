<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Pages\CreateTicketingPembayar;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Pages\EditTicketingPembayar;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Pages\ListTicketingPembayars;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Schemas\TicketingPembayarForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Tables\TicketingPembayarsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingPembayar;
use UnitEnum;

class TicketingPembayarResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingPembayar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Nama Pembayar';

    protected static ?string $slug = 'nama-pembayar';

    protected static ?int $navigationSort = 9;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Nama Pembayar';

    public static function form(Schema $schema): Schema
    {
        return TicketingPembayarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingPembayarsTable::configure($table);
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
            'index' => ListTicketingPembayars::route('/'),
            // 'create' => CreateTicketingPembayar::route('/create'),
            // 'edit' => EditTicketingPembayar::route('/{record}/edit'),
        ];
    }
}
