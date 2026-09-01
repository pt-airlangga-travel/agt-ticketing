<?php

namespace Modules\Ticketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TicketingActivityLog extends Model
{
    protected $table = 'ticketing_activity_log';

    protected $fillable = [
        'user_id',
        'tckt_pemesanan_id',
        'entity_type',
        'entity_id',
        'event',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(TicketingPemesanan::class, 'tckt_pemesanan_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }

    public function getEntityLabelAttribute(): string
    {
        $labels = [
            TicketingPemesanan::class => 'Pemesanan',
            TicketingPembayaran::class => 'Pembayaran',
            TicketingTiketPesawat::class => 'Tiket Pesawat',
            TicketingTiketKereta::class => 'Tiket Kereta',
            TicketingKamarHotel::class => 'Kamar Hotel',
            TicketingDokumen::class => 'Dokumen',
        ];

        return $labels[$this->entity_type] ?? class_basename($this->entity_type);
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Dibuat',
            'updated' => 'Diubah',
            'deleted' => 'Dihapus',
            default => ucfirst($this->event),
        };
    }
}
