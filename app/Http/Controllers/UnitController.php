<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterUnitRequest;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Http\Services\UnitService;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
    public function __construct(
        private readonly UnitService $unitService
    ) {
    }

    public function index(FilterUnitRequest $request): AnonymousResourceCollection
    {
        return UnitResource::collection(
            $this->unitService->getAllUnits($request->validated())
        );
    }

    public function store(StoreUnitRequest $request): UnitResource
    {
        return UnitResource::make(
            $this->unitService->createUnit($request->validated())
        );
    }

    public function show(Unit $unit): UnitResource
    {
        return UnitResource::make(
            $this->unitService->showUnit($unit, request()->current_membership)
        );
    }

    public function update(UpdateUnitRequest $request, Unit $unit): UnitResource
    {
        return UnitResource::make(
            $this->unitService->updateUnit($unit, $request->validated())
        );
    }

    public function destroy(Unit $unit): void
    {
        $this->unitService->deleteUnit($unit, request()->current_membership);
    }
}
