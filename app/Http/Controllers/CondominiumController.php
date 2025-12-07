<?php

namespace App\Http\Controllers;

use App\Http\Requests\CondominiumsFiltersRequest;
use App\Http\Requests\StoreCondominiumRequest;
use App\Http\Requests\UpdateCondominiumRequest;
use App\Http\Resources\CondominiumResource;
use App\Http\Services\CondominiumService;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CondominiumController extends Controller
{

    public function __construct(
        private readonly CondominiumService $condominiumService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(CondominiumsFiltersRequest $request): AnonymousResourceCollection
    {
        return CondominiumResource::collection(
            $this->condominiumService->getCondominiums(
                $request->validated(),
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCondominiumRequest $request): CondominiumResource
    {
        return CondominiumResource::make(
            $this->condominiumService->store($request->validated())
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Condominium $condominium): CondominiumResource
    {
        return CondominiumResource::make($condominium);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCondominiumRequest $request, Condominium $condominium): CondominiumResource
    {
        return CondominiumResource::make(
            $this->condominiumService->updateCondominium($condominium, $request->validated())
        );
    }
}
