<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = [
        'name',
        'user_id',
        'color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function budgets() {
        return $this->hasMany(Budget::class);
    }

    public function getBadgeHtmlAttribute()
    {
        $color = $this->color ?? '#6b7280';

        return "<span style='display:inline-flex;align-items:center;gap:6px'>
                    <span style='width:10px;height:10px;border-radius:50%;background:{$color}'></span>
                    {$this->name}
                </span>";
    }

}
