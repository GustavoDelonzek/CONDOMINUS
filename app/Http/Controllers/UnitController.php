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

    /**
     * Display a listing of the resource.
     */
    public function index(FilterUnitRequest $request): AnonymousResourceCollection
    {
        return UnitResource::collection(
            $this->unitService->getAllUnits($request->validated())
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request)
    {
        return UnitResource::make(
            $this->unitService->createUnit($request->validated())
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return UnitResource::make($unit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        return UnitResource::make(
            $this->unitService->updateUnit($unit, $request->validated())
        );
    }
}
