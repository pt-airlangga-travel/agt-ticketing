<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ListsReservasiByEntity;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\ReservasiDokumenResource;
use Modules\Ticketing\Models\TicketingDokumen;

class ListReservasiDokumens extends ListRecords
{
    use HasClusterSubNavigation;
    use ListsReservasiByEntity;

    protected static string $resource = ReservasiDokumenResource::class;

    protected function getReservasiAnchorModel(): string
    {
        return TicketingDokumen::class;
    }

    protected function getReservasiExportColumns(): array
    {
        return array_merge($this->reservasiBaseExportColumns(), [
            'penumpang' => 'Pemilik Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'keterangan' => 'Keterangan',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->exportReservasiAction(),
            CreateAction::make()
                ->label('+ Tambah')
                ->button()
                ->createAnother(false),
        ];
    }
}
