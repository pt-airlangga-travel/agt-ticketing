<?php

namespace Modules\Ticketing\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Ticketing\Models\TicketingActivityLog;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingPenumpang;

trait LogsReservasiActivity
{
    protected static function bootLogsReservasiActivity(): void
    {
        static::created(function (Model $model) {
            $model->recordActivity('created');
        });

        static::updated(function (Model $model) {
            $model->recordActivity('updated');
        });

        static::deleted(function (Model $model) {
            $model->recordActivity('deleted');
        });
    }

    protected function recordActivity(string $event): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $changes = $this->resolveActivityChanges($event);

        if ($event === 'updated' && empty($changes)) {
            return;
        }

        TicketingActivityLog::create([
            'user_id' => $userId,
            'tckt_pemesanan_id' => $this->resolvePemesananId(),
            'entity_type' => static::class,
            'entity_id' => $this->getKey(),
            'event' => $event,
            'changes' => $changes,
        ]);
    }

    protected function resolvePemesananId(): ?int
    {
        if ($this instanceof TicketingPemesanan) {
            return (int) $this->getKey();
        }

        if (isset($this->tckt_pemesanan_id)) {
            return (int) $this->tckt_pemesanan_id;
        }

        // Untuk model yang link via pembayaran (mis. TicketingPembayaranPenumpang)
        if (isset($this->tckt_pembayaran_id)) {
            $pembayaran = $this->ticketingPembayaran ?? null;
            if ($pembayaran && isset($pembayaran->tckt_pemesanan_id)) {
                return (int) $pembayaran->tckt_pemesanan_id;
            }
            // Fallback query langsung jika relasi belum load
            try {
                $pid = DB::table('ticketing_pembayaran')
                    ->where('id', $this->tckt_pembayaran_id)
                    ->value('tckt_pemesanan_id');
                if ($pid) {
                    return (int) $pid;
                }
            } catch (\Throwable $e) {
            }
        }

        // Untuk TicketingPenumpang yang via pivot (tidak simpan pemesanan_id langsung)
        if ($this instanceof TicketingPenumpang) {
            // Coba ambil dari tiket pesawat/kereta/hotel/dokumen pertama yang terkait
            try {
                foreach (['ticketingTiketPesawat', 'ticketingTiketKereta', 'ticketingKamarHotel', 'ticketingDokumen'] as $rel) {
                    if (method_exists($this, $rel)) {
                        $related = $this->$rel()->first();
                        if ($related && isset($related->tckt_pemesanan_id)) {
                            return (int) $related->tckt_pemesanan_id;
                        }
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    protected function resolveActivityChanges(string $event): ?array
    {
        if ($event === 'created') {
            return $this->activitySanitize($this->getAttributes());
        }

        if ($event === 'updated') {
            $changes = [];

            foreach ($this->getChanges() as $field => $newValue) {
                if ($this->activityExcludedFieldsContains($field)) {
                    continue;
                }

                $oldValue = $this->getOriginal($field);

                if ($oldValue == $newValue) {
                    continue;
                }

                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }

            return $changes;
        }

        return null;
    }

    protected function activitySanitize(array $attributes): array
    {
        return array_filter(
            $attributes,
            fn ($field): bool => ! $this->activityExcludedFieldsContains($field),
            ARRAY_FILTER_USE_KEY,
        );
    }

    protected function activityExcludedFieldsContains(string $field): bool
    {
        return in_array($field, [
            'id',
            'created_at',
            'updated_at',
        ], true);
    }
}
