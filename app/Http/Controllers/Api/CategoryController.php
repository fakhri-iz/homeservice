<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryApiResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    public function index(Request $request)
    {
        $categories = Category::withCount(['homeServices']);

        if ($request->has('limit')) {
            $categories->limit($request->input('limit'));
        }

        return CategoryApiResource::collection($categories->get());
    }

    //model binding
    public function show(Category $category)
    {
        $category->load(['homeServices', 'popularServices']);
        $category->loadCount(['homeServices']);

        return new CategoryApiResource($category);
    }
}
