<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Every category with its total number of posts, drafts included.
     */
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::withCount('posts')->orderBy('name')->get());
    }

    public function store(CategoryRequest $request): CategoryResource
    {
        $category = Category::create($request->validated());

        return CategoryResource::make($category)->additional(['message' => 'Category created.']);
    }

    public function show(Category $category): CategoryResource
    {
        return CategoryResource::make($category->loadCount('posts'));
    }

    public function update(CategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return CategoryResource::make($category)->additional(['message' => 'Category saved.']);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => "“{$category->name}” was deleted. Its posts are now uncategorised."]);
    }
}
