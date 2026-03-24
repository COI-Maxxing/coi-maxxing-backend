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
        // Create Role
        DB::unprepared("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'coi-maxxing-app') THEN
                    CREATE ROLE \"coi-maxxing-app\" WITH LOGIN PASSWORD 'secret_password_lol';
                END IF;
            END
            $$;
        ");

        // Grant Schema Usage
        DB::statement('GRANT USAGE ON SCHEMA public TO "coi-maxxing-app";');

        // Grant Table Permission
        DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON companies, users, subcontractors, documents, upload_requests, document_events TO "coi-maxxing-app";');

        // Grant Queue Monitoring
        DB::statement('GRANT SELECT ON jobs, failed_jobs TO "coi-maxxing-app";');

        // Grant Function Execution
        DB::statement('GRANT EXECUTE ON FUNCTION get_current_company_id() TO "coi-maxxing-app";');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Always Revoke Reversely
        DB::statement('REVOKE EXECUTE ON FUNCTION get_current_company_id() FROM "coi-maxxing-app";');
        DB::statement('REVOKE SELECT ON jobs, failed_jobs FROM "coi-maxxing-app";');
        DB::statement('REVOKE SELECT, INSERT, UPDATE, DELETE ON companies, users, subcontractors, documents, upload_requests, document_events FROM "coi-maxxing-app";');
        DB::statement('REVOKE USAGE ON SCHEMA public FROM "coi-maxxing-app";');
    }
};
