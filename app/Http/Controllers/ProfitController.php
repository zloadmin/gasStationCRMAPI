<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfitRequest;
use App\Services\ProfitService;
use Illuminate\Http\JsonResponse;

class ProfitController extends Controller
{
    public function __construct(
        private readonly ProfitService $profitService
    ) {}

    public function index(ProfitRequest $request): JsonResponse
    {
        $data = $this->profitService->get(
            $request->validated()
        );

        return response()->json([
            'data' => $data,
        ]);
    }
}
