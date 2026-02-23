<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;


class CategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Expenses by Category';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Transaction::where('transactions.user_id', Auth::id())
            ->where('transactions.type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.name')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => ['#f87171', '#60a5fa', '#fbbf24', '#34d399', '#a78bfa'],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getMaxHeight(): ?string
    {
        return '275px';
    }

}