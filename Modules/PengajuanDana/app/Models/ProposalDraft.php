<?php

namespace Modules\PengajuanDana\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\PengajuanDana\Enums\ProposalDraftStatus;

class ProposalDraft extends Model
{
    use HasFactory;

    protected $table = 'judan_proposal_drafts';

    protected $fillable = [
        'no_pengajuan',
        'event_id',
        'creative_member_id',
        'organizer_admin_id',
        'status',
        'catatan_admin',
        'catatan_member',
        'deadline_pembayaran',
        'file_attached',
    ];

    protected $casts = [
        'status' => ProposalDraftStatus::class,
        'deadline_pembayaran' => 'date',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function creativeMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creative_member_id');
    }

    public function organizerAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_admin_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'judan_proposal_draft_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProposalSubmission::class, 'judan_proposal_draft_id');
    }
}
