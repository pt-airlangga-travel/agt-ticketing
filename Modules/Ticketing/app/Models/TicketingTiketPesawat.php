<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Modules\Ticketing\Models\Concerns\LogsReservasiActivity;

// use Modules\Ticketing\Database\Factories\TicketingTiketPesawatFactory;

class TicketingTiketPesawat extends Model
{
    use HasFactory;
    use LogsReservasiActivity;

    protected $table = 'ticketing_tiket_pesawat';

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if (array_key_exists('jenis_penerbangan', $model->getAttributes())) {
                try {
                    if (! Schema::hasColumn('ticketing_tiket_pesawat', 'jenis_penerbangan')) {
                        unset($model->jenis_penerbangan);
                    }
                } catch (\Throwable $e) {
                }
            }
        });
    }

    public function save(array $options = []): bool
    {
        try {
            return parent::save($options);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), "Unknown column 'jenis_penerbangan'")) {
                unset($this->attributes['jenis_penerbangan']);

                return parent::save($options);
            }

            throw $e;
        }
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tckt_maskapai_id',
        'tckt_vendor_id',
        'tckt_pemesanan_id',
        'tckt_bandara_berangkat_id',
        'tckt_bandara_tiba_id',
        'nomor_ticket',
        'nomor_penerbangan',
        'kode_booking_pesawat',
        'kelas',
        'jenis_penerbangan',
        'jadwal_berangkat_pesawat',
        'jadwal_tiba_pesawat',
        'detail_pulang_pergi',
        'zona_waktu',
        'zona_waktu_kedatangan',
    ];

    public function getRouteKeyName(): string
    {
        return 'invoice';
    }

    public function getRouteKey()
    {
        return $this->ticketingPemesanan?->invoice;
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        if ($field === 'invoice') {
            return $query->whereHas('ticketingPemesanan', function ($q) use ($value): void {
                $q->where('ticketing_pemesanan.invoice', $value);
            });
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    public function ticketingMaskapai()
    {
        return $this->belongsTo(TicketingMaskapai::class, 'tckt_maskapai_id');
    }

    public function ticketingPemesanan()
    {
        return $this->belongsTo(TicketingPemesanan::class, 'tckt_pemesanan_id');
    }

    public function ticketingVendor()
    {
        return $this->belongsTo(TicketingVendor::class, 'tckt_vendor_id');
    }

    public function ticketingBerangkatBandara()
    {
        return $this->belongsTo(TicketingBandara::class, 'tckt_bandara_berangkat_id');
    }

    public function ticketingTibaBandara()
    {
        return $this->belongsTo(TicketingBandara::class, 'tckt_bandara_tiba_id');
    }

    public function ticketingPenumpang()
    {
        return $this->belongsToMany(
            TicketingPenumpang::class,
            'ticketing_penumpang_tiket_pesawat',
            'tckt_tiket_pesawat_id',
            'tckt_penumpang_id'
        );
    }

    public function ticketingPembayaranPenumpang()
    {
        return $this->hasManyThrough(
            TicketingPembayaranPenumpang::class,
            TicketingPembayaran::class,
            'tckt_pemesanan_id',
            'tckt_pembayaran_id',
            'tckt_pemesanan_id',
            'id'
        );
    }
}
