<?php

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Models\Concerns\LogsReservasiActivity;

// use Modules\Ticketing\Database\Factories\TicketingDokumenFactory;

class TicketingDokumen extends Model
{
    use HasFactory;
    use LogsReservasiActivity;

    protected $table = 'ticketing_dokumen';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tckt_vendor_id',
        'tckt_pemesanan_id',
        'jenis_dokumen',
        'keterangan',
    ];

    public function ticketingPemesanan()
    {
        return $this->belongsTo(TicketingPemesanan::class, 'tckt_pemesanan_id');
    }

    public function ticketingPenumpang()
    {
        return $this->belongsToMany(
            TicketingPenumpang::class,
            'ticketing_penumpang_dokumen',
            'tckt_dokumen_id',
            'tckt_penumpang_id'
        );
    }

    public function ticketingVendor()
    {
        return $this->belongsTo(TicketingVendor::class, 'tckt_vendor_id');
    }
}
