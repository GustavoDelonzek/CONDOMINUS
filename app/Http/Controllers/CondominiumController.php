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

    public function index(CondominiumsFiltersRequest $request): AnonymousResourceCollection
    {
        return CondominiumResource::collection(
            $this->condominiumService->getCondominiums(
                $request->validated(),
                $request->user(),
            )
        );
    }

    public function store(StoreCondominiumRequest $request): CondominiumResource
    {
        return CondominiumResource::make(
            $this->condominiumService->store($request->validated(), $request->user())
        );
    }

    public function show(Request $request, Condominium $condominium): CondominiumResource
    {
        return CondominiumResource::make(
            $this->condominiumService->showCondominium($condominium, $request->user())
        );
    }

    public function update(UpdateCondominiumRequest $request, Condominium $condominium): CondominiumResource
    {
        return CondominiumResource::make(
            $this->condominiumService->updateCondominium($condominium, $request->validated(), $request->user())
        );
    }
}
