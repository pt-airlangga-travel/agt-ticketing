<?php

namespace Modules\User\Filament\Clusters\User\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(false)
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Nama Akses')
                    ->searchable(),

                // count user
                TextColumn::make('users_count')
                    ->counts('users')
                    ->alignCenter()
                    ->label('Total Akun')
                    ->badge(),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->alignCenter()
                    ->label('Total Izin Akses')
                    ->badge(),

                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->toggleable()
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->button()->hiddenLabel(),
                EditAction::make()->button()->hiddenLabel(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
