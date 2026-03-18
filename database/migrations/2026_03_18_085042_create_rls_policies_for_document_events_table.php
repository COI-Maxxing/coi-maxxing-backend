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
            CREATE POLICY "document_events_select_own_company"
            ON document_events
            FOR SELECT
            USING (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "document_events_insert_own_company"
            ON document_events
            FOR INSERT
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "document_events_update_own_company"
            ON document_events
            FOR UPDATE
            USING (false);
        ');

        DB::statement('
            CREATE POLICY "document_events_delete_own_company"
            ON document_events
            FOR DELETE
            USING (false);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS "document_events_select_own_company" ON document_events;');
        DB::statement('DROP POLICY IF EXISTS "document_events_insert_own_company" ON document_events;');
        DB::statement('DROP POLICY IF EXISTS "document_events_update_own_company" ON document_events;');
        DB::statement('DROP POLICY IF EXISTS "document_events_delete_own_company" ON document_events;');
    }
};
