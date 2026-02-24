<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Api\V1\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Auth::user()->categories);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $category = Auth::user()->categories()->create($fields);

        return new CategoryResource($category);
    }

    public function show(Category $category)
    {
        abort_if($category->user_id !== Auth::id(), 403, 'Unauthorized');

        return new CategoryResource($category);
    }
}
