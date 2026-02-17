<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterCommonAreaRequest;
use App\Http\Requests\StoreCommonAreaRequest;
use App\Http\Requests\UpdateCommonAreaRequest;
use App\Http\Resources\CommonAreaResource;
use App\Http\Services\CommonAreaService;
use App\Models\CommonArea;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommonAreaController extends Controller
{
    public function __construct(
        private readonly CommonAreaService $commonAreaService
    ) {

    }

    public function index(FilterCommonAreaRequest $request): AnonymousResourceCollection
    {
        return CommonAreaResource::collection(
            $this->commonAreaService->getAllCommonAreas($request->validated())
        );
    }

    public function store(StoreCommonAreaRequest $request)
    {
        return CommonAreaResource::make(
            $this->commonAreaService->store($request->validated())
        );
    }

    public function show(CommonArea $commonArea)
    {
        return CommonAreaResource::make(
            $this->commonAreaService->show($commonArea, request()->current_membership)
        );
    }

    public function update(UpdateCommonAreaRequest $request, CommonArea $commonArea)
    {
        return CommonAreaResource::make(
            $this->commonAreaService->update($commonArea, $request->validated())
        );
    }

    public function destroy(CommonArea $commonArea)
    {
        $this->commonAreaService->destroy($commonArea, request()->current_membership);
    }
}
