<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Schemas;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Modules\PengajuanDana\Models\Bank;
use Modules\PengajuanDana\Models\Need;

class ProposalSubmissionCreateSchema
{
    /**
     * @return array<int, Field>
     */
    public static function getFields(): array
    {
        return [
            TextInput::make('booking_code')
                ->label('Booking Code')
                ->maxLength(255),

            Select::make('needs')
                ->label('Kebutuhan')
                ->options(fn () => Need::query()
                    ->where('is_active', true)
                    ->pluck('nama_kebutuhan', 'id'))
                ->multiple()
                ->searchable()
                ->preload()
                ->required(),

            Repeater::make('bankAccounts')
                ->label('Rekening Tujuan')
                ->defaultItems(1)
                ->addActionLabel('+ Tambah Rekening')
                ->schema([
                    Select::make('judan_bank_id')
                        ->label('Bank')
                        ->options(fn () => Bank::query()
                            ->where('is_active', true)
                            ->pluck('nama_bank', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('pemilik')
                        ->label('Nama Pemilik')
                        ->required()
                        ->maxLength(150),

                    TextInput::make('nomor_rekening')
                        ->label('Nomor Rekening')
                        ->required()
                        ->maxLength(50),

                    TextInput::make('sub_total')
                        ->label('Sub Total')
                        ->numeric()
                        ->required(),
                ]),
        ];
    }
}
