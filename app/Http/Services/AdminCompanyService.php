<?php

namespace App\Http\Services;

use App\Filters\AdminCompanyFilter;
use App\Models\AdminCompany;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminCompanyService
{
    public function index(array $data): LengthAwarePaginator
    {
        $query = (new AdminCompanyFilter(AdminCompany::query(), $data))->applyFilters();
        $query->orderBy('created_at', 'desc');

        return $query->paginate((int) data_get($data, 'per_page', 15));
    }

    public function store(array $data): AdminCompany
    {
        return AdminCompany::create($data);
    }
}
