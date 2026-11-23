<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProfitService
{
    public function get(array $filters): array
    {
        $dateFrom = Carbon::parse($filters['date_from'])->startOfDay();
        $dateTo = Carbon::parse($filters['date_to'])->endOfDay();

        $serviceId = $filters['service_id'] ?? null;

        // Продолжительность выбранного периода в днях.
        $days = $dateFrom->copy()->startOfDay()->diffInDays(
                $dateTo->copy()->startOfDay()
            ) + 1;

        // Предыдущий аналогичный период.
        $previousDateTo = $dateFrom->copy()->subSecond();

        $previousDateFrom = $previousDateTo->copy()
            ->subDays($days)
            ->startOfDay();

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

        return [
            'summary' => $this->makeSummary(
                $current['totals'],
                $previous['totals']
            ),
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
        $query = $this->baseQuery(
            $dateFrom,
            $dateTo,
            $serviceId
        );

        // Прибыль по услугам.
        $byServices = (clone $query)
            ->select('transactions.service_id')
            ->selectRaw('MAX(transactions.profit_service_name) as service_name')
            ->selectRaw('SUM(transactions.amount_dispensed) as volume_liters')
            ->selectRaw('SUM(transactions.profit_revenue) as revenue')
            ->selectRaw('SUM(transactions.profit_cost) as cost')
            ->selectRaw('SUM(transactions.profit_profit) as profit')
            ->groupBy(
                'transactions.service_id'
            )
            ->orderBy('service_name')
            ->get()
            ->map(function ($item) {
                return [
                    'service_id' => (int) $item->service_id,
                    'service_name' => $item->service_name,
                    'volume_liters' => round(
                        (float) $item->volume_liters,
                        3
                    ),
                    'revenue' => round((float) $item->revenue, 2),
                    'cost' => round((float) $item->cost, 2),
                    'profit' => round((float) $item->profit, 2),
                ];
            })
            ->values()
            ->toArray();

        // Общие показатели.
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

        // Данные для графика по дням.
        $chart = (clone $query)
            ->selectRaw('DATE(transactions.remote_created_at) as date')
            ->selectRaw('SUM(transactions.profit_revenue) as revenue')
            ->selectRaw('SUM(transactions.profit_cost) as cost')
            ->selectRaw('SUM(transactions.profit_profit) as profit')
            ->groupByRaw('DATE(transactions.remote_created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => round((float) $item->revenue, 2),
                    'cost' => round((float) $item->cost, 2),
                    'profit' => round((float) $item->profit, 2),
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
     * Базовый запрос транзакций.
     */
    private function baseQuery(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $serviceId
    ): Builder {
        return DB::table('transactions')
            ->whereBetween('transactions.remote_created_at', [
                $dateFrom,
                $dateTo,
            ])
            ->whereNotNull('transactions.profit_service_name')
            ->whereNotNull('transactions.profit_revenue')
            ->whereNotNull('transactions.profit_cost')
            ->whereNotNull('transactions.profit_profit')
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

        return round(
            (($current - $previous) / abs($previous)) * 100,
            2
        );
    }
}
