<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasPrintInvoiceBulkAction;
use Modules\Ticketing\Models\TicketingPembayar;
use Modules\Ticketing\Models\TicketingPembayaranPenumpang;
use Modules\Ticketing\Models\TicketingPenumpang;
use Modules\Ticketing\Models\TicketingUnitKerja;

class PenumpangPesawatRelationManager extends RelationManager
{
    use HasPrintInvoiceBulkAction;

    protected static string $relationship = 'ticketingPembayaranPenumpang';

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

            Select::make('tckt_pembayar_id')
                ->label('Nama Pembayar')
                ->options(function (): array {
                    if (! Schema::hasTable('ticketing_pembayar')) {
                        return [];
                    }

                    return TicketingPembayar::query()
                        ->pluck('nama_pembayar', 'id')
                        ->all();
                })
                ->searchable()
                ->preload(),

            Select::make('unit_kerja_pembayar')
                ->label('Unit Kerja Pembayar')
                ->options(fn () => TicketingUnitKerja::query()
                    ->where('is_active', true)
                    ->pluck('nama_unit_kerja', 'id'))
                ->searchable()
                ->preload(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticketingPenumpang.nama_penumpang')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('ticketingPenumpang.jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn ($state): string => (int) $state ? 'Perempuan' : 'Laki-laki'),

                TextColumn::make('ticketingPembayar.nama_pembayar')
                    ->label('Pembayar'),

                TextColumn::make('ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Penumpang')
                    ->mutateFormDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->using(function (array $data): Model {
                        $pembayaran = $this->getOwnerRecord()?->ticketingPemesanan?->ticketingPembayaran;

                        if (! $pembayaran) {
                            abort(400, 'Belum ada data pembayaran untuk reservasi ini.');
                        }

                        $penumpang = TicketingPenumpang::create([
                            'nama_penumpang' => $data['nama_penumpang'],
                            'jenis_kelamin' => $data['jenis_kelamin'],
                        ]);

                        $this->getOwnerRecord()->ticketingPenumpang()->attach($penumpang);

                        return TicketingPembayaranPenumpang::create([
                            'tckt_penumpang_id' => $penumpang->id,
                            'tckt_pembayaran_id' => $pembayaran->id,
                            'tckt_pembayar_id' => $data['tckt_pembayar_id'] ?? null,
                            'tckt_unit_kerja_id' => $data['unit_kerja_pembayar'] ?? null,
                            'jumlah_membayar' => 0,
                            'user_id' => auth()->id(),
                        ]);
                    }),
            ])
            ->bulkActions([
                $this->printInvoiceBulkAction('print-invoice-pesawat', 'tckt_penumpang_id'),
            ])
            ->actions([
                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->mutateRecordDataUsing(function (array $data, Model $record): array {
                        $data['nama_penumpang'] = $record->ticketingPenumpang?->nama_penumpang;
                        $data['jenis_kelamin'] = $record->ticketingPenumpang?->jenis_kelamin;
                        $data['tckt_pembayar_id'] = $record->tckt_pembayar_id;
                        $data['unit_kerja_pembayar'] = $record->tckt_unit_kerja_id;

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        $record->ticketingPenumpang?->update([
                            'nama_penumpang' => $data['nama_penumpang'],
                            'jenis_kelamin' => $data['jenis_kelamin'],
                        ]);

                        $record->update([
                            'tckt_pembayar_id' => $data['tckt_pembayar_id'] ?? null,
                            'tckt_unit_kerja_id' => $data['unit_kerja_pembayar'] ?? null,
                        ]);

                        return $record;
                    }),

                DeleteAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->using(function (Model $record): void {
                        $this->getOwnerRecord()->ticketingPenumpang()->detach($record->tckt_penumpang_id);
                        $record->delete();
                    }),
            ]);
    }
}
