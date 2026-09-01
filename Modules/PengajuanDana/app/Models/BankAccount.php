<?php

namespace Modules\PengajuanDana\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'judan_bank_accounts';

    protected $fillable = [
        'sub_total',
        'pemilik',
        'nomor_rekening',
        'is_revised',
        'alasan_revisi',
        'judan_bank_id',
        'judan_proposal_submission_id',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'is_revised' => 'boolean',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'judan_bank_id');
    }

    public function proposalSubmission(): BelongsTo
    {
        return $this->belongsTo(ProposalSubmission::class, 'judan_proposal_submission_id');
    }

    public function bankTransfers(): HasMany
    {
        return $this->hasMany(BankTransfer::class, 'judan_bank_account_id');
    }
}
