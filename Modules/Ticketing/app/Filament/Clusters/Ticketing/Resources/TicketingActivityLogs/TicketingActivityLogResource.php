<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingActivityLogs;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingActivityLogs\Pages\ListTicketingActivityLogs;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingActivityLogs\Tables\TicketingActivityLogsTable;
use Modules\Ticketing\Filament\Clusters\Ticketing\TicketingCluster;
use Modules\Ticketing\Models\TicketingActivityLog;
use UnitEnum;

class TicketingActivityLogResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = TicketingActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $slug = 'log-aktivitas';

    protected static ?int $navigationSort = 9;

    protected static ?string $cluster = TicketingCluster::class;

    protected static ?string $recordTitleAttribute = 'Log Aktivitas';

    public static function table(Table $table): Table
    {
        return TicketingActivityLogsTable::configure($table);
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
            'index' => ListTicketingActivityLogs::route('/'),
        ];
    }
}
