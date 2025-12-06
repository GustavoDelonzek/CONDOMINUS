<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterAdminCompanyRequest;
use App\Http\Requests\StoreAdminCompanyRequest;
use App\Http\Resources\AdminCompanyResource;
use App\Http\Services\AdminCompanyService;
use App\Models\AdminCompany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminCompanyController extends Controller
{
    public function __construct(
        private readonly AdminCompanyService $adminCompanyService,
    ) {
    }

    public function index(FilterAdminCompanyRequest $request): AnonymousResourceCollection
    {
        return AdminCompanyResource::collection(
            $this->adminCompanyService->index($request->validated())
        );
    }

    public function store(StoreAdminCompanyRequest $request)
    {
        return AdminCompanyResource::make(
            $this->adminCompanyService->store($request->validated())
        );
    }

    public function show(AdminCompany $adminCompany)
    {
        return AdminCompanyResource::make($adminCompany);
    }
}
