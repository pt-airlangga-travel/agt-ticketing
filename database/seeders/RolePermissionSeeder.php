<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // CREATE PERMISSIONS (auto from Filament resources)
        // =========================
        $this->command?->info('Men-generate permission dari resource Filament...');

        $permissions = [];

        foreach (filament()->getResources() as $resource) {
            $groupName = $resource::getRbacGroupLabel();
            $groupSlug = $resource::getRbacGroup();
            $resourceSlug = $resource::getRbacResource();

            $this->command?->line("  - {$groupName} / {$resourceSlug}");

            foreach ($resource::getRbacPermissionNames() as $action => $name) {
                $permission = Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => Str::headline($action).' '.Str::headline($resourceSlug),
                        'description' => Str::headline($action).' data '.Str::headline($resourceSlug).' ('.Str::headline($groupName).')',
                        'group_name' => $groupName,
                        'is_active' => true,
                    ]
                );

                $permissions[$name] = $permission->id;
            }
        }

        // Bersihkan permission master data lama yang masih tergabung di grup "Pengajuan Dana"
        foreach (['divisions', 'institutions', 'banks', 'bank-asals', 'needs'] as $staleSlug) {
            Permission::where('name', 'like', "pengajuan-dana.{$staleSlug}.%")->delete();
        }

        $this->command?->info('Membersihkan permission master data stale di grup Pengajuan Dana...');

        // =========================================================
        // 2. CREATE/UPDATE SUPER ADMIN ROLE
        // =========================================================
        $superAdmin = Role::updateOrCreate(
            ['name' => 'super-admin'],
            [
                'display_name' => 'Super Admin',
                'description' => 'Memiliki seluruh izin akses',
                'is_active' => true,
            ]
        );

        // Assign all permissions to Super Admin
        $superAdmin->permissions()->sync(array_values($permissions));

        // =========================================================
        // 3. CREATE/UPDATE BUSINESS ROLES (PENGAJUAN DANA)
        // =========================================================
        $this->command?->info('Membuat role bisnis Pengajuan Dana...');

        $roles = [
            'creative-member' => [
                'display_name' => 'Creative Member',
                'description' => 'Membuat proposal draft dan mengajukan submission',
                'is_active' => true,
                'permissions' => [
                    'pengajuan-dana.events.view',
                    'pengajuan-dana.events.create',
                    'pengajuan-dana.proposal-drafts.view',
                    'pengajuan-dana.proposal-drafts.create',
                    'pengajuan-dana.proposal-drafts.edit',
                    'pengajuan-dana.proposal-submissions.view',
                    'pengajuan-dana.proposal-submissions.create',
                    'pengajuan-dana.proposal-submissions.edit',
                ],
            ],
            'organizer-admin' => [
                'display_name' => 'Organizer Admin',
                'description' => 'Menyetujui/lengkapi proposal dan submission',
                'is_active' => true,
                'permissions' => [
                    'pengajuan-dana.events.view',
                    'pengajuan-dana.events.create',
                    'pengajuan-dana.events.edit',
                    'pengajuan-dana.proposal-drafts.view',
                    'pengajuan-dana.proposal-drafts.create',
                    'pengajuan-dana.proposal-drafts.edit',
                    'pengajuan-dana.proposal-submissions.view',
                    'pengajuan-dana.proposal-submissions.create',
                    'pengajuan-dana.proposal-submissions.edit',
                ],
            ],
            'inspiring-manager' => [
                'display_name' => 'Inspiring Manager',
                'description' => 'Menyetujui, menolak, atau mengembalikan submission',
                'is_active' => true,
                'permissions' => [
                    'pengajuan-dana.events.view',
                    'pengajuan-dana.proposal-drafts.view',
                    'pengajuan-dana.proposal-submissions.view',
                    'pengajuan-dana.proposal-submissions.edit',
                ],
            ],
            'eagle-treasurer' => [
                'display_name' => 'Eagle Treasurer',
                'description' => 'Memproses transfer dan bukti transfer submission yang disetujui',
                'is_active' => true,
                'permissions' => [
                    'pengajuan-dana.proposal-submissions.view',
                    'pengajuan-dana.proposal-submissions.edit',
                    'master-data.banks.view',
                    'master-data.bank-asals.view',
                ],
            ],
            'system-admin' => [
                'display_name' => 'System Admin',
                'description' => 'Mengelola master data pengajuan dana',
                'is_active' => false,
                'permissions' => [
                    'master-data.divisions.view',
                    'master-data.divisions.create',
                    'master-data.divisions.edit',
                    'master-data.divisions.delete',
                    'master-data.institutions.view',
                    'master-data.institutions.create',
                    'master-data.institutions.edit',
                    'master-data.institutions.delete',
                    'master-data.banks.view',
                    'master-data.banks.create',
                    'master-data.banks.edit',
                    'master-data.banks.delete',
                    'master-data.bank-asals.view',
                    'master-data.bank-asals.create',
                    'master-data.bank-asals.edit',
                    'master-data.bank-asals.delete',
                    'master-data.needs.view',
                    'master-data.needs.create',
                    'master-data.needs.edit',
                    'master-data.needs.delete',
                ],
            ],
        ];

        foreach ($roles as $name => $config) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $config['display_name'],
                    'description' => $config['description'],
                    'is_active' => $config['is_active'],
                ]
            );

            $permIds = [];
            foreach ($config['permissions'] as $permName) {
                if (isset($permissions[$permName])) {
                    $permIds[] = $permissions[$permName];
                }
            }

            $role->permissions()->sync($permIds);

            $this->command?->line("  - {$name} ({$config['display_name']}) aktif=".var_export($config['is_active'], true).' perms='.count($permIds));
        }

        // =========================================================
        // 4. ASSIGN SUPER ADMIN USER (id 17)
        // =========================================================
        $user = User::where('username', 'superadmin')->orWhere('id', 17)->first();
        $role = Role::where('name', 'super-admin')->first();

        if ($user && $role) {
            $user->roles()->sync([$role->id]);
            $user->forgetAccessCache();
            $this->command?->info("Super admin ditetapkan ke user id {$user->id} ({$user->username}).");
        } else {
            $this->command?->warn('User superadmin (id 17) tidak ditemukan.');
        }
    }
}
