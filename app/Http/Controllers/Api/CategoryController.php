<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Category::latest()->paginate(15);

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', Category::class);

       $validated = $request->validated();
       $validated['tenant_id'] = $request->user()->tenant_id; 
       $validated['slug'] = Str::slug($validated['name']);
       
       $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => new CategoryResource($category),
        ], 201);
    }

    public function show(Category $category): CategoryResource
    {
        Gate::authorize('view', $category);

        return response()->json([
            'category' => new CategoryResource($category)
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        Gate::authorize('update', $category);

        $validated = $request->validated();
        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => new CategoryResource($category),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ]);
    }

}
