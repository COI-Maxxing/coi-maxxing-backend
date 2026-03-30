<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessDocumentJob implements ShouldQueue
{
    use Queueable, SerializesModels, Dispatchable, InteractsWithQueue;

    protected $documentId;
    protected $companyId; // for rls context only
    protected $document;
    public $tries = 3;

    public function __construct(string $documentId, string $companyId)
    {
        $this->documentId = $documentId;
        $this->companyId = $companyId;
    }

   public function handle(): void
   {
        DB::statement("SELECT set_config('app.current_company_id', ?, false)", [$this->companyId]);

        $this->document = Document::findOrFail($this->documentId);

        if (!$this->document) {

        }

        // reload the document to get the fresh data
        $this->document->refresh();

        if ($this->document->status != "pending") {
            Log::warning("Only documents in pending can be processed. Job Aborted: Document {$this->document->id}");
            return;
        }

        $this->document->logEvent("extraction_started", "system");

        // payload
        $payload = [
            "document_id" => $this->document->id,
            "pdf_url" => $this->document->file_url,
            "webhook_url" => url('/api/webhooks/extraction'),
            "document_type" => $this->document->document_type
        ];

        $fastApi_url = config('services.fastapi.url');

        Http::timeout(75)->throw()->post($fastApi_url, $payload);           
    }

    public function failed(Throwable $e): void
    {
        $this->document->update(['status' => 'rejected']);
        $this->document->logEvent('extraction_failed', 'system', ['error' => $e->getMessage()]);
        Log::error("ProcessDocumentJob permanently failed for Document {$this->document->id}: {$e->getMessage()}");
    }
}
