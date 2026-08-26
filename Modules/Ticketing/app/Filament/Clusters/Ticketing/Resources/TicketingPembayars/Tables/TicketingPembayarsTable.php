<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;

class TicketingPembayarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('nama_pembayar')
                    ->label('Nama Pembayar')
                    ->searchable(),
            ])
            ->paginationMode(PaginationMode::Default)
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button()->hiddenLabel()->modalHeading('Edit Nama Pembayar'),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
