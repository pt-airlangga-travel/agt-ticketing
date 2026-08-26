<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasFormattedNumber;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\ViewProposalDraft;
use Modules\PengajuanDana\Models\ProposalSubmission;

class RecentSubmissionsWidget extends TableWidget
{
    use HasFormattedNumber;

    protected static ?int $sort = 3;

    protected function getTableQuery(): Builder
    {
        return ProposalSubmission::query()
            ->with(['proposalDraft.event', 'bankAccounts'])
            ->latest('created_at')
            ->limit(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordUrl(fn ($record): ?string => $record->proposalDraft
                ? ViewProposalDraft::getUrl(['record' => $record->proposalDraft])
                : null)
            ->columns([
                TextColumn::make('no_submission')
                    ->label('No. Submission')
                    ->formatStateUsing(fn ($state) => self::formatDefinedId((string) $state)),

                TextColumn::make('proposalDraft.event.nama')
                    ->label('Event')
                    ->wrap(),

                TextColumn::make('total')
                    ->label('Total')
                    ->getStateUsing(fn ($record): string => 'Rp ' . number_format((float) $record->bankAccounts->sum('sub_total'), 0, ',', '.')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProposalSubmissionStatus $state): string => $state?->label() ?? '-')
                    ->color(fn (?ProposalSubmissionStatus $state): string => $state?->color() ?? 'gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y'),
            ]);
    }
}