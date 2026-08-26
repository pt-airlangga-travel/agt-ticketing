<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\Pages\CreateTicketingUnitKerja;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\Pages\EditTicketingUnitKerja;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\Pages\ListTicketingUnitKerjas;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\Schemas\TicketingUnitKerjaForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\Tables\TicketingUnitKerjasTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingUnitKerja;
use UnitEnum;

class TicketingUnitKerjaResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingUnitKerja::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Unit Kerja';

    protected static ?string $slug = 'unit-kerja';

    protected static ?int $navigationSort = 7;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Unit Kerja';

    public static function form(Schema $schema): Schema
    {
        return TicketingUnitKerjaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingUnitKerjasTable::configure($table);
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
            'index' => ListTicketingUnitKerjas::route('/'),
            // 'create' => CreateTicketingUnitKerja::route('/create'),
            // 'edit' => EditTicketingUnitKerja::route('/{record}/edit'),
        ];
    }
}
