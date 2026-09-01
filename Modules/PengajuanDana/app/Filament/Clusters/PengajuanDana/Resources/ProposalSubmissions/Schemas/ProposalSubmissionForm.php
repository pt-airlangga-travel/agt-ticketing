<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasFormattedNumber;
use Modules\PengajuanDana\Models\Bank;
use Modules\PengajuanDana\Models\ProposalDraft;

class ProposalSubmissionForm
{
    use HasFormattedNumber;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Detail Submission')->schema([
                    TextInput::make('no_submission')
                        ->label('No. Submission')
                        ->disabled()
                        ->visible(fn (string $operation): bool => $operation === 'edit'),

                    TextInput::make('event_identity')
                        ->label('Identitas Event')
                        ->disabled()
                        ->visible(fn (string $operation): bool => $operation === 'edit'),

                    TextInput::make('judan_proposal_draft_id')
                        ->hidden()
                        ->dehydratedWhenHidden()
                        ->required(),

                    Placeholder::make('draft_display')
                        ->label('Proposal Draft')
                        ->content(function ($get): ?string {
                            $draft = ProposalDraft::query()
                                ->with('event')
                                ->find($get('judan_proposal_draft_id'));

                            return $draft ? self::formatDefinedId($draft->no_pengajuan).' - '.$draft->event?->nama : null;
                        }),

                    TextInput::make('booking_code')
                        ->label('Booking Code')
                        ->maxLength(255),
                ])->columnSpanFull(),

                Section::make('Kebutuhan & Rekening')->schema([
                    Select::make('needs')
                        ->label('Kebutuhan')
                        ->relationship('needs', 'nama_kebutuhan')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required(),

                    Repeater::make('bankAccounts')
                        ->label('Rekening Tujuan')
                        ->relationship()
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
                ])->columnSpanFull(),
            ]);
    }
}
