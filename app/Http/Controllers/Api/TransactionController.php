<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TransactionController extends Controller
{

        public function index(Request $request)
    {
        $transactions = Auth::user()->transactions()
            ->orderBy('transaction_date', 'desc')
            ->paginate(10);

        return TransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'type'             => 'required|in:income,expense',
            'category'         => 'required|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction = Auth::user()->transactions()->create($validatedData);
        
        return new TransactionResource($transaction);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return new TransactionResource($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $fields = $request->validate([
            'description'      => 'sometimes|required|string|max:255',
            'amount'           => 'sometimes|required|numeric|min:0.01',
            'type'             => 'sometimes|required|in:income,expense',
            'category'         => 'sometimes|required|string',
            'transaction_date' => 'sometimes|required|date',
        ]);

        $transaction->update($fields);

        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return response()->json(['message' => 'Transaction has been moved to trash.']);
    }

    public function trashed()
    {
        $transactions = Auth::user()->transactions()->onlyTrashed()->get();
        
        return TransactionResource::collection($transactions);
    }

    public function restore($id)
    {
        $transaction = Auth::user()->transactions()->onlyTrashed()->findOrFail($id);
        
        $transaction->restore();

        return response()->json([
            'message' => 'Transaction restored successfully.',
            'transaction' => new TransactionResource($transaction)
        ]);
    }

    public function forceDelete($id)
    {
        $transaction = Auth::user()->transactions()->withTrashed()->findOrFail($id);

        $this->authorize('delete', $transaction); 

        $transaction->forceDelete();

        return response()->json(['message' => 'Transaction permanently deleted.']);
    }

    public function stats()
    {
        $user = Auth::user();
        $now = now();

        $totals = $user->transactions()
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
                SUM(CASE WHEN type = 'income' AND strftime('%m', transaction_date) = ? AND strftime('%Y', transaction_date) = ? THEN amount ELSE 0 END) as month_income,
                SUM(CASE WHEN type = 'expense' AND strftime('%m', transaction_date) = ? AND strftime('%Y', transaction_date) = ? THEN amount ELSE 0 END) as month_expense
            ", [$now->format('m'), $now->format('Y'), $now->format('m'), $now->format('Y')])
            ->first();

        $categoryBreakdown = $user->transactions()
            ->where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category,
                'total' => (float) $item->total
            ]);

        $biggestExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->orderByDesc('amount')
            ->first();

        return response()->json([
            'balance' => [
                'total_income' => (float) ($totals->total_income ?? 0),
                'total_expense' => (float) ($totals->total_expense ?? 0),
                'current_balance' => round(($totals->total_income ?? 0) - ($totals->total_expense ?? 0), 2),
            ],
            'this_month' => [
                'month_name' => $now->format('F Y'),
                'income' => (float) ($totals->month_income ?? 0),
                'expense' => (float) ($totals->month_expense ?? 0),
                'savings_rate' => $totals->month_income > 0 
                    ? round((($totals->month_income - $totals->month_expense) / $totals->month_income) * 100, 2) . '%' 
                    : '0%',
            ],
            'top_expenses_by_category' => $categoryBreakdown,
            'biggest_purchase' => $biggestExpense ? [
                'description' => $biggestExpense->description,
                'amount' => (float) $biggestExpense->amount,
                'date' => $biggestExpense->transaction_date->format('Y-m-d')
            ] : null,
        ]);
    }
}