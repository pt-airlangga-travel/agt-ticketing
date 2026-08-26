<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TicketingHotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('nama_hotel')
                    ->label('Nama Hotel')
                    ->searchable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->wrap()
                    ->searchable(),
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
                EditAction::make()->button()->hiddenLabel()->modalHeading('Edit Hotel'),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
