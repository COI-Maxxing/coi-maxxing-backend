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
        // Select Policy for Project Manager
        DB::statement('
            CREATE POLICY "upload_requests_select_own_company"
            ON upload_requests
            FOR SELECT
            USING (company_id = get_current_company_id());
        ');

        // Select Policy for Portal Token Validation
        DB::statement('
            CREATE POLICY "upload_requests_portal_token_lookup"
            ON upload_requests
            FOR SELECT
            USING (
                get_current_company_id() IS NULL
                AND used_at IS NULL
                AND expires_at > now()
            );
        ');

        DB::statement('
            CREATE POLICY "upload_requests_insert_own_company"
            ON upload_requests
            FOR INSERT
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "upload_requests_update_own_company"
            ON upload_requests
            FOR UPDATE
            USING (company_id = get_current_company_id())
            WITH CHECK (company_id = get_current_company_id());
        ');

        DB::statement('
            CREATE POLICY "upload_requests_delete_own_company"
            ON upload_requests
            FOR DELETE
            USING (false);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS "upload_requests_select_own_company" ON upload_requests;');
        DB::statement('DROP POLICY IF EXISTS "upload_requests_portal_token_lookup" ON upload_requests;');
        DB::statement('DROP POLICY IF EXISTS "upload_requests_insert_own_company" ON upload_requests;');
        DB::statement('DROP POLICY IF EXISTS "upload_requests_update_own_company" ON upload_requests;');
        DB::statement('DROP POLICY IF EXISTS "upload_requests_delete_own_company" ON upload_requests;');
    }
};
