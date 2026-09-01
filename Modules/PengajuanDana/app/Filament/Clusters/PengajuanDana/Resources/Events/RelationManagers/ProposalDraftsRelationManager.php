<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Modules\PengajuanDana\Enums\ProposalDraftStatus;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Pages\ViewEvent;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\ProposalDraftResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\ProposalSubmissionResource;

class ProposalDraftsRelationManager extends RelationManager
{
    protected static string $relationship = 'proposalDrafts';

    protected static ?string $relatedResource = ProposalDraftResource::class;

    protected static ?string $label = 'Proposal Draft';

    protected static ?string $pluralLabel = 'Proposal Drafts';

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('created_at', 'desc')
            // ->filtersLayout(FiltersLayout::AboveContent)
            ->headerActions([
                Action::make('create-draft')
                    ->label('Buat Pengajuan')
                    ->icon(Heroicon::OutlinedPlus)
                    ->visible(fn (): bool => $this->getPageClass() === ViewEvent::class
                        && auth()->user()?->canAccess(
                            ProposalDraftResource::getRbacPermissionNames()['create']
                        ) ?? false)
                    ->url(fn (): string => ProposalDraftResource::getUrl('create', [
                        'event_id' => $this->getOwnerRecord()->getRouteKey(),
                    ])),
            ])
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('no_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('creativeMember.name')
                    ->label('Pemohon')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('vendors_count')
                    ->label('Vendor')
                    ->counts('vendors')
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_vendor')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.')),

                TextColumn::make('deadline_pembayaran')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProposalDraftStatus $state): string => $state?->label() ?? '-')
                    ->color(fn (?ProposalDraftStatus $state): string => $state?->color() ?? 'gray')
                    ->sortable(),
            ])
            ->filters([
                // SelectFilter::make('status')
                //     ->options(collect(ProposalDraftStatus::cases())->mapWithKeys(
                //         fn (ProposalDraftStatus $status): array => [$status->value => $status->label()]
                //     )),
            ])
            ->recordActions([
                Action::make('ajukan')
                    ->label('Ajukan')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('primary')
                    ->visible(fn ($record): bool => $record->status === ProposalDraftStatus::Menunggu
                        && auth()->user()?->canAccess(ProposalSubmissionResource::getRbacPermissionNames()['create']))
                    ->url(fn ($record): string => ProposalSubmissionResource::getUrl('create', [
                        'judan_proposal_draft_id' => $record->getKey(),
                    ])),

                Action::make('download')
                    ->label('Unduh')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->visible(fn ($record): bool => filled($record->file_attached)
                        && Storage::disk('local')->exists('proposal/'.$record->file_attached))
                    ->action(fn ($record) => response()->download(
                        Storage::disk('local')->path('proposal/'.$record->file_attached)
                    )),

                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->modalHeading('Edit Proposal Draft'),
            ]);
    }

    protected function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['creativeMember'])
            ->withCount('vendors')
            ->withSum('vendors as total_vendor', 'sub_total');

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canAccess(ProposalDraftResource::getRbacPermissionNames()['view'])) {
            return $query;
        }

        return $query->where('creative_member_id', $user->getKey());
    }
}
