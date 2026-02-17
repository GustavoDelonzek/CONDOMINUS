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
        private readonly BlockService $blockService
    ) {

    }

    public function index(FilterBlockRequest $request): AnonymousResourceCollection
    {
        return BlockResource::collection(
            $this->blockService->getAllBlocksByCondominiumId($request->validated())
        );
    }

    public function store(StoreBlockRequest $request): BlockResource
    {
        return BlockResource::make(
            $this->blockService->createBlockInCondominium($request->validated())
        );
    }

    public function show(Block $block): BlockResource
    {
        return BlockResource::make(
            $this->blockService->showBlock($block, request()->current_membership)
        );
    }

    public function update(UpdateBlockRequest $request, Block $block): BlockResource
    {
        return BlockResource::make(
            $this->blockService->updateBlock($block, $request->validated())
        );
    }

    public function destroy(Block $block): void
    {
        $this->blockService->deleteBlock($block, request()->current_membership);
    }
}
