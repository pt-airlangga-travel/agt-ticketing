<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Models\Bank;
use Modules\PengajuanDana\Models\Need;

class ProposalSubmissionViewSchema
{
    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public static function getFields(): array
    {
        return [
            Section::make('Detail Submission')
                ->columns(2)
                ->schema([
                    TextInput::make('no_submission')
                        ->label('No. Submission')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('event_identity')
                        ->label('Identitas Event')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('booking_code')
                        ->label('Booking Code')
                        ->disabled()
                        ->dehydrated(false),

                    Select::make('status')
                        ->label('Status')
                        ->options(collect(ProposalSubmissionStatus::cases())->mapWithKeys(
                            fn (ProposalSubmissionStatus $status): array => [$status->value => $status->label()]
                        ))
                        ->disabled()
                        ->dehydrated(false),
                ]),

            Section::make('Kebutuhan & Rekening Tujuan')
                ->schema([
                    Select::make('needs')
                        ->label('Kebutuhan')
                        ->relationship('needs', 'nama_kebutuhan')
                        ->multiple()
                        ->disabled()
                        ->dehydrated(false),

                    Repeater::make('bankAccounts')
                        ->label('Rekening Tujuan')
                        ->relationship()
                        ->columns(2)
                        ->disabled()
                        ->dehydrated(false)
                        ->schema([
                            Select::make('judan_bank_id')
                                ->label('Bank')
                                ->options(fn () => Bank::query()
                                    ->where('is_active', true)
                                    ->pluck('nama_bank', 'id'))
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('pemilik')
                                ->label('Nama Pemilik')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('nomor_rekening')
                                ->label('Nomor Rekening')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('sub_total')
                                ->label('Sub Total')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                ]),
        ];
    }
}