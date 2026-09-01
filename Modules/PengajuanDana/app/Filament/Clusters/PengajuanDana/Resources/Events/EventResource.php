<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\PengajuanDanaCluster;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Pages\CreateEvent;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Pages\EditEvent;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Pages\ListEvents;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Pages\ViewEvent;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\RelationManagers\ProposalDraftsRelationManager;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Schemas\EventForm;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Schemas\EventInfolist;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Tables\EventsTable;
use Modules\PengajuanDana\Models\Event;

class EventResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Event';

    protected static ?string $slug = 'events';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = PengajuanDanaCluster::class;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProposalDraftsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'view' => ViewEvent::route('/{record}'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
