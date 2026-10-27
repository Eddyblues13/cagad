<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Categories that have at least one published post, with their counts.
     */
    public function index(): AnonymousResourceCollection
    {
        $published = fn (Builder $query) => $query->published();

        $categories = Category::query()
            ->whereHas('posts', $published)
            ->withCount(['posts' => $published])
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }
}
