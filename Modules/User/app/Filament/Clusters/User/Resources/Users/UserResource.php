<?php

namespace Modules\User\Filament\Clusters\User\Resources\Users;

use App\Filament\Concerns\HasRbacPermission;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\User\Filament\Clusters\User\Resources\Users\Pages\CreateUser;
use Modules\User\Filament\Clusters\User\Resources\Users\Pages\EditUser;
use Modules\User\Filament\Clusters\User\Resources\Users\Pages\ListUsers;
use Modules\User\Filament\Clusters\User\Resources\Users\Pages\ViewUser;
use Modules\User\Filament\Clusters\User\Resources\Users\Schemas\UserForm;
use Modules\User\Filament\Clusters\User\Resources\Users\Schemas\UserInfolist;
use Modules\User\Filament\Clusters\User\Resources\Users\Tables\UsersTable;
use Modules\User\Filament\Clusters\User\UserCluster;

class UserResource extends Resource
{
    use HasRbacPermission;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = UserCluster::class;

    protected static ?string $slug = 'akun'; // atau 'daftar', sesuai maunya

    protected static ?string $navigationLabel = 'Akun';

    protected static ?string $recordTitleAttribute = 'Akun';

    protected static ?string $modelLabel = 'Akun';

    // navigaiton sort
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
