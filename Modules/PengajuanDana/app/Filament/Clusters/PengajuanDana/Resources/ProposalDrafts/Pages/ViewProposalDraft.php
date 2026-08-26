<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasClusterSubNavigation;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\ProposalDraftResource;

class ViewProposalDraft extends ViewRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = ProposalDraftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit'),
        ];
    }

    public function getTitle(): string
    {
        return 'Detail';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $submission = $this->record->submissions()
            ->where('status', ProposalSubmissionStatus::Menunggu->value)
            ->latest('id')
            ->first();

        if ($submission) {
            $data['needs'] = $submission->needs()->pluck('judan_needs.id')->all();
        }

        return $data;
    }
}