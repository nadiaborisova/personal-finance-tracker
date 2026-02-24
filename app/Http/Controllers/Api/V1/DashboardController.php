<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = now();
        $lastMonth = now()->subMonth();

        return response()->json([
            'overview' => $this->getOverviewStats($user),
            'monthly_comparison' => $this->getMonthlyComparison($user, $now, $lastMonth),
            'spending_patterns' => $this->getSpendingPatterns($user),
            'budget_health' => $this->getBudgetHealth($user),
        ]);
    }

    private function getOverviewStats($user)
    {
        $totals = $user->transactions()
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            ")->first();

        return [
            'net_worth' => round($totals->total_income - $totals->total_expense, 2),
            'total_income' => (float)$totals->total_income,
            'total_expense' => (float)$totals->total_expense,
        ];
    }

    private function getMonthlyComparison($user, $now, $lastMonth)
    {
        $data = $user->transactions()
            ->where('transaction_date', '>=', $lastMonth->startOfMonth())
            ->selectRaw("
                strftime('%m', transaction_date) as month,
                type,
                SUM(amount) as total
            ")
            ->groupBy('month', 'type')
            ->get();

        $currentIncome = $data->where('month', $now->format('m'))->where('type', 'income')->sum('total');
        $prevIncome = $data->where('month', $lastMonth->format('m'))->where('type', 'income')->sum('total');

        return [
            'current_month' => [
                'income' => $currentIncome,
                'expense' => $data->where('month', $now->format('m'))->where('type', 'expense')->sum('total'),
            ],
            'income_growth_pc' => $prevIncome > 0 ? round((($currentIncome - $prevIncome) / $prevIncome) * 100, 2) : 0
        ];
    }

    private function getSpendingPatterns($user)
    {
        return $user->transactions()
            ->where('type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.color', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    private function getBudgetHealth($user)
    {
        return $user->budgets()
            ->with('category')
            ->withSum(['transactions as spent' => function ($query) {
                $query->where('type', 'expense')
                    ->whereColumn('transaction_date', '>=', 'budgets.starts_at')
                    ->whereColumn('transaction_date', '<=', 'budgets.ends_at');
            }], 'amount')
            ->get()
            ->map(fn($budget) => [
                'category' => $budget->category->name,
                'limit' => (float)$budget->amount,
                'spent' => (float)($budget->spent ?? 0),
                'percentage' => $budget->amount > 0 ? round((($budget->spent ?? 0) / $budget->amount) * 100, 2) : 0,
                'status' => ($budget->spent ?? 0) > $budget->amount ? 'over_budget' : 'on_track'
            ]);
    }
}