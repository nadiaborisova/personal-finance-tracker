<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use App\Http\Resources\Api\V1\RecurringTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecurringTransactionController extends Controller
{
    public function __construct(
        private RecurringTransactionService $service
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', RecurringTransaction::class);

        $recurring = $request->user()
            ->recurringTransactions()
            ->with('category')
            ->get();

        return RecurringTransactionResource::collection($recurring);
    }

    public function store(Request $request)
    {
        $this->authorize('create', RecurringTransaction::class);

        $data = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'description' => 'required|string',
            'amount'      => 'required|numeric|min:0.01',
            'type'        => 'required|in:income,expense',
            'frequency'   => 'required|in:daily,weekly,monthly,yearly',
            'starts_at'   => 'required|date',
            'ends_at'     => 'nullable|date|after:starts_at',
        ]);

        $recurring = $this->service->create($data, $request->user()->id);

        return new RecurringTransactionResource($recurring->load('category'));
    }

    public function show(RecurringTransaction $recurringTransaction)
    {
        $this->authorize('view', $recurringTransaction);

        return new RecurringTransactionResource($recurringTransaction->load('category'));
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        $this->authorize('update', $recurringTransaction);

        $data = $request->validate([
            'category_id' => [
                'sometimes',
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'description' => 'sometimes|string|max:255',
            'amount'      => 'sometimes|numeric|min:0.01',
            'is_active'   => 'sometimes|boolean',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);

        $recurring = $this->service->update($recurringTransaction, $data);

        return new RecurringTransactionResource($recurring->load('category'));
    }

    public function destroy(RecurringTransaction $recurringTransaction)
    {
        $this->authorize('delete', $recurringTransaction);

        $this->service->delete($recurringTransaction);

        return response()->noContent();
    }
}
