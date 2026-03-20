<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * During login, app.current_company_id is NOT set (because no one is
     * authenticated yet). The existing RLS policy "users_select_same_company"
     * requires company_id = get_current_company_id(), which returns NULL —
     * so every SELECT returns 0 rows for the "coi-maxxing-app" role.
     *
     * This migration adds a SELECT policy that allows reading users
     * when no company context has been set (i.e. during authentication).
     */
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
