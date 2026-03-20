<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The User model uses UUIDs (uuid7) but Sanctum's default migration
     * creates tokenable_id as bigint. This changes it to uuid and also
     * grants the "coi-maxxing-app" role the required permissions.
     */
    public function up(): void
    {
        // Change tokenable_id from bigint to uuid
        DB::statement('DROP INDEX IF EXISTS personal_access_tokens_tokenable_type_tokenable_id_index');
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE uuid USING tokenable_id::text::uuid');
        DB::statement('CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id)');

        // Grant permissions to app role
        DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON personal_access_tokens TO "coi-maxxing-app"');
        DB::statement('GRANT USAGE, SELECT ON SEQUENCE personal_access_tokens_id_seq TO "coi-maxxing-app"');
    }

    public function down(): void
    {
        DB::statement('REVOKE SELECT, INSERT, UPDATE, DELETE ON personal_access_tokens FROM "coi-maxxing-app"');
        DB::statement('REVOKE USAGE, SELECT ON SEQUENCE personal_access_tokens_id_seq FROM "coi-maxxing-app"');

        DB::statement('DROP INDEX IF EXISTS personal_access_tokens_tokenable_type_tokenable_id_index');
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE bigint USING tokenable_id::text::bigint');
        DB::statement('CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id)');
    }
};
