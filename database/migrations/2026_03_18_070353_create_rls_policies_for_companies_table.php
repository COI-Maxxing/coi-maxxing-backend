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
            CREATE POLICY "companies_select_own"
            ON companies
            FOR SELECT
            USING (id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "companies_insert_deny"
            ON companies
            FOR INSERT
            WITH CHECK (false);
        ');

        DB::statement('
            CREATE POLICY "companies_update_own"
            ON companies
            FOR UPDATE
            USING (id = get_current_company_id())
            WITH CHECK (id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "companies_delete_deny"
            ON companies
            FOR DELETE
            USING (false);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS "companies_select_own" ON companies;');
        DB::statement('DROP POLICY IF EXISTS "companies_insert_deny" ON companies;');
        DB::statement('DROP POLICY IF EXISTS "companies_update_own" ON companies;');
        DB::statement('DROP POLICY IF EXISTS "companies_delete_deny" ON companies;');
    }
};
