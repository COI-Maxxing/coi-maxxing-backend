<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubcontractorRequest;
use App\Models\Subcontractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcontractorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // eager loading the documents associated with the subcontractor
        $subcontractors = Subcontractor::with('documents')->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $subcontractors
        ]);
    }

    public function store(StoreSubcontractorRequest $request): JsonResponse
    {
        $subcontractors = Subcontractor::create($request->validated());

        return response()->json([
            'data' => $subcontractors
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $subcontractor = Subcontractor::with(['documents.events'])->findOrFail($id);

        return response()->json([
            'data' => $subcontractor
        ]); 
    }

    public function destroy(string $id): JsonResponse
    {
        $subcontractor = Subcontractor::findOrFail($id);
        $subcontractor->delete();

        return response()->json(null, 204);
    }
}
