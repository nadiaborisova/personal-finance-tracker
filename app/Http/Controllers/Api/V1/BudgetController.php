<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\V1\BudgetResource;


class BudgetController extends Controller
{    
    public function index()
    {
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
}
