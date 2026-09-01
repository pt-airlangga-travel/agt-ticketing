<?php

namespace Modules\PengajuanDana\Services;

use Modules\PengajuanDana\Models\Event;
use Modules\PengajuanDana\Models\ProposalDraft;

class ProposalDraftService
{
    /**
     * Generate nomor pengajuan 17 karakter:
     * 12 digit kode event + 3 digit index draft + 2 digit revisi.
     */
    public function generateNoPengajuan(Event $event): string
    {
        $eventCode = str_pad((string) $event->getKey(), 12, '0', STR_PAD_LEFT);

        $index = ProposalDraft::query()
            ->where('event_id', $event->getKey())
            ->count() + 1;

        return $eventCode
            .str_pad((string) $index, 3, '0', STR_PAD_LEFT)
            .str_pad('0', 2, '0', STR_PAD_LEFT);
    }
}
