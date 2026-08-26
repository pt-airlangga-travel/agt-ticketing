<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasFormattedNumber;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\ViewProposalDraft;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\ProposalSubmissionResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Schemas\ProposalSubmissionCreateSchema;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Schemas\ProposalSubmissionViewSchema;
use Modules\PengajuanDana\Services\ProposalSubmissionService;

class ProposalSubmissionsRelationManager extends RelationManager
{
    use HasFormattedNumber;

    protected static string $relationship = 'submissions';

    protected static ?string $relatedResource = ProposalSubmissionResource::class;

    protected static ?string $label = 'Proposal Submission';

    protected static ?string $pluralLabel = 'Proposal Submissions';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewProposalDraft::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('create-submission')
                    ->label('Buat Proposal Submission')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->modalHeading('Buat Proposal Submission')
                    ->form(ProposalSubmissionCreateSchema::getFields())
                    ->visible(fn (): bool => $this->getPageClass() === ViewProposalDraft::class
                        && auth()->user()?->canAccess(
                            ProposalSubmissionResource::getRbacPermissionNames()['create']
                        ) ?? false)
                    ->action(function (array $data): void {
                        try {
                            app(ProposalSubmissionService::class)->submit($this->getOwnerRecord(), auth()->user(), $data);
                            Notification::make()->success()->title('Proposal submission berhasil dibuat')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make('view-detail')
                    ->label('Lihat')
                    ->modalHeading(fn ($record) => 'Detail Submission ' . self::formatDefinedId((string) $record->no_submission))
                    ->schema(ProposalSubmissionViewSchema::getFields()),
            ])
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('no_submission')
                    ->label('No. Submission')
                    ->formatStateUsing(fn ($state) => self::formatDefinedId((string) $state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_identity')
                    ->label('Identitas Event')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('needs_count')
                    ->label('Kebutuhan')
                    ->counts('needs')
                    ->badge()
                    ->color('info'),

                TextColumn::make('bank_accounts_count')
                    ->label('Rekening')
                    ->counts('bankAccounts')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProposalSubmissionStatus $state): string => $state?->label() ?? '-')
                    ->color(fn (?ProposalSubmissionStatus $state): string => $state?->color() ?? 'gray')
                    ->sortable(),
            ]);
    }

    protected function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['needs', 'bankAccounts']);
    }
}