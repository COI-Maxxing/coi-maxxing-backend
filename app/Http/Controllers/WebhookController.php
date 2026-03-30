<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebhookExtractionRequest;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleExtraction(WebhookExtractionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $document = Document::withoutGlobalScopes()->findOrFail($data['document_id']);

        DB::statement(
            "SELECT set_config('app.current_company_id', ?, false)",
            [$document->company_id]
        );

        if ($document->status !== 'pending') {
            Log::warning("Webhook ignored: Document {$document->id} is not in pending status.");
            return response()->json([
                "message" => "Webhook Ignored"
            ], 200);
        }

        DB::transaction(function () use ($data, $document) {
            if ($data['status'] === 'success') {
                $updatedData = [
                    'insurer' => $data['insurer'],
                    'policy_number' => $data['policy_number'],
                    'coverage_amount' => $data['coverage_amount'],
                    'expiry_date' => $data['expiry_date'],
                    'holder_name' => $data['holder_name'],
                    'ai_raw_response' => $data['ai_raw_response'],
                    'status' => 'pending_review'
                ];
                $document->update($updatedData);
                $document->logEvent('extracted', 'system', ['model' => 'stub-v1']);
            }

            if ($data['status'] === 'failed') {
                $document->update(['status' => 'rejected']);
                $document->logEvent('extraction_failed', 'system', ['error' => $data['error_message'] ?? 'No error detail provided.']);
            }
        });

        return response()->json([
            "message" => "Webhook Processed."
        ], 200);
    }
}
