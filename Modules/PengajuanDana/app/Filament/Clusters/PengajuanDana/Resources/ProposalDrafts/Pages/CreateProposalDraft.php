<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\PengajuanDana\Enums\ProposalDraftStatus;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasClusterSubNavigation;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Pages\ListEvents;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\ProposalDraftResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Schemas\ProposalDraftCreateForm;
use Modules\PengajuanDana\Models\Event;
use Modules\PengajuanDana\Services\ProposalDraftService;
use Modules\PengajuanDana\Services\ProposalSubmissionService;

class CreateProposalDraft extends CreateRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = ProposalDraftResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        parent::mount();

        $eventKey = request()->query('event_id');

        if (! $eventKey) {
            $this->redirect(ListEvents::getUrl());

            return;
        }

        $event = Event::query()
            ->where((new Event)->getRouteKeyName(), $eventKey)
            ->first();

        if (! $event) {
            $this->redirect(ListEvents::getUrl());

            return;
        }

        $this->form->fill([
            'event_id' => $event->getKey(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return ProposalDraftCreateForm::configure($schema);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $event = Event::query()->findOrFail($data['event_id']);
        $data['no_pengajuan'] = app(ProposalDraftService::class)->generateNoPengajuan($event);
        $data['creative_member_id'] = auth()->id();
        $data['status'] = 'menunggu';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $draft = parent::handleRecordCreation($data);

            $service = app(ProposalSubmissionService::class);
            $sequence = $draft->submissions()->count() + 1;

            $submission = $draft->submissions()->create([
                'no_submission' => $service->generateNoSubmission($draft),
                'event_identity' => $service->generateEventIdentity($draft->event, $draft, $sequence),
                'organizer_admin_id' => auth()->id(),
                'status' => ProposalSubmissionStatus::Menunggu->value,
            ]);

            $submission->needs()->sync($data['needs'] ?? []);

            foreach ($data['bankAccounts'] ?? [] as $account) {
                $submission->bankAccounts()->create($account);
            }

            $draft->update([
                'status' => ProposalDraftStatus::Diajukan->value,
            ]);

            return $draft;
        });
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (! $record->file_attached) {
            return;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists('proposal/'.$record->file_attached)) {
            return;
        }

        $extension = pathinfo($record->file_attached, PATHINFO_EXTENSION);
        $newName = $record->no_pengajuan.'.'.$extension;

        $disk->move('proposal/'.$record->file_attached, 'proposal/'.$newName);
        $record->forceFill(['file_attached' => $newName])->save();
    }
}
