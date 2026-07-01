<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreItemRequest;
use App\Http\Requests\Admin\Catalog\UpdateItemRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\TaxResource;
use App\Models\Category;
use App\Models\Item;
use App\Models\Tax;
use App\Services\ItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function __construct(protected ItemService $itemService) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::CATALOG_ITEMS);

        $sort = request('sort', 'newest');
        $perPage = (int) request('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) request('search', ''));

        $items = Item::query()
            ->with(['category', 'tax'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when($sort === 'newest', fn ($query) => $query->orderByDesc('created_at'))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('name'))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('unit_price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('unit_price'))
            ->when(! in_array($sort, ['oldest', 'newest', 'name_asc', 'name_desc', 'price_asc', 'price_desc'], true), fn ($query) => $query->orderByDesc('created_at'))
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        $taxes = Tax::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Catalog/Items/Index', [
            'items' => ItemResource::collection($items),
            'items_count' => $items->total(),
            'categories' => CategoryResource::collection($categories),
            'taxes' => TaxResource::collection($taxes),
            'filters' => [
                'sort' => $sort,
                'per_page' => $perPage,
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_ITEMS);

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

        $this->itemService->create($data);

        return back()->with('success', 'Item created successfully.');
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_ITEMS);

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

        $this->itemService->update($item, $data);

        return back()->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_ITEMS);

        $this->itemService->delete($item);

        return back()->with('success', 'Item deleted successfully.');
    }
}
