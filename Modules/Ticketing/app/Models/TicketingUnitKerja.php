<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Ticketing\Database\Factories\TicketingUnitKerjaFactory;

class TicketingUnitKerja extends Model
{
    use HasFactory;

    protected $table = 'ticketing_unit_kerja';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_unit_kerja',
        'kode_unit_kerja',
        'is_active',
    ];

    public function ticketingPemesanan()
    {
        return $this->hasMany(TicketingPemesanan::class);
    }

    public function ticketingPembayaran()
    {
        return $this->hasMany(TicketingPembayaran::class);
    }

    public function ticketingPenumpang()
    {
        return $this->hasMany(TicketingPenumpang::class);
    }

    // protected static function newFactory(): TicketingUnitKerjaFactory
    // {
    //     // return TicketingUnitKerjaFactory::new();
    // }
}
