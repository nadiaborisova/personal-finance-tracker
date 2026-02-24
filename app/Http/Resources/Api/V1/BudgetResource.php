<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\CategoryResource;

class BudgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $spent = (float)($this->spent ?? 0);
        $amount = (float)$this->amount;

        return [
            'id' => $this->id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'amount' => $amount,
            'spent' => $spent,
            'remaining' => round($amount - $spent, 2),
            'percentage' => $amount > 0 ? round(($spent / $amount) * 100, 2) : 0,
            'starts_at' => $this->starts_at?->format('Y-m-d'),
            'ends_at' => $this->ends_at?->format('Y-m-d'),
        ];
    }
}
