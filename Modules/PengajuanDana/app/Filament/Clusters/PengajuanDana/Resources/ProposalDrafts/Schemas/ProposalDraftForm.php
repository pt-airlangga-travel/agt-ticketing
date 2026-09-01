<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasMoneyFields;
use Modules\PengajuanDana\Models\Event;
use Modules\PengajuanDana\Models\Need;

class ProposalDraftForm
{
    use HasMoneyFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Data Pengajuan')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('no_pengajuan')
                            ->label('No. Pengajuan')
                            ->disabled()
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->columnSpanFull(),

                        Placeholder::make('event_display')
                            ->label('Event')
                            ->content(fn ($get) => Event::find($get('event_id'))?->nama),

                        TextInput::make('event_id')
                            ->hidden()
                            ->dehydratedWhenHidden()
                            ->required(),

                        DatePicker::make('deadline_pembayaran')
                            ->label('Deadline Pembayaran')
                            ->required()
                            ->native(false),

                        Textarea::make('catatan_member')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Vendor & Invoice')
                    ->columnSpanFull()
                    ->schema([

                        Select::make('needs')
                            ->label('Kebutuhan yang dibiayai')
                            ->options(fn () => Need::query()
                                ->where('is_active', true)
                                ->pluck('nama_kebutuhan', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih semua jenis kebutuhan yang akan dibiayai pada pengajuan ini.')
                            ->columnSpanFull(),

                        Repeater::make('vendors')
                            ->label('Daftar Vendor')
                            ->relationship()
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Vendor')
                            ->grid(1)
                            ->columns(4)
                            ->schema([
                                TextInput::make('nama_vendor')
                                    ->label('Nama Vendor')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('sub_total')
                                    ->label('Nominal')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->dehydrateStateUsing(fn ($state) => (int) self::parseRupiah($state))
                                    ->live()
                                    ->required(),

                                TextInput::make('kontak')
                                    ->label('Kontak')
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        FileUpload::make('file_attached')
                            ->label('Invoice / Lampiran')
                            ->disk('local')
                            ->directory('proposal')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
