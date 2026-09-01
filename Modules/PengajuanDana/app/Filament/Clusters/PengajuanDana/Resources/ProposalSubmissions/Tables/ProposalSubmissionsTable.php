<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasFormattedNumber;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\ProposalSubmissionResource;
use Modules\PengajuanDana\Models\BankAsal;
use Modules\PengajuanDana\Services\ProposalSubmissionService;

class ProposalSubmissionsTable
{
    use HasFormattedNumber;

    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('created_at', 'desc')
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('no_submission')
                    ->label('No. Submission')
                    ->formatStateUsing(fn ($state) => self::formatDefinedId((string) $state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('proposalDraft.no_pengajuan')
                    ->label('No. Pengajuan')
                    ->formatStateUsing(fn ($state) => self::formatDefinedId((string) $state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('proposalDraft.event.nama')
                    ->label('Event')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('needs')
                    ->label('Kebutuhan')
                    ->formatStateUsing(function ($record): HtmlString {
                        $badges = $record->needs
                            ->map(fn ($need): string => sprintf(
                                '<span class="fi-badge rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset ring-primary-600/20 bg-primary-50 text-primary-700">%s</span>',
                                e($need->nama_kebutuhan)
                            ))
                            ->implode(' ');

                        return new HtmlString($badges);
                    })
                    ->listWithLineBreaks(),

                TextColumn::make('bankAccounts_count')
                    ->label('Rekening')
                    ->counts('bankAccounts')
                    ->badge()
                    ->color('info'),

                TextColumn::make('bankTransfers_count')
                    ->label('Transfer')
                    ->counts('bankTransfers')
                    ->badge()
                    ->color('success')
                    ->visible(fn ($record): bool => in_array($record?->status, [
                        ProposalSubmissionStatus::ProsesTransfer,
                        ProposalSubmissionStatus::Selesai,
                    ], true)),

                TextColumn::make('inspiringManager.name')
                    ->label('Manager')
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProposalSubmissionStatus $state): string => $state?->label() ?? '-')
                    ->color(fn (?ProposalSubmissionStatus $state): string => $state?->color() ?? 'gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ProposalSubmissionStatus::cases())->mapWithKeys(
                        fn (ProposalSubmissionStatus $status): array => [$status->value => $status->label()]
                    )),

                SelectFilter::make('judan_proposal_draft_id')
                    ->label('Proposal Draft')
                    ->relationship('proposalDraft', 'no_pengajuan')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon(Heroicon::OutlinedHandThumbUp)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Submission')
                    ->modalDescription(fn ($record): string => 'Setujui submission '.$record->no_submission.'?')
                    ->visible(fn ($record): bool => static::canApprove($record))
                    ->action(function ($record): void {
                        try {
                            app(ProposalSubmissionService::class)->approve($record, auth()->user());
                            Notification::make()->success()->title('Submission disetujui')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon(Heroicon::OutlinedHandThumbDown)
                    ->color('danger')
                    ->modalHeading('Tolak Submission')
                    ->form([
                        Textarea::make('catatan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record): bool => static::canApprove($record))
                    ->action(function ($record, array $data): void {
                        try {
                            app(ProposalSubmissionService::class)->reject($record, auth()->user(), $data['catatan'] ?? null);
                            Notification::make()->success()->title('Submission ditolak')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        }
                    }),

                ActionGroup::make([
                    Action::make('transfer')
                        ->label('Transfer')
                        ->icon(Heroicon::OutlinedBanknotes)
                        ->color('primary')
                        ->modalHeading('Input Bukti Transfer')
                        ->modalDescription(fn ($record): string => 'Submission '.$record->no_submission)
                        ->form(fn ($record): array => [
                            Select::make('judan_bank_account_id')
                                ->label('Rekening Tujuan')
                                ->options(fn (): array => $record->bankAccounts
                                    ->filter(fn ($account): bool => $account->bankTransfers->isEmpty())
                                    ->mapWithKeys(fn ($account): array => [
                                        $account->getKey() => sprintf(
                                            '%s - %s - %s (Rp %s)',
                                            $account->bank?->nama_bank ?? '-',
                                            $account->pemilik,
                                            $account->nomor_rekening,
                                            number_format((float) $account->sub_total, 0, ',', '.')
                                        ),
                                    ])
                                    ->all())
                                ->required()
                                ->searchable(),

                            Select::make('judan_bank_asal_id')
                                ->label('Bank Asal')
                                ->options(fn (): array => BankAsal::query()
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(fn ($bank): array => [
                                        $bank->getKey() => "{$bank->nama_bank} - {$bank->no_rekening}",
                                    ])
                                    ->all())
                                ->searchable()
                                ->required(),

                            TextInput::make('melalui')
                                ->label('Melalui')
                                ->required()
                                ->maxLength(255),

                            DatePicker::make('tanggal_transfer')
                                ->label('Tanggal Transfer')
                                ->default(now()->toDateString())
                                ->required(),

                            FileUpload::make('file_attached')
                                ->label('Bukti Transfer')
                                ->disk('local')
                                ->directory('bukti_tf')
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                ->maxSize(5120)
                                ->required(),
                        ])
                        ->action(function ($record, array $data): void {
                            try {
                                app(ProposalSubmissionService::class)->recordTransfer($record, auth()->user(), $data);
                                Notification::make()->success()->title('Bukti transfer berhasil disimpan')->send();
                            } catch (\Throwable $e) {
                                Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                            }
                        }),

                    Action::make('selesai')
                        ->label('Selesai')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Selesai')
                        ->modalDescription(fn ($record): string => 'Tandai submission '.$record->no_submission.' sebagai selesai (reimburse berhasil)?')
                        ->action(function ($record): void {
                            try {
                                app(ProposalSubmissionService::class)->complete($record);
                                Notification::make()->success()->title('Reimburse diselesaikan')->send();
                            } catch (\Throwable $e) {
                                Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                            }
                        }),

                    Action::make('tolak-reimburse')
                        ->label('Tolak Reimburse')
                        ->icon(Heroicon::OutlinedHandThumbDown)
                        ->color('danger')
                        ->modalHeading('Tolak Reimburse')
                        ->modalDescription(fn ($record): string => 'Submission '.$record->no_submission.' akan ditandai ditolak.')
                        ->form([
                            Textarea::make('catatan')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data): void {
                            try {
                                app(ProposalSubmissionService::class)->rejectReimburse($record, $data['catatan'] ?? null);
                                Notification::make()->success()->title('Reimburse ditolak')->send();
                            } catch (\Throwable $e) {
                                Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                            }
                        }),
                ])
                    ->visible(fn ($record): bool => static::canTransfer($record)),

                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->modalHeading('Edit Submission'),
            ]);
    }

    private static function canApprove($record): bool
    {
        return $record->status === ProposalSubmissionStatus::Menunggu
            && auth()->user()?->canAccess(ProposalSubmissionResource::getRbacPermissionNames()['edit']) ?? false;
    }

    private static function canTransfer($record): bool
    {
        return $record->status === ProposalSubmissionStatus::ProsesTransfer
            && auth()->user()?->canAccess(ProposalSubmissionResource::getRbacPermissionNames()['edit']) ?? false;
    }
}
