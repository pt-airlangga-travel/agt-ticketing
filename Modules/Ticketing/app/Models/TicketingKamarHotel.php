<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Models\Concerns\LogsReservasiActivity;

// use Modules\Ticketing\Database\Factories\TicketingKamarHotelFactory;

class TicketingKamarHotel extends Model
{
    use HasFactory;
    use LogsReservasiActivity;

    protected $table = 'ticketing_kamar_hotel';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tckt_hotel_id',
        'tckt_vendor_id',
        'tckt_pemesanan_id',
        'jumlah_kamar',
        'lama_menginap',
        'tipe_kamar',
        'jadwal_checkin',
        'jadwal_checkout',
        'include_breakfast',
        'zona_waktu',
    ];

    public function ticketingVendor()
    {
        return $this->belongsTo(TicketingVendor::class, 'tckt_vendor_id');
    }

    public function ticketingHotel()
    {
        return $this->belongsTo(TicketingHotel::class, 'tckt_hotel_id');
    }

    public function ticketingPemesanan()
    {
        return $this->belongsTo(TicketingPemesanan::class, 'tckt_pemesanan_id');
    }

    public function ticketingPenumpang()
    {
        return $this->belongsToMany(
            TicketingPenumpang::class,
            'ticketing_kamar_hotel_penumpang',
            'tckt_kamar_hotel_id',
            'tckt_penumpang_id'
        );
    }
}
