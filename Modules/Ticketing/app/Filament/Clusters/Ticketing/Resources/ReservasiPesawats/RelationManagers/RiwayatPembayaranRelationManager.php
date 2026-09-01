<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\RelationManagers;

use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Ticketing\Models\TicketingPembayar;
use Modules\Ticketing\Models\TicketingPembayaranPenumpang;
use Modules\Ticketing\Models\TicketingUnitKerja;

class RiwayatPembayaranRelationManager extends RelationManager
{
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

                    return $penumpangs->mapWithKeys(fn ($penumpang): array => [
                        $penumpang->id => $this->sisaTagihan($penumpang->id) <= 0
                            ? $penumpang->nama_penumpang.' (Lunas)'
                            : $penumpang->nama_penumpang.' (Sisa Rp '.number_format($this->sisaTagihan($penumpang->id), 0, ',', '.').')',
                    ])->all();
                })
                ->disableOptionWhen(fn (string $value): bool => $this->sisaTagihan($value) <= 0)
                ->helperText(function (Get $get): ?string {
                    $penumpangId = $get('tckt_penumpang_id');

                    if (blank($penumpangId)) {
                        return null;
                    }

                    $sisa = $this->sisaTagihan($penumpangId);

                    return $sisa <= 0
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
                    if (! Schema::hasTable('ticketing_pembayar')) {
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
            ->modifyQueryUsing(fn ($query) => $query->with([
                'ticketingPenumpang.ticketingPembayaranPenumpang',
            ]))
            ->columns([
                TextColumn::make('ticketingPenumpang.nama_penumpang')
                    ->label('Penumpang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ticketingPembayar.nama_pembayar')
                    ->label('Pembayar')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja')
                    ->placeholder('-'),

                TextColumn::make('jumlah_membayar')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
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
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
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
                        $penumpangs = $tiket->ticketingPenumpang;
                        $total = $tiket->ticketingPemesanan?->harga_jual;

                        $hargaSatuan = ($penumpangs && $penumpangs->isNotEmpty())
                            ? (int) round((int) $total / $penumpangs->count())
                            : 0;

                        $terbayar = $record->ticketingPenumpang?->ticketingPembayaranPenumpang
                            ->sum('jumlah_membayar');

                        return max($hargaSatuan - $terbayar, 0);
                    })
                    ->formatStateUsing(function ($state) {
                        return (int) $state > 0
                            ? 'Rp '.number_format((int) $state, 0, ',', '.')
                            : 'Lunas';
                    })
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'warning' : 'success')
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
                CreateAction::make()
                    ->label('Tambah Pembayaran')
                    ->using(function (array $data): Model {
                        $pembayaran = $this->getOwnerRecord()?->ticketingPemesanan?->ticketingPembayaran;

                        if (! $pembayaran) {
                            abort(400, 'Belum ada data pembayaran untuk reservasi ini.');
                        }

                        return TicketingPembayaranPenumpang::create([
                            'tckt_penumpang_id' => $data['tckt_penumpang_id'],
                            'tckt_pembayaran_id' => $pembayaran->id,
                            'tckt_pembayar_id' => $data['tckt_pembayar_id'] ?? null,
                            'tckt_unit_kerja_id' => $data['tckt_unit_kerja_id'] ?? null,
                            'jumlah_membayar' => $data['jumlah_membayar'],
                            'tgl_membayar' => $data['tgl_membayar'] ?? null,
                            'bukti_pembayaran' => $data['bukti_pembayaran'] ?? null,
                            'status_bukti_bayar' => $data['status_bukti_bayar'] ?? false,
                            'user_id' => auth()->id(),
                        ]);
                    }),
            ])
            ->bulkActions([
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

    protected function sisaTagihan(int $penumpangId): int
    {
        return max($this->hargaSatuan() - $this->terbayarPerPenumpang()[$penumpangId] ?? 0, 0);
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
