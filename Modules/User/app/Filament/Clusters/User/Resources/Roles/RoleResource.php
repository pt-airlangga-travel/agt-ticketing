<?php

namespace Modules\User\Filament\Clusters\User\Resources\Roles;

use App\Filament\Concerns\HasRbacPermission;
use App\Models\Role;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\User\Filament\Clusters\User\Resources\Roles\Pages\CreateRole;
use Modules\User\Filament\Clusters\User\Resources\Roles\Pages\EditRole;
use Modules\User\Filament\Clusters\User\Resources\Roles\Pages\ListRoles;
use Modules\User\Filament\Clusters\User\Resources\Roles\Pages\ViewRole;
use Modules\User\Filament\Clusters\User\Resources\Roles\Schemas\RoleForm;
use Modules\User\Filament\Clusters\User\Resources\Roles\Schemas\RoleInfolist;
use Modules\User\Filament\Clusters\User\Resources\Roles\Tables\RolesTable;
use Modules\User\Filament\Clusters\User\UserCluster;

class RoleResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = UserCluster::class;

    protected static ?string $recordTitleAttribute = 'Akses';

    protected static ?string $navigationLabel = 'Akses';

    protected static ?string $modelLabel = 'Akses';

    protected static ?string $pluralModelLabel = 'Akses'; // "akses" sama bentuk singular/plural-nya

    protected static ?string $slug = 'akses'; // atau 'daftar', sesuai maunya

    // navigaiton sort
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
