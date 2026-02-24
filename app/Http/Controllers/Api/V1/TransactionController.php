<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TransactionResource;
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
            'category_id'      => 'required|exists:categories,id,user_id,' . Auth::id(),
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
            'category_id'         => 'sometimes|required|string',
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
}