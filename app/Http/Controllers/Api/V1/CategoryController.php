<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->categories);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string',
        ]);

        $category = Auth::user()->categories()->create($fields);

        return response()->json($category, 201);
    }
}
