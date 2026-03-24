<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE POLICY users_select_for_login
            ON users
            FOR SELECT
            TO \"coi-maxxing-app\"
            USING (get_current_company_id() IS NULL);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS users_select_for_login ON users;");
    }
};
