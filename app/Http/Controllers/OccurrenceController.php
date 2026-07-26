<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterOccurrenceRequest;
use App\Http\Requests\UpdateOccurrenceRequest;
use App\Http\Resources\OccurrenceResource;
use App\Http\Services\OccurrenceService;
use App\Models\Occurrence;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OccurrenceController extends Controller
{
    public function __construct(
        private readonly OccurrenceService $occurrenceService
    ) {
    }

    public function index(FilterOccurrenceRequest $request): AnonymousResourceCollection
    {
        return OccurrenceResource::collection(
            $this->occurrenceService->getAllOccurrences($request->validated())
        );
    }

    public function updateStatus(UpdateOccurrenceRequest $request, Occurrence $occurrence): OccurrenceResource
    {
        return OccurrenceResource::make(
            $this->occurrenceService->updateStatus($occurrence, $request->validated())
        );
    }
}
