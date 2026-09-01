<?php

namespace Modules\PengajuanDana\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'judan_vendors';

    protected $fillable = [
        'nama_vendor',
        'sub_total',
        'kontak',
        'email',
        'judan_proposal_draft_id',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
    ];

    public function proposalDraft(): BelongsTo
    {
        return $this->belongsTo(ProposalDraft::class, 'judan_proposal_draft_id');
    }
}
