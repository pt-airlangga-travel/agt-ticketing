<?php

namespace Modules\PengajuanDana\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $table = 'judan_events';

    protected $fillable = [
        'nama',
        'nama_singkat',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'judan_institution_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            $base = Str::slug($event->nama_singkat);
            $slug = $base;
            $index = 2;

            while (Event::query()
                ->where('slug', $slug)
                ->whereKeyNot($event->getKey())
                ->exists()) {
                $slug = "{$base}-{$index}";
                $index++;
            }

            $event->slug = $slug;
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'judan_institution_id');
    }

    public function proposalDrafts(): HasMany
    {
        return $this->hasMany(ProposalDraft::class, 'event_id');
    }

    public function getEventIdentityAttribute(): string
    {
        $monthStart = $this->tanggal_mulai?->format('M');
        $monthEnd = $this->tanggal_selesai?->format('M');
        $dayStart = $this->tanggal_mulai?->format('j');
        $dayEnd = $this->tanggal_selesai?->format('j');

        $month = $monthStart === $monthEnd ? $monthStart : "{$monthStart}-{$monthEnd}";
        $day = $dayStart === $dayEnd ? $dayStart : "{$dayStart}-{$dayEnd}";

        return collect([
            $this->tanggal_mulai?->format('Y'),
            $this->institution?->slug,
            $this->nama_singkat ? Str::slug($this->nama_singkat) : $this->slug,
            strtolower((string) $month),
            $day,
        ])->join('/');
    }
}
