<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Models\Concerns\LogsReservasiActivity;

// use Modules\Ticketing\Database\Factories\TicketingTiketKeretaFactory;

class TicketingTiketKereta extends Model
{
    use HasFactory;
    use LogsReservasiActivity;

    protected $table = 'ticketing_tiket_kereta';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tckt_pemesanan_id',
        'tckt_vendor_id',
        'tckt_kereta_id',
        'tckt_stasiun_berangkat_id',
        'tckt_stasiun_tiba_id',
        'kode_booking_kereta',
        'jadwal_berangkat_kereta',
        'jadwal_tiba_kereta',
        'zona_waktu',
        'zona_waktu_kedatangan',
    ];

    public function ticketingPemesanan()
    {
        return $this->belongsTo(TicketingPemesanan::class, 'tckt_pemesanan_id');
    }

    public function ticketingVendor()
    {
        return $this->belongsTo(TicketingVendor::class, 'tckt_vendor_id');
    }

    public function ticketingKereta()
    {
        return $this->belongsTo(TicketingKereta::class, 'tckt_kereta_id');
    }

    public function ticketingBerangkatStasiun()
    {
        return $this->belongsTo(TicketingStasiun::class, 'tckt_stasiun_berangkat_id');
    }

    public function ticketingTibaStasiun()
    {
        return $this->belongsTo(TicketingStasiun::class, 'tckt_stasiun_tiba_id');
    }

    public function ticketingPenumpang()
    {
        return $this->belongsToMany(
            TicketingPenumpang::class,
            'ticketing_penumpang_tiket_kereta',
            'tckt_tiket_kereta_id',
            'tckt_penumpang_id'
        );
    }
}
