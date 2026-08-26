<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;

class TicketingUnitKerjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('nama_unit_kerja')
                    ->label('Unit Kerja')
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->toggleable()
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->paginationMode(PaginationMode::Default)
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button()->hiddenLabel()->modalHeading('Edit Unit Kerja'),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
