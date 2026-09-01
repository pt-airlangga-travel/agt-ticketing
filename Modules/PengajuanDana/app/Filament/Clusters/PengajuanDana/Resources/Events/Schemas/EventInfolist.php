<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Section::make('Informasi Event')
                    ->schema([
                        TextEntry::make('event_identity')
                            ->label('Identitas Event')
                            ->copyable(),

                        TextEntry::make('nama')
                            ->label('Nama Event')
                            ->columnSpanFull(),

                        TextEntry::make('institution.nama_institusi')
                            ->label('Institusi')
                            ->columnSpanFull(),

                        TextEntry::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->date('d M Y'),

                        TextEntry::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->date('d M Y'),

                        TextEntry::make('is_active')
                            ->label('Aktif')
                            ->badge()
                            ->color(fn ($state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn ($state): string => $state ? 'Aktif' : 'Nonaktif'),
                    ])
                    ->inlineLabel()
                    ->columnSpanFull(),

            ]);
    }
}
