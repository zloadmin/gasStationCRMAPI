<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProfitService
{
    /**
     * Успешные типы оплаты.
     */
    private const SUCCESS_TYPES = [
        'payment_cash',
        'payment_card',
        'payment_sbp',
    ];

    public function get(array $filters): array
    {
        $dateFrom = Carbon::parse($filters['date_from'])->startOfDay();
        $dateTo = Carbon::parse($filters['date_to'])->endOfDay();

        $serviceId = $filters['service_id'] ?? null;

        // Продолжительность выбранного периода в днях.
        $days = $dateFrom->copy()->startOfDay()->diffInDays($dateTo->copy()->startOfDay()) + 1;

        // Предыдущий аналогичный период.
        $previousDateTo = $dateFrom->copy()->subSecond();
        $previousDateFrom = $previousDateTo->copy()->subDays($days)->startOfDay();

        $current = $this->getPeriodData(
            $dateFrom,
            $dateTo,
            $serviceId
        );

        $previous = $this->getPeriodData(
            $previousDateFrom,
            $previousDateTo,
            $serviceId
        );

        $summary = $this->makeSummary($current['totals'], $previous['totals']);

        return [
            'summary' => $summary,
            'chart' => $current['chart'],
            'by_services' => $current['by_services'],
        ];
    }

    /**
     * Получить данные за период.
     */
    private function getPeriodData(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $serviceId
    ): array {
        $query = $this->baseQuery($dateFrom, $dateTo, $serviceId);

        $byServices = (clone $query)
            ->select([
                'transactions.service_id',
                'services.name as service_name',
            ])
            ->selectRaw('SUM(transactions.amount_dispensed) as volume_liters')
            ->selectRaw('SUM(transactions.amount_dispensed * transactions.service_price) as revenue')
            ->selectRaw('SUM(
                transactions.amount_dispensed
                * fuel_arrivals.total_price
                / fuel_arrivals.volume_liters
            ) as cost')
            ->groupBy(
                'transactions.service_id',
                'services.name'
            )
            ->orderBy('services.name')
            ->get()
            ->map(function ($item) {
                $revenue = (float) $item->revenue;
                $cost = (float) $item->cost;

                return [
                    'service_id' => (int) $item->service_id,
                    'service_name' => $item->service_name,
                    'volume_liters' => round((float) $item->volume_liters, 3),
                    'revenue' => round($revenue, 2),
                    'cost' => round($cost, 2),
                    'profit' => round($revenue - $cost, 2),
                ];
            })
            ->values()
            ->toArray();

        $totals = [
            'revenue' => 0,
            'cost' => 0,
            'profit' => 0,
        ];

        foreach ($byServices as $item) {
            $totals['revenue'] += $item['revenue'];
            $totals['cost'] += $item['cost'];
            $totals['profit'] += $item['profit'];
        }

        $chart = (clone $query)
            ->selectRaw('DATE(transactions.remote_created_at) as date')
            ->selectRaw('SUM(transactions.inserted) as revenue')
            ->selectRaw('SUM(
                transactions.amount_dispensed
                * fuel_arrivals.total_price
                / fuel_arrivals.volume_liters
            ) as cost')
            ->groupByRaw('DATE(transactions.remote_created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $revenue = (float) $item->revenue;
                $cost = (float) $item->cost;

                return [
                    'date' => $item->date,
                    'revenue' => round($revenue, 2),
                    'cost' => round($cost, 2),
                    'profit' => round($revenue - $cost, 2),
                ];
            })
            ->values()
            ->toArray();

        return [
            'totals' => [
                'revenue' => round($totals['revenue'], 2),
                'cost' => round($totals['cost'], 2),
                'profit' => round($totals['profit'], 2),
            ],
            'chart' => $chart,
            'by_services' => $byServices,
        ];
    }

    /**
     * Базовый запрос транзакций с приходами и марками топлива.
     */
    private function baseQuery(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $serviceId
    ): Builder {
        return DB::table('transactions')
            ->join(
                'fuel_arrivals',
                'fuel_arrivals.id',
                '=',
                'transactions.fuel_arrival_id'
            )
            ->join(
                'services',
                'services.id',
                '=',
                'transactions.service_id'
            )
//            ->whereIn('transactions.type', self::SUCCESS_TYPES)
            ->whereBetween('transactions.remote_created_at', [
                $dateFrom,
                $dateTo,
            ])
            ->whereNotNull('transactions.amount_dispensed')
            ->whereNotNull('transactions.service_price')
            ->whereNotNull('fuel_arrivals.volume_liters')
            ->whereNotNull('fuel_arrivals.total_price')
            ->where('transactions.amount_dispensed', '>', 0)
            ->where('transactions.service_price', '>', 0)
            ->where('fuel_arrivals.volume_liters', '>', 0)
            ->where('fuel_arrivals.total_price', '>=', 0)
            ->when(
                $serviceId,
                fn (Builder $query) => $query->where(
                    'transactions.service_id',
                    $serviceId
                )
            );
    }

    /**
     * Сформировать сводные показатели и изменения.
     */
    private function makeSummary(array $current, array $previous): array
    {
        $revenue = $current['revenue'];
        $cost = $current['cost'];
        $profit = $current['profit'];

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $profit,
            'profitability' => $revenue > 0
                ? round(($profit / $revenue) * 100, 2)
                : 0,

            'changes' => [
                'revenue' => $this->calculateChange(
                    $current['revenue'],
                    $previous['revenue']
                ),
                'cost' => $this->calculateChange(
                    $current['cost'],
                    $previous['cost']
                ),
                'profit' => $this->calculateChange(
                    $current['profit'],
                    $previous['profit']
                ),
            ],
        ];
    }

    /**
     * Процент изменения относительно предыдущего периода.
     */
    private function calculateChange(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 2);
    }
}
