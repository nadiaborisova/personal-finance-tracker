<?php

namespace App\Filament\Support;

use App\Models\Category;

class CategoryBadge
{
    public static function render(?Category $category): string
    {
        $color = $category?->color ?? '#6b7280';
        $name = $category?->name ?? 'N/A';

        return "<span style='display:inline-flex;align-items:center;gap:6px'>
            <span style='width:10px;height:10px;border-radius:50%;background:{$color};flex-shrink:0'></span>
            {$name}
        </span>";
    }
}