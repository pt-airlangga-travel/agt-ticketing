<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingMaskapaiFactory;

class TicketingMaskapai extends Model
{
    use HasFactory;

    protected $table = 'ticketing_maskapai';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_maskapai',
        'is_active',
    ];

    public function ticketingTiketPesawats()
    {
        return $this->hasMany(TicketingTiketPesawat::class);
    }

    // protected static function newFactory(): TicketingMaskapaiFactory
    // {
    //     // return TicketingMaskapaiFactory::new();
    // }
}
