<?php

namespace Modules\Ticketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Ticketing\Models\Concerns\LogsReservasiActivity;

// use Modules\Ticketing\Database\Factories\TicketingPemesananFactory;

class TicketingPemesanan extends Model
{
    use HasFactory;
    use LogsReservasiActivity;

    protected $table = 'ticketing_pemesanan';

    protected static function booted(): void
    {
        static::creating(function (TicketingPemesanan $pemesanan) {
            $userId = Auth::id();
            if ($userId && ! $pemesanan->created_by) {
                $pemesanan->created_by = $userId;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'invoice';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice',
        'nama_customer',
        'tckt_kategori_pemesanan_id',
        'tckt_unit_kerja_id',
        'status_pemesanan',
        'pulang_pergi',
        'tanggal_pemesanan',
        'harga_beli',
        'harga_publish',
        'harga_jual',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticketingKategoriPemesanan()
    {
        return $this->belongsTo(TicketingKategoriPemesanan::class, 'tckt_kategori_pemesanan_id');
    }

    public function ticketingUnitKerja()
    {
        return $this->belongsTo(TicketingUnitKerja::class, 'tckt_unit_kerja_id');
    }

    public function ticketingTiketPesawat()
    {
        return $this->hasOne(TicketingTiketPesawat::class, 'tckt_pemesanan_id');
    }

    public function ticketingDokumen()
    {
        return $this->hasOne(TicketingDokumen::class, 'tckt_pemesanan_id');
    }

    public function ticketingTiketKereta()
    {
        return $this->hasOne(TicketingTiketKereta::class, 'tckt_pemesanan_id');
    }

    public function ticketingKamarHotel()
    {
        return $this->hasOne(TicketingKamarHotel::class, 'tckt_pemesanan_id');
    }

    public function ticketingPembayaran()
    {
        return $this->hasOne(TicketingPembayaran::class, 'tckt_pemesanan_id');
    }

    public function ticketingPembayaranPenumpang()
    {
        return $this->hasManyThrough(
            TicketingPembayaranPenumpang::class,
            TicketingPembayaran::class,
            'tckt_pemesanan_id',
            'tckt_pembayaran_id',
            'id',
            'id'
        );
    }

    public function ticketingPenumpang()
    {
        if ($this->ticketingTiketPesawat) {
            return $this->ticketingTiketPesawat->ticketingPenumpang();
        } elseif ($this->ticketingTiketKereta) {
            return $this->ticketingTiketKereta->ticketingPenumpang();
        } elseif ($this->ticketingKamarHotel) {
            return $this->ticketingKamarHotel->ticketingPenumpang();
        } elseif ($this->ticketingDokumen) {
            return $this->ticketingDokumen->ticketingPenumpang();
        }

        return null;
    }

    public function ticketingTerbayar($id = null)
    {
        $pembayaran = $this->ticketingPembayaran;
        if (! $pembayaran) {
            return 0;
        }

        $idPenumpang = [];
        if ($id === null) {
            $penumpangs = $this->ticketingPenumpang();
            if ($penumpangs) {
                foreach ($penumpangs as $key) {
                    array_push($idPenumpang, $key->id);
                }
            }
        } else {
            $idPenumpang = $id;
        }

        $totalBayar = DB::table('ticketing_pembayaran_penumpang')
            ->whereIn('tckt_penumpang_id', $idPenumpang)
            ->where('tckt_pembayaran_id', $pembayaran->id)
            ->sum('jumlah_membayar');

        return $totalBayar ?: 0;
    }

    public function ticketingCekLunas()
    {
        return $this->ticketingTerbayar() >= $this->harga_jual;
    }
}
