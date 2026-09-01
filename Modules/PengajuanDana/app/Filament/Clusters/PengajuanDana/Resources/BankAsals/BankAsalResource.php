<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\BankAsals;

use App\Filament\Concerns\HasRbacPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\PengajuanDanaCluster;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\BankAsals\Pages\CreateBankAsal;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\BankAsals\Pages\EditBankAsal;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\BankAsals\Pages\ListBankAsals;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\BankAsals\Schemas\BankAsalForm;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\BankAsals\Tables\BankAsalsTable;
use Modules\PengajuanDana\Models\BankAsal;
use UnitEnum;

class BankAsalResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = BankAsal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Bank Asal';

    protected static ?string $slug = 'bank-asals';

    protected static ?int $navigationSort = 4;

    protected static ?string $cluster = PengajuanDanaCluster::class;

    protected static ?string $recordTitleAttribute = 'nama_bank';

    // navigation group
    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return BankAsalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankAsalsTable::configure($table);
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
            'index' => ListBankAsals::route('/'),
            // 'create' => CreateBankAsal::route('/create'),
            // 'edit' => EditBankAsal::route('/{record}/edit'),
        ];
    }
}
