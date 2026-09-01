<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingStasiunFactory;

class TicketingStasiun extends Model
{
    use HasFactory;

    protected $table = 'ticketing_stasiun';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_stasiun',
        'kode_stasiun',
        'is_active',
    ];

    public function ticketingBerangkatTiketKeretas()
    {
        return $this->hasMany(TicketingTiketKereta::class, 'tckt_stasiun_berangkat_id');
    }

    public function ticketingTibaTiketKeretas()
    {
        return $this->hasMany(TicketingTiketKereta::class, 'tckt_stasiun_tiba_id');
    }

    // protected static function newFactory(): TicketingStasiunFactory
    // {
    //     // return TicketingStasiunFactory::new();
    // }
}
