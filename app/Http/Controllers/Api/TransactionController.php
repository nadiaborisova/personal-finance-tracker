<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->transactions();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }


        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(10);

        return response()->json($transactions);
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

        return response()->json($transaction, 201);
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['message' => 'Access denied!'], 403);
        }
        return $transaction;
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['message' => 'Access denied!'], 403);
        }

        $fields = $request->validate([
            'description' => 'sometimes|required|string',
            'amount'      => 'sometimes|required|numeric|min:0.01',
            'type'        => 'sometimes|required|in:income,expense',
        ]);

        $transaction->update($fields);

        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['message' => 'Access denied!'], 403);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction has been moved to trash.']);
    }

    public function stats()
    {
        $user = Auth::user();

        $totalIncome = $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpense = $user->transactions()->where('type', 'expense')->sum('amount');

        $categoryBreakdown = $user->transactions()
            ->where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        $thisMonthExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $thisMonthIncome = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $biggestExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->orderBy('amount', 'desc')
            ->first();

        return response()->json([
            'balance' => [
                'total_income' => round($totalIncome, 2),
                'total_expense' => round($totalExpense, 2),
                'current_balance' => round($totalIncome - $totalExpense, 2),
            ],
            'this_month' => [
                'month_name' => now()->format('F Y'),
                'income' => round( $thisMonthIncome, 2),
                'expense' => round($thisMonthExpense, 2),
            ],
            'top_expenses_by_category' => $categoryBreakdown,
            'biggest_purchase' => $biggestExpense ? [
                'description' => $biggestExpense->description,
                'amount' => $biggestExpense->amount,
                'date' => $biggestExpense->transaction_date
            ] : null,
        ]);
    }
    public function trashed()
    {
        $transactions = Auth::user()->transactions()->onlyTrashed()->get();
        return response()->json($transactions);
    }

    public function restore($id)
    {
        $transaction = Auth::user()->transactions()->onlyTrashed()->findOrFail($id);
        $transaction->restore();

        return response()->json([
            'message' => 'Transaction restored successfully.',
            'transaction' => $transaction
        ]);
    }

    public function forceDelete($id)
    {
        $transaction = Auth::user()->transactions()->withTrashed()->findOrFail($id);
        $transaction->forceDelete();

        return response()->json([
            'message' => 'Transaction permanently deleted.'
        ]);
    }

}