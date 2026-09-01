<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingHotelFactory;

class TicketingHotel extends Model
{
    use HasFactory;

    protected $table = 'ticketing_hotel';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_hotel',
        'bintang',
        'kota',
        'telepon',
        'email',
        'alamat',
        'gambar',
        'is_active',
    ];

    public function ticketingKamarHotels()
    {
        return $this->hasMany(TicketingKamarHotel::class);
    }

    // protected static function newFactory(): TicketingHotelFactory
    // {
    //     // return TicketingHotelFactory::new();
    // }
}
