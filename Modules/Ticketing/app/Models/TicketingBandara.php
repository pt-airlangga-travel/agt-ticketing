<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingBandaraFactory;

class TicketingBandara extends Model
{
    use HasFactory;

    protected $table = 'ticketing_bandara';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_bandara',
        'kode_bandara',
        'is_active',
    ];

    public function ticketingBerangkatTiketPesawats()
    {
        return $this->hasMany(TicketingTiketPesawat::class, 'tckt_bandara_berangkat_id');
    }

    public function ticketingTibaTiketPesawats()
    {
        return $this->hasMany(TicketingTiketPesawat::class, 'tckt_bandara_tiba_id');
    }

    // protected static function newFactory(): TicketingBandaraFactory
    // {
    //     // return TicketingBandaraFactory::new();
    // }
}
