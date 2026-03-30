<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\ValidateDocumentRequest;
use App\Jobs\ProcessDocumentJob;
use App\Models\Subcontractor;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(string $subcontractorId): JsonResponse
    {
        $subcontractor = Subcontractor::findOrFail($subcontractorId);

        $documents = $subcontractor->documents()->with('events')->orderBy('created_at', 'desc')->paginate(request()->input('per_page', 25));

        return response()->json([
            'data' => $documents
        ]);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = Document::create(
            array_merge($request->validated(), ['status' => 'pending'])
        );

        $document->logEvent(
            eventType: 'uploaded',
            actor: auth('sanctum')->user()->email,
        );

        ProcessDocumentJob::dispatch($document, $document->company_id)->afterCommit();        

        return response()->json([
            'data' => $document
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $document = Document::with(['events', 'subcontractor'])->findOrFail($id);

        return response()->json([
            'data' => $document
        ]);
    }

    public function confirm(ValidateDocumentRequest $request, string $id): JsonResponse
    {
        $document = Document::findOrFail($id);

        if ($document->status != 'pending_review') {
            return response()->json([
                'message' => 'Only documents in pending_review status can be validated.',
                'current_status' => $document->status,
            ], 409);
        }

        $corrections = $request->validated();

        $document->update(array_merge($corrections, ['status' => 'valid']));

        $document->logEvent(
            eventType: 'validated',
            actor: auth('sanctum')->user()->email,
            metadata: empty($corrections) ? null : ['corrections_applied' => array_keys($corrections)]
        );

        return response()->json([
            'data' => $document->fresh(['events'])
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $document = Document::findOrFail($id);

        if ($document->status != 'pending_review') {
            return response()->json([
                'message' => 'Only documents in pending review status can be rejected.',
                'current_status' => $document->status
            ], 409);
        }

        $document->update(['status' => 'rejected']);

        $document->logEvent(
            eventType: 'rejected',
            actor: auth('sanctum')->user()->email,
            metadata: $request->filled('reason') ? ['reason' => $request->input('reason')] : null
        );

        return response()->json([
            'data' => $document->fresh(['events'])
        ]);
    }
}
