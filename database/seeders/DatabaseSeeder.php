<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Seeds the initial SuperAdmin + a few demo companies.
//
// The spec says: "Create a SuperAdmin account using a Database Seeder using raw SQL."
// So we use DB::statement(...) below rather than Eloquent / the query builder.
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $hashed = Hash::make('password'); // default password = "password"

        // 3 demo companies, inserted in one go.
        DB::statement(
            'INSERT INTO companies (name, created_at, updated_at) VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?)',
            [
                'Acme Corp',         $now, $now,
                'Tech Solutions',    $now, $now,
                'Global Industries', $now, $now,
            ]
        );

        // SuperAdmin -- no company (they're global).
        DB::statement(
            'INSERT INTO users (name, email, password, role, company_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            ['Super Admin', 'superadmin@example.com', $hashed, 'SuperAdmin', null, $now, $now]
        );
    }
}
