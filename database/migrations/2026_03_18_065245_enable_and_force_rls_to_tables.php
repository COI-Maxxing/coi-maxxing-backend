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
        // Enable Row Level Security
        DB::statement("ALTER TABLE companies ENABLE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE users ENABLE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE subcontractors ENABLE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE documents ENABLE ROW LEVEL SECURITY");
        DB::statement("ALTER TABLE upload_requests ENABLE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE document_events ENABLE ROW LEVEL SECURITY;");

        // Force Row Level Security
        DB::statement("ALTER TABLE companies FORCE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE users FORCE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE subcontractors FORCE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE documents FORCE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE upload_requests FORCE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE document_events FORCE ROW LEVEL SECURITY;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
