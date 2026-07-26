<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterReservationRequest;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Http\Resources\ReservationResource;
use App\Http\Services\ReservationService;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService
    ) {
    }

    public function index(FilterReservationRequest $request): AnonymousResourceCollection
    {
        return ReservationResource::collection(
            $this->reservationService->getAllReservations($request->validated())
        );
    }

    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation): ReservationResource
    {
        return ReservationResource::make(
            $this->reservationService->updateStatus($reservation, $request->validated())
        );
    }
}
