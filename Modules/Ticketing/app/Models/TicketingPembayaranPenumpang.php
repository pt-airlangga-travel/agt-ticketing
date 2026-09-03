<?php

namespace Modules\Ticketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

// use Modules\Ticketing\Database\Factories\TicketingPembayaranPenumpangFactory;

class TicketingPembayaranPenumpang extends Model
{
    use HasFactory;

    protected $table = 'ticketing_pembayaran_penumpang';

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            // Cek di DB aktif (bisa gapura / mandegan_gapura / u101763413_inticket).
            // Schema::hasColumn cache per-connection, tapi jika migrasi belum jalan di DB production,
            // hasColumn akan true di lokal (gapura) → tetap coba insert → 1054. Karena itu fallback via try-catch di level query.
            foreach (['tckt_pembayar_id', 'tckt_unit_kerja_id', 'nama_pembayar'] as $col) {
                if (! array_key_exists($col, $model->getAttributes())) {
                    continue;
                }
                try {
                    if (! Schema::hasColumn('ticketing_pembayaran_penumpang', $col)) {
                        unset($model->$col);
                    }
                } catch (\Throwable $e) {
                    // Jika hasColumn gagal (mis. DB tidak ada), biarkan query yang tentukan; akan di-handle di catch 1054.
                }
            }
        });
    }

    /**
     * Override save untuk tahan 1054 Unknown column jika migrasi belum jalan di DB production.
     */
    public function save(array $options = []): bool
    {
        try {
            return parent::save($options);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'Unknown column') && str_contains($e->getMessage(), 'tckt_')) {
                // Hapus kolom yang tidak ada lalu retry sekali
                $msg = $e->getMessage();
                foreach (['tckt_pembayar_id', 'tckt_unit_kerja_id', 'nama_pembayar'] as $col) {
                    if (str_contains($msg, $col)) {
                        unset($this->attributes[$col]);
                    }
                }

                return parent::save($options);
            }

            throw $e;
        }
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tckt_penumpang_id',
        'tckt_pembayaran_id',
        'tckt_pembayar_id',
        'tckt_unit_kerja_id',
        'nama_pembayar',
        'jumlah_membayar',
        'user_id',
        'bukti_pembayaran',
        'tgl_membayar',
        'status_bukti_bayar',
    ];

    public function ticketingPembayar()
    {
        return $this->belongsTo(TicketingPembayar::class, 'tckt_pembayar_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticketingPembayaran()
    {
        return $this->belongsTo(TicketingPembayaran::class, 'tckt_pembayaran_id');
    }

    public function ticketingPenumpang()
    {
        return $this->belongsTo(TicketingPenumpang::class, 'tckt_penumpang_id');
    }

    public function ticketingUnitKerja()
    {
        return $this->belongsTo(TicketingUnitKerja::class, 'tckt_unit_kerja_id');
    }
}
