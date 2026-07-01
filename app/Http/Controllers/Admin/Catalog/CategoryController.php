<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreCategoryRequest;
use App\Http\Requests\Admin\Catalog\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\TaxResource;
use App\Models\Category;
use App\Models\Item;
use App\Models\Tax;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::CATALOG_CATEGORIES);

        $sort = request('sort', 'newest');
        $perPage = (int) request('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) request('search', ''));

        $categories = Category::query()
            ->with('tax')
            ->withCount('items')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when($sort === 'newest', fn ($query) => $query->orderByDesc('created_at'))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('name'))
            ->when($sort === 'items_asc', fn ($query) => $query->orderBy('items_count'))
            ->when($sort === 'items_desc', fn ($query) => $query->orderByDesc('items_count'))
            ->when(! in_array($sort, ['oldest', 'newest', 'name_asc', 'name_desc', 'items_asc', 'items_desc'], true), fn ($query) => $query->orderByDesc('created_at'))
            ->paginate($perPage)
            ->withQueryString();

        $taxes = Tax::query()
            ->orderBy('name')
            ->get();

        $itemsCategoryId = request('items_category_id');
        $itemsForCategory = null;
        $itemsCategory = null;

        if ($itemsCategoryId) {
            $itemsCategory = Category::query()
                ->select('id', 'name')
                ->find($itemsCategoryId);

            if ($itemsCategory) {
                $itemsForCategory = Item::query()
                    ->where('category_id', $itemsCategory->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return Inertia::render('Admin/Catalog/Categories/Index', [
            'categories' => CategoryResource::collection($categories),
            'categories_count' => $categories->total(),
            'taxes' => TaxResource::collection($taxes),
            'category_items' => $itemsForCategory ? ItemResource::collection($itemsForCategory) : null,
            'category_items_category' => $itemsCategory,
            'filters' => [
                'sort' => $sort,
                'per_page' => $perPage,
                'items_category_id' => $itemsCategory?->id,
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_CATEGORIES);

        $data = $request->validated();
        $data['tax_id'] = $data['tax_id'] ?? null;
        if ($data['tax_id'] === '') {
            $data['tax_id'] = null;
        }
        $data['has_discount'] = (bool) ($data['has_discount'] ?? false);
        $data['discount_is_percentage'] = (bool) ($data['discount_is_percentage'] ?? false);

        if (! $data['has_discount']) {
            $data['discount_amount'] = null;
            $data['discount_is_percentage'] = false;
        }

        $this->categoryService->create($data);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_CATEGORIES);

        $data = $request->validated();
        $data['tax_id'] = $data['tax_id'] ?? null;
        if ($data['tax_id'] === '') {
            $data['tax_id'] = null;
        }
        $data['has_discount'] = (bool) ($data['has_discount'] ?? false);
        $data['discount_is_percentage'] = (bool) ($data['discount_is_percentage'] ?? false);

        if (! $data['has_discount']) {
            $data['discount_amount'] = null;
            $data['discount_is_percentage'] = false;
        }

        $this->categoryService->update($category, $data);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_CATEGORIES);

        $this->categoryService->delete($category);

        return back()->with('success', 'Category deleted successfully.');
    }
}
