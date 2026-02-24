<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    protected $with = ['category'];

    protected $fillable = [
        'user_id', 'category_id', 'description',
        'amount', 'type', 'frequency',
        'starts_at', 'next_due_date', 'ends_at', 'is_active'
    ];

    protected $casts = [
        'starts_at'     => 'date',
        'next_due_date' => 'date',
        'ends_at'       => 'date',
        'amount'        => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
