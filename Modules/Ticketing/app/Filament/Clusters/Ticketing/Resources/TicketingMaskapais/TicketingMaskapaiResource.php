<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\Pages\CreateTicketingMaskapai;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\Pages\EditTicketingMaskapai;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\Pages\ListTicketingMaskapais;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\Schemas\TicketingMaskapaiForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\Tables\TicketingMaskapaisTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingMaskapai;
use UnitEnum;

class TicketingMaskapaiResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingMaskapai::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Maskapai';

    protected static ?string $slug = 'maskapai';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Maskapai Penerbangan';

    public static function form(Schema $schema): Schema
    {
        return TicketingMaskapaiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingMaskapaisTable::configure($table);
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
            'index' => ListTicketingMaskapais::route('/'),
            // 'create' => CreateTicketingMaskapai::route('/create'),
            // 'edit' => EditTicketingMaskapai::route('/{record}/edit'),
        ];
    }
}
