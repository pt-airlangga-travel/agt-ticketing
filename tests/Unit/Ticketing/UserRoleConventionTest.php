<?php

namespace Tests\Unit\Ticketing;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

/**
 * Test murni (tanpa database) untuk konvensi model yang dipakai modul Ticketing.
 */
class UserRoleConventionTest extends TestCase
{
    public function test_username_is_slugified_on_assignment(): void
    {
        $user = new User;

        $user->username = '  Budi Santoso!! ';

        $this->assertSame('budi_santoso', $user->username);
    }

    public function test_role_name_is_slugified_on_assignment(): void
    {
        $role = new Role;

        $role->name = 'Ticketing Admin';

        $this->assertSame('ticketing-admin', $role->name);
    }
}
