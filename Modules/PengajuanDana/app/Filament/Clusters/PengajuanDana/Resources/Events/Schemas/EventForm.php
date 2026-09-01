<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\PengajuanDana\Models\Institution;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Data Event')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Event')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('nama_singkat')
                            ->label('Nama Singkat')
                            ->required()
                            ->maxLength(12),

                        Select::make('judan_institution_id')
                            ->label('Institusi')
                            ->options(fn () => Institution::query()
                                ->where('is_active', true)
                                ->pluck('nama_institusi', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false),

                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->after('tanggal_mulai'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
