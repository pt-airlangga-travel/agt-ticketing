<?php

namespace Modules\Ticketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingPembayaranPenumpangFactory;

class TicketingPembayaranPenumpang extends Model
{
    use HasFactory;

    protected $table = 'ticketing_pembayaran_penumpang';

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
