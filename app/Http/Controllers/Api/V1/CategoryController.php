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
        $this->authorize('viewAny', Category::class);

        return CategoryResource::collection(Auth::user()->categories);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Category::class);

        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $category = Auth::user()->categories()->create($fields);

        return new CategoryResource($category);
    }

    public function show(Category $category)
    {
        $this->authorize('view', $category);

        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $fields = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $category->update($fields);

        return new CategoryResource($category);
    }
}
