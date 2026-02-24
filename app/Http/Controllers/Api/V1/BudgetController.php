<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BudgetResource;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class BudgetController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Budget::class);

        $budgets = Auth::user()->budgets()
            ->with('category')
            ->withSum(['transactions as spent' => function ($query) {
                $query->where('user_id', Auth::id())
                    ->where('type', 'expense')
                    ->whereColumn('transaction_date', '>=', 'budgets.starts_at')
                    ->whereColumn('transaction_date', '<=', 'budgets.ends_at');
            }], 'amount')
            ->get();

        return BudgetResource::collection($budgets);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Budget::class);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $budget = Auth::user()->budgets()->create($validated);

        return new BudgetResource($budget->load('category'));
    }

    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);

        return new BudgetResource($budget->load('category'));
    }

    public function update(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'sometimes|required|date|after_or_equal:starts_at',
        ]);

        $budget->update($validated);

        return new BudgetResource($budget->load('category'));
    }

    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return response()->noContent(); 
    }
}