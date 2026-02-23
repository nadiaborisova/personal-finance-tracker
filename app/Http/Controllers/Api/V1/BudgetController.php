<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Auth::user()->budgets()->with('category')->get()->map(function ($budget) {
            $spent = Transaction::where('category_id', $budget->category_id)
                ->where('user_id', Auth::id())
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$budget->starts_at, $budget->ends_at])
                ->sum('amount');

            return [
                'id' => $budget->id,
                'category_name' => $budget->category?->name ?? 'N/A',
                'amount' => $budget->amount,
                'spent' => (float)$spent,
                'remaining' => $budget->amount - $spent,
                'percentage' => $budget->amount > 0 ? round(($spent / $budget->amount) * 100, 2) : 0,
                'starts_at' => $budget->starts_at,
                'ends_at' => $budget->ends_at,
            ];
        });

        return response()->json($budgets);
    }
}
