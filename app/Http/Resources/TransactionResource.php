<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'type' => $this->type,
            'category' => $this->category,
            'date' => $this->transaction_date->format('Y-m-d'),
            'human_date' => $this->transaction_date->diffForHumans(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
