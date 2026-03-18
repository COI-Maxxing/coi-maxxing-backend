<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            CREATE POLICY "users_select_same_company"
            ON users
            FOR SELECT
            USING (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "users_insert_own_company"
            ON users
            FOR INSERT
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "users_update_own_company"
            ON users
            FOR UPDATE
            USING (company_id = get_current_company_id())
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "users_delete_own_company"
            ON users
            FOR DELETE
            USING (company_id = get_current_company_id());
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS "users_select_same_company" ON users;');
        DB::statement('DROP POLICY IF EXISTS "users_insert_own_company" ON users;');
        DB::statement('DROP POLICY IF EXISTS "users_update_own_company" ON users;');
        DB::statement('DROP POLICY IF EXISTS "users_delete_own_company" ON users;');
    }
};
