<?php

namespace Modules\User\Filament\Clusters\User\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $groups = Permission::query()
            ->where('is_active', true)
            ->get()
            ->groupBy('group_name');

        $components = [];

        // 🔹 Section Role
        $components[] = Section::make('Akses')
            ->schema([
                TextInput::make('name')
                    ->label('Nama Akses')
                    ->required()
                    ->disabled(fn ($record) => $record?->id == 1)
                    ->unique(ignoreRecord: true),

                Toggle::make('is_active')
                    ->inline(false)
                    ->default(true)
                    ->label('Aktif')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(fn ($record) => $record?->id == 1),
            ])
            ->columns(2)
            ->columnSpanFull();

        // 🔹 Section Permissions (per group)
        foreach ($groups as $group => $permissions) {
            $components[] = Section::make($group ?? 'Other')
                ->schema([
                    CheckboxList::make('permissions_'.($group ?? 'other'))
                        ->label('Izin Akses')
                        ->options(
                            $permissions->pluck('display_name', 'id')->toArray()
                        )
                        ->columns(2)
                        ->bulkToggleable()
                        ->afterStateHydrated(function ($component, $state, $record) use ($permissions) {
                            if ($record) {
                                $component->state(
                                    $record->permissions
                                        ->whereIn('id', $permissions->pluck('id'))
                                        ->pluck('id')
                                        ->toArray()
                                );
                            }
                        })
                        ->dehydrated(false),
                ])
                ->columnSpanFull();
        }

        return $schema->components($components);
    }
}
