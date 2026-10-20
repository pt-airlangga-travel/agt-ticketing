<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\DetachAction;
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
use Illuminate\Support\Facades\Schema as DBSchema;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasPrintInvoiceBulkAction;
use Modules\Ticketing\Models\TicketingPembayar;
use Modules\Ticketing\Models\TicketingPembayaranPenumpang;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingPenumpang;
use Modules\Ticketing\Models\TicketingUnitKerja;

class PenumpangHotelRelationManager extends RelationManager
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

            Select::make('tckt_pembayar_id')
                ->label('Nama Pembayar')
                ->options(function (): array {
                    if (! DBSchema::hasTable('ticketing_pembayar')) {
                        return [];
                    }

                    return TicketingPembayar::query()
                        ->pluck('nama_pembayar', 'id')
                        ->all();
                })
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('nama_pembayar')
                        ->label('Nama Pembayar')
                        ->required()
                        ->maxLength(255),
                ])
                ->createOptionUsing(fn (array $data) => TicketingPembayar::firstOrCreate([
                    'nama_pembayar' => $data['nama_pembayar'],
                ])->id),

            Select::make('unit_kerja_pembayar')
                ->label('Unit Kerja Pembayar')
                ->options(fn () => TicketingUnitKerja::query()
                    ->where('is_active', true)
                    ->pluck('nama_unit_kerja', 'id'))
                ->searchable()
                ->preload()
                ->afterStateHydrated(function ($component, $record) {
                    if ($record) {
                        $owner = $this->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $pembayaranId = $pemesanan?->ticketingPembayaran?->id;
                        $pembayaranPenumpang = $record->ticketingPembayaranPenumpang()
                            ->when($pembayaranId, fn ($q) => $q->where('tckt_pembayaran_id', $pembayaranId))
                            ->first() ?? $record->ticketingPembayaranPenumpang->first();
                        $component->state($pembayaranPenumpang?->tckt_unit_kerja_id ?? $pembayaranPenumpang?->ticketingPembayar?->tckt_unit_kerja_id);
                    }
                }),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('penumpang_id')
                    ->label('Penumpang ID')
                    ->state(function (Model $record, $livewire) {
                        $owner = $livewire->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $invoice = $pemesanan?->invoice ?? $owner?->invoice ?? '-';
                        $penumpangs = $owner?->ticketingPenumpang;
                        if ($penumpangs instanceof \Illuminate\Database\Eloquent\Collection) {
                            $index = $penumpangs->search(fn($r) => $r->id === $record->id);
                        } else {
                            $index = false;
                        }
                        return $index !== false ? $invoice . '-' . ($index + 1) : '-';
                    })
                    ->searchable(false),

                TextColumn::make('nama_penumpang')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn ($state): string => (int) $state ? 'Perempuan' : 'Laki-laki'),

                TextColumn::make('pembayar')
                    ->label('Pembayar')
                    ->state(function (Model $record, $livewire) {
                        $owner = $livewire->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $pembayaranId = $pemesanan?->ticketingPembayaran?->id;
                        $pembayaran = $record->ticketingPembayaranPenumpang()
                            ->when($pembayaranId, fn ($q) => $q->where('tckt_pembayaran_id', $pembayaranId))
                            ->first() ?? $record->ticketingPembayaranPenumpang->first();
                        return $pembayaran?->ticketingPembayar?->nama_pembayar
                            ?? $pembayaran?->nama_pembayar
                            ?? '-';
                    }),

                TextColumn::make('unit_kerja')
                    ->label('Unit Kerja Pembayar')
                    ->state(function (Model $record, $livewire) {
                        $owner = $livewire->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $pembayaranId = $pemesanan?->ticketingPembayaran?->id;
                        $pembayaranPenumpang = $record->ticketingPembayaranPenumpang()
                            ->when($pembayaranId, fn ($q) => $q->where('tckt_pembayaran_id', $pembayaranId))
                            ->first() ?? $record->ticketingPembayaranPenumpang->first();
                        return $pembayaranPenumpang?->ticketingUnitKerja?->nama_unit_kerja
                            ?? $pembayaranPenumpang?->ticketingPembayar?->ticketingUnitKerja?->nama_unit_kerja
                            ?? '-';
                    }),

                IconColumn::make('lunas')
                    ->label('Lunas')
                    ->alignCenter()
                    ->boolean()
                    ->state(function (Model $record, $livewire): bool {
                        $owner = $livewire->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;

                        return (bool) ($pemesanan?->isPenumpangLunas($record->id) ?? false);
                    })
                    ->action(function (Model $record, $livewire): void {
                        $owner = $livewire->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
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
                        $owner = $livewire->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
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

                        Select::make('tckt_pembayar_id')
                            ->label('Nama Pembayar')
                            ->options(function (): array {
                                if (! DBSchema::hasTable('ticketing_pembayar')) {
                                    return [];
                                }

                                return TicketingPembayar::query()
                                    ->pluck('nama_pembayar', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('nama_pembayar')
                                    ->label('Nama Pembayar')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data) => TicketingPembayar::firstOrCreate([
                                'nama_pembayar' => $data['nama_pembayar'],
                            ])->id),

                        Select::make('unit_kerja_pembayar')
                            ->label('Unit Kerja Pembayar')
                            ->options(fn () => TicketingUnitKerja::query()
                                ->where('is_active', true)
                                ->pluck('nama_unit_kerja', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $owner = $livewire->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $penumpangId = $data['penumpang_id'];

                        $owner->ticketingPenumpang()->syncWithoutDetaching([$penumpangId]);

                        if ($pemesanan) {
                            $pembayaran = $pemesanan->ticketingPembayaran;
                            if (! $pembayaran) {
                                $pembayaran = $pemesanan->ticketingPembayaran()->create([
                                    'tckt_unit_kerja_id' => $pemesanan->tckt_unit_kerja_id,
                                    'nama_pembayar' => $pemesanan->nama_customer ?? '-',
                                ]);
                            }

                            if ($pembayaran) {
                                $payload = [
                                    'tckt_penumpang_id' => $penumpangId,
                                    'tckt_pembayaran_id' => $pembayaran->id,
                                    'tckt_pembayar_id' => $data['tckt_pembayar_id'] ?? null,
                                    'tckt_unit_kerja_id' => $data['unit_kerja_pembayar'] ?? null,
                                    'jumlah_membayar' => 0,
                                    'user_id' => auth()->id() ?? 1,
                                ];

                                if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_pembayar_id')) {
                                    unset($payload['tckt_pembayar_id']);
                                }
                                if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_unit_kerja_id')) {
                                    unset($payload['tckt_unit_kerja_id']);
                                }
                                if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'nama_pembayar')) {
                                    unset($payload['nama_pembayar']);
                                }

                                TicketingPembayaranPenumpang::updateOrCreate([
                                    'tckt_penumpang_id' => $penumpangId,
                                    'tckt_pembayaran_id' => $pembayaran->id,
                                ], $payload);
                            }
                        }
                    }),
            ])
            ->bulkActions([
                $this->printInvoiceBulkAction('print-invoice-hotel'),
            ])
            ->actions([
                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->mutateRecordDataUsing(function (array $data, Model $record): array {
                        $owner = $this->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $pembayaranId = $pemesanan?->ticketingPembayaran?->id;
                        $pembayaranPenumpang = $record->ticketingPembayaranPenumpang()
                            ->when($pembayaranId, fn ($q) => $q->where('tckt_pembayaran_id', $pembayaranId))
                            ->first() ?? $record->ticketingPembayaranPenumpang->first();

                        $data['nama_penumpang'] = $record->nama_penumpang;
                        $data['jenis_kelamin'] = $record->jenis_kelamin;
                        $data['tckt_pembayar_id'] = $pembayaranPenumpang?->tckt_pembayar_id;
                        $data['unit_kerja_pembayar'] = $pembayaranPenumpang?->tckt_unit_kerja_id;

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        $record->update([
                            'nama_penumpang' => $data['nama_penumpang'],
                            'jenis_kelamin' => $data['jenis_kelamin'],
                        ]);

                        $owner = $this->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $pembayaran = $pemesanan?->ticketingPembayaran;
                        if (! $pembayaran && $pemesanan) {
                            $pembayaran = $pemesanan->ticketingPembayaran()->create([
                                'tckt_unit_kerja_id' => $pemesanan->tckt_unit_kerja_id,
                                'nama_pembayar' => $pemesanan->nama_customer ?? '-',
                            ]);
                        }

                        $pembayaranPenumpang = $pembayaran ? $record->ticketingPembayaranPenumpang()
                            ->where('tckt_pembayaran_id', $pembayaran->id)
                            ->first() : $record->ticketingPembayaranPenumpang->first();

                        if ($pembayaranPenumpang) {
                            $payload = [
                                'tckt_pembayar_id' => $data['tckt_pembayar_id'] ?? null,
                                'tckt_unit_kerja_id' => $data['unit_kerja_pembayar'] ?? null,
                            ];
                            if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_pembayar_id')) {
                                unset($payload['tckt_pembayar_id']);
                            }
                            if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_unit_kerja_id')) {
                                unset($payload['tckt_unit_kerja_id']);
                            }

                            $pembayaranPenumpang->update($payload);
                        } elseif ($pembayaran) {
                            $payload = [
                                'tckt_penumpang_id' => $record->id,
                                'tckt_pembayaran_id' => $pembayaran->id,
                                'tckt_pembayar_id' => $data['tckt_pembayar_id'] ?? null,
                                'tckt_unit_kerja_id' => $data['unit_kerja_pembayar'] ?? null,
                                'jumlah_membayar' => 0,
                                'user_id' => auth()->id() ?? 1,
                            ];

                            if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_pembayar_id')) {
                                unset($payload['tckt_pembayar_id']);
                            }
                            if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_unit_kerja_id')) {
                                unset($payload['tckt_unit_kerja_id']);
                            }
                            if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'nama_pembayar')) {
                                unset($payload['nama_pembayar']);
                            }

                            TicketingPembayaranPenumpang::create($payload);
                        }

                        return $record;
                    }),

                DetachAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->after(function (Model $record): void {
                        $owner = $this->getOwnerRecord();
                        $pemesanan = $owner instanceof TicketingPemesanan ? $owner : $owner?->ticketingPemesanan;
                        $pembayaranId = $pemesanan?->ticketingPembayaran?->id;
                        if ($pembayaranId) {
                            $record->ticketingPembayaranPenumpang()
                                ->where('tckt_pembayaran_id', $pembayaranId)
                                ->delete();
                        }
                    }),
            ]);
    }
}
