<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upload_requests', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignUuid('subcontractor_id')->constrained('subcontractors')->onDelete('cascade');
            $table->text('document_type');
            $table->text('token')->unique();
            $table->timestampTz('expires_at');
            $table->timestampTz('used_at')->nullable(); // NULL = unused
            $table->timestampTz('created_at')->useCurrent();

            $table->index('subcontractor_id');
            $table->index(['subcontractor_id', 'document_type', 'used_at']); // Duplicate Active Request Prevention
        });

        DB::statement("ALTER TABLE upload_requests ADD CONSTRAINT check_document_type CHECK (document_type IN ('coi', 'license', 'w9'))");
        DB::statement("ALTER TABLE upload_requests ADD CONSTRAINT check_expires_at CHECK (expires_at > created_at)");
        DB::statement("ALTER TABLE upload_requests ADD CONSTRAINT check_used_at CHECK (used_at IS NULL OR used_at >= created_at)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_requests');
    }
};
