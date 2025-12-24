<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterBlockRequest;
use App\Http\Requests\StoreBlockRequest;
use App\Http\Requests\UpdateBlockRequest;
use App\Http\Resources\BlockResource;
use App\Http\Services\BlockService;
use App\Models\Block;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlockController extends Controller
{
    public function __construct(
        private BlockService $blockService
    ) {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(FilterBlockRequest $request): AnonymousResourceCollection
    {
        return BlockResource::collection(
            $this->blockService->getAllBlocksByCondominiumId($request->validated())
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlockRequest $request): BlockResource
    {
        return BlockResource::make(
            $this->blockService->createBlockInCondominium($request->validated())
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Block $block)
    {
        //TODO: Apenas adicionar as verificações necessárias aqui
        return BlockResource::make($block);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlockRequest $request, Block $block): BlockResource
    {
        return BlockResource::make(
            $this->blockService->updateBlock($block, $request->validated())
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Block $block)
    {
        //TODO: adicionar as verificações necessárias...
        $block->delete();
    }
}
