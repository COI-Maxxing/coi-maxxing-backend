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
        DB::unprepared("
            CREATE OR REPLACE FUNCTION get_current_company_id()
            RETURNS uuid
            LANGUAGE sql
            STABLE
            SECURITY DEFINER
            AS $$
                SELECT NULLIF(current_setting('app.current_company_id', true), '')::uuid;
            $$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP FUNCTION get_current_company_id();");
    }
};
