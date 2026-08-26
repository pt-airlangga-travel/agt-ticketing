<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\Pages\CreateTicketingBandara;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\Pages\EditTicketingBandara;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\Pages\ListTicketingBandaras;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\Schemas\TicketingBandaraForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\Tables\TicketingBandarasTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingBandara;
use UnitEnum;

class TicketingBandaraResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingBandara::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Bandar Udara';

    protected static ?string $slug = 'bandar-udara';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Bandar Udara';

    public static function form(Schema $schema): Schema
    {
        return TicketingBandaraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingBandarasTable::configure($table);
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
            'index' => ListTicketingBandaras::route('/'),
            // 'create' => CreateTicketingBandara::route('/create'),
            // 'edit' => EditTicketingBandara::route('/{record}/edit'),
        ];
    }
}
