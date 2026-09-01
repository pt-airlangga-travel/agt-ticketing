<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketingPembayar extends Model
{
    use HasFactory;

    protected $table = 'ticketing_pembayar';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_pembayar',
    ];
}
