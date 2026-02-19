<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Carbon\Carbon;

class TransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Transactions';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $query = Transaction::query()->where('user_id', Auth::id());

        $incomeData = Trend::query((clone $query)->where('type', 'income'))
            ->dateColumn('transaction_date')
            ->between(
                start: now()->subMonths(6)->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perMonth()
            ->sum('amount');

        $expenseData = Trend::query((clone $query)->where('type', 'expense'))
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
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenseData->map(fn ($value) => abs($value->aggregate)),
                    'borderColor' => '#ef4444',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $incomeData->map(fn ($value) => Carbon::parse($value->date)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
