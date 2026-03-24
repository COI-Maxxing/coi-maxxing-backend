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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignUuid('subcontractor_id')->constrained('subcontractors')->onDelete('cascade');
            $table->text('document_type');
            $table->text('status')->default('pending');
            $table->text('file_url')->nullable();
            $table->text('uploaded_by')->nullable();
            $table->text('insurer')->nullable();
            $table->text('policy_number')->nullable(); # COI Specific
            $table->decimal('coverage_amount', 15, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('holder_name')->nullable();
            $table->jsonb('ai_raw_response')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrentOnUpdate();

            $table->index('company_id'); // RLS Evaluation on Every Document Read
            $table->index('subcontractor_id'); // Subcontractor Detail Page
            $table->index(['subcontractor_id', 'document_type']); // Compliance Slot Lookup Per Sub Per Type
            $table->index(['status', 'expiry_date']); // nightly expiry cron
        });

        DB::statement("ALTER TABLE documents ADD CONSTRAINT check_document_type CHECK (document_type IN ('coi', 'license', 'w9'))");
        DB::statement("ALTER TABLE documents ADD CONSTRAINT check_status CHECK (status IN ('pending', 'pending_review', 'valid', 'expiring_soon', 'extracted', 'expired', 'rejected'))");
        DB::statement("ALTER TABLE documents ADD CONSTRAINT check_coverage_amount CHECK (coverage_amount > 0)");

        DB::statement("
            CREATE INDEX idx_subcontractor_doc_type_active
            ON documents (subcontractor_id, document_type)
            WHERE status IN ('valid', 'pending_review')
        "); // one active document business rule
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
