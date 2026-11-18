<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class TransactionController extends Controller
{
    public function index(IndexTransactionRequest $request)
    {
        $filters = $request->validated();

        $query = Transaction::query()
            ->with([
                'terminal',
                'terminalService',
            ]);

        // Period
        $query->when(
            $filters['date_from'] ?? null,
            fn (Builder $q, $date) => $q->where(
                'remote_created_at',
                '>=',
                $date
            )
        );

        $query->when(
            $filters['date_to'] ?? null,
            fn (Builder $q, $date) => $q->where(
                'remote_created_at',
                '<=',
                $date
            )
        );

        // Fuel service
        $query->when(
            $filters['service_id'] ?? null,
            fn (Builder $q, $id) => $q->where('service_id', $id)
        );

        // Terminal
        $query->when(
            $filters['terminal_id'] ?? null,
            fn (Builder $q, $id) => $q->where('terminal_id', $id)
        );

        // Payment type
        $query->when(
            $filters['type'] ?? null,
            fn (Builder $q, $type) => $q->where('type', $type)
        );

        // Transaction ID
        $query->when(
            $filters['id'] ?? null,
            fn (Builder $q, $id) => $q->where('id', $id)
        );

        // Terminal transaction ID
        $query->when(
            $filters['tid'] ?? null,
            fn (Builder $q, $tid) => $q->where('tid', $tid)
        );

        return TransactionResource::collection(
            $query
                ->orderByDesc('remote_created_at')
                ->paginate($filters['per_page'] ?? 20)
        );
    }
}
