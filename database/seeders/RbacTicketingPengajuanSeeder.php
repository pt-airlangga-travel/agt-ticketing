<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Semua Resource module Ticketing
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\TicketingPelangganResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\TicketingVendorResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\TicketingPembayarResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\TicketingUnitKerjaResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas\TicketingKeretaResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\ReservasiKeretaResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\TicketingBandaraResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\ReservasiDokumenResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\TicketingStasiunResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\TicketingMaskapaiResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingActivityLogs\TicketingActivityLogResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\TicketingHotelResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\ReservasiPesawatResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\ReservasiHotelResource;

// Semua Resource module PengajuanDana
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\BankAsals\BankAsalResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\EventResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Needs\NeedResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Banks\BankResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalSubmissions\ProposalSubmissionResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Institutions\InstitutionResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\ProposalDrafts\ProposalDraftResource;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Divisions\DivisionResource;

class RbacTicketingPengajuanSeeder extends Seeder
{
    protected array $ticketingResources = [
        TicketingPelangganResource::class,
        TicketingVendorResource::class,
        TicketingPembayarResource::class,
        TicketingUnitKerjaResource::class,
        TicketingKeretaResource::class,
        ReservasiKeretaResource::class,
        TicketingBandaraResource::class,
        ReservasiDokumenResource::class,
        TicketingStasiunResource::class,
        TicketingMaskapaiResource::class,
        TicketingActivityLogResource::class,
        TicketingHotelResource::class,
        ReservasiPesawatResource::class,
        ReservasiHotelResource::class,
    ];

    protected array $pengajuanDanaResources = [
        BankAsalResource::class,
        EventResource::class,
        NeedResource::class,
        BankResource::class,
        ProposalSubmissionResource::class,
        InstitutionResource::class,
        ProposalDraftResource::class,
        DivisionResource::class,
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $ticketingRole = Role::firstOrCreate(
                ['name' => 'ticketing'],
                ['display_name' => 'Ticketing', 'is_active' => true]
            );

            $pengajuanRole = Role::firstOrCreate(
                ['name' => 'pengajuan-dana'],
                ['display_name' => 'Pengajuan Dana', 'is_active' => true]
            );

            $ticketingPermissionIds = $this->syncPermissionsFor($this->ticketingResources);
            $pengajuanPermissionIds = $this->syncPermissionsFor($this->pengajuanDanaResources);

            $ticketingRole->permissions()->syncWithoutDetaching($ticketingPermissionIds);
            $pengajuanRole->permissions()->syncWithoutDetaching($pengajuanPermissionIds);
        });

        $this->command?->info('Role & permission Ticketing + Pengajuan Dana berhasil di-generate.');
    }

    /**
     * @param  array<class-string>  $resourceClasses
     * @return array<int> id permission yang tersinkron
     */
    protected function syncPermissionsFor(array $resourceClasses): array
    {
        $ids = [];

        foreach ($resourceClasses as $resourceClass) {
            $names = $resourceClass::getRbacPermissionNames();
            // tambahan action deleteAny biar konsisten dengan trait
            $names['deleteAny'] = $resourceClass::getRbacGroup().'.'.$resourceClass::getRbacResource().'.delete';

            foreach ($names as $action => $permissionName) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName],
                    [
                        'display_name' => ucfirst($action).' '.class_basename($resourceClass),
                        'group_name' => $resourceClass::getRbacGroup(),
                        'is_active' => true,
                    ]
                );

                $ids[] = $permission->id;
            }
        }

        return array_unique($ids);
    }
}
