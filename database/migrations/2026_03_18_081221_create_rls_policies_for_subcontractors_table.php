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
            CREATE POLICY "subcontractors_select_own_company"
            ON subcontractors
            FOR SELECT
            USING (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "subcontractors_insert_own_company"
            ON subcontractors
            FOR INSERT
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "subcontractors_update_own_company"
            ON subcontractors
            FOR UPDATE
            USING (company_id = get_current_company_id())
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "subcontractors_delete_own_company"
            ON subcontractors
            FOR DELETE
            USING (company_id = get_current_company_id());
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS "subcontractors_select_own_company" ON subcontractors;');
        DB::statement('DROP POLICY IF EXISTS "subcontractors_insert_own_company" ON subcontractors;');
        DB::statement('DROP POLICY IF EXISTS "subcontractors_update_own_company" ON subcontractors;');
        DB::statement('DROP POLICY IF EXISTS "subcontractors_delete_own_company" ON subcontractors;');
    }
};
