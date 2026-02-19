<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;


class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();

        $monthlyIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $monthlyExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $totalBalance = $monthlyIncome - $monthlyExpenses;

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
