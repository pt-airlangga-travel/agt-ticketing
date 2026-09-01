<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingKategoriPemesananFactory;

class TicketingKategoriPemesanan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ticketing_kategori_pemesanan';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_kategori',
    ];

    public function ticketingPemesanan()
    {
        return $this->hasMany(ticketingPemesanan::class);
    }
}
