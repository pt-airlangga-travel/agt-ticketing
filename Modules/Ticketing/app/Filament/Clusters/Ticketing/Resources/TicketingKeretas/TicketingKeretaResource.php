<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas\Pages\CreateTicketingKereta;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas\Pages\EditTicketingKereta;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas\Pages\ListTicketingKeretas;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas\Schemas\TicketingKeretaForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas\Tables\TicketingKeretasTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingKereta;
use UnitEnum;

class TicketingKeretaResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingKereta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Kereta Api';

    protected static ?string $slug = 'kereta-api';

    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Kereta';

    public static function form(Schema $schema): Schema
    {
        return TicketingKeretaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingKeretasTable::configure($table);
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
            'index' => ListTicketingKeretas::route('/'),
            // 'create' => CreateTicketingKereta::route('/create'),
            // 'edit' => EditTicketingKereta::route('/{record}/edit'),
        ];
    }
}
