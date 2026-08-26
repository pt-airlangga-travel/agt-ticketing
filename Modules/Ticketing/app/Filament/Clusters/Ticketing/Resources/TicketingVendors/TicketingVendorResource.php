<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\Pages\CreateTicketingVendor;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\Pages\EditTicketingVendor;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\Pages\ListTicketingVendors;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\Schemas\TicketingVendorForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\Tables\TicketingVendorsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingVendor;
use UnitEnum;

class TicketingVendorResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingVendor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Vendor';

    protected static ?string $slug = 'vendor';

    protected static ?int $navigationSort = 6;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Vendor';

    public static function form(Schema $schema): Schema
    {
        return TicketingVendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingVendorsTable::configure($table);
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
            'index' => ListTicketingVendors::route('/'),
            // 'create' => CreateTicketingVendor::route('/create'),
            // 'edit' => EditTicketingVendor::route('/{record}/edit'),
        ];
    }
}
