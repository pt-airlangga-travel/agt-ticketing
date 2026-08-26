<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Modules\PengajuanDana\Enums\ProposalDraftStatus;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasFormattedNumber;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\ViewProposalDraft;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\ProposalSubmissionResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Schemas\ProposalSubmissionCreateSchema;
use Modules\PengajuanDana\Services\ProposalSubmissionService;

class ProposalDraftsTable
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

                TextColumn::make('no_pengajuan')
                    ->label('No. Pengajuan')
                    ->formatStateUsing(fn ($state) => self::formatDefinedId((string) $state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event.nama')
                    ->label('Event')
                    ->formatStateUsing(function ($record) {
                        $identity = $record->event?->event_identity;

                        if (! $identity) {
                            return $record->event?->nama;
                        }

                        return '<div>' . e($record->event->nama) . '</div>'
                            . '<div class="text-xs text-gray-400 dark:text-gray-500">' . e($identity) . '</div>';
                    })
                    ->html()
                    ->wrap()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('deadline_pembayaran')
                    ->label('Deadline Pembayaran')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('catatan_member')
                    ->label('Catatan Pengajuan')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('vendors_count')
                    ->label('Vendor')
                    ->counts('vendors')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProposalDraftStatus $state): string => $state?->label() ?? '-')
                    ->color(fn (?ProposalDraftStatus $state): string => $state?->color() ?? 'gray')
                    ->sortable(),

                TextColumn::make('file_attached')
                    ->label('File Pengajuan')
                    ->formatStateUsing(fn ($state) => filled($state) ? 'Unduh' : '-')
                    ->icon(fn ($state) => filled($state) ? Heroicon::OutlinedArrowDownTray : null)
                    ->color('primary')
                    ->action(function ($record) {
                        if (filled($record->file_attached) && Storage::disk('local')->exists('proposal/' . $record->file_attached)) {
                            return response()->download(
                                Storage::disk('local')->path('proposal/' . $record->file_attached)
                            );
                        }
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ProposalDraftStatus::cases())->mapWithKeys(
                        fn (ProposalDraftStatus $status): array => [$status->value => $status->label()]
                    )),

                SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn ($record): string => ViewProposalDraft::getUrl(['record' => $record])),

                Action::make('ajukan')
                    ->label('Ajukan')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('primary')
                    ->modalHeading('Buat Proposal Submission')
                    ->modalDescription(fn ($record): string => 'Ajukan ' . self::formatDefinedId($record->no_pengajuan) . ' (' . $record->event?->nama . ')?')
                    ->form(ProposalSubmissionCreateSchema::getFields())
                    ->visible(fn ($record): bool => $record->status === ProposalDraftStatus::Menunggu
                        && auth()->user()?->canAccess(ProposalSubmissionResource::getRbacPermissionNames()['create']))
                    ->action(function ($record, array $data): void {
                        try {
                            app(ProposalSubmissionService::class)->submit($record, auth()->user(), $data);
                            Notification::make()->success()->title('Proposal submission berhasil dibuat')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        }
                    }),

                Action::make('download')
                    ->label('Unduh')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->visible(fn ($record): bool => filled($record->file_attached)
                        && Storage::disk('local')->exists('proposal/' . $record->file_attached))
                    ->action(fn ($record) => response()->download(
                        Storage::disk('local')->path('proposal/' . $record->file_attached)
                    )),

                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->modalHeading('Edit Proposal Draft'),
            ]);
    }
}