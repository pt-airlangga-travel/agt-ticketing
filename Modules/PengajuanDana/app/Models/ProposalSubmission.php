<?php

namespace Modules\PengajuanDana\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;

class ProposalSubmission extends Model
{
    use HasFactory;

    protected $table = 'judan_proposal_submissions';

    protected $fillable = [
        'no_submission',
        'event_identity',
        'booking_code',
        'judan_proposal_draft_id',
        'organizer_admin_id',
        'inspiring_manager_id',
        'status',
        'catatan_manager',
        'checked_date',
    ];

    protected $casts = [
        'status' => ProposalSubmissionStatus::class,
        'checked_date' => 'date',
    ];

    public function proposalDraft(): BelongsTo
    {
        return $this->belongsTo(ProposalDraft::class, 'judan_proposal_draft_id');
    }

    public function organizerAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_admin_id');
    }

    public function inspiringManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspiring_manager_id');
    }

    public function needs(): BelongsToMany
    {
        return $this->belongsToMany(
            Need::class,
            'judan_need_submissions',
            'judan_proposal_submission_id',
            'judan_need_id'
        );
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'judan_proposal_submission_id');
    }

    public function bankTransfers(): HasManyThrough
    {
        return $this->hasManyThrough(
            BankTransfer::class,
            BankAccount::class,
            'judan_proposal_submission_id',
            'judan_bank_account_id',
            'id',
            'id'
        );
    }
}
