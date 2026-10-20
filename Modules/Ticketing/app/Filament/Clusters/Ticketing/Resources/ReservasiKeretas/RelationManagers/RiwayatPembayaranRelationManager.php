<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema as DBSchema;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasExportRiwayatPembayaranBulkAction;
use Modules\Ticketing\Models\TicketingPembayar;
use Modules\Ticketing\Models\TicketingPembayaranPenumpang;
use Modules\Ticketing\Models\TicketingUnitKerja;

class RiwayatPembayaranRelationManager extends RelationManager
{
    use HasExportRiwayatPembayaranBulkAction;

    protected static bool $shouldSkipAuthorization = true;

    protected static string $relationship = 'ticketingPembayaranPenumpang';

    protected static ?string $title = 'Riwayat Pembayaran';

    protected ?array $terbayarPerPenumpangCache = null;

    public function form(Schema $schema): Schema
    {
        $this->terbayarPerPenumpangCache = null;

        return $schema->schema([
            Select::make('tckt_penumpang_id')
                ->label('Penumpang')
                ->options(function (): array {
                    $penumpangs = $this->getOwnerRecord()?->ticketingPenumpang ?? collect();

                    if ($penumpangs->isEmpty()) {
                        return [];
                    }

                    $pemesanan = $this->getOwnerRecord()?->ticketingPemesanan;

                    return $penumpangs->mapWithKeys(fn ($penumpang): array => [
                        $penumpang->id => ($pemesanan?->isPenumpangLunas($penumpang->id) || $this->sisaTagihan($penumpang->id) <= 0)
                            ? $penumpang->nama_penumpang.' (Lunas)'
                            : $penumpang->nama_penumpang.' (Sisa Rp '.number_format($this->sisaTagihan($penumpang->id), 0, ',', '.').')',
                    ])->all();
                })
                ->disableOptionWhen(fn (string $value): bool => $this->sisaTagihan($value) <= 0 && ! (bool) ($this->getOwnerRecord()?->ticketingPemesanan?->isPenumpangLunas($value) ?? false))
                ->helperText(function (Get $get): ?string {
                    $penumpangId = $get('tckt_penumpang_id');

                    if (blank($penumpangId)) {
                        return null;
                    }

                    $isLunas = (bool) ($this->getOwnerRecord()?->ticketingPemesanan?->isPenumpangLunas($penumpangId) ?? false);
                    $sisa = $this->sisaTagihan($penumpangId);

                    return ($isLunas || $sisa <= 0)
                        ? 'Penumpang ini sudah lunas.'
                        : 'Sisa tagihan: Rp '.number_format($sisa, 0, ',', '.');
                })
                ->searchable()
                ->preload()
                ->live()
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
                ->preload(),

            Select::make('tckt_unit_kerja_id')
                ->label('Unit Kerja Pembayar')
                ->options(fn () => TicketingUnitKerja::query()
                    ->where('is_active', true)
                    ->pluck('nama_unit_kerja', 'id'))
                ->searchable()
                ->preload(),

            TextInput::make('jumlah_membayar')
                ->label('Jumlah Bayar')
                ->numeric()
                ->required()
                ->default(0)
                ->prefix('Rp')
                ->maxValue(function (Get $get): int {
                    $penumpangId = $get('tckt_penumpang_id');

                    return blank($penumpangId) ? PHP_INT_MAX : $this->sisaTagihan($penumpangId);
                }),

            DatePicker::make('tgl_membayar')
                ->label('Tanggal Bayar')
                ->nullable(),

            FileUpload::make('bukti_pembayaran')
                ->label('Bukti Pembayaran')
                ->image()
                ->directory('bukti-pembayaran')
                ->nullable(),

            Toggle::make('status_bukti_bayar')
                ->label('Bukti Valid')
                ->default(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(fn (): string => ($this->getOwnerRecord()?->ticketingPemesanan?->isSemuaPenumpangLunas() ?? false) ? 'Status Invoice: Semua Penumpang Lunas' : 'Status Invoice: Belum Semua Penumpang Lunas')
            ->modifyQueryUsing(fn ($query) => $query->with([
                'ticketingPenumpang.ticketingPembayaranPenumpang',
            ]))
            ->columns([
                TextColumn::make('ticketingPenumpang.nama_penumpang')
                    ->label('Nama Penumpang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ticketingPembayar.nama_pembayar')
                    ->label('Nama Pembayar')
                    ->default(fn (Model $record) => $record->nama_pembayar ?? '-')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja')
                    ->placeholder('-'),

                TextColumn::make('jumlah_membayar')
                    ->label('Jumlah')
                    ->sortable(),

                TextColumn::make('total_per_penumpang')
                    ->label('Total')
                    ->state(function ($record) {
                        $tiket = $this->getOwnerRecord();
                        $penumpangs = $tiket->ticketingPenumpang;
                        $total = $tiket->ticketingPemesanan?->harga_jual;

                        if (! $penumpangs || $penumpangs->isEmpty()) {
                            return 0;
                        }

                        return (int) round((int) $total / $penumpangs->count());
                    })
                    ->sortable(),

                TextColumn::make('terbayar_per_penumpang')
                    ->label('Terbayar')
                    ->state(function ($record) {
                        return $record->ticketingPenumpang?->ticketingPembayaranPenumpang
                            ->sum('jumlah_membayar');
                    })
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('sisa_per_penumpang')
                    ->label('Sisa')
                    ->state(function ($record) {
                        $tiket = $this->getOwnerRecord();
                        $pemesanan = $tiket?->ticketingPemesanan;
                        $isLunas = (bool) ($pemesanan?->isPenumpangLunas($record->tckt_penumpang_id) ?? false);
                        if ($isLunas) {
                            return 0;
                        }

                        $penumpangs = $tiket->ticketingPenumpang;
                        $total = $pemesanan?->harga_jual;

                        $hargaSatuan = ($penumpangs && $penumpangs->isNotEmpty())
                            ? (int) round((int) $total / $penumpangs->count())
                            : 0;

                        $terbayar = $record->ticketingPenumpang?->ticketingPembayaranPenumpang
                            ->sum('jumlah_membayar');

                        return max($hargaSatuan - $terbayar, 0);
                    })
                    ->formatStateUsing(function ($state, $record) {
                        $pemesanan = $this->getOwnerRecord()?->ticketingPemesanan;
                        $isLunas = (bool) ($pemesanan?->isPenumpangLunas($record->tckt_penumpang_id) ?? false);
                        if ($isLunas || (int) $state <= 0) {
                            return 'Lunas';
                        }

                        return 'Rp '.number_format((int) $state, 0, ',', '.');
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        $pemesanan = $this->getOwnerRecord()?->ticketingPemesanan;
                        $isLunas = (bool) ($pemesanan?->isPenumpangLunas($record->tckt_penumpang_id) ?? false);

                        return ($isLunas || (int) $state <= 0) ? 'success' : 'warning';
                    })
                    ->sortable(),

                TextColumn::make('tgl_membayar')
                    ->label('Tgl Bayar')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status_bukti_bayar')
                    ->label('Bukti')
                    ->formatStateUsing(fn ($state) => $state ? 'Valid' : 'Belum')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('creator.name')
                    ->label('Diinput Oleh')
                    ->placeholder('-'),
            ])
            ->headerActions([
                Action::make('statusLunasPenumpang')
                    ->label('Status Lunas Penumpang')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->modalHeading('Status Lunas Penumpang')
                    ->modalDescription('Pilih penumpang yang statusnya telah lunas pada reservasi ini.')
                    ->form([
                        CheckboxList::make('lunas_penumpang_ids')
                            ->label('Daftar Penumpang')
                            ->options(function (): array {
                                $tiket = $this->getOwnerRecord();
                                $pemesanan = $tiket?->ticketingPemesanan;
                                $penumpangs = $tiket?->ticketingPenumpang ?? collect();
                                $total = (int) ($pemesanan?->harga_jual ?? 0);
                                $hargaSatuan = ($penumpangs->isNotEmpty())
                                    ? (int) round($total / $penumpangs->count())
                                    : 0;

                                return $penumpangs->mapWithKeys(function ($penumpang) use ($hargaSatuan): array {
                                    $terbayar = (int) $penumpang->ticketingPembayaranPenumpang->sum('jumlah_membayar');
                                    $info = 'Rp '.number_format($terbayar, 0, ',', '.').' / Rp '.number_format($hargaSatuan, 0, ',', '.');

                                    return [
                                        $penumpang->id => "{$penumpang->nama_penumpang} (Terbayar: {$info})",
                                    ];
                                })->all();
                            })
                            ->default(fn (): array => $this->getOwnerRecord()?->ticketingPemesanan?->getLunasPenumpangIds() ?? []),
                    ])
                    ->action(function (array $data): void {
                        $pemesanan = $this->getOwnerRecord()?->ticketingPemesanan;
                        if ($pemesanan) {
                            $selected = array_map('intval', $data['lunas_penumpang_ids'] ?? []);
                            $pemesanan->update(['lunas_penumpang_ids' => $selected]);
                            Notification::make()
                                ->title('Status lunas penumpang berhasil diperbarui')
                                ->success()
                                ->send();
                        }
                    }),

                CreateAction::make()
                    ->label('Tambah Pembayaran')
                    ->using(function (array $data): Model {
                        $pembayaran = $this->getOwnerRecord()?->ticketingPemesanan?->ticketingPembayaran;

                        if (! $pembayaran) {
                            abort(400, 'Belum ada data pembayaran untuk reservasi ini.');
                        }

                        $payload = [
                            'tckt_penumpang_id' => $data['tckt_penumpang_id'],
                            'tckt_pembayaran_id' => $pembayaran->id,
                            'tckt_pembayar_id' => $data['tckt_pembayar_id'] ?? null,
                            'tckt_unit_kerja_id' => $data['tckt_unit_kerja_id'] ?? null,
                            'jumlah_membayar' => $data['jumlah_membayar'],
                            'tgl_membayar' => $data['tgl_membayar'] ?? null,
                            'bukti_pembayaran' => $data['bukti_pembayaran'] ?? null,
                            'status_bukti_bayar' => $data['status_bukti_bayar'] ?? false,
                            'user_id' => auth()->id(),
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

                        return TicketingPembayaranPenumpang::create($payload);
                    }),
            ])
            ->bulkActions([
                $this->exportRiwayatPembayaranBulkAction(),
                BulkAction::make('tandai_valid')
                    ->label('Tandai Bukti Valid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (BulkAction $action, $records): void {
                        foreach ($records as $record) {
                            $record->update(['status_bukti_bayar' => true]);
                        }
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->button()
                    ->hiddenLabel(),

                DeleteAction::make()
                    ->button()
                    ->hiddenLabel(),
            ]);
    }

    protected function sisaTagihan(int|string $penumpangId): int
    {
        if ((bool) ($this->getOwnerRecord()?->ticketingPemesanan?->isPenumpangLunas($penumpangId) ?? false)) {
            return 0;
        }

        return max($this->hargaSatuan() - ($this->terbayarPerPenumpang()[$penumpangId] ?? 0), 0);
    }

    protected function hargaSatuan(): int
    {
        $tiket = $this->getOwnerRecord();
        $penumpangs = $tiket?->ticketingPenumpang ?? collect();

        if ($penumpangs->isEmpty()) {
            return 0;
        }

        return (int) round((int) $tiket->ticketingPemesanan?->harga_jual / $penumpangs->count());
    }

    protected function terbayarPerPenumpang(): array
    {
        if ($this->terbayarPerPenumpangCache !== null) {
            return $this->terbayarPerPenumpangCache;
        }

        $penumpangIds = ($this->getOwnerRecord()?->ticketingPenumpang ?? collect())
            ->pluck('id')
            ->all();

        $this->terbayarPerPenumpangCache = empty($penumpangIds)
            ? []
            : TicketingPembayaranPenumpang::query()
                ->whereIn('tckt_penumpang_id', $penumpangIds)
                ->groupBy('tckt_penumpang_id')
                ->selectRaw('tckt_penumpang_id, SUM(jumlah_membayar) as total_terbayar')
                ->pluck('total_terbayar', 'tckt_penumpang_id')
                ->map(fn ($total) => (int) $total)
                ->all();

        return $this->terbayarPerPenumpangCache;
    }
}

