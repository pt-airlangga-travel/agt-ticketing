<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
// use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;

class TicketingBandarasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('nama_bandara')
                    ->label('Nama Bandar Udara')
                    ->searchable(),
                TextColumn::make('kode_bandara')
                    ->label('Kode')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->toggleable()
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            // ->paginationMode(PaginationMode::Default)
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button()->hiddenLabel()->modalHeading('Edit Bandar Udara'),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
