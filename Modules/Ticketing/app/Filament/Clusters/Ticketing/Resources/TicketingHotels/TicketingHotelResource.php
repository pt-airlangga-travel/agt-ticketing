<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\Pages\CreateTicketingHotel;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\Pages\EditTicketingHotel;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\Pages\ListTicketingHotels;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\Schemas\TicketingHotelForm;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\Tables\TicketingHotelsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingHotel;
use UnitEnum;

class TicketingHotelResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingHotel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Hotel';

    protected static ?string $slug = 'hotel';

    protected static ?int $navigationSort = 5;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Hotel';

    public static function form(Schema $schema): Schema
    {
        return TicketingHotelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketingHotelsTable::configure($table);
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
            'index' => ListTicketingHotels::route('/'),
            // 'create' => CreateTicketingHotel::route('/create'),
            // 'edit' => EditTicketingHotel::route('/{record}/edit'),
        ];
    }
}
