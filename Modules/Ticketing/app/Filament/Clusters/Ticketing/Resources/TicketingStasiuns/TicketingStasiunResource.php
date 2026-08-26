<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\Pages\CreateTicketingStasiun;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\Pages\EditTicketingStasiun;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\Pages\ListTicketingStasiuns;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\Schemas\TicketingStasiunForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\Tables\TicketingStasiunsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingStasiun;
use UnitEnum;

class TicketingStasiunResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingStasiun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Stasiun';

    protected static ?string $slug = 'stasiun';

    protected static ?int $navigationSort = 4;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Stasiun';

    public static function form(Schema $schema): Schema
    {
        return TicketingStasiunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingStasiunsTable::configure($table);
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
            'index' => ListTicketingStasiuns::route('/'),
            // 'create' => CreateTicketingStasiun::route('/create'),
            // 'edit' => EditTicketingStasiun::route('/{record}/edit'),
        ];
    }
}
