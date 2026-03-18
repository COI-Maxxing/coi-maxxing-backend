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
            CREATE POLICY "documents_select_own_company"
            ON documents
            FOR SELECT
            USING (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "documents_insert_own_company"
            ON documents
            FOR INSERT
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "documents_update_own_company"
            ON documents
            FOR UPDATE
            USING (company_id = get_current_company_id())
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "documents_delete_own_company"
            ON documents
            FOR DELETE
            USING (company_id = get_current_company_id());
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS "documents_select_own_company" ON documents;');
        DB::statement('DROP POLICY IF EXISTS "documents_insert_own_company" ON documents;');
        DB::statement('DROP POLICY IF EXISTS "documents_update_own_company" ON documents;');
        DB::statement('DROP POLICY IF EXISTS "documents_delete_own_company" ON documents;');
    }
};
