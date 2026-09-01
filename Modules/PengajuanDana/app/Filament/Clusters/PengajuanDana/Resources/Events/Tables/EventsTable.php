<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('tanggal_mulai', 'desc')
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('nama')
                    ->label('Event')
                    ->wrap()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('institution.nama_institusi')
                    ->label('Institusi')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('tanggal_mulai')
                    ->label('Periode')
                    ->formatStateUsing(function ($state, $record) {
                        $mulai = $record->tanggal_mulai
                            ? Carbon::parse($record->tanggal_mulai)->format('d M Y')
                            : '-';
                        $selesai = $record->tanggal_selesai
                            ? Carbon::parse($record->tanggal_selesai)->format('d M Y')
                            : '-';

                        return new HtmlString(<<<HTML
                            <div class="text-xs text-gray-500 dark:text-gray-400">{$mulai} &rarr; {$selesai}</div>
                        HTML);
                    })
                    ->sortable(),

                TextColumn::make('proposalDrafts_count')
                    ->label('Pengajuan')
                    ->counts('proposalDrafts')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Availability')
                    ->alignCenter()
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Pengajuan')
                    ->button(),

                EditAction::make()
                    ->label('Edit')
                    ->button(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
