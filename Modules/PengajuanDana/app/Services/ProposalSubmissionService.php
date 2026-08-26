<?php

namespace Modules\PengajuanDana\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\PengajuanDana\Enums\ProposalDraftStatus;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Models\BankAccount;
use Modules\PengajuanDana\Models\BankTransfer;
use Modules\PengajuanDana\Models\Event;
use Modules\PengajuanDana\Models\ProposalDraft;
use Modules\PengajuanDana\Models\ProposalSubmission;
use RuntimeException;

class ProposalSubmissionService
{
    public const MAX_RESTATEMENT = 3;

    /**
     * Generate nomor submission 17 karakter:
     * 12 digit kode event + 3 digit index draft + 2 digit index submission.
     */
    public function generateNoSubmission(ProposalDraft $draft): string
    {
        $eventCode = str_pad((string) $draft->event->getKey(), 12, '0', STR_PAD_LEFT);
        $draftIndex = substr($draft->no_pengajuan, 12, 3);

        $sequence = ProposalSubmission::query()
            ->where('judan_proposal_draft_id', $draft->getKey())
            ->count() + 1;

        return $eventCode
            . $draftIndex
            . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Nomor surat (event identity) sesuai format lama:
     * tahun/institusi/nama_singkat/bulan/tanggal/index_submission.
     */
    public function generateEventIdentity(Event $event, ProposalDraft $draft, int $sequence): string
    {
        $institution = $event->institution;
        $institutionCode = $institution->kode_institusi
            ?: str_pad((string) $institution->getKey(), 3, '0', STR_PAD_LEFT);

        return collect([
            $event->tanggal_mulai?->format('Y'),
            $institutionCode,
            $event->nama_singkat,
            $event->tanggal_mulai?->format('m'),
            $event->tanggal_mulai?->format('d'),
            str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
        ])->join('/');
    }

    public function assertCanSubmit(ProposalDraft $draft): void
    {
        $activeCount = $draft->submissions()
            ->whereIn('status', [
                ProposalSubmissionStatus::Menunggu->value,
                ProposalSubmissionStatus::ProsesTransfer->value,
            ])
            ->count();

        if ($activeCount > 0) {
            throw new RuntimeException('Masih ada proses pengajuan berjalan. Silakan tunggu proses yang berjalan selesai.');
        }

        if ($draft->submissions()->count() >= self::MAX_RESTATEMENT) {
            throw new RuntimeException('Pengajuan ini tidak dapat diajukan ulang. Silakan buat pengajuan baru.');
        }
    }

    public function submit(ProposalDraft $draft, User $user, array $data): ProposalSubmission
    {
        $this->assertCanSubmit($draft);

        $sequence = $draft->submissions()->count() + 1;

        return DB::transaction(function () use ($draft, $user, $data, $sequence): ProposalSubmission {
            $submission = $draft->submissions()->create([
                'no_submission' => $this->generateNoSubmission($draft),
                'event_identity' => $this->generateEventIdentity($draft->event, $draft, $sequence),
                'organizer_admin_id' => $user->getKey(),
                'status' => ProposalSubmissionStatus::Menunggu,
                'booking_code' => $data['booking_code'] ?? null,
            ]);

            $submission->needs()->attach($data['needs'] ?? []);

            foreach ($data['bankAccounts'] ?? [] as $account) {
                $submission->bankAccounts()->create($account);
            }

            $draft->update([
                'status' => ProposalDraftStatus::Diajukan,
                'organizer_admin_id' => $user->getKey(),
            ]);

            return $submission;
        });
    }

    public function approve(ProposalSubmission $submission, User $manager): void
    {
        if ($submission->status !== ProposalSubmissionStatus::Menunggu) {
            throw new RuntimeException('Submission tidak dalam status menunggu.');
        }

        DB::transaction(function () use ($submission, $manager): void {
            $submission->update([
                'status' => ProposalSubmissionStatus::ProsesTransfer,
                'inspiring_manager_id' => $manager->getKey(),
                'checked_date' => now()->toDateString(),
            ]);

            $submission->proposalDraft->update([
                'status' => ProposalDraftStatus::Diajukan,
            ]);
        });
    }

    public function reject(ProposalSubmission $submission, User $manager, ?string $note): void
    {
        if ($submission->status !== ProposalSubmissionStatus::Menunggu) {
            throw new RuntimeException('Submission tidak dalam status menunggu.');
        }

        DB::transaction(function () use ($submission, $manager, $note): void {
            $submission->update([
                'status' => ProposalSubmissionStatus::Ditolak,
                'inspiring_manager_id' => $manager->getKey(),
                'catatan_manager' => $note,
                'checked_date' => now()->toDateString(),
            ]);

            $submission->proposalDraft->update([
                'status' => ProposalDraftStatus::Ditolak,
            ]);
        });
    }

    public function returnToMember(ProposalSubmission $submission, User $manager, ?string $note): void
    {
        if ($submission->status !== ProposalSubmissionStatus::ProsesTransfer) {
            throw new RuntimeException('Submission tidak dalam proses transfer.');
        }

        DB::transaction(function () use ($submission, $manager, $note): void {
            $submission->update([
                'status' => ProposalSubmissionStatus::Dikembalikan,
                'inspiring_manager_id' => $manager->getKey(),
                'catatan_manager' => $note,
            ]);
        });
    }

    /**
     * Nomor transfer 17 karakter:
     * 12 digit kode event + 3 digit index draft + 2 digit urutan rekening.
     */
    public function generateNoTransfer(BankAccount $account): string
    {
        $draft = $account->proposalSubmission->proposalDraft;

        $eventCode = str_pad((string) $draft->event->getKey(), 12, '0', STR_PAD_LEFT);
        $draftIndex = substr($draft->no_pengajuan, 12, 3);

        $accountIndex = $account->proposalSubmission->bankAccounts()
            ->where('id', '<=', $account->getKey())
            ->count();

        return $eventCode
            . $draftIndex
            . str_pad((string) $accountIndex, 2, '0', STR_PAD_LEFT);
    }

    public function recordTransfer(ProposalSubmission $submission, User $treasurer, array $data): void
    {
        if ($submission->status !== ProposalSubmissionStatus::ProsesTransfer) {
            throw new RuntimeException('Submission tidak dalam proses transfer.');
        }

        $account = BankAccount::query()
            ->where('judan_proposal_submission_id', $submission->getKey())
            ->findOrFail($data['judan_bank_account_id']);

        if ($account->bankTransfers()->exists()) {
            throw new RuntimeException('Rekening ini sudah memiliki bukti transfer.');
        }

        DB::transaction(function () use ($submission, $treasurer, $data, $account): void {
            $fileAttached = $data['file_attached'];
            if (is_array($fileAttached)) {
                $fileAttached = $fileAttached[0] ?? null;
            }

            BankTransfer::create([
                'no_transfer' => $this->generateNoTransfer($account),
                'judan_bank_account_id' => $account->getKey(),
                'judan_bank_asal_id' => $data['judan_bank_asal_id'] ?? null,
                'eagle_treasurer_id' => $treasurer->getKey(),
                'file_attached' => $fileAttached,
                'melalui' => $data['melalui'],
                'tanggal_transfer' => $data['tanggal_transfer'],
            ]);

            $remaining = $submission->bankAccounts()
                ->whereDoesntHave('bankTransfers')
                ->count();

            if ($remaining === 0) {
                $this->markSelesai($submission);
            }
        });
    }

    public function complete(ProposalSubmission $submission): void
    {
        if ($submission->status !== ProposalSubmissionStatus::ProsesTransfer) {
            throw new RuntimeException('Submission tidak dalam proses transfer.');
        }

        DB::transaction(function () use ($submission): void {
            $this->markSelesai($submission);
        });
    }

    public function rejectReimburse(ProposalSubmission $submission, ?string $note): void
    {
        if ($submission->status !== ProposalSubmissionStatus::ProsesTransfer) {
            throw new RuntimeException('Submission tidak dalam proses transfer.');
        }

        DB::transaction(function () use ($submission, $note): void {
            $submission->update([
                'status' => ProposalSubmissionStatus::Ditolak,
                'catatan_manager' => $note,
                'checked_date' => now()->toDateString(),
            ]);

            $submission->proposalDraft->update([
                'status' => ProposalDraftStatus::Ditolak,
            ]);
        });
    }

    private function markSelesai(ProposalSubmission $submission): void
    {
        $submission->update([
            'status' => ProposalSubmissionStatus::Selesai,
        ]);

        $submission->proposalDraft->update([
            'status' => ProposalDraftStatus::Diterima,
        ]);
    }
}