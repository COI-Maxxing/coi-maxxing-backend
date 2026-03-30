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
        DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE jobs TO \"coi-maxxing-app\";");
        DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE failed_jobs TO \"coi-maxxing-app\";");

        DB::statement("GRANT USAGE, SELECT ON SEQUENCE jobs_id_seq TO \"coi-maxxing-app\";");
        DB::statement("GRANT USAGE, SELECT ON SEQUENCE failed_jobs_id_seq TO \"coi-maxxing-app\";");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("REVOKE SELECT, INSERT, UPDATE, DELETE ON TABLE jobs FROM \"coi-maxxing-app\";");
        DB::statement("REVOKE SELECT, INSERT, UPDATE, DELETE ON TABLE failed_jobs FROM \"coi-maxxing-app\";");

        DB::statement("REVOKE USAGE, SELECT ON SEQUENCE jobs_id_seq FROM \"coi-maxxing-app\";");
        DB::statement("REVOKE USAGE, SELECT ON SEQUENCE failed_jobs_id_seq FROM \"coi-maxxing-app\";");
    }
};
