<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;

class TicketingPenumpangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('nama_penumpang')
                    ->label('Nama Penumpang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn ($state): string => (int) $state ? 'Perempuan' : 'Laki-laki')
                    ->sortable(),
            ])
            ->paginationMode(PaginationMode::Default)
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button()->hiddenLabel()->modalHeading('Edit Nama Penumpang'),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
