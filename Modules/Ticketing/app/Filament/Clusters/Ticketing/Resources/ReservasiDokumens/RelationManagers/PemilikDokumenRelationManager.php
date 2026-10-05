<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasPrintInvoiceBulkAction;

class PemilikDokumenRelationManager extends RelationManager
{
    use HasPrintInvoiceBulkAction;

    protected static bool $shouldSkipAuthorization = true;

    protected static string $relationship = 'ticketingPenumpang';

    protected static ?string $title = 'Daftar Pemilik Dokumen';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('nama_penumpang')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            Select::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->options([0 => 'Laki-laki', 1 => 'Perempuan'])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_penumpang')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (int $state): string => $state ? 'Perempuan' : 'Laki-laki'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Pemilik Dokumen'),
            ])
            ->bulkActions([
                $this->printInvoiceBulkAction('print-invoice-dokumen'),
            ])
            ->actions([
                EditAction::make()->button()->hiddenLabel(),

                DetachAction::make()->button()->hiddenLabel(),
            ]);
    }
}
