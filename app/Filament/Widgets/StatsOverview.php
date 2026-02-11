<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBalance = \App\Models\Transaction::sum('amount');

        $monthlyIncome = \App\Models\Transaction::where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $monthlyExpenses = \App\Models\Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        return [
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Balance', number_format($totalBalance, 2) . ' EUR')
                ->description('Your net worth')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($totalBalance >= 0 ? 'success' : 'danger'),

            \Filament\Widgets\StatsOverviewWidget\Stat::make('Monthly Income', number_format($monthlyIncome, 2) . ' EUR')
                ->description('Earnings this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            \Filament\Widgets\StatsOverviewWidget\Stat::make('Monthly Expenses', number_format(abs($monthlyExpenses), 2) . ' EUR')
                ->description('Spending this month')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
