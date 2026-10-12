<?php

namespace Modules\Ticketing\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Modules\Ticketing\Models\Concerns\LogsReservasiActivity;

// use Modules\Ticketing\Database\Factories\TicketingTiketPesawatFactory;

class TicketingTiketPesawat extends Model
{
    use HasFactory;
    use LogsReservasiActivity;

    protected $table = 'ticketing_tiket_pesawat';

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if (array_key_exists('jenis_penerbangan', $model->getAttributes())) {
                try {
                    if (! Schema::hasColumn('ticketing_tiket_pesawat', 'jenis_penerbangan')) {
                        unset($model->jenis_penerbangan);
                    }
                } catch (\Throwable $e) {
                }
            }
        });
    }

    protected static array $maskapaiCache = [];
    protected static array $bandaraCache = [];

    /**
     * Parser terstruktur untuk detail_pulang_pergi.
     *
     * Mengembalikan array segmen; setiap segmen berisi nilai yang SUDAH
     * ter-resolve (nama maskapai, kode bandara, tanggal ter-format), siap
     * dipakai kolom tabel masing-masing. Mengembalikan [] bila bukan
     * round-trip atau detail kosong/tidak valid.
     *
     * @return array<int, array{
     *     maskapai: ?string,
     *     nomor_penerbangan: ?string,
     *     nomor_ticket: ?string,
     *     kode_booking: ?string,
     *     kelas: ?string,
     *     rute: ?string,
     *     jadwal_berangkat: ?string,
     *     jadwal_tiba: ?string,
     * }>
     */
    public function pulangPergiSegmens(): array
    {
        if (($this->ticketingPemesanan && ! $this->ticketingPemesanan->pulang_pergi) || empty($this->detail_pulang_pergi)) {
            return [];
        }

        $detail = json_decode((string) $this->detail_pulang_pergi, true);
        if (! is_array($detail) || empty($detail)) {
            return [];
        }

        $segmens = [];
        if (isset($detail['segmen']) && is_array($detail['segmen']) && ! empty($detail['segmen'])) {
            $segmens = $detail['segmen'];
        } else {
            $segmens = [$detail];
        }

        $result = [];

        foreach ($segmens as $seg) {
            if (! is_array($seg)) {
                continue;
            }

            $maskapaiId = $seg['maskapai_id'] ?? ($seg['maskapai_id_pulang'] ?? null);
            $maskapai = null;
            if ($maskapaiId) {
                if (! array_key_exists($maskapaiId, static::$maskapaiCache)) {
                    static::$maskapaiCache[$maskapaiId] = TicketingMaskapai::find($maskapaiId)?->nama_maskapai;
                }
                $maskapai = static::$maskapaiCache[$maskapaiId];
            }

            $asalId = $seg['bandara_berangkat_id'] ?? ($seg['bandara_berangkat_id_pulang'] ?? null);
            $asal = null;
            if ($asalId) {
                if (! array_key_exists($asalId, static::$bandaraCache)) {
                    static::$bandaraCache[$asalId] = TicketingBandara::find($asalId)?->kode_bandara;
                }
                $asal = static::$bandaraCache[$asalId];
            }

            $tujuanId = $seg['bandara_tiba_id'] ?? ($seg['bandara_tiba_id_pulang'] ?? null);
            $tujuan = null;
            if ($tujuanId) {
                if (! array_key_exists($tujuanId, static::$bandaraCache)) {
                    static::$bandaraCache[$tujuanId] = TicketingBandara::find($tujuanId)?->kode_bandara;
                }
                $tujuan = static::$bandaraCache[$tujuanId];
            }

            $rute = ($asal && $tujuan) ? "{$asal} - {$tujuan}" : ($asal ?: $tujuan);

            $jadwalBerangkat = $seg['jadwal_berangkat_pesawat'] ?? null;
            if (! $jadwalBerangkat && ! empty($seg['tanggal_keberangkatan_pulang'])) {
                $jam = $seg['jam_keberangkatan_pulang'] ?? '';
                $jadwalBerangkat = trim("{$seg['tanggal_keberangkatan_pulang']} {$jam}");
            }
            if ($jadwalBerangkat) {
                try {
                    $jadwalBerangkat = Carbon::parse($jadwalBerangkat)->format('d M Y H:i');
                } catch (\Throwable $e) {
                }
            }

            $jadwalTiba = $seg['jadwal_tiba_pesawat'] ?? null;
            if (! $jadwalTiba && (! empty($seg['tanggal_tiba_pulang']) || ! empty($seg['jam_tiba_pulang']))) {
                $tgl = $seg['tanggal_tiba_pulang'] ?? ($seg['tanggal_keberangkatan_pulang'] ?? '');
                $jam = $seg['jam_tiba_pulang'] ?? '';
                $jadwalTiba = trim("{$tgl} {$jam}");
            }
            if ($jadwalTiba) {
                try {
                    $jadwalTiba = Carbon::parse($jadwalTiba)->format('d M Y H:i');
                } catch (\Throwable $e) {
                }
            }

            $result[] = [
                'maskapai' => $maskapai,
                'nomor_penerbangan' => $seg['nomor_penerbangan'] ?? ($seg['nomor_penerbangan_pulang'] ?? null),
                'nomor_ticket' => $seg['nomor_ticket'] ?? ($seg['nomor_ticket_pulang'] ?? null),
                'kode_booking' => $seg['kode_booking_pesawat'] ?? ($seg['kode_booking_pesawat_pulang'] ?? null),
                'kelas' => $seg['kelas'] ?? ($seg['kelas_pulang'] ?? null),
                'rute' => $rute ?: null,
                'jadwal_berangkat' => $jadwalBerangkat ?: null,
                'jadwal_tiba' => $jadwalTiba ?: null,
            ];
        }

        return $result;
    }

    /**
     * Status pemesanan pulang-pergi dari detail JSON (mis. Confirmed), null bila kosong.
     */
    public function pulangPergiStatus(): ?string
    {
        if (empty($this->detail_pulang_pergi)) {
            return null;
        }

        $detail = json_decode((string) $this->detail_pulang_pergi, true);

        if (! is_array($detail)) {
            return null;
        }

        $status = $detail['status_pemesanan_pulang_pergi'] ?? null;

        return is_string($status) && $status !== '' ? $status : null;
    }

    public function getFormattedDetailPulangPergiAttribute(): string
    {
        $segmens = $this->pulangPergiSegmens();
        if (empty($segmens)) {
            return '-';
        }

        $formattedSegmens = [];

        foreach ($segmens as $idx => $seg) {
            $parts = [];
            if ($seg['maskapai']) $parts[] = $seg['maskapai'];
            if ($seg['nomor_penerbangan']) $parts[] = $seg['nomor_penerbangan'];
            if ($seg['rute']) $parts[] = "({$seg['rute']})";
            if ($seg['jadwal_berangkat']) $parts[] = "Berangkat: {$seg['jadwal_berangkat']}";
            if ($seg['jadwal_tiba']) $parts[] = "Tiba: {$seg['jadwal_tiba']}";
            if ($seg['nomor_ticket']) $parts[] = "Tiket: {$seg['nomor_ticket']}";
            if ($seg['kode_booking']) $parts[] = "Kode Booking: {$seg['kode_booking']}";
            if ($seg['kelas']) $parts[] = "Kelas: {$seg['kelas']}";

            if (! empty($parts)) {
                $prefix = count($segmens) > 1 ? 'Segmen ' . ($idx + 1) . ': ' : '';
                $formattedSegmens[] = $prefix . implode(' | ', $parts);
            }
        }

        if (empty($formattedSegmens)) {
            return '-';
        }

        $res = implode('; ', $formattedSegmens);
        $status = $this->pulangPergiStatus();
        if ($status) {
            $res .= " [Status: {$status}]";
        }

        return $res;
    }

    public function save(array $options = []): bool
    {
        try {
            return parent::save($options);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), "Unknown column 'jenis_penerbangan'")) {
                unset($this->attributes['jenis_penerbangan']);

                return parent::save($options);
            }

            throw $e;
        }
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tckt_maskapai_id',
        'tckt_vendor_id',
        'tckt_pemesanan_id',
        'tckt_bandara_berangkat_id',
        'tckt_bandara_tiba_id',
        'nomor_ticket',
        'nomor_penerbangan',
        'kode_booking_pesawat',
        'kelas',
        'jenis_penerbangan',
        'jadwal_berangkat_pesawat',
        'jadwal_tiba_pesawat',
        'detail_pulang_pergi',
        'zona_waktu',
        'zona_waktu_kedatangan',
    ];

    public function getRouteKeyName(): string
    {
        return 'invoice';
    }

    public function getRouteKey()
    {
        return $this->ticketingPemesanan?->invoice;
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        if ($field === 'invoice') {
            return $query->whereHas('ticketingPemesanan', function ($q) use ($value): void {
                $q->where('ticketing_pemesanan.invoice', $value);
            });
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    public function ticketingMaskapai()
    {
        return $this->belongsTo(TicketingMaskapai::class, 'tckt_maskapai_id');
    }

    public function ticketingPemesanan()
    {
        return $this->belongsTo(TicketingPemesanan::class, 'tckt_pemesanan_id');
    }

    public function ticketingVendor()
    {
        return $this->belongsTo(TicketingVendor::class, 'tckt_vendor_id');
    }

    public function ticketingBerangkatBandara()
    {
        return $this->belongsTo(TicketingBandara::class, 'tckt_bandara_berangkat_id');
    }

    public function ticketingTibaBandara()
    {
        return $this->belongsTo(TicketingBandara::class, 'tckt_bandara_tiba_id');
    }

    public function ticketingPenumpang()
    {
        return $this->belongsToMany(
            TicketingPenumpang::class,
            'ticketing_penumpang_tiket_pesawat',
            'tckt_tiket_pesawat_id',
            'tckt_penumpang_id'
        );
    }

    public function ticketingPembayaranPenumpang()
    {
        return $this->hasManyThrough(
            TicketingPembayaranPenumpang::class,
            TicketingPembayaran::class,
            'tckt_pemesanan_id',
            'tckt_pembayaran_id',
            'tckt_pemesanan_id',
            'id'
        );
    }
}
