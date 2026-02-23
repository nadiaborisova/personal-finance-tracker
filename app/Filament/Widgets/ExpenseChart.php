<?php
namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;


class ExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Expenses (Current Month)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Transaction::where('user_id', Auth::id())
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->select(DB::raw("DATE(transaction_date) as date"), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => '#ef4444',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getColumnSpan(): string
    {
        return 'full';
    }
}
