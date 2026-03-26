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

class ProcessDocumentJob implements ShouldQueue
{
    use Queueable, SerializesModels, Dispatchable, InteractsWithQueue;

    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

   public function handle(): void
    {
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
            "webhook_secret" => config('services.fastapi.webhook_secret'),
            "document_type" => $this->document->document_type
        ];

        $fastApi_url = config('services.fastapi.url');

        Http::timeout(75)->throw()->post($fastApi_url, $payload);

    }
}
