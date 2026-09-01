<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingVendorFactory;

class TicketingVendor extends Model
{
    use HasFactory;

    protected $table = 'ticketing_vendor';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_vendor',
        'jenis_vendor',
        'is_active',
    ];

    public function ticketingTiketPesawats()
    {
        return $this->hasMany(TicketingTiketPesawat::class);
    }

    public function ticketingTiketKeretas()
    {
        return $this->hasMany(TicketingTiketKereta::class);
    }

    public function ticketingKamarHotels()
    {
        return $this->hasMany(TicketingKamarHotel::class);
    }

    public function ticketingDokumens()
    {
        return $this->hasMany(TicketingDokumen::class);
    }

    // protected static function newFactory(): TicketingVendorFactory
    // {
    //     // return TicketingVendorFactory::new();
    // }
}
