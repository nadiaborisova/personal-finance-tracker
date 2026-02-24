<?php

namespace App\Http\Resources\api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\CategoryResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'type' => $this->type,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'date' => $this->transaction_date->format('Y-m-d'),
            'human_date' => $this->transaction_date->diffForHumans(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
