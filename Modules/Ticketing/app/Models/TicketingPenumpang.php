<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingPenumpangFactory;

class TicketingPenumpang extends Model
{
    use HasFactory;

    protected $table = 'ticketing_penumpang';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_penumpang',
        'jenis_kelamin',
    ];

    public function ticketingUnitKerja()
    {
        return $this->belongsTo(TicketingUnitKerja::class);
    }

    public function ticketingTiketPesawat()
    {
        return $this->belongsToMany(
            TicketingTiketPesawat::class,
            'ticketing_penumpang_tiket_pesawat',
            'tckt_penumpang_id',
            'tckt_tiket_pesawat_id'
        );
    }

    public function ticketingTiketKereta()
    {
        return $this->belongsToMany(
            TicketingTiketKereta::class,
            'ticketing_penumpang_tiket_kereta',
            'tckt_penumpang_id',
            'tckt_tiket_kereta_id'
        );
    }

    public function ticketingKamarHotel()
    {
        return $this->belongsToMany(
            TicketingKamarHotel::class,
            'ticketing_kamar_hotel_penumpang',
            'tckt_penumpang_id',
            'tckt_kamar_hotel_id'
        );
    }

    public function ticketingDokumen()
    {
        return $this->belongsToMany(
            TicketingDokumen::class,
            'ticketing_penumpang_dokumen',
            'tckt_penumpang_id',
            'tckt_dokumen_id'
        );
    }

    public function ticketingPembayaranPenumpang()
    {
        return $this->hasMany(TicketingPembayaranPenumpang::class, 'tckt_penumpang_id');
    }
}
