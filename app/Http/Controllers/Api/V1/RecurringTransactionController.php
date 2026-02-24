<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use App\Http\Resources\Api\V1\RecurringTransactionResource;
use Illuminate\Http\Request;

class RecurringTransactionController extends Controller
{
    public function __construct(
        private RecurringTransactionService $service
    ) {}

    public function index(Request $request)
    {
        $recurring = $request->user()
            ->recurringTransactions()
            ->with('category')
            ->get();

        return RecurringTransactionResource::collection($recurring);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
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

    public function show(Request $request, RecurringTransaction $recurringTransaction)
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        return new RecurringTransactionResource($recurringTransaction->load('category'));
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'description' => 'sometimes|string',
            'amount'      => 'sometimes|numeric|min:0.01',
            'is_active'   => 'sometimes|boolean',
            'ends_at'     => 'nullable|date',
        ]);

        $recurring = $this->service->update($recurringTransaction, $data);

        return new RecurringTransactionResource($recurring->load('category'));
    }

    public function destroy(Request $request, RecurringTransaction $recurringTransaction)
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        $this->service->delete($recurringTransaction);

        return response()->json(null, 204);
    }
}
