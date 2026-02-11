<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;

class TransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Transactions';

    protected function getData(): array
    {
        $incomeData = Trend::query(Transaction::query()->where('type', 'income'))
            ->dateColumn('transaction_date')
            ->between(
                start: now()->subMonths(6)->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perMonth()
            ->sum('amount');

        $expenseData = Trend::query(Transaction::query()->where('type', 'expense'))
            ->dateColumn('transaction_date')
            ->between(
                start: now()->subMonths(6)->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $incomeData->map(fn ($value) => $value->aggregate),
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenseData->map(fn ($value) => abs($value->aggregate)),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $incomeData->map(fn ($value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
