<?php

namespace Modules\PengajuanDana\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransfer extends Model
{
    use HasFactory;

    protected $table = 'judan_bank_transfers';

    protected $fillable = [
        'no_transfer',
        'judan_bank_account_id',
        'judan_bank_asal_id',
        'eagle_treasurer_id',
        'file_attached',
        'melalui',
        'tanggal_transfer',
    ];

    protected $casts = [
        'tanggal_transfer' => 'date',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'judan_bank_account_id');
    }

    public function bankAsal(): BelongsTo
    {
        return $this->belongsTo(BankAsal::class, 'judan_bank_asal_id');
    }

    public function eagleTreasurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eagle_treasurer_id');
    }
}
