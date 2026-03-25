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
        Schema::create('document_events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->text('event_type');
            $table->text('actor')->check("actor <> ''");
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('document_id'); // audit timeline query
            $table->index('company_id'); // rls evaluation on every read
        });

        DB::statement("ALTER TABLE document_events ADD CONSTRAINT check_event_type CHECK (event_type IN ('uploaded', 'extraction_started', 'validated', 'extracted', 'rejected', 'expiring_soon', 'expired', 'update_requested'))");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_events');
    }
};
