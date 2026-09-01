<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasClusterSubNavigation;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages\ListProposalDrafts;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\ProposalSubmissionResource;
use Modules\PengajuanDana\Models\ProposalDraft;
use Modules\PengajuanDana\Services\ProposalSubmissionService;

class CreateProposalSubmission extends CreateRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = ProposalSubmissionResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        parent::mount();

        $draftId = request()->query('judan_proposal_draft_id');

        if (! $draftId) {
            $this->redirect(ListProposalDrafts::getUrl());

            return;
        }

        $this->form->fill([
            'judan_proposal_draft_id' => $draftId,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(ProposalSubmissionService::class);

        $draft = ProposalDraft::query()
            ->with('event.institution')
            ->findOrFail($data['judan_proposal_draft_id']);

        $service->assertCanSubmit($draft);

        $sequence = $draft->submissions()->count() + 1;

        $data['no_submission'] = $service->generateNoSubmission($draft);
        $data['event_identity'] = $service->generateEventIdentity($draft->event, $draft, $sequence);
        $data['organizer_admin_id'] = auth()->id();
        $data['status'] = 'menunggu';

        $draft->update([
            'status' => 'diajukan',
            'organizer_admin_id' => auth()->id(),
        ]);

        return $data;
    }
}
