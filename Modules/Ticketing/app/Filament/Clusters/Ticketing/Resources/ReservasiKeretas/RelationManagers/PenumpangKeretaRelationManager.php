<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Modules\Ticketing\Models\TicketingPenumpang;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasPrintInvoiceBulkAction;

class PenumpangKeretaRelationManager extends RelationManager
{
    use HasPrintInvoiceBulkAction;

    protected static bool $shouldSkipAuthorization = true;

    protected static string $relationship = 'ticketingPenumpang';

    protected static ?string $title = 'Daftar Penumpang';

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
                TextColumn::make('penumpang_id')
                    ->label('Penumpang ID')
                    ->state(function (\Illuminate\Database\Eloquent\Model $record, $livewire) {
                        $owner = $livewire->getOwnerRecord();
                        $invoice = $owner?->ticketingPemesanan?->invoice ?? $owner?->invoice ?? '-';
                        $index = $owner?->ticketingPenumpang?->search(fn($r) => $r->id === $record->id);
                        return $index !== false ? $invoice . '-' . ($index + 1) : '-';
                    })
                    ->searchable(false),

                TextColumn::make('nama_penumpang')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (int $state): string => $state ? 'Perempuan' : 'Laki-laki'),

                IconColumn::make('lunas')
                    ->label('Lunas')
                    ->alignCenter()
                    ->boolean()
                    ->state(function (Model $record, $livewire): bool {
                        $pemesanan = $livewire->getOwnerRecord()?->ticketingPemesanan;

                        return (bool) ($pemesanan?->isPenumpangLunas($record->id) ?? false);
                    })
                    ->action(function (Model $record, $livewire): void {
                        $pemesanan = $livewire->getOwnerRecord()?->ticketingPemesanan;
                        if ($pemesanan) {
                            $isLunas = $pemesanan->isPenumpangLunas($record->id);
                            $pemesanan->setPenumpangLunas($record->id, ! $isLunas);
                            Notification::make()
                                ->title((! $isLunas) ? "Penumpang {$record->nama_penumpang} ditandai lunas" : "Status lunas {$record->nama_penumpang} dibatalkan")
                                ->success()
                                ->send();
                        }
                    })
                    ->tooltip(function (Model $record, $livewire): string {
                        $pemesanan = $livewire->getOwnerRecord()?->ticketingPemesanan;
                        $isLunas = (bool) ($pemesanan?->isPenumpangLunas($record->id) ?? false);

                        return $isLunas ? 'Lunas (klik untuk batalkan)' : 'Belum Lunas (klik untuk tandai lunas)';
                    }),
            ])
            ->headerActions([
                Action::make('tambah_penumpang')
                    ->label('Tambah Penumpang')
                    ->form([
                        Select::make('penumpang_id')
                            ->label('Nama Penumpang')
                            ->options(fn () => TicketingPenumpang::query()->pluck('nama_penumpang', 'id'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('nama_penumpang')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([0 => 'Laki-laki', 1 => 'Perempuan'])
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return TicketingPenumpang::create([
                                    'nama_penumpang' => $data['nama_penumpang'],
                                    'jenis_kelamin' => $data['jenis_kelamin'],
                                ])->id;
                            })
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $livewire->getOwnerRecord()->ticketingPenumpang()->syncWithoutDetaching([$data['penumpang_id']]);
                    }),
            ])
            ->bulkActions([
                $this->printInvoiceBulkAction('print-invoice-kereta'),
            ])
            ->actions([
                EditAction::make()->button()->hiddenLabel(),

                DetachAction::make()->button()->hiddenLabel(),
            ]);
    }
}
